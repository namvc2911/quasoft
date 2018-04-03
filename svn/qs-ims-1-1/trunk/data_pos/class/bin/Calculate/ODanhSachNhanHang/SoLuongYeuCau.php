<?php
class Qss_Bin_Calculate_ODanhSachNhanHang_SoLuongYeuCau extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        $common    = new Qss_Model_Extra_Extra();
        $soDonHang = $this->ONhanHang->PO(1);
        $soYeuCau  = $this->_object->getFieldByCode('SoYeuCau')->getValue();
        $maMatHang = $this->_object->getFieldByCode('MaMatHang')->getValue();
        $donViTinh = (int)$this->_object->getFieldByCode('DonViTinh')->intRefIOID;
        $soLuong   = $this->_object->getFieldByCode('SoLuongYeuCau')->getValue();

        if(!$soYeuCau)
        {
            $line      = $common->getTableFetchOne('ODanhSachNhanHang', array('IOID'=>$this->_object->i_IOID));
            $soYeuCau  = $line?$line->SoYeuCau:'';
            $maMatHang = $line?$line->MaMatHang:'';
            $donViTinh = (int)($line?$line->Ref_DonViTinh:0);
            $soLuong   = $line?$line->SoLuongYeuCau:0;
        }

        if($soYeuCau)
        {
            $sql = sprintf('
            SELECT DSDonMuaHang.*
            FROM ODonMuaHang AS DonMuaHang
            INNER JOIN ODSDonMuaHang AS DSDonMuaHang ON DonMuaHang.IFID_M401 = DSDonMuaHang.IFID_M401
            WHERE DonMuaHang.SoDonHang = %1$s
                AND DSDonMuaHang.SoYeuCau = %2$s
                AND DSDonMuaHang.MaSP = %3$s
                AND DSDonMuaHang.Ref_DonViTinh = %4$s'
                , $this->_db->quote($soDonHang)
                , $this->_db->quote($soYeuCau)
                , $this->_db->quote($maMatHang)
                , $donViTinh);

            $dataSql = $this->_db->fetchOne($sql);

            if($dataSql && $soLuong == 0)
            {
                return $dataSql->SoLuong;
            }
        }
    }
}
?>