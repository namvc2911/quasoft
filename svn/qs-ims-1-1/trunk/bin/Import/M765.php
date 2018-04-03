<?php
class Qss_Bin_Import_M765 extends Qss_Lib_Bin
{
	public function __doExecute()
	{
		$ifid = $this->_form->i_IFID;
		if(!$ifid)
		{
		    // Cập nhật hết giá trị của loại mở máy có sốt hoạt động khác 0 về 1
			$sql = sprintf('
                update ONhatTrinhThietBi 
                inner join OChiSoMayMoc on OChiSoMayMoc.IOID = ONhatTrinhThietBi.Ref_ChiSo
                set ONhatTrinhThietBi.SoHoatDong = 1
                where ifnull(ONhatTrinhThietBi.SoHoatDong,0) != 0 and DongHo = "ON" and ONhatTrinhThietBi.Ngay >= %1$s',
				date('Y-m-d', strtotime('-1 months')));
			$this->_db->execute($sql);

			$sql = sprintf('create temporary table if not exists ONhatTrinhThietBi_tmp 
						SELECT
						 ONhatTrinhThietBi.*,DongHo
						from ONhatTrinhThietBi
						inner join OChiSoMayMoc on OChiSoMayMoc.IOID = ONhatTrinhThietBi.Ref_ChiSo
						where DongHo = "ON" or DongHo = "OFF" and ONhatTrinhThietBi.Ngay >= %1$s
						order by Ref_MaTB,concat(Ngay," ",ThoiGian);',
				date('Y-m-d', strtotime('-1 months')));
			$this->_db->execute($sql);

			$sql = sprintf('update ONhatTrinhThietBi
						inner join OChiSoMayMoc on OChiSoMayMoc.IOID = ONhatTrinhThietBi.Ref_ChiSo
						set ONhatTrinhThietBi.SoHoatDong = time_to_sec(
						timediff(concat(Ngay," ",ThoiGian),
						
						(SELECT 
						  CASE WHEN (MAX(concat(ONhatTrinhThietBi_tmp.Ngay," ",ONhatTrinhThietBi_tmp.ThoiGian)) < concat(ODanhSachDiemDo.NgayReset," ",ODanhSachDiemDo.GioReset))
						  THEN concat(ODanhSachDiemDo.NgayReset," ",ODanhSachDiemDo.GioReset)
						  ELSE MAX(concat(ONhatTrinhThietBi_tmp.Ngay," ",ONhatTrinhThietBi_tmp.ThoiGian)) END 
						 
						FROM ONhatTrinhThietBi_tmp 
						INNER JOIN OChiSoMayMoc ON OChiSoMayMoc.IOID = ONhatTrinhThietBi_tmp.Ref_ChiSo
						INNER JOIN ODanhSachDiemDo ON ONhatTrinhThietBi_tmp.Ref_DiemDo = ODanhSachDiemDo.IOID
						WHERE 
						ONhatTrinhThietBi.Ngay >= %1$s and
						concat(ONhatTrinhThietBi_tmp.Ngay," ",ONhatTrinhThietBi_tmp.ThoiGian)  < concat(ONhatTrinhThietBi.Ngay," ",ONhatTrinhThietBi.ThoiGian)
						and ONhatTrinhThietBi_tmp.Ref_MaTB = ONhatTrinhThietBi.Ref_MaTB
						and OChiSoMayMoc.DongHo = "ON"
						and ONhatTrinhThietBi_tmp.Ngay >= ODanhSachDiemDo.NgayReset)
						))/3600
						where OChiSoMayMoc.DongHo = "OFF";',
				date('Y-m-d', strtotime('-1 months')));
            $this->_db->execute($sql);
		}
	}
	
}