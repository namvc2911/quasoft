<?php
class Qss_Bin_Filter_OYeuCauBaoTri extends Qss_Lib_Filter
{
	public function getJoin()
	{
		$retval = '';
		return '';
		$rights = Qss_Lib_System::getFormRights('M715', $this->_user->user_group_list);
		if($rights && !($rights & 16))
		{
			//select 
			$retval = sprintf(' 
					inner join ODanhSachThietBi on v.Ref_MaMayMoc = ODanhSachThietBi.IOID
					inner join OKhuVuc on OKhuVuc.IOID = ODanhSachThietBi.Ref_MaKhuVuc
					inner join (SELECT t1.Ref_Ma,t1.IFID_M125,t2.* FROM OThietBi as t1 left join OKhuVuc as t2 
					on t2.IOID=t1.Ref_Ma) as TB on (TB.lft <= OKhuVuc.lft and TB.rgt >= OKhuVuc.rgt) or TB.Ref_Ma=ODanhSachThietBi.IOID 
					inner join ONhanVien on ONhanVien.IFID_M125 = TB.IFID_M125 
					inner join ODanhSachNhanVien on ODanhSachNhanVien.IOID =  ONhanVien.Ref_MaNV
					');
		}
		return $retval;
	}
    /*
	public function getWhere()
	{
		$retval = '';
		$rights = Qss_Lib_System::getFormRights('M715', $this->_user->user_group_list);
		if($rights && !($rights & 16))
		{
			//select 
			$retval = sprintf(' and ODanhSachNhanVien.Ref_TenTruyCap = %1$d',$this->_user->user_id);
		}
		return $retval;
	}
    */

    public function getRights($ifid)
    {
        $retval = 63;
        if(Qss_Lib_System::formSecure('M720'))
        {
            $sql = sprintf('
                select 63 as rights 
                from OYeuCauBaoTri                  
                left JOIN ODanhSachThietBi ON IFNULL(OYeuCauBaoTri.Ref_MaThietBi, 0) = ODanhSachThietBi.IOID
                left JOIN OKhuVuc ON IFNULL(OYeuCauBaoTri.Ref_MaKhuVuc, 0) = OKhuVuc.IOID
                			or ODanhSachThietBi.Ref_MaKhuVuc = OKhuVuc.IOID
                where 
                    (
                        OKhuVuc.IFID_M720 in (
                        SELECT IFID 
                        FROM qsrecordrights 
                        WHERE UID = %1$d) 
                    )
                    and OYeuCauBaoTri.IFID_M747 = %2$d'
                ,$this->_user->user_id
                ,$ifid);
            $dataSQL = $this->_db->fetchOne($sql);
            $retval = $dataSQL?$dataSQL->rights:0;
        }
        return $retval;
    }
}