<?php
class Qss_Bin_Calculate_OChiTietBanGiaoTaiSan_DonGia extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        $asset = $this->_object->getFieldByCode('MaTaiSan')->intRefIOID;
        $sql   = sprintf('SELECT NguyenGia FROM ODanhMucCongCuDungCu WHERE IOID = %1$d', $asset);
        $dat   = $this->_db->fetchOne($sql);

        return $dat?$dat->NguyenGia/1000:'';
    }

}
?>