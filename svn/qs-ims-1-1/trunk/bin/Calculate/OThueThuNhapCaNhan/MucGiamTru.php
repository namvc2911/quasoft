
<?php
class Qss_Bin_Calculate_OThueThuNhapCaNhan_MucGiamTru extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		//@warning: Chưa chắc đã chạy!
		$sql = sprintf('select * from ODanhSachNhanVien as dsnv
						left join OQuanHeGiaDinh as qhgd
						on dsnv.IFID_M316 = qhgd.IFID_M316
						where dsnv.IOID = %1$d', $this->_object->getFieldByCode('MaNhanVien')->intRefIOID);
		$relationship = $this->_db->fetchAll($sql);
		$SYSTEM_PARAMS  = new Qss_Model_System_Param;
		$SYSTEM_PARAMS  = $SYSTEM_PARAMS->getById('GTPT');
		$count          =  0;
		foreach ($relationship as $val)
		{
			if($val->GiamTruPhuThuoc == 1)
			{
				$count++;
			}
		}
		return $count * $SYSTEM_PARAMS->Value;		
	}
}
?>