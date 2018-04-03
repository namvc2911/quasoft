<?php
/**
 * Model for instanse form
 *
 */
class Qss_Bin_Tabs_M759_Tab2 extends Qss_Lib_Filter
{
	public $name = 'Đang thực hiện';
	
	public function getWhere()
	{
		$retval = '';
		$retval = sprintf(' and qsiforms.Status = 2');
		return $retval;
	}
}
?>