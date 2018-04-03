<?php
class Qss_Model_M875_Main extends Qss_Model_Abstract {
    public function getData($projectIOID, $dungCu = '',$ycccIOID = 0) {
        $where = '';

        if($dungCu === 1) {
            $where = ' AND IFNULL(OSanPham.CongCu, 0) = 1';
        }
        elseif($dungCu === 0) {
            $where = ' AND IFNULL(OSanPham.CongCu, 0) = 0';
        }
        else {
            $where = '';
        }
		if($ycccIOID)
		{
			$where .= sprintf(' and OYeuCauTrangThietBiVatTu.IOID = %1$d',$ycccIOID); 
		}
        $sql = sprintf('
            SELECT 
                OYeuCauVatTu.*
                , OYeuCauTrangThietBiVatTu.SoPhieu
                , OSanPham.DonViTinh AS DonViTinhCoSo
                , IFNULL(OSanPham.CongCu, 0) AS CongCu
                , IFNULL(NhapKhoDuAn.NhapMua, 0) AS NhapMua
                , IFNULL(NhapKhoDuAn.NhapXuatKho, 0) AS NhapXuatKho
                , IFNULL(XuatKhoDuAn.XuatHuHong, 0) AS XuatHuHong
                , IFNULL(XuatKhoDuAn.XuatMat, 0) AS XuatMat
                , IFNULL(XuatKhoDuAn.XuatSuDung, 0) AS XuatSuDung
                , SoLuongYeuCau
                , OSanPham.ViTri
            FROM OYeuCauTrangThietBiVatTu            
            INNER JOIN qsiforms ON OYeuCauTrangThietBiVatTu.IFID_M751 = qsiforms.IFID
            INNER JOIN OYeuCauVatTu ON OYeuCauTrangThietBiVatTu.IFID_M751 = OYeuCauVatTu.IFID_M751
            INNER JOIN OSanPham ON OYeuCauVatTu.Ref_MaVatTu = OSanPham.IOID        
            LEFT JOIN (
                SELECT 
                    ODanhSachNhapKho.Ref_MaSanPham
                    , SUM(
                        CASE WHEN OLoaiNhapKho.Loai = "MUAHANG" 
                        THEN (IFNULL(ODanhSachNhapKho.SoLuong, 0) * ODonViTinhSP.HeSoQuyDoi)  ELSE 0 END) AS NhapMua
                    , SUM(
                        CASE WHEN OLoaiNhapKho.Loai = "CHUYENKHO" 
                        THEN (IFNULL(ODanhSachNhapKho.SoLuong, 0) * ODonViTinhSP.HeSoQuyDoi) ELSE 0 END) AS NhapXuatKho
                FROM ONhapKho
                INNER JOIN qsiforms ON ONhapKho.IFID_M402 = qsiforms.IFID
                INNER JOIN ODuAn ON ONhapKho.Ref_Kho = ODuAn.Ref_KhoVatTu 
                    OR ONhapKho.Ref_Kho = ODuAn.Ref_KhoCongCu
                INNER JOIN OLoaiNhapKho ON ONhapKho.Ref_LoaiNhapKho = OLoaiNhapKho.IOID
                INNER JOIN ODanhSachNhapKho ON ONhapKho.IFID_M402 = ODanhSachNhapKho.IFID_M402
                INNER JOIN OSanPham ON ODanhSachNhapKho.Ref_MaSanPham = OSanPham.IOID
                INNER JOIN ODonViTinhSP ON OSanPham.IFID_M113 = ODonViTinhSP.IFID_M113 
                    AND ODanhSachNhapKho.Ref_DonViTinh = ODonViTinhSP.IOID
                WHERE qsiforms.Status = 2 AND ODuAn.IOID = %1$d                             
                GROUP BY ODanhSachNhapKho.Ref_MaSanPham
            ) AS NhapKhoDuAn ON OYeuCauVatTu.Ref_MaVatTu = NhapKhoDuAn.Ref_MaSanPham
            
            LEFT JOIN (
                SELECT 
                    ODanhSachXuatKho.Ref_MaSP
                    , SUM(
                        CASE WHEN OLoaiNhapKho.Loai = "XUATHUHONGVATTU" 
                        THEN (IFNULL(ODanhSachXuatKho.SoLuong, 0) * ODonViTinhSP.HeSoQuyDoi)  ELSE 0 END) AS XuatHuHong
                    , SUM(
                        CASE WHEN OLoaiNhapKho.Loai = "XUATMATVATTU" 
                        THEN (IFNULL(ODanhSachXuatKho.SoLuong, 0) * ODonViTinhSP.HeSoQuyDoi) ELSE 0 END) AS XuatMat
                    , SUM(
                        CASE WHEN OLoaiNhapKho.Loai = "XUATSANXUAT" 
                        THEN (IFNULL(ODanhSachXuatKho.SoLuong, 0) * ODonViTinhSP.HeSoQuyDoi) ELSE 0 END) AS XuatSuDung                        
                FROM OXuatKho
                INNER JOIN qsiforms ON OXuatKho.IFID_M506 = qsiforms.IFID
                INNER JOIN ODuAn ON OXuatKho.Ref_Kho = ODuAn.Ref_KhoVatTu 
                    OR OXuatKho.Ref_Kho = ODuAn.Ref_KhoCongCu
                INNER JOIN OLoaiNhapKho ON OXuatKho.Ref_LoaiXuatKho = OLoaiNhapKho.IOID
                INNER JOIN ODanhSachXuatKho ON OXuatKho.IFID_M506 = ODanhSachXuatKho.IFID_M506
                INNER JOIN OSanPham ON ODanhSachXuatKho.Ref_MaSP = OSanPham.IOID
                INNER JOIN ODonViTinhSP ON OSanPham.IFID_M113 = ODonViTinhSP.IFID_M113 
                    AND ODanhSachXuatKho.Ref_DonViTinh = ODonViTinhSP.IOID
                WHERE qsiforms.Status = 2 AND ODuAn.IOID = %1$d                             
                GROUP BY ODanhSachXuatKho.Ref_MaSP
            ) AS XuatKhoDuAn ON OYeuCauVatTu.Ref_MaVatTu = XuatKhoDuAn.Ref_MaSP
            WHERE IFNULL(OYeuCauTrangThietBiVatTu.Ref_DuAn, 0) = %1$d AND qsiforms.Status = 3 %2$s
            ORDER BY OSanPham.CongCu
        ', $projectIOID, $where);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }
}