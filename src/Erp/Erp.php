<?php

namespace Erp;

use Common\ApiException;
use Exception;

/**
 * Class Erp
 *
 * @author  Doğan Can <dgncan@gmail.com>
 * @package Erp
 */
class Erp
{
    public $container;
    private $tablePrefix = '';
    private $erpPrefix = 'LG_';
    private $firmNr;
    private $logoObject;

    public function __construct($container)
    {
        $this->container = $container;
        $firmNr = trim($this->container->get('settings')['erp']['firmNr']);
        $this->firmNr = $firmNr;
        $firmDonem = trim($this->container->get('settings')['erp']['firmDonem']);

        $this->tablePrefix .= $this->erpPrefix . $firmNr;
        if ($firmDonem != '') {
            $this->tablePrefix .= '_' . $firmDonem;
        }
        $this->logoObject = new LogoObject($this->container);
    }


    # FIND ###

    /**
     * Data Object tipine göre sorgu yapıp ham xml döner.
     *
     * @param $dataType
     * @param $dataReference
     * @return \SimpleXMLElement
     */
    public function find($dataType, $dataReference)
    {
        $xmlObj = $this->logoObject->read($dataType, $dataReference);

        return $xmlObj;
    }

    /**
     * Cari Hesap Bulma
     *
     * @param $xmlObj
     * @return CardEntity
     */
    public function parseClCard($xmlObj)
    {
        $entity = new CardEntity();
        foreach ($xmlObj->AR_AP->children() as $child) {
            $keyName = (string)$child->getName();
            $entity->{$keyName} = (string)$child;
        }
        if ($entity instanceof CardEntity) {
            return $entity;
        }
        throw ApiException::serverError(5300, 'Cari Kart bulunamadı. CardEntity oluşmadı');
    }

    public function parseXml($xml)
    {
        if (false !== ($xmlObj = @simplexml_load_string($xml))) {
            return $xmlObj;
        }
    }

    public function parseDataObject($dataType, $xmlObj)
    {
        switch ($dataType) {
            case LogoObject::DO_SATIS_SIPARIS:
                return $this->parseOrder($xmlObj);
                break;
            case LogoObject::DO_ALIM_SIPARIS:
                return $this->parsePurchase($xmlObj);
                break;
            case LogoObject::DO_CARI_CART:
                return $this->parseClCard($xmlObj);
                break;
            case LogoObject::DO_CARI_FIS:
                return $this->parseClFiche($xmlObj);
                break;
            case LogoObject::DO_SATIS_FATURA:
                return $this->parseInvovice($xmlObj);
                break;
        }
    }

    /**
     * Sipariş Parse etme
     *
     * @param $xmlObj
     * @return OrderEntity
     */
    public function parseOrder($xmlObj)
    {
        $entity = new OrderEntity();
        foreach ($xmlObj->ORDER_SLIP->children() as $child) {
            $keyName = (string)$child->getName();
            if ($keyName === 'TRANSACTIONS') {
                //$orderItemCollection = new OrderItemCollection();
                $itemEntityArr = [];
                foreach ($child->TRANSACTION as $orderItem) {
                    //$orderItemCollection->addOrderItem($item);
                    $itemEntity = new OrderItemEntity();
                    foreach ($orderItem->children() as $item) {
                        $itemKeyName = (string)$item->getName();
                        $itemEntity->{$itemKeyName} = (string)$item;
                    }
                    $itemEntityArr[] = $itemEntity;
                }
                $entity->{$keyName} = $itemEntityArr;
                //$entity->setOrderItemCollection($orderItemCollection);
            } else {
                $entity->{$keyName} = (string)$child;
            }
        }
        if ($entity instanceof OrderEntity) {
            return $entity;
        }
        throw ApiException::serverError(5300, 'Cari Kart bulunamadı. CardEntity oluşmadı');
    }


    private function parsePurchase($xmlObj)
    {
        return $xmlObj;
    }


    /**
     * Cari Fişi bulma
     *
     * @param $xmlObj
     * @return OrderEntity
     */
    public function parseClFiche($xmlObj)
    {
        $entity = new OrderEntity();
        foreach ($xmlObj->ORDER_SLIP->children() as $child) {
            $keyName = (string)$child->getName();
            if ($keyName === 'TRANSACTIONS') {
                //$orderItemCollection = new OrderItemCollection();
                $itemEntityArr = [];
                foreach ($child->TRANSACTION as $orderItem) {
                    //$orderItemCollection->addOrderItem($item);
                    $itemEntity = new OrderItemEntity();
                    foreach ($orderItem->children() as $item) {
                        $itemKeyName = (string)$item->getName();
                        $itemEntity->{$itemKeyName} = (string)$item;
                    }
                    $itemEntityArr[] = $itemEntity;
                }
                $entity->{$keyName} = $itemEntityArr;
                //$entity->setOrderItemCollection($orderItemCollection);
            } else {
                $entity->{$keyName} = (string)$child;
            }
        }
        if ($entity instanceof OrderEntity) {
            return $entity;
        }
        throw ApiException::serverError(5300, 'Cari Kart bulunamadı. CardEntity oluşmadı');
    }

