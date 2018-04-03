<?php
class Qss_Bin_Filter_ODanhSachThietBi extends Qss_Lib_Filter
{
    public function getJoin()
    {
        return '';
//        $retval = '';
//        $rights = Qss_Lib_System::getFormRights('M705', $this->_user->user_group_list);
//        if($rights && !($rights & 2))
//        {
//            //select
//            $retval = sprintf('
//					inner join OKhuVuc on OKhuVuc.IOID = v.Ref_MaKhuVuc
//					inner join (SELECT t1.Ref_Ma,t1.IFID_M125,t2.* FROM OThietBi as t1 left join OKhuVuc as t2
//					on t2.IOID=t1.Ref_Ma) as TB on (TB.lft <= OKhuVuc.lft and TB.rgt >= OKhuVuc.rgt) or TB.Ref_Ma=v.IOID
//					inner join ONhanVien on ONhanVien.IFID_M125 = TB.IFID_M125
//					inner join ODanhSachNhanVien on ODanhSachNhanVien.IOID =  ONhanVien.Ref_MaNV
//					');
//        }
//        return $retval;
    }
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
//        $retval = sprintf(' and v.Ref_MaKhuVuc in (SELECT IOID FROM OKhuVuc
//						inner join qsrecordrights on OKhuVuc.IFID_M720 = qsrecordrights.IFID
//						WHERE UID = %1$d)'
//            ,$this->_user->user_id);
        if($makhuvuc)
        {
            $retval .= sprintf(' and v.Ref_MaKhuVuc in (SELECT IOID FROM OKhuVuc
						WHERE OKhuVuc.lft >= (SELECT lft FROM OKhuVuc WHERE IOID=%1$d) 
						and OKhuVuc.rgt <= (SELECT rgt FROM OKhuVuc WHERE IOID = %1$d))'
                ,$makhuvuc);
            //echo $retval;die;
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

        /*$rights = Qss_Lib_System::getFormRights('M705', $this->_user->user_group_list);
        if($rights && !($rights & 2))
        {
            //select
            $retval .= sprintf(' and ODanhSachNhanVien.Ref_TenTruyCap = %1$d',$this->_user->user_id);
        }*/
        return $retval;
    }

}