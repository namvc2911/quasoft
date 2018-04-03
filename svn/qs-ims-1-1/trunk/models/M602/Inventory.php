<?php
class Qss_Model_M602_Inventory extends Qss_Model_Abstract
{
    public function countLineByStock($Ref_Kho, $Ref_MatHang = 0, $likeMatHang = '', $tool = 0, $material= 0) {
        $sql = sprintf('
            SELECT count(1) as Total
            FROM OKho
            LEFT JOIN OSanPham ON OKho.Ref_MaSP = OSanPham.IOID
            WHERE IFNULL(OKho.Ref_Kho, 0) = %1$d AND IFNULL(OKho.SoLuongHC, 0) > 0           
        ', $Ref_Kho);
        $sql .= $Ref_MatHang?sprintf(' AND IFNULL(OKho.Ref_MaSP, 0) = %1$d ', $Ref_MatHang):'';
        $sql .= $likeMatHang?sprintf(' AND (OKho.MaSP like "%%%1$s%%" OR OKho.TenSP like "%%%1$s%%") ', $likeMatHang):'';
        $sql .= ($tool && !$material)?sprintf(' AND IFNULL(OSanPham.CongCu, 0) =  1'):'';
        $sql .= ($material && !$tool)?sprintf(' AND IFNULL(OSanPham.CongCu, 0) =  0'):'';

        // echo '<pre>'; print_r($sql); die;
        $dat = $this->_o_DB->fetchOne($sql);
        return $dat?$dat->Total:0;
    }


    public function getAllInventoryOfItems($refKho = 0, $itemIOIDs = array()) {
        $where = '';

        if(count($itemIOIDs)) {
            $where .= sprintf(' AND OSanPham.IOID IN (%1$s) ', implode(',', $itemIOIDs));
        }

        if($refKho) {
            $where .= sprintf(' AND IFNULL(OKho.Ref_Kho, 0) = %1$d ', $refKho);
        }

        $sql = sprintf('
            SELECT 
              OSanPham.IOID AS Ref_MaSanPham
              , OSanPham.MaSanPham
              , OSanPham.TenSanPham
              , OSanPham.NhomSP AS NhomSanPham
              , OKho.Ref_DonViTinh
              , OKho.DonViTinh
              , OKho.SoLuongHC
            FROM OKho
            INNER JOIN OSanPham ON  OSanPham.IOID = OKho.Ref_MaSP 
            WHERE 1=1 %1$s
            ORDER BY OSanPham.MaSanPham
        ', $where);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getInventoryOfItems($itemIOIDs = array()) {
        $itemIOIDs[] = 0;

        $sql = sprintf('
            SELECT 
              SUM(IF( IFNULL(ODanhSachKho.IOID, 0) != 0, IFNULL(OKho.SoLuongHC,0) * IFNULL(ODonViTinhSP.HeSoQuyDoi, 0), 0)) AS  SoLuongHC
              , OSanPham.IOID AS Ref_MaSanPham
              , OSanPham.MaSanPham
              , OSanPham.TenSanPham
              , OKho.Ref_DonViTinh
              , OKho.DonViTinh
            FROM OKho
            INNER JOIN ODanhSachKho ON OKho.Ref_Kho = ODanhSachKho.IOID AND ODanhSachKho.LoaiKho = %2$s
            INNER JOIN OSanPham ON  OSanPham.IOID = OKho.Ref_MaSP 
            INNER JOIN ODonViTinhSP ON OSanPham.IFID_M113 = ODonViTinhSP.IFID_M113 AND ODonViTinhSP.IOID = OKho.Ref_DonViTinh
            WHERE OSanPham.IOID IN (%1$s)
            GROUP BY OKho.Ref_MaSP 
        ', implode(',', $itemIOIDs), $this->_o_DB->quote(Qss_Lib_Extra_Const::WAREHOUSE_TYPE_MATERIAL));

        return $this->_o_DB->fetchAll($sql);
    }

    public function getArrayInventoryOfItems($itemIOIDs = array()) {
        $retval = array();
        $data   = $this->getInventoryOfItems();

        foreach ($data as $item) {
            $retval[$item->Ref_MaSanPham] = $item->SoLuongHC;
        }

        return $retval;
    }
}