<?php
//cho đà nẵng
class Qss_Bin_Calculate_OPhieuBaoTri_TuLam extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        $retval  = 0;
        $ifid    =  $this->_object->i_IFID;

        if($ifid) {
            $sql     = sprintf('
                SELECT SUM(IFNULL(ChiPhiNhanCong, 0) + IFNULL(ChiPhiVatTu, 0)) AS ChiPhi 
                FROM OCongViecBTPBT 
                WHERE IFID_M759 = %1$d AND IFNULL(HinhThuc, 0) = 0',$ifid
            );
            $dataSQL = $this->_db->fetchOne($sql);

            if($dataSQL) {
                $retval = $dataSQL->ChiPhi;
            }
        }

        return Qss_Lib_Util::formatMoney($retval);
    }
}