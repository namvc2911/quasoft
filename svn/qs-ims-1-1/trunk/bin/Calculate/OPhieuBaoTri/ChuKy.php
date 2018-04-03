<?php
class Qss_Bin_Calculate_OPhieuBaoTri_ChuKy extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		/*$retval = '';
		$field =  $this->_object->getFieldByCode('ChuKy');
		$sql = sprintf('select * from OChuKyBaoTri as t 
					inner join OBaoTriDinhKy as t1 on t.IFID_M724 = t1.IFID_M724
					where t1.Ref_MaThietBi = %1$d and Ref_LoaiBaoTri = %2$d'
				,$this->_object->getFieldByCode('MaThietBi')->getRefIOID()
				,$this->_object->getFieldByCode('LoaiBaoTri')->getRefIOID());
		$dataSQL = $this->_db->fetchAll($sql);
		foreach ($dataSQL as $item)
		{
			$retval .= $item->ChuKy;
		}
		return $retval;*/
	}
}
?>