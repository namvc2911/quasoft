<?php
class Qss_Model_Extra_Manufacturing extends Qss_Model_Abstract
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getManufacturingByMonth($month, $year)
    {
        $mDep      = new Qss_Model_Admin_Department();
        $congty    = ('SELECT * FROM qsdepartments WHERE DeptCode = \'CTY\'');
        $datCongTy = $this->_o_DB->fetchOne($congty);
        $depts     = $mDep->getChildDepartmentIDs(@(int)$datCongTy->DepartmentID);
        //$depts[]   = @(int)$datCongTy->DepartmentID;
        
        $lastMonth     = @(int)$month;
        $yearOfLast    = @(int)$year;
        $monthDiffOne  = 0;
        
        if($month == 1)
        {
            $lastMonth  = 12;
            $yearOfLast = $yearOfLast - 1;
        }
        else
        {
            $lastMonth = $lastMonth - 1;
        }        
        
        $sql = sprintf('
            SELECT
                dept2.DepartmentID,
                dept2.DeptCode,
                dept2.NAME AS DeptName,
                ifnull(sanluong.ThanSach, 0) AS ThanSach,
                ifnull(sanluongthangtruoc.ThanSach, 0) AS ThanSachThangTruoc,            
                ifnull(kehoach1.SanLuongDonVi, 0) AS KeHoachSanLuongDonVi,
                ifnull(kehoach1.DienNangDonVi, 0) AS KeHoachDienNangDonVi
            FROM
                qsdepartments AS dept2
            
            /* San luong thang nay */
            LEFT JOIN (
                SELECT
                    qsdepartments.DepartmentID,
                    sum(IFNULL(ct.ThanSach, 0)) AS ThanSach
                FROM 
                    OHoatDongSanXuatHangThang AS thongtin
                INNER JOIN OChiTietSanLuongHangThang AS ct ON thongtin.IFID_M159 = ct.IFID_M159
                INNER JOIN qsdepartments ON qsdepartments.DepartmentID = ct.Ref_DonVi
                WHERE
                    thongtin.IFID_M159 IS NOT NULL
                    AND thongtin.Thang = %1$d
                    AND thongtin.Nam = %2$d     
                    AND qsdepartments.DepartmentID in (%3$s)
                GROUP BY
                    ct.Ref_DonVi        
            ) AS sanluong ON sanluong.DepartmentID = dept2.DepartmentID  
            
            /* San Luong thang truoc*/
            LEFT JOIN (
                SELECT
                    qsdepartments.DepartmentID,
                    sum(IFNULL(ct.ThanSach, 0)) AS ThanSach
                FROM 
                    OHoatDongSanXuatHangThang AS thongtin
                INNER JOIN OChiTietSanLuongHangThang AS ct ON thongtin.IFID_M159 = ct.IFID_M159
                INNER JOIN qsdepartments ON qsdepartments.DepartmentID = ct.Ref_DonVi
                WHERE
                    thongtin.IFID_M159 IS NOT NULL
                    AND thongtin.Thang = %4$d
                    AND thongtin.Nam = %5$d     
                    AND qsdepartments.DepartmentID in (%3$s)
                GROUP BY
                    ct.Ref_DonVi        
            ) AS sanluongthangtruoc ON sanluongthangtruoc.DepartmentID = dept2.DepartmentID 
            
            /* Ke hoach nam */
            LEFT JOIN
            (
                SELECT
                    dept1.DepartmentID
                    , ifnull(khoan.SanLuong, 0) AS SanLuongDonVi
                    , ifnull(khoan.DienNang, 0) AS DienNangDonVi
                FROM OKeHoachDienNangSanLuong AS kehoach
                INNER JOIN OKhoanDienNang AS khoan ON kehoach.IFID_M560 = khoan.IFID_M560
                INNER JOIN qsdepartments AS dept1 ON khoan.Ref_DonVi = dept1.DepartmentID
                WHERE kehoach.Nam = %2$d AND dept1.DepartmentID in (%3$s)
            ) AS kehoach1 ON kehoach1.DepartmentID = dept2.DepartmentID  
            WHERE dept2.DepartmentID in (%3$s)
            ORDER BY dept2.Level                     
        ', @(int)$month, @(int)$year, implode(', ', $depts), $lastMonth, $yearOfLast);
        //echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }


    /**
     * M187
     * @param $truocNgay
     */
    public function getLuyKeDienNangHangNgay($truocNgay)
    {
        $sql = sprintf('
            SELECT 
                DeptCode                  
                , SUM(
                    IF(depts.DeptCode = "TT1", IFNULL(ChiTiet.DienNang, 0), 0)
                )  AS Total_tt1 
                
                , SUM(
                    IF(depts.DeptCode = "TT2" , IFNULL(ChiTiet.DienNang, 0), 0)
                )  AS Total_tt2  
                
                , SUM(
                    IF(depts.DeptCode = "TT3" , IFNULL(ChiTiet.DienNang, 0), 0)
                )  AS Total_tt3      
                
                , SUM(
                    IF(depts.DeptCode = "PMT" , IFNULL(ChiTiet.DienNang, 0), 0)
                )  AS Total_letb
                
                , SUM(
                    IF(depts.DeptCode = "KB2" , IFNULL(ChiTiet.DienNang, 0), 0)
                )  AS Total_b2   
                                                
                , SUM(
                    IF(depts.DeptCode = "2A" , IFNULL(ChiTiet.DienNang, 0), 0)
                )  AS Total_l2a 
                , SUM(
                    IF(depts.DeptCode = "4B"  , IFNULL(ChiTiet.DienNang, 0), 0)
                )  AS Total_l4b        
                , SUM(
                    IF(depts.DeptCode = "6B" , IFNULL(ChiTiet.DienNang, 0), 0)
                )  AS Total_l6b                                           
                , SUM(
                    IF(depts.DeptCode = "CTY" , IFNULL(ChiTiet.DienNang, 0), 0)
                )  AS Total_cty    
            FROM OHoatDongSanXuat AS HangNgay
            INNER JOIN OChiTietHoatDongSanXuat AS ChiTiet ON HangNgay.IFID_M149 = ChiTiet.IFID_M149
            INNER JOIN qsdepartments AS depts ON ChiTiet.Ref_DonVi = depts.DepartmentID
            WHERE  DeptCode IN ("TT1", "TT2", "TT3", "PMT", "KB2", "2A", "4B", "6B", "CTY")
                AND HangNgay.Ngay < %1$s            
        ', $this->_o_DB->quote($truocNgay));
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchOne($sql);
    }

    /**
     * Báo cáo M187
     * @param $startMonth
     * @param $endMonth
     * @param $year
     */
    public function getTongDienNangHangNgay($start, $end)
    {
        $sql = sprintf('
            SELECT 
                DAYOFMONTH(HangNgay.Ngay) AS Ngay                  
                , SUM(
                    IF(depts.DeptCode = "TT1" AND (HangNgay.Ngay BETWEEN %1$s AND %2$s), IFNULL(ChiTiet.DienNang, 0), 0)
                )  AS Quantity_tt1 
                
                , SUM(
                    IF(depts.DeptCode = "TT2" AND (HangNgay.Ngay BETWEEN %1$s AND %2$s), IFNULL(ChiTiet.DienNang, 0), 0)
                )  AS Quantity_tt2  
                
                , SUM(
                    IF(depts.DeptCode = "TT3" AND (HangNgay.Ngay BETWEEN %1$s AND %2$s), IFNULL(ChiTiet.DienNang, 0), 0)
                )  AS Quantity_tt3      
                
                , SUM(
                    IF(depts.DeptCode = "PMT" AND (HangNgay.Ngay BETWEEN %1$s AND %2$s), IFNULL(ChiTiet.DienNang, 0), 0)
                )  AS Quantity_letb   
                
                , SUM(
                    IF(depts.DeptCode = "KB2" AND (HangNgay.Ngay BETWEEN %1$s AND %2$s), IFNULL(ChiTiet.DienNang, 0), 0)
                )  AS Quantity_b2   
                                                
                , SUM(
                    IF(depts.DeptCode = "2A" AND (HangNgay.Ngay BETWEEN %1$s AND %2$s), IFNULL(ChiTiet.DienNang, 0), 0)
                )  AS Quantity_l2a 
                , SUM(
                    IF(depts.DeptCode = "4B" AND (HangNgay.Ngay BETWEEN %1$s AND %2$s), IFNULL(ChiTiet.DienNang, 0), 0)
                )  AS Quantity_l4b        
                , SUM(
                    IF(depts.DeptCode = "6B" AND (HangNgay.Ngay BETWEEN %1$s AND %2$s), IFNULL(ChiTiet.DienNang, 0), 0)
                )  AS Quantity_l6b                                           
                , SUM(
                    IF(depts.DeptCode = "CTY" AND (HangNgay.Ngay BETWEEN %1$s AND %2$s), IFNULL(ChiTiet.DienNang, 0), 0)
                )  AS Quantity_cty    
            FROM OHoatDongSanXuat AS HangNgay
            INNER JOIN OChiTietHoatDongSanXuat AS ChiTiet ON HangNgay.IFID_M149 = ChiTiet.IFID_M149
            INNER JOIN qsdepartments AS depts ON ChiTiet.Ref_DonVi = depts.DepartmentID
            WHERE  DeptCode IN ("TT1", "TT2", "TT3", "PMT", "KB2", "2A", "4B", "6B", "CTY")
            GROUP BY DAYOFMONTH(HangNgay.Ngay)
            ORDER BY DAYOFMONTH(HangNgay.Ngay)
        ', $this->_o_DB->quote($start), $this->_o_DB->quote($end));
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }
}