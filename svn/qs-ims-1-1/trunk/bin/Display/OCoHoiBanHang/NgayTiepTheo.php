<?php
class Qss_Bin_Display_OCoHoiBanHang_NgayTiepTheo extends Qss_Lib_Style
{
	public function __doExecute()
	{
		/* Get newest transaction percent */
		$retval  = 0;
		$table = Qss_Model_Db::Table('OGiaoDich');
		$table->where(sprintf('IFID_M504=%1$d',$this->_data->IFID_M504));
		$table->orderby('NgayKetThuc desc, IOID desc limit 1');
		$item     = $table->fetchOne();
		$retval  = @$item->NgayKetThuc;
		$date = Qss_Lib_Date::mysqltodisplay($retval);
		return $date?$date:'N/A';
	}
}
?>