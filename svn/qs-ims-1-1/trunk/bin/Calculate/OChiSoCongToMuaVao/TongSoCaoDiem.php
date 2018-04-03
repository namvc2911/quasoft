<?php
class Qss_Bin_Calculate_OChiSoCongToMuaVao_TongSoCaoDiem extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        return (($this->ChiSoCuoiCaoDiem(1) - $this->ChiSoDauCaoDiem(1)) *  $this->HeSo(1));
    }
}
?>