    /**
     * Fatura bulma
     *
     * @param $xmlObj
     * @return InvoiceEntity
     */
    public function parseInvovice($xmlObj)
    {
        $entity = new InvoiceEntity();
        foreach ($xmlObj->INVOICE->children() as $child) {
            $keyName = (string)$child->getName();
            if ($keyName === 'TRANSACTIONS') {
                $itemEntityArr = [];
                foreach ($child->PAYMENT_LIST as $paymentItem) {
                    $itemEntity = new OrderItemEntity();
                    foreach ($paymentItem->children() as $item) {
                        $itemKeyName = (string)$item->getName();
                        $itemEntity->{$itemKeyName} = (string)$item;
                    }
                    $itemEntityArr[] = $itemEntity;
                }
                $entity->{$keyName} = $itemEntityArr;
                //$entity->setOrderItemCollection($orderItemCollection);
            } else {
                $entity->{$keyName} = (string)$child;
            }
        }
        if ($entity instanceof OrderEntity) {
            return $entity;
        }
        throw ApiException::serverError(5300, 'Cari Kart bulunamadı. CardEntity oluşmadı');

        //todo:parse işlemi olacak burda
        return $xmlObj;
    }



    # INSERT ###

    /**
     * Cari Hesap Oluşturma
     *
     * @param $cardEntityXml
     * @return \stdClass
     */
    public function insertClCard($cardEntityXml)
    {
        /**
         * $data{PA_NAME}, $data{PA_LASTNAME}, $data{ADDRESS},$data{CITY} , $data{TOWN},$data{PHONE7}, $data{AREA3}, $prop_id, $data{PA_EMAIL}
         */

        $paramXML = (new ParamEntity())->toXml();

        $response = $this->logoObject->append(LogoObject::DO_CARI_CART, $cardEntityXml, $paramXML);

        $responseObj = new \stdClass();
        $responseObj->dataReference = $response->dataReference;
        $responseObj->dataType = LogoObject::DO_CARI_CART;
        $responseObj->dataXML = $response->dataXML;

        return $responseObj;
    }

    /**
     * Sipariş Oluşturma
     *
     * @param $orderEntityXml
     * @return \stdClass
     */
    public function insertOrder($orderEntityXml)
    {
        /**
         * $code, 'kk', 0, 0, $order, $total, $acccode, $prop_id
         */

        $paramXML = (new ParamEntity())->toXml();

        $response = $this->logoObject->append(LogoObject::DO_SATIS_SIPARIS, $orderEntityXml, $paramXML);

        $responseObj = new \stdClass();
        $responseObj->dataReference = $response->dataReference;
        $responseObj->dataType = LogoObject::DO_SATIS_SIPARIS;
        $responseObj->dataXML = $response->dataXML;

        return $responseObj;
    }

    /**
     * Cari Fiş oluşturma
     *
     * @param $ficheEntityXml
     * @return \stdClass
     */
    public function insertFiche($ficheEntityXml)
    {
        $paramXML = (new ParamEntity())->toXml();

        $response = $this->logoObject->append(LogoObject::DO_CARI_FIS, $ficheEntityXml, $paramXML);

        $responseObj = new \stdClass();
        $responseObj->dataReference = $response->dataReference;
        $responseObj->dataType = LogoObject::DO_CARI_FIS;
        $responseObj->dataXML = $response->dataXML;
        $responseObj->status = $response->status;

        return $responseObj;
    }



    # DELETE ###

    /**
     * Cari Kart silme
     *
     * @param int $dataReference
     * @return bool
     */
    public function deleteClCard($dataReference = 0)
    {
        //todo: cari kartı silerken başka processler olursa buraya konacak. Bu katman bu yüzden şimdilik sade gözüküyor.
        return $this->logoObject->delete(LogoObject::DO_CARI_CART, $dataReference);
    }


    public function deleteOrder($dataReference = 0)
    {
        //todo: order silerken beraberinde belki irsaliyesini de silmek isteriz, ilerde...
        return $this->logoObject->delete(LogoObject::DO_SATIS_SIPARIS, $dataReference);
    }

