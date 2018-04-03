<?php
class Qss_Bin_Calculate_OPhieuBaoTri_NgayKetThucDungMay extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        $temp        = NULL;

        // Nhập thời gian dừng máy mặc định
        if($this->_object->getFieldByCode('MaNguyenNhanSuCo')->getRefIOID() ) // && !$this->_object->getFieldByCode('NgayKetThucDungMay')->getValue()
        {
            $temp = $this->_object->getFieldByCode('Ngay')->getValue();
        }
        else
        {
            $temp = '';
        }

        return $temp;
    }
}
?>