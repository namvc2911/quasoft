<?php
/**
 * Model for instanse form
 *
 */
class Qss_Bin_Tabs_M759_Tab1 extends Qss_Lib_Filter
{
	public $name = 'Chưa thực hiện';
	
	public function getWhere()
	{
		$retval = '';
		$retval = sprintf(' and qsiforms.Status = 1');
		return $retval;
	}
}
?>