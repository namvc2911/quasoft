<?php
class Qss_Bin_Calculate_ODSYeuCauMuaSam_NhanHang extends Qss_Lib_Calculate
{
	public static $_data;
	
	public function __doExecute()
	{
		if(!self::$_data)
		{
			//load data đơn hàng
			self::$_data = array();
			$sql = sprintf('select ds.*,dh.*,dsyc.IOID as LineID from ODanhSachNhanHang as ds
						inner join ONhanHang as dh on dh.IFID_M408 = ds.IFID_M408
						inner join OYeuCauMuaSam as yc on yc.IOID = ds.Ref_SoYeuCau
						inner join ODSYeuCauMuaSam as dsyc on yc.IFID_M412 = dsyc.IFID_M412 and dsyc.MaSP = ds.MaMatHang
						where yc.IFID_M412 = %1$d',$this->_object->i_IFID);
			$dataSQL = $this->_db->fetchAll($sql);
			foreach ($dataSQL as $item)
			{
				self::$_data[$item->LineID] = $item;
			}
		}
		if(isset(self::$_data[$this->_object->i_IOID]))
		{
			return self::$_data[$this->_object->i_IOID]->SoLuong;
		}
	}
}
?>