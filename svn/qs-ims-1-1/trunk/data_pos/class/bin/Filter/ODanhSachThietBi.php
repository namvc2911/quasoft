<?php
class Qss_Bin_Filter_ODanhSachThietBi extends Qss_Lib_Filter
{
	public function getJoin()
	{
		return '';
		$retval = '';
		$rights = Qss_Lib_System::getFormRights('M705', $this->_user->user_group_list);
		if($rights && !($rights & 2))
		{
			//select 
			$retval = sprintf(' 
					inner join OKhuVuc on OKhuVuc.IOID = v.Ref_MaKhuVuc
					inner join (SELECT t1.Ref_Ma,t1.IFID_M125,t2.* FROM OThietBi as t1 left join OKhuVuc as t2 
					on t2.IOID=t1.Ref_Ma) as TB on (TB.lft <= OKhuVuc.lft and TB.rgt >= OKhuVuc.rgt) or TB.Ref_Ma=v.IOID 
					inner join ONhanVien on ONhanVien.IFID_M125 = TB.IFID_M125 
					inner join ODanhSachNhanVien on ODanhSachNhanVien.IOID =  ONhanVien.Ref_MaNV
					');
		}
		return $retval;
	}
	public function getWhere()
	{
		$retval = '';


		// Lọc thiết bị theo dự án
        $duan = (int) @$this->_params['maduan'];

        if($duan) {
            $retval .= sprintf(' and v.Ref_DuAn = %1$d ',$duan);
        }


        // Lọc thiết bị theo khu vực
        $makhuvuc = (int) @$this->_params['makhuvuc'];
		if($makhuvuc)
		{
			$retval .= sprintf(' and v.Ref_MaKhuVuc in (SELECT IOID FROM OKhuVuc
						WHERE OKhuVuc.lft >= (SELECT lft FROM OKhuVuc WHERE IOID=%1$d) 
						and OKhuVuc.rgt <= (SELECT rgt FROM OKhuVuc WHERE IOID = %1$d))'
				,$makhuvuc);
		}


		if(isset($this->_params['trangthaithietbi']))
        {
            $tinhTrang = (int)$this->_params['trangthaithietbi'];
        }
        else
        {
            $tinhTrang = -2;
        }

        if($tinhTrang >= 0)
        {
            $retval .= sprintf(' and IFNULL(v.TrangThai, 0) = %1$d ',$tinhTrang);
        }

        $whereofall = '';
        if(Qss_Lib_System::formSecure('M720'))
        {
       	 	$whereofall = sprintf(' (v.Ref_MaKhuVuc in (SELECT IOID FROM OKhuVuc
						inner join qsrecordrights on OKhuVuc.IFID_M720 = qsrecordrights.IFID 
						WHERE UID = %1$d))'
				,$this->_user->user_id);
        }
        if(Qss_Lib_System::formSecure('M803') && Qss_Lib_System::fieldActive('ODanhSachThietBi', 'DuAn'))
        {
        	if($whereofall != '')
        	{
        		$whereofall .= ' or ';
        	}
        	$whereofall .= sprintf(' (v.Ref_DuAn in (SELECT IOID FROM ODuAn
						inner join qsrecordrights on ODuAn.IFID_M803 = qsrecordrights.IFID 
						WHERE UID = %1$d))'
				,$this->_user->user_id);
        }
        if($whereofall != '')
        {
        	$retval .= sprintf(' and (%1$s)',$whereofall);
        }



		return $retval;
	}
	public function getRights($ifid)
	{
		$retval = 63;
		if(Qss_Lib_System::formSecure('M720'))
        {
            $sql = sprintf('select case when ifnull(Ref_MaKhuVuc,0)=0 then 63 else Rights end as Rights,ODanhSachThietBi.Loai from 
            			ODanhSachThietBi
            			left join OKhuVuc on ODanhSachThietBi.Ref_MaKhuVuc = OKhuVuc.IOID
            			left join qsrecordrights on OKhuVuc.IFID_M720 = qsrecordrights.IFID and qsrecordrights.UID = %1$d 
            			WHERE IFID_M705 = %2$d'
                ,$this->_user->user_id
                ,$ifid);
            $dataSQL = $this->_db->fetchOne($sql);
           	$retval = $dataSQL?(($dataSQL->Loai == 3)?$dataSQL->Rights:0):0;
        }
        if(!$retval 
        	&& Qss_Lib_System::formSecure('M803') 
        	&& Qss_Lib_System::fieldActive('ODanhSachThietBi', 'DuAn'))
        {
        	$sql = sprintf('select Rights,ODanhSachThietBi.Loai from 
            			ODanhSachThietBi
            			left join ODuAn on ODanhSachThietBi.Ref_DuAn = ODuAn.IOID
            			left join qsrecordrights on ODuAn.IFID_M803 = qsrecordrights.IFID and qsrecordrights.UID = %1$d 
            			WHERE IFID_M705 = %2$d'
                ,$this->_user->user_id
                ,$ifid);
            $dataSQL = $this->_db->fetchOne($sql);
           	$retval = $dataSQL?(($dataSQL->Loai == 3)?2:$dataSQL->Rights):63;
        }
        return $retval;
	}
}