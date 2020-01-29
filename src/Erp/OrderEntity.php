<?php


namespace Erp;

/**
 * Class OrderEntity
 *
 * @author  Doğan Can <dgncan@gmail.com>
 * @package Erp
 */
class OrderEntity extends BaseEntity
{
    public $NUMBER = '~';
    public $DATE;
    public $TIME;
    public $AUXIL_CODE;
    public $AUTH_CODE;
    public $ARP_CODE;
    public $GL_CODE = '120.34.N001'; # cari muhasebe kodu
    public $TOTAL_DISCOUNTED;
    public $TOTAL_VAT;
    public $TOTAL_GROSS;
    public $TOTAL_NET;
    public $RC_RATE = '1';
    public $RC_NET;
    public $NOTES1;
    public $PAYMENT_CODE;
    public $PAYDEFREF = '1';
    public $ORDER_STATUS = '4'; # kredi kartında 4 diğer ödeme tiplerinde farklı olur.
    public $CREATED_BY;
    public $DATE_CREATED;
    public $HOUR_CREATED;
    public $MIN_CREATED;
    public $SEC_CREATED;
    public $MODIFIED_BY = '1';
    public $DATE_MODIFIED;
    public $HOUR_MODIFIED;
    public $MIN_MODIFIED;
    public $SEC_MODIFIED;
    public $SALESMAN_CODE = 'TKN-01'; # anasayfadan alışlarda default. outbound da farklı olur bu.
    public $CURRSEL_TOTAL = '1';
    public $DATA_REFERENCE;
    public $ORGLOGOID;
    public $DEFNFLDSLIST;
    public $AFFECT_RISK = '0';
    public $GUID;
    public $DEDUCTIONPART1 = '2';
    public $DEDUCTIONPART2 = '3';

    /** @var OrderItemCollection */
    public $orderItemCollection;

    public function setOrderItemCollection(OrderItemCollection $orderItemCollection)
    {
        $this->orderItemCollection = $orderItemCollection;
    }

    public function setTime(\DateTime $dateTime)
    {
        $h = $dateTime->format("H") * 65536 * 256;
        $i = $dateTime->format("i") * 65536;
        $s = $dateTime->format("s") * 256;
        $this->TIME = $h + $i + $s;
    }

    public function toXml()
    {
        $xml = '';
        $xmlOrderItems = '';

        foreach ($this as $key => $val) {
            if ($val instanceof OrderItemCollection) {
                $xmlOrderItems = $this->orderItemCollection->toXml();
            }
            if (!is_object($val)) {
                $xml .= '<' . $key . '>' . $val . '</' . $key . '>';
            }
        }

        return '<?xml version="1.0" encoding="ISO-8859-9"?>
                <SALES_ORDERS>
                    <ORDER_SLIP DBOP="INS">
                    ' . $xml . '
                    ' . $xmlOrderItems . '
                    </ORDER_SLIP>
                </SALES_ORDERS>';
    }

    public function setDate(\DateTime $dateTime)
    {
        $this->DATE = $dateTime->format("d/m/Y");
    }
}
