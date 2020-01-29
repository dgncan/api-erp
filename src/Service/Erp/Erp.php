<?php

namespace Service\Erp;

use Slim\App;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Service\AbstractService;
use Common\AuthorizationMiddleware;

use Erp\LogoObject;

/**
 * Class Erp
 *
 * @author Doğan Can <dgncan@gmail.com>
 * @package Service\Erp
 */
class Erp extends AbstractService
{
    private $app;

    public function __construct(App $app)
    {
        parent::__construct($app->getContainer());
        $this->app = $app;

        $app->get('[/]', [$this, 'index']);
        $app->get('/order', [$this, 'getOrders'])
            ->add((new AuthorizationMiddleware($app))->withRequiredScope(['auth.basic']));
        $app->get('/order/{orderId}', [$this, 'getOrder'])
            ->add((new AuthorizationMiddleware($app))->withRequiredScope(['auth.basic']));
        $app->get('/locator', [$this, 'locator'])
            ->add((new AuthorizationMiddleware($app))->withRequiredScope(['auth.basic']));
    }

    /**
     * GET      /api/erp
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @return ResponseInterface|Erp
     */
    public function index(ServerRequestInterface $request, ResponseInterface $response)
    {
        $resource = [
            "Erp API"
        ];

        return $this->response(self::STATUS_OK, $resource);
    }

    /**
     * GET      /api/erp/order
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param                        $args
     * @return ResponseInterface
     */
    public function getOrders(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $params = $request->getQueryParams();

        $erpModel = new \Erp\Erp($this->app->getContainer());
        if ($params['type'] == 'door') {
            $result = $erpModel->findOrderDoor();
        }

        $resource = [
            'request' => [
                'method' => (string)$request->getMethod(),
                'path'   => (string)$request->getUri(),
                'args'   => func_get_arg(2)
            ],
            'list'    => $result
        ];

        return $this->response(self::STATUS_OK, $resource);
    }

    /**
     * GET      /api/erp/order/{orderId}
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param                        $args
     * @return ResponseInterface
     */
    public function getOrder(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $orderId = intval($args['orderId']);

        $order = new \Market\Order($this->container);
        $erpData = $order->getOrderErp($orderId);

        if (count($erpData) > 0) {
            $erpModel = new \Erp\Erp($this->container);
            foreach ($erpData as $item) {
                $xmlObj = $erpModel->find($item['dataType'], $item['dataReference']);
                $tmp['data'] = $xmlObj;
            }
        }

        $resource = [
            'request' => [
                'method' => (string)$request->getMethod(),
                'path'   => (string)$request->getUri(),
                'args'   => func_get_arg(2)//,
            ],
            'list'    => [
                'erp' => $erpData
            ]
        ];

        return $this->response(self::STATUS_OK, $resource);
    }


    /**
     * GET      /api/erp/locator
     *
     * örnek query params:
     * ?dataType=30&ficheNo=A0001
     * ?dataType=3&bankOrderId=TKNMA1234123212
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param                        $args
     * @return ResponseInterface
     */
    public function locator(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        $params = $request->getQueryParams();
        $orderId = filter_var(@$params['orderId'], FILTER_SANITIZE_NUMBER_INT);
        $dataType = filter_var(@$params['dataType'], FILTER_SANITIZE_NUMBER_INT);
        $bankOrderId = filter_var(@$params['bankOrderId'], FILTER_SANITIZE_STRING);
        $ficheNo = filter_var(@$params['ficheNo'], FILTER_SANITIZE_STRING);
        $clientRef = filter_var(@$params['clientRef'], FILTER_SANITIZE_NUMBER_INT);

        // orderId ile gelmişse bankOrderId yi tespit ederiz.
        if ($bankOrderId ==  "") {
            if ($orderId == "") {
                return $this->response(self::STATUS_NO_CONTENT);
            }
            $bankOrderModel = new \Bank\Order($this->container);
            $bankOrderId = $bankOrderModel->getBankOrderId($orderId);
        }
        $erpModel = new \Erp\Erp($this->container);

        $list = false;
        switch ($dataType) {
            case LogoObject::DO_CARI_FIS:
                $list = $erpModel->findClCardByBankOrderId($bankOrderId);


                if (isset($list['clCardRef'])) {
                    $clCard = $erpModel->findClCardByReference($list['clCardRef']);
                    $list['code'] = $clCard[0]['code'];
                    $list['incharge'] = $clCard[0]['incharge'];

                    $list['orders']['dataType'] = LogoObject::DO_SATIS_SIPARIS;
                    $list['orders']['list'] = $erpModel->findOrderByClientRef($list['clCardRef']);

                    $list['invoices']['dataType'] = LogoObject::DO_SATIS_FATURA;
                    $list['invoices']['list'] = $erpModel->findInvoiceByClientRef($list['clCardRef']);

                    //$list['invoices']['dataType'] = LogoObject::DO_ALIM_SIPARIS;
                    //$list['invoices']['list'] = $erpModel->findPurchOrderBy($list['xxx']);
                }

                break;
            case LogoObject::DO_SATIS_SIPARIS:
                $list = $erpModel->findOrderByFicheNo($bankOrderId);
                break;
            case LogoObject::DO_SATIS_FATURA:
                $list = $erpModel->findInvoiceByClientRef($clientRef);
                break;
            case LogoObject::DO_ALIM_SIPARIS:
                //$list = $erpModel->findPurchOrderBy($xxx);
                break;
        }

        if (!$list || count($list) == 0 ) {
            return $this->response(self::STATUS_NO_CONTENT);
        }

        $resource = [
            'request' => [
                'method' => (string)$request->getMethod(),
                'path'   => (string)$request->getUri(),
                'args'   => func_get_arg(2)//,
            ],
            'list'    => $list
        ];

        return $this->response(self::STATUS_OK, $resource);
    }
}
