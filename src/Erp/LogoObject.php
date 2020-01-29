<?php


namespace Erp;

use Common\ApiException;
use Psr\Container\ContainerInterface;
use SoapClient;
use SoapFault;

/**
 * Class LogoObject
 *
 * @author  Doğan Can <dgncan@gmail.com>
 * @package Erp
 */
class LogoObject
{
    /** @var ContainerInterface $container */
    private $container;


    const FUNCTION_APPEND = 'AppendDataObject';
    const FUNCTION_READ = 'ReadDataObject';
    const FUNCTION_DELETE = 'DeleteDataObject';
    const FUNCTION_CALCULATE = 'CalculateDataObject';
    const FUNCTION_EXEC = 'ExecQuery';
    const FUNCTION_DIRECT = 'DirectQuery';
    const FUNCTION_VALUE = 'getValue';
    const FUNCTION_TABLE = 'getTableName';
    const FUNCTION_PRINTDOC = 'printDoc';
    const FUNCTION_INFO = 'getInfo';

    /**
     * Data Nesnelerinden ihtiyacımız olanlar alındı
     * https://docs.logo.com.tr/public/wua/logo-objects/logo-objects-kuetuephanesi/data/data-nesneleri
     */
    const DO = [
        self::DO_MALZEME                 => "Satış Sipariş",
        self::DO_SATIS_SIPARIS           => "Satış Sipariş",
        self::DO_ALIM_SIPARIS            => "Alım Sipariş",
        self::DO_SATIS_IRSALIYE          => "Satış İrsaliye",
        self::DO_SATIS_FATURA            => "Satış Fatura",
        self::DO_BANKA_FISI              => "Banka Fişi",
        self::DO_CARI_CART               => "Cari Kart",
        self::DO_CARI_FIS                => "Cari Fiş",
        self::DO_CARI_SEVKIYAT_ADRESLERI => "Cari Sevkiyat Adresleri"
    ];
    const DO_MALZEME = 0;
    const DO_SATIS_SIPARIS = 3;
    const DO_ALIM_SIPARIS = 4;
    const DO_SATIS_IRSALIYE = 17;
    const DO_SATIS_FATURA = 19;
    const DO_BANKA_FISI = 24;
    const DO_CARI_CART = 30;
    const DO_CARI_FIS = 31;
    const DO_CARI_SEVKIYAT_ADRESLERI = 34;

