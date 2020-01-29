<?php


namespace Erp;

/**
 * Class CardEntity
 *
 * @author  Doğan Can <dgncan@gmail.com>
 * @package Erp
 */
class CardEntity extends BaseEntity
{
    public $ACCOUNT_TYPE = '2';
    public $CODE;
    public $TITLE;
    public $ADDRESS1;
    public $TOWN;
    public $CITY;
    public $COUNTRY_CODE = 'TR';
    public $COUNTRY = 'TÜRKİYE';
    public $TELEPHONE1;
    public $TELEPHONE1_CODE;
    public $TAX_OFFICE = 'NİHAİ TÜKETİCİ';
    public $CONTACT;
    public $E_MAIL;
    public $CORRESP_LANG = 1;
    public $CREATED_BY;
    public $DATE_CREATED;
    public $HOUR_CREATED;
    public $MIN_CREATED;
    public $SEC_CREATED;
    public $NOTES;
    public $GL_CODE = '120.34.N001';
    public $CL_ORD_FREQ = 1;
    public $INVOICE_PRNT_CNT = 1;
    public $PURCHBRWS = 1;
    public $SALESBRWS = 1;
    public $IMPBRWS = 1;
    public $EXPBRWS = 1;
    public $FINBRWS = 1;
    public $COLLATRLRISK_TYPE = 1;
    public $ACC_RISK_TOTAL;
    public $REP_ACC_RISK_TOTAL;
    public $PERSCOMPANY = 1;
    public $TCKNO = '11111111111';
    public $PROFILE_ID = 2;
    public $NAME;
    public $SURNAME;

    public function toXml()
    {
        $xml = '';
        foreach ($this as $key => $val) {
            $xml .= '<' . $key . '>' . $val . '</' . $key . '>';
        }

        return '<?xml version="1.0" encoding="ISO-8859-9"?><AR_APS><AR_AP DBOP="INS">' . $xml . '</AR_AP></AR_APS>';
    }

    public function generateCode()
    {
        return '0.' . date("YmdHis");
    }

    public function setPhoneNumber($phoneStr)
    {
        $right = substr($phoneStr, -7);
        $left = substr($phoneStr, -10, 3);
        $this->TELEPHONE1_CODE = $left;
        $this->TELEPHONE1 = $right;
    }

    public function setEmail($invoiceEmail)
    {
        $this->E_MAIL = 'info@dogancan.net';
        if ($invoiceEmail != '') {
            $this->E_MAIL = $invoiceEmail . ';' . $this->E_MAIL;
        }
    }

    public function setTckno($corporate, $invoiceVatOffice, $invoiceVatNumber)
    {
        if ($corporate == 1) {
            //todo: şirketse TCKNO ne olacak belirlenmeli
        } else {
            if ($invoiceVatNumber != '') {
                $this->TCKNO = $invoiceVatNumber;
            }
        }
    }
}
