<?php
//Bỏ vì chỉ cần tạo phiếu bảo trì
class Qss_Bin_Style_ODSYeuCauMuaSam_MaSP extends Qss_Lib_Style
{
    public function __doExecute()
    {
        if(Qss_Lib_System::fieldActive('OSanPham', 'MaTam'))
        {
            $mTable = Qss_Model_Db::Table('ODSYeuCauMuaSam');
            $mTable->select('OSanPham.MaTam');
            $mTable->join('INNER JOIN OSanPham ON ODSYeuCauMuaSam.Ref_MaSP = OSanPham.IOID');
            $mTable->where($mTable->ifnullNumber('OSanPham.MaTam', 1));
            $mTable->where(sprintf('ODSYeuCauMuaSam.IOID = %1$d', $this->_data->IOID));
            $item = $mTable->fetchOne();

            if($item && $item->MaTam == 1)
            {
                return 'bgpink bold';
            }
        }

        return '';
    }

}
?>