    # EXEC RAPOR ###

    /**
     * Fiş No ile order data ref bulmak
     *
     * @param string $ficheNo
     * @return mixed
     */
    public function findOrderByFicheNo(string $ficheNo)
    {
        $sql = "SELECT  LOGICALREF as dataReference,
                        FICHENO as ficheNo,
                        DATE_ as dateTime
                FROM [" . $this->tablePrefix . "_ORFICHE] 
                WHERE FICHENO='" . $ficheNo . "'";
        $result = $this->logoObject->execQuery($sql);
        $arr['dataType'] = LogoObject::DO_SATIS_SIPARIS;
        $arr['dataReference'] = $result['0']['LOGICALREF'];
        $arr['ficheNo'] = $result['0']['FICHENO'];

        return $arr;
    }

    public function findPurchaseByFicheNo(string $ficheNo)
    {
        //TODO: ilgili purch orders tablosu bulunup findOrderByFicheNo gibi bişey uygulanacak
    }

    /**
     * Cari referans no dan siparişleri bulur
     *
     * @param string $clientRef
     * @return mixed
     */
    public function findOrderByClientRef(string $clientRef)
    {
        $sql = "SELECT  LOGICALREF as dataReference, 
                        FICHENO as ficheNo, 
                        DATE_ as dateTime
                FROM [" . $this->tablePrefix . "_ORFICHE] 
                WHERE CLIENTREF='" . $clientRef . "'";
        $result = $this->logoObject->execQuery($sql);

        return $result;
    }

    /**
     * CLFICHE tablosundaki GENEXP2 yani NOTES2 alanına göre arama yapar.
     *
     * @param string $bankOrderId
     * @return mixed
     */
    public function findClCardByBankOrderId(string $bankOrderId)
    {
        $sql = "SELECT  LOGICALREF as dataReference, 
                        FICHENO as ficheNo, 
                        DATE_ as dateTime, 
                        CLCARDREF as clCardRef, 
                        BANKACCREF as bankaCcRef, 
                        GENEXP1 as genExp1, 
                        GENEXP2 as genExp2
                FROM [" . $this->tablePrefix . "_CLFICHE] 
                WHERE GENEXP2 LIKE '%" . $bankOrderId . "%'";
        $result = $this->logoObject->execQuery($sql);
        if (!isset($result) || !is_array($result) || count($result) == 0 || !isset($result['0'])) {
            return false;
        }

        $arr = $result['0'];
        $arr['dataType'] = LogoObject::DO_CARI_CART;

        return $arr;
    }


    /**
     * CLCARD tablosundan data reference ile CODE
     *
     * @param string $dataReference
     * @return mixed
     */
    public function findClCardByReference(string $dataReference)
    {
        $sql = "SELECT  LOGICALREF as dataReference, 
                        CODE as code, 
                        INCHARGE as incharge  
                FROM [" . $this->erpPrefix . $this->firmNr . "_CLCARD] 
                WHERE LOGICALREF = " . $dataReference;
        $result = $this->logoObject->execQuery($sql);

        return $result;
    }

    /**
     * INVOICE tablosundaki CLIENTREF alanına göre
     *
     * @param string $clientRef
     * @return mixed
     */
    public function findInvoiceByClientRef(string $clientRef)
    {
        $sql = "SELECT  LOGICALREF as dataReference, 
                        FICHENO as ficheNo, 
                        DATE_ as dateTime 
                FROM [" . $this->tablePrefix . "_INVOICE] 
                WHERE CLIENTREF = '" . $clientRef . "'";
        $result = $this->logoObject->execQuery($sql);

        return $result;
    }


    /**
     * TC kimlikten cl card bulma
     *
     * @param $tckno
     * @return mixed
     */
    public function findClCardByTckn($tckno)
    {
        $sql = "SELECT ACCOUNT_TYPE, CODE, TITLE, TELEPHONE1, TAX_ID, TAX_OFFICE,
                       CONTACT, E_MAIL, ACTIVE, 
                FROM [dbo.Cari_Kart]
                WHERE TCKNO = '" . $tckno . "'";

        return $this->logoObject->execQuery($sql);
    }

    /**
     * Kapıda Ödeme Raporları
     *
     * @return mixed
     */
    public function findOrderDoor()
    {
        $sql = "SELECT TOP 10 * FROM [HVL-KO_Listesi]";
        $arr = $this->logoObject->execQuery($sql);

        return $arr;
    }
}