    private $wsdlUrl;
    private $firmNr;
    private $securityCode;
    private $soapClient;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $this->wsdlUrl = $container->get('settings')['erp']['wsdlUrl'];
        $this->firmNr = $container->get('settings')['erp']['firmNr'];
        $this->securityCode = $container->get('settings')['erp']['securityCode'];
        $this->soapClient = new SoapClient($this->wsdlUrl);
    }

    /**
     * LogoObject e Soap ile istek yapılan bölüm
     *
     * @param $functionName
     * @param $params
     * @return mixed
     */
    protected function request($functionName, $params)
    {
        try {
            $return = $this->soapClient->__call($functionName, [$params]);

            if ($return->errorString != '' || $return->status == 4) {
                throw ApiException::clientError(4100, '' .
                    'status:' . $return->status . ' - errorString:' . $return->errorString);
            }

            return $return;
        } catch (SoapFault $fault) {
            throw ApiException::clientError(5300, '' .
                'Fault - Logoya yapılan isekte hata oluştu: ' . $fault->getMessage());
        } catch (\Exception $e) {
            throw ApiException::clientError(4100, '' .
                'Exception - Logoya yapılan isekte hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * AppendDataObject
     *
     * @param $dataType
     * @param $dataXML
     * @param $paramXML
     * @return mixed
     */
    public function append($dataType, $dataXML, $paramXML)
    {
        $params = [
            'dataType'      => $dataType,
            'dataReference' => 0,
            'dataXML'       => $dataXML,
            'paramXML'      => $paramXML,
            'errorString'   => '',
            'returnNumber'  => '',
            'status'        => 0,
            'FirmNr'        => $this->firmNr,
            'securityCode'  => $this->securityCode
        ];

        return $this->request(self::FUNCTION_APPEND, $params);
    }

    /**
     * ReadDataObject
     *
     * @param $dataType
     * @param $dataReference
     * @return \SimpleXMLElement
     */
    public function read($dataType, $dataReference)
    {
        $params = [
            'dataType'      => $dataType,
            'dataReference' => $dataReference,
            'dataXML'       => '',
            'paramXML'      => '',
            'errorString'   => '',
            'returnNumber'  => '',
            'status'        => 0,
            'LbsLoadPass'   => '',
            'FirmNr'        => $this->firmNr,
            'securityCode'  => $this->securityCode
        ];
        $response = $this->request(self::FUNCTION_READ, $params);
        $response->dataXML = preg_replace("/ISO-8859-9/", "UTF-8", $response->dataXML);
        $obj = simplexml_load_string($response->dataXML);
        if ($obj !== null && $obj !== false) {
            return $obj;
        }

        return false;
    }

    /**
     * ExecQuery
     *
     * @param $sqlText
     * @return mixed
     */
    public function execQuery($sqlText)
    {
        $params = [
            'sqlText'      => base64_encode($sqlText),
            'orderByText'  => '',
            'resultXML'    => '',
            'errorString'  => '',
            'status'       => 0,
            'LbsLoadPass'  => '',
            'securityCode' => $this->securityCode
        ];
        $response = $this->request(self::FUNCTION_EXEC, $params);
        if ($response->status != 3) {
            throw ApiException::serverError(500, $response->errorString, 'LogoObject:execQuery Hata verdi');
        }
        if ($response->resultXML == "") {
            return [];
            throw ApiException::clientError(400, 'Böyle bir kaynak yok. Sorgu sonucu bos.');
        }
        $obj = simplexml_load_string($response->resultXML);
        if ($obj !== null && $obj !== false) {
            $args = [];
            foreach ($obj->RESULTLINE as $line) {
                $lineArr = [];
                foreach ($line->children() as $child) {
                    $lineArr[(string)$child->getName()] = (string)$child;
                }
                $args[] = $lineArr;
            }

            return $args;
        }
    }

    /**
     * DeleteDataObject
     *
     * @param $dataType
     * @param $dataReference
     * @return mixed
     */
    public function delete($dataType, $dataReference)
    {
        $params = [
            'dataType'      => $dataType,
            'dataReference' => $dataReference,
            'errorString'   => '',
            'status'        => 0,
            'LbsLoadPass'   => '',
            'FirmNr'        => $this->firmNr,
            'securityCode'  => $this->securityCode
        ];
        $this->request(self::FUNCTION_DELETE, $params);

        return true;
    }


    /**
     * getInfo logo object in çalışma parametreleri
     */
    public function getInfo()
    {
        $params = [
            'resultXML'    => '',
            'securityCode' => $this->securityCode
        ];
        $response = $this->request(self::FUNCTION_INFO, $params);

        return (string)$response->resultXML;
    }

    /**
     * getInfo logo object in çalışma parametreleri
     */
    public function getTableName()
    {
        $params = [
            'TableNr' => '103'
        ];
        $response = $this->request(self::FUNCTION_TABLE, $params);

        return (string)$response->resultXML;
    }

    /**
     * Gerçek bir insert yapmayan xml i hazırlayan servis,
     *
     * @param $dataType
     * @param $dataXML
     * @param $paramXML
     * @return mixed
     */
    public function calculateDataObject($dataType, $dataXML, $paramXML)
    {
        $params = [
            'dataType'      => $dataType,
            'dataReference' => 0,
            'dataXML'       => $dataXML,
            'paramXML'      => $paramXML,
            'errorString'   => '',
            'returnNumber'  => '',
            'status'        => 0,
            'LbsLoadPass'   => '',
            'FirmNr'        => $this->firmNr,
            'securityCode'  => $this->securityCode
        ];
        $response = $this->request(self::FUNCTION_READ, $params);

        return $response;
    }
}
