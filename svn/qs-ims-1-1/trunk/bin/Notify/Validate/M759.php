<?php
class Qss_Bin_Notify_Validate_M759 extends Qss_Lib_Notify_Validate
{
    /**
     * Kiểm tra "Phiếu bảo trì"
     * 1. Kiểm tra phiếu bảo trì quá hạn chưa đóng.
     */
	public function __doExecute()
	{
		$sql = sprintf('update qsiforms 
					set Error = 0, ErrorMessage=NULL
					where FormCode = "M759"');
		$sql = sprintf('update qsiforms 
					inner join OPhieuBaoTri on OPhieuBaoTri.IFID_M759 = qsiforms.IFID
					set Error = 1, ErrorMessage=concat(%1$s,\': \',OPhieuBaoTri.SoPhieu),EDate=now()
					where Status < 4 and IFID in (select IFID_M759 from OPhieuBaoTri where NgayYeuCau < now()) ',
					$this->_db->quote('Phiếu bảo trì quá hạn'));
		$this->_db->execute($sql);
	}
}
?>