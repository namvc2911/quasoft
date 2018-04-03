<?php
class Qss_Bin_Filter_OChiSoCongToDienNoiBo extends Qss_Lib_Filter
{
    public function getWhere()
    {
        // Chỉ lấy công tơ bán nội bộ
        // Only get meters sold internally
        $retval = sprintf(' and v.Ref_MaCongTo in (SELECT IOID FROM OCongToDien WHERE ifnull(DoiTuongMua, 0) = 3)');
        return $retval;
    }
}