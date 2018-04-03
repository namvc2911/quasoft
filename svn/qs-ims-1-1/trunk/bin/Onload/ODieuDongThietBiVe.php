<?php
class Qss_Bin_Onload_ODieuDongThietBiVe extends Qss_Lib_Onload
{
    public function __doExecute()
    {

    }

    public function DuAn() {
        if(Qss_Lib_System::formSecure('M803'))
        {
            $user   = Qss_Register::get('userinfo');
            $this->_object->getFieldByCode('DuAn')->arrFilters[] = sprintf(' 
                v.IFID_M803 in (
                    SELECT IFID 
                    FROM qsrecordrights 
                    WHERE FormCode = "M803" and UID = %1$d
                )'
                ,$user->user_id);
        }
    }
}