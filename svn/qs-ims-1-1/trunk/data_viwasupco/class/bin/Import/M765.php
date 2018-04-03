<?php
class Qss_Bin_Import_M765 extends Qss_Lib_Bin
{
	public function __doExecute()
	{
		$ifid = $this->_form->i_IFID;
		if(!$ifid)
		{
			$sql = sprintf('update ONhatTrinhThietBi 
					inner join OChiSoMayMoc on OChiSoMayMoc.IOID = ONhatTrinhThietBi.Ref_ChiSo
					set ONhatTrinhThietBi.SoHoatDong = 1
					where ifnull(ONhatTrinhThietBi.SoHoatDong,0) != 0 and DongHo = "ON"');
			$this->_db->execute($sql);

			$sql = sprintf('create temporary table if not exists ONhatTrinhThietBi_tmp 
						SELECT
						 ONhatTrinhThietBi.*,DongHo
						from ONhatTrinhThietBi
						inner join OChiSoMayMoc on OChiSoMayMoc.IOID = ONhatTrinhThietBi.Ref_ChiSo
						where DongHo = "ON" or DongHo = "OFF" 
						order by Ref_MaTB,concat(Ngay," ",ThoiGian);');
			$this->_db->execute($sql);

			$sql = sprintf('update ONhatTrinhThietBi
						inner join OChiSoMayMoc on OChiSoMayMoc.IOID = ONhatTrinhThietBi.Ref_ChiSo
						set ONhatTrinhThietBi.SoHoatDong = time_to_sec(timediff(concat(Ngay," ",ThoiGian),
						(SELECT MAX(concat(ONhatTrinhThietBi_tmp.Ngay," ",ONhatTrinhThietBi_tmp.ThoiGian)) 
						FROM ONhatTrinhThietBi_tmp WHERE concat(ONhatTrinhThietBi_tmp.Ngay," ",ONhatTrinhThietBi_tmp.ThoiGian)  < concat(ONhatTrinhThietBi.Ngay," ",ONhatTrinhThietBi.ThoiGian)
						and ONhatTrinhThietBi_tmp.Ref_MaTB = ONhatTrinhThietBi.Ref_MaTB)
						))/3600
						where OChiSoMayMoc.DongHo = "OFF";');
            $this->_db->execute($sql);
		}
	}
	
}