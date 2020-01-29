<?php


namespace Erp;

/**
 * Class FicheEntity
 *
 * @author  Doğan Can <dgncan@gmail.com>
 * @package Erp
 */
class FicheEntity extends BaseEntity
{
    public $NUMBER = '';
    public $DATE;
    public $TIME;
    public $TOTAL_CREDIT;
    public $AFFECT_RISK;
    public $RC_TOTAL_CREDIT;
    public $ARP_CODE;
    public $CURRSEL_TOTALS;
    public $TYPE;
    public $CREATED_BY;

    public $BANKACC_CODE;
    public $NOTES1;
    public $NOTES2;

    /** @var FicheItemCollection */
    public $ficheItemCollection;

    public function setOrderItemCollection(FicheItemCollection $ficheItemCollection)
    {
        $this->ficheItemCollection = $ficheItemCollection;
    }

    public function setDate(\DateTime $dateTime)
    {
        $this->DATE = $dateTime->format("d/m/Y");
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
        $xmlFicheItems = '';

        foreach ($this as $key => $val) {
            if ($val instanceof FicheItemCollection) {
                $xmlFicheItems = $this->ficheItemCollection->toXml();
            }
            if (!is_object($val)) {
                $xml .= '<' . $key . '>' . $val . '</' . $key . '>';
            }
        }

        return '<?xml version="1.0" encoding="ISO-8859-9"?>
                <ARP_VOUCHERS>
                    <ARP_VOUCHER DBOP="INS">
                    ' . $xml . '
                    ' . $xmlFicheItems . '
                    </ARP_VOUCHER>
                </ARP_VOUCHERS>';
    }

    public function setNote1($refNo)
    {
        $this->NOTES1 = 'islem no: ' . $refNo;
    }

    public function setNote2($bankOrderId)
    {
        $this->NOTES2 = 'siparis no:' . $bankOrderId;
    }

}
