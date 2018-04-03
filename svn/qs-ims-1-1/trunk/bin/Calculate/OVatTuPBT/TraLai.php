<?php
class Qss_Bin_Calculate_OVatTuPBT_TraLai extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        $refItem = (int)$this->_object->getFieldByCode('MaVatTu')->intRefIOID;
        $refUOM  = (int)$this->_object->getFieldByCode('DonViTinh')->intRefIOID;
        $ifid    = (int)$this->_object->i_IFID;
        $ioid    = (int)$this->_object->i_IOID;

        if(!$refItem)
        {
            $sql      = sprintf('SELECT * FROM OVatTuPBT WHERE IOID = %1$d',$ioid);
            $material = $this->_db->fetchOne($sql);

            $refItem  = $material?$material->Ref_MaVatTu:0;
            $refUOM   = $material?$material->Ref_DonViTinh:0;
        }

        if($refItem && $refUOM)
        {
            $sql = sprintf('
                SELECT DSNhapKho.*, sum(ifnull(DSNhapKho.SoLuong, 0)) AS SoLuong
                FROM ONhapKho AS NhapKho
                INNER JOIN ODanhSachNhapKho AS DSNhapKho ON NhapKho.IFID_M402 = DSNhapKho.IFID_M402
                INNER JOIN OXuatKho AS XuatKho ON NhapKho.Ref_PhieuXuatKho = XuatKho.IOID
                INNER JOIN OPhieuBaoTri AS PhieuBT ON XuatKho.Ref_PhieuBaoTri = PhieuBT.IOID
                WHERE PhieuBT.IFID_M759 = %1$d AND DSNhapKho.Ref_MaSanPham = %2$d AND DSNhapKho.Ref_DonViTinh = %3$d
            ', $ifid, $refItem, $refUOM);

            //echo '<pre>'; print_r($sql); die;
            $input = $this->_db->fetchOne($sql);
            return $input?$input->SoLuong:0;
        }


    }
}