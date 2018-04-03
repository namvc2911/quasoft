<?php
class Qss_Model_Extra_News extends Qss_Model_Abstract
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * KE HOACH SUA CHUA TRONG NGAY
     */
    public function getPlanTasks($date)
    {
        $sql = sprintf('
            SELECT thongtin.*, qsiforms.Status, qsdepartments.Name AS DeptName
            FROM OKeHoachSuaChuaTheoNgay AS thongtin
            INNER JOIN qsiforms ON thongtin.IFID_M148 = qsiforms.IFID
            INNER JOIN qsdepartments ON qsdepartments.DepartmentID = thongtin.DeptID
            WHERE
                thongtin.IFID_M148 is not null
                AND thongtin.DeptID in (%1$s)
                AND thongtin.Ngay = %2$s
            ORDER BY qsdepartments.ParentID
        ', $this->_user->user_dept_list, $this->_o_DB->quote($date));
        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * Lay bao cao san xuat cua cong ty (tin tuc khong phai la lich lam viec)
     * Hien thi 100 tin tuc moi nhat
     */
    public function getManufacturingReport($date)
    {
        $sql = sprintf('
            SELECT
                ct.*
                , qsdepartments.Name AS DeptName
                , qsdepartments.DeptCode
        	    ,(select sum(ThanSach) from OChiTietHoatDongSanXuat
        	        inner join OHoatDongSanXuat on OHoatDongSanXuat.IFID_M149 = OChiTietHoatDongSanXuat.IFID_M149
        	        where month(Ngay) = month(%1$s) and year(Ngay) = year(%1$s) and day(Ngay) <= day(%1$s) and OChiTietHoatDongSanXuat.Ref_DonVi = ct.Ref_DonVi
                ) as LuyKeThanSach
        	    ,(select sum(ThanTieuThu) from OChiTietHoatDongSanXuat
                    inner join OHoatDongSanXuat on OHoatDongSanXuat.IFID_M149 = OChiTietHoatDongSanXuat.IFID_M149
        	        where month(Ngay) = month(%1$s) and year(Ngay) = year(%1$s) and day(Ngay) <= day(%1$s) and OChiTietHoatDongSanXuat.Ref_DonVi = ct.Ref_DonVi
                ) as LuyKeThanTieuThu
        	    ,(select sum(DienNang) from OChiTietHoatDongSanXuat
        	        inner join OHoatDongSanXuat on OHoatDongSanXuat.IFID_M149 = OChiTietHoatDongSanXuat.IFID_M149
        	        where month(Ngay) = month(%1$s) and year(Ngay) = year(%1$s) and day(Ngay) <= day(%1$s) and OChiTietHoatDongSanXuat.Ref_DonVi = ct.Ref_DonVi
                ) as LuyKeDienNang
        	    ,(select SanLuong from OKhoanDienNang
        	        inner join OKeHoachDienNangSanLuong on OKeHoachDienNangSanLuong.IFID_M560 = OKhoanDienNang.IFID_M560
                  where Nam = year(%1$s) and OKhoanDienNang.Ref_DonVi = ct.Ref_DonVi limit 1
                ) as SanLuongKeHoach
        	    ,(select DienNang from OKhoanDienNang
        	        inner join OKeHoachDienNangSanLuong on OKeHoachDienNangSanLuong.IFID_M560 = OKhoanDienNang.IFID_M560
        	        where Nam = year(%1$s) and OKhoanDienNang.Ref_DonVi = ct.Ref_DonVi limit 1
                ) as DienNangKeHoach
        	    ,(select STH from OKhoanDienNang
        	        inner join OKeHoachDienNangSanLuong on OKeHoachDienNangSanLuong.IFID_M560 = OKhoanDienNang.IFID_M560
                    where Nam = year(%1$s) and OKhoanDienNang.Ref_DonVi = ct.Ref_DonVi limit 1
                ) as STHKeHoach
            FROM OHoatDongSanXuat AS thongtin
            INNER JOIN OChiTietHoatDongSanXuat AS ct ON thongtin.IFID_M149 = ct.IFID_M149
            INNER JOIN qsdepartments ON qsdepartments.DepartmentID = ct.Ref_DonVi
            WHERE
                thongtin.IFID_M149 is not null
                AND thongtin.Ngay = %1$s
            ORDER BY qsdepartments.Level
            LIMIT 1000
        ', $this->_o_DB->quote($date));
        return $this->_o_DB->fetchAll($sql);
    }

    public function getSanLuongCuaTt1Tt2Tt3($date)
    {
        $sql = sprintf('
                        SELECT sum(ifnull(OChiTietHoatDongSanXuat.ThanSach, 0)) AS TongThanSach
                        FROM OChiTietHoatDongSanXuat
                        INNER JOIN OHoatDongSanXuat ON OChiTietHoatDongSanXuat.IFID_M149 = OHoatDongSanXuat.IFID_M149
                        INNER JOIN qsdepartments ON OChiTietHoatDongSanXuat.Ref_DonVi = qsdepartments.DepartmentID
                        WHERE
                          OHoatDongSanXuat.Ngay = %1$s
                          AND qsdepartments.DeptCode in (\'TT1\', \'TT2\', \'TT3\')
                    ', $this->_o_DB->quote($date));
        return $this->_o_DB->fetchOne($sql);
    }

    public function getRejectPlanTasks($date)
    {
        $sql = sprintf('
            SELECT thongtin.*, qsiforms.Status, qsdepartments.Name AS DeptName
            FROM OKeHoachSuaChuaTheoNgay AS thongtin
            INNER JOIN qsiforms ON thongtin.IFID_M148 = qsiforms.IFID
            INNER JOIN qsdepartments ON qsdepartments.DepartmentID = thongtin.DeptID
            WHERE
                thongtin.IFID_M148 is not null
                AND thongtin.DeptID in (%1$s)
                AND thongtin.Ngay = %2$s
                AND qsiforms.Status in (1, 3)
            ORDER BY qsdepartments.ParentID
        ', $this->_user->user_dept_list, $this->_o_DB->quote($date));
        return $this->_o_DB->fetchAll($sql);
    }

    public function getPolicy()
    {
        $sql = sprintf('
            SELECT thongtin.*, dep2.Name AS DeptName
            FROM OQuyDinh AS thongtin
            INNER JOIN qsdepartments AS dep2 ON dep2.DepartmentID = thongtin.DeptID
            INNER JOIN ODonViApDungQuyDinh AS apdung ON thongtin.IFID_M147 = apdung.IFID_M147
            INNER JOIN qsdepartments ON qsdepartments.DepartmentID = thongtin.DeptID
            WHERE
                thongtin.IFID_M147 is not null
                AND apdung.Ref_DonVi in (%1$s)
            GROUP BY thongtin.IFID_M147
            ORDER BY qsdepartments.ParentID, thongtin.ThoiGianHieuLuc DESC, thongtin.Ngay DESC, thongtin.IOID DESC
        ', $this->_user->user_dept_list);
        return $this->_o_DB->fetchAll($sql);
    }

    public function getDepartmentByPolicyIFID($ifid)
    {
        $sql = sprintf('
            SELECT donvi.*, qsdepartments.Name AS DeptName, qsdepartments.DepartmentID
            FROM qsdepartments
            LEFT JOIN ODonViApDungQuyDinh AS donvi ON qsdepartments.DepartmentID = donvi.Ref_DonVi
              AND donvi.IFID_M147 = %1$d
        ', $ifid);
        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * Hien thi 100 kien thuc moi nhat
     * @return mixed
     */
    public function getKnowledge()
    {
        $sql = sprintf('
            SELECT thongtin.*, qsdepartments.Name AS DeptName
            FROM OQuanLyKienThuc AS thongtin
            INNER JOIN qsdepartments ON qsdepartments.DepartmentID = thongtin.DeptID
            WHERE
                thongtin.IFID_M152 is not null
                AND thongtin.DeptID in (%1$s)
            ORDER BY thongtin.IOID DESC, thongtin.Ref_NhomKienThuc DESC
            LIMIT 100
        ', $this->_user->user_dept_list);
        return $this->_o_DB->fetchAll($sql);
    }
/**
     * Lay bao cao san xuat cua cong ty (tin tuc khong phai la lich lam viec)
     * Hien thi 100 tin tuc moi nhat
     */
    public function getWaterReport($date)
    {
        $sql = sprintf('SELECT Day(Ngay) as Ngay,
        	(ifnull(G31,0)+ifnull(G33,0)*0.4) as TuanHoanTT1,
        	(ifnull(G32,0)+ifnull(G33,0)*0.6) as TuanHoanTT2,
        	(ifnull(CongNghe1,0)+ifnull(BaRa1,0)+ifnull(CongNghe3,0)*0.4) as BoSungTT1,
        	(ifnull(CongNghe2,0)+ifnull(BaRa2,0)+ifnull(CongNghe3,0)*0.6) as BoSungTT2,
        	(ifnull(G31,0)+ifnull(G33,0)*0.4) + (ifnull(CongNghe1,0)+ifnull(BaRa1,0)+ifnull(CongNghe3,0)*0.4) as TT1,
        	(ifnull(G32,0)+ifnull(G33,0)*0.6) + (ifnull(CongNghe2,0)+ifnull(BaRa2,0)+ifnull(CongNghe3,0)*0.6) as TT2
        	FROM OChiSoNuocCongNghiep
            WHERE
             month(Ngay) = month(%1$s)
             order by Day(Ngay) 
        ',$this->_o_DB->quote($date));
        //echo $sql;die;
        //(ifnull(G31,0)+ifnull(CongNghe4,0)) as TuanHoanTT1,
        //(ifnull(G32,0)+ifnull(G33,0)-ifnull(CongNghe4,0)) as TuanHoanTT2,
        return $this->_o_DB->fetchAll($sql);
    }
    public function getSanLuongTheoTuyen($date,$tuyen)
    {
        $sql = sprintf('
                        SELECT sum(ifnull(OChiTietHoatDongSanXuat.ThanSach, 0)) AS TongThanSach
                        , day(OHoatDongSanXuat.Ngay) as Ngay
                        FROM OChiTietHoatDongSanXuat
                        INNER JOIN OHoatDongSanXuat ON OChiTietHoatDongSanXuat.IFID_M149 = OHoatDongSanXuat.IFID_M149
                        INNER JOIN qsdepartments ON OChiTietHoatDongSanXuat.Ref_DonVi = qsdepartments.DepartmentID
                        WHERE
                          month(OHoatDongSanXuat.Ngay) = month(%1$s)
                          AND qsdepartments.DeptCode = %2$s
                          group by day(OHoatDongSanXuat.Ngay)
                    ', $this->_o_DB->quote($date)
        			,$this->_o_DB->quote($tuyen));
        return $this->_o_DB->fetchAll($sql);
    }

    public function getElectricReportByDate($start, $end)
    {
        $sql = sprintf('
            SELECT TheoDoi.*, qsdepartments.DeptCode, OCa.MaCa
            FROM OTheoDoiDienNang AS TheoDoi
            INNER JOIN qsdepartments ON TheoDoi.Ref_DonVi = qsdepartments.DepartmentID
            LEFT JOIN OCa ON TheoDoi.Ref_Ca = OCa.IOID
            WHERE Ngay BETWEEN %1$s AND %2$s
        ', $this->_o_DB->quote($start), $this->_o_DB->quote($end));
        return $this->_o_DB->fetchAll($sql);
    }
    
}