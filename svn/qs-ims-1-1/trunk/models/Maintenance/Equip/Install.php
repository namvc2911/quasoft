<?php
class Qss_Model_Maintenance_Equip_Install extends Qss_Model_Abstract
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Dem so luong dong cai dat theo mot mang thiet bi truyen vao
     * @param $equipIOIDs
     * @return mixed
     */
    public function countInstallLine($equipIOIDs)
    {
        $equipIOIDs[] = 0;

        $sql = sprintf('
            SELECT
                ThietBi.*
                , SUM(CASE WHEN IFNULL(CaiDat.IOID, 0) = 0 THEN 0 ELSE 1 END) AS Total
            FROM ODanhSachThietBi AS ThietBi
            LEFT JOIN OCaiDatDiChuyenThietBi AS CaiDat ON ThietBi.IOID = CaiDat.Ref_MaThietBi
            WHERE ThietBi.IOID IN (%1$s)
            GROUP BY ThietBi.IOID
        ', implode(', ', $equipIOIDs));
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getLastInstallBeforeDate($date, $equipIOIDs)
    {
        $equipIOIDs[] = 0;

        $sql = sprintf('
        SELECT *
        FROM
        (
            SELECT
                ThietBi.IOID AS ThietBiIOID
                , IF(ifnull(CaiDat.Ref_TrucThuoc, 0) != 0, CaiDat.Ref_TrucThuoc, ThietBi.Ref_TrucThuoc) AS Ref_TrucThuoc
                , IF(ifnull(CaiDat.IOID, 0) != 0, KhuVucCaiDat.IOID, KhuVucHienTai.IOID) AS Ref_KhuVuc
                , IF(ifnull(CaiDat.IOID, 0) != 0, DayChuyenCaiDat.IOID, DayChuyenHienTai.IOID) AS Ref_DayChuyen
                , IF(ifnull(CaiDat.IOID, 0) != 0, TrungTamChiPhiCaiDat.IOID, TrungTamChiPhiHienTai.IOID) AS Ref_TrungTamChiPhi
                , IF(ifnull(CaiDat.IOID, 0) != 0, NhanVienCaiDat.IOID, NhanVienHienTai.IOID) AS Ref_NhanVien
            FROM ODanhSachThietBi AS ThietBi
            LEFT JOIN OCaiDatDiChuyenThietBi AS CaiDat ON CaiDat.Ref_MaThietBi = ThietBi.IOID
            LEFT JOIN OKhuVuc AS KhuVucCaiDat ON ifnull(CaiDat.Ref_KhuVuc, 0) = KhuVucCaiDat.IOID
            LEFT JOIN OKhuVuc AS KhuVucHienTai ON ifnull(ThietBi.Ref_MaKhuVuc, 0) = KhuVucHienTai.IOID
            LEFT JOIN ODayChuyen AS DayChuyenCaiDat ON ifnull(CaiDat.Ref_DayChuyen, 0) = DayChuyenCaiDat.IOID
            LEFT JOIN ODayChuyen AS DayChuyenHienTai ON ifnull(ThietBi.Ref_DayChuyen, 0) = DayChuyenHienTai.IOID
            LEFT JOIN OTrungTamChiPhi AS TrungTamChiPhiCaiDat ON ifnull(CaiDat.Ref_TrungTamChiPhi, 0) = TrungTamChiPhiCaiDat.IOID
            LEFT JOIN OTrungTamChiPhi AS TrungTamChiPhiHienTai ON ifnull(ThietBi.Ref_TrungTamChiPhi, 0) = TrungTamChiPhiHienTai.IOID
            LEFT JOIN ODanhSachNhanVien AS NhanVienCaiDat ON IFNULL(CaiDat.Ref_QuanLy, 0) = NhanVienCaiDat.IOID
            LEFT JOIN ODanhSachNhanVien AS NhanVienHienTai ON IFNULL(ThietBi.Ref_QuanLy, 0) = NhanVienHienTai.IOID
            WHERE
                ThietBi.IOID in (%2$s)
                AND (ifnull(CaiDat.IOID, 0) = 0 or CaiDat.Ngay <= %1$s)
            ORDER BY CaiDat.Ngay DESC, ifnull(CaiDat.Gio, \'\') DESC    
            LIMIT 18446744073709551615
        ) AS Table1
        GROUP BY Table1.ThietBiIOID
         
        ', $this->_o_DB->quote($date), implode(', ', $equipIOIDs));
        return $this->_o_DB->fetchAll($sql);
    }

    public function getInstallHistory($equipIOID)
    {
        $sql = sprintf('
            SELECT
                -- Thiet bi cai dat
                CaiDat.IOID
                , CaiDat.IFID_M173
                , ThietBi.MaThietBi
                , ThietBi.TenThietBi
                , ThietBi.NgayDuaVaoSuDung
                , CaiDat.Ngay
                , CaiDat.Gio
                -- Khu vuc
                , IFNULL(KhuVucCaiDat.IOID, 0) AS Ref_KhuVuc
                , KhuVucCaiDat.MaKhuVuc
                , KhuVucCaiDat.Ten AS TenKhuVuc
                , IFNULL(KhuVucHienTai.IOID, 0) AS Ref_KhuVucTB
                , KhuVucHienTai.MaKhuVuc AS MaKhuVucTB
                , KhuVucHienTai.Ten AS TenKhuVucTB
                -- Day chuyen
                , IFNULL(DayChuyenCaiDat.IOID, 0) AS Ref_DayChuyen
                , DayChuyenCaiDat.MaDayChuyen
                , DayChuyenCaiDat.TenDayChuyen
                , IFNULL(DayChuyenHienTai.IOID, 0) AS Ref_DayChuyenTB
                , DayChuyenHienTai.MaDayChuyen AS MaDayChuyenTB
                , DayChuyenHienTai.TenDayChuyen AS TenDayChuyenTB
                -- Trung tam chi phi
                , IFNULL(TrungTamChiPhiCaiDat.IOID, 0) AS Ref_TrungTamChiPhi
                , TrungTamChiPhiCaiDat.Ma AS MaTrungTamChiPhi
                , TrungTamChiPhiCaiDat.Ten AS TenTrungTamChiPhi
                , IFNULL(TrungTamChiPhiHienTai.IOID, 0) AS Ref_TrungTamChiPhiTB
                , TrungTamChiPhiHienTai.Ma AS MaTrungTamChiPhiTB
                , TrungTamChiPhiHienTai.Ten AS TenTrungTamChiPhiTB
                -- Quan ly
                , IFNULL(NhanVienCaiDat.IOID, 0) AS Ref_NhanVien
                , NhanVienCaiDat.MaNhanVien
                , NhanVienCaiDat.TenNhanVien
                , IFNULL(NhanVienHienTai.IOID, 0) AS Ref_NhanVienTB
                , NhanVienHienTai.MaNhanVien AS MaNhanVienTB
                , NhanVienHienTai.TenNhanVien AS TenNhanVienTB
            FROM ODanhSachThietBi AS ThietBi
            LEFT JOIN OCaiDatDiChuyenThietBi AS CaiDat ON CaiDat.Ref_MaThietBi = ThietBi.IOID
            LEFT JOIN OKhuVuc AS KhuVucCaiDat ON ifnull(CaiDat.Ref_KhuVuc, 0) = KhuVucCaiDat.IOID
            LEFT JOIN OKhuVuc AS KhuVucHienTai ON ifnull(ThietBi.Ref_MaKhuVuc, 0) = KhuVucHienTai.IOID
            LEFT JOIN ODayChuyen AS DayChuyenCaiDat ON ifnull(CaiDat.Ref_DayChuyen, 0) = DayChuyenCaiDat.IOID
            LEFT JOIN ODayChuyen AS DayChuyenHienTai ON ifnull(ThietBi.Ref_DayChuyen, 0) = DayChuyenHienTai.IOID
            LEFT JOIN OTrungTamChiPhi AS TrungTamChiPhiCaiDat ON ifnull(CaiDat.Ref_TrungTamChiPhi, 0) = TrungTamChiPhiCaiDat.IOID
            LEFT JOIN OTrungTamChiPhi AS TrungTamChiPhiHienTai ON ifnull(ThietBi.Ref_TrungTamChiPhi, 0) = TrungTamChiPhiHienTai.IOID
            LEFT JOIN ODanhSachNhanVien AS NhanVienCaiDat ON IFNULL(CaiDat.Ref_QuanLy, 0) = NhanVienCaiDat.IOID
            LEFT JOIN ODanhSachNhanVien AS NhanVienHienTai ON IFNULL(ThietBi.Ref_QuanLy, 0) = NhanVienHienTai.IOID
            WHERE ThietBi.IOID = %1$d
            ORDER BY CaiDat.Ngay DESC, ifnull(CaiDat.Gio, \'\') DESC
        ', $equipIOID);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getLastInstall($equipIOID)
    {
        $sql = sprintf('
            SELECT
                -- Thiet bi cai dat
                CaiDat.IOID
                , CaiDat.IFID_M173
                , ThietBi.MaThietBi
                , ThietBi.TenThietBi
                , ThietBi.NgayDuaVaoSuDung
                , CaiDat.Ngay
                , CaiDat.Gio
                -- Khu vuc
                , IFNULL(KhuVucCaiDat.IOID, 0) AS Ref_KhuVuc
                , KhuVucCaiDat.MaKhuVuc
                , KhuVucCaiDat.Ten AS TenKhuVuc
                , IFNULL(KhuVucHienTai.IOID, 0) AS Ref_KhuVucTB
                , KhuVucHienTai.MaKhuVuc AS MaKhuVucTB
                , KhuVucHienTai.Ten AS TenKhuVucTB
                -- Day chuyen
                , IFNULL(DayChuyenCaiDat.IOID, 0) AS Ref_DayChuyen
                , DayChuyenCaiDat.MaDayChuyen
                , DayChuyenCaiDat.TenDayChuyen
                , IFNULL(DayChuyenHienTai.IOID, 0) AS Ref_DayChuyenTB
                , DayChuyenHienTai.MaDayChuyen AS MaDayChuyenTB
                , DayChuyenHienTai.TenDayChuyen AS TenDayChuyenTB
                -- Trung tam chi phi
                , IFNULL(TrungTamChiPhiCaiDat.IOID, 0) AS Ref_TrungTamChiPhi
                , TrungTamChiPhiCaiDat.Ma AS MaTrungTamChiPhi
                , TrungTamChiPhiCaiDat.Ten AS TenTrungTamChiPhi
                , IFNULL(TrungTamChiPhiHienTai.IOID, 0) AS Ref_TrungTamChiPhiTB
                , TrungTamChiPhiHienTai.Ma AS MaTrungTamChiPhiTB
                , TrungTamChiPhiHienTai.Ten AS TenTrungTamChiPhiTB
                -- Quan ly
                , IFNULL(NhanVienCaiDat.IOID, 0) AS Ref_NhanVien
                , NhanVienCaiDat.MaNhanVien
                , NhanVienCaiDat.TenNhanVien
                , IFNULL(NhanVienHienTai.IOID, 0) AS Ref_NhanVienTB
                , NhanVienHienTai.MaNhanVien AS MaNhanVienTB
                , NhanVienHienTai.TenNhanVien AS TenNhanVienTB
            FROM OCaiDatDiChuyenThietBi AS CaiDat
            INNER JOIN ODanhSachThietBi AS ThietBi ON CaiDat.Ref_MaThietBi = ThietBi.IOID
            LEFT JOIN OKhuVuc AS KhuVucCaiDat ON ifnull(CaiDat.Ref_KhuVuc, 0) = KhuVucCaiDat.IOID
            LEFT JOIN OKhuVuc AS KhuVucHienTai ON ifnull(ThietBi.Ref_MaKhuVuc, 0) = KhuVucHienTai.IOID
            LEFT JOIN ODayChuyen AS DayChuyenCaiDat ON ifnull(CaiDat.Ref_DayChuyen, 0) = DayChuyenCaiDat.IOID
            LEFT JOIN ODayChuyen AS DayChuyenHienTai ON ifnull(ThietBi.Ref_DayChuyen, 0) = DayChuyenHienTai.IOID
            LEFT JOIN OTrungTamChiPhi AS TrungTamChiPhiCaiDat ON ifnull(CaiDat.Ref_TrungTamChiPhi, 0) = TrungTamChiPhiCaiDat.IOID
            LEFT JOIN OTrungTamChiPhi AS TrungTamChiPhiHienTai ON ifnull(ThietBi.Ref_TrungTamChiPhi, 0) = TrungTamChiPhiHienTai.IOID
            LEFT JOIN ODanhSachNhanVien AS NhanVienCaiDat ON IFNULL(CaiDat.Ref_QuanLy, 0) = NhanVienCaiDat.IOID
            LEFT JOIN ODanhSachNhanVien AS NhanVienHienTai ON IFNULL(ThietBi.Ref_QuanLy, 0) = NhanVienHienTai.IOID
            WHERE ThietBi.IOID = %1$d
            ORDER BY CaiDat.Ngay DESC, ifnull(CaiDat.Gio, \'\') DESC
            LIMIT 1
        ', $equipIOID);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchOne($sql);
    }
}