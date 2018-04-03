<?php
class Qss_Bin_Calculate_OVatTuPBT_XuatKho extends Qss_Lib_Calculate
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
                SELECT DSXuatKho.*, sum(ifnull(DSXuatKho.SoLuong, 0)) AS SoLuong
                FROM OXuatKho AS XuatKho
                INNER JOIN ODanhSachXuatKho AS DSXuatKho ON XuatKho.IFID_M506 = DSXuatKho.IFID_M506
                INNER JOIN OPhieuBaoTri AS PhieuBT ON XuatKho.Ref_PhieuBaoTri = PhieuBT.IOID
                WHERE PhieuBT.IFID_M759 = %1$d AND DSXuatKho.Ref_MaSP = %2$d AND DSXuatKho.Ref_DonViTinh = %3$d
            ', $ifid, $refItem, $refUOM);


            $output = $this->_db->fetchOne($sql);
            return $output?$output->SoLuong:0;
        }
    }
}