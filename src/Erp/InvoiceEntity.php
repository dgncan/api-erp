<?php


namespace Erp;

/**
 * Class InvoiceEntity
 *
 * @author  Doğan Can <dgncan@gmail.com>
 * @package Erp
 */
class InvoiceEntity extends BaseEntity
{
    public $AFFECT_RISK;
    public $ARP_CODE;
    public $AUTH_CODE;
    public $AUXIL_CODE;
    public $CREATED_BY;
    public $CURRSEL_TOTALS;
    public $DATA_LINK_REFERENCE;
    public $DATA_REFERENCE;
    public $DATE;
    public $DATE_CREATED;
    public $DEDUCTIONPART1;
    public $DEDUCTIONPART2;
    public $DEFNFLDSLIST;
    public $DISPATCHES;
    public $DOC_DATE;
    public $EARCHIVEDETR_EARCHIVESTATUS;
    public $EARCHIVEDETR_INTPAYMENTTYPE;
    public $EARCHIVEDETR_INTSALESADDR;
    public $EARCHIVEDETR_INVOICEREF;
    public $EARCHIVEDETR_LOGICALREF;
    public $EARCHIVEDETR_SENDMOD;
    public $EBOOK_DOCTYPE;
    public $EDTCURR_GLOBAL_CODE;
    public $EDURATION_TYPE;
    public $EINVOICE;
    public $EINVOICE_TURETPRICESTR;
    public $ESTATUS;
    public $EXIMVAT;
    public $GUID;
    public $HOUR_CREATED;
    public $INTEL_LIST;
    public $MIN_CREATED;
    public $NOTES1;
    public $NUMBER;
    public $OKCINFO_LIST;
    public $ORGLOGOID;
    public $PAYMENT_CODE;
    public $PAYMENT_LIST;
    public $POST_FLAGS;
    public $PREACCLINES;
    public $PROFILE_ID;
    public $RC_NET;
    public $RC_XRATE;
    public $SEC_CREATED;
    public $TC_NET;
    public $TIME;
    public $TOTAL_DISCOUNTED;
    public $TOTAL_GROSS;
    public $TOTAL_NET;
    public $TOTAL_NET_STR;
    public $TOTAL_VAT;
    public $TRACK_NR;
    public $TRANSACTIONS;
    public $TYPE;
    public $VAT_RATE;


    public function toXml(OrderItemCollection $orderItemCollection = null)
    {
    }
}
