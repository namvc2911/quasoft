<?php
class Qss_Bin_Notify_Validate_M506 extends Qss_Lib_Notify_Validate
{
    /**
     * Kiểm tra "Phiếu bảo trì"
     * 1. Kiểm tra phiếu bảo trì quá hạn chưa đóng.
     */
	public function __doExecute()
	{
		$sql = sprintf('update qsiforms 
					set Error = 0, ErrorMessage=NULL
					where FormCode = "M506"');
		$this->_db->execute($sql);
		$sql = sprintf('update qsiforms 
					inner join OXuatKho on OXuatKho.IFID_M506 = qsiforms.IFID
					set Error = 1, ErrorMessage=concat(%1$s,\': \',OXuatKho.SoChungTu),EDate=now()
					where Status = 0 and IFID in (select IFID_M506 from OXuatKho where NgayChungTu < now()) ',
					$this->_db->quote('Phiếu xuất kho quá hạn'));
		$this->_db->execute($sql);
	}
}
?>