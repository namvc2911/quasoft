<?php
class Qss_Bin_Calculate_OTaiSanCaNhan_KhauHaoConLai extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        return $this->ODanhMucCongCuDungCu->GiaTri(1);
    }
}
?>