<?php
class Qss_Model_Mtsplan extends Qss_Model_Abstract
{
    public function getGeneralPlans($year, $location= 0, $type = 0) {
        $whereSql1 = '';

        if($location) {
            $objLoc = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $location));

            if($objLoc) {
                $whereSql1 .= sprintf('
                    AND (
                        IFNULL(KeHoach.Ref_MaKhuVuc, 0) IN (SELECT IOID FROM OKhuVuc WHERE lft >= %1$d AND rgt <= %2$d)
                        OR
                        IFNULL(ThietBi.Ref_MaKhuVuc, 0) IN (SELECT IOID FROM OKhuVuc WHERE lft >= %1$d AND rgt <= %2$d)
                    )                    
                ', $objLoc->lft, $objLoc->rgt);
            }
        }

        if($type) {
            $whereSql1 .= sprintf(' AND IFNULL(KeHoach.LoaiKeHoach, 0) = %1$d ', $type);
        }

        $sql = sprintf('
            -- @Note: Lưu ý ở đây sử dụng inner join với thiết bị, nhóm loại và khu vực do các
            -- trường này bắt buộc trong phiếu bảo trì và danh sách thiết bị nếu có thay đổi cần phải sửa lại phần này

            SELECT 
                KeHoach.*
                , ThietBi.*
                , KhuVuc.IOID AS KhuVucIOID
                , KhuVuc.Ten AS TenKhuVuc
                , NhomThietBi.IOID AS NhomThietBiIOID
                , LoaiThietBi.IOID AS LoaiThietBiIOID
                , LoaiBaoTri.lft AS LoaiBaoTriLft
                , (IFNULL(KeHoach.TuLam, 0) + IFNULL(KeHoach.TrongCongTy, 0) + IFNULL(KeHoach.TrongTKV, 0) + IFNULL(KeHoach.NgoaiTKV, 0)) AS TongSo
            FROM OKeHoachBaoTri AS KeHoach
            INNER JOIN qsiforms AS IFormChiTiet ON KeHoach.IFID_M837 = IFormChiTiet.IFID
            INNER JOIN OKeHoachTongThe AS TongThe ON IFNULL(KeHoach.Ref_KeHoachTongThe, 0) = TongThe.IOID
            INNER JOIN qsiforms AS IFormChinh ON TongThe.IFID_M838 = IFormChinh.IFID
            INNER JOIN ODanhSachThietBi AS ThietBi ON IFNULL(KeHoach.Ref_MaThietBi, 0) = ThietBi.IOID
            INNER JOIN OKhuVuc AS KhuVuc ON ThietBi.Ref_MaKhuVuc = KhuVuc.IOID
            INNER JOIN OPhanLoaiBaoTri AS LoaiBaoTri ON IFNULL(KeHoach.Ref_LoaiBaoTri, 0) = LoaiBaoTri.IOID
            INNER JOIN OLoaiThietBi AS LoaiThietBi ON IFNULL(ThietBi.Ref_LoaiThietBi, 0) = LoaiThietBi.IOID
            INNER JOIN ONhomThietBi AS NhomThietBi ON IFNULL(ThietBi.Ref_NhomThietBi, 0) = NhomThietBi.IOID
            WHERE ThietBi.DeptID in (%1$s) 
                AND TongThe.Nam = %2$d
                AND IFormChinh.Status = 3 
                %3$s
                -- AND IFormChiTiet.Status = 2
            ORDER BY  KhuVuc.lft, LoaiBaoTri.lft, NhomThietBi.lft, LoaiThietBi.lft, KeHoach.LoaiKeHoach, ThietBi.TenThietBi
        ', $this->_user->user_dept_list, $year, $whereSql1);

        // echo '<pre>'; print_r($sql); die;

        return $this->_o_DB->fetchAll($sql);
    }


