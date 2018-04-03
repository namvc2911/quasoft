<?php
class Qss_Bin_Calculate_OPhieuBaoTri_NgayDungMay extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        $temp        = NULL;

        // Nhập thời gian dừng máy mặc định
        if($this->_object->getFieldByCode('MaNguyenNhanSuCo')->getRefIOID()) //&& !$this->_object->getFieldByCode('NgayDungMay')->getValue()
        {
            $temp = $this->_object->getFieldByCode('NgayBatDau')->getValue();
        }
        else
        {
            $temp = '';
        }

        return $temp;
    }
}
?>