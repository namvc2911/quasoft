<?php
class Qss_Bin_Filter_OTheoDoiDuAn extends Qss_Lib_Filter
{
	public function getWhere()
	{
		$retval = '';

		$maduan   = (int) @$this->_params['maduan'];
        $employee = (int) @$this->_params['employee'];

		if(Qss_Lib_System::formSecure('M803'))
		{
			$retval = sprintf(' and v.Ref_DuAn in (SELECT IOID FROM ODuAn
						inner join qsrecordrights on ODuAn.IFID_M803 = qsrecordrights.IFID 
						WHERE UID = %1$d)'
				,$this->_user->user_id);
		}

		if($maduan)
		{
			$retval .= sprintf(' and v.Ref_DuAn = %1$d'
					,$maduan);
		}

		if($employee != 0)
        {
            if($employee == -1)
            {
                $retval .= sprintf(' and IFNULL(v.Ref_NguoiThucHien, 0) = 0'
                    ,$employee);
            }
            else
            {
                $retval .= sprintf(' and IFNULL(v.Ref_NguoiThucHien, 0) = %1$d'
                    ,$employee);
            }

        }

		return $retval;
	}
}