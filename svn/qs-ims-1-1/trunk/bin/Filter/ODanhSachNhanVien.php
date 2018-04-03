<?php
class Qss_Bin_Filter_ODanhSachNhanVien extends Qss_Lib_Filter {
    public function getWhere() {
        $retval = '';
        $iPhongBan = (int) @$this->_params['phongban'];

        if($iPhongBan) {
            if(Qss_Lib_System::fieldExists('OPhongBan', 'lft')) {
                $retval .= sprintf(' 
                and v.Ref_MaPhongBan IN  (
                    SELECT IOID
                    FROM OPhongBan 
                    WHERE lft >= (SELECT lft FROM OPhongBan WHERE IOID = %1$d)
                        AND rgt <= (SELECT rgt FROM OPhongBan WHERE IOID = %1$d)
                )
            ', $iPhongBan);
            }
            else {
                $retval .= sprintf(' and v.Ref_MaPhongBan = %1$d ', $iPhongBan);
            }
        }

        if(Qss_Lib_System::formSecure('M319')) {
            $retval .= sprintf(' 
			    and (
			        IFNULL(v.Ref_MaPhongBan,0)=0 
			        or 
			        v.Ref_MaPhongBan in 
			        (
			            SELECT IOID FROM OPhongBan
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
                select case when ifnull(Ref_MaPhongBan,0)=0 then 63 else Rights end as Rights 
                from ODanhSachNhanVien
                left join OPhongBan on ODanhSachNhanVien.Ref_MaPhongBan = OPhongBan.IOID
                left join qsrecordrights on OPhongBan.IFID_M319 = qsrecordrights.IFID and qsrecordrights.UID = %1$d 
                WHERE ODanhSachNhanVien.IFID_M316 = %2$d'
            ,$this->_user->user_id
            ,$ifid);

            $dataSQL = $this->_db->fetchOne($sql);
            $retval  = $dataSQL?$dataSQL->Rights:63;
        }
        return $retval;
    }
}