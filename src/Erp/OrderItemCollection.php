<?php


namespace Erp;

/**
 * Class OrderItemCollection
 *
 * @author  Doğan Can <dgncan@gmail.com>
 * @package Erp
 */
class OrderItemCollection extends \ArrayIterator
{
    /** @var OrderItemEntity */
    private $orderItems = [];

    public function addOrderItem(\SimpleXMLElement $orderItem)
    {
        $itemEntity = new OrderItemEntity();
        foreach ($orderItem->children() as $item) {
            $itemKeyName = (string)$item->getName();
            $itemEntity->{$itemKeyName} = (string)$item;
        }
        $this->orderItems[] = $itemEntity;
    }

    public function toXml()
    {
        $xml = '';
        while ($this->valid()) {
            $xml .= $this->current()->toXml();
            $this->next();
        }

        return '<TRANSACTIONS>' . $xml . '</TRANSACTIONS>';
    }
}
