<?php
class Qss_Bin_Custom_M759_Document extends Qss_Lib_Bin
{
    public $_prefix   = '#';
    public $_lenth    = 3;
    public $_docField = 'SoPhieu';
    public $_object;
    public function getPrefix($data)
    {
    	//mặc định không thay đổi//
    	if(isset($data->MaDVBT))
    		return $data->MaDVBT.'.#';
    	if(isset($data->DVBT))
    		return $data->DVBT.'.#';
    }
}