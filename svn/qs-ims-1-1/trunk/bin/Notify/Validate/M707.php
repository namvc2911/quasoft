<?php
class Qss_Bin_Notify_Validate_M707 extends Qss_Lib_Notify_Validate
{
    /**
     * Kiểm tra "Phiếu bảo trì"
     * 1. Kiểm tra phiếu bảo trì quá hạn chưa đóng.
     */
	public function __doExecute()
	{
		$sql = sprintf('update qsiforms 
					set Error = 0, ErrorMessage=NULL
					where FormCode = "M707"');
		$this->_db->execute($sql);
		$sql = sprintf('update qsiforms 
					set Error = 1, ErrorMessage=%1$s
					where Status in (1) and FormCode = "M707"',
					$this->_db->quote('Sự cố thiết bị'));
		$this->_db->execute($sql);
	}
}
?>