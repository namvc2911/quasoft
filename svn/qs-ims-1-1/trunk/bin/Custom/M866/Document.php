<?php
class Qss_Bin_Custom_M866_Document extends Qss_Lib_Bin
{
    public $_prefix   = '#';
    public $_lenth    = 3;
    public $_docField = 'So';
    public function getPrefix($data)
    {
    	//mặc định không thay đổi//
    	return '';
    }
}