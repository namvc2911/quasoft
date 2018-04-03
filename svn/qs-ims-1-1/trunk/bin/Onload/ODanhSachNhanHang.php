<?php
class Qss_Bin_Onload_ODanhSachNhanHang extends Qss_Lib_Onload
{
    public function __doExecute()
    {
        parent::__doExecute();

        $sql = sprintf('
            SELECT *
            FROM ONhanHang
            WHERE IFID_M408 = %1$d
        ', (int)$this->_object->i_IFID);

        $dataSQL = $this->_db->fetchOne($sql);

        $this->_object->getFieldByCode('SoYeuCau')->arrFilters[] =
            sprintf('
                ifnull(v.IOID, 0) in (
                    SELECT IFNULL(ODSDonMuaHang.Ref_SoYeuCau, 0)
                    FROM ODonMuaHang
                    INNER JOIN ODSDonMuaHang ON ODonMuaHang.IFID_M401 = ODSDonMuaHang.IFID_M401
                    WHERE ODonMuaHang.IOID = %1$d
                )
            ', (int)$dataSQL->Ref_PO);

        $this->_object->getFieldByCode('MaMatHang')->arrFilters[] =
            sprintf('
                ifnull(v.IOID, 0) in (
                    SELECT IFNULL(ODSDonMuaHang.Ref_MaSP, 0)
                    FROM ODonMuaHang
                    INNER JOIN ODSDonMuaHang ON ODonMuaHang.IFID_M401 = ODSDonMuaHang.IFID_M401
                    WHERE ODonMuaHang.IOID = %1$d AND IFNULL(ODSDonMuaHang.Ref_SoYeuCau, 0) = %2$d
                )
            ', (int)$dataSQL->Ref_PO, (int)$this->_object->getFieldByCode('SoYeuCau')->intRefIOID);
    }
}