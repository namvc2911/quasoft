<?php

class Qss_Model_Db extends Qss_Model_Abstract
{
    public static function Table($name)
    {
        return new Qss_Model_Db_Table($name);
    }
}