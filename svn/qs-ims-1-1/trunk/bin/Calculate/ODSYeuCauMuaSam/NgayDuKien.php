<?php
class Qss_Bin_Calculate_ODSYeuCauMuaSam_NgayDuKien extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		if(!Qss_Bin_Calculate_ODSYeuCauMuaSam_DatHang::$_data)
		{
			//load data đơn hàng
			Qss_Bin_Calculate_ODSYeuCauMuaSam_DatHang::$_data = array();
			$sql = sprintf('select sum(ds.SoLuong) as SoLuong, max(ds.NgayGiaoHang) as NgayGiaoHang,dsyc.IOID as LineID from ODSDonMuaHang as ds
						inner join ODonMuaHang as dh on dh.IFID_M401 = ds.IFID_M401
						inner join OYeuCauMuaSam as yc on yc.IOID = ds.Ref_SoYeuCau
						inner join ODSYeuCauMuaSam as dsyc on yc.IFID_M412 = dsyc.IFID_M412 and dsyc.MaSP = ds.MaSP
						where yc.IFID_M412 = %1$d
						group by dsyc.IOID',$this->_object->i_IFID);
			$dataSQL = $this->_db->fetchAll($sql);
			foreach ($dataSQL as $item)
			{
				Qss_Bin_Calculate_ODSYeuCauMuaSam_DatHang::$_data[$item->LineID] = $item;
			}
		}
		if(isset(Qss_Bin_Calculate_ODSYeuCauMuaSam_DatHang::$_data[$this->_object->i_IOID]))
		{
			$date = Qss_Bin_Calculate_ODSYeuCauMuaSam_DatHang::$_data[$this->_object->i_IOID]->NgayGiaoHang;
			if($date)
			{
				return Qss_Lib_Date::mysqltodisplay($date);
			}
		}
	}
}
?>