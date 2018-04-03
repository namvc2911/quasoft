<?php
/**
 * Yeu cau mua sam
 *
 */
class Qss_Model_Purchase_Request extends Qss_Model_Abstract
{
    public function __construct ()
    {
        parent::__construct();
        $this->_user = Qss_Register::get('userinfo');
    }

    public function getRequestByIFID($requestIFID)
    {
        $sql = sprintf('
            SELECT YeuCau.*
            FROM OYeuCauMuaSam AS YeuCau
            WHERE YeuCau.IFID_M412 = %1$d
        ', $requestIFID);
        return $this->_o_DB->fetchOne($sql);
    }

    public function getRequestLineByIFID($requestIFID)
    {
        $extendSelect = '';

        if(Qss_Lib_System::fieldActive('OSanPham', 'MaTam')) {
            $extendSelect = ', IFNULL(MaTam, 0) AS MaTam';
        }

        $sql = sprintf('
            SELECT DanhSach.*, MatHang.SLToiThieu %2$s
            FROM OYeuCauMuaSam AS YeuCau
            INNER JOIN ODSYeuCauMuaSam AS DanhSach On YeuCau.IFID_M412 = DanhSach.IFID_M412
            INNER JOIN OSanPham AS MatHang ON DanhSach.Ref_MaSP = MatHang.IOID
            WHERE YeuCau.IFID_M412 = %1$d
        ', $requestIFID, $extendSelect);
        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * Lay ra chi tiet mua hang theo nhung yeu cau da duoc duyet
     * @param array $requestIOIDs - danh sach cac yeu cau <Luu y chi co nhung yeu cau da duoc duyet>
     * @return mixed
     */
    public function getRequestDetails($requestIOIDs = array())
    {
        $filter = (count($requestIOIDs))?sprintf(' AND yeucau.IOID in (%1$s) ', implode(', ', $requestIOIDs)):'';
        $sql = sprintf('
            SELECT
                yeucaumuahang.*
                , ifnull(yeucaumuahang.SoLuongYeuCau, 0) AS Requested
                , ifnull(kehoachmuahang.PlanIOID, 0) AS PlanIOID
                , ifnull(kehoachmuahang.SoLuongKeHoach, 0) AS Planed
                , ifnull(baogiamuahang.SoBaoGia, 0) AS AlreadyQuote
                , ifnull(donmuahang.SoLuongDatHang, 0) AS Ordered
                , ifnull(SoLuongDatHangKhongCoKeHoach, 0)  AS OrderedNotPlan
                , ifnull(SoLuongDatHangCoKeHoach, 0) AS OrderedHasPlan            
                , ifnull(nhanmuahang.SoLuongNhan, 0) AS Received         
            FROM
            (
                SELECT
                    yeucau.SoPhieu
                    , yeucau.IOID AS RequestIOID
                    , chitiet.Ref_MaSP AS ItemIOID
                    , chitiet.Ref_DonViTinh AS UomIOID
                    , chitiet.MaSP AS MaMatHang
                    , chitiet.TenSP AS TenMatHang
                    , chitiet.DonViTinh
                    , chitiet.SoLuong AS SoLuongYeuCau
                    , chitiet.MucDich
                FROM OYeuCauMuaSam AS yeucau
                INNER JOIN qsiforms AS iform ON yeucau.IFID_M412 = iform.IFID
                LEFT JOIN ODSYeuCauMuaSam AS chitiet ON yeucau.IFID_M412 = chitiet.IFID_M412
                WHERE iform.Status = 3 %1$s
                ORDER BY yeucau.IFID_M412, chitiet.MaSP, chitiet.DonViTinh
            ) AS yeucaumuahang

            LEFT JOIN
            (
                SELECT
                    ifnull(kehoach.IOID, 0) AS PlanIOID
                    , ifnull(yeucau.IOID, 0) AS RequestIOID
                    , ifnull(chitiet.Ref_MaSP, 0) AS ItemIOID
                    , ifnull(chitiet.Ref_DonViTinh, 0) AS UomIOID
                    , sum(ifnull(chitiet.SoLuongYeuCau, 0)) AS SoLuongKeHoach
                FROM OKeHoachMuaSam AS kehoach
                INNER JOIN ODSKeHoachMuaSam AS chitiet ON kehoach.IFID_M716 = chitiet.IFID_M716
                INNER JOIN OYeuCauMuaSam AS yeucau ON chitiet.Ref_SoYeuCau = yeucau.IOID
                WHERE 1=1 %1$s
                GROUP BY yeucau.IFID_M412, ifnull(chitiet.Ref_MaSP, 0), ifnull(chitiet.Ref_DonViTinh, 0)
                ORDER BY yeucau.IFID_M412, ifnull(chitiet.Ref_MaSP, 0), ifnull(chitiet.Ref_DonViTinh, 0)
            ) AS kehoachmuahang ON yeucaumuahang.RequestIOID = kehoachmuahang.RequestIOID 
                AND yeucaumuahang.ItemIOID = kehoachmuahang.ItemIOID
                AND yeucaumuahang.UomIOID = kehoachmuahang.UomIOID

            LEFT JOIN
            (
                SELECT
                    count(1) As SoBaoGia                   
                    , ifnull(danhsach.Ref_MaSP, 0) AS ItemIOID
                    , ifnull(danhsach.Ref_DonViTinh, 0) AS UomIOID
                FROM OBaoGiaMuaHang AS baogia
                INNER JOIN ODSBGMuaHang AS danhsach ON baogia.IFID_M406 = danhsach.IFID_M406                
                GROUP BY danhsach.Ref_MaSP, danhsach.Ref_DonViTinh
            ) AS baogiamuahang ON  yeucaumuahang.ItemIOID = baogiamuahang.ItemIOID
                AND yeucaumuahang.UomIOID = baogiamuahang.UomIOID

            LEFT JOIN
            (
                SELECT 
                    ifnull(yeucau.IOID, 0) AS RequestIOID
                    , ifnull(danhsach.Ref_MaSP, 0) AS ItemIOID
                    , ifnull(danhsach.Ref_DonViTinh, 0) AS UomIOID                    
                    , sum(danhsach.SoLuong) AS SoLuongDatHang
                    , 
                     sum(CASE WHEN ifnull(donhang.Ref_SoKeHoach, 0) = 0 THEN danhsach.SoLuong ELSE 0 END) 
                     AS SoLuongDatHangKhongCoKeHoach
                    , 
                     sum(CASE WHEN ifnull(donhang.Ref_SoKeHoach, 0) != 0 THEN danhsach.SoLuong ELSE 0 END) 
                     AS SoLuongDatHangCoKeHoach
                FROM ODonMuaHang AS donhang
                INNER JOIN ODSDonMuaHang AS danhsach ON donhang.IFID_M401 = danhsach.IFID_M401
                INNER JOIN OYeuCauMuaSam AS yeucau ON danhsach.Ref_SoYeuCau = yeucau.IOID
                WHERE 1=1 %1$s
                GROUP BY yeucau.IOID, danhsach.Ref_MaSP, danhsach.Ref_DonViTinh
                ORDER BY yeucau.IOID, danhsach.Ref_MaSP, danhsach.Ref_DonViTinh
            ) AS donmuahang ON yeucaumuahang.RequestIOID = donmuahang.RequestIOID 
                AND yeucaumuahang.ItemIOID = donmuahang.ItemIOID
                AND yeucaumuahang.UomIOID = donmuahang.UomIOID
          
            LEFT JOIN
            (
                SELECT 
                    ifnull(yeucau.IOID, 0) AS RequestIOID
                    , ifnull(danhsach.Ref_DonViTinh, 0) AS UomIOID                    
                    , ifnull(danhsach.Ref_MaMatHang, 0) AS ItemIOID
                    , sum(ifnull(danhsach.SoLuong, 0)) AS SoLuongNhan
                FROM ONhanHang AS nhanhang
                INNER JOIN ODanhSachNhanHang AS danhsach ON nhanhang.IFID_M408 = danhsach.IFID_M408
                INNER JOIN OYeuCauMuaSam AS yeucau ON danhsach.Ref_SoYeuCau = yeucau.IOID
                INNER JOIN qsiforms ON nhanhang.IFID_M408 = qsiforms.IFID
                WHERE qsiforms.Status = 2 %1$s
                GROUP BY yeucau.IOID, danhsach.MaMatHang, danhsach.DonViTinh
                ORDER BY yeucau.IOID, danhsach.MaMatHang, danhsach.DonViTinh
            ) AS nhanmuahang ON yeucaumuahang.RequestIOID = nhanmuahang.RequestIOID 
                AND yeucaumuahang.ItemIOID = nhanmuahang.ItemIOID         
                AND yeucaumuahang.UomIOID = nhanmuahang.UomIOID   

        ', $filter);
   
        //echo '<pre>'; print_r($sql); die;
       
        return $this->_o_DB->fetchAll($sql);
    }

    public function getDraftRequests1($uid, $sessionIFID)
    {
        $sql = sprintf('
            SELECT
                ODSYeuCauMuaSam.*
                , OYeuCauMuaSam.SoPhieu
                , OYeuCauMuaSam.IOID AS RequestIOID
                , OYeuCauMuaSam.IFID_M412 AS RequestIFID
                , IFNULL(OYeuCauPhienXLMH.IOID, 0) AS SessionLineIOID
                , IFNULL(OYeuCauPhienXLMH.Chon, 0) AS Chon
            FROM OYeuCauMuaSam
            INNER JOIN qsiforms ON OYeuCauMuaSam.IFID_M412 = qsiforms.IFID
            INNER JOIN ODSYeuCauMuaSam ON OYeuCauMuaSam.IFID_M412 = ODSYeuCauMuaSam.IFID_M412
            LEFT JOIN OYeuCauPhienXLMH ON OYeuCauMuaSam.IOID = IFNULL(OYeuCauPhienXLMH.Ref_SoPhieu, 0)
            WHERE qsiforms.UID = %1$d AND IFNULL(OYeuCauPhienXLMH.IOID , 0) = 0 AND qsiforms.Status = 1

            UNION ALL

            SELECT
                ODSYeuCauMuaSam.*
                , OYeuCauMuaSam.SoPhieu
                , OYeuCauMuaSam.IOID AS RequestIOID
                , OYeuCauMuaSam.IFID_M412 AS RequestIFID
                , IFNULL(OYeuCauPhienXLMH.IOID, 0) AS SessionLineIOID
                , IFNULL(OYeuCauPhienXLMH.Chon, 0) AS Chon
            FROM OYeuCauPhienXLMH
            INNER JOIN OYeuCauMuaSam ON OYeuCauPhienXLMH.Ref_SoPhieu = OYeuCauMuaSam.IOID
            INNER JOIN ODSYeuCauMuaSam ON OYeuCauMuaSam.IFID_M412 = ODSYeuCauMuaSam.IFID_M412
            WHERE OYeuCauPhienXLMH.IFID_M415 = %2$d
        ', $uid, $sessionIFID);
        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * Lay yeu cau theo session
     * <Luu y: voi phan lay ke hoach ham chi loc theo ke hoach cua phien>
     * @param unknown $sessionIFID
     * @param unknown $planIOID
     */
    public function getRequestsBySession($sessionIFID)
    {
        $sql = sprintf('
            
            SELECT 
                t1.*,
                IF(t1.ConLai > 0,  t1.ConLai, 0) AS ConLai
            FROM
            (
                SELECT
                    yeucaumuahang.*
                    , ifnull(yeucaumuahang.SoLuong, 0) AS Requested
                    , ifnull(kehoachmuahang.PlanIOID, 0) AS PlanIOID
                    , ifnull(kehoachmuahang.SoLuongYeuCau, 0) AS Planed
                    , ifnull(donmuahang.SoLuong, 0) AS Ordered  
                    , (ifnull(yeucaumuahang.SoLuong, 0) - ifnull(donmuahang.SoLuong, 0)) AS ConLai                       
                FROM            
                (
                    SELECT
                        yeucau.SoPhieu
                        , ifnull(yeucau.IFID_M412, 0)  AS RequestIFID 
                        , yeucau.IOID AS RequestIOID
                        , chitiet.Ref_MaSP AS ItemIOID
                        , chitiet.Ref_DonViTinh AS UomIOID
                        , chitiet.MaSP
                        , chitiet.TenSP
                        , chitiet.DonViTinh
                        , ifnull(chitiet.SoLuong, 0) AS SoLuong
                        , chitiet.MucDich
                    FROM OYeuCauPhienXLMH AS yeucaucuaphien
                    INNER JOIN OYeuCauMuaSam AS yeucau ON yeucaucuaphien.Ref_SoPhieu = yeucau.IOID
                    LEFT JOIN ODSYeuCauMuaSam AS chitiet ON yeucau.IFID_M412 = chitiet.IFID_M412
                    WHERE yeucaucuaphien.IFID_M415 = %1$d
                ) AS yeucaumuahang
                
                LEFT JOIN
                (
                    SELECT
                        ifnull(kehoach.IOID, 0) AS PlanIOID
                        , ifnull(chitiet.Ref_SoYeuCau, 0) AS RequestIOID
                        , ifnull(chitiet.Ref_MaSP, 0) AS ItemIOID
                        , ifnull(chitiet.Ref_DonViTinh, 0) AS UomIOID
                        , ifnull(chitiet.SoLuongYeuCau, 0) AS SoLuongYeuCau
                    FROM OKeHoachMuaSam AS kehoach
                    INNER JOIN ODSKeHoachMuaSam AS chitiet ON kehoach.IFID_M716 = chitiet.IFID_M716
                    WHERE kehoach.IOID IN (
                        SELECT Ref_SoKeHoach FROM OPhienXuLyMuaHang WHERE IFID_M415 = %1$d
                    ) 
                ) AS kehoachmuahang ON yeucaumuahang.RequestIOID = kehoachmuahang.RequestIOID 
                    AND yeucaumuahang.ItemIOID = kehoachmuahang.ItemIOID
                    AND yeucaumuahang.UomIOID = kehoachmuahang.UomIOID
                
                LEFT JOIN
                (
                    SELECT 
                        ifnull(yeucau.IOID, 0) AS RequestIOID
                        , ifnull(danhsach.Ref_MaSP, 0) AS ItemIOID
                        , ifnull(danhsach.Ref_DonViTinh, 0) AS UomIOID                    
                        , sum(danhsach.SoLuong) AS SoLuong
                    FROM ODonMuaHang AS donhang
                    INNER JOIN ODSDonMuaHang AS danhsach ON donhang.IFID_M401 = danhsach.IFID_M401
                    INNER JOIN OYeuCauPhienXLMH AS yeucaucuaphien ON danhsach.Ref_SoYeuCau = yeucaucuaphien.Ref_SoPhieu
                    INNER JOIN OYeuCauMuaSam AS yeucau ON yeucaucuaphien.Ref_SoPhieu = yeucau.IOID
                    WHERE yeucaucuaphien.IFID_M415 = %1$d
                    GROUP BY yeucau.IOID, danhsach.Ref_MaSP, danhsach.Ref_DonViTinh
                    ORDER BY yeucau.IOID, danhsach.Ref_MaSP, danhsach.Ref_DonViTinh
                ) AS donmuahang ON yeucaumuahang.RequestIOID = donmuahang.RequestIOID 
                    AND yeucaumuahang.ItemIOID = donmuahang.ItemIOID
                    AND yeucaumuahang.UomIOID = donmuahang.UomIOID       
            ) AS t1     
        ', $sessionIFID);
        
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * Lay doi tuong chinh cua yeu cau <khong bao gom chi tiet mat hang yeu cau>
     * @param $sessionIFID
     */
    public function getRequestsInfoBySession($sessionIFID)
    {
        $sql = sprintf('
            SELECT
                yeucau.*
            FROM OYeuCauPhienXLMH AS yeucaucuaphien
            INNER JOIN OYeuCauMuaSam AS yeucau ON yeucaucuaphien.Ref_SoPhieu = yeucau.IOID
            WHERE yeucaucuaphien.IFID_M415 = %1$d
        ', $sessionIFID);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getRequestsByPlan($planIOID)
    {
        $sql = sprintf('
            SELECT
                YeuCau.*, DuAn.KhachHang, IFNULL(DuAn.Ref_KhachHang, 0) AS Ref_KhachHang
            FROM OKeHoachMuaSam AS KeHoach
            INNER JOIN ODSKeHoachMuaSam AS DSKeHoach ON KeHoach.IFID_M716 = DSKeHoach.IFID_M716
            INNER JOIN OYeuCauMuaSam AS YeuCau ON DSKeHoach.Ref_SoYeuCau = YeuCau.IOID
            LEFT JOIN ODuAn AS DuAn On YeuCau.Ref_DuAn = DuAn.IOID
            WHERE KeHoach.IOID = %1$d
            GROUP BY YeuCau.IFID_M412
        ', $planIOID);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * @use-in: Mẫu in M220 của POS
     * @param $orderIOID
     * @return mixed
     */
    public function getRequestsByOrder($orderIOID)
    {
        $sql = sprintf('
            SELECT
                YeuCau.*, DuAn.KhachHang, IFNULL(DuAn.Ref_KhachHang, 0) AS Ref_KhachHang
            FROM ODonMuaHang AS DonMuaHang
            INNER JOIN OKeHoachMuaSam AS KeHoach ON DonMuaHang.Ref_SoKeHoach = KeHoach.IOID
            INNER JOIN ODSKeHoachMuaSam AS DSKeHoach ON KeHoach.IFID_M716 = DSKeHoach.IFID_M716
            INNER JOIN OYeuCauMuaSam AS YeuCau ON DSKeHoach.Ref_SoYeuCau = YeuCau.IOID
            LEFT JOIN ODuAn AS DuAn On YeuCau.Ref_DuAn = DuAn.IOID
            WHERE DonMuaHang.IOID = %1$d
            GROUP BY YeuCau.IFID_M412
        ', $orderIOID);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getTrackRequests($start, $end, $requestIOID = 0, $employeeIOID = 0)
    {
        $where = $requestIOID?sprintf(' AND YeuCau.IOID = %1$d ', $requestIOID):'';
        $where.= $employeeIOID?sprintf(' AND IFNULL(YeuCau.Ref_NguoiDeNghi, 0) = %1$d ', $employeeIOID):'';

        $sql = sprintf('
            SELECT
                DanhSach.Ref_MaSP
                , DanhSach.MaSP
                , DanhSach.TenSP
                , YeuCau.SoPhieu
                , YeuCau.NguoiDeNghi
                -- , DanhSach.DaThanhToan
                , IF(IFNULL(DanhSach.NgayYeuCau, "") != "", DanhSach.NgayCanCo, YeuCau.Ngay) AS NgayYeuCau
                , DanhSach.DonViTinh
                , DanhSach.SoLuong
                , IF(IFNULL(DanhSach.NgayCanCo, "") != "", DanhSach.NgayCanCo, YeuCau.NgayCanCo) AS NgayCanCo
                , IF(IFNULL(DanhSach.NgayYeuCau, "") != "", DanhSach.NgayCanCo, YeuCau.Ngay) AS Ngay
                , YeuCau.IOID AS RequestIOID
                , NhapKho.NgayChungTu AS NgayChungTuNhapKho
                , NhapKho.NgayChuyenHang AS NgayChuyenHangNhapKho
                , IFNULL(DanhSachNhapKho.SoLuong, 0) AS SoLuongNhapKho
                , NhapKho.SoChungTu AS SoPhieuNhapKho
                , NhapKho.TenNCC
                , DanhSachNhapKho.DonGia
                , DanhSachNhapKho.ThanhTien
                , MatHang.IOID As Ref_MatHang
            FROM OYeuCauMuaSam AS YeuCau
            INNER JOIN ODSYeuCauMuaSam AS DanhSach On YeuCau.IFID_M412 = DanhSach.IFID_M412
            INNER JOIN OSanPham AS MatHang ON DanhSach.Ref_MaSP = MatHang.IOID
            LEFT JOIN ODanhSachNhapKho AS DanhSachNhapKho ON YeuCau.IOID = IFNULL(DanhSachNhapKho.Ref_SoYeuCau, 0)
                AND DanhSach.Ref_MaSP = DanhSachNhapKho.Ref_MaSanPham
            LEFT JOIN ONhapKho AS NhapKho ON DanhSachNhapKho.IFID_M402 = NhapKho.IFID_M402
            WHERE (YeuCau.Ngay BETWEEN %1$s AND %2$s) %3$s

            UNION

            SELECT
                DanhSachNhapKho.Ref_MaSanPham AS Ref_MaSP
                , DanhSachNhapKho.MaSanPham AS MaSP
                , DanhSachNhapKho.TenSanPham AS TenSP
                , YeuCau.SoPhieu
                , YeuCau.NguoiDeNghi
                -- , DanhSach.DaThanhToan
                , YeuCau.Ngay AS NgayYeuCau
                , DanhSachNhapKho.DonViTinh
                , 0 AS SoLuong
                , YeuCau.NgayCanCo
                , YeuCau.Ngay
                , YeuCau.IOID AS RequestIOID
                , NhapKho.NgayChungTu AS NgayChungTuNhapKho
                , NhapKho.NgayChuyenHang AS NgayChuyenHangNhapKho
                , IFNULL(DanhSachNhapKho.SoLuong, 0) AS SoLuongNhapKho
                , NhapKho.SoChungTu AS SoPhieuNhapKho
                , NhapKho.TenNCC
                , DanhSachNhapKho.DonGia
                , DanhSachNhapKho.ThanhTien
                , DanhSachNhapKho.Ref_MaSanPham As Ref_MatHang
            FROM ONhapKho AS NhapKho
            INNER JOIN ODanhSachNhapKho AS DanhSachNhapKho ON DanhSachNhapKho.IFID_M402 = NhapKho.IFID_M402
            INNER JOIN OYeuCauMuaSam AS YeuCau ON YeuCau.IOID = IFNULL(DanhSachNhapKho.Ref_SoYeuCau, 0)
            LEFT JOIN ODSYeuCauMuaSam AS DanhSach ON YeuCau.IFID_M412 = DanhSach.IFID_M412
                AND DanhSach.Ref_MaSP = DanhSachNhapKho.Ref_MaSanPham
            WHERE IFNULL(DanhSachNhapKho.Ref_SoYeuCau, 0) IN (
                SELECT
                    YeuCau.IOID
                FROM OYeuCauMuaSam AS YeuCau
                WHERE (YeuCau.Ngay BETWEEN %1$s AND %2$s) %3$s
            ) AND IFNULL(DanhSach.IOID, 0) = 0

            ORDER BY RequestIOID, Ref_MatHang
            '
            , $this->_o_DB->quote($start)
            , $this->_o_DB->quote($end)
            , $where
        );
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function countRequestsNeedApprove()
    {
        $sql = sprintf('
            SELECT count(1) AS Total
            FROM OYeuCauMuaSam 
            INNER JOIN qsiforms  ON OYeuCauMuaSam.IFID_M412 = qsiforms.IFID                       
            WHERE qsiforms.Status = 2           
        ');

        $data = $this->_o_DB->fetchOne($sql);
        return $data?$data->Total:0;
    }

    public function getUnderMinimumItems($user)
    {
        // Lấy hạn mức của sản phẩm + Lấy hạn mức ở từng kho cụ thể => Kết quả order by mã mặt hàng
        $sqlItems = sprintf('
            SELECT DISTINCT IOID
            FROM (
                -- Lấy từ hàn mức lưu trữ từng kho
                SELECT
                    MatHang.IOID
                    , HanMuc.MaKho
                    , HanMuc.TenKho
                    , HanMuc.Ref_MaKho
                    , sum(IFNULL(Kho.SoLuongHC,0) * IFNULL(DonViTinh.HeSoQuyDoi, 1)) AS TongTonKho
                    , IFNULL(HanMuc.SoLuongThoiThieu, 0) AS ToiThieu
                    , IFNULL(HanMuc.SoLuongToiDa, 0) AS ToiDa
                FROM OSanPham AS MatHang            
                INNER JOIN OHanMucLuuTru AS HanMuc ON MatHang.IFID_M113 = HanMuc.IFID_M113      
                LEFT JOIN OKho AS Kho ON MatHang.IOID = Kho.Ref_MaSP AND HanMuc.Ref_MaKho = Kho.Ref_Kho 
                LEFT JOIN ODonViTinhSP AS DonViTinh ON MatHang.IFID_M113 = DonViTinh.IFID_M113 
                    AND Kho.Ref_DonViTinh = DonViTinh.IOID');

        if(Qss_Lib_System::formSecure('M601'))
        {
            $sqlItems .= sprintf('
	            WHERE IFNULL(HanMuc.Ref_MaKho, 0) in (
	                SELECT IOID FROM ODanhSachKho
                    inner join qsrecordrights on ODanhSachKho.IFID_M601 = qsrecordrights.IFID
                    WHERE UID = %1$d)
            ', $user->user_id);
        }

        if(Qss_Lib_System::fieldActive('OSanPham', 'MaTam'))
        {
            $sqlItems .= sprintf(' AND IFNULL(MatHang.MaTam, 0) = 0 ');
        }

        $sqlItems .= sprintf('
            GROUP BY HanMuc.Ref_MaKho, MatHang.IOID
            HAVING ToiThieu > TongTonKho AND ToiThieu != 0

            UNION
            
            -- Lấy từ hạn mức lưu trữ từng sản phẩm
            SELECT
                MatHang.IOID
                , NULL AS MaKho
                , NULL AS TenKho
                , 0 AS Ref_MaKho                    
                , sum(IFNULL(Kho.SoLuongHC,0) * IFNULL(DonViTinh.HeSoQuyDoi, 1)) AS TongTonKho
                , IFNULL(MatHang.SLToiThieu, 0) AS ToiThieu
                , IFNULL(MatHang.SLToiDa, 0) AS ToiDa
            FROM OSanPham AS MatHang
            LEFT JOIN OKho AS Kho ON MatHang.IOID = Kho.Ref_MaSP 
            LEFT JOIN ODonViTinhSP AS DonViTinh ON MatHang.IFID_M113 = DonViTinh.IFID_M113 
                AND Kho.Ref_DonViTinh = DonViTinh.IOID
        ');

        if(Qss_Lib_System::fieldActive('OSanPham', 'MaTam'))
        {
            $sqlItems .= sprintf(' WHERE IFNULL(MatHang.MaTam, 0) = 0 ');
        }

        $sqlItems .= sprintf('
                GROUP BY MatHang.IOID
                HAVING ToiThieu > TongTonKho AND ToiThieu != 0    
            ) AS HanMucLuuTru            
        ');


        // echo '<pre>'; print_r($sqlItems); die;
        $dataItems = $this->_o_DB->fetchAll($sqlItems);
        $items     = array(0);

        if($dataItems)
        {
            foreach($dataItems as $item)
            {
                $items[] = $item->IOID;
            }
        }

        $sql = sprintf('
            SELECT
                YeuCauVatTu.*
                , IFNULL(YeuCauMuaSam.TongYeuCauMua, 0) AS TongYeuCauMua
                , IFNULL(MuaHang.TongDatHang, 0) AS TongDatHang
                , IFNULL(NhapKho.TongNhapKho, 0) AS TongNhapKho
            FROM
            (
                SELECT *
                FROM (
                    -- Lấy từ hàn mức lưu trữ từng kho
                    SELECT
                        MatHang.*   
                        , HanMuc.MaKho
                        , HanMuc.TenKho
                        , HanMuc.Ref_MaKho
                        , sum(IFNULL(Kho.SoLuongHC,0) * IFNULL(DonViTinh.HeSoQuyDoi, 1)) AS TongTonKho
                        , IFNULL(HanMuc.SoLuongThoiThieu, 0) AS ToiThieu
                        , IFNULL(HanMuc.SoLuongToiDa, 0) AS ToiDa
                        , DonViTinhCS.IOID AS Ref_DonViTinhCoSo
                        , DonViTinhCS.DonViTinh AS DonViTinhCoSo                        
                    FROM OSanPham AS MatHang 
                    INNER JOIN ODonViTinhSP AS DonViTinhCS ON MatHang.IFID_M113 = DonViTinhCS.IFID_M113 
                        AND MatHang.Ref_DonViTinh = DonViTinhCS.Ref_DonViTinh
                    INNER JOIN OHanMucLuuTru AS HanMuc ON MatHang.IFID_M113 = HanMuc.IFID_M113      
                    LEFT JOIN OKho AS Kho ON MatHang.IOID = Kho.Ref_MaSP AND HanMuc.Ref_MaKho = Kho.Ref_Kho 
                    LEFT JOIN ODonViTinhSP AS DonViTinh ON MatHang.IFID_M113 = DonViTinh.IFID_M113 
                        AND Kho.Ref_DonViTinh = DonViTinh.IOID
                    ');

        if(Qss_Lib_System::formSecure('M601'))
        {
            $sql .= sprintf('
	            WHERE IFNULL(HanMuc.Ref_MaKho, 0) in (
	                SELECT IOID FROM ODanhSachKho
                    inner join qsrecordrights on ODanhSachKho.IFID_M601 = qsrecordrights.IFID
                    WHERE UID = %1$d)
            ', $user->user_id);
        }

        $sql .= sprintf('
                GROUP BY HanMuc.Ref_MaKho, MatHang.IOID
                HAVING ToiThieu > TongTonKho AND ToiThieu != 0
            
                    UNION
                    
                    -- Lấy từ hạn mức lưu trữ từng sản phẩm
                    SELECT
                        MatHang.*      
                        , NULL AS MaKho
                        , NULL AS TenKho
                        , 0 AS Ref_MaKho                    
                        , sum(IFNULL(Kho.SoLuongHC,0) * IFNULL(DonViTinh.HeSoQuyDoi, 1)) AS TongTonKho
                        , IFNULL(MatHang.SLToiThieu, 0) AS ToiThieu
                        , IFNULL(MatHang.SLToiDa, 0) AS ToiDa
                        , DonViTinhCS.IOID AS Ref_DonViTinhCoSo
                        , DonViTinhCS.DonViTinh AS DonViTinhCoSo                        
                    FROM OSanPham AS MatHang         
                    INNER JOIN ODonViTinhSP AS DonViTinhCS ON MatHang.IFID_M113 = DonViTinhCS.IFID_M113 
                        AND MatHang.Ref_DonViTinh = DonViTinhCS.Ref_DonViTinh                                                            
                    LEFT JOIN OKho AS Kho ON MatHang.IOID = Kho.Ref_MaSP 
                    LEFT JOIN ODonViTinhSP AS DonViTinh ON MatHang.IFID_M113 = DonViTinh.IFID_M113 
                        AND Kho.Ref_DonViTinh = DonViTinh.IOID
                    
                    GROUP BY MatHang.IOID
                    HAVING ToiThieu > TongTonKho AND ToiThieu != 0    
                ) AS HanMucLuuTru                
            ) AS YeuCauVatTu 

            LEFT JOIN
            (
                SELECT
                    DanhSach.Ref_MaSP
                    , DanhSach.Ref_DonViTinh
                    , SUM(IFNULL(DanhSach.SoLuong, 0) * IFNULL(DonViTinh.HeSoQuyDoi, 0)) AS TongYeuCauMua
                FROM OYeuCauMuaSam AS YeuCauMS
                INNER JOIN ODSYeuCauMuaSam AS DanhSach ON YeuCauMS.IFID_M412 = DanhSach.IFID_M412
                INNER JOIN qsiforms AS iform ON YeuCauMS.IFID_M412 = iform.IFID
                INNER JOIN ODonViTinhSP AS DonViTinh ON DanhSach.Ref_DonViTinh = DonViTinh.IOID
                WHERE DanhSach.Ref_MaSP IN (%1$s) AND iform.Status IN (1,2)
                GROUP BY DanhSach.Ref_MaSP, DanhSach.Ref_DonViTinh
            ) AS YeuCauMuaSam ON YeuCauVatTu.IOID = YeuCauMuaSam.Ref_MaSP
                AND YeuCauVatTu.Ref_DonViTinhCoSo = YeuCauMuaSam.Ref_DonViTinh
            -- AND IFNULL(YeuCauVatTu.Ref_ThuocTinh, 0) = IFNULL(YeuCauMuaSam.Ref_ThuocTinh, 0)
            LEFT JOIN
            (
                SELECT
                    DanhSach.Ref_MaSP
                    , DanhSach.Ref_DonViTinh
                    , SUM(IFNULL(DanhSach.SoLuong, 0) * IFNULL(DonViTinh.HeSoQuyDoi, 0)) AS TongDatHang
                FROM ODonMuaHang AS MuaHang
                INNER JOIN ODSDonMuaHang AS DanhSach ON MuaHang.IFID_M401 = DanhSach.IFID_M401
                INNER JOIN qsiforms AS iform ON MuaHang.IFID_M401 = iform.IFID
                INNER JOIN ODonViTinhSP AS DonViTinh ON DanhSach.Ref_DonViTinh = DonViTinh.IOID
                WHERE DanhSach.Ref_MaSP IN (%1$s) AND iform.Status IN (1,2)
                GROUP BY DanhSach.Ref_MaSP, DanhSach.Ref_DonViTinh
            ) AS MuaHang ON YeuCauVatTu.IOID = MuaHang.Ref_MaSP
                AND YeuCauVatTu.Ref_DonViTinhCoSo = MuaHang.Ref_DonViTinh
                -- AND YeuCauMuaSam.IOID = MuaHang.Ref_SoYeuCau
            LEFT JOIN
            (
                SELECT
                    DanhSach.Ref_MaSanPham
                    , DanhSach.Ref_DonViTinh
                    , SUM(IFNULL(DanhSach.SoLuong, 0) * IFNULL(DonViTinh.HeSoQuyDoi, 0)) AS TongNhapKho
                FROM ONhapKho AS NhapKho
                INNER JOIN ODanhSachNhapKho AS DanhSach ON NhapKho.IFID_M402 = DanhSach.IFID_M402
                INNER JOIN qsiforms AS iform ON NhapKho.IFID_M402 = iform.IFID
                INNER JOIN ODonViTinhSP AS DonViTinh ON DanhSach.Ref_DonViTinh = DonViTinh.IOID
                WHERE DanhSach.Ref_MaSanPham IN (%1$s) AND iform.Status = 2
            ) AS NhapKho ON YeuCauVatTu.IOID = NhapKho.Ref_MaSanPham
                AND YeuCauVatTu.Ref_DonViTinhCoSo = NhapKho.Ref_DonViTinh
                
            ORDER BY MaSanPham, MaKho
            
        ', implode(',', $items));
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

}
?>