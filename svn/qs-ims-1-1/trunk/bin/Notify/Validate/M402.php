<?php
class Qss_Bin_Notify_Validate_M402 extends Qss_Lib_Notify_Validate
{
    /**
     * Kiểm tra "Phiếu bảo trì"
     * 1. Kiểm tra phiếu bảo trì quá hạn chưa đóng.
     */
	public function __doExecute()
	{
		$sql = sprintf('update qsiforms 
					set Error = 0, ErrorMessage=NULL
					where FormCode = "M402"');
		$this->_db->execute($sql);
		$sql = sprintf('update qsiforms 
					inner join ONhapKho on ONhapKho.IFID_M402 = qsiforms.IFID
					set Error = 1, ErrorMessage=concat(%1$s,\': \',ONhapKho.SoChungTu),EDate=now()
					where Status = 0 and IFID in (select IFID_M402 from ONhapKho where NgayChungTu < now()) ',
					$this->_db->quote('Phiếu nhập kho quá hạn'));
		$this->_db->execute($sql);
	}
}
?>