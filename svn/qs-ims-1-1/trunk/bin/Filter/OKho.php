<?php
class Qss_Bin_Filter_OKho extends Qss_Lib_Filter
{

	public function getJoin()
	{
		$retval = '';
		if(Qss_Lib_System::formSecure('M601'))
		{
			$retval = sprintf('inner join ODanhSachKho on ODanhSachKho.IOID = v.Ref_Kho
						inner join qsrecordrights on ODanhSachKho.IFID_M601 = qsrecordrights.IFID');
		}
		return $retval;
	}

	public function getWhere()
	{
		$retval = '';
		$makho = (int) @$this->_params['makho'];
		if($makho)
		{
			$retval = sprintf(' and v.Ref_Kho =%1$d ',$makho);
			//echo $retval;die;
		}

		if(Qss_Lib_System::formSecure('M601'))
		{
			$retval .= sprintf(' and qsrecordrights.UID = %1$d'
			,$this->_user->user_id);
		}
		return $retval;
	}

}