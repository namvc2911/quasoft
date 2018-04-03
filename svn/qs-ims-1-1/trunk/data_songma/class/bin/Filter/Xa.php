<?php
class Qss_Bin_Filter_Xa extends Qss_Lib_Filter
{
	
	public function Huyen()
	{
		$tinh = $this->_params['Xa_Tinh'];
		$retval = sprintf(' đand Ref_Tinh in (select IOID from Tinh where IOID = %1$d)',$tinh);
		return $retval;
	}
	
}