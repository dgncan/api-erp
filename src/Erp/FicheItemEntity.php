<?php


namespace Erp;

/**
 * Class FicheItemEntity
 *
 * @author  Doğan Can <dgncan@gmail.com>
 * @package Erp
 */
class FicheItemEntity extends BaseEntity
{
    public $TRANNO = '';
    public $YEAR;
    public $TC_AMOUNT;
    public $ARP_CODE;
    public $MONTH;
    public $RC_AMOUNT;
    public $TC_XRATE = 1;
    public $GL_CODE1 = '120.34.N001';
    public $AFFECT_RISK = 0;
    public $CREDIT_CARD_NO;
    public $BANKACC_CODE;
    public $CREDIT;
    public $RC_XRATE = 1;
    public $DISTRIBUTION_TYPE_FNO;
    public $SALESMAN_CODE = 'TKN-01';


    public function toXml()
    {
        $xml = '';
        foreach ($this as $key => $val) {
            if (!is_object($val)) {
                $xml .= '<' . $key . '>' . $val . '</' . $key . '>';
            }
        }

        return '<TRANSACTION>' . $xml . '</TRANSACTION>';
    }
}
