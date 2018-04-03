<?php
class Qss_Bin_Calculate_OCTBangLuong_HeSoLuong extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		/* BỎ đi thôi */
		$refEmployee = $this->_object->getFieldByCode('MaNhanVien')->intRefIOID;
		$sql         = sprintf(' select *, max(NgayBatDau) 
						 from OHopDongLaoDong 
						 where Ref_MaNhanVien = %1$d 
						 group by Ref_MaNhanVien',$refEmployee);
		$heSoLuong   = $this->_db->fetchOne($sql); 
		$heSoLuong   = (float) @$heSoLuong->HeSoLuong;
		return $heSoLuong;
	}
}
?>