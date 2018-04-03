<?php
class Qss_Bin_Calculate_OChiSoCongToDien_TongSoThapDiem extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        return (($this->ChiSoCuoiThapDiem(1) - $this->ChiSoDauThapDiem(1)) * $this->HeSo(1));
    }
}
?>