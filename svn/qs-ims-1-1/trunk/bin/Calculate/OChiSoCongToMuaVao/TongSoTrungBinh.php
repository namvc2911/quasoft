<?php
class Qss_Bin_Calculate_OChiSoCongToMuaVao_TongSoTrungBinh extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        return (($this->ChiSoCuoiTrungBinh(1) - $this->ChiSoDauTrungBinh(1)) *  $this->HeSo(1));
    }
}
?>