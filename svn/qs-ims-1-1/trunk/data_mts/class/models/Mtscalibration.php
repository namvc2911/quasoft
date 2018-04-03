<?php
class Qss_Model_Mtscalibration extends Qss_Model_Abstract
{
    public function getKeHoachHieuChuanKiemDinh($loaiThietBi, $ngayBatDau, $ngayKetThuc)
    {
        $sql = sprintf('
            SELECT 
                ThietBi.*       
                , (CASE WHEN IFNULL(HieuChuanLanCuoiTrongKy.IOID, 0) != 0 THEN HieuChuanLanCuoiTrongKy.NgayKiemDinhTiepTheo
                    WHEN IFNULL(ThietBi.NgayDuaVaoSuDung, "") != "" THEN DATE_ADD(ThietBi.NgayDuaVaoSuDung, INTERVAL QuyDinh.ChuKy MONTH) - INTERVAL 1 DAY
                    ELSE "0000-00-00" END) AS NgayKiemDinhTiepTheo
                , QuyDinh.HetHieuLuc
            FROM ODanhSachThietBi AS ThietBi
            INNER JOIN OQuyDinhHieuChuanKiemDinh AS QuyDinh ON  ThietBi.IOID = QuyDinh.Ref_MaThietBi
            INNER JOIN OLoaiThietBi AS LoaiThietBi ON IFNULL(ThietBi.Ref_LoaiThietBi, 0) = LoaiThietBi.IOID
            INNER JOIN OLoaiThietBi AS LoaiThietBiRoot ON LoaiThietBiRoot.lft <= LoaiThietBi.lft 
                AND LoaiThietBiRoot.rgt >= LoaiThietBi.rgt
                AND IFNULL(LoaiThietBiRoot.Ref_TrucThuoc, 0) = 0            
            LEFT JOIN (
                SELECT *
                FROM (
                    SELECT *
                    FROM OHieuChuanKiemDinh
                    WHERE NgayKiemDinhTiepTheo <= %2$s                    
                    ORDER BY MaThietBi, NgayKiemDinhTiepTheo DESC 
                    LIMIT 9999999999999
                ) AS HieuChuanCuoi
                GROUP BY HieuChuanCuoi.MaThietBi
            ) AS HieuChuanLanCuoiTrongKy ON ThietBi.IOID = HieuChuanLanCuoiTrongKy.Ref_MaThietBi
            WHERE     
                (LoaiThietBiRoot.MaLoai IN (%3$s) OR LoaiThietBi.MaLoai IN (%3$s))                 
            HAVING 
                (
                    NgayKiemDinhTiepTheo >= CURDATE()                      
                    AND IFNULL(ThietBi.TrangThai, 0) = 0 
                    AND IFNULL(QuyDinh.HetHieuLuc, 0) = 0       
                    AND (NgayKiemDinhTiepTheo BETWEEN %1$s AND %2$s)                              
                )
                OR 
                (
                    NgayKiemDinhTiepTheo < CURDATE()   
                    AND (NgayKiemDinhTiepTheo BETWEEN %1$s AND %2$s)                      
                )                          
        ', $this->_o_DB->quote($ngayBatDau), $this->_o_DB->quote($ngayKetThuc), implode(', ', $loaiThietBi));

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getKeHoachHieuChuanKiemDinhTheoLoaiThietBi($loaiThietBi, $ngayBatDau, $ngayKetThuc)
    {
        $sql = sprintf('
            SELECT 
                ThietBi.*       
                , LoaiThietBi.*
                , ThietBi.SoLuong
            FROM ODanhSachThietBi AS ThietBi
            INNER JOIN OQuyDinhHieuChuanKiemDinh AS QuyDinh ON  ThietBi.IOID = QuyDinh.Ref_MaThietBi
            INNER JOIN OLoaiThietBi AS LoaiThietBi ON IFNULL(ThietBi.Ref_LoaiThietBi, 0) = LoaiThietBi.IOID
            INNER JOIN OLoaiThietBi AS LoaiThietBiRoot ON LoaiThietBiRoot.lft <= LoaiThietBi.lft 
                AND LoaiThietBiRoot.rgt >= LoaiThietBi.rgt
                AND IFNULL(LoaiThietBiRoot.Ref_TrucThuoc, 0) = 0            
            LEFT JOIN (
                SELECT *
                FROM (
                    SELECT *
                    FROM OHieuChuanKiemDinh
                    WHERE NgayKiemDinhTiepTheo <= %2$s                    
                    ORDER BY MaThietBi, NgayKiemDinhTiepTheo DESC 
                    LIMIT 9999999999999
                ) AS HieuChuanCuoi
                GROUP BY HieuChuanCuoi.MaThietBi
            ) AS HieuChuanLanCuoiTrongKy ON ThietBi.IOID = HieuChuanLanCuoiTrongKy.Ref_MaThietBi
            WHERE                     
                (LoaiThietBiRoot.MaLoai IN (%3$s) OR LoaiThietBi.MaLoai IN (%3$s))     
                AND
                (
                    (
                        (CASE WHEN IFNULL(HieuChuanLanCuoiTrongKy.IOID, 0) != 0 THEN HieuChuanLanCuoiTrongKy.NgayKiemDinhTiepTheo
                        WHEN IFNULL(ThietBi.NgayDuaVaoSuDung, "") != "" THEN DATE_ADD(ThietBi.NgayDuaVaoSuDung, INTERVAL QuyDinh.ChuKy MONTH) - INTERVAL 1 DAY
                        ELSE "0000-00-00" END) >= CURDATE()                    
                        AND IFNULL(ThietBi.TrangThai, 0) = 0 
                        AND IFNULL(QuyDinh.HetHieuLuc, 0) = 0 
                        AND ((CASE WHEN IFNULL(HieuChuanLanCuoiTrongKy.IOID, 0) != 0 THEN HieuChuanLanCuoiTrongKy.NgayKiemDinhTiepTheo
                            WHEN IFNULL(ThietBi.NgayDuaVaoSuDung, "") != "" THEN DATE_ADD(ThietBi.NgayDuaVaoSuDung, INTERVAL QuyDinh.ChuKy MONTH) - INTERVAL 1 DAY
                            ELSE "0000-00-00" END) BETWEEN %1$s AND %2$s)                              
                    )
                    OR 
                    (
                        (CASE WHEN IFNULL(HieuChuanLanCuoiTrongKy.IOID, 0) != 0 THEN HieuChuanLanCuoiTrongKy.NgayKiemDinhTiepTheo
                        WHEN IFNULL(ThietBi.NgayDuaVaoSuDung, "") != "" THEN DATE_ADD(ThietBi.NgayDuaVaoSuDung, INTERVAL QuyDinh.ChuKy MONTH) - INTERVAL 1 DAY
                        ELSE "0000-00-00" END) < CURDATE()   
                        AND ((CASE WHEN IFNULL(HieuChuanLanCuoiTrongKy.IOID, 0) != 0 THEN HieuChuanLanCuoiTrongKy.NgayKiemDinhTiepTheo
                            WHEN IFNULL(ThietBi.NgayDuaVaoSuDung, "") != "" THEN DATE_ADD(ThietBi.NgayDuaVaoSuDung, INTERVAL QuyDinh.ChuKy MONTH) - INTERVAL 1 DAY
                            ELSE "0000-00-00" END) BETWEEN %1$s AND %2$s)                      
                    )
                )

            -- GROUP BY LoaiThietBi.IOID
            ORDER BY LoaiThietBi.TenLoai
        ', $this->_o_DB->quote($ngayBatDau), $this->_o_DB->quote($ngayKetThuc), implode(', ', $loaiThietBi));

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }
}