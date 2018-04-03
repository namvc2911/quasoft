<?php
include "../sysbase.php";
$db     = Qss_Db::getAdapter('main');
$object = new Qss_Model_System_Object();
$temp   = 'temp_'.uniqid();
$woArr  = array(0);

$db->execute('SET NAMES utf8;');

$sql = sprintf('
    SELECT
        PhieuBaoTri.IFID_M759,
        IFNULL(ChiPhiVatTu.ChiPhiVatTu, 0) AS ChiPhiVatTu,
        IFNULL(ChiPhiVatTu.ChiPhiVatTuDK, 0) AS ChiPhiVatTuDK,
        IFNULL(ChiPhiNhanCong.ChiPhiNhanCong, 0) AS ChiPhiNhanCong,
        IFNULL(ChiPhiNhanCong.ChiPhiNhanCongDK, 0) AS ChiPhiNhanCongDK,
        IFNULL(ChiPhiDichVu.ChiPhiDichVu, 0) AS ChiPhiDichVu,
        IFNULL(ChiPhiDichVu.ChiPhiDichVuDK, 0) AS ChiPhiDichVuDK,
        IFNULL(BangChiPhi.IOID, 0) AS DaCoBangChiPhi
    FROM OPhieuBaoTri AS PhieuBaoTri
    INNER JOIN qsiforms ON PhieuBaoTri.IFID_M759 = qsiforms.IFID

    LEFT JOIN (
        SELECT
            SUM(
                CASE WHEN TinhLuong = 1 THEN (ThoiGian/60) * LuongTheoGio
                WHEN  TinhLuong = 2 THEN NhanCong * LuongTheoCong
                ELSE NhanCong * LuongTheoCong  END
            ) AS ChiPhiNhanCong
            , SUM(
                CASE WHEN TinhLuong = 1 THEN (ThoiGianDuKien/60) * LuongTheoGio
                WHEN  TinhLuong = 2 THEN NhanCongDuKien * LuongTheoCong
                ELSE NhanCongDuKien * LuongTheoCong  END
            ) AS ChiPhiNhanCongDK
            , IFID_M759
        FROM (
            SELECT *
            FROM (
                SELECT
                    PhieuBaoTri.IFID_M759, CongViec.IOID, CongViec.ThoiGian, CongViec.ThoiGianDuKien, CongViec.NhanCong, CongViec.NhanCongDuKien, BangLuong.LuongTheoGio, BangLuong.LuongTheoCong, PhanLoaiCongViec.TinhLuong
                FROM OCongViecBTPBT AS CongViec
                INNER JOIN OPhieuBaoTri AS PhieuBaoTri ON CongViec.IFID_M759 = PhieuBaoTri.IFID_M759
                INNER JOIN OCongViecBaoTri AS PhanLoaiCongViec ON CongViec.Ref_Ten = PhanLoaiCongViec.IOID
                INNER JOIN OBangLuongTheoCongViec AS BangLuong ON PhanLoaiCongViec.IFID_M714 = BangLuong.IFID_M714
                    AND IF(IFNULL(CongViec.Ngay, \'\') != \'\', CongViec.Ngay, PhieuBaoTri.NgayBatDau) >= BangLuong.Ngay
                    AND (IFNULL(BangLuong.Ref_DonVi, 0) = 0 OR IFNULL(BangLuong.Ref_DonVi, 0) = PhieuBaoTri.DeptID)
                ORDER BY PhieuBaoTri.IFID_M759, CongViec.IOID, BangLuong.Ngay DESC, BangLuong.IOID DESC
            ) AS CongViec
            GROUP BY CongViec.IFID_M759, CongViec.IOID
        ) AS ChiPhiNhanCong
        GROUP BY IFID_M759
    ) AS ChiPhiNhanCong ON PhieuBaoTri.IFID_M759 = ChiPhiNhanCong.IFID_M759

    LEFT JOIN (
        SELECT
            VatTu.IFID_M759
            , SUM( IFNULL(VatTu.SoLuong, 0) * IFNULL(DSXuatKho.DonGia, 0)) AS ChiPhiVatTu
            , SUM(IFNULL(VatTu.SoLuongDuKien,0) * IFNULL(DSXuatKho.DonGia, 0)) as ChiPhiVatTuDK
        FROM OVatTuPBT AS VatTu
        INNER JOIN OPhieuBaoTri AS PhieuBaoTri ON VatTu.IFID_M759 = PhieuBaoTri.IFID_M759
        INNER JOIN OXuatKho AS XuatKho ON PhieuBaoTri.IOID = IFNULL(XuatKho.Ref_PhieuBaoTri, 0)
        INNER JOIN ODanhSachXuatKho AS DSXuatKho ON XuatKho.IFID_M506 = DSXuatKho.IFID_M506
            AND VatTu.Ref_MaVatTu = DSXuatKho.Ref_MaSP
            AND VatTu.Ref_DonViTinh = DSXuatKho.Ref_DonViTinh
        GROUP BY VatTu.IFID_M759
    ) AS ChiPhiVatTu ON PhieuBaoTri.IFID_M759 = ChiPhiVatTu.IFID_M759

    LEFT JOIN (
        SELECT
            DichVu.IFID_M759
            , SUM(ifnull(DichVu.ChiPhi,0)) as ChiPhiDichVu
            , SUM(ifnull(DichVu.ChiPhiDuKien,0)) as ChiPhiDichVuDK
        FROM ODichVuPBT AS DichVu
        GROUP BY DichVu.IFID_M759
    ) AS ChiPhiDichVu ON PhieuBaoTri.IFID_M759 = ChiPhiDichVu.IFID_M759

    LEFT JOIN (
        SELECT *
        FROM OChiPhiPBT
        GROUP BY IFID_M759
    ) AS BangChiPhi ON PhieuBaoTri.IFID_M759 = BangChiPhi.IFID_M759

    WHERE qsiforms.Status NOT IN (1, 2, 5)


    ');

// echo '<pre>'; print_r($sql); die;

$chiPhi = $db->fetchAll($sql);

foreach($chiPhi as $item)
{
    if($item->DaCoBangChiPhi)
    {
        $sql = sprintf('update OChiPhiPBT set
                        ChiPhiVatTu = %2$.0f,
                        ChiPhiNhanCong = %3$.0f,
                        ChiPhiDichVu = %4$.0f,
                        ChiPhiVatTuDK = %5$.0f,
                        ChiPhiNhanCongDK = %6$.0f,
                        ChiPhiDichVuDK = %7$.0f
                        where IFID_M759 = %1$d;'
            , $item->IFID_M759
            , $item->ChiPhiVatTu
            , $item->ChiPhiNhanCong
            , $item->ChiPhiDichVu
            , $item->ChiPhiVatTuDK
            , $item->ChiPhiNhanCongDK
            , $item->ChiPhiDichVuDK);
    }
    else
    {
        $sql = sprintf('INSERT INTO OChiPhiPBT(IFID_M759,ChiPhiVatTu,ChiPhiNhanCong,ChiPhiDichVu,ChiPhiVatTuDK,ChiPhiNhanCongDK,ChiPhiDichVuDK)
                        VALUES(%1$d,%2$.0f,%3$.0f,%4$.0f,%5$.0f,%6$.0f,%7$.0f);'
            , $item->IFID_M759
            , $item->ChiPhiVatTu
            , $item->ChiPhiNhanCong
            , $item->ChiPhiDichVu
            , $item->ChiPhiVatTuDK
            , $item->ChiPhiNhanCongDK
            , $item->ChiPhiDichVuDK);
    }
    //echo '<pre>';echo $sql;die;
    $db->execute($sql);
}