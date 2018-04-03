<?php
//cho đà nẵng
class Qss_Bin_Calculate_OPhieuBaoTri_ChiPhiNhanCong extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        $nhancong = (int)$this->_object->getFieldByCode('NhanCong')->getValue(false);
        $dongia  = (int)$this->_object->getFieldByCode('DonGiaNhanCong')->getValue(false);
       	return $nhancong * $dongia;
    }
}