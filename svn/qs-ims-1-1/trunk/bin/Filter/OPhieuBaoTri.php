<?php
class Qss_Bin_Filter_OPhieuBaoTri extends Qss_Lib_Filter
{
	public function getJoin()
	{
		$retval = '';
		if(Qss_Lib_System::formSecure('M720'))
        {
			//select 
			$retval = sprintf('
                left join ODanhSachThietBi on v.Ref_MaThietBi = ODanhSachThietBi.IOID
				left join OKhuVuc AS KhuVucTheoThietBi ON KhuVucTheoThietBi.IOID = ODanhSachThietBi.Ref_MaKhuVuc
				left join OKhuVuc AS KhuVucTheoMaKhuVuc ON KhuVucTheoMaKhuVuc.IOID = v.Ref_MaKhuVuc
            ');
		}
		return $retval;
	}
	public function getWhere()
	{
		$makhuvuc   = (int) @$this->_params['makhuvuc'];
		$vaitro     = (int) @$this->_params['vaitro'];
        $employee   = (int) @$this->_params['employee'];
        $loaiBaoTri = (int) @$this->_params['maintaintype'];
        $workcenter = (int) @$this->_params['workcenter'];
		$retval     = '';

//        if(Qss_Lib_System::formSecure('M720'))
//        {
//            if(Qss_Lib_System::fieldActive('ODanhSachThietBi', 'MaTam')) {
//                $retval = sprintf('
//			    AND
//			    (
//			        IFNULL(v.Ref_MaThietBi, 0) IN (
//                        SELECT ODanhSachThietBi.IOID
//                        FROM ODanhSachThietBi
//                        INNER JOIN OKhuVuc AS KhuVucThietBi ON IFNULL(ODanhSachThietBi.Ref_MaKhuVuc, 0) = KhuVucThietBi.IOID
//                        INNER JOIN OKhuVuc AS KhuVucCha ON KhuVucThietBi.lft >=  KhuVucCha.lft AND KhuVucThietBi.rgt <= KhuVucCha.rgt
//                        INNER JOIN qsrecordrights on KhuVucCha.IFID_M720 = qsrecordrights.IFID
//                        WHERE UID = %1$d
//			        )
//			        OR IFNULL(v.Ref_MaThietBi, 0) = 0
//			        OR IFNULL(ODanhSachThietBi.MaTam, 0) = 1
//			        /*
//			        OR
//			        (
//			            IFNULL(v.Ref_MaKhuVuc, 0) IN (
//			                SELECT KhuVucCha.IOID
//			                FROM OKhuVuc AS KhuVucHienTai
//			                INNER JOIN OKhuVuc AS KhuVucCha ON KhuVucHienTai.lft >= KhuVucCha.lft AND KhuVucHienTai.rgt <= KhuVucCha.rgt
//			                INNER JOIN qsrecordrights on KhuVucCha.IFID_M720 = qsrecordrights.IFID
//			                WHERE UID = %1$d AND KhuVucHienTai.IOID = %2$d
//			            )
//			        )
//                    */
//                )',$this->_user->user_id, $makhuvuc);
//            }
//            else {
//                $retval = sprintf('
//			    AND
//			    (
//			        IFNULL(v.Ref_MaThietBi, 0) IN (
//                        SELECT ODanhSachThietBi.IOID
//                        FROM ODanhSachThietBi
//                        INNER JOIN OKhuVuc AS KhuVucThietBi ON IFNULL(ODanhSachThietBi.Ref_MaKhuVuc, 0) = KhuVucThietBi.IOID
//                        INNER JOIN OKhuVuc AS KhuVucCha ON KhuVucThietBi.lft >=  KhuVucCha.lft AND KhuVucThietBi.rgt <= KhuVucCha.rgt
//                        INNER JOIN qsrecordrights on KhuVucCha.IFID_M720 = qsrecordrights.IFID
//                        WHERE UID = %1$d
//			        )
//			        OR IFNULL(v.Ref_MaThietBi, 0) = 0
//			        /*
//			        OR
//			        (
//			            IFNULL(v.Ref_MaKhuVuc, 0) IN (
//			                SELECT KhuVucCha.IOID
//			                FROM OKhuVuc AS KhuVucHienTai
//			                INNER JOIN OKhuVuc AS KhuVucCha ON KhuVucHienTai.lft >= KhuVucCha.lft AND KhuVucHienTai.rgt <= KhuVucCha.rgt
//			                INNER JOIN qsrecordrights on KhuVucCha.IFID_M720 = qsrecordrights.IFID
//			                WHERE UID = %1$d AND KhuVucHienTai.IOID = %2$d
//			            )
//			        )
//                    */
//                )',$this->_user->user_id, $makhuvuc);
//            }
//
//        }

        // echo '<pre>'; print_r($this->_params); die;

		
        if(Qss_Lib_System::formSecure('M720')) {
            $retval = sprintf(' 
                AND 
                (   
                    (
                        KhuVucTheoThietBi.IOID IN (
                            SELECT KhuVucThietBi.IOID
                            FROM  OKhuVuc AS KhuVucThietBi                        			 
                            INNER JOIN qsrecordrights on KhuVucThietBi.IFID_M720 = qsrecordrights.IFID 
                            WHERE qsrecordrights.UID = %1$d 
                        )
                        OR IFNULL(KhuVucTheoThietBi.IOID, 0) = 0
                    )
                    AND
                    (
                        KhuVucTheoMaKhuVuc.IOID IN (
                            SELECT KhuVucThietBi.IOID
                            FROM  OKhuVuc AS KhuVucThietBi                        			 
                            INNER JOIN qsrecordrights on KhuVucThietBi.IFID_M720 = qsrecordrights.IFID 
                            WHERE qsrecordrights.UID = %1$d 
                        )
                        OR IFNULL(KhuVucTheoMaKhuVuc.IOID, 0) = 0                    
                    )
                )',$this->_user->user_id);
        }

        if($workcenter) {
            $retval .= sprintf(' AND IFNULL(v.Ref_MaDVBT,0)= %1$d ', $workcenter);
        }

		if(Qss_Lib_System::formSecure('M125')
            && Qss_Lib_System::fieldActive('OPhieuBaoTri', 'MaDVBT'))
		{
			$retval .= sprintf(' 
			    and (
			        IFNULL(v.Ref_MaDVBT,0)=0 
			        or 
			        v.Ref_MaDVBT in 
			        (
			            SELECT IOID FROM ODonViSanXuat
						INNER JOIN qsrecordrights ON ODonViSanXuat.IFID_M125 = qsrecordrights.IFID 
						WHERE UID = %1$d
                    )
                )'
				,$this->_user->user_id);
		}

        /*if(Qss_Lib_System::formSecure('M708'))
        {
            $retval .= sprintf(' 
			        and IFNULL(v.Ref_LoaiBaoTri, 0) IN (
                        SELECT IOID
                        FROM OPhanLoaiBaoTri 
                        INNER JOIN qsrecordrights on OPhanLoaiBaoTri.IFID_M708 = qsrecordrights.IFID 
                        WHERE UID = %1$d)
			',$this->_user->user_id);
        }*/

        if($loaiBaoTri) {
            $retval .= sprintf(' and Ref_LoaiBaoTri = %1$d',$loaiBaoTri);
        }

		if($makhuvuc) {
            $retval .= sprintf(' 
                    and 
                    (
                        (
                            KhuVucTheoThietBi.lft >= (SELECT lft FROM OKhuVuc WHERE IOID = %1$d) 
                            and KhuVucTheoThietBi.rgt <= (SELECT rgt FROM OKhuVuc WHERE IOID = %1$d)
                        )
                        OR 
                        (
                            KhuVucTheoMaKhuVuc.lft >= (SELECT lft FROM OKhuVuc WHERE IOID = %1$d) 
                            and KhuVucTheoMaKhuVuc.rgt <= (SELECT rgt FROM OKhuVuc WHERE IOID = %1$d)           
                        )

                    )',$makhuvuc);

			//echo $retval;die;
		}

		if($vaitro == 1)
		{
			$retval .= sprintf(' and v.Ref_NguoiThucHien in (SELECT ODanhSachNhanVien.IOID FROM ODanhSachNhanVien
						WHERE Ref_TenTruyCap = %1$d)'
					,$this->_user->user_id);
			//echo $retval;die;
		}
		elseif($vaitro == 2)
		{
			$retval .= sprintf(' and v.Ref_NguoiNghiemThu in (SELECT ODanhSachNhanVien.IOID FROM ODanhSachNhanVien
						WHERE Ref_TenTruyCap = %1$d)'
					,$this->_user->user_id);
			//echo $retval;die;
		}

		/*$rights = Qss_Lib_System::getFormRights('M759', $this->_user->user_group_list);
		if($rights && !($rights & 2))
		{
			//select 
			$retval .= sprintf(' and ODanhSachNhanVien.Ref_TenTruyCap = %1$d',$this->_user->user_id);
		}*/

		// echo '<pre>'; print_r($retval); die;


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

    public function getRights($ifid)
    {
        $retval = 63;
        if(Qss_Lib_System::formSecure('M720'))
        {
        	$sql = sprintf('select case when ifnull(ODanhSachThietBi.Ref_MaKhuVuc,0)=0 and ifnull(OPhieuBaoTri.Ref_MaKhuVuc,0)=0 then 63 else Rights end as Rights from 
            			OPhieuBaoTri
            			left join ODanhSachThietBi on  OPhieuBaoTri.Ref_MaThietBi = ODanhSachThietBi.IOID
            			left join OKhuVuc on ODanhSachThietBi.Ref_MaKhuVuc = OKhuVuc.IOID or OPhieuBaoTri.Ref_MaKhuVuc = OKhuVuc.IOID
            			left join qsrecordrights on OKhuVuc.IFID_M720 = qsrecordrights.IFID and qsrecordrights.UID = %1$d 
            			WHERE IFID_M759 = %2$d'
                ,$this->_user->user_id
                ,$ifid);
            $dataSQL = $this->_db->fetchOne($sql);
           	$retval = $dataSQL?$dataSQL->Rights:63;
        }
        return $retval;
    }
}