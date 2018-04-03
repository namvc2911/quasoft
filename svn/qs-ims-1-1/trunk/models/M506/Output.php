<?php
class Qss_Model_M506_Output extends Qss_Model_Abstract {
    public function getItemsByStock(
        $IFID_M506
        , $Ref_SoYeuCau
        , $Ref_Kho
        , $page
        , $display
        , $Ref_MatHang = 0
        , $likeMatHang = ''
        , $tool = 0
        , $material = 0) {
        $sql = sprintf('
            SELECT 
                OSanPham.IOID AS Ref_MaSanPham
                , OSanPham.MaSanPham
                , OSanPham.TenSanPham
                , OSanPham.NhomSP AS NhomSanPham
                , OKho.Ref_DonViTinh
                , OKho.DonViTinh
                , OKho.SoLuongHC
                , IFNULL(XuatKhoHienTai.IOID, 0) AS RefXuatKhoHienTai
                , XuatKhoHienTai.SoLuong AS SoLuongDaLay
                , XuatKhoKhac.SoLuongKhac AS SoLuongXuatKhac
            FROM OKho
            INNER JOIN OSanPham ON  OSanPham.IOID = OKho.Ref_MaSP 
            -- Xuat Kho Hien Tai
            LEFT JOIN
            (
                SELECT *
                FROM ODanhSachXuatKho
                WHERE IFID_M506 = %1$d
            ) AS XuatKhoHienTai ON OKho.Ref_MaSP = XuatKhoHienTai.Ref_MaSP AND OKho.Ref_DonViTinh = XuatKhoHienTai.Ref_DonViTinh
            
            -- Cac phieu khac lay duoc bao nhieu roi
            LEFT JOIN
            (
                SELECT 
                    ODanhSachXuatKho.*
                    , SUM(IFNULL(ODanhSachXuatKho.SoLuong, 0)) AS SoLuongKhac
                FROM OXuatKho
                INNER JOIN qsiforms ON OXuatKho.IFID_M506 = qsiforms.IFID
                INNER JOIN ODanhSachXuatKho ON OXuatKho.IFID_M506 = ODanhSachXuatKho.IFID_M506
                WHERE OXuatKho.Ref_Kho = %3$d AND OXuatKho.IFID_M506 != %1$d AND qsiforms.Status = 1
                GROUP BY ODanhSachXuatKho.Ref_MaSP, ODanhSachXuatKho.Ref_DonViTinh
            ) AS XuatKhoKhac ON OKho.Ref_MaSP = XuatKhoKhac.Ref_MaSP AND OKho.Ref_DonViTinh = XuatKhoKhac.Ref_DonViTinh
            
            WHERE IFNULL(OKho.Ref_Kho, 0) = %3$d AND IFNULL(OKho.SoLuongHC, 0) > 0
            
            
        ', $IFID_M506, $Ref_SoYeuCau, $Ref_Kho);
        $sql .= $Ref_MatHang?sprintf(' AND IFNULL(OKho.Ref_MaSP, 0) = %1$d ', $Ref_MatHang):'';
        $sql .= $likeMatHang?sprintf(' AND (OKho.MaSP like "%%%1$s%%" OR OKho.TenSP like "%%%1$s%%") ', $likeMatHang):'';
        $sql .= ($tool && !$material)?sprintf(' AND IFNULL(OSanPham.CongCu, 0) =  1'):'';
        $sql .= ($material && !$tool)?sprintf(' AND IFNULL(OSanPham.CongCu, 0) =  0'):'';
        $sql .= ' ORDER BY OSanPham.MaSanPham ';
        $sql .= sprintf(' LIMIT %1$d, %2$d ', ceil(( abs($page-1) * $display)), $display);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getItemsByRequest(
        $Ref_YeuCau, $IFID_M506, $Ref_Kho, $page, $display
        , $Ref_MatHang = 0, $likeMatHang = '', $tool= 0, $material = 0)  {
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
                , IFNULL(YeuCauVatTu.SoLuongMua, 0) AS SoLuongMua
                , IF(IFNULL(DanhSachXuatKho.IOID, 0) <> 0
                    , IFNULL(DanhSachXuatKho.SoLuong, 0)
                    , IFNULL(YeuCauVatTu.SoLuongDieuDong, 0)) AS SoLuongXuatKho
                , IFNULL(YCTrenPhieuKhacChuaNhap.SoLuongPhieuKhacChuaXuat, 0) AS SoLuongPhieuKhacChuaXuat
                , IFNULL(TonKho.SoLuongTonKho, 0) AS SoLuongTonKho
            FROM OYeuCauVatTu AS YeuCauVatTu
            INNER JOIN OYeuCauTrangThietBiVatTu ON YeuCauVatTu.IFID_M751 = OYeuCauTrangThietBiVatTu.IFID_M751
            INNER JOIN OSanPham AS MatHang ON YeuCauVatTu.Ref_MaVatTu = MatHang.IOID
            LEFT JOIN (
                SELECT * 
                FROM ODanhSachXuatKho
                WHERE IFID_M506 = %2$d
            ) AS DanhSachXuatKho ON YeuCauVatTu.Ref_MaVatTu = DanhSachXuatKho.Ref_MaSP
                AND YeuCauVatTu.Ref_DonViTinh = DanhSachXuatKho.Ref_DonViTinh
            LEFT JOIN (
                SELECT ODanhSachXuatKho.*, SUM(IFNULL(ODanhSachXuatKho.SoLuong, 0)) AS SoLuongPhieuKhacChuaXuat
                FROM OXuatKho
                INNER JOIN qsiforms ON OXuatKho.IFID_M506 = qsiforms.IFID
                INNER JOIN ODanhSachXuatKho ON OXuatKho.IFID_M506 = ODanhSachXuatKho.IFID_M506
                WHERE IFNULL(OXuatKho.Ref_SoYeuCau, 0) = %1$d AND qsiforms.Status = 1 AND ODanhSachXuatKho.IFID_M506 != %2$d
                GROUP BY ODanhSachXuatKho.Ref_MaSP, ODanhSachXuatKho.Ref_DonViTinh
            ) AS YCTrenPhieuKhacChuaNhap ON DanhSachXuatKho.Ref_MaSP = YCTrenPhieuKhacChuaNhap.Ref_MaSP
                AND DanhSachXuatKho.Ref_DonViTinh = YCTrenPhieuKhacChuaNhap.Ref_DonViTinh
            LEFT JOIN (
                SELECT *, Sum(IFNULL(OKho.SoLuongHC, 0)) AS SoLuongTonKho
                FROM OKho
                WHERE Ref_Kho = %3$d AND IFNULL(SoLuongHC, 0) > 0 
                GROUP BY Ref_MaSP , Ref_DonViTinh              
            ) AS TonKho ON  DanhSachXuatKho.Ref_MaSP = TonKho.Ref_MaSP
                AND DanhSachXuatKho.Ref_DonViTinh = TonKho.Ref_DonViTinh
            WHERE OYeuCauTrangThietBiVatTu.IOID = %1$d     
        ', $Ref_YeuCau, $IFID_M506, $Ref_Kho );

        $sql .= $Ref_MatHang?sprintf(' AND IFNULL(YeuCauVatTu.Ref_MaVatTu, 0) = %1$d ', $Ref_MatHang):'';
        $sql .= $likeMatHang?sprintf(' AND (YeuCauVatTu.MaVatTu like "%%%1$s%%" OR YeuCauVatTu.TenVatTu like "%%%1$s%%") ', $likeMatHang):'';
        $sql .= ($tool && !$material)?sprintf(' AND IFNULL(MatHang.CongCu, 0) =  1'):'';
        $sql .= ($material && !$tool)?sprintf(' AND IFNULL(MatHang.CongCu, 0) =  0'):'';

        $sql .= ' ORDER BY MatHang.MaSanPham   ';
        $sql .= sprintf(' LIMIT %1$d, %2$d ', ceil(( abs($page-1) * $display)), $display);

        // echo '<Pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }


}