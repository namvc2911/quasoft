<?php
class Qss_Model_Master_WorkingCalendar extends Qss_Model_Abstract
{
    public function __construct ()
    {
        parent::__construct();
    }

    public function getCalendarByEquipment($id)
    {
        $sql = sprintf('select * from ODanhSachThietBi as dstb
        				left join OLichLamViec as lich on lich.IOID = dstb.Ref_LichLamViec
        				where dstb.IOID = %1$d', $id);
        return $this->_o_DB->fetchOne($sql);
    }
}