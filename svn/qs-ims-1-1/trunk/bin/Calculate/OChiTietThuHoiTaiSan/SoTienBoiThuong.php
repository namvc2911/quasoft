<?php
class Qss_Bin_Calculate_OChiTietThuHoiTaiSan_SoTienBoiThuong extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        $retval = 0;
        $hong   = (int)$this->_object->getFieldByCode('Hong')->getValue();

        if($hong)
        {
            $qty       = $this->SoLuong(1)?$this->SoLuong(1):0;
            $unitPrice = $this->DonGia(1)?$this->DonGia(1):0;
            $khauHao   = $this->PhanTramKhauHao(1)?$this->PhanTramKhauHao(1):0;
            $khauHao   = 100 - $khauHao;
            $retval    = ($khauHao > 0)? ($qty * $unitPrice * $khauHao)/100:0;
        }

        return $retval;
    }
}
?>