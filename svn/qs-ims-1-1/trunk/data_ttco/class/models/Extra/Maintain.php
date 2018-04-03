<?php
class Qss_Model_Extra_Maintain extends Qss_Model_Abstract
{
    const B1 = 'B1'; // IFID loai bao tri B1
    const B2 = 'B2'; // IFID loai bao tri B2
    const TT = 'TT'; // IFID loai bao tri TT
    const DX = 'DX'; // Loại bảo trì đọt xuất

    const BD125H  = 125;
    const BD250H  = 250;
    const BD500H  = 500;
    const BD1000H = 1000;
    const BD2000H = 1000;

    const DUNG_MAY_CHE_DO   = 'CD';
    const DUNG_MAY_HONG_MAY = 'HM';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param $startDate
     * @param $endDate
     * @param int $locationIOID
     * @param int $equipGroupIOID
     * @param int $equipTypeIOID
     * @param int $equipIOID
     * @return mixed
     */
    public function getThoiGianHoatDongCuaThietBi(
        $startDate
        , $endDate
        , $locationIOID = 0
        , $equipGroupIOID = 0
        , $equipTypeIOID = 0
        , $equipIOID = 0)
    {
        $where    = '';
        $locName  = $locationIOID?$this->_o_DB->fetchOne(sprintf('select * from OKhuVuc where IOID = %1$d', @(int)$locationIOID)):false;
        $where   .= ($locName)?sprintf(' AND (ThietBi.Ref_MaKhuVuc IN  (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d)) ',  $locName->lft, $locName->rgt):'';
        $where   .= ($equipGroupIOID)?sprintf(' AND ThietBi.Ref_NhomThietBi = %1$d ', $equipGroupIOID):'';
        $eqTypes  = $equipTypeIOID?$this->_o_DB->fetchOne(sprintf('select * from OLoaiThietBi where IOID = %1$d', @(int)$equipTypeIOID)):false;
        $where   .= ($eqTypes)?sprintf(' AND (ThietBi.Ref_LoaiThietBi IN  (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d)) ',  $eqTypes->lft, $eqTypes->rgt):'';
        $where   .= ($equipIOID)?sprintf(' AND ThietBi.IOID = %1$d ', $equipIOID):'';

        $mDept    = new Qss_Model_Admin_Department();
        $rDept    = array($this->_user->user_dept_id);
        $dept     = $mDept->getChildDepartments($this->_user->user_dept_id);

        foreach($dept as $item)
        {
            $rDept[] = $item->DepartmentID;
        }

        // echo '<pre>'; print_r($rDept); die;

        $sql = sprintf('
            SELECT
                ThietBi.*
                , NhatTrinh.Ref_Ca
                , NhatTrinh.SoHoatDong
                , NhatTrinh.Ngay AS NgayNhatTrinh
                , NhomThietBi.IFID_M704 AS IFIDLoaiThietBi
                , NhomThietBi.LoaiThietBi AS MaLoaiThietBi
                , NhomThietBi.MoTa AS TenLoaiThietBi
            FROM ODanhSachThietBi AS ThietBi
            INNER JOIN ONhomThietBi AS NhomThietBi ON ThietBi.Ref_NhomThietBi = NhomThietBi.IOID
            LEFT JOIN ONhatTrinhThietBi AS NhatTrinh ON NhatTrinh.Ref_MaTB = ThietBi.IOID
                AND IFNULL(NhatTrinh.Ref_BoPhan, 0) = 0
                AND (NhatTrinh.Ngay BETWEEN %1$s AND %2$s)
            WHERE
                 -- ThietBi.DeptID in (%4$s)AND
                ThietBi.DeptID in (%4$s)
                AND IFNULL(ThietBi.Ref_TrucThuoc, 0) = 0
                %3$s
            ORDER BY NhomThietBi.lft,  CAST(RIGHT(ThietBi.MaThietBi, 4) AS UNSIGNED)'
            , $this->_o_DB->quote($startDate)
            , $this->_o_DB->quote($endDate)
            , $where
            , implode(', ', $rDept)
        );
        // secho '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * @note: Query có sử dụng giá trị cố định
     * @todo: Gio theo quy dinh, Gia tri thuc hien co can tinh theo  ba loai b1, b2. tt ko?
     * @todo: Ttco khong su dung quy trinh trong phieu bao tri co the them vao sau
     * @todo: Chua loc thiet bi theo user va hoat dong
     * @param $start
     * @param $end
     * @param int $locationIOID
     * @param int $equipGroupIOID
     * @param int $equipTypeIOID
     * @param int $equipIOID
     * @return mixed
     */
    public function getThucHienKeHoachVanHanhSuaChua(
        $start
        , $end
        , $locationIOID = 0
        , $equipGroupIOID = 0
        , $equipTypeIOID = 0
        , $equipIOID = 0)
    {
        $where  = '';
        $where .= sprintf(' AND ThietBi.DeptID IN (%1$s) ', $this->_user->user_dept_list);
        $where .= sprintf(' AND IFNULL(ThietBi.Ref_TrucThuoc, 0) = 0 ');
        $loc    = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locationIOID));
        $where .= $loc?sprintf(' AND (ThietBi.Ref_MaKhuVuc IN (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))', $loc->lft, $loc->rgt):'';
        $type   = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OLoaiThietBi WHERE IOID = %1$d', $equipTypeIOID));
        $where .= $type?sprintf(' AND (ThietBi.Ref_LoaiThietBi IN (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d))', $type->lft, $type->rgt):'';
        $where .= ($equipGroupIOID)?sprintf(' AND ThietBi.Ref_NhomThietBi = %1$d  ', $equipGroupIOID):'';
        $where .= ($equipIOID)?sprintf(' AND ThietBi.IOID = %1$d  ', $equipIOID):'';

