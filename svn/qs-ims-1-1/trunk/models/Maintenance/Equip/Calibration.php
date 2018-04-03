<?php
/**
 * Hieu chuan thiet bi
 */
class Qss_Model_Maintenance_Equip_Calibration extends Qss_Model_Abstract
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Lay ngay hieu chuan tiep theo cua thiet bi trong mot khoang thoi gian (Lay ca nhung hieu chuan qua han)
     * TB1 - Chu ky hieu chuan 1 - Lan hieu chuan cuoi cung (Theo thiet bi, bo phan, loai, chu ky)
     * TB1 - Chu ky hieu chuan 2 - Lan hieu chuan cuoi cung
     * @return mixed
     */
    public function getNextCalibrations(
        $mStart
        , $mEnd
        , $locationIOID = 0
        , $equipIOID = 0
        , $equipGroupIOID = 0
        , $equipTypeIOID = 0)
    {
        $filter = '';
        $loc    = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locationIOID));
        $filter.= $loc?sprintf(' AND ifnull(HieuChuanThietBi.Ref_MaKhuVuc, 0) IN (SELECT IOID FROM OKhuVuc WHERE lft >= %1$d AND rgt <= %2$d) ', $loc->lft, $loc->rgt):'';
        $filter.= $equipIOID?sprintf(' AND HieuChuanThietBi.ThietBiIOID = %1$d ', $equipIOID):'';
        $type   = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OLoaiThietBi WHERE IOID = %1$d', $equipTypeIOID));
        $filter.= $type?sprintf(' AND ifnull(HieuChuanThietBi.Ref_LoaiThietBi, 0) IN (SELECT IOID FROM OLoaiThietBi WHERE lft >= %1$d AND rgt <= %2$d) ', $type->lft, $type->rgt):'';
        $filter.= $equipGroupIOID?sprintf(' AND HieuChuanThietBi.Ref_NhomThietBi = %1$d ', $equipGroupIOID):'';

