<?php
class Qss_Bin_Calculate_OThanhToan_ThoiHanThanhToan extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        $NgayThanhToan   = $this->NgayThanhToan(1)?$this->NgayThanhToan(1):date('Y-m-d');
        $SoNgayThanhToan = $this->SoNgayThanhToan(1)?$this->SoNgayThanhToan(1):0;

        return date('d-m-Y', strtotime($NgayThanhToan. ' + '.$SoNgayThanhToan.' days'));
    }
}
?>