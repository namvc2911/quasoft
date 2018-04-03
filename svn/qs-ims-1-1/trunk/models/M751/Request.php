<?php
class Qss_Model_M751_Request extends Qss_Model_Abstract
{
    public function countItemsByRequest($Ref_YeuCau, $Ref_MatHang = 0, $likeMatHang = '', $tool = 0, $material = 0) {
        $sql = sprintf('
            SELECT count(1) as Total
            FROM OYeuCauVatTu
            INNER JOIN OYeuCauTrangThietBiVatTu ON OYeuCauVatTu.IFID_M751 = OYeuCauTrangThietBiVatTu.IFID_M751
            LEFT JOIN OSanPham On OYeuCauVatTu.Ref_MaVatTu = OSanPham.IOID
            WHERE IFNULL(OYeuCauTrangThietBiVatTu.IOID, 0) = %1$d           
        ', $Ref_YeuCau);
        $sql .= $Ref_MatHang?sprintf(' AND IFNULL(OYeuCauVatTu.Ref_MaVatTu, 0) = %1$d ', $Ref_MatHang):'';
        $sql .= $likeMatHang?sprintf(' AND (OYeuCauVatTu.MaVatTu like "%%%1$s%%" OR OYeuCauVatTu.TenVatTu like "%%%1$s%%") ', $likeMatHang):'';
        $sql .= ($tool && !$material)?sprintf(' AND IFNULL(OSanPham.CongCu, 0) =  1'):'';
        $sql .= ($material && !$tool)?sprintf(' AND IFNULL(OSanPham.CongCu, 0) =  0'):'';


        // echo '<pre>'; print_r($sql); die;
        $dat = $this->_o_DB->fetchOne($sql);
        return $dat?$dat->Total:0;
    }

    /* remove */
    public function getRequestItemsFromOutput($IFID_M751, $IFID_M506) {
        $sql = sprintf('
            SELECT
                YeuCauVatTu.IOID AS RequestIOID
                , MatHang.MaSanPham
                , MatHang.TenSanPham
                , MatHang.NhomSP AS NhomSanPham
                , YeuCauVatTu.Ref_DonViTinh
                , YeuCauVatTu.DonViTinh
                , YeuCauVatTu.SoLuongYeuCau
                , IFNULL(YeuCauVatTu.SoLuongDieuDong, 0) AS SoLuongDieuDong
                , IFNULL(DanhSachXuatKho.SoLuong, 0) AS SoLuongXuatKho
            FROM OYeuCauVatTu AS YeuCauVatTu
            INNER JOIN OSanPham AS MatHang ON YeuCauVatTu.Ref_MaVatTu = MatHang.IOID
            LEFT JOIN (
                SELECT * 
                FROM ODanhSachXuatKho
                WHERE IFID_M506 = %2$d
            ) AS DanhSachXuatKho ON YeuCauVatTu.Ref_MaVatTu = DanhSachXuatKho.Ref_MaSP
                AND YeuCauVatTu.Ref_DonViTinh = DanhSachXuatKho.Ref_DonViTinh
            WHERE YeuCauVatTu.IFID_M751 = %1$d     
            ORDER BY MatHang.MaSanPham
            -- @todo: Có thể lấy các xuất kho không có ở trong yêu cầu để hiện ra.            
        ', $IFID_M751, $IFID_M506);

        // echo '<Pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    /* remove */
    public function getRequestItemsFromInput($IFID_M751, $IFID_M402) {
        $sql = sprintf('
            SELECT
                YeuCauVatTu.IOID AS RequestIOID
                , MatHang.MaSanPham
                , MatHang.TenSanPham
                , MatHang.NhomSP AS NhomSanPham
                , YeuCauVatTu.Ref_DonViTinh
                , YeuCauVatTu.DonViTinh
                , YeuCauVatTu.SoLuongYeuCau
                , IFNULL(YeuCauVatTu.SoLuongDieuDong, 0) AS SoLuongDieuDong
                , IFNULL(ODanhSachNhapKho.SoLuong, 0) AS SoLuongNhapKho
            FROM OYeuCauVatTu AS YeuCauVatTu
            INNER JOIN OSanPham AS MatHang ON YeuCauVatTu.Ref_MaVatTu = MatHang.IOID
            LEFT JOIN (
                SELECT * 
                FROM ODanhSachNhapKho
                WHERE IFID_M402 = %2$d
            ) AS ODanhSachNhapKho ON YeuCauVatTu.Ref_MaVatTu = ODanhSachNhapKho.Ref_MaSanPham
                AND YeuCauVatTu.Ref_DonViTinh = ODanhSachNhapKho.Ref_DonViTinh
            WHERE YeuCauVatTu.IFID_M751 = %1$d     
            ORDER BY MatHang.MaSanPham
            -- @todo: Có thể lấy các xuất kho không có ở trong yêu cầu để hiện ra.            
        ', $IFID_M751, $IFID_M402);

        // echo '<Pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }
}