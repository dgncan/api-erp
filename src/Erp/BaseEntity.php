<?php


namespace Erp;

/**
 * Class BaseEntity
 *
 * @author  Doğan Can <dgncan@gmail.com>
 * @package Erp
 */
class BaseEntity
{
    public function __construct($params = [])
    {
        $this->load($params);
    }

    public function load($params)
    {
        if (count($params) > 0) {
            foreach ($params as $key => $val) {
                $this->$key = $val;
            }
        }

        return clone $this;
    }
}
