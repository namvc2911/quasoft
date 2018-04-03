<?php
class Qss_Model_Master_Workcenter extends Qss_Model_Abstract
{
    public function __construct ()
    {
        parent::__construct();
    }

    public function getWorkcenterByIOID($ioid)
    {
        $sql = sprintf('select * from ODonViSanXuat where IOID = %1$d', $ioid);
        return $this->_o_DB->fetchOne($sql);
    }

    public function getAll()
    {
        $sql = sprintf('SELECT * FROM ODonViSanXuat ORDER BY Ma');
        return $this->_o_DB->fetchAll($sql);
    }
}