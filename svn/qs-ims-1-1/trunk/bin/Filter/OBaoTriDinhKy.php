<?php
class Qss_Bin_Filter_OBaoTriDinhKy extends Qss_Lib_Filter
{
	public function getWhere()
	{
		$retval = '';
		/*$rights = Qss_Lib_System::getFormRights('M705', $this->_user->user_group_list);
		$arrLocation = array();
		if($rights && !($rights & 2))
		{
			$arrLocation = array(0);
			$locationModel = new Qss_Model_Maintenance_Employee();
			$locations = $locationModel->getLocationByCurrentUser($this->_user->user_id);
			foreach ($locations as $item)
			{
				$arrLocation[] = $item->Ref_Ma;
			}
		}
		if($makhuvuc)
		{
			if(count($arrLocation))
			{
				$arrLocation = array_intersect($arrLocation,array(0,$makhuvuc));	
			}
			else 
			{
				$arrLocation[] = $makhuvuc; 
			}
		}*/
		$makhuvuc = (int) @$this->_params['makhuvuc'];
//		$retval = sprintf(' and
//						v.Ref_MaThietBi in (SELECT ODanhSachThietBi.IOID FROM
//						ODanhSachThietBi
//						inner join OKhuVuc on ODanhSachThietBi.Ref_MaKhuVuc = OKhuVuc.IOID
//						inner join qsrecordrights on OKhuVuc.IFID_M720 = qsrecordrights.IFID
//						WHERE UID = %1$d)'
//				,$this->_user->user_id);

        if(Qss_Lib_System::formSecure('M720'))
        {
            $retval = sprintf(' 
			    AND 
			    (   
			        IFNULL(v.Ref_MaThietBi, 0) IN (
                        SELECT ODanhSachThietBi.IOID
                        FROM ODanhSachThietBi 
                        INNER JOIN OKhuVuc AS KhuVucThietBi ON IFNULL(ODanhSachThietBi.Ref_MaKhuVuc, 0) = KhuVucThietBi.IOID
                        INNER JOIN OKhuVuc AS KhuVucCha ON KhuVucThietBi.lft >=  KhuVucCha.lft AND KhuVucThietBi.rgt <= KhuVucCha.rgt			 
                        INNER JOIN qsrecordrights on KhuVucCha.IFID_M720 = qsrecordrights.IFID 
                        WHERE UID = %1$d
			        )
			        OR IFNULL(v.Ref_MaThietBi, 0) = 0
			        /*
			        OR
			        (
			            IFNULL(v.Ref_MaKhuVuc, 0) IN (
			                SELECT KhuVucCha.IOID 
			                FROM OKhuVuc AS KhuVucHienTai
			                INNER JOIN OKhuVuc AS KhuVucCha ON KhuVucHienTai.lft >= KhuVucCha.lft AND KhuVucHienTai.rgt <= KhuVucCha.rgt
			                INNER JOIN qsrecordrights on KhuVucCha.IFID_M720 = qsrecordrights.IFID
			                WHERE UID = %1$d AND KhuVucHienTai.IOID = %2$d
			            )
			        )
                    */
                )
			',$this->_user->user_id, $makhuvuc);
        }


		if($makhuvuc)
		{
			$retval .= sprintf(' and v.Ref_MaThietBi in (SELECT ODanhSachThietBi.IOID FROM ODanhSachThietBi
						inner join OKhuVuc on OKhuVuc.IOID = ODanhSachThietBi.Ref_MaKhuVuc
						WHERE OKhuVuc.lft >= (SELECT lft FROM OKhuVuc WHERE IOID in (%1$s)) 
						and OKhuVuc.rgt <= (SELECT rgt FROM OKhuVuc WHERE IOID = %1$d))'
				,$makhuvuc);
			//echo $retval;die;
		}
		return $retval;
	}
}