    public function getThietBiTrungDaiTuTheoLoai($MaLoaiThietBi) {
        $sql = sprintf('
            SELECT KeHoach.*
                , (IFNULL(KeHoach.TuLam, 0) + IFNULL(KeHoach.TrongCongTy, 0) + IFNULL(KeHoach.TrongTKV, 0) + IFNULL(KeHoach.NgoaiTKV, 0)) AS TongSo
            FROM OKeHoachBaoTri AS KeHoach
            INNER JOIN OKeHoachTongThe AS TongThe ON IFNULL(KeHoach.Ref_KeHoachTongThe, 0) = TongThe.IOID
            INNER JOIN ODanhSachThietBi AS ThietBi ON IFNULL(KeHoach.Ref_MaThietBi, 0) = ThietBi.IOID
            INNER JOIN OLoaiThietBi AS LoaiThietBi ON IFNULL(ThietBi.Ref_LoaiThietBi, 0) = LoaiThietBi.IOID
            INNER JOIN OLoaiThietBi AS LoaiThietBiRoot ON LoaiThietBiRoot.lft <= LoaiThietBi.lft 
                AND LoaiThietBiRoot.rgt >= LoaiThietBi.rgt
                AND IFNULL(LoaiThietBiRoot.Ref_TrucThuoc, 0) = 0
            WHERE (TRIM(LoaiThietBiRoot.MaLoai) = "%2$s" OR TRIM(LoaiThietBi.MaLoai) = "%2$s") 
                -- AND IFNULL(ThietBi.TrangThai, 0) = 0 
                AND ThietBi.DeptID in (%1$s)                  
                AND TRIM(ThietBi.NhomThietBi) = "Thiết bị"
                AND (TRIM(KeHoach.LoaiBaoTri) = "Trung tu" OR TRIM(KeHoach.LoaiBaoTri) = "Đại tu")
        ', $this->_user->user_dept_list, $MaLoaiThietBi);
        return $this->_o_DB->fetchAll($sql);
    }

    public function getThietBiKhacTrungDaiTu() {
        $sql = sprintf('
            SELECT KeHoach.*
            , (IFNULL(KeHoach.TuLam, 0) + IFNULL(KeHoach.TrongCongTy, 0) + IFNULL(KeHoach.TrongTKV, 0) + IFNULL(KeHoach.NgoaiTKV, 0)) AS TongSo
            FROM OKeHoachBaoTri AS KeHoach
            INNER JOIN OKeHoachTongThe AS TongThe ON IFNULL(KeHoach.Ref_KeHoachTongThe, 0) = TongThe.IOID
            INNER JOIN ODanhSachThietBi AS ThietBi ON IFNULL(KeHoach.Ref_MaThietBi, 0) = ThietBi.IOID
            INNER JOIN OLoaiThietBi AS LoaiThietBi ON IFNULL(ThietBi.Ref_LoaiThietBi, 0) = LoaiThietBi.IOID
            INNER JOIN OLoaiThietBi AS LoaiThietBiRoot ON LoaiThietBiRoot.lft <= LoaiThietBi.lft 
                AND LoaiThietBiRoot.rgt >= LoaiThietBi.rgt
                AND IFNULL(LoaiThietBiRoot.Ref_TrucThuoc, 0) = 0
            WHERE TRIM(LoaiThietBiRoot.MaLoai) NOT IN ("VTT", "VTB") 
                -- AND IFNULL(ThietBi.TrangThai, 0) = 0 
                AND ThietBi.DeptID in (%1$s)                  
                AND TRIM(ThietBi.NhomThietBi) = "Thiết bị"
                AND (TRIM(KeHoach.LoaiBaoTri) = "Trung tu" OR TRIM(KeHoach.LoaiBaoTri) = "Đại tu")
        ', $this->_user->user_dept_list);
        return $this->_o_DB->fetchAll($sql);
    }

    public function getVatKienTrucTrungDaiTu() {
        $sql = sprintf('
            SELECT KeHoach.*
            , (IFNULL(KeHoach.TuLam, 0) + IFNULL(KeHoach.TrongCongTy, 0) + IFNULL(KeHoach.TrongTKV, 0) + IFNULL(KeHoach.NgoaiTKV, 0)) AS TongSo
            FROM OKeHoachBaoTri AS KeHoach
            INNER JOIN OKeHoachTongThe AS TongThe ON IFNULL(KeHoach.Ref_KeHoachTongThe, 0) = TongThe.IOID
            INNER JOIN ODanhSachThietBi AS ThietBi ON IFNULL(KeHoach.Ref_MaThietBi, 0) = ThietBi.IOID
            INNER JOIN OLoaiThietBi AS LoaiThietBi ON IFNULL(ThietBi.Ref_LoaiThietBi, 0) = LoaiThietBi.IOID
            INNER JOIN OLoaiThietBi AS LoaiThietBiRoot ON LoaiThietBiRoot.lft <= LoaiThietBi.lft 
                AND LoaiThietBiRoot.rgt >= LoaiThietBi.rgt
                AND IFNULL(LoaiThietBiRoot.Ref_TrucThuoc, 0) = 0
            WHERE 
                ThietBi.DeptID in (%1$s)
                -- AND IFNULL(ThietBi.TrangThai, 0) = 0
                AND TRIM(ThietBi.NhomThietBi) = "Vật kiến trúc"
                AND (TRIM(KeHoach.LoaiBaoTri) = "Trung tu" OR TRIM(KeHoach.LoaiBaoTri) = "Đại tu")
        ', $this->_user->user_dept_list);
        return $this->_o_DB->fetchAll($sql);
    }

    public function getThietBiSuaChuaThuongXuyenTheoLoai($MaLoaiThietBi) {
        $sql = sprintf('
            SELECT KeHoach.*
            , (IFNULL(KeHoach.TuLam, 0) + IFNULL(KeHoach.TrongCongTy, 0) + IFNULL(KeHoach.TrongTKV, 0) + IFNULL(KeHoach.NgoaiTKV, 0)) AS TongSo
            FROM OKeHoachBaoTri AS KeHoach
            INNER JOIN OKeHoachTongThe AS TongThe ON IFNULL(KeHoach.Ref_KeHoachTongThe, 0) = TongThe.IOID
            INNER JOIN ODanhSachThietBi AS ThietBi ON IFNULL(KeHoach.Ref_MaThietBi, 0) = ThietBi.IOID
            INNER JOIN OLoaiThietBi AS LoaiThietBi ON IFNULL(ThietBi.Ref_LoaiThietBi, 0) = LoaiThietBi.IOID
            INNER JOIN OLoaiThietBi AS LoaiThietBiRoot ON LoaiThietBiRoot.lft <= LoaiThietBi.lft 
                AND LoaiThietBiRoot.rgt >= LoaiThietBi.rgt
                AND IFNULL(LoaiThietBiRoot.Ref_TrucThuoc, 0) = 0
            WHERE (TRIM(LoaiThietBiRoot.MaLoai) = "%2$s" OR TRIM(LoaiThietBi.MaLoai) = "%2$s") 
                -- AND IFNULL(ThietBi.TrangThai, 0) = 0 
                AND ThietBi.DeptID in (%1$s)                  
                AND TRIM(ThietBi.NhomThietBi) = "Thiết bị"
                AND TRIM(KeHoach.LoaiBaoTri)  = "Sửa chữa thường xuyên"
        ', $this->_user->user_dept_list, $MaLoaiThietBi);
        return $this->_o_DB->fetchAll($sql);
    }

    public function getThietBiKhacSuaChua() {
        $sql = sprintf('
            SELECT KeHoach.*
            , (IFNULL(KeHoach.TuLam, 0) + IFNULL(KeHoach.TrongCongTy, 0) + IFNULL(KeHoach.TrongTKV, 0) + IFNULL(KeHoach.NgoaiTKV, 0)) AS TongSo
            FROM OKeHoachBaoTri AS KeHoach
            INNER JOIN OKeHoachTongThe AS TongThe ON IFNULL(KeHoach.Ref_KeHoachTongThe, 0) = TongThe.IOID
            INNER JOIN ODanhSachThietBi AS ThietBi ON IFNULL(KeHoach.Ref_MaThietBi, 0) = ThietBi.IOID
            INNER JOIN OLoaiThietBi AS LoaiThietBi ON IFNULL(ThietBi.Ref_LoaiThietBi, 0) = LoaiThietBi.IOID
            INNER JOIN OLoaiThietBi AS LoaiThietBiRoot ON LoaiThietBiRoot.lft <= LoaiThietBi.lft 
                AND LoaiThietBiRoot.rgt >= LoaiThietBi.rgt
                AND IFNULL(LoaiThietBiRoot.Ref_TrucThuoc, 0) = 0
            WHERE TRIM(LoaiThietBiRoot.MaLoai) NOT IN ("VTT", "VTB") 
                -- AND IFNULL(ThietBi.TrangThai, 0) = 0 
                AND ThietBi.DeptID in (%1$s)                  
                AND TRIM(ThietBi.NhomThietBi) = "Thiết bị"
                AND TRIM(KeHoach.LoaiBaoTri)  = "Sửa chữa thường xuyên"
        ', $this->_user->user_dept_list);
        return $this->_o_DB->fetchAll($sql);
    }

    public function getVatKienTrucSuaChua() {
        $sql = sprintf('
            SELECT KeHoach.*
            , (IFNULL(KeHoach.TuLam, 0) + IFNULL(KeHoach.TrongCongTy, 0) + IFNULL(KeHoach.TrongTKV, 0) + IFNULL(KeHoach.NgoaiTKV, 0)) AS TongSo
            FROM OKeHoachBaoTri AS KeHoach
            INNER JOIN OKeHoachTongThe AS TongThe ON IFNULL(KeHoach.Ref_KeHoachTongThe, 0) = TongThe.IOID
            INNER JOIN ODanhSachThietBi AS ThietBi ON IFNULL(KeHoach.Ref_MaThietBi, 0) = ThietBi.IOID
            INNER JOIN OLoaiThietBi AS LoaiThietBi ON IFNULL(ThietBi.Ref_LoaiThietBi, 0) = LoaiThietBi.IOID
            INNER JOIN OLoaiThietBi AS LoaiThietBiRoot ON LoaiThietBiRoot.lft <= LoaiThietBi.lft 
                AND LoaiThietBiRoot.rgt >= LoaiThietBi.rgt
                AND IFNULL(LoaiThietBiRoot.Ref_TrucThuoc, 0) = 0
            WHERE 
                ThietBi.DeptID in (%1$s)
                -- AND IFNULL(ThietBi.TrangThai, 0) = 0
                AND TRIM(ThietBi.NhomThietBi) = "Vật kiến trúc"
                AND TRIM(KeHoach.LoaiBaoTri)  = "Sửa chữa thường xuyên"
        ', $this->_user->user_dept_list);
        return $this->_o_DB->fetchAll($sql);
    }

}