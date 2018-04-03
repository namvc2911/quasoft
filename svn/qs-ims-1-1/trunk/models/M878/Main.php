<?php
class Qss_Model_M878_Main extends Qss_Model_Abstract
{
    public function getData(
        $date
        , $refLoaiKhoaHoc   = 0
        , $refPhongBan      = 0
        , $refNhanVien      = 0
        , $filterByNhanVien = 0
        , $filterByChucDanh = 0
    )
    {
        $sql = ' SELECT * FROM (';

        if($filterByNhanVien || (!$filterByNhanVien && !$filterByChucDanh)) {
            $sql .= sprintf('
            -- Lấy theo nhân viên
            SELECT
                ODanhSachNhanVien.IOID AS Ref_MaNhanVien
                , ODanhSachNhanVien.MaNhanVien
                , ODanhSachNhanVien.TenNhanVien
                , ODanhSachNhanVien.MaPhongBan
                , ODanhSachNhanVien.ChucVu
                , ODanhSachNhanVien.ChucDanh
                , OYeuCauDaoTao.MaYeuCau
                , OYeuCauDaoTao.TenYeuCau
                , DaoTaoLanCuoi.NgayCuoi
                , DangHocTai.LopHoc
                , ODaoTaoTheoNhanVien.Ref_KhoaHoc    
                , ODaoTaoTheoNhanVien.KhoaHoc
                , IFNULL(KiemTraDaHoc.DangHocKhoaNay, 0) AS DangHocKhoaNay
                , CASE WHEN OYeuCauDaoTao.LapLai = "S" THEN OYeuCauDaoTao.NgayBatDau
                    WHEN OYeuCauDaoTao.LapLai = "D" 
                        THEN IF( 
                            -- Nếu có lần cuối cộng theo lần cuối không sẽ cộng theo ngày bắt đầu
                            IFNULL(DaoTaoLanCuoi.NgayCuoi, "") = "" 
                            , OYeuCauDaoTao.NgayBatDau
                            , DATE_ADD(DaoTaoLanCuoi.NgayCuoi, INTERVAL OYeuCauDaoTao.SoKyLap DAY)
                        )
                    WHEN OYeuCauDaoTao.LapLai = "W"
                        THEN IF( 
                            -- Nếu có lần cuối cộng theo lần cuối không sẽ cộng theo ngày bắt đầu
                            IFNULL(DaoTaoLanCuoi.NgayCuoi, "") = "" 
                            , OYeuCauDaoTao.NgayBatDau
                            , DATE_ADD(DaoTaoLanCuoi.NgayCuoi, INTERVAL (OYeuCauDaoTao.SoKyLap * 7) DAY)
                        )   
                    WHEN OYeuCauDaoTao.LapLai = "M"
                        THEN IF( 
                            -- Nếu có lần cuối cộng theo lần cuối không sẽ cộng theo ngày bắt đầu
                            IFNULL(DaoTaoLanCuoi.NgayCuoi, "") = "" 
                            , OYeuCauDaoTao.NgayBatDau
                            , DATE_ADD(DaoTaoLanCuoi.NgayCuoi, INTERVAL (OYeuCauDaoTao.SoKyLap * 30) DAY)
                        )  
                    WHEN OYeuCauDaoTao.LapLai = "Y"
                        THEN IF( 
                            -- Nếu có lần cuối cộng theo lần cuối không sẽ cộng theo ngày bắt đầu
                            IFNULL(DaoTaoLanCuoi.NgayCuoi, "") = "" 
                            , OYeuCauDaoTao.NgayBatDau
                            , DATE_ADD(DaoTaoLanCuoi.NgayCuoi, INTERVAL (OYeuCauDaoTao.SoKyLap * 365) DAY)
                        )
                END AS NgayYeuCau
                
            FROM OYeuCauDaoTao
            INNER JOIN qsiforms ON OYeuCauDaoTao.IFID_M087 = qsiforms.IFID
            INNER JOIN ODaoTaoTheoNhanVien ON OYeuCauDaoTao.IFID_M087 = ODaoTaoTheoNhanVien.IFID_M087
            INNER JOIN ODanhSachNhanVien ON ODaoTaoTheoNhanVien.Ref_MaNhanVien = ODanhSachNhanVien.IOID
            -- Kiểm tra loại khóa học cho nhân viên đã được tạo chưa
            LEFT JOIN (
                SELECT OLopHoc.Ref_LoaiKhoaHoc, ODanhSachHocVien.Ref_MaNhanVien, 1 AS DangHocKhoaNay
                FROM OLopHoc
                INNER JOIN qsiforms ON OLopHoc.IFID_M328 = qsiforms.IFID
                INNER JOIN ODanhSachHocVien ON OLopHoc.IFID_M328 = ODanhSachHocVien.IFID_M328
                WHERE qsiforms.Status != 3
                GROUP BY  OLopHoc.Ref_LoaiKhoaHoc, ODanhSachHocVien.Ref_MaNhanVien
            ) AS KiemTraDaHoc ON ODaoTaoTheoNhanVien.Ref_KhoaHoc = KiemTraDaHoc.Ref_LoaiKhoaHoc
                AND ODaoTaoTheoNhanVien.Ref_MaNhanVien = KiemTraDaHoc.Ref_MaNhanVien
            -- Đào tạo lần cuối theo loại khóa học cho nhân viên
            LEFT JOIN (
                SELECT ODanhSachHocVien.Ref_MaNhanVien, OKhoaHoc.Ref_LoaiKhoaHoc, MAX(OLopHoc.NgayBatDau) AS NgayCuoi
                FROM OLopHoc
                INNER JOIN qsiforms ON OLopHoc.IFID_M328 = qsiforms.IFID
                INNER JOIN OKhoaHoc ON OLopHoc.Ref_KhoaHoc = OKhoaHoc.IOID
                INNER JOIN ODanhSachHocVien ON OLopHoc.IFID_M328 = ODanhSachHocVien.IFID_M328
                WHERE qsiforms.Status >= 2
                GROUP BY ODanhSachHocVien.Ref_MaNhanVien, OKhoaHoc.Ref_LoaiKhoaHoc
            ) AS DaoTaoLanCuoi ON ODaoTaoTheoNhanVien.Ref_KhoaHoc = DaoTaoLanCuoi.Ref_LoaiKhoaHoc 
                AND ODaoTaoTheoNhanVien.Ref_MaNhanVien = DaoTaoLanCuoi.Ref_MaNhanVien
            LEFT JOIN (
                SELECT 
                    ODanhSachHocVien.Ref_MaNhanVien
                    , OKhoaHoc.Ref_LoaiKhoaHoc
                    , GROUP_CONCAT(OLopHoc.MaLopHoc  SEPARATOR ", ") AS LopHoc
                FROM OLopHoc
                INNER JOIN OKhoaHoc ON OLopHoc.Ref_KhoaHoc = OKhoaHoc.IOID
                INNER JOIN ODanhSachHocVien ON OLopHoc.IFID_M328 = ODanhSachHocVien.IFID_M328
                GROUP BY ODanhSachHocVien.Ref_MaNhanVien, OKhoaHoc.Ref_LoaiKhoaHoc
            ) AS DangHocTai  ON ODaoTaoTheoNhanVien.Ref_KhoaHoc = DangHocTai.Ref_LoaiKhoaHoc 
                AND ODaoTaoTheoNhanVien.Ref_MaNhanVien = DangHocTai.Ref_MaNhanVien
            WHERE 
                qsiforms.Status = 2 -- Đã duyệt
                AND IFNULL(ODanhSachNhanVien.ThoiViec, 0) != 1 -- Đang làm việc
                AND 
                (
                    (
                        OYeuCauDaoTao.LapLai = "S"
                        AND IFNULL(DaoTaoLanCuoi.NgayCuoi, "") = ""
                    )
                    OR (
                        OYeuCauDaoTao.LapLai = "D"                     
                        AND (
                            IFNULL(DaoTaoLanCuoi.NgayCuoi, "") = ""
                            OR                                     
                            (
                                DATEDIFF(%1$s, DaoTaoLanCuoi.NgayCuoi) > 0
                                AND DATEDIFF(%1$s, DaoTaoLanCuoi.NgayCuoi) > OYeuCauDaoTao.SoKyLap
                            )
                        )                    
                    )
                    OR (
                        OYeuCauDaoTao.LapLai = "W"                     
                        AND (
                            IFNULL(DaoTaoLanCuoi.NgayCuoi, "") = ""
                            OR                             
                            (
                                DATEDIFF(%1$s, DaoTaoLanCuoi.NgayCuoi) > 0
                                AND DATEDIFF(%1$s, DaoTaoLanCuoi.NgayCuoi) > (OYeuCauDaoTao.SoKyLap * 7)
                            )                        
                        )                    
                    )        
                    OR (
                        OYeuCauDaoTao.LapLai = "M"                     
                        AND (
                            IFNULL(DaoTaoLanCuoi.NgayCuoi, "") = ""
                            OR                                     
                            (
                                DATEDIFF(%1$s, DaoTaoLanCuoi.NgayCuoi) > 0
                                AND DATEDIFF(%1$s, DaoTaoLanCuoi.NgayCuoi) > (OYeuCauDaoTao.SoKyLap * 30)
                            )                        
                        )                    
                    )      
                    OR (
                        OYeuCauDaoTao.LapLai = "Y"                     
                        AND (
                            IFNULL(DaoTaoLanCuoi.NgayCuoi, "") = ""
                            OR 
                            (
                                DATEDIFF(%1$s, DaoTaoLanCuoi.NgayCuoi) > 0
                                AND DATEDIFF(%1$s, DaoTaoLanCuoi.NgayCuoi) > (OYeuCauDaoTao.SoKyLap * 365)
                            )
                        )                    
                    )   
                )                                                                               
            ', $this->_o_DB->quote($date));

            if($refLoaiKhoaHoc) {
                $sql .= sprintf(' AND ODaoTaoTheoNhanVien.Ref_KhoaHoc  = %1$d ', $refLoaiKhoaHoc);
            }

            if($refPhongBan) {
                $sql .= sprintf(' 
                    AND ODanhSachNhanVien.Ref_MaPhongBan  IN (
                        SELECT OPhongBan.IOID 
                        FROM OPhongBan
                        WHERE lft >= (SELECT lft FROM OPhongBan WHERE IOID = %1$d)
                            AND rgt <= (SELECT rgt FROM OPhongBan WHERE IOID = %1$d)
                    )
                ', $refPhongBan);
            }

            if($refNhanVien) {
                $sql .= sprintf(' AND ODanhSachNhanVien.IOID  = %1$d ', $refNhanVien);
            }
        }

        if(($filterByNhanVien && $filterByChucDanh) || (!$filterByNhanVien && !$filterByChucDanh)) {
            $sql .= sprintf(' UNION ');
        }

        if($filterByChucDanh || (!$filterByNhanVien && !$filterByChucDanh)) {
            $sql .= sprintf('                                       
            -- Lấy theo chức danh
            SELECT 
                ODanhSachNhanVien.IOID AS Ref_MaNhanVien
                , ODanhSachNhanVien.MaNhanVien
                , ODanhSachNhanVien.TenNhanVien
                , ODanhSachNhanVien.MaPhongBan
                , ODanhSachNhanVien.ChucVu
                , ODanhSachNhanVien.ChucDanh
                , OYeuCauDaoTao.MaYeuCau
                , OYeuCauDaoTao.TenYeuCau
                , DaoTaoLanCuoi.NgayCuoi
                , DangHocTai.LopHoc
                , ODaoTaoTheoChucDanh.Ref_KhoaHoc    
                , ODaoTaoTheoChucDanh.KhoaHoc       
                , IFNULL(KiemTraDaHoc.DangHocKhoaNay, 0) AS DangHocKhoaNay                         
                , CASE WHEN OYeuCauDaoTao.LapLai = "S" THEN OYeuCauDaoTao.NgayBatDau
                    WHEN OYeuCauDaoTao.LapLai = "D" 
                        THEN IF( 
                            -- Nếu có lần cuối cộng theo lần cuối không sẽ cộng theo ngày bắt đầu
                            IFNULL(DaoTaoLanCuoi.NgayCuoi, "") = "" 
                            , OYeuCauDaoTao.NgayBatDau
                            , DATE_ADD(DaoTaoLanCuoi.NgayCuoi, INTERVAL OYeuCauDaoTao.SoKyLap DAY)
                        )
                    WHEN OYeuCauDaoTao.LapLai = "W"
                        THEN IF( 
                            -- Nếu có lần cuối cộng theo lần cuối không sẽ cộng theo ngày bắt đầu
                            IFNULL(DaoTaoLanCuoi.NgayCuoi, "") = "" 
                            , OYeuCauDaoTao.NgayBatDau
                            , DATE_ADD(DaoTaoLanCuoi.NgayCuoi, INTERVAL (OYeuCauDaoTao.SoKyLap * 7) DAY)
                        )   
                    WHEN OYeuCauDaoTao.LapLai = "M"
                        THEN IF( 
                            -- Nếu có lần cuối cộng theo lần cuối không sẽ cộng theo ngày bắt đầu
                            IFNULL(DaoTaoLanCuoi.NgayCuoi, "") = "" 
                            , OYeuCauDaoTao.NgayBatDau
                            , DATE_ADD(DaoTaoLanCuoi.NgayCuoi, INTERVAL (OYeuCauDaoTao.SoKyLap * 30) DAY)
                        )  
                    WHEN OYeuCauDaoTao.LapLai = "Y"
                        THEN IF( 
                            -- Nếu có lần cuối cộng theo lần cuối không sẽ cộng theo ngày bắt đầu
                            IFNULL(DaoTaoLanCuoi.NgayCuoi, "") = "" 
                            , OYeuCauDaoTao.NgayBatDau
                            , DATE_ADD(DaoTaoLanCuoi.NgayCuoi, INTERVAL (OYeuCauDaoTao.SoKyLap * 365) DAY)
                        )
                    END AS NgayYeuCau                
            FROM OYeuCauDaoTao
            INNER JOIN qsiforms ON OYeuCauDaoTao.IFID_M087 = qsiforms.IFID
            INNER JOIN ODaoTaoTheoChucDanh ON OYeuCauDaoTao.IFID_M087 = ODaoTaoTheoChucDanh.IFID_M087
            INNER JOIN ODanhSachNhanVien ON ODaoTaoTheoChucDanh.Ref_MaChucDanh = ODanhSachNhanVien.Ref_ChucDanh
            -- Kiểm tra loại khóa học cho nhân viên đã được tạo chưa
            LEFT JOIN (
                SELECT OLopHoc.Ref_LoaiKhoaHoc, ODanhSachHocVien.Ref_MaNhanVien, 1 AS DangHocKhoaNay
                FROM OLopHoc
                INNER JOIN qsiforms ON OLopHoc.IFID_M328 = qsiforms.IFID
                INNER JOIN ODanhSachHocVien ON OLopHoc.IFID_M328 = ODanhSachHocVien.IFID_M328
                WHERE qsiforms.Status != 3
                GROUP BY  OLopHoc.Ref_LoaiKhoaHoc, ODanhSachHocVien.Ref_MaNhanVien
            ) AS KiemTraDaHoc ON ODaoTaoTheoChucDanh.Ref_KhoaHoc = KiemTraDaHoc.Ref_LoaiKhoaHoc
                AND ODanhSachNhanVien.IOID = KiemTraDaHoc.Ref_MaNhanVien            
            -- Đào tạo lần cuối theo loại khóa học cho nhân viên
            LEFT JOIN (
                SELECT ODanhSachHocVien.Ref_MaNhanVien, OKhoaHoc.Ref_LoaiKhoaHoc, MAX(OLopHoc.NgayBatDau) AS NgayCuoi
                FROM OLopHoc
                INNER JOIN qsiforms ON OLopHoc.IFID_M328 = qsiforms.IFID
                INNER JOIN OKhoaHoc ON OLopHoc.Ref_KhoaHoc = OKhoaHoc.IOID
                INNER JOIN ODanhSachHocVien ON OLopHoc.IFID_M328 = ODanhSachHocVien.IFID_M328
                WHERE qsiforms.Status >= 2
                GROUP BY ODanhSachHocVien.Ref_MaNhanVien, OKhoaHoc.Ref_LoaiKhoaHoc
            ) AS DaoTaoLanCuoi ON ODaoTaoTheoChucDanh.Ref_KhoaHoc = DaoTaoLanCuoi.Ref_LoaiKhoaHoc 
                AND ODanhSachNhanVien.IOID = DaoTaoLanCuoi.Ref_MaNhanVien   
            LEFT JOIN (
                SELECT 
                    ODanhSachHocVien.Ref_MaNhanVien
                    , OKhoaHoc.Ref_LoaiKhoaHoc
                    , GROUP_CONCAT(OLopHoc.MaLopHoc  SEPARATOR ", ") AS LopHoc
                FROM OLopHoc
                INNER JOIN OKhoaHoc ON OLopHoc.Ref_KhoaHoc = OKhoaHoc.IOID
                INNER JOIN ODanhSachHocVien ON OLopHoc.IFID_M328 = ODanhSachHocVien.IFID_M328
                GROUP BY ODanhSachHocVien.Ref_MaNhanVien, OKhoaHoc.Ref_LoaiKhoaHoc
            ) AS DangHocTai  ON ODaoTaoTheoChucDanh.Ref_KhoaHoc = DangHocTai.Ref_LoaiKhoaHoc 
                AND ODanhSachNhanVien.IOID = DangHocTai.Ref_MaNhanVien                  
            WHERE 
                qsiforms.Status = 2 -- Đã duyệt
                AND IFNULL(ODanhSachNhanVien.ThoiViec, 0) != 1 -- Đang làm việc
                AND             
                (
                    (
                        OYeuCauDaoTao.LapLai = "S"
                        AND IFNULL(DaoTaoLanCuoi.NgayCuoi, "") = ""
                    )
                    OR (
                        OYeuCauDaoTao.LapLai = "D"                     
                        AND (
                            IFNULL(DaoTaoLanCuoi.NgayCuoi, "") = ""
                            OR                                     
                            (
                                DATEDIFF(%1$s, DaoTaoLanCuoi.NgayCuoi) > 0
                                AND DATEDIFF(%1$s, DaoTaoLanCuoi.NgayCuoi) > OYeuCauDaoTao.SoKyLap
                            )
                        )                    
                    )
                    OR (
                        OYeuCauDaoTao.LapLai = "W"                     
                        AND (
                            IFNULL(DaoTaoLanCuoi.NgayCuoi, "") = ""
                            OR                             
                            (
                                DATEDIFF(%1$s, DaoTaoLanCuoi.NgayCuoi) > 0
                                AND DATEDIFF(%1$s, DaoTaoLanCuoi.NgayCuoi) > (OYeuCauDaoTao.SoKyLap * 7)
                            )                        
                        )                    
                    )        
                    OR (
                        OYeuCauDaoTao.LapLai = "M"                     
                        AND (
                            IFNULL(DaoTaoLanCuoi.NgayCuoi, "") = ""
                            OR                                     
                            (
                                DATEDIFF(%1$s, DaoTaoLanCuoi.NgayCuoi) > 0
                                AND DATEDIFF(%1$s, DaoTaoLanCuoi.NgayCuoi) > (OYeuCauDaoTao.SoKyLap * 30)
                            )                        
                        )                    
                    )      
                    OR (
                        OYeuCauDaoTao.LapLai = "Y"                     
                        AND (
                            IFNULL(DaoTaoLanCuoi.NgayCuoi, "") = ""
                            OR 
                            (
                                DATEDIFF(%1$s, DaoTaoLanCuoi.NgayCuoi) > 0
                                AND DATEDIFF(%1$s, DaoTaoLanCuoi.NgayCuoi) > (OYeuCauDaoTao.SoKyLap * 365)
                            )
                        )                    
                    )   
                )
                                                                       
            ', $this->_o_DB->quote($date));

            if($refLoaiKhoaHoc) {
                $sql .= sprintf(' AND ODaoTaoTheoChucDanh.Ref_KhoaHoc  = %1$d ', $refLoaiKhoaHoc);
            }

            if($refPhongBan) {
                $sql .= sprintf(' 
                    AND ODanhSachNhanVien.Ref_MaPhongBan  IN (
                        SELECT OPhongBan.IOID 
                        FROM OPhongBan
                        WHERE lft >= (SELECT lft FROM OPhongBan WHERE IOID = %1$d)
                            AND rgt <= (SELECT rgt FROM OPhongBan WHERE IOID = %1$d)
                    )
                ', $refPhongBan);
            }

            if($refNhanVien) {
                $sql .= sprintf(' AND ODanhSachNhanVien.IOID  = %1$d ', $refNhanVien);
            }
        }

        $sql .= ' ) AS DaoTao ';

        $sql .= sprintf(' 
            WHERE  NgayYeuCau <= %1$s AND (IFNULL(NgayCuoi, "") = "" OR NgayYeuCau > NgayCuoi) 
        ', $this->_o_DB->quote($date));
        $sql .= sprintf(' ORDER BY  Ref_KhoaHoc, MaNhanVien, MaYeuCau ');

        //echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }
}