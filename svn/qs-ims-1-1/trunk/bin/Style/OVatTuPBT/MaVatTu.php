<?php
//Bỏ vì chỉ cần tạo phiếu bảo trì
class Qss_Bin_Style_OVatTuPBT_MaVatTu extends Qss_Lib_Style
{
    public function __doExecute()
    {
    	$bg = '';
        if($this->_data->MaVatTu == '')
        {
            $bg = 'bgorange';
        }

        if(Qss_Lib_System::fieldActive('OSanPham', 'MaTam'))
        {
            $mTable = Qss_Model_Db::Table('OVatTuPBT');
            $mTable->select('OSanPham.MaTam');
            $mTable->join('INNER JOIN OSanPham ON OVatTuPBT.Ref_MaVatTu = OSanPham.IOID');
            $mTable->where($mTable->ifnullNumber('OSanPham.MaTam', 1));
            $mTable->where(sprintf('OVatTuPBT.IOID = %1$d', $this->_data->IOID));
            $item = $mTable->fetchOne();

            if($item && $item->MaTam == 1)
            {
                return 'bgpink bold';
            }
        }
        return $bg;
    }

}
?>