<?php
class Qss_Bin_Onload_OYeuCauTrangThietBiVatTu extends Qss_Lib_Onload {
    public function __doExecute() {
        parent::__doExecute();

    }

    public function DuAn() {
        $user = Qss_Register::get('userinfo');

        $this->_object->getFieldByCode('DuAn')->arrFilters[] = sprintf('
             v.IOID in (
                SELECT IOID 
                FROM ODuAn
                INNER JOIN qsrecordrights ON ODuAn.IFID_M803 = qsrecordrights.IFID 
                WHERE UID = %1$d
            )
        ', $user->user_id);
    }
}