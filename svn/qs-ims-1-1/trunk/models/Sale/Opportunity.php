<?php

class Qss_Model_Sale_Opportunity extends Qss_Model_Abstract
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getIncommingEvent($start, $end)
    {
        $nStart   = Qss_Lib_Date::displaytomysql($start);
        $nEnd     = Qss_Lib_Date::displaytomysql($end);
        $sql = sprintf('select * from OGiaoDich
        				inner join OCoHoiBanHang on OCoHoiBanHang.IFID_M504 = OGiaoDich.IFID_M504
        				inner join qsiforms on qsiforms.IFID = OCoHoiBanHang.IFID_M504
        				where OGiaoDich.NgayKetThuc between %1$s and %2$s
        				order by OGiaoDich.NgayKetThuc'
        	,$this->_o_DB->quote($nStart)
        	,$this->_o_DB->quote($nEnd));
        return $this->_o_DB->fetchAll($sql);
    }
}