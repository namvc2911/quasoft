<?php
/**
 * Model for instanse form
 *
 */
class Qss_Bin_Tabs_M705_Tab2 extends Qss_Lib_Filter
{
	public $name = 'Ngừng hoạt động';
	
	public function getWhere()
	{
		$retval = '';
		$retval = sprintf(' and TrangThai in (%1$s) ', implode(',', Qss_Lib_Extra_Const::$EQUIP_STATUS_STOP));
		return $retval;
	}
}
?>