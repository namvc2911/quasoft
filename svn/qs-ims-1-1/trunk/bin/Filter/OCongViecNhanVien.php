<?php
class Qss_Bin_Filter_OCongViecNhanVien extends Qss_Lib_Filter
{
    public function getWhere() {
        $modelCommon = new Qss_Model_Extra_Extra();
        $retval      = '';
        $shiftIOID   = (int) @$this->_params['nameShift'];
        $makhuvuc    = (int) @$this->_params['makhuvuc'];
        $date        = @$this->_params['nameDate']?Qss_Lib_Date::displaytomysql(@$this->_params['nameDate']):'';

        if($shiftIOID) {
            $objShift = $modelCommon->getTableFetchOne('OCa', array('IOID'=>$shiftIOID));

            $retval .= sprintf(' AND (v.ThoiGianBatDauDuKien BETWEEN %1$s AND %2$s )'
                , $this->_db->quote($objShift->GioBatDau)
                , $this->_db->quote($objShift->GioKetThuc));
        }

        if($date)
        {
            $retval .= sprintf(' AND (v.Ngay = %1$s)', $this->_db->quote($date));
        }

        if(Qss_Lib_System::formSecure('M720'))
        {
            $retval .= sprintf(' 
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
						WHERE OKhuVuc.lft >= (SELECT lft FROM OKhuVuc WHERE IOID = %1$d) 
						and OKhuVuc.rgt <= (SELECT rgt FROM OKhuVuc WHERE IOID = %1$d))'
                ,$makhuvuc);
            //echo $retval;die;
        }

        return $retval;
    }
}