<?php
/**
 * Model: Điều động thiết bị + Y
 */
class Qss_Model_Maintenance_Equipmentworking extends Qss_Model_Abstract
{
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * 
     * @param int $requestForEquipIFID IFID của yêu cầu điều động thiết bị
     * @todo: Chi xu ly cac phieu yeu cau da duoc duyet (OK)
     * datediff(hc.NgayKiemDinhTiepTheo,now()) as HanHCKD
     */
    public function getDetailOfRequestForEquipModuleByIFID($movingIFID, $requestForEquipIOID)
    {
        $sql = sprintf('
            SELECT
                YeuCauLoaiThietBi.IFID_M751 AS RFEIFID
                , YeuCauLoaiThietBi.YeuCauLoaiThietBiIOID AS RFEDIOID
                , YeuCauLoaiThietBi.LoaiThietBiTrucThuocIOID AS EquipTypeIOID
                , YeuCauLoaiThietBi.TenLoai AS EquipType
                , YeuCauLoaiThietBi.Ref_TenLoai AS RefEquipType
                , YeuCauLoaiThietBi.SoLuong AS Qty
                , YeuCauLoaiThietBi.DonViTinh AS UOM
                , YeuCauLoaiThietBi.NgayBatDau AS Start
                , YeuCauLoaiThietBi.NgayKetThuc AS End

                , if(ifnull(DieuDongThietBi.IOID, 0) = 0, 0, 1) AS `Transferred`
                , ifnull(DieuDongThietBi.IOID, 0) AS EWIOID
                , ifnull(DieuDongThietBi.IFID_M706, 0) AS EWIFID
                , ifnull(DieuDongThietBi.Status, 0) AS EWStatus
                , DieuDongThietBi.DuAn
                , DieuDongThietBi.Ref_DuAn

                , YeuCauLoaiThietBi.SoPhieu AS DocNo
                , ThietBi.MaThietBi AS EqCode
                , ThietBi.TenThietBi AS EqName
                , ThietBi.IOID AS EqIOID

                , if(ifnull(DieuDongThietBi.IOID, 0) <> 0, DieuDongThietBi.NgayBatDau, YeuCauLoaiThietBi.NgayBatDau) AS EqStartDate
                , if(ifnull(DieuDongThietBi.IOID, 0) <> 0, DieuDongThietBi.NgayKetThuc, YeuCauLoaiThietBi.NgayKetThuc) AS EqEndDate
                , hc.HanHCKD as HanHCKD


                , IFNULL(ThietBiCon.IOID, 0) AS ThietBiConIOID
                , ThietBiCon.MaThietBi AS MaThietBiCon
                , ThietBiCon.TenThietBi AS TenThietBiCon
                , ThietBiCon.Serial AS SerialThietBiCon
                , IF(IFNULL(ThietBiCon.Ref_TrucThuoc, 0) != 0, 1, 0) AS IsChild
                , IF(IFNULL(DieuDongThietBiCungYeuCau.Ref_MaThietBi, 0) != 0, 1, 0) AS SameRequestButInOther
            FROM
            (
                SELECT
                    LoaiThietBiTrucThuoc.IOID AS LoaiThietBiTrucThuocIOID
                    , LoaiThietBiTrucThuoc.TenLoai
                    , LoaiThietBiTrucThuoc.IOID AS Ref_TenLoai
                    , LoaiThietBiTrucThuoc.lft
                    , LoaiThietBiTrucThuoc.rgt
                    , YeuCauLoaiThietBi.IOID AS YeuCauLoaiThietBiIOID
                    , YeuCauLoaiThietBi.SoLuongDieuDong AS SoLuong
                    , YeuCauLoaiThietBi.DonViTinh
                    , YeuCauLoaiThietBi.NgayBatDau
                    , YeuCauLoaiThietBi.NgayKetThuc
                    , YeuCauDieuDong.IFID_M751
                    , YeuCauDieuDong.IOID AS YeuCauIOID
                    , YeuCauDieuDong.SoPhieu
                FROM OYeuCauTrangThietBiVatTu AS YeuCauDieuDong
                INNER JOIN qsiforms ON qsiforms.IFID = YeuCauDieuDong.IFID_M751
                INNER JOIN OYeuCauTrangThietBi AS YeuCauLoaiThietBi ON YeuCauDieuDong.IFID_M751 = YeuCauLoaiThietBi.IFID_M751
                INNER JOIN OLoaiThietBi AS LoaiThietBi ON  YeuCauLoaiThietBi.Ref_LoaiThietBi = LoaiThietBi.IOID
                INNER JOIN OLoaiThietBi AS LoaiThietBiTrucThuoc ON LoaiThietBiTrucThuoc.lft >= LoaiThietBi.lft
                    AND LoaiThietBiTrucThuoc.rgt <= LoaiThietBi.rgt
                WHERE YeuCauDieuDong.IOID = %2$d AND qsiforms.Status = 3
                ORDER BY YeuCauLoaiThietBi.IOID
            ) AS YeuCauLoaiThietBi
            INNER JOIN ODanhSachThietBi AS ThietBi ON YeuCauLoaiThietBi.LoaiThietBiTrucThuocIOID = ThietBi.Ref_LoaiThietBi
                AND IFNULL(ThietBi.Ref_TrucThuoc, 0) = 0 AND IFNULL(ThietBi.TrangThai, 0) = 0
            INNER JOIN ODanhSachThietBi AS ThietBiCon ON ThietBi.lft <= ThietBiCon.lft AND ThietBi.rgt >= ThietBiCon.rgt
                AND IFNULL(ThietBiCon.TrangThai, 0) = 0

            LEFT JOIN
            (
                SELECT
                    DanhSachThietBiDieuDong.IOID
                    , DanhSachThietBiDieuDong.Ref_MaThietBi
                    , DanhSachThietBiDieuDong.IFID_M706
                    , qsiforms.Status
                    , DieuDongThietBi.NgayBatDau
                    , DieuDongThietBi.NgayKetThuc
                    , DieuDongThietBi.DuAn
                    , DieuDongThietBi.Ref_DuAn
                FROM OLichThietBi AS DieuDongThietBi
                INNER JOIN qsiforms ON DieuDongThietBi.IFID_M706 = qsiforms.IFID
                LEFT JOIN ODanhSachDieuDongThietBi AS DanhSachThietBiDieuDong ON DieuDongThietBi.IFID_M706 = DanhSachThietBiDieuDong.IFID_M706
                WHERE DieuDongThietBi.IFID_M706 = %1$s
            ) AS DieuDongThietBi ON ThietBi.IOID = DieuDongThietBi.Ref_MaThietBi


            LEFT JOIN
            (
                SELECT
                    DanhSachThietBiDieuDong.Ref_MaThietBi
                FROM OLichThietBi AS DieuDongThietBi
                INNER JOIN qsiforms ON DieuDongThietBi.IFID_M706 = qsiforms.IFID
                LEFT JOIN ODanhSachDieuDongThietBi AS DanhSachThietBiDieuDong ON DieuDongThietBi.IFID_M706 = DanhSachThietBiDieuDong.IFID_M706
                WHERE DieuDongThietBi.IFID_M706 != %1$s AND DieuDongThietBi.Ref_PhieuYeuCau = %2$s AND qsiforms.Status >= 2
                GROUP BY DanhSachThietBiDieuDong.Ref_MaThietBi
            ) AS DieuDongThietBiCungYeuCau ON ThietBiCon.IOID = DieuDongThietBiCungYeuCau.Ref_MaThietBi

            LEFT JOIN
            (
                SELECT
                    ODanhSachDieuDongThietBi.Ref_MaThietBi
                    , OLichThietBi.NgayBatDau
                    , OLichThietBi.NgayKetThuc
                FROM ODanhSachDieuDongThietBi
                INNER JOIN OLichThietBi ON ODanhSachDieuDongThietBi.IFID_M706 = OLichThietBi.IFID_M706
                INNER JOIN qsiforms ON ODanhSachDieuDongThietBi.IFID_M706 = qsiforms.IFID
                WHERE
                    qsiforms.Status = 2
                    AND OLichThietBi.IFID_M706 != %1$s
                    AND OLichThietBi.Ref_PhieuYeuCau != %2$s
                    
            ) AS DieuDongKhac ON ThietBiCon.IOID = DieuDongKhac.Ref_MaThietBi
                AND (DieuDongKhac.NgayBatDau <= YeuCauLoaiThietBi.NgayKetThuc AND DieuDongKhac.NgayKetThuc >= YeuCauLoaiThietBi.NgayBatDau)

            LEFT JOIN
            (
                SELECT
                    Ref_MaThietBi
                    ,GROUP_CONCAT(CONCAT(Ref_Loai," ",NgayKiemDinhTiepTheo) SEPARATOR " <br> ") AS HanHCKD
                FROM
                (
                    SELECT
                        Ref_MaThietBi
                        , Ref_Loai
                        , NgayKiemDinhTiepTheo
                        , Loai
                        , ChuKy
                    FROM OHieuChuanKiemDinh
                    ORDER BY NgayKiemDinhTiepTheo DESC
                ) AS t
                GROUP BY Ref_MaThietBi,Loai,ChuKy
            ) AS hc ON hc.Ref_MaThietBi = ThietBiCon.IOID

            WHERE IFNULL(DieuDongKhac.Ref_MaThietBi, 0) = 0 AND ThietBiCon.DeptID IN (%3$s) AND IFNULL(ThietBiCon.Ref_DuAn, 0) = 0
            ORDER BY YeuCauLoaiThietBi.YeuCauLoaiThietBiIOID, YeuCauLoaiThietBi.lft, ThietBiCon.lft
        ', $movingIFID, $requestForEquipIOID, $this->_user->user_dept_list);

//        $sql = sprintf('
//
//            SELECT *
//                , IFNULL(ThietBiCon.IOID, 0) AS ThietBiConIOID
//                , ThietBiCon.MaThietBi AS MaThietBiCon
//                , ThietBiCon.TenThietBi AS TenThietBiCon
//                , IF(IFNULL(ThietBiCon.Ref_TrucThuoc, 0) != 0, 1, 0) AS IsChild
//            from
//            (
//                SELECT
//                    ycthietbi.IFID_M751 AS RFEIFID
//                    , ycdieudong.IOID AS RFEDIOID
//                    , loaitbcon.IOID AS EquipTypeIOID
//                    , loaitbcon.TenLoai AS EquipType
//                    , ycdieudong.SoLuong AS Qty
//                    , ycdieudong.DonViTinh AS UOM
//                    , ycdieudong.NgayBatDau AS Start
//                    , ycdieudong.NgayKetThuc AS End
//                    , if(ifnull(dsdieudongtb.IOID, 0) = 0, 0, 1) AS `Transferred`
//                    , ifnull(dsdieudongtb.IOID, 0) AS EWIOID
//                    , ifnull(dsdieudongtb.IFID_M706, 0) AS EWIFID
//                    , ifnull(iformdieudong.Status, 0) AS EWStatus
//                    , ycthietbi.SoPhieu AS DocNo
//                    , danhsachtb.MaThietBi AS EqCode
//                    , danhsachtb.TenThietBi AS EqName
//                    , danhsachtb.IOID AS EqIOID
//
//                    , if(ifnull(dieudongtb.IOID, 0) <> 0, dieudongtb.NgayBatDau, ycdieudong.NgayBatDau) AS EqStartDate
//                    , if(ifnull(dieudongtb.IOID, 0) <> 0, dieudongtb.NgayKetThuc, ycdieudong.NgayKetThuc) AS EqEndDate
//                    , HanHCKD as HanHCKD
//                    , danhsachtb.lft
//                    , danhsachtb.rgt
//
//                FROM OYeuCauTrangThietBi AS ycdieudong
//                INNER JOIN OYeuCauTrangThietBiVatTu AS ycthietbi ON ycdieudong.IFID_M751 = ycthietbi.IFID_M751
//                INNER JOIN OLoaiThietBi AS loaitb ON loaitb.IOID = ycdieudong.Ref_LoaiThietBi
//                INNER JOIN qsiforms AS iformyeucau ON iformyeucau.IFID = ycthietbi.IFID_M751
//                INNER JOIN OLoaiThietBi AS loaitbcon
//                    ON loaitbcon.lft >= loaitb.lft and loaitbcon.rgt <= loaitb.rgt
//                INNER JOIN ODanhSachThietBi AS danhsachtb
//                    ON danhsachtb.Ref_LoaiThietBi = loaitbcon.IOID
//                INNER JOIN OLichThietBi AS dieudongtb ON dieudongtb.Ref_PhieuYeuCau = ycthietbi.IOID
//                INNER JOIN qsiforms AS iformdieudong ON iformdieudong.IFID = dieudongtb.IFID_M706
//                LEFT JOIN ODanhSachDieuDongThietBi AS dsdieudongtb ON
//                      dieudongtb.IFID_M706 = dsdieudongtb.IFID_M706
//                      AND dsdieudongtb.Ref_MaThietBi = danhsachtb.IOID
//                        /*AND dieudongtb.Ref_MaThietBi = danhsachtb.IOID*/
//
//                left join (select Ref_MaThietBi,group_concat(concat(Ref_Loai," ",NgayKiemDinhTiepTheo) SEPARATOR " <br> ") as HanHCKD
//                            from (select * from OHieuChuanKiemDinh
//                            order by NgayKiemDinhTiepTheo desc) as t
//                            group by Ref_MaThietBi,Loai,ChuKy) as hc
//                            on hc.Ref_MaThietBi = danhsachtb.IOID
//
//                WHERE
//                    IFNULL(danhsachtb.Ref_TrucThuoc, 0) = 0
//                    AND dieudongtb.IFID_M706 = %1$d
//                    AND iformyeucau.Status = 2 /* Chi xet cac yeu cau dieu dong duoc duyet */
//                    AND ifnull(danhsachtb.TrangThai, 0) NOT IN (%2$s)
//                    AND danhsachtb.IOID NOT IN
//                    (SELECT ODanhSachDieuDongThietBi.Ref_MaThietBi FROM ODanhSachDieuDongThietBi
//                    INNER JOIN OLichThietBi ON ODanhSachDieuDongThietBi.IFID_M706 = OLichThietBi.IFID_M706
//                    INNER JOIN qsiforms ON ODanhSachDieuDongThietBi.IFID_M706 = qsiforms.IFID
//                     WHERE
//                      qsiforms.Status > 2 AND
//                      ODanhSachDieuDongThietBi.Ref_MaThietBi = danhsachtb.IOID AND (
//                      OLichThietBi.NgayBatDau <= ycdieudong.NgayKetThuc AND OLichThietBi.NgayKetThuc >= ycdieudong.NgayBatDau))
//
//
//                ORDER BY ycdieudong.IFID_M751, ycdieudong.NgayBatDau, ycdieudong.IOID, loaitbcon.lft, danhsachtb.MaThietBi
//
//            ) AS a1
//            left join ODanhSachThietBi AS ThietBiCon ON a1.lft <= ThietBiCon.lft AND a1.rgt >= ThietBiCon.rgt
//            order by ThietBiCon.lft
//        ', $movingIFID, implode(', ', Qss_Lib_Extra_Const::$EQUIP_STATUS_STOP));

        // echo '<pre>'; print_r($sql); die;

        return $this->_o_DB->fetchAll($sql);
    }
    
    /**
     * Lấy lịch điều động thiết bị
     */
    public function getEquipmentMovement($startDate, $endDate, $equipIOID = 0)
    {
        $filter   = array();
        if($equipIOID)
        {
            $filter[] = sprintf(' dstb.IOID = %1$d ', $equipIOID );
        }
        $sFilter = count($filter)?sprintf(' where %1$s ', implode(' and ', $filter)):'';
        
        $sql = sprintf('
            SELECT
                dstb.IOID AS Ref_Equip
                ,ifnull(ltb.Ref_LichLamViec, dstb.Ref_LichLamViec) AS Ref_Cal
                ,if(ifnull(ltb.IOID, 0) <> 0, 1, 0) AS UseMove
                ,ltb.NgayBatDau AS StartDate
                ,ltb.NgayKetThuc AS EndDate 
            FROM ODanhSachThietBi AS dstb
            LEFT JOIN 
            (SELECT
                ltb.*
            FROM OLichThietBi AS ltb 
            LEFT JOIN qsiforms ON ltb.IFID_M706 = qsiforms.IFID
            WHERE 
                qsiforms.Status in  (2, 3)
            AND 
                ((%2$s >= ltb.NgayBatDau AND %3$s <= ltb.NgayKetThuc  ) 
                OR (ifnull(ltb.NgayKetThuc, \'\') = \'\'  AND  (ltb.NgayBatDau between %2$s and %3$s )))
            ) AS ltb ON dstb.IOID = ltb.Ref_MaThietBi 
            %1$s
            ORDER BY ltb.Ref_MaThietBi, ltb.NgayBatDau DESC, ltb.IOID 
		',$sFilter, $this->_o_DB->quote($startDate), $this->_o_DB->quote($endDate));
        return $this->_o_DB->fetchAll($sql);        
    }

    public function getNumOfOrderedEquipsByDepartment($dept = 0)
    {
        $where = $dept?sprintf(' AND Dept.DepartmentID = %1$d ', $dept):'';

        $sql = sprintf('
            SELECT                                                                
                LoaiThietBi.IOID AS Ref_LoaiThietBi
                , Dept.DepartmentID
                , SUM(IF( IFNULL(DanhSach.DaVe, 0) = 0, 1, 0 )) AS SoLuongDaDieuDong
            FROM OLichThietBi AS DieuDong
            INNER JOIN qsiforms AS IformDieuDong ON DieuDong.IFID_M706 = IformDieuDong.IFID
            INNER JOIN ODanhSachDieuDongThietBi AS DanhSach ON DieuDong.IFID_M706 = DanhSach.IFID_M706
            INNER JOIN ODanhSachThietBi AS ThietBi ON IFNULL(DanhSach.Ref_MaThietBi, 0) = ThietBi.IOID
            INNER JOIN qsdepartments AS Dept ON ThietBi.DeptID = Dept.DepartmentID
            INNER JOIN OLoaiThietBi AS LoaiThietBi ON IFNULL(ThietBi.Ref_LoaiThietBi, 0) = LoaiThietBi.IOID            
            WHERE IFNULL(ThietBi.TrangThai, 0) = 0 AND IFNULL(IformDieuDong.Status, 0) IN (2, 3) AND IFNULL(DanhSach.DaVe, 0) = 0  %1$s
            GROUP BY LoaiThietBi.IOID, Dept.DepartmentID
        ', $where);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getNumOfRequiredEquipsByDepartment($dept = 0)
    {
        $where = $dept?sprintf(' AND Dept.DepartmentID = %1$d ', $dept):'';

        $sql = sprintf('
            SELECT                                                                
                LoaiThietBi.IOID AS Ref_LoaiThietBi
                , Dept.DepartmentID
                , SUM(IFNULL(DanhSach.SoLuong, 0)) AS SoLuongDangYeuCau
                , DanhSach.DonViTinh
            FROM OYeuCauTrangThietBiVatTu AS DieuDong
            INNER JOIN qsiforms AS IformDieuDong ON DieuDong.IFID_M751 = IformDieuDong.IFID
            INNER JOIN OYeuCauTrangThietBi AS DanhSach ON DieuDong.IFID_M751 = DanhSach.IFID_M751            
            INNER JOIN qsdepartments AS Dept ON DieuDong.DeptID = Dept.DepartmentID
            INNER JOIN OLoaiThietBi AS LoaiThietBi ON IFNULL(DanhSach.Ref_LoaiThietBi, 0) = LoaiThietBi.IOID            
            WHERE IFNULL(IformDieuDong.Status, 0) = 2 %1$s
            GROUP BY LoaiThietBi.IOID, Dept.DepartmentID
        ', $where);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getNumOfEquipsByDepartment($dept = 0)
    {
        $where = $dept?sprintf(' AND Dept.DepartmentID = %1$d ', $dept):'';

        $sql = sprintf('
            SELECT                                                                
                LoaiThietBi.IOID AS Ref_LoaiThietBi
                , Dept.DepartmentID
                , LoaiThietBi.TenLoai AS LoaiThietBi
                , LoaiThietBi.MaLoai AS MaLoaiThietBi
                , Dept.Name AS DonViQuanLy
                , COUNT(1) AS SoLuong
                , SUM(CASE WHEN IFNULL(ThietBi.TrangThai, 0) = 3 THEN 1 ELSE 0 END) AS SoLuongHong
            FROM OLoaiThietBi AS LoaiThietBi
            INNER JOIN ODanhSachThietBi AS ThietBi ON IFNULL(ThietBi.Ref_LoaiThietBi, 0) = LoaiThietBi.IOID
            INNER JOIN qsdepartments AS Dept ON ThietBi.DeptID = Dept.DepartmentID                        
            WHERE IFNULL(ThietBi.TrangThai, 0) IN  (0, 3) %1$s
            GROUP BY LoaiThietBi.IOID, Dept.DepartmentID
        ', $where);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }



    public function getNumOfOrderedToolsByDepartment($dept = 0)
    {
        $where = $dept?sprintf(' AND Dept.DepartmentID = %1$d ', $dept):'';

        $sql = sprintf('
            SELECT                                                                
                CongCu.IOID AS Ref_CongCuDungCu
                , Dept.DepartmentID
                , SUM(
                    CASE 
                    WHEN (IFNULL(DanhSach.SoLuong, 0) - IFNULL(DanhSach.SoLuongDaVe, 0)) > 0
                    THEN IFNULL(DanhSach.SoLuong, 0) - IFNULL(DanhSach.SoLuongDaVe, 0)
                    ELSE 0 END                
                ) AS SoLuongDaDieuDong
            FROM OLichThietBi AS DieuDong
            INNER JOIN qsiforms AS IformDieuDong ON DieuDong.IFID_M706 = IformDieuDong.IFID
            INNER JOIN ODanhSachDieuDongCongCu AS DanhSach ON DieuDong.IFID_M706 = DanhSach.IFID_M706            
            INNER JOIN ODanhMucCongCuDungCu AS CongCu ON IFNULL(DanhSach.Ref_Ma, 0) = CongCu.IOID                                        
            INNER JOIN qsdepartments AS Dept ON CongCu.DeptID = Dept.DepartmentID                                       
            WHERE IFNULL(IformDieuDong.Status, 0) IN (2, 3) %1$s
            GROUP BY Dept.DepartmentID
        ', $where);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getNumOfRequiredToolsByDepartment($dept = 0)
    {
        $where = $dept?sprintf(' AND Dept.DepartmentID = %1$d ', $dept):'';

        $sql = sprintf('
            SELECT                                                                
                CongCu.IOID AS Ref_CongCuDungCu
                , Dept.DepartmentID
                , SUM(IFNULL(DanhSach.SoLuong, 0)) AS SoLuongDangYeuCau
                , DanhSach.DonViTinh
            FROM OYeuCauTrangThietBiVatTu AS DieuDong
            INNER JOIN qsiforms AS IformDieuDong ON DieuDong.IFID_M751 = IformDieuDong.IFID
            INNER JOIN OYeuCauCongCu AS DanhSach ON DieuDong.IFID_M751 = DanhSach.IFID_M751            
            INNER JOIN qsdepartments AS Dept ON DieuDong.DeptID = Dept.DepartmentID
            INNER JOIN ODanhMucCongCuDungCu AS CongCu ON IFNULL(DanhSach.Ref_Ma, 0) = CongCu.IOID            
            WHERE IFNULL(IformDieuDong.Status, 0) = 2 %1$s
            GROUP BY Dept.DepartmentID
        ', $where);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getNumOfToolsByDepartment($dept = 0)
    {
        $where = $dept?sprintf(' AND Dept.DepartmentID = %1$d ', $dept):'';

        $sql = sprintf('
            SELECT                                                                
                CongCu.IOID AS Ref_CongCuDungCu
                , Dept.DepartmentID
                , CongCu.Ten AS TenCongCuDungCu
                , CongCu.Ma AS MaCongCuDungCu
                , ChiTietCongCu.DonViTinh
                , Dept.Name AS DonViQuanLy
                , IFNULL(ChiTietCongCu.SoLuong, 0) AS SoLuong
            FROM ODanhMucCongCuDungCu AS CongCu            
            INNER JOIN qsdepartments AS Dept ON CongCu.DeptID = Dept.DepartmentID     
            INNER JOIN OQuanLyCongCuDungCu AS ChiTietCongCu ON CongCu.IOID = ChiTietCongCu.Ref_Ma
            WHERE 1=1 %1$s            
        ', $where);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getToolsByRequests($requestIOID)
    {
        $sql = sprintf('
            SELECT CongCu.*
            FROM OYeuCauTrangThietBiVatTu AS YeuCau
            INNER JOIN qsiforms AS Iform ON YeuCau.IFID_M751 = Iform.IFID
            INNER JOIN OYeuCauCongCu AS CongCu ON YeuCau.IFID_M751 = CongCu.IFID_M751
            WHERE YeuCau.IOID = %1$d AND Iform.Status = 2
        ', $requestIOID);
        // echo '<pre>'; print_r($sql); die;

        return $this->_o_DB->fetchAll($sql);
    }

    public function getSelectedTools($moveIOID)
    {
        $sql = sprintf('
            SELECT CongCu.*, Iform.Status
            FROM OLichThietBi AS DieuDong
            INNER JOIN qsiforms AS Iform ON DieuDong.IFID_M706 = Iform.IFID
            INNER JOIN ODanhSachDieuDongCongCu AS CongCu ON CongCu.IFID_M706 = DieuDong.IFID_M706
            WHERE DieuDong.IOID = %1$d
        ', $moveIOID);
        // echo '<pre>'; print_r($sql); die;

        return $this->_o_DB->fetchAll($sql);
    }

    public function getToolsInOther($requestIOID, $moveIOID)
    {
        $sql = sprintf('
            SELECT CongCu2.Ref_Ma, SUM(IFNULL(CongCu2.SoLuong, 0) - IFNULL(CongCu2.SoLuongDaVe,0)) AS SoLuongDaDieuDong
            FROM OYeuCauTrangThietBiVatTu AS YeuCau
            INNER JOIN OYeuCauCongCu AS CongCu ON YeuCau.IFID_M751 = CongCu.IFID_M751
            INNER JOIN OLichThietBi AS DieuDong ON YeuCau.IOID = IFNULL(DieuDong.Ref_PhieuYeuCau, 0)
            INNER JOIN ODanhSachDieuDongCongCu AS CongCu2 ON DieuDong.IFID_M706 = CongCu2.IFID_M706 AND CongCu.Ref_Ma = CongCu2.Ref_Ma 
            WHERE YeuCau.IOID = %1$d AND DieuDong.IOID != %2$d
            GROUP BY CongCu2.Ref_Ma
        ', $requestIOID, $moveIOID);
        return $this->_o_DB->fetchAll($sql);
    }
}

?>