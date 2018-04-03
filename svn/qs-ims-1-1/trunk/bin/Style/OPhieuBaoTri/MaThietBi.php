<?php
class Qss_Bin_Style_OPhieuBaoTri_MaThietBi extends Qss_Lib_Style
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

        return $bg;
    }
}