//        --, CASE
//                --    WHEN HieuChuanThietBi.NgayHieuChuanTiepTheo > %3$s THEN \'FUTURE\'
//                --    WHEN HieuChuanThietBi.NgayHieuChuanTiepTheo = %3$s THEN \'NOW\'
//                --    ELSE \'PAST\'
//                --END AS `Time`
//                --, CASE
//                --    WHEN HieuChuanThietBi.NgayHieuChuanTiepTheo > %3$s THEN \'bgyellow\'
//                --    WHEN HieuChuanThietBi.NgayHieuChuanTiepTheo = %3$s THEN \'bgyellow\'
//                --    ELSE \'bgpink\'
//                --END AS `Class`

        // Yeu cau can phai hieu chuan thiet bi va bo phan
        $sql = sprintf('
            SELECT
                *
                , IF(HieuChuanThietBi.NgayHieuChuanTiepTheo < %3$s, \'bgpink\', \'\') AS `Class`
            FROM
            (
                SELECT
                    ifnull(ThietBi.IOID, 0) AS ThietBiIOID
                    , ThietBi.IFID_M705
                    , ThietBi.MaThietBi
                    , ThietBi.TenThietBi
                    , ThietBi.Ref_LoaiThietBi
                    , ThietBi.Ref_NhomThietBi
                    , ThietBi.Ref_MaKhuVuc
                    , IF( ifnull(CaiDatHieuChuan.Ref_ViTri, 0) != 0, CaiDatHieuChuan.ViTri, HieuChuan.BoPhan) AS ViTri
                    , IF( ifnull(CaiDatHieuChuan.ChuKy, 0) != 0, CaiDatHieuChuan.ChuKy, HieuChuan.ChuKy) AS ChuKy
                    , HieuChuan.DonVi
                    , HieuChuan.NoiDung
                    , HieuChuan.CacThongSoKiemTra
                    , HieuChuan.Ngay AS NgayHieuChuanGanNhat
                    -- Neu kho chon loai thi mac dinh la hieu chuan
                    , IF( ifnull(CaiDatHieuChuan.Loai, 0) != 0
                        , CaiDatHieuChuan.Loai
                        , ifnull(HieuChuan.Loai, 1)
                    ) AS Loai
                    -- Ngay hieu chaun tiep theo dua tren hieu chuan cuoi cung
                    -- Neu ko co hieu chuan thi dua theo chu ky hieu chuan va ngay dua vao su dung
                    -- Neu ko co ngay dua vao su duung thi bo qua
                    , (
                        -- Neu co hieu chuan cuoi cung thi lay ngay hieu chuan tiep theo
                        CASE WHEN ifnull(HieuChuan.IOID, 0) != 0 THEN HieuChuan.NgayKiemDinhTiepTheo
                        -- Neu kho co hieu chuan thi dua vao cai dat chu ky cua thiet bi va ngay dua vao su dung
                        ELSE
                            (
                                CASE
                                    WHEN ifnull(ThietBi.NgayDuaVaoSuDung, \'\') != \'\' THEN
                                        (
                                            -- 1 thang
                                            CASE WHEN CaiDatHieuChuan.ChuKy = 1 THEN
                                                DATE_ADD(ThietBi.NgayDuaVaoSuDung, INTERVAL 1 MONTH) - INTERVAL 1 DAY
                                            -- 3 thang
                                            WHEN CaiDatHieuChuan.ChuKy = 2 THEN
                                                DATE_ADD(ThietBi.NgayDuaVaoSuDung, INTERVAL 3 MONTH) - INTERVAL 1 DAY
                                            -- 6 thang
                                            WHEN CaiDatHieuChuan.ChuKy = 3 THEN
                                                DATE_ADD(ThietBi.NgayDuaVaoSuDung, INTERVAL 6 MONTH) - INTERVAL 1 DAY
                                            -- 12 thang
                                            WHEN CaiDatHieuChuan.ChuKy = 4 THEN
                                                DATE_ADD(ThietBi.NgayDuaVaoSuDung, INTERVAL 12 MONTH) - INTERVAL 1 DAY
                                            END
                                        )
                                    ELSE \'\'
                                END
                            )
                        END
                    ) AS NgayHieuChuanTiepTheo
                FROM ODanhSachThietBi AS ThietBi
                -- Lay danh sach cai dat hieu chuan cua thiet bi
                -- Neu ko co vi tri thi la hieu chuan thiet bi
                -- Neu chon vi tri thi la hieu chuan bo phan (Thiet bi do)
                LEFT JOIN
                (
                    SELECT CaiDatHieuChuan.*
                    FROM ODanhSachThietBi AS ThietBi
                    INNER JOIN OChuKyHieuChuan AS CaiDatHieuChuan ON ThietBi.IFID_M705 = CaiDatHieuChuan.IFID_M705
                ) AS CaiDatHieuChuan ON ThietBi.IFID_M705 = CaiDatHieuChuan.IFID_M705
                -- Lay hieu chuan lan cuoi cung cua thiet bi neu co
                -- Neu hieu chuan cu chua thuc hien ma co hieu chuan moi thi chi lay hieu chuan moi
                -- voi chung thiet bi , bo phan, chu ky, loai
                LEFT JOIN
                (
                    SELECT *
                    FROM (
                        SELECT HieuChuan.*
                        FROM OHieuChuanKiemDinh AS HieuChuan
                        ORDER BY HieuChuan.Ref_MaThietBi, HieuChuan.Ref_BoPhan, HieuChuan.Loai, HieuChuan.ChuKy, HieuChuan.Ngay DESC
                    ) AS HieuChuan
                    GROUP BY HieuChuan.Ref_MaThietBi, HieuChuan.Ref_BoPhan, HieuChuan.Loai, HieuChuan.ChuKy
                ) AS HieuChuan ON
                    ThietBi.IOID = HieuChuan.Ref_MaThietBi
                    AND ifnull(CaiDatHieuChuan.Ref_ViTri, 0) = ifnull(HieuChuan.Ref_BoPhan, 0)
                    AND (ifnull(CaiDatHieuChuan.Loai, 0) = 0 OR ifnull(HieuChuan.Loai, 0) = 0 OR CaiDatHieuChuan.Loai = HieuChuan.Loai)
                    AND (ifnull(CaiDatHieuChuan.ChuKy, 0) = 0 OR ifnull(HieuChuan.ChuKy, 0) = 0 OR CaiDatHieuChuan.ChuKy = HieuChuan.ChuKy)
                WHERE IFNULL(ThietBi.TrangThai, 0) = 0
            ) AS HieuChuanThietBi
            -- Ngay hieu chuan tiep theo luon tra ve mot gia tri neu co hieu chuan hoac cai dat hieu chuan
            -- Khong tinh doi voi cac thiet bi chua duoc hieu chuan lan nao va ko co ngay dua vao su dung
            -- Ngay hieu chuan o trong mot khoang thoi gian xac dinh hoac cac hieu chuan da qua han
            WHERE
                ifnull(HieuChuanThietBi.NgayHieuChuanTiepTheo, \'\') != \'\'
                AND (
                    (HieuChuanThietBi.NgayHieuChuanTiepTheo BETWEEN %1$s AND %2$s)
                    OR HieuChuanThietBi.NgayHieuChuanTiepTheo <= %3$s
                )
                AND (
                    IFNULL(HieuChuanThietBi.Ref_MaKhuVuc, 0) in (
                        SELECT IOID FROM OKhuVuc
                        inner join qsrecordrights on OKhuVuc.IFID_M720 = qsrecordrights.IFID
                        WHERE UID = %5$d
                    )
                )
                %4$s
        '
        , $this->_o_DB->quote($mStart)
        , $this->_o_DB->quote($mEnd)
        , $this->_o_DB->quote(date('Y-m-d'))
        , $filter
        , $this->_user->user_id);
        //echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getProcessingCalibrationsFormCalibrationAndVerify($start, $end, $location = 0, $equip = 0, $equipGroup = 0)
    {
        $loc     =  $location?$this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $location)):'';
        $filter  = $loc?
            sprintf('
                AND ifnull(ThietBi.Ref_MaKhuVuc, 0) IN (SELECT IOID FROM OKhuVuc WHERE lft >= %1$d AND rgt <= %2$d)
            ', $loc->lft, $loc->rgt):'';
        $filter .= $equip?sprintf(' AND ThietBi.IOID = %1$d ', $equip):'';
        $filter .= $equipGroup?sprintf(' AND IFNULL(ThietBi.Ref_NhomThietBi, 0) = %1$d ', $equipGroup):'';

        if(Qss_Lib_System::formActive('M843'))
        {
            $sql = sprintf('
            SELECT PhieuBaoTri.*, qsiforms.Status, QuyDinh.Ref_ChuKy, QuyDinh.Ref_Loai 
            FROM OHieuChuanKiemDinh AS PhieuBaoTri
            INNER JOIN ODanhSachThietBi AS ThietBi ON IFNULL(PhieuBaoTri.Ref_MaThietBi, 0) = ThietBi.IOID
            INNER JOIN qsiforms ON PhieuBaoTri.IFID_M753 = qsiforms.IFID
            INNER JOIN OQuyDinhHieuChuanKiemDinh AS QuyDinh ON IFNULL(PhieuBaoTri.Ref_TenHieuChuan, 0) = QuyDinh.IOID
            WHERE IFNULL(PhieuBaoTri.Ref_MaThietBi, 0) != 0  AND qsiforms.Status = 1
                AND ((PhieuBaoTri.Ngay BETWEEN %1$s AND %2$s) OR PhieuBaoTri.Ngay <= %3$s)
                %4$s
            ORDER BY PhieuBaoTri.Ngay DESC
        ' , $this->_o_DB->quote($start), $this->_o_DB->quote($end), $this->_o_DB->quote(date('Y-m-d')), $filter);
        }
        else
        {
            $sql = sprintf('
            SELECT PhieuBaoTri.*, qsiforms.Status 
            FROM OHieuChuanKiemDinh AS PhieuBaoTri
            INNER JOIN ODanhSachThietBi AS ThietBi ON IFNULL(PhieuBaoTri.Ref_MaThietBi, 0) = ThietBi.IOID
            INNER JOIN qsiforms ON PhieuBaoTri.IFID_M753 = qsiforms.IFID
            WHERE IFNULL(PhieuBaoTri.Ref_MaThietBi, 0) != 0  AND qsiforms.Status = 1
                AND ((PhieuBaoTri.Ngay BETWEEN %1$s AND %2$s) OR PhieuBaoTri.Ngay <= %3$s)
                %4$s
            ORDER BY PhieuBaoTri.Ngay DESC
        ' , $this->_o_DB->quote($start), $this->_o_DB->quote($end), $this->_o_DB->quote(date('Y-m-d')), $filter);
        }


        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getProcessingCalibrationsFormWorkOrder($start, $end, $location = 0, $equip = 0, $equipGroup = 0)
    {
        $loc     =  $location?$this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $location)):'';
        $filter  = $loc?
            sprintf('
                AND ifnull(ThietBi.Ref_MaKhuVuc, 0) IN (SELECT IOID FROM OKhuVuc WHERE lft >= %1$d AND rgt <= %2$d)
            ', $loc->lft, $loc->rgt):'';
        $filter .= $equip?sprintf(' AND ThietBi.IOID = %1$d ', $equip):'';
        $filter .= $equipGroup?sprintf(' AND IFNULL(ThietBi.Ref_NhomThietBi, 0) = %1$d ', $equipGroup):'';

        $sql = sprintf('
            SELECT PhieuBaoTri.*, ChuKy.KyBaoDuong as MaKy, qsiforms.Status, ChuKy.Thu
            FROM OPhieuBaoTri AS PhieuBaoTri
            INNER JOIN ODanhSachThietBi AS ThietBi ON IFNULL(PhieuBaoTri.Ref_MaThietBi, 0) = ThietBi.IOID
            INNER JOIN qsiforms ON PhieuBaoTri.IFID_M759 = qsiforms.IFID
            INNER JOIN OPhanLoaiBaoTri AS LoaiBaoTri on PhieuBaoTri.Ref_LoaiBaoTri = LoaiBaoTri.IOID
            INNER JOIN OChuKyBaoTri AS ChuKy ON IFNULL(PhieuBaoTri.Ref_ChuKy, 0) = ChuKy.IOID
            WHERE IFNULL(PhieuBaoTri.Ref_MaThietBi, 0) != 0 AND IFNULL(LoaiBaoTri.LoaiBaoTri, "") = "CA" AND qsiforms.Status < 4
                AND ((PhieuBaoTri.NgayBatDau BETWEEN %1$s AND %2$s) OR PhieuBaoTri.NgayBatDau <= %3$s)
                %4$s
            ORDER BY PhieuBaoTri.NgayBatDau DESC
        ' , $this->_o_DB->quote($start), $this->_o_DB->quote($end), $this->_o_DB->quote(date('Y-m-d')), $filter);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }


    public function getEquips($locationIOID = 0, $equipTypeIOID = 0, $equipGroupIOID = 0, $equipIOID = 0)
    {
        $filter = '';
        $loc    = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locationIOID));
        $filter.= $loc?sprintf(' AND ifnull(ThietBi.Ref_MaKhuVuc, 0) IN (SELECT IOID FROM OKhuVuc WHERE lft >= %1$d AND rgt <= %2$d) ', $loc->lft, $loc->rgt):'';
        $type   = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OLoaiThietBi WHERE IOID = %1$d', $equipTypeIOID));
        $filter.= $type?sprintf(' AND ifnull(ThietBi.Ref_LoaiThietBi, 0) IN (SELECT IOID FROM OLoaiThietBi WHERE lft >= %1$d AND rgt <= %2$d) ', $type->lft, $type->rgt):'';
        $filter.= $equipGroupIOID?sprintf(' AND ThietBi.Ref_NhomThietBi = %1$d ', $equipGroupIOID):'';
        $filter.= $equipIOID?sprintf(' AND ThietBi.IOID = %1$d ', $equipIOID):'';

        $sql = sprintf('
            SELECT ThietBi.*, ChuKy.*
            FROM ODanhSachThietBi AS ThietBi
            INNER JOIN OChuKyHieuChuan AS ChuKy ON ThietBi.IFID_M705 = ChuKy.IFID_M705
            LEFT JOIN OCauTrucThietBi AS CauTruc ON IFNULL(ChuKy.Ref_BoPhan, 0) = CauTruc.IOID
            WHERE IFNULL(ThietBi.TrangThai, 0) = 0  %1$s
            ORDER BY ThietBi.IOID, IFNULL(CauTruc.lft, 0), ChuKy.IOID
        ', $filter);
        //echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getCalibrations(
        $mStart
        , $mEnd
        , $locationIOID = 0
        , $equipTypeIOID = 0
        , $equipGroupIOID = 0
        , $equipIOID = 0
        , $order = ''
    )
    {
        $filter = '';
        $loc    = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locationIOID));
        $filter.= $loc?sprintf(' AND ifnull(ThietBi.Ref_MaKhuVuc, 0) IN (SELECT IOID FROM OKhuVuc WHERE lft >= %1$d AND rgt <= %2$d) ', $loc->lft, $loc->rgt):'';
        $type   = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OLoaiThietBi WHERE IOID = %1$d', $equipTypeIOID));
        $filter.= $type?sprintf(' AND ifnull(ThietBi.Ref_LoaiThietBi, 0) IN (SELECT IOID FROM OLoaiThietBi WHERE lft >= %1$d AND rgt <= %2$d) ', $type->lft, $type->rgt):'';
        $filter.= $equipGroupIOID?sprintf(' AND ThietBi.Ref_NhomThietBi = %1$d ', $equipGroupIOID):'';
        $filter.= $equipIOID?sprintf(' AND ThietBi.IOID = %1$d ', $equipIOID):'';

        if($order == 'LOCATION') {
            $order = ' ORDER BY KhuVuc.lft ';
        }

        $sql = sprintf('
            SELECT HieuChuan.*, KhuVuc.MaKhuVuc, KhuVuc.Ten AS TenKhuVuc, DoiTac.MaDoiTac, DoiTac.TenDoiTac
            FROM OHieuChuanKiemDinh AS HieuChuan
            INNER JOIN ODanhSachThietBi AS ThietBi ON HieuChuan.Ref_MaThietBi = ThietBi.IOID
            /*INNER JOIN OQuyDinhHieuChuanKiemDinh AS QuyDinh ON HieuChuan.Ref_TenHieuChuan = QuyDinh.IOID*/
            LEFT JOIN OKhuVuc AS KhuVuc ON IFNULL(ThietBi.Ref_MaKhuVuc, 0) =  KhuVuc.IOID
            LEFT JOIN ODoiTac AS DoiTac ON IFNULL(HieuChuan.Ref_DonVi, 0) = DoiTac.IOID
            WHERE (HieuChuan.Ngay BETWEEN  %1$s AND %2$s) %3$s 
            %4$s
        ', $this->_o_DB->quote($mStart)
        , $this->_o_DB->quote($mEnd)
        , $filter
        , $order);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }


    public function getNextDate($object)
    {
        $add          = 0; // Số lượng thời gian thêm vào
        $ngayKiemDinh = ''; // Ngày kiểm định hiện thời

        if(Qss_Lib_System::formActive('M843')) // Dành cho các project có sử dụng danh mục quy định hiệu chuẩn
        {
            $ngayKiemDinh = $object->getFieldByCode('Ngay')->getValue();
            $quyDinh      = $object->getFieldByCode('TenHieuChuan')->getRefIOID();
            $chuKy        = '';

            if(!$quyDinh && !$ngayKiemDinh) // Dành cho trường hợp sử dụng trên grid không lấy được quy định
            {
                $mTable = Qss_Model_Db::Table('OHieuChuanKiemDinh');
                $mTable->where(sprintf(' IFID_M753 = %1$d ', $object->i_IFID));
                $tmp    = $mTable->fetchOne();

                if($tmp)
                {
                    $ngayKiemDinh = @$tmp->Ngay;
                    $quyDinh      = @(int)$tmp->Ref_TenHieuChuan;
                }
            }

            if($quyDinh) // Lấy chu kỳ từ quy định M843
            {
                $mTable = Qss_Model_Db::Table('OQuyDinhHieuChuanKiemDinh');
                $mTable->where(sprintf(' IOID = %1$d ', $quyDinh));
                $tmp    = $mTable->fetchOne();

                if($tmp)
                {
                    $chuKy = $tmp->ChuKy;
                }
            }
        }
        else // Dành cho các project không dùng danh mục quy định hiệu chuẩn kiểm định
        {
            $ngayKiemDinh = $object->getFieldByCode('Ngay')->getValue();
            $chuKy        = (int)$object->getFieldByCode('ChuKy')->getValue();

            if(!$ngayKiemDinh && !$chuKy) // Dành cho trường hợp sử dụng trên grid không lấy được dữ liệu
            {
                $mTable = Qss_Model_Db::Table('OHieuChuanKiemDinh');
                $mTable->where(sprintf(' IFID_M753 = %1$d ', $object->i_IFID));
                $tmp    = $mTable->fetchOne();

                if($tmp)
                {
                    $ngayKiemDinh = @$tmp->Ngay;
                    $chuKy        = @(int)$tmp->ChuKy;
                }
            }
        }


        if($ngayKiemDinh && $chuKy) // Tinh ngày kiểm định tiếp theo
        {
            $chuKy = (int)$chuKy;

            switch ($chuKy)
            {
                case 1: // Hiệu chuẩn 3 tháng
                    $add = 3;
                break;

                case 2:
                case 3: // Hiệu chuẩn 6 tháng
                    $add = 6;
                break;

                case 4: // Hiệu chuẩn 12 tháng hoặc 1 năm
                    $add = 12;
                break;

                case 5: // Hiểu chuẩn 2 năm
                    $add = 24;
                break;

                case 6: // Hiệu chuẩn 3 năm
                    $add = 36;
                break;
            }
        }

        return ($add && $ngayKiemDinh)?date("Y-m-d", strtotime("+{$add} month", strtotime($ngayKiemDinh))):$ngayKiemDinh;
    }
}