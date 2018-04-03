<?php
class Qss_Bin_Style_OPhieuBaoTri_SoPhieu extends Qss_Lib_Style
{
    public function __doExecute()
    {
        $bg = '';

        if(Qss_Lib_System::fieldActive('ODanhSachThietBi', 'MaTam'))
        {
            $mTable = Qss_Model_Db::Table('OPhieuBaoTri');
            $mTable->select('ODanhSachThietBi.MaTam');
            $mTable->join('INNER JOIN ODanhSachThietBi ON OPhieuBaoTri.Ref_MaThietBi = ODanhSachThietBi.IOID');
            $mTable->where($mTable->ifnullNumber('ODanhSachThietBi.MaTam', 1));
            $mTable->where(sprintf('OPhieuBaoTri.IFID_M759 = %1$d', $this->_data->IFID_M759));
            $item = $mTable->fetchOne();

            if($item && $item->MaTam == 1)
            {
                $bg = 'bgpink bold';
            }
        }

        if(Qss_Lib_System::fieldActive('OSanPham', 'MaTam'))
        {
            $mTable = Qss_Model_Db::Table('OVatTuPBT');
            $mTable->select('OSanPham.MaTam');
            $mTable->join('INNER JOIN OSanPham ON OVatTuPBT.Ref_MaVatTu = OSanPham.IOID');
            $mTable->where($mTable->ifnullNumber('OSanPham.MaTam', 1));
            $mTable->where(sprintf('OVatTuPBT.IFID_M759 = %1$d', $this->_data->IFID_M759));
            $mTable->display(1);
            $item = $mTable->fetchOne();

            if($item && $item->MaTam == 1)
            {
                $bg = 'bgpink bold';
            }

            if(Qss_Lib_System::objectInForm('M759', 'OVatTuPBTDK')) {
                $mTable = Qss_Model_Db::Table('OVatTuPBTDK');
                $mTable->select('OSanPham.MaTam');
                $mTable->join('INNER JOIN OSanPham ON OVatTuPBTDK.Ref_MaVatTu = OSanPham.IOID');
                $mTable->where($mTable->ifnullNumber('OSanPham.MaTam', 1));
                $mTable->where(sprintf('OVatTuPBTDK.IFID_M759 = %1$d', $this->_data->IFID_M759));
                $mTable->display(1);
                $item = $mTable->fetchOne();

                if($item && $item->MaTam == 1)
                {
                    $bg = 'bgpink bold';
                }
            }

        }
        return $bg;
    }
}