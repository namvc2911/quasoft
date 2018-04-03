<?php
/**
 * Model for instanse form
 *
 */
class Qss_Bin_Tabs_M759_Tab3 extends Qss_Lib_Filter
{
	public $name = 'Chờ duyệt';
	
	public function getWhere()
	{
		$retval = '';
		$retval = sprintf(' and qsiforms.Status = 3');
		return $retval;
	}
}
?>
