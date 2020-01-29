<?php


namespace Erp;

/**
 * Class FicheItemCollection
 *
 * @author  Doğan Can <dgncan@gmail.com>
 * @package Erp
 */
class FicheItemCollection extends \ArrayIterator
{
    /** @var FicheItemEntity */
    private $items = [];

    public function addItem(\SimpleXMLElement $objItem)
    {
        $itemEntity = new FicheItemEntity();
        foreach ($objItem->children() as $item) {
            $itemKeyName = (string)$item->getName();
            $itemEntity->{$itemKeyName} = (string)$item;
        }
        $this->items[] = $itemEntity;
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
