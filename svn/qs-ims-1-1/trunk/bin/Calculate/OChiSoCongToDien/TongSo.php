<?php
class Qss_Bin_Calculate_OChiSoCongToDien_TongSo extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        return (($this->ChiSoCuoi(1) - $this->ChiSoDau(1)) * $this->HeSo(1));
    }
}
?>