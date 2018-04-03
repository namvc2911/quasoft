<?php
class Qss_Model_Maintenance_Electric extends Qss_Model_Abstract
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Lấy chỉ số điện năng mua vào sắp xếp theo đối tượng cung cấp, đơn vị cung cấp, vi trí công tơ,  theo tháng
     * Get purchase meter readings order by supplier type, supplier ID, meter position
     * @param int $month Lấy chỉ số theo tháng/ Filter by month
     * @param int $year Lấy chỉ số theo năm/ Filter by year
     * @return object Điện năng tiêu thụ theo tháng
     */
    public function getChiSoDienNangMuaVao($month, $year)
    {
        $sql = sprintf('
            SELECT
                ctmua.DoiTuongCungCap AS DoiTuong
                , ctmua.ViTri
                , ifnull(doitac.HeSoTonHao, 1) AS HeSoTonHao
                , ifnull(ctmua.HeSoCongTo, 1) AS HeSo
                , ifnull(chiso.Ky, 1) AS Ky
                , ctmua.MaCongTo
                , ctmua.TenCongTo
                , ctmua.Loai AS LoaiGia
                , ctmua.DonViCungCap
                , ctmua.Ref_DonViCungCap
                , ctmua.DonViSuDung
                , ctmua.Ref_DonViSuDung AS Ref_DonViMua
                , ctmua.Ref_DonViSuDung
                , ifnull(chiso.ChiSoDau, 0) AS ChiSoDau
                , ifnull(chiso.ChiSoCuoi, 0) AS ChiSoCuoi
                , ROUND(ifnull(chiso.TongSo, 0)) AS TongSo
                , ifnull(chiso.ChiSoDauThapDiem, 0) AS ChiSoDauThapDiem
                , ifnull(chiso.ChiSoCuoiThapDiem, 0) AS ChiSoCuoiThapDiem
                , ROUND(ifnull(chiso.TongSoThapDiem, 0)) AS TongSoThapDiem
                , ifnull(chiso.ChiSoDauTrungBinh, 0) AS ChiSoDauTrungBinh 
                , ifnull(chiso.ChiSoCuoiTrungBinh, 0) AS ChiSoCuoiTrungBinh
                , ROUND(ifnull(chiso.TongSoTrungBinh, 0)) AS TongSoTrungBinh
                , ifnull(chiso.ChiSoDauCaoDiem, 0) AS ChiSoDauCaoDiem
                , ifnull(chiso.ChiSoCuoiCaoDiem, 0) AS ChiSoCuoiCaoDiem
                , ROUND(ifnull(chiso.TongSoCaoDiem, 0)) AS TongSoCaoDiem
                , ctmua.IOID AS CongToIOID
                , dept.DeptCode AS CodeDonViNoiBo
                , dept.Name AS NameDonViNoiBo
                , dept.Level AS LevelDonViNoiBo
                , round((ifnull(chiso.TongSo, 0) * ifnull(doitac.HeSoTonHao, 1))) AS TongSoCoTonHao
                , round((ifnull(chiso.TongSoCaoDiem, 0) * ifnull(doitac.HeSoTonHao, 1))) AS TongSoCaoDiemCoTonHao
                , round((ifnull(chiso.TongSoThapDiem, 0) * ifnull(doitac.HeSoTonHao, 1))) AS TongSoThapDiemCoTonHao
                , round((ifnull(chiso.TongSoTrungBinh, 0) * ifnull(doitac.HeSoTonHao, 1))) AS TongSoTrungBinhCoTonHao
                , IFNULL(chiso.TienPhatCosPi,0) AS TongTienPhatCosPi           
            FROM  OChiSoCongToMuaVao AS chiso
            LEFT JOIN OQuanLyCongToMuaVao AS ctmua ON ctmua.IOID = chiso.Ref_MaCongTo
            LEFT JOIN ODoiTac AS doitac ON ctmua.Ref_DonViCungCap = doitac.IOID
            LEFT JOIN qsdepartments as dept ON ctmua.Ref_DonViSuDung = dept.DepartmentID
            WHERE chiso.Thang = %1$d AND chiso.Nam = %2$d
            ORDER BY
              FIELD(ifnull(ctmua.DoiTuongCungCap, 0), 1, 2, 0, NULL)
              , ctmua.Loai DESC
              , CASE WHEN ctmua.ViTri like convert(cast(convert(\'Trạm 35\' using  latin1) as binary) using utf8) THEN 0
              WHEN ctmua.ViTri like convert(cast(convert(\'Trạm bơm\' using  latin1) as binary) using utf8) THEN 0
              ELSE ctmua.ViTri END
              , ctmua.MaCongTo
              , FIELD(ifnull(chiso.Ky, 0), 0, 1, 2, 3)
              , ctmua.DonViCungCap
        ', $month, $year);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * Lấy tổng chỉ số điện năng mua vào nhóm theo công tơ và sắp xếp theo đối tượng cung cấp, đơn vị cung cấp theo tháng
     * Get total purchase meter readings group by meter order by supplier type and supplier ID
     * @param int $month Lấy chỉ số theo tháng/ Filter by month
     * @param int $year Lấy chỉ số theo năm/ Filter by year
     * @return object Tổng điện năng tiêu thụ theo tháng
     */
    public function getTongDienNangMuaVao($month, $year)
    {
        $sql = sprintf('
            SELECT
                ctmua.*
                , ifnull(doitac.HeSoTonHao, 1) AS HeSoTonHao
                , ctmua.IOID AS CongToIOID
                , ctmua.Loai AS LoaiGia
                , ctmua.DoiTuongCungCap AS DoiTuong
                , ctmua.DonViCungCap
                , ctmua.Ref_DonViSuDung AS Ref_DonViMua
                , ctmua.DonViSuDung AS DonViMua
                , sum(round( (ifnull(chiso.TongSo, 0) * ifnull(doitac.HeSoTonHao, 1)) )) AS TongSoDienChung
                , sum(round( (ifnull(chiso.TongSoThapDiem, 0) * ifnull(doitac.HeSoTonHao, 1)) )) AS TongSoDienThapDiem
                , sum(round( (ifnull(chiso.TongSoTrungBinh, 0) * ifnull(doitac.HeSoTonHao, 1)) )) AS TongSoDienTrungBinh
                , sum(round( (ifnull(chiso.TongSoCaoDiem, 0) * ifnull(doitac.HeSoTonHao, 1)) )) AS TongSoDienCaoDiem
                , sum(IFNULL(chiso.TienPhatCosPi,0) ) AS TongTienPhatCosPi
            FROM OQuanLyCongToMuaVao AS ctmua
            LEFT JOIN OChiSoCongToMuaVao AS chiso ON ctmua.IOID = chiso.Ref_MaCongTo
            LEFT JOIN ODoiTac AS doitac ON ctmua.Ref_DonViCungCap = doitac.IOID
            WHERE chiso.Thang = %1$d AND chiso.Nam = %2$d
            GROUP BY ctmua.IOID
            ORDER BY  FIELD(ifnull(ctmua.DoiTuongCungCap, 0), 1, 2, 0, NULL), ctmua.DonViCungCap, ctmua.MaCongTo
        ', $month, $year);
        
        // echo '<pre>'; print_r($sql); die;
        
        return $this->_o_DB->fetchAll($sql);
    }


    public function getChiSoDienNangBanRa($month, $year)
    {
        $sql = sprintf('
            SELECT
                ctmua.DoiTuongMua AS DoiTuong
                , ctmua.ViTri
                , ifnull(doitac.HeSoTonHao, 1) AS HeSoTonHao
                , ifnull(ctmua.HeSo, 1) AS HeSoCongTo
                , ifnull(ctmua.HeSo, 1) AS HeSo
                , ifnull(chiso.Ky, 1) AS Ky
                , ctmua.Ma AS MaCongTo
                , ctmua.Ten AS TenCongTo
                , ctmua.Loai AS LoaiGia
                , ctmua.DonViBan
                , ctmua.Ref_DonViBan
                , ctmua.DonViMuaNgoai
                , ctmua.DonViMuaNgoai AS DonViCungCap
                , ctmua.Ref_DonViMuaNgoai AS Ref_DonViCungCapp
                , ctmua.Ref_DonViMuaNgoai
                , ifnull(chiso.ChiSoDau, 0) AS ChiSoDau
                , ifnull(chiso.ChiSoCuoi, 0) AS ChiSoCuoi
                , ROUND(ifnull(chiso.TongSo, 0)) AS TongSo
                , ifnull(chiso.ChiSoDauThapDiem, 0) AS ChiSoDauThapDiem
                , ifnull(chiso.ChiSoCuoiThapDiem, 0) AS ChiSoCuoiThapDiem
                , ROUND(ifnull(chiso.TongSoThapDiem, 0)) AS TongSoThapDiem
                , ifnull(chiso.ChiSoDauTrungBinh, 0) AS ChiSoDauTrungBinh
                , ifnull(chiso.ChiSoCuoiTrungBinh, 0) AS ChiSoCuoiTrungBinh
                , ROUND(ifnull(chiso.TongSoTrungBinh, 0)) AS TongSoTrungBinh
                , ifnull(chiso.ChiSoDauCaoDiem, 0) AS ChiSoDauCaoDiem
                , ifnull(chiso.ChiSoCuoiCaoDiem, 0) AS ChiSoCuoiCaoDiem
                , ROUND(ifnull(chiso.TongSoCaoDiem, 0)) AS TongSoCaoDiem
                , ctmua.IOID AS CongToIOID
                , doitac.HeSoTonHao
                , dept.DeptCode AS CodeDonViNoiBo
                , dept.Name AS NameDonViNoiBo
                , dept.Level AS LevelDonViNoiBo
                , round( (ifnull(chiso.TongSo, 0) * ifnull(doitac.HeSoTonHao, 1)) ) AS TongSoCoTonHao
                , round( (ifnull(chiso.TongSoCaoDiem, 0) * ifnull(doitac.HeSoTonHao, 1)) ) AS TongSoCaoDiemCoTonHao
                , round( (ifnull(chiso.TongSoThapDiem, 0) * ifnull(doitac.HeSoTonHao, 1)) ) AS TongSoThapDiemCoTonHao
                , round( (ifnull(chiso.TongSoTrungBinh, 0) * ifnull(doitac.HeSoTonHao, 1)) ) AS TongSoTrungBinhCoTonHao
                , IFNULL(chiso.TienPhatCosPi,0) AS TongTienPhatCosPi                
            FROM  OChiSoCongToDien AS chiso
            INNER JOIN OCongToDien AS ctmua ON ctmua.IOID = chiso.Ref_MaCongTo 
            LEFT JOIN ODoiTac AS doitac ON ctmua.Ref_DonViMuaNgoai = doitac.IOID
            LEFT JOIN qsdepartments as dept ON ctmua.Ref_DonViBan = dept.DepartmentID
            WHERE ctmua.DoiTuongMua != 3 AND chiso.Thang = %1$d AND chiso.Nam = %2$d
            ORDER BY
              FIELD(ifnull(ctmua.DoiTuongMua, 0), 1, 2, 0, NULL)
              , ctmua.Loai DESC
              , ctmua.Ma
              , FIELD(ifnull(chiso.Ky, 0), 0, 1, 2, 3)
              , ctmua.DonViMuaNgoai
        ', $month, $year);
        //echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getChiSoDienNangBanRaNoiBo($month, $year)
    {
        $sql = sprintf('
            SELECT
                ctmua.DoiTuongMua AS DoiTuong
                , ctmua.ViTri
                , ifnull(doitac.HeSoTonHao, 1) AS HeSoTonHao
                , ifnull(ctmua.HeSo, 1) AS HeSo
                , ctmua.Ma AS MaCongTo
                , ctmua.Ten AS TenCongTo
                , ctmua.Loai AS LoaiGia
                , ctmua.DonViMuaNgoai AS DonViCungCap
                , ctmua.Ref_DonViMuaNgoai AS Ref_DonViCungCapp
                , ctmua.DonViMuaNgoai
                , ifnull(ctmua.Ref_DonViMuaNgoai, 0) AS Ref_DonViMuaNgoai
                , ifnull(chiso.ChiSoDau, 0) AS ChiSoDau
                , ifnull(chiso.ChiSoCuoi, 0) AS ChiSoCuoi
                , ROUND(ifnull(chiso.TongSo, 0),2) AS TongSo
                , ifnull(chiso.HeSo, 0) AS HeSo
                , ctmua.IOID AS CongToIOID
                , chiso.HeSo
                , ctmua.Ref_DonViMua
                , ctmua.Ref_DonViBan
                , dept1.DeptCode AS CodeDonViMuaNoiBo
                , dept2.DeptCode AS CodeDonViBan
                , dept2.Level AS LevelDonViBan
                , dept1.Level AS LevelDonViMuaNoiBo
                , dept1.Name AS NameDonViMuaNoiBo
                , dept2.Name AS NameDonViBan
            FROM OChiSoCongToDienNoiBo AS chiso
            INNER JOIN OCongToDien AS ctmua ON ctmua.IOID = chiso.Ref_MaCongTo
            LEFT JOIN ODoiTac AS doitac ON ctmua.Ref_DonViMuaNgoai = doitac.IOID
            LEFT JOIN qsdepartments AS dept1 ON dept1.DepartmentID = ctmua.Ref_DonViMua
            LEFT JOIN qsdepartments AS dept2 ON dept2.DepartmentID = ctmua.Ref_DonViBan
            WHERE ctmua.DoiTuongMua = 3 AND chiso.Thang = %1$d AND chiso.Nam = %2$d AND ifnull(ctmua.Ref_DonViMuaNgoai,0) = 0
            ORDER BY ifnull(dept1.Level, 0), ctmua.IOID
        ', $month, $year);
        // echo '<pre>'.$sql; die;
        return $this->_o_DB->fetchAll($sql);
    }
    
    public function getChiSoDienNangBanRaNoiBoCoMuaNgoai($month, $year)
    {
        $sql = sprintf('
            SELECT
                ctmua.DoiTuongMua AS DoiTuong
                , ctmua.ViTri
                , ifnull(doitac.HeSoTonHao, 1) AS HeSoTonHao
                , ifnull(ctmua.HeSo, 1) AS HeSo
                , ctmua.Ma AS MaCongTo
                , ctmua.Ten AS TenCongTo
                , ctmua.Loai AS LoaiGia
                , ctmua.DonViMuaNgoai
                , ifnull(ctmua.Ref_DonViMuaNgoai, 0) AS Ref_DonViMuaNgoai
                , ifnull(chiso.ChiSoDau, 0) AS ChiSoDau
                , ifnull(chiso.ChiSoCuoi, 0) AS ChiSoCuoi
                , ROUND(ifnull(chiso.TongSo, 0),2) AS TongSo
                , ifnull(chiso.HeSo, 0) AS HeSo
                , ctmua.IOID AS CongToIOID
                , chiso.HeSo
                , ctmua.Ref_DonViMua
                , ctmua.Ref_DonViBan
                , dept1.DeptCode AS CodeDonViMuaNoiBo
                , dept2.DeptCode AS CodeDonViBan
                , dept1.Level AS LevelDonViMuaNoiBo
                , dept2.Level AS LevelDonViBan
                , dept1.Name AS NameDonViMuaNoiBo
                , dept2.Name AS NameDonViBan
            FROM OChiSoCongToDienNoiBo AS chiso
            INNER JOIN OCongToDien AS ctmua ON ctmua.IOID = chiso.Ref_MaCongTo
            LEFT JOIN ODoiTac AS doitac ON ctmua.Ref_DonViMuaNgoai = doitac.IOID
            LEFT JOIN qsdepartments AS dept1 ON dept1.DepartmentID = ctmua.Ref_DonViMua
            LEFT JOIN qsdepartments AS dept2 ON dept2.DepartmentID = ctmua.Ref_DonViBan
            WHERE ctmua.DoiTuongMua = 3 AND chiso.Thang = %1$d AND chiso.Nam = %2$d AND ifnull(ctmua.Ref_DonViMuaNgoai,0) != 0
            ORDER BY ctmua.Ref_DonViBan, ctmua.Ref_DonViMua, ctmua.IOID
        ', $month, $year);
        // echo '<pre>'.$sql; die;
        return $this->_o_DB->fetchAll($sql);
    }    

    public function getDienNangBanRa($month, $year)
    {
        $sql = sprintf('
            SELECT
                ctban.*
                , ifnull(doitac.HeSoTonHao, 1) AS HeSoTonHao
                , ctban.IOID AS CongToIOID
                , ctban.Loai AS LoaiGia
                , ctban.DonViBan AS DonViBan
                , if(ifnull(ctban.DonViMuaNgoai, \'\') = \'\', ctban.DonViMua, ctban.DonViMuaNgoai) AS DonViMuaChinh
                , ctban.Ref_DonViBan AS RefDonViBan
                , ctban.DonViMua AS DonViMuaNoiBo
                , ctban.Ref_DonViMua AS RefDonViMuaNoiBo
                , ctban.DoiTuongMua AS DoiTuong
                , sum(round( (ifnull(chiso.TongSo, 0) * ifnull(doitac.HeSoTonHao, 1)) )) AS TongSoDienChung
                , sum(round( (ifnull(chiso.TongSoThapDiem, 0) * ifnull(doitac.HeSoTonHao, 1)) )) AS TongSoDienThapDiem
                , sum(round( (ifnull(chiso.TongSoTrungBinh, 0) * ifnull(doitac.HeSoTonHao, 1)) )) AS TongSoDienTrungBinh
                , sum(round( (ifnull(chiso.TongSoCaoDiem, 0) * ifnull(doitac.HeSoTonHao, 1)) )) AS TongSoDienCaoDiem
                , sum(IFNULL(chiso.TienPhatCosPi,0)) AS TongTienPhatCosPi
                , doitac.HeSoTonHao
            FROM OCongToDien AS ctban
            LEFT JOIN OChiSoCongToDien AS chiso ON ctban.IOID = chiso.Ref_MaCongTo
            LEFT JOIN ODoiTac AS doitac ON ctban.Ref_DonViMuaNgoai = doitac.IOID
            WHERE ifnull(ctban.DoiTuongMua, 0) !=3 /* Chỉ lấy công tơ bán ra ngoài/ Only get meters sold out */
                AND chiso.Thang = %1$d
                AND chiso.Nam = %2$d
            GROUP BY ctban.IOID
            ORDER BY
                FIELD(ifnull(ctban.DoiTuongMua, 0), 1, 3,  2, 0, NULL)
                , if(ifnull(ctban.DonViMuaNgoai, \'\') = \'\', ctban.DonViMua, ctban.DonViMuaNgoai)
                , ctban.Ma
        ', $month, $year);
        


        return $this->_o_DB->fetchAll($sql);
    }
    
    public function getDienNangBanRaDotXuat($month, $year)
    {
        $sql = sprintf('
            SELECT 
                dotxuat.*, qsdepartments.DeptCode, qsdepartments.Name AS DeptName, qsdepartments.Level AS DeptLevel
            FROM OBanDienDotXuat AS dotxuat
            LEFT JOIN qsdepartments ON dotxuat.Ref_DonViBan = qsdepartments.DepartmentID
            WHERE dotxuat.Thang = %1$d AND dotxuat.Nam = %2$d
            ORDER BY dotxuat.DonViMua      
        ', $month, $year);
    
    
    
        return $this->_o_DB->fetchAll($sql);
    }    

    public function getDienNangBanRaNoiBo($month, $year)
    {
        $sql = sprintf('
            SELECT
                ctban.*
                , ctban.IOID AS CongToIOID
                , ctban.Loai AS LoaiGia
                , ctban.DonViBan AS DonViBan
                , if(ifnull(ctban.DonViMuaNgoai, \'\') = \'\', ctban.DonViMua, ctban.DonViMuaNgoai) AS DonViMuaChinh
                , ctban.Ref_DonViBan AS RefDonViBan
                , ctban.DonViMua AS DonViMuaNoiBo
                , ctban.Ref_DonViMua AS RefDonViMuaNoiBo
                , ctban.DoiTuongMua AS DoiTuong
                , sum(ROUND(ifnull(chiso.TongSo, 0), 2)) AS TongSoDienChung
                /*, sum(ifnull(chiso.TongSoThapDiem, 0)) AS TongSoDienThapDiem*/
                /*, sum(ifnull(chiso.TongSoTrungBinh, 0)) AS TongSoDienTrungBinh*/
                /*, sum(ifnull(chiso.TongSoCaoDiem, 0)) AS TongSoDienCaoDiem*/
                , sum(IFNULL(chiso.TienPhatCosPi,0)) AS TongTienPhatCosPi
            FROM OCongToDien AS ctban /* Lấy toàn bộ công tơ bán */
            LEFT JOIN OChiSoCongToDienNoiBo AS chiso ON ctban.IOID = chiso.Ref_MaCongTo
            WHERE ifnull(ctban.DoiTuongMua, 0) = 3 /* Chỉ lấy công tơ bán nội bộ/ Only get meters sold internally */
                AND ifnull(ctban.Ref_DonViMuaNgoai,0) = 0
                AND chiso.Thang = %1$d
                AND chiso.Nam = %2$d
            GROUP BY ctban.IOID
            ORDER BY
              FIELD(ifnull(ctban.DoiTuongMua, 0), 1, 3,  2, 0, NULL)
              , if(ifnull(ctban.DonViMuaNgoai, \'\') = \'\'
              , ctban.DonViMua, ctban.DonViMuaNgoai)
              , ctban.Ma
        ', $month, $year);
        //echo '<pre>'; print_r($sql); die;

        return $this->_o_DB->fetchAll($sql);
    }

    public function getGiaDienNangMuaVao()
    {
        $sql = sprintf('

        SELECT *
        FROM
        (
            SELECT
                gia.*
                , ctm.MaCongTo
                , ctm.TenCongTo
                , ctm.Loai AS LoaiGia
                , ctm.IOID AS EMIOID
                , ctm.Ref_DonViCungCap AS PIOID
            FROM OQuanLyCongToMuaVao AS ctm
            INNER JOIN OBangGiaCongToMuaVao AS gia ON ctm.IFID_M735 = gia.IFID_M735
            WHERE
                gia.NgayHieuLuc <= %1$s
            ORDER BY ctm.IOID, gia.NgayHieuLuc DESC
        ) AS t1
        GROUP BY EMIOID
        ORDER BY EMIOID, NgayHieuLuc DESC

        ', $this->_o_DB->quote(date('Y-m-d')));
        //echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getGiaDienNangBanRa()
    {

        $sql = sprintf('

                SELECT *
                FROM
                (
                    SELECT
                        gia.*
                        , ctm.Ma AS MaCongTo
                        , ctm.Ten AS TenCongTo
                        , ctm.Loai AS LoaiGia
                        , ctm.IOID AS EMIOID
                        , ctm.Ref_DonViMuaNgoai AS PIOID
                    FROM OCongToDien AS ctm
                    INNER JOIN OBangGiaCongToBanRa AS gia ON ctm.IFID_M556 = gia.IFID_M556
                    WHERE
                        gia.NgayHieuLuc <= %1$s
                    ORDER BY ctm.IOID, gia.NgayHieuLuc DESC
                ) AS t1
                GROUP BY EMIOID
                ORDER BY EMIOID, NgayHieuLuc DESC

        ', $this->_o_DB->quote(date('Y-m-d')));

        return $this->_o_DB->fetchAll($sql);
    }


    /**
     * @param int $year
     * @return object
     */
    public function getPlanByYear($year)
    {
        $sql = sprintf('
            SELECT
                kehoach.DienNangUocTinh AS DienNangCongTy
                , kehoach.SanLuongUocTinh AS SanLuongCongTy
                , dept1.DepartmentID
                , dept1.DeptCode
                , dept1.`Name` AS DeptName
                , ifnull(khoan.DienNang, 0) AS DienNangDonVi
                , ifnull(khoan.SanLuong, 0) AS SanLuongDonVi
            FROM OKeHoachDienNangSanLuong AS kehoach
            INNER JOIN OKhoanDienNang AS khoan ON kehoach.IFID_M560 = khoan.IFID_M560
            INNER JOIN qsdepartments AS dept1 ON khoan.Ref_DonVi = dept1.DepartmentID
            WHERE kehoach.Nam = %1$d
        ', @(int)$year);
        return $this->_o_DB->fetchAll($sql);
    }      
    
    
    public function getKhoanDienNang($month, $year)
    {
        $sql = sprintf('
            SELECT 
                *
            FROM
            (
                SELECT
                    khoan.Ref_DonVi AS IDDonViDuocKhoan
                    , dept1.DeptCode AS MaDonViDuocKhoan
                    , dept1.Name AS TenDonViDuocKhoan
                    , dept1.Level AS LevelDonViDuocKhoan
                    , khoan.Ref_DonViBan AS IDDonViBan
                    , dept2.DeptCode AS MaDonViBan
                    , dept2.Level AS LevelDonViBan
                    , dept2.Name AS TenDonViBan
                    , khoan.ViTri AS ViTriKhoan
                    , ifnull(khoan.SoDienNang, 0) AS SoDienNang    
                FROM OKeHoachDienNangSanLuong AS kehoach
                INNER JOIN OKhoanDien AS khoan ON kehoach.IFID_M560 = khoan.IFID_M560
                INNER JOIN qsdepartments AS dept1 ON khoan.Ref_DonVi = dept1.DepartmentID
                LEFT JOIN qsdepartments AS dept2 ON khoan.Ref_DonViBan = dept2.DepartmentID 
                WHERE kehoach.Nam = %1$d AND 
                    (
                        ifnull(khoan.DenThang, 0) = 0 AND khoan.TuThang <= %2$d
                        OR (%2$d between khoan.TuThang and khoan.DenThang)
                    )
                ORDER BY  khoan.Ref_DonVi, ifnull(khoan.Ref_DonViBan, 0), khoan.TuThang DESC            
            ) AS t1
            GROUP BY t1.IDDonViDuocKhoan, ifnull(t1.IDDonViBan, 0)
        ', @(int)$year, $month);
        return $this->_o_DB->fetchAll($sql);        
    }

    public function getSoSanhDienNang($month, $year)
    {
        $sql = sprintf('
            SELECT
                qsdepartments.DepartmentID
                , qsdepartments.DeptCode
                , qsdepartments.`Name` AS DeptName
                , IFNULL(KeHoachDienNang.DienNangKeHoach, 0) DienNangKeHoach
                , IFNULL(KeHoachDienNang.SanLuongKeHoach, 0) SanLuongKeHoach
                , IFNULL(KeHoachDienNang.SuatTieuHaoKeHoach, 0) SuatTieuHaoKeHoach
                , IFNULL(ThucHienThangNay.TongThanSach, 0) TongThanSach
                , IFNULL(ThucHienThangNay.TongDienNang, 0) TongDienNang
                , IF(ThucHienThangNay.TongThanSach, ThucHienThangNay.TongDienNang/ThucHienThangNay.TongThanSach, 0) AS SuatTieuHao
                , IFNULL(ThucHienThangNayNamTruoc.TongThanSachCungKyNamTruoc, 0) TongThanSachCungKyNamTruoc
                , IFNULL(ThucHienThangNayNamTruoc.TongDienNangCungKyNamTruoc, 0) TongDienNangCungKyNamTruoc
                , IF(ThucHienThangNayNamTruoc.TongThanSachCungKyNamTruoc, ThucHienThangNayNamTruoc.TongDienNangCungKyNamTruoc/ThucHienThangNayNamTruoc.TongThanSachCungKyNamTruoc, 0) AS SuatTieuHaoCungKyNamTruoc
                , IF(ThucHienThangNayNamTruoc.TongDienNangCungKyNamTruoc, ThucHienThangNay.TongDienNang/ThucHienThangNayNamTruoc.TongDienNangCungKyNamTruoc, 0) AS SoSanhDienNang
            FROM qsdepartments
            LEFT JOIN (
                SELECT
                    khoan.Ref_DonVi
                    , ifnull(khoan.DienNang, 0) AS DienNangKeHoach
                    , ifnull(khoan.SanLuong, 0) AS SanLuongKeHoach
                    , ifnull(khoan.STH, 0) AS SuatTieuHaoKeHoach
                FROM OKeHoachDienNangSanLuong AS kehoach
                INNER JOIN OKhoanDienNang AS khoan ON kehoach.IFID_M560 = khoan.IFID_M560
                WHERE kehoach.Nam = %2$d
                ORDER BY kehoach.IOID DESC
                LIMIT 1
            ) KeHoachDienNang ON KeHoachDienNang.Ref_DonVi = qsdepartments.DepartmentID
            LEFT JOIN (
                SELECT ct.Ref_DonVi, SUM(IFNULL(ct.ThanSach, 0)) AS TongThanSach, SUM(IFNULL(ct.DienNang, 0)) AS TongDienNang
                FROM OHoatDongSanXuat AS thongtin
                INNER JOIN OChiTietHoatDongSanXuat AS ct ON thongtin.IFID_M149 = ct.IFID_M149
                WHERE thongtin.IFID_M149 is not null AND MONTH(thongtin.Ngay) = %1$d AND YEAR(thongtin.Ngay) = %2$d
                GROUP BY ct.Ref_DonVi
            ) ThucHienThangNay ON ThucHienThangNay.Ref_DonVi = qsdepartments.DepartmentID
            LEFT JOIN (
                SELECT
                    ct.Ref_DonVi
                    , SUM(IFNULL(ct.ThanSach, 0)) AS TongThanSachCungKyNamTruoc
                    , SUM(IFNULL(ct.DienNang, 0)) AS TongDienNangCungKyNamTruoc
                FROM OHoatDongSanXuat AS thongtin
                INNER JOIN OChiTietHoatDongSanXuat AS ct ON thongtin.IFID_M149 = ct.IFID_M149
                WHERE thongtin.IFID_M149 is not null AND MONTH(thongtin.Ngay) = %1$d AND YEAR(thongtin.Ngay) = %3$d
                GROUP BY ct.Ref_DonVi
            ) ThucHienThangNayNamTruoc ON ThucHienThangNayNamTruoc.Ref_DonVi = qsdepartments.DepartmentID
            WHERE qsdepartments.ParentID = 1
            ORDER BY qsdepartments.Level
        ', $month, $year, $year - 1);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }
}