        $sql = sprintf('
            SELECT
                ThietBi.TenThietBi
                , ThietBi.LoaiThietBi
                , NhomThietBi.LoaiThietBi AS MaNhomThietBi
                , NhomThietBi.MoTa AS TenNhomThietBi
                , NhomThietBi.IFID_M704
                , IFNULL(ChuKyLoaiThietBi.QuyDinhBaoDuongB1, 0) AS QuyDinhBaoDuongB1
                , IFNULL(ChuKyLoaiThietBi.QuyDinhBaoDuongB2, 0) AS QuyDinhBaoDuongB2
                , IFNULL(ChuKyLoaiThietBi.QuyDinhBaoDuongTT, 0) AS QuyDinhBaoDuongTT
                , IFNULL(NhatTrinhLuyKe.GioHoatDongTrongKy, 0) AS GioHoatDongTrongKy
                , IFNULL(NhatTrinhLuyKe.GioHoatDongLuyKe, 0) AS GioHoatDongLuyKe
                , IFNULL(SoLanPhieuBaoTri.SoLanBaoTriB1, 0) AS SoLanBaoTriB1
                , IFNULL(SoLanPhieuBaoTri.SoLanBaoTriB2, 0) AS SoLanBaoTriB2
                , IFNULL(SoLanPhieuBaoTri.SoLanBaoTriTT, 0) AS SoLanBaoTriTT
                , IFNULL(SoCongBaoTri.SoCong, 0) AS SoCong
                , IFNULL(ChiPhiBaoTri.ChiPhiNhanCong, 0) AS ChiPhiNhanCong
                , IFNULL(ChiPhiBaoTri.ChiPhiVatTu, 0) AS ChiPhiVatTu
            FROM ODanhSachThietBi AS ThietBi
            INNER JOIN ONhomThietBi AS NhomThietBi ON  ThietBi.Ref_NhomThietBi = NhomThietBi.IOID

            LEFT JOIN
            (
                SELECT
                    ChuKyLoaiThietBi.IFID_M770
                    , ChuKyLoaiThietBi.LoaiThietBiIOID
                    , GROUP_CONCAT( IF(ChuKyLoaiThietBi.Loai = %3$s, ChuKyLoaiThietBi.SoGio, null) separator \',\') AS QuyDinhBaoDuongB1
                    , GROUP_CONCAT( IF(ChuKyLoaiThietBi.Loai = %4$s, ChuKyLoaiThietBi.SoGio, null) separator \',\') AS QuyDinhBaoDuongB2
                    , GROUP_CONCAT( IF(ChuKyLoaiThietBi.Loai = %5$s, ChuKyLoaiThietBi.SoGio, null) separator \',\') AS QuyDinhBaoDuongTT
                FROM
                (
                    SELECT
                        LoaiThietBi.IFID_M770
                        , LoaiThietBi.IOID AS LoaiThietBiIOID
                        , LoaiBaoTri.Loai
                        , ChuKy.SoGio
                    FROM OLoaiThietBi AS LoaiThietBi
                    INNER JOIN OChuKyLoaiThietBi AS ChuKy ON LoaiThietBi.IFID_M770 = ChuKy.IFID_M770
                    INNER JOIN OPhanLoaiBaoTri AS LoaiBaoTri ON ChuKy.Ref_LoaiBaoTri = LoaiBaoTri.IOID
                    ORDER BY LoaiThietBi.IFID_M770, LoaiBaoTri.IOID, ChuKy.SoGio
                ) AS ChuKyLoaiThietBi
                GROUP BY ChuKyLoaiThietBi.IFID_M770
            ) AS ChuKyLoaiThietBi ON IFNULL(ThietBi.Ref_LoaiThietBi, 0) = ChuKyLoaiThietBi.LoaiThietBiIOID

            LEFT JOIN
            (
                SELECT
                    NhatTrinh.Ref_MaTB
                    , sum( IFNULL(NhatTrinh.SoHoatDong, 0) ) AS GioHoatDongLuyKe
                    , sum( IF((NhatTrinh.Ngay BETWEEN %1$s AND %2$s), IFNULL(NhatTrinh.SoHoatDong, 0), 0)) AS GioHoatDongTrongKy
                FROM ONhatTrinhThietBi AS NhatTrinh
                INNER JOIN OChiSoMayMoc AS ChiSo On NhatTrinh.Ref_ChiSo = ChiSo.IOID
                WHERE  IFNULL(ChiSo.Gio, 0) != 0 AND NhatTrinh.Ngay <= %2$s
                GROUP BY NhatTrinh.Ref_MaTB
            ) AS NhatTrinhLuyKe ON ThietBi.IOID = NhatTrinhLuyKe.Ref_MaTB

            LEFT JOIN
            (
                SELECT
                    PhieuBaoTri.Ref_MaThietBi
                    , SUM(CASE WHEN LoaiBaoTri.Loai = %3$s THEN 1 ELSE 0 END) AS SoLanBaoTriB1
                    , SUM(CASE WHEN LoaiBaoTri.Loai = %4$s THEN 1 ELSE 0 END) AS SoLanBaoTriB2
                    , SUM(CASE WHEN LoaiBaoTri.Loai = %5$s THEN 1 ELSE 0 END) AS SoLanBaoTriTT
                FROM OPhieuBaoTri AS PhieuBaoTri
                INNER JOIN OPhanLoaiBaoTri AS LoaiBaoTri ON PhieuBaoTri.Ref_LoaiBaoTri = LoaiBaoTri.IOID
                INNER JOIN qsiforms ON PhieuBaoTri.IFID_M759 = qsiforms.IFID
                WHERE qsiforms.Status in (3, 4)
                    AND (PhieuBaoTri.NgayBatDau BETWEEN %1$s AND %2$s)
                    AND LoaiBaoTri.Loai IN (%3$s, %4$s, %5$s)
                GROUP BY PhieuBaoTri.Ref_MaThietBi
            ) AS SoLanPhieuBaoTri ON ThietBi.IOID = SoLanPhieuBaoTri.Ref_MaThietBi

            LEFT JOIN
            (
                SELECT
                    PhieuBaoTri.Ref_MaThietBi
                    , SUM(IFNULL(CongViec.NhanCong, 0)) AS SoCong
                FROM OPhieuBaoTri AS PhieuBaoTri
                INNER JOIN OPhanLoaiBaoTri AS LoaiBaoTri ON PhieuBaoTri.Ref_LoaiBaoTri = LoaiBaoTri.IOID
                INNER JOIN OCongViecBTPBT AS CongViec ON PhieuBaoTri.IFID_M759 = CongViec.IFID_M759
                INNER JOIN qsiforms ON PhieuBaoTri.IFID_M759 = qsiforms.IFID
                WHERE qsiforms.Status in (3, 4) AND (PhieuBaoTri.NgayBatDau BETWEEN %1$s AND %2$s)
                    AND LoaiBaoTri.Loai IN (%3$s, %4$s, %5$s)
                GROUP BY PhieuBaoTri.Ref_MaThietBi
            ) AS SoCongBaoTri ON ThietBi.IOID = SoCongBaoTri.Ref_MaThietBi

            LEFT JOIN
            (
                SELECT
                    PhieuBaoTri.Ref_MaThietBi
                    , SUM(IFNULL(ChiPhi.ChiPhiNhanCong, 0)) AS ChiPhiNhanCong
                    , SUM(IFNULL(ChiPhi.ChiPhiVatTu, 0)) AS ChiPhiVatTu
                FROM OPhieuBaoTri AS PhieuBaoTri
                INNER JOIN OPhanLoaiBaoTri AS LoaiBaoTri ON PhieuBaoTri.Ref_LoaiBaoTri = LoaiBaoTri.IOID
                INNER JOIN OChiPhiPBT AS ChiPhi ON PhieuBaoTri.IFID_M759 = ChiPhi.IFID_M759
                INNER JOIN qsiforms ON PhieuBaoTri.IFID_M759 = qsiforms.IFID
                WHERE qsiforms.Status in (3, 4) AND (PhieuBaoTri.NgayBatDau BETWEEN %1$s AND %2$s)
                    AND LoaiBaoTri.Loai IN (%3$s, %4$s, %5$s)
                GROUP BY PhieuBaoTri.Ref_MaThietBi
            ) AS ChiPhiBaoTri ON ThietBi.IOID = ChiPhiBaoTri.Ref_MaThietBi
            WHERE 1=1 %7$s
            ORDER BY NhomThietBi.lft, CAST(RIGHT(ThietBi.MaThietBi, 4) AS UNSIGNED)
        '
        , $this->_o_DB->quote($start)
        , $this->_o_DB->quote($end)
        , $this->_o_DB->quote(Qss_Model_Extra_Maintain::B1)
        , $this->_o_DB->quote(Qss_Model_Extra_Maintain::B2)
        , $this->_o_DB->quote(Qss_Model_Extra_Maintain::TT)
        , $this->_user->user_dept_list
        , $where);

        //echo '<pre>'; print_r($sql); die;

        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * Lay thuc hien sua chua thuong xuyen theo loai thiet bi
     *
     * @use-in: M827
     * @todo: Can xet den ca truong hop thiet bi co truc thuoc
     * @todo: Chua loc thiet bi theo user va hoat dong
     * @param $start
     * @param $end
     * @param int $locationIOID
     * @param int $equipGroupIOID
     * @param int $equipTypeIOID
     * @param int $equipIOID
     * @return mixed
     */
    public function getThucHienSuaChuaThuongXuyen(
        $start
        , $end
        , $locationIOID = 0
        , $equipGroupIOID = 0
        , $equipTypeIOID = 0
        , $equipIOID = 0)
    {
        $mDept    = new Qss_Model_Admin_Department();
        $rDept    = array($this->_user->user_dept_id);
        $dept     = $mDept->getChildDepartments($this->_user->user_dept_id);

        foreach($dept as $item)
        {
            $rDept[] = $item->DepartmentID;
        }

        $where  = '';
        $where .= sprintf(' AND ThietBi.DeptID IN (%1$s) ', implode(', ', $rDept));
        $where .= sprintf(' AND IFNULL(ThietBi.Ref_TrucThuoc, 0) = 0 ');
        $where .= sprintf(' AND IFNULL(ThietBi.Ref_TrucThuoc, 0) = 0 ');
        $loc    = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locationIOID));
        $where .= $loc?sprintf(' AND (ThietBi.Ref_MaKhuVuc IN (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))', $loc->lft, $loc->rgt):'';
        $type   = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OLoaiThietBi WHERE IOID = %1$d', $equipTypeIOID));
        $where .= $type?sprintf(' AND (ThietBi.Ref_LoaiThietBi IN (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d))', $type->lft, $type->rgt):'';
        $where .= ($equipGroupIOID)?sprintf(' AND ThietBi.Ref_NhomThietBi = %1$d  ', $equipGroupIOID):'';
        $where .= ($equipIOID)?sprintf(' AND ThietBi.IOID = %1$d  ', $equipIOID):'';


        $sql = sprintf('
            SELECT
                NhomThietBi.MoTa AS TenLoai
                , IFNULL(DemNhomThietBi.CountEquip, 0) AS CountEquip
                , IFNULL(DemBaoTri.BD125H, 0) AS BD125H
                , IFNULL(DemBaoTri.BD250H, 0) AS BD250H
                , IFNULL(DemBaoTri.BD500H, 0) AS BD500H
                , IFNULL(DemBaoTri.BD1000H, 0) AS BD1000H
                , IFNULL(DemBaoTri.BD2000H, 0) AS BD2000H
                , IFNULL(DemBaoTri.TieuTu, 0) AS TieuTu
                , IFNULL(DemBaoTri.DotXuat, 0) AS DotXuat
                , IFNULL(ChiPhiBaoTri.ChiPhiNhanCong, 0) AS ChiPhiNhanCong
                , IFNULL(ChiPhiBaoTri.ChiPhiVatTu, 0) AS ChiPhiVatTu
                , (IFNULL(ChiPhiBaoTri.ChiPhiVatTu, 0) + IFNULL(ChiPhiBaoTri.ChiPhiNhanCong, 0)) AS ChiPhi
            FROM ONhomThietBi AS NhomThietBi

            -- Dem so luong thiet bi theo loai thiet bi
            LEFT JOIN
            (
                SELECT NhomThietBi.IOID AS NhomThietBiIOID, count(1) AS CountEquip
                FROM ONhomThietBi AS NhomThietBi
                INNER JOIN ODanhSachThietBi AS ThietBi ON NhomThietBi.IOID = ThietBi.Ref_NhomThietBi
                WHERE 1=1 %12$s
                GROUP BY NhomThietBi.IOID
            ) AS DemNhomThietBi ON NhomThietBi.IOID = DemNhomThietBi.NhomThietBiIOID


            -- Dem so luong bao duong theo gio theo chu ky
            LEFT JOIN
            (
                SELECT
                    NhomThietBi.IOID AS NhomThietBiIOID
                    , SUM(CASE WHEN ChuKy.GiaTri > 0 AND ChuKy.GiaTri <= %3$d THEN 1 ELSE 0 END) AS BD125H
                    , SUM(CASE WHEN ChuKy.GiaTri > %3$d AND ChuKy.GiaTri <= %4$d THEN 1 ELSE 0 END) AS BD250H
                    , SUM(CASE WHEN ChuKy.GiaTri > %4$d AND ChuKy.GiaTri <= %5$d THEN 1 ELSE 0 END) AS BD500H
                    , SUM(CASE WHEN ChuKy.GiaTri > %5$d AND ChuKy.GiaTri <= %6$d THEN 1 ELSE 0 END) AS BD1000H
                    , SUM(CASE WHEN ChuKy.GiaTri > %6$d AND ChuKy.GiaTri <= %7$d THEN 1 ELSE 0 END) AS BD2000H
                    , SUM(CASE WHEN LoaiBaoTri.Loai = %8$s THEN 1 ELSE 0 END) AS TieuTu
                    , SUM(CASE WHEN LoaiBaoTri.Loai = %9$s THEN 1 ELSE 0 END) AS DotXuat
                FROM OPhieuBaoTri AS PhieuBaoTri
                INNER JOIN qsiforms ON PhieuBaoTri.IFID_M759 = qsiforms.IFID

                INNER JOIN ODanhSachThietBi AS ThietBi ON PhieuBaoTri.Ref_MaThietBi = ThietBi.IOID
                INNER JOIN ONhomThietBi AS NhomThietBi ON ThietBi.Ref_NhomThietBi = NhomThietBi.IOID

                LEFT JOIN OChuKyBaoTri AS ChuKy On IFNULL(PhieuBaoTri.Ref_ChuKy, 0) = ChuKy.IOID
                LEFT JOIN OPhanLoaiBaoTri AS LoaiBaoTri ON PhieuBaoTri.Ref_LoaiBaoTri = LoaiBaoTri.IOID

                WHERE
                    qsiforms.Status in (3, 4)
                    AND LoaiBaoTri.Loai IN (%8$s, %9$s, %10$s, %11$s)
                    AND (PhieuBaoTri.NgayBatDau BETWEEN %1$s AND %2$s)
                    %12$s
                GROUP BY NhomThietBi.IOID
            ) AS DemBaoTri ON NhomThietBi.IOID = DemBaoTri.NhomThietBiIOID

            -- Chi phi bao tri
            LEFT JOIN
            (
                SELECT
                    NhomThietBi.IOID AS NhomThietBiIOID
                    , SUM(IFNULL(ChiPhi.ChiPhiNhanCong, 0)) AS ChiPhiNhanCong
                    , SUM(IFNULL(ChiPhi.ChiPhiVatTu, 0)) AS ChiPhiVatTu
                FROM OPhieuBaoTri AS PhieuBaoTri
                INNER JOIN qsiforms ON PhieuBaoTri.IFID_M759 = qsiforms.IFID
                INNER JOIN OPhanLoaiBaoTri AS LoaiBaoTri ON PhieuBaoTri.Ref_LoaiBaoTri = LoaiBaoTri.IOID
                INNER JOIN OChiPhiPBT AS ChiPhi ON PhieuBaoTri.IFID_M759 = ChiPhi.IFID_M759
                INNER JOIN ODanhSachThietBi AS ThietBi ON PhieuBaoTri.Ref_MaThietBi = ThietBi.IOID
                INNER JOIN ONhomThietBi AS NhomThietBi ON ThietBi.Ref_NhomThietBi = NhomThietBi.IOID
                WHERE
                    qsiforms.Status in (3, 4)
                    AND (PhieuBaoTri.NgayBatDau BETWEEN %1$s AND %2$s)
                    AND LoaiBaoTri.Loai IN (%8$s, %9$s, %10$s, %11$s)
                    %12$s
                GROUP BY NhomThietBi.IOID
            ) AS ChiPhiBaoTri ON NhomThietBi.IOID = ChiPhiBaoTri.NhomThietBiIOID

            WHERE IFNULL(DemNhomThietBi.CountEquip, 0) > 0

            ORDER BY NhomThietBi.lft
        '
        , $this->_o_DB->quote($start)
        , $this->_o_DB->quote($end)
        , self::BD125H
        , self::BD250H
        , self::BD500H
        , self::BD1000H
        , self::BD2000H
        , $this->_o_DB->quote(Qss_Model_Extra_Maintain::TT)
        , $this->_o_DB->quote(Qss_Model_Extra_Maintain::DX)
        , $this->_o_DB->quote(Qss_Model_Extra_Maintain::B1)
        , $this->_o_DB->quote(Qss_Model_Extra_Maintain::B2)
        , $where);

        // echo '<pre>'; print_r($sql); die;

        return $this->_o_DB->fetchAll($sql);
    }


    /**
     * Lay thuc hien sua chua thuong xuyen theo loai thiet bi
     * @todo: Can xet den ca truong hop thiet bi co truc thuoc
     * @todo: Chua loc thiet bi theo user va hoat dong
     * @param $start
     * @param $end
     * @param int $locationIOID
     * @param int $equipGroupIOID
     * @param int $equipTypeIOID
     * @param int $equipIOID
     * @return mixed
     */
    public function getThucHienKeHoachSuaChuaThuongXuyen(
        $start
        , $end
        , $locationIOID = 0
        , $equipGroupIOID = 0
        , $equipTypeIOID = 0
        , $equipIOID = 0)
    {
        $mDept    = new Qss_Model_Admin_Department();
        $rDept    = array($this->_user->user_dept_id);
        $dept     = $mDept->getChildDepartments($this->_user->user_dept_id);

        foreach($dept as $item)
        {
            $rDept[] = $item->DepartmentID;
        }

        $where  = '';
        $where .= sprintf(' AND ThietBi.DeptID IN (%1$s) ', implode(', ', $rDept));
        $where .= sprintf(' AND IFNULL(ThietBi.Ref_TrucThuoc, 0) = 0 ');
        $loc    = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locationIOID));
        $where .= $loc?sprintf(' AND (ThietBi.Ref_MaKhuVuc IN (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))', $loc->lft, $loc->rgt):'';
        $type   = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OLoaiThietBi WHERE IOID = %1$d', $equipTypeIOID));
        $where .= $type?sprintf(' AND (ThietBi.Ref_LoaiThietBi IN (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d))', $type->lft, $type->rgt):'';
        $where .= ($equipGroupIOID)?sprintf(' AND ThietBi.Ref_NhomThietBi = %1$d  ', $equipGroupIOID):'';
        $where .= ($equipIOID)?sprintf(' AND ThietBi.IOID = %1$d  ', $equipIOID):'';

