<?php
/**
 * Model for instanse form
 *
 */
class Qss_Bin_Tabs_M705_Tab1 extends Qss_Lib_Filter
{
	public $name = 'Đang hoạt động';
	
	public function getWhere()
	{
		$retval = '';
        $retval = sprintf(' and TrangThai not in (%1$s) ', implode(',', Qss_Lib_Extra_Const::$EQUIP_STATUS_STOP));
		return $retval;
	}
}
?>