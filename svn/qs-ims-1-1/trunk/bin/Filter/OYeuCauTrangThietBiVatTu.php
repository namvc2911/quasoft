<?php
class Qss_Bin_Filter_OYeuCauTrangThietBiVatTu extends Qss_Lib_Filter
{
    public function getWhere()
    {
        $retval = '';

        if(Qss_Lib_System::formSecure('M803'))
        {
            $retval = sprintf(' 
                AND (v.Ref_DuAn IN (
                    SELECT IOID 
                    FROM ODuAn
                    INNER JOIN qsrecordrights ON ODuAn.IFID_M803 = qsrecordrights.IFID 
                    WHERE UID = %1$d
                ) or ifnull(v.Ref_DuAn,0)=0)'
                ,$this->_user->user_id);
        }

        return $retval;
    }
}