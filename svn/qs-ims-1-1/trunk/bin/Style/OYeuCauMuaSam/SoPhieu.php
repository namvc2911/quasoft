<?php
//Bỏ vì chỉ cần tạo phiếu bảo trì
class Qss_Bin_Style_OYeuCauMuaSam_SoPhieu extends Qss_Lib_Style
{
    public function __doExecute()
    {
        if(Qss_Lib_System::fieldActive('OSanPham', 'MaTam'))
        {
            $mTable = Qss_Model_Db::Table('ODSYeuCauMuaSam');
            $mTable->select('count(1) AS Total');
            $mTable->join('INNER JOIN OSanPham ON ODSYeuCauMuaSam.Ref_MaSP = OSanPham.IOID');
            $mTable->where($mTable->ifnullNumber('OSanPham.MaTam', 1));
            $mTable->where(sprintf('IFID_M412 = %1$d', $this->_data->IFID_M412));
            $count = $mTable->fetchOne();
            $count = $count?$count->Total:0;

            if($count)
            {
                return 'bgpink bold';
            }
        }

        return '';
    }

}
?>