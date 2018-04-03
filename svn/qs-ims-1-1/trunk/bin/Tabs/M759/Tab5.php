<?php
/**
 * Model for instanse form
 *
 */
class Qss_Bin_Tabs_M759_Tab5 extends Qss_Lib_Filter
{
	public $name = 'Hủy';
	
	public function getWhere()
	{
		$retval = '';
		$retval = sprintf(' and qsiforms.Status = 5');
		return $retval;
	}
}
?>