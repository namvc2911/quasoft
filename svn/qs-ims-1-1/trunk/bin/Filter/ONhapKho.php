<?php
class Qss_Bin_Filter_ONhapKho extends Qss_Lib_Filter
{
	public function getWhere()
	{
		$retval = '';
		$makho = (int) @$this->_params['makho'];
        if(Qss_Lib_System::formSecure('M601'))
        {
            $retval = sprintf(' and v.Ref_Kho in (SELECT IOID FROM ODanhSachKho
						inner join qsrecordrights on ODanhSachKho.IFID_M601 = qsrecordrights.IFID
						WHERE UID = %1$d)'
                ,$this->_user->user_id);
        }

		if($makho)
		{
			$retval .= sprintf(' and v.Ref_Kho =%1$d ',$makho);
			//echo $retval;die;
		}
		return $retval;
	}
	public function getRights($ifid)
	{
		$retval = 63;
		if(Qss_Lib_System::formSecure('M601'))
        {
            $sql = sprintf('select 63 as rights from  ONhapKho where Ref_Kho in (SELECT IOID FROM ODanhSachKho
						inner join qsrecordrights on ODanhSachKho.IFID_M601 = qsrecordrights.IFID
						WHERE UID = %1$d) or ifnull(Ref_Kho,0)=0 and IFID_M402 = %2$d'
                ,$this->_user->user_id
                ,$ifid);
            $dataSQL = $this->_db->fetchOne($sql);
           	$retval = $dataSQL?$dataSQL->rights:0;
        }
        return $retval;
	}
}