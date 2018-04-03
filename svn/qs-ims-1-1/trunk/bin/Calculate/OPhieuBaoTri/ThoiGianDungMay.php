<?php
class Qss_Bin_Calculate_OPhieuBaoTri_ThoiGianDungMay extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		return $this->calculateDowntime();
	}
	
	private function calculateDowntime()
	{
	    $sdate = $this->_object->getFieldByCode('NgayDungMay')->getValue();
	    $edate = $this->_object->getFieldByCode('NgayKetThucDungMay')->getValue();
	    $stime = $this->_object->getFieldByCode('ThoiGianBatDauDungMay')->getValue();
	    $etime = $this->_object->getFieldByCode('ThoiGianKetThucDungMay')->getValue();
	     
	    if($sdate && $edate)
	    {
	        $sdate = Qss_Lib_Date::displaytomysql($sdate);
	        $edate = Qss_Lib_Date::displaytomysql($edate);
	         
	        $sdate = ($stime)?$sdate .' '. $stime:$sdate .' 00:00:00';
	        $edate = ($etime)?$edate .' '. $etime:$edate .' 23:59:59';
            
	        return Qss_Lib_Date::diffTime($sdate, $edate, 'H');
	    }
	    return 0;
	}
	
}
?>