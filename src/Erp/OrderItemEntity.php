<?php


namespace Erp;

/**
 * Class OrderItemEntity
 *
 * @author  Doğan Can <dgncan@gmail.com>
 * @package Erp
 */
class OrderItemEntity extends BaseEntity
{

    public $TYPE = '0';
    public $MASTER_CODE;
    public $QUANTITY;
    public $PRICE;
    public $TOTAL;
    public $VAT_RATE;
    public $VAT_AMOUNT;
    public $VAT_BASE;
    public $UNIT_CODE = 'ADET';
    public $UNIT_CONV1 = '1';
    public $UNIT_CONV2 = '1';
    public $VAT_INCLUDED = '1';
    public $DUE_DATE;
    public $PC_PRICE;
    public $ORDER_RESERVE = '0';
    public $RC_XRATE = '1';
    public $TOTAL_NET;
    public $DATA_REFERENCE;
    public $CAMPAIGN_INFOS;
    public $DETAILS = '';
    public $SALESMAN_CODE;
    public $DEFNFLDS;
    public $MULTI_ADD_TAX;
    public $EDT_PRICE;
    public $EDT_CURR = '160';
    public $ORG_DUE_DATE;
    public $ORG_QUANTITY;
    public $GUID;

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
