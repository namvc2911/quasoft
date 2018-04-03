<?php
class Qss_Bin_Calculate_ODSYeuCauMuaSam_DonViTinh extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        $item    = $this->MaSP(1)?$this->MaSP(1):'';
        $sql     = sprintf('SELECT * FROM OSanPham AS MatHang WHERE MaSanPham = %1$s', $this->_db->quote($item));
        $dataSql = $this->_db->fetchOne($sql);

        return $dataSql?$dataSql->DonViTinh:0;
    }
}
?>