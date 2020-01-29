<?php


namespace Erp;

/**
 * Class ParamEntity
 *
 * @author  Doğan Can <dgncan@gmail.com>
 * @package Erp
 */
class ParamEntity
{
    public $ReplicMode = 0;
    public $CheckParams = 0;
    public $CheckRight = 0;
    public $ApplyCampaign = 0;
    public $ApplyCondition = 0;
    public $FillAccCodes = 0;
    public $FormSeriLotLines = 0;

    public function toXml()
    {
        $xml = '';
        foreach ($this as $key => $val) {
            $xml .= '<' . $key . '>' . $val . '</' . $key . '>';
        }

        return '<?xml version="1.0" encoding="utf-16"?><Parameters>' . $xml . '</Parameters>';
    }
}
