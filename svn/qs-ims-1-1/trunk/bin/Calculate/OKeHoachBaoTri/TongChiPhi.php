<?php
class Qss_Bin_Calculate_OKeHoachBaoTri_TongChiPhi extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        if(Qss_Lib_System::fieldActive('OKeHoachBaoTri', 'TongChiPhi'))
        {
            $sql = sprintf('
                SELECT * FROM OChiPhiKeHoach WHERE IFID_M837 = %1$d ORDER BY IFID_M837 DESC LIMIT 1
            ', $this->_object->i_IFID);
            $data = $this->_db->fetchOne($sql);

            if($data) {
                return Qss_Lib_Util::formatMoney($data->TongChiPhi);
            }
        }
    }
}
?>
