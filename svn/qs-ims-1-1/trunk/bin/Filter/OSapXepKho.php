<?php
class Qss_Bin_Filter_OSapXepKho extends Qss_Lib_Filter
{
	
	public function getWhere()
	{
		$retval = '';
		$makho = (int) @$this->_params['makho'];
		if($makho)
		{
			$retval = sprintf(' and v.Ref_Kho =%1$d ',$makho);
			//echo $retval;die;
		}
		return $retval;
	}
	
}