        $sql = sprintf('
            SELECT
                NhomThietBi.MoTa AS TenLoai
                , IFNULL(DemNhomThietBi.CountEquip, 0) AS CountEquip
                , IFNULL(DemBaoTri.TieuTu, 0) AS SoLanBaoTriTT
                , IFNULL(DemBaoTri.B1, 0) AS SoLanBaoTriB1
                , IFNULL(DemBaoTri.B2, 0) AS SoLanBaoTriB2
                , IFNULL(ChiPhiBaoTri.ChiPhiNhanCong, 0) AS ChiPhiNhanCong
                , IFNULL(ChiPhiBaoTri.ChiPhiVatTu, 0) AS ChiPhiVatTu
                , (IFNULL(ChiPhiBaoTri.ChiPhiVatTu, 0) + IFNULL(ChiPhiBaoTri.ChiPhiNhanCong, 0)) AS ChiPhi
            FROM ONhomThietBi AS NhomThietBi

            -- Dem so luong thiet bi theo loai thiet bi
            LEFT JOIN
            (
                SELECT NhomThietBi.IOID AS NhomThietBiIOID, count(1) AS CountEquip
                FROM ONhomThietBi AS NhomThietBi
                INNER JOIN ODanhSachThietBi AS ThietBi ON NhomThietBi.IOID = ThietBi.Ref_NhomThietBi
                WHERE 1=1 %12$s
                GROUP BY NhomThietBi.IOID
            ) AS DemNhomThietBi ON NhomThietBi.IOID = DemNhomThietBi.NhomThietBiIOID

            -- Dem so luong bao duong theo gio theo chu ky
            LEFT JOIN
            (
                SELECT
                    NhomThietBi.IOID AS NhomThietBiIOID
                    , SUM(CASE WHEN LoaiBaoTri.Loai = %8$s THEN 1 ELSE 0 END) AS TieuTu
                    , SUM(CASE WHEN LoaiBaoTri.Loai = %10$s THEN 1 ELSE 0 END) AS B1
                    , SUM(CASE WHEN LoaiBaoTri.Loai = %11$s THEN 1 ELSE 0 END) AS B2
                FROM OPhieuBaoTri AS PhieuBaoTri
                INNER JOIN qsiforms ON PhieuBaoTri.IFID_M759 = qsiforms.IFID
                INNER JOIN OPhanLoaiBaoTri AS LoaiBaoTri ON PhieuBaoTri.Ref_LoaiBaoTri = LoaiBaoTri.IOID
                INNER JOIN ODanhSachThietBi AS ThietBi ON PhieuBaoTri.Ref_MaThietBi = ThietBi.IOID
                INNER JOIN ONhomThietBi AS NhomThietBi ON ThietBi.Ref_NhomThietBi = NhomThietBi.IOID
                WHERE
                    qsiforms.Status in (3, 4)
                    AND LoaiBaoTri.Loai IN (%8$s, %10$s, %11$s)
                    AND (PhieuBaoTri.NgayBatDau BETWEEN %1$s AND %2$s)
                    %12$s
                GROUP BY NhomThietBi.IOID
            ) AS DemBaoTri ON NhomThietBi.IOID = DemBaoTri.NhomThietBiIOID

            -- Chi phi bao tri
            LEFT JOIN
            (
                SELECT
                    NhomThietBi.IOID AS NhomThietBiIOID
                    , SUM(IFNULL(ChiPhi.ChiPhiNhanCong, 0)) AS ChiPhiNhanCong
                    , SUM(IFNULL(ChiPhi.ChiPhiVatTu, 0)) AS ChiPhiVatTu
                FROM OPhieuBaoTri AS PhieuBaoTri
                INNER JOIN qsiforms ON PhieuBaoTri.IFID_M759 = qsiforms.IFID
                INNER JOIN OPhanLoaiBaoTri AS LoaiBaoTri ON PhieuBaoTri.Ref_LoaiBaoTri = LoaiBaoTri.IOID
                INNER JOIN OChiPhiPBT AS ChiPhi ON PhieuBaoTri.IFID_M759 = ChiPhi.IFID_M759
                INNER JOIN ODanhSachThietBi AS ThietBi ON PhieuBaoTri.Ref_MaThietBi = ThietBi.IOID
                INNER JOIN ONhomThietBi AS NhomThietBi ON ThietBi.Ref_NhomThietBi = NhomThietBi.IOID
                WHERE qsiforms.Status in (3, 4)
                    AND (PhieuBaoTri.NgayBatDau BETWEEN %1$s AND %2$s)
                    AND LoaiBaoTri.Loai IN (%8$s, %10$s, %11$s)
                    %12$s
                GROUP BY NhomThietBi.IOID
            ) AS ChiPhiBaoTri ON NhomThietBi.IOID = ChiPhiBaoTri.NhomThietBiIOID

            WHERE IFNULL(DemNhomThietBi.CountEquip, 0) > 0


            ORDER BY NhomThietBi.lft
        '
            , $this->_o_DB->quote($start)
            , $this->_o_DB->quote($end)
            , self::BD125H
            , self::BD250H
            , self::BD500H
            , self::BD1000H
            , self::BD2000H
            , $this->_o_DB->quote(Qss_Model_Extra_Maintain::TT)
            , $this->_o_DB->quote(Qss_Model_Extra_Maintain::DX)
            , $this->_o_DB->quote(Qss_Model_Extra_Maintain::B1)
            , $this->_o_DB->quote(Qss_Model_Extra_Maintain::B2)
            , $where);

