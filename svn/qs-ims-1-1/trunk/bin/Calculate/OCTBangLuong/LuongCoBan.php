
<?php
class Qss_Bin_Calculate_OCTBangLuong_LuongCoBan extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		$refEmployee = $this->_object->getFieldByCode('MaNhanVien')->intRefIOID;
		$sql         = sprintf(' select *, max(NgayBatDau) 
						 from OHopDongLaoDong 
						 where Ref_MaNhanVien = %1$d 
						 group by Ref_MaNhanVien',$refEmployee);
		$contract    = $this->_db->fetchOne($sql);  /* Hop dong lao dong*/
		$basicSalary = (float) @$contract->LuongCoBan; /* Basic salary */
		return $basicSalary/1000;
	}
}
?>