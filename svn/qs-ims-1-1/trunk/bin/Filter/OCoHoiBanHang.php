<?php
class Qss_Bin_Filter_OCoHoiBanHang extends Qss_Lib_Filter
{
	public function getWhere()
	{
		$retval = '';
		if(Qss_Lib_System::formSecure('M848'))
		{
			$retval = sprintf(' and v.Ref_KhuVuc in (SELECT IOID FROM OKhuVucBanHang
						inner join qsrecordrights on OKhuVucBanHang.IFID_M848 = qsrecordrights.IFID 
						WHERE UID = %1$d)'
				,$this->_user->user_id);
		}
		$khuvuc = (int) @$this->_params['khuvucbanhang'];


		if($khuvuc != 0)
        {
            $retval .= sprintf(' and IFNULL(v.Ref_KhuVuc, 0) = %1$d'
                    ,$khuvuc);
        }
		return $retval;
	}
	public function getRights($ifid)
	{
		$retval = 63;
		if(Qss_Lib_System::formSecure('M848'))
        {
            $sql = sprintf('select 63 as rights from OCoHoiBanHang where Ref_KhuVuc in (SELECT IOID FROM OKhuVucBanHang
						inner join qsrecordrights on OKhuVucBanHang.IFID_M848 = qsrecordrights.IFID 
						WHERE UID = %1$d) and IFID_M504 = %2$d'
                ,$this->_user->user_id
                ,$ifid);
            $dataSQL = $this->_db->fetchOne($sql);
           	$retval = $dataSQL?$dataSQL->rights:0;
        }
        return $retval;
	}
}