        // echo '<pre>'; print_r($sql); die;

        return $this->_o_DB->fetchAll($sql);
    }


    public function getSoLuongNangLucHoatDongThietBi(
        $start
        , $end
        , $locationIOID = 0
        , $equipGroupIOID = 0
        , $equipTypeIOID = 0
        , $equipIOID = 0)
    {
        $mDept    = new Qss_Model_Admin_Department();
        $rDept    = array($this->_user->user_dept_id);
        $dept     = $mDept->getChildDepartments($this->_user->user_dept_id);

        foreach($dept as $item)
        {
            $rDept[] = $item->DepartmentID;
        }

        $where  = '';
        $where .= sprintf(' AND ThietBi.DeptID IN (%1$s) ', implode(', ', $rDept));
        $where .= sprintf(' AND IFNULL(ThietBi.Ref_TrucThuoc, 0) = 0 ');
        $loc    = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locationIOID));
        $where .= $loc?sprintf(' AND (ThietBi.Ref_MaKhuVuc IN (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))', $loc->lft, $loc->rgt):'';
        $type   = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OLoaiThietBi WHERE IOID = %1$d', $equipTypeIOID));
        $where .= $type?sprintf(' AND (ThietBi.Ref_LoaiThietBi IN (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d))', $type->lft, $type->rgt):'';
        $where .= ($equipGroupIOID)?sprintf(' AND ThietBi.Ref_NhomThietBi = %1$d  ', $equipGroupIOID):'';
        $where .= ($equipIOID)?sprintf(' AND ThietBi.IOID = %1$d  ', $equipIOID):'';

        $sql = sprintf('
            SELECT
                ThietBi.MaThietBi
                , ThietBi.TenThietBi
                , NhomThietBi.MoTa AS TenNhomThietBi
                , IFNull(NhomThietBi.IOID, 0) AS Ref_NhomThietBi
                , IFNULL(ThoiGianHoatDong.ThoiGianHoatDong, 0) AS ThoiGianHoatDong
                , IFNULL(SoCa.SoCaMax, 0) AS SoCa
                , IFNULL(DungMay.DungMayCheDo, 0) AS DungMayCheDo
                , IFNULL(DungMay.DungMayHongMay, 0) AS DungMayHongMay
            FROM ODanhSachThietBi AS ThietBi
            INNER JOIN ONhomThietBi AS NhomThietBi On ThietBi.Ref_NhomThietBi = NhomThietBi.IOID

            -- Thoi gian lam viec
            LEFT JOIN
            (
                SELECT NhatTrinh.Ref_MaTB, SUM( IFNULL(NhatTrinh.SoHoatDong, 0) ) AS ThoiGianHoatDong
                FROM ONhatTrinhThietBi AS NhatTrinh
                INNER JOIN ODanhSachThietBi AS ThietBi ON NhatTrinh.Ref_MaTB = ThietBi.IOID
                INNER JOIN OChiSoMayMoc AS ChiSo ON NhatTrinh.Ref_ChiSo = ChiSo.IOID
                WHERE
                    (NhatTrinh.Ngay BETWEEN %1$s AND %2$s)
                    AND IFNULL(ChiSo.Gio, 0) != 0
                    AND IFNULL(NhatTrinh.Ref_BoPhan, 0) = 0
                GROUP BY NhatTrinh.Ref_MaTB
            ) AS ThoiGianHoatDong ON ThietBi.IOID = ThoiGianHoatDong.Ref_MaTB

            -- So Ca may
            LEFT JOIN
            (
                SELECT
                      ThietBi.IOID,
                      GREATEST(
                          IF(IFNULL(LichNgay2.Ref_Shift1,0), 1, 0) +
                          IF(IFNULL(LichNgay2.Ref_Shift2,0), 1, 0) +
                          IF(IFNULL(LichNgay2.Ref_Shift3,0), 1, 0) +
                          IF(IFNULL(LichNgay2.Ref_Shift4,0), 1, 0),

                          IF(IFNULL(LichNgay3.Ref_Shift1,0), 1, 0) +
                          IF(IFNULL(LichNgay3.Ref_Shift2,0), 1, 0) +
                          IF(IFNULL(LichNgay3.Ref_Shift3,0), 1, 0) +
                          IF(IFNULL(LichNgay3.Ref_Shift4,0), 1, 0),

                          IF(IFNULL(LichNgay4.Ref_Shift1,0), 1, 0) +
                          IF(IFNULL(LichNgay4.Ref_Shift2,0), 1, 0) +
                          IF(IFNULL(LichNgay4.Ref_Shift3,0), 1, 0) +
                          IF(IFNULL(LichNgay4.Ref_Shift4,0), 1, 0),

                          IF(IFNULL(LichNgay5.Ref_Shift1,0), 1, 0) +
                          IF(IFNULL(LichNgay5.Ref_Shift2,0), 1, 0) +
                          IF(IFNULL(LichNgay5.Ref_Shift3,0), 1, 0) +
                          IF(IFNULL(LichNgay5.Ref_Shift4,0), 1, 0),

                          IF(IFNULL(LichNgay6.Ref_Shift1,0), 1, 0) +
                          IF(IFNULL(LichNgay6.Ref_Shift2,0), 1, 0) +
                          IF(IFNULL(LichNgay6.Ref_Shift3,0), 1, 0) +
                          IF(IFNULL(LichNgay6.Ref_Shift4,0), 1, 0),

                          IF(IFNULL(LichNgay7.Ref_Shift1,0), 1, 0) +
                          IF(IFNULL(LichNgay7.Ref_Shift2,0), 1, 0) +
                          IF(IFNULL(LichNgay7.Ref_Shift3,0), 1, 0) +
                          IF(IFNULL(LichNgay7.Ref_Shift4,0), 1, 0),

                          IF(IFNULL(LichNgay8.Ref_Shift1,0), 1, 0) +
                          IF(IFNULL(LichNgay8.Ref_Shift2,0), 1, 0) +
                          IF(IFNULL(LichNgay8.Ref_Shift3,0), 1, 0) +
                          IF(IFNULL(LichNgay8.Ref_Shift4,0), 1, 0)
                      ) AS SoCaMax
                FROM ODanhSachThietBi AS ThietBi
                INNER JOIN OLichLamViec AS LichTuan ON ThietBi.Ref_LichLamViec = LichTuan.IOID
                INNER JOIN OLichLamViecNgay AS LichNgay2 ON LichTuan.Ref_ThuHai = LichNgay2.IOID
                INNER JOIN OLichLamViecNgay AS LichNgay3 ON LichTuan.Ref_ThuBa = LichNgay3.IOID
                INNER JOIN OLichLamViecNgay AS LichNgay4 ON LichTuan.Ref_ThuTu = LichNgay4.IOID
                INNER JOIN OLichLamViecNgay AS LichNgay5 ON LichTuan.Ref_ThuNam = LichNgay5.IOID
                INNER JOIN OLichLamViecNgay AS LichNgay6 ON LichTuan.Ref_ThuSau = LichNgay6.IOID
                INNER JOIN OLichLamViecNgay AS LichNgay7 ON LichTuan.Ref_ThuBay = LichNgay7.IOID
                INNER JOIN OLichLamViecNgay AS LichNgay8 ON LichTuan.Ref_ChuNhat = LichNgay8.IOID

            ) AS SoCa On ThietBi.IOID = SoCa.IOID

            -- Dung may
            LEFT JOIN
            (
                SELECT
                    IOID
                    , SUM(CASE WHEN DungMay.Ma = %3$s THEN IFNULL(DungMay.ThoiGianNgungMay, 0) ELSE  0 END) AS DungMayCheDo
                    , SUM(CASE WHEN DungMay.Ma = %4$s THEN IFNULL(DungMay.ThoiGianNgungMay, 0) ELSE  0 END) AS DungMayHongMay
                FROM
                (
                    -- Dung may
                    (
                        SELECT
                            ThietBi.IOID
                            , NguyenNhan.Ma
                            , IFNULL(DungMayKeHoach.ThoiGianDungMay, 0) AS ThoiGianNgungMay
                        FROM OPhieuSuCo AS DungMayKeHoach
                        LEFT JOIN ONguyenNhanSuCo AS NguyenNhan ON DungMayKeHoach.Ref_MaNguyenNhanSuCo = NguyenNhan.IOID
                        LEFT JOIN ODanhSachThietBi AS ThietBi ON DungMayKeHoach.Ref_MaThietBi = ThietBi.IOID
                        WHERE IFNULL(DungMayKeHoach.Ref_BoPhan, 0) = 0
                            AND DungMayKeHoach.NgayDungMay BETWEEN %1$s AND %2$s
                        GROUP BY ThietBi.IOID

                    )
                    UNION ALL
                    -- Dung may phieu bao tri
                    (
                        SELECT
                            ThietBi.IOID
                            , NguyenNhan.Ma
                            , IFNULL(DungMayBaoTri.ThoiGianDungMay, 0)
                        FROM OPhieuBaoTri AS DungMayBaoTri
                        LEFT JOIN ONguyenNhanSuCo AS NguyenNhan ON DungMayBaoTri.Ref_MaNguyenNhanSuCo = NguyenNhan.IOID
                        LEFT JOIN ODanhSachThietBi AS ThietBi ON DungMayBaoTri.Ref_MaThietBi = ThietBi.IOID
                        WHERE IFNULL(DungMayBaoTri.Ref_BoPhan, 0) = 0
                            AND DungMayBaoTri.NgayBatDau BETWEEN %1$s AND %2$s
                    )
                ) AS DungMay
            ) AS DungMay On ThietBi.IOID = DungMay.IOID

            WHERE  1=1 %5$s

            ORDER By NhomThietBi.lft, CAST(RIGHT(ThietBi.MaThietBi, 4) AS UNSIGNED)
        '
            , $this->_o_DB->quote($start)
            , $this->_o_DB->quote($end)
            , $this->_o_DB->quote(self::DUNG_MAY_CHE_DO)
            , $this->_o_DB->quote(self::DUNG_MAY_HONG_MAY)
            , $where);

        // echo '<pre>'; print_r($sql); die;

        return $this->_o_DB->fetchAll($sql);
    }

    public function countSoLanBaoTriTheoNhomThietBi(
        $start
        , $end
        , $locationIOID = 0
        , $equipGroupIOID = 0
        , $equipTypeIOID = 0
        , $equipIOID = 0
    )
    {
        $mDept    = new Qss_Model_Admin_Department();
        $rDept    = array($this->_user->user_dept_id);
        $dept     = $mDept->getChildDepartments($this->_user->user_dept_id);

        foreach($dept as $item)
        {
            $rDept[] = $item->DepartmentID;
        }

        $where  = '';
        $where .= sprintf(' AND ThietBi.DeptID IN (%1$s) ', implode(', ', $rDept));
        $where .= sprintf(' AND IFNULL(ThietBi.Ref_TrucThuoc, 0) = 0 ');
        $loc    = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locationIOID));
        $where .= $loc?sprintf(' AND (ThietBi.Ref_MaKhuVuc IN (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))', $loc->lft, $loc->rgt):'';
        $type   = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OLoaiThietBi WHERE IOID = %1$d', $equipTypeIOID));
        $where .= $type?sprintf(' AND (ThietBi.Ref_LoaiThietBi IN (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d))', $type->lft, $type->rgt):'';
        $where .= ($equipGroupIOID)?sprintf(' AND ThietBi.Ref_NhomThietBi = %1$d  ', $equipGroupIOID):'';
        $where .= ($equipIOID)?sprintf(' AND ThietBi.IOID = %1$d  ', $equipIOID):'';

        $sql = sprintf('
            SELECT
                NhomThietBi.IOID AS Ref_NhomThietBi
                , COUNT(1) AS Total
            FROM OPhieuBaoTri AS PhieuBaotri
            INNER JOIN qsiforms ON PhieuBaotri.IFID_M759 = qsiforms.IFID
            INNER JOIN ODanhSachThietBi AS ThietBi On PhieuBaotri.Ref_MaThietBi = ThietBi.IOID
            INNER JOIN ONhomThietBi AS NhomThietBi On ThietBi.Ref_NhomThietBi = NhomThietBi.IOID
            WHERE (PhieuBaotri.NgayBatDau BETWEEN %1$s AND %2$s %3$s) AND qsiforms.Status IN (3,4)
            GROUP By NhomThietBi.IOID
        '
        , $this->_o_DB->quote($start)
        , $this->_o_DB->quote($end)
        , $where);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getKeHoachSuaChuaBaoDuongThuongXuyen(
        $start
        , $end
        , $locationIOID = 0
        , $equipGroupIOID = 0
        , $equipTypeIOID = 0
        , $equipIOID = 0

    )
    {
        $mDept    = new Qss_Model_Admin_Department();
        $rDept    = array($this->_user->user_dept_id);
        $dept     = $mDept->getChildDepartments($this->_user->user_dept_id);

        foreach($dept as $item)
        {
            $rDept[] = $item->DepartmentID;
        }

        $where  = '';
        $where .= sprintf(' AND ThietBi.DeptID IN (%1$s) ', implode(', ', $rDept));
        $where .= sprintf(' AND IFNULL(ThietBi.Ref_TrucThuoc, 0) = 0 ');
        $loc    = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locationIOID));
        $where .= $loc?sprintf(' AND (ThietBi.Ref_MaKhuVuc IN (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))', $loc->lft, $loc->rgt):'';
        $type   = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OLoaiThietBi WHERE IOID = %1$d', $equipTypeIOID));
        $where .= $type?sprintf(' AND (ThietBi.Ref_LoaiThietBi IN (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d))', $type->lft, $type->rgt):'';
        $where .= ($equipGroupIOID)?sprintf(' AND ThietBi.Ref_NhomThietBi = %1$d  ', $equipGroupIOID):'';
        $where .= ($equipIOID)?sprintf(' AND ThietBi.IOID = %1$d  ', $equipIOID):'';

        $sql = sprintf('
            SELECT
                NhomThietBi.IOID AS Ref_NhomThietBi
                , NhomThietBi.MoTa AS TenNhomThietBi
                , ThietBi.MaThietBi
                , ThietBi.TenThietBi
                , IFNULL(ChuKyLoaiThietBi.QuyDinhBaoDuongB1, 0) AS QuyDinhBaoDuongB1
                , IFNULL(ChuKyLoaiThietBi.QuyDinhBaoDuongB2, 0) AS QuyDinhBaoDuongB2
                , IFNULL(ChuKyLoaiThietBi.QuyDinhBaoDuongTT, 0) AS QuyDinhBaoDuongTT
            FROM ODanhSachThietBi AS ThietBi
            INNER JOIN ONhomThietBi AS NhomThietBi ON ThietBi.Ref_NhomThietBi = NhomThietBi.IOID

            LEFT JOIN OLoaiThietBi AS LoaiThietBi ON ThietBi.Ref_LoaiThietBi = LoaiThietBi.IOID

            LEFT JOIN
            (
                SELECT
                    ChuKyLoaiThietBi.IFID_M770
                    , GROUP_CONCAT( IF(ChuKyLoaiThietBi.Loai = %2$s, ChuKyLoaiThietBi.SoGio, null) separator \',\') AS QuyDinhBaoDuongB1
                    , GROUP_CONCAT( IF(ChuKyLoaiThietBi.Loai = %3$s, ChuKyLoaiThietBi.SoGio, null) separator \',\') AS QuyDinhBaoDuongB2
                    , GROUP_CONCAT( IF(ChuKyLoaiThietBi.Loai = %4$s, ChuKyLoaiThietBi.SoGio, null) separator \',\') AS QuyDinhBaoDuongTT
                FROM
                (
                    SELECT
                        LoaiThietBi.IFID_M770
                        , LoaiBaoTri.Loai
                        , ChuKy.SoGio
                    FROM OLoaiThietBi AS LoaiThietBi
                    INNER JOIN OChuKyLoaiThietBi AS ChuKy ON LoaiThietBi.IFID_M770 = ChuKy.IFID_M770
                    INNER JOIN OPhanLoaiBaoTri AS LoaiBaoTri ON ChuKy.Ref_LoaiBaoTri = LoaiBaoTri.IOID
                    ORDER BY LoaiThietBi.IFID_M770, LoaiBaoTri.IOID, ChuKy.SoGio
                ) AS ChuKyLoaiThietBi
                GROUP BY ChuKyLoaiThietBi.IFID_M770
            ) AS ChuKyLoaiThietBi ON LoaiThietBi.IFID_M770 = ChuKyLoaiThietBi.IFID_M770

            WHERE 1=1 %1$s
            ORDER BY NhomThietBi.lft, CAST(RIGHT(ThietBi.MaThietBi, 4) AS UNSIGNED)
        '
        , $where
        , $this->_o_DB->quote(Qss_Model_Extra_Maintain::B1)
        , $this->_o_DB->quote(Qss_Model_Extra_Maintain::B2)
        , $this->_o_DB->quote(Qss_Model_Extra_Maintain::TT));

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getQuyetToanSuDungVatTuSCTX(
        $start
        , $end
        , $locationIOID = 0
        , $equipGroupIOID = 0
        , $equipTypeIOID = 0
        , $equipIOID = 0
    )
    {
        $mDept    = new Qss_Model_Admin_Department();
        $rDept    = array($this->_user->user_dept_id);
        $dept     = $mDept->getChildDepartments($this->_user->user_dept_id);

        foreach($dept as $item)
        {
            $rDept[] = $item->DepartmentID;
        }

        $where  = '';
        $where .= sprintf(' AND ThietBi.DeptID IN (%1$s) ', implode(', ', $rDept));
        $where .= sprintf(' AND IFNULL(ThietBi.Ref_TrucThuoc, 0) = 0 ');
        $loc    = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locationIOID));
        $where .= $loc?sprintf(' AND (ThietBi.Ref_MaKhuVuc IN (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))', $loc->lft, $loc->rgt):'';
        $type   = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OLoaiThietBi WHERE IOID = %1$d', $equipTypeIOID));
        $where .= $type?sprintf(' AND (ThietBi.Ref_LoaiThietBi IN (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d))', $type->lft, $type->rgt):'';
        $where .= ($equipGroupIOID)?sprintf(' AND ThietBi.Ref_NhomThietBi = %1$d  ', $equipGroupIOID):'';
        $where .= ($equipIOID)?sprintf(' AND ThietBi.IOID = %1$d  ', $equipIOID):'';


        $sql = sprintf('
            SELECT
                MatHang.*
                , IF( IFNULL(DSXuatKho.IOID, 0) != 0, DSXuatKho.DonGia, MatHang.GiaMua) AS GiaMua
                , sum( IFNULL(VatTu.SoLuong, 0) * IFNULL(DonViTinh.HeSoQuyDoi, 0) ) AS SoLuongVatTu
                , NhomThietBi.IOID AS Ref_NhomThietBi
                , NhomThietBi.MoTa AS TenNhomThietBi
                , ThietBi.IOID AS Ref_ThietBi
                , ThietBi.TenThietBi
                , VatTu.ViTri
                , VatTu.BoPhan
                , Max( IF(IFNULL(VatTu.Ngay, \'\') != \'\', VatTu.Ngay, PhieuBaoTri.Ngay) ) AS NgayThayMoi
                , TruocDo.NgayThayMoi AS NgayGanKe
            FROM OPhieuBaoTri AS PhieuBaoTri
            INNER JOIN qsiforms ON PhieuBaoTri.IFID_M759 = qsiforms.IFID
            INNER JOIN ODanhSachThietBi AS ThietBi ON PhieuBaoTri.Ref_MaThietBi = ThietBi.IOID
            INNER JOIN ONhomThietBi AS NhomThietBi ON ThietBi.Ref_NhomThietBi = NhomThietBi.IOID
            INNER JOIN OVatTuPBT AS VatTu ON PhieuBaoTri.IFID_M759 = VatTu.IFID_M759
            INNER JOIN OSanPham AS MatHang ON VatTu.Ref_MaVatTu = MatHang.IOID
            INNER JOIN ODonViTinhSP AS DonViTinh ON MatHang.IFID_M113 = DonViTinh.IFID_M113 AND VatTu.Ref_DonViTinh = DonViTinh.IOID
            INNER JOIN OPhanLoaiBaoTri AS LoaiBaoTri ON PhieuBaoTri.Ref_LoaiBaoTri = LoaiBaoTri.IOID
            LEFT JOIN OXuatKho AS XuatKho ON PhieuBaoTri.IOID = IFNULL(XuatKho.Ref_PhieuBaoTri, 0)
            LEFT JOIN ODanhSachXuatKho AS DSXuatKho ON XuatKho.IFID_M506 = DSXuatKho.IFID_M506
                AND VatTu.Ref_MaVatTu = DSXuatKho.Ref_MaSP
                AND VatTu.Ref_DonViTinh = DSXuatKho.Ref_DonViTinh             
            LEFT JOIN OCauTrucThietBi AS CauTruc ON ThietBi.IFID_M705 = CauTruc.IFID_M705 AND IFNULL(VatTu.Ref_ViTri, 0) = CauTruc.IOID
            LEFT JOIN(
                SELECT
                    NhomThietBi.IOID AS Ref_NhomThietBi
                    , ThietBi.IOID AS Ref_ThietBi
                    , IFNULL(CauTruc.IOID, 0) AS Ref_ViTri
                    ,  VatTu.Ref_MaVatTu AS Ref_MatHang
                    , Max( IF(IFNULL(VatTu.Ngay, \'\') != \'\', VatTu.Ngay, PhieuBaoTri.Ngay) ) AS NgayThayMoi
                FROM OPhieuBaoTri AS PhieuBaoTri
                INNER JOIN qsiforms ON PhieuBaoTri.IFID_M759 = qsiforms.IFID
                INNER JOIN ODanhSachThietBi AS ThietBi ON PhieuBaoTri.Ref_MaThietBi = ThietBi.IOID
                INNER JOIN ONhomThietBi AS NhomThietBi ON ThietBi.Ref_NhomThietBi = NhomThietBi.IOID
                INNER JOIN OVatTuPBT AS VatTu ON PhieuBaoTri.IFID_M759 = VatTu.IFID_M759
                INNER JOIN OPhanLoaiBaoTri AS LoaiBaoTri ON PhieuBaoTri.Ref_LoaiBaoTri = LoaiBaoTri.IOID
                LEFT JOIN OCauTrucThietBi AS CauTruc ON ThietBi.IFID_M705 = CauTruc.IFID_M705 AND IFNULL(VatTu.Ref_ViTri, 0) = CauTruc.IOID
                WHERE qsiforms.Status in (3,4)
                AND
                     (PhieuBaoTri.NgayBatDau <= %2$s)
                    AND LoaiBaoTri.Loai IN (%4$s, %6$s, %7$s)
                    %1$s
                GROUP BY NhomThietBi.IOID, ThietBi.IOID, IFNULL(CauTruc.IOID, 0),  VatTu.Ref_MaVatTu
            ) AS TruocDo ON NhomThietBi.IOID = TruocDo.Ref_NhomThietBi
                AND ThietBi.IOID = TruocDo.Ref_ThietBi
                AND IFNULL(CauTruc.IOID, 0) = IFNULL(TruocDo.Ref_ViTri, 0)
                AND MatHang.IOID = TruocDo.Ref_MatHang
               
            WHERE qsiforms.Status in (3,4)
                AND (PhieuBaoTri.NgayBatDau BETWEEN %2$s AND %3$s)
                AND LoaiBaoTri.Loai IN (%4$s, %6$s, %7$s)
                %1$s
            GROUP BY NhomThietBi.IOID, ThietBi.IOID, IFNULL(CauTruc.IOID, 0),  MatHang.IOID
            ORDER BY NhomThietBi.lft, CAST(RIGHT(ThietBi.MaThietBi, 4) AS UNSIGNED), CauTruc.ViTri, MatHang.MaSanPham
        ', $where
        , $this->_o_DB->quote($start)
        , $this->_o_DB->quote($end)
        , $this->_o_DB->quote(Qss_Model_Extra_Maintain::TT)
        , $this->_o_DB->quote(Qss_Model_Extra_Maintain::DX)
        , $this->_o_DB->quote(Qss_Model_Extra_Maintain::B1)
        , $this->_o_DB->quote(Qss_Model_Extra_Maintain::B2));

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getThucHienKeHoachVanHanhSuaChuaBaoDuong(
        $start
        , $end
        , $locationIOID = 0
        , $equipGroupIOID = 0
        , $equipTypeIOID = 0
        , $equipIOID = 0
    )
    {
        $mDept    = new Qss_Model_Admin_Department();
        $rDept    = array($this->_user->user_dept_id);
        $dept     = $mDept->getChildDepartments($this->_user->user_dept_id);

        foreach($dept as $item)
        {
            $rDept[] = $item->DepartmentID;
        }

        $where  = '';
        $where .= sprintf(' AND ThietBi.DeptID IN (%1$s) ', implode(', ', $rDept));
        $where .= sprintf(' AND IFNULL(ThietBi.Ref_TrucThuoc, 0) = 0 ');
        $loc    = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locationIOID));
        $where .= $loc?sprintf(' AND (ThietBi.Ref_MaKhuVuc IN (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))', $loc->lft, $loc->rgt):'';
        $type   = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OLoaiThietBi WHERE IOID = %1$d', $equipTypeIOID));
        $where .= $type?sprintf(' AND (ThietBi.Ref_LoaiThietBi IN (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d))', $type->lft, $type->rgt):'';
        $where .= ($equipGroupIOID)?sprintf(' AND ThietBi.Ref_NhomThietBi = %1$d  ', $equipGroupIOID):'';
        $where .= ($equipIOID)?sprintf(' AND ThietBi.IOID = %1$d  ', $equipIOID):'';


        $sql = sprintf('
            SELECT
                NhomThietBi.IOID AS Ref_NhomThietBi
                , NhomThietBi.MoTa AS TenNhomThietBi
                , ThietBi.MaThietBi
                , ThietBi.TenThietBi
            FROM ODanhSachThietBi AS ThietBi
            INNER JOIN ONhomThietBi AS NhomThietBi ON ThietBi.Ref_NhomThietBi = NhomThietBi.IOID
            WHERE 1=1 %1$s
            ORDER BY NhomThietBi.lft, CAST(RIGHT(ThietBi.MaThietBi, 4) AS UNSIGNED)
        ', $where);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }
}