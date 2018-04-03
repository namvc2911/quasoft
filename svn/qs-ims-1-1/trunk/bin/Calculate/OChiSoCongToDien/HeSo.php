<?php
class Qss_Bin_Calculate_OChiSoCongToDien_HeSo extends Qss_Lib_Calculate
{
    public function __doExecute()
    {    
        return $this->OCongToDien->HeSo(1);    
    }
}