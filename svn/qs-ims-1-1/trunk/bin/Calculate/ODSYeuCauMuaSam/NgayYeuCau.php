<?php
class Qss_Bin_Calculate_ODSYeuCauMuaSam_NgayYeuCau extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        return $this->OYeuCauMuaSam->Ngay(1);
    }
}