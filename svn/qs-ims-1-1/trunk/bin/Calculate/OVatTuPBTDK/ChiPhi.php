<?php
class Qss_Bin_Calculate_OVatTuPBTDK_ChiPhi extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        $soluong  = (int)$this->_object->getFieldByCode('SoLuong')->getValue(false);
        $dongia = (int)$this->_object->getFieldByCode('DonGia')->getValue(false);
        return $soluong * $dongia;
    }
}