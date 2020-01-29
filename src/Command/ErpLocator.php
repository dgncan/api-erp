<?php

namespace Command;

use Erp\Erp;
use Erp\LogoObject;
use Market\Order;
use PDO;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class ErpLocator
 *
 * Özel bir amaç için üretilmiştir. Herkesin işine yaramaz.
 *
 * @author  Doğan Can <dgncan@gmail.com>
 * @package Command
 */
class ErpLocator extends Command
{
    protected $settings;
    protected $runtimePath = __DIR__ . '/../../runtime';

    /** @var SymfonyStyle */
    protected $io;

    /** @var PDO $db */
    protected $db;

    protected $orderId;

    protected function configure()
    {
        $this
            ->setName('erp:locator')
            ->setDescription('Erp den bank order Id ile keşfeder')
            ->setHelp('Erp den bank order Id ile keşfeder');
        $this
            ->addArgument('method', InputArgument::OPTIONAL, 'insert / update')
            ->addArgument('since', InputArgument::OPTIONAL, 'all/24/48')
            ->addArgument('bankOrderId', InputArgument::OPTIONAL, 'banka order id')
            ->addArgument('orderId', InputArgument::OPTIONAL, 'market order id');
    }

    protected function setup(SymfonyStyle $io)
    {
        $this->io = $io;

        $settingsPath = __DIR__ . '/../../conf/settings.php';
        if (!@file_exists($settingsPath)) {
            $io->error("Settings dosyasi yok. Path:" . $settingsPath);
            exit(1);
        }

        $settings = include $settingsPath;
        $this->settings = $settings['settings'];
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $this->setup($io);

        $io->title('Erp Locator');

        $container = new \Slim\Container();
        $container['settings'] = $this->settings;
        $container['db'] = function ($c) {
            $settings = $c->get('settings')['db'];
            $dsn = $settings['driver'] . ":host=" . $settings['host'] . ";dbname=" . $settings['database'] . ";charset=" . $settings['charset'];
            $options = [
                PDO::ATTR_PERSISTENT         => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            try {
                $db = new PDO($dsn, $settings['user'], $settings['password'], $options);

                return $db;
            } catch (\Exception $e) {
                $this->io->error($e->getMessage());
                exit(1);
            }
        };

        $method = $input->getArgument('method');
        $since = $input->getArgument('since');
        $bankOrderId = $input->getArgument('bankOrderId');
        $orderId = $input->getArgument('orderId');

        if ($bankOrderId == "") {
            $orderModel = new Order($container);
            $orderList = $orderModel->getOrdersLite($method, $since);
            if (count($orderList) == 0) {
                throw new \Exception("Bank Order Id yok / OrderListe yok");
            }
        } else {
            $orderList[] = [
                'bankOrderId' => $bankOrderId,
                'orderId'     => $orderId
            ];
        }

        $orderModel = new Order($container);
        $erpModel = new Erp($container);

        foreach ($orderList as $order) {
            $io->section('bankOrderId : ' . $order['bankOrderId'] . ' orderId:' . $order['orderId'] . ' - ' . $order['orderDateTime']);
            try {

                # zaten olanlar çekilir.
                $orderErpDataRaw = $orderModel->getOrderErp($order['orderId'], false);
                foreach ($orderErpDataRaw as $item) {
                    $orderErpData[$item['dataType']] = $item;
                }

                if (false === $list = $erpModel->findClCardByBankOrderId($order['bankOrderId'])) {
                    continue;
                }

                if (isset($list['clCardRef'])) {
                    $clCard = $erpModel->findClCardByReference($list['clCardRef']);
                    $list['code'] = $clCard[0]['code'];
                    $list['incharge'] = $clCard[0]['incharge'];

                    $list['orders']['dataType'] = LogoObject::DO_SATIS_SIPARIS;
                    $list['orders']['list'] = $erpModel->findOrderByClientRef($list['clCardRef']);

                    $list['invoices']['dataType'] = LogoObject::DO_SATIS_FATURA;
                    $list['invoices']['list'] = $erpModel->findInvoiceByClientRef($list['clCardRef']);
                }

                # insert ya da update ler
                # clCard
                $xmlObj = $erpModel->find($list['dataType'], $list['clCardRef']);
                if (isset($orderErpData[$list['dataType']]['dataReference'])) {
                    if ($orderModel->putOrderErp($order['orderId'], $list['dataType'], $list['clCardRef'],
                        $xmlObj->asXML())) {
                        $io->success($order['orderId'] . ' - ' . $list['dataType'] . ' - ' . $list['clCardRef']);
                    }
                } else {
                    if ($orderModel->setOrderErp($order['orderId'], $list['dataType'], $list['clCardRef'],
                        $xmlObj->asXML())) {
                        $io->success($order['orderId'] . ' - ' . $list['dataType'] . ' - ' . $list['clCardRef']);
                    }
                }

                # cari Fiş
                $xmlObj = $erpModel->find(31, $list['dataReference']);
                if (isset($orderErpData[31]['dataReference'])) {
                    if ($orderModel->putOrderErp($order['orderId'], 31, $list['dataReference'], $xmlObj->asXML())) {
                        $io->success($order['orderId'] . ' - 31 - ' . $list['dataReference']);
                    }
                } else {
                    if ($orderModel->setOrderErp($order['orderId'], 31, $list['dataReference'], $xmlObj->asXML())) {
                        $io->success($order['orderId'] . ' - 31 - ' . $list['dataReference']);
                    }
                }

                # Satış Sipariş
                foreach ($list['orders']['list'] as $erpOrder) {
                    $xmlObj = $erpModel->find($list['orders']['dataType'], $erpOrder['dataReference']);
                    if (isset($orderErpData[$list['orders']['dataType']]['dataReference'])) {
                        if ($orderModel->putOrderErp($order['orderId'], $list['orders']['dataType'],
                            $erpOrder['dataReference'], $xmlObj->asXML())) {
                            $io->success($order['orderId'] . ' - ' . $list['orders']['dataType'] . ' - ' . $erpOrder['dataReference']);
                        }
                    } else {
                        if ($orderModel->setOrderErp($order['orderId'], $list['orders']['dataType'],
                            $erpOrder['dataReference'], $xmlObj->asXML())) {
                            $io->success($order['orderId'] . ' - ' . $list['orders']['dataType'] . ' - ' . $erpOrder['dataReference']);
                        }
                    }
                }

                # Satış Fatura
                foreach ($list['invoices']['list'] as $erpInvoices) {
                    $xmlObj = $erpModel->find($list['invoices']['dataType'], $erpInvoices['dataReference']);
                    if (isset($orderErpData[$list['invoices']['dataType']]['dataReference'])) {
                        if ($orderModel->putOrderErp($order['orderId'], $list['invoices']['dataType'],
                            $erpInvoices['dataReference'], $xmlObj->asXML())) {
                            $io->success($order['orderId'] . ' - ' . $list['invoices']['dataType'] . ' - ' . $erpInvoices['dataReference']);
                        }
                    } else {
                        if ($orderModel->setOrderErp($order['orderId'], $list['invoices']['dataType'],
                            $erpInvoices['dataReference'], $xmlObj->asXML())) {
                            $io->success($order['orderId'] . ' - ' . $list['invoices']['dataType'] . ' - ' . $erpInvoices['dataReference']);
                        }
                    }
                }

            } catch (\Exception $e) {
                $this->io->warning($e->getMessage());
            }

        }
    }
}
