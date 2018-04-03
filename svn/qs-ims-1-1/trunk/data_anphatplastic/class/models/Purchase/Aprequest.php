<?php
/**
 * Yeu cau mua sam
 *
 */
class Qss_Model_Purchase_Aprequest extends Qss_Model_Abstract
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param $start
     * @param $end
     * @param int $requestIOID
     * @param int $employeeIOID
     * @return mixed
     * Danh sách được lấy từ yêu cầu mua sắm và những nhập kho có yêu cầu mua sắm nhưng mặt hàng không có trong danh
     * sách yêu cầu.
     */
    public function getTrackRequests($start, $end, $requestIOID = 0, $employeeIOID = 0, $materials = array())
    {
        $where = $requestIOID?sprintf(' AND YeuCau.IOID = %1$d ', $requestIOID):'';
        $where.= $employeeIOID?sprintf(' AND IFNULL(YeuCau.Ref_NguoiDeNghi, 0) = %1$d ', $employeeIOID):'';
        $where1 = count($materials)?sprintf(' AND DanhSach.Ref_MaSP IN (%1$s) ', implode(',', $materials)):'';
        $where2 = count($materials)?sprintf(' AND DanhSachNhapKho.Ref_MaSanPham IN (%1$s) ', implode(',', $materials)):'';

        $sql = sprintf('
            SELECT
                DanhSach.Ref_MaSP
                , DanhSach.MaSP
                , DanhSach.TenSP
                , DanhSach.LyDoHangChuaVe
                , DanhSach.NgayDuKien
                , YeuCau.SoPhieu
                , YeuCau.NguoiDeNghi
                , DanhSach.DaThanhToan
                , IF(IFNULL(DanhSach.NgayYeuCau, "") != "", DanhSach.NgayYeuCau, YeuCau.Ngay) AS NgayYeuCau
                , DanhSach.DonViTinh
                , DanhSach.SoLuong
                , IF(IFNULL(DanhSach.NgayCanCo, "") != "", DanhSach.NgayCanCo, YeuCau.NgayCanCo) AS NgayCanCo
                , IF(IFNULL(DanhSach.NgayYeuCau, "") != "", DanhSach.NgayYeuCau, YeuCau.Ngay) AS Ngay
                , YeuCau.IOID AS RequestIOID
                , NhapKho.NgayChungTu AS NgayChungTuNhapKho
                , NhapKho.NgayChuyenHang AS NgayChuyenHangNhapKho
                , IFNULL(DanhSachNhapKho.SoLuong, 0) AS SoLuongNhapKho
                , NhapKho.SoChungTu AS SoPhieuNhapKho
                , DanhSach.NCC AS TenNCC
                , DanhSachNhapKho.DonGia
                , DanhSachNhapKho.ThanhTien
            FROM OYeuCauMuaSam AS YeuCau
            INNER JOIN qsiforms ON YeuCau.IFID_M412 = qsiforms.IFID
            INNER JOIN ODSYeuCauMuaSam AS DanhSach On YeuCau.IFID_M412 = DanhSach.IFID_M412
            LEFT JOIN ODanhSachNhapKho AS DanhSachNhapKho ON YeuCau.IOID = IFNULL(DanhSachNhapKho.Ref_SoYeuCau, 0)
                AND DanhSach.Ref_MaSP = DanhSachNhapKho.Ref_MaSanPham
            LEFT JOIN ONhapKho AS NhapKho ON DanhSachNhapKho.IFID_M402 = NhapKho.IFID_M402
            WHERE (YeuCau.Ngay BETWEEN %1$s AND %2$s) AND qsiforms.Status IN (3, 5) %3$s %4$s

            UNION ALL

            SELECT
                DanhSachNhapKho.Ref_MaSanPham AS Ref_MaSP
                , DanhSachNhapKho.MaSanPham AS MaSP
                , DanhSachNhapKho.TenSanPham AS TenSP
                , NULL AS LyDoHangChuaVe
                , NULL AS NgayDuKien
                , YeuCau.SoPhieu
                , YeuCau.NguoiDeNghi
                , DanhSach.DaThanhToan
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
            FROM ONhapKho AS NhapKho
            INNER JOIN ODanhSachNhapKho AS DanhSachNhapKho ON DanhSachNhapKho.IFID_M402 = NhapKho.IFID_M402
            INNER JOIN OYeuCauMuaSam AS YeuCau ON YeuCau.IOID = IFNULL(DanhSachNhapKho.Ref_SoYeuCau, 0)
            
            LEFT JOIN ODSYeuCauMuaSam AS DanhSach ON YeuCau.IFID_M412 = DanhSach.IFID_M412
                AND DanhSach.Ref_MaSP = DanhSachNhapKho.Ref_MaSanPham
            WHERE IFNULL(DanhSachNhapKho.Ref_SoYeuCau, 0) IN (
                SELECT
                    YeuCau.IOID
                FROM OYeuCauMuaSam AS YeuCau
                INNER JOIN qsiforms ON YeuCau.IFID_M412 = qsiforms.IFID
                WHERE (YeuCau.Ngay BETWEEN %1$s AND %2$s) AND qsiforms.Status IN (3, 5) %3$s %5$s
            )  AND IFNULL(DanhSach.IOID, 0) = 0
            ORDER BY RequestIOID, Ref_MaSP, NgayChungTuNhapKho ASC, SoPhieuNhapKho
            '
            , $this->_o_DB->quote($start)
            , $this->_o_DB->quote($end)
            , $where
            ,$where1
            ,$where2
        );
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getUnderMinimumItems($user, $requestIFID)
    {
        $whereInMinPartOfItemsSql = '';
        $whereInGenPartOfItemsSql = '';
        $whereInMinPartOfRetSql   = '';
        $whereInGenPartOfRetSql   = '';

        if(Qss_Lib_System::formSecure('M601')) {
            $whereInMinPartOfItemsSql .= sprintf('
	            AND IFNULL(HanMuc.Ref_MaKho, 0) in (
	                SELECT IOID FROM ODanhSachKho
                    inner join qsrecordrights on ODanhSachKho.IFID_M601 = qsrecordrights.IFID
                    WHERE UID = %1$d)', $user->user_id);
            $whereInMinPartOfRetSql .= sprintf('
	            AND IFNULL(HanMuc.Ref_MaKho, 0) in (
	                SELECT IOID FROM ODanhSachKho
                    inner join qsrecordrights on ODanhSachKho.IFID_M601 = qsrecordrights.IFID
                    WHERE UID = %1$d)
            ', $user->user_id);
        }

        if(Qss_Lib_System::fieldActive('OSanPham', 'MaTam')) {
            $whereInMinPartOfItemsSql .= sprintf(' AND IFNULL(MatHang.MaTam, 0) = 0 ');
            $whereInGenPartOfItemsSql .= sprintf(' AND IFNULL(MatHang.MaTam, 0) = 0 ');
            $whereInMinPartOfRetSql   .= sprintf(' AND IFNULL(MatHang.MaTam, 0) = 0 ');
            $whereInGenPartOfRetSql   .= sprintf(' AND IFNULL(MatHang.MaTam, 0) = 0 ');
        }

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
                LEFT JOIN ODanhSachKho AS DSKho ON Kho.Ref_Kho = DSKho.IOID
                LEFT JOIN ODonViTinhSP AS DonViTinh ON MatHang.IFID_M113 = DonViTinh.IFID_M113 
                    AND Kho.Ref_DonViTinh = DonViTinh.IOID
                WHERE 1=1 AND IFNULL(DSKho.LoaiKho, "") NOT IN ("PHELIEU", "TAM") %1$s
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
                LEFT JOIN ODanhSachKho AS DSKho ON Kho.Ref_Kho = DSKho.IOID
                LEFT JOIN ODonViTinhSP AS DonViTinh ON MatHang.IFID_M113 = DonViTinh.IFID_M113 
                    AND Kho.Ref_DonViTinh = DonViTinh.IOID
                WHERE 1=1 AND IFNULL(DSKho.LoaiKho, "") NOT IN ("PHELIEU", "TAM") %2$s
                GROUP BY MatHang.IOID
                HAVING ToiThieu > TongTonKho AND ToiThieu != 0    
            ) AS HanMucLuuTru   
        ', $whereInMinPartOfItemsSql, $whereInGenPartOfItemsSql);

        $dataItems = $this->_o_DB->fetchAll($sqlItems);
        $items     = array(0);

        if($dataItems) {
            foreach($dataItems as $item) {
                $items[] = $item->IOID;
            }
        }

        $sql = sprintf('
            SELECT
                YeuCauVatTu.*
                , IFNULL(YeuCauMuaSam.TongYeuCauConLai, 0) AS TongYeuCauMua
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
                    LEFT JOIN ODanhSachKho AS DSKho ON Kho.Ref_Kho = DSKho.IOID
                    LEFT JOIN ODonViTinhSP AS DonViTinh ON MatHang.IFID_M113 = DonViTinh.IFID_M113 
                        AND Kho.Ref_DonViTinh = DonViTinh.IOID
                    WHERE 1=1 AND IFNULL(DSKho.LoaiKho, "") NOT IN ("PHELIEU", "TAM") %3$s
                    GROUP BY HanMuc.Ref_MaKho, MatHang.IOID
                    HAVING ToiThieu > TongTonKho AND ToiThieu != 0
            
                    UNION ALL
                    
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
                    LEFT JOIN ODanhSachKho AS DSKho ON Kho.Ref_Kho = DSKho.IOID
                    LEFT JOIN ODonViTinhSP AS DonViTinh ON MatHang.IFID_M113 = DonViTinh.IFID_M113 
                        AND Kho.Ref_DonViTinh = DonViTinh.IOID
                    WHERE 1=1 AND IFNULL(DSKho.LoaiKho, "") NOT IN ("PHELIEU", "TAM") %4$s
                    GROUP BY MatHang.IOID
                    HAVING ToiThieu > TongTonKho AND ToiThieu != 0    
                ) AS HanMucLuuTru                
            ) AS YeuCauVatTu 

            LEFT JOIN
            (
                SELECT 
                    YeuCauKhac.Ref_MaSP
                    , SUM(IFNULL(YeuCauKhac.TongYeuCauMua, 0) - IFNULL(NhapKho.TongNhapKho, 0)) AS TongYeuCauConLai
                FROM
                (
                    SELECT
                        DanhSach.Ref_MaSP                        
                        , YeuCauMS.IOID AS Ref_SoYeuCau
                        , SUM(IFNULL(DanhSach.SoLuong, 0) * IFNULL(DonViTinh.HeSoQuyDoi, 0)) AS TongYeuCauMua
                    FROM OYeuCauMuaSam AS YeuCauMS
                    INNER JOIN ODSYeuCauMuaSam AS DanhSach ON YeuCauMS.IFID_M412 = DanhSach.IFID_M412
                    INNER JOIN qsiforms AS iform ON YeuCauMS.IFID_M412 = iform.IFID
                    INNER JOIN ODonViTinhSP AS DonViTinh ON DanhSach.Ref_DonViTinh = DonViTinh.IOID                                                                                                                                               
                    WHERE DanhSach.Ref_MaSP IN (%1$s) AND iform.Status IN (3, 5) AND YeuCauMS.IFID_M412 != %2$d
                    GROUP BY YeuCauMS.IOID, DanhSach.Ref_MaSP  
                ) AS YeuCauKhac
                LEFT JOIN
                (
                    SELECT
                        DanhSach.Ref_MaSanPham AS Ref_MaSP
                        , DanhSach.Ref_SoYeuCau
                        , SUM(IFNULL(DanhSach.SoLuong, 0) * IFNULL(DonViTinh.HeSoQuyDoi, 0)) AS TongNhapKho
                    FROM ONhapKho AS NhapKho
                    INNER JOIN ODanhSachNhapKho AS DanhSach ON NhapKho.IFID_M402 = DanhSach.IFID_M402
                    INNER JOIN OYeuCauMuaSam AS YeuCauMS ON IFNULL(DanhSach.Ref_SoYeuCau, 0) = YeuCauMS.IOID
                    INNER JOIN qsiforms AS iform ON NhapKho.IFID_M402 = iform.IFID
                    INNER JOIN ODonViTinhSP AS DonViTinh ON DanhSach.Ref_DonViTinh = DonViTinh.IOID
                    WHERE DanhSach.Ref_MaSanPham IN (%1$s) AND iform.Status = 2 AND YeuCauMS.IFID_M412 != %2$d
                    GROUP BY DanhSach.Ref_SoYeuCau, DanhSach.Ref_MaSanPham
                ) AS NhapKho ON YeuCauKhac.Ref_MaSP = NhapKho.Ref_MaSP AND YeuCauKhac.Ref_SoYeuCau = NhapKho.Ref_SoYeuCau 
                GROUP BY YeuCauKhac.Ref_MaSP
            ) AS YeuCauMuaSam ON YeuCauVatTu.IOID = YeuCauMuaSam.Ref_MaSP                
        ', implode(',', $items), $requestIFID, $whereInMinPartOfRetSql, $whereInGenPartOfRetSql);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getInventoryOfItems($itemCodeOrName, $requestIFID)
    {
        $arrItems = array(0);

        if($itemCodeOrName)
        {
            $sqlItems = sprintf('
                SELECT *  
                FROM OSanPham AS MatHang
                WHERE MaSanPham like "%%%1$s%%" OR TenSanPham like "%%%1$s%%"  
                LIMIT 100
            ', $itemCodeOrName);

            $datItems = $this->_o_DB->fetchAll($sqlItems);

            foreach ($datItems as $item)
            {
                $arrItems[] = $item->IOID;
            }
        }

        $sql = sprintf('
            SELECT 
                OSanPham.IOID
                , OSanPham.MaSanPham
                , OSanPham.TenSanPham
                , ODonViTinhSP.IOID AS Ref_DonViTinh
                , ODonViTinhSP.DonViTinh
                , IFNULL(OKho.SoLuongHC, 0) AS SoLuongHC
                , IFNULL(OYeuCauKhac.TongYeuCauConLai, 0) AS TongYeuCauMua             
            FROM OSanPham
            INNER JOIN ODonViTinhSP ON OSanPham.IFID_M113 = ODonViTinhSP.IFID_M113 AND OSanPham.Ref_DonViTinh = ODonViTinhSP.Ref_DonViTinh
            LEFT JOIN (
                SELECT 
                  SUM(IF( IFNULL(ODanhSachKho.IOID, 0) != 0, IFNULL(OKho.SoLuongHC,0) * IFNULL(ODonViTinhSP.HeSoQuyDoi, 0), 0)) AS  SoLuongHC
                  , OSanPham.IOID AS Ref_MaSanPham
				FROM OKho
                INNER JOIN ODanhSachKho ON OKho.Ref_Kho = ODanhSachKho.IOID AND ODanhSachKho.LoaiKho = %1$s
				INNER JOIN OSanPham ON  OSanPham.IOID = OKho.Ref_MaSP 
				INNER JOIN ODonViTinhSP ON OSanPham.IFID_M113 = ODonViTinhSP.IFID_M113 AND ODonViTinhSP.IOID = OKho.Ref_DonViTinh
				WHERE OSanPham.IOID IN (%2$s) AND IFNULL(ODanhSachKho.LoaiKho, "") NOT IN ("PHELIEU", "TAM")
				GROUP BY OKho.Ref_MaSP            
            ) AS OKho ON OSanPham.IOID = OKho.Ref_MaSanPham
            LEFT JOIN (
                SELECT 
                    YeuCauKhac.Ref_MaSP
                    , SUM(IFNULL(YeuCauKhac.TongYeuCauMua, 0) - IFNULL(NhapKho.TongNhapKho, 0)) AS TongYeuCauConLai
                FROM
                (
                    SELECT
                        DanhSach.Ref_MaSP                        
                        , YeuCauMS.IOID AS Ref_SoYeuCau
                        , SUM(IFNULL(DanhSach.SoLuong, 0) * IFNULL(DonViTinh.HeSoQuyDoi, 0)) AS TongYeuCauMua
                    FROM OYeuCauMuaSam AS YeuCauMS
                    INNER JOIN ODSYeuCauMuaSam AS DanhSach ON YeuCauMS.IFID_M412 = DanhSach.IFID_M412
                    INNER JOIN qsiforms AS iform ON YeuCauMS.IFID_M412 = iform.IFID
                    INNER JOIN ODonViTinhSP AS DonViTinh ON DanhSach.Ref_DonViTinh = DonViTinh.IOID                                                                                                                                               
                    WHERE DanhSach.Ref_MaSP IN (%2$s) AND iform.Status IN (3, 5) AND YeuCauMS.IFID_M412 != %3$d
                    GROUP BY YeuCauMS.IOID, DanhSach.Ref_MaSP 
                ) AS YeuCauKhac
                LEFT JOIN
                (
                    SELECT
                        DanhSach.Ref_MaSanPham AS Ref_MaSP
                        , DanhSach.Ref_SoYeuCau
                        , SUM(IFNULL(DanhSach.SoLuong, 0) * IFNULL(DonViTinh.HeSoQuyDoi, 0)) AS TongNhapKho
                    FROM ONhapKho AS NhapKho
                    INNER JOIN ODanhSachNhapKho AS DanhSach ON NhapKho.IFID_M402 = DanhSach.IFID_M402
                    INNER JOIN OYeuCauMuaSam AS YeuCauMS ON IFNULL(DanhSach.Ref_SoYeuCau, 0) = YeuCauMS.IOID
                    INNER JOIN qsiforms AS iform ON NhapKho.IFID_M402 = iform.IFID
                    INNER JOIN ODonViTinhSP AS DonViTinh ON DanhSach.Ref_DonViTinh = DonViTinh.IOID
                    WHERE DanhSach.Ref_MaSanPham IN (%2$s) AND iform.Status = 2 AND YeuCauMS.IFID_M412 != %3$d
                    GROUP BY DanhSach.Ref_SoYeuCau, DanhSach.Ref_MaSanPham
                ) AS NhapKho ON YeuCauKhac.Ref_MaSP = NhapKho.Ref_MaSP AND YeuCauKhac.Ref_SoYeuCau = NhapKho.Ref_SoYeuCau    
                GROUP BY YeuCauKhac.Ref_MaSP
            ) AS OYeuCauKhac ON OSanPham.IOID = IFNULL(OYeuCauKhac.Ref_MaSP, 0) 
            WHERE OSanPham.IOID IN (%2$s)
        ', $this->_o_DB->quote(Qss_Lib_Extra_Const::WAREHOUSE_TYPE_MATERIAL), implode(',', $arrItems), $requestIFID);


        if(Qss_Lib_System::fieldActive('OSanPham', 'MaTam'))
        {
            $sql .= sprintf(' AND IFNULL(MaTam, 0) = 0 ');
        }

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }
}