<?php
/**
 * Model for instanse form
 *
 */
class Qss_Bin_Tabs_M759_Tab4 extends Qss_Lib_Filter
{
	public $name = 'Đã đóng';
	
	public function getWhere()
	{
		$retval = '';
		$retval = sprintf(' and qsiforms.Status = 4');
		return $retval;
	}
}
?>