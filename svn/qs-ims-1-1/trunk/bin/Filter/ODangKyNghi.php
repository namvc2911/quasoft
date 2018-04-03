<?php
class Qss_Bin_Filter_ODangKyNghi extends Qss_Lib_Filter {
    public function getWhere() {
        $retval = '';
        $iPhongBan = (int) @$this->_params['phongban'];

        if($iPhongBan) {
            $retval .= sprintf(' 
                and v.PhongBanHienTai IN  (
                    SELECT OPhongBan.MaPhongBan
                    FROM OPhongBan                        
                    WHERE OPhongBan.lft >= (SELECT lft FROM OPhongBan WHERE IOID = %1$d)
                        AND OPhongBan.rgt <= (SELECT rgt FROM OPhongBan WHERE IOID = %1$d)
                )
            ', $iPhongBan);
        }

        if(Qss_Lib_System::formSecure('M319')) {
            $retval .= sprintf(' 
			    and (
			        IFNULL(v.PhongBanHienTai, "") = ""  
			        or 
			        v.PhongBanHienTai in (
			            SELECT OPhongBan.MaPhongBan 
			            FROM OPhongBan
						INNER JOIN qsrecordrights ON OPhongBan.IFID_M319 = qsrecordrights.IFID 
						WHERE UID = %1$d
                    )
                )'
                ,$this->_user->user_id);
        }
        return $retval;
    }

    public function getRights($ifid) {
        $retval = 63;

        if(Qss_Lib_System::formSecure('M319')) {
            $sql = sprintf('
                select case when ifnull(PhongBanHienTai,"")="" then 63 else Rights end as Rights 
                from ODangKyNghi
                left join OPhongBan on ODangKyNghi.PhongBanHienTai = OPhongBan.MaPhongBan
                left join qsrecordrights on OPhongBan.IFID_M319 = qsrecordrights.IFID and qsrecordrights.UID = %1$d 
                WHERE ODangKyNghi.IFID_M077 = %2$d'
                ,$this->_user->user_id
                ,$ifid);

            $dataSQL = $this->_db->fetchOne($sql);
            $retval  = $dataSQL?$dataSQL->Rights:63;
        }
        return $retval;
    }
}