<?php 

class Qss_Model_Purchase_Quotation extends Qss_Model_Abstract
{
    public function __construct ()
    {
        parent::__construct();
        $this->_user = Qss_Register::get('userinfo');
    }

    public function getQuotationCompareList($planIOID = 0)
    {
        $sql = sprintf('
            SELECT
                BaoGia.*,
                ifnull(DonMuaHang.SoLuongDatHang, 0) AS Ordered
            FROM (
            
                SELECT *
                FROM 
                (
                    SELECT
                        baogia.IOID AS QuotationIOID
                        , baogia.Ref_SoKeHoach AS PlanIOID
                        , baogia.Ref_MaNCC AS PartnerIOID
                        , baogia.SoChungTu AS DocNo
                        , baogia.MaNCC AS PartnerCode
                        , baogia.TenNCC AS PartnerName
                        , baogia.ThoiGianGiaoHang AS DeliveryTime
                        , baogia.SoKeHoach AS PlanNo
        
                        , danhsach.Ref_MaSP AS ItemIOID
                        , danhsach.Ref_DonViTinh AS UomIOID
                        , danhsach.MaSP AS ItemCode
                        , danhsach.TenSP AS ItemName
                        , danhsach.DonViTinh AS UOM
                        , ifnull(danhsach.DonGia, 0) AS UnitPrice
                        , ifnull(danhsach.SoLuong, 0) AS Qty
                        , ifnull(dskehoach.SoLuongYeuCau, 0) AS Planed
                        , danhsach.ThanhTien AS TotalAmount
                        , ifnull(danhsach.KyThuat, 0) AS Pass
                        , ifnull(danhsach.ChatLuong, 0) AS Quality
                        , ifnull(dskehoach.Ref_SoYeuCau, 0) AS RequestIOID
                        , dskehoach.SoYeuCau AS RequestNo
                        , ifnull(baogia.NgayBaoGia, \'\')  AS QuoteDate
                        , ifnull(baogia.NgayYeuCau, \'\') AS ReqDate
                        , baogia.DiaDiemGiaoHang
                        , baogia.HinhThucThanhToan
                        , baogia.ThoiGianGiaoHang
                        , baogia.ThoiGianBaoHanh
        
                    FROM OBaoGiaMuaHang AS baogia
                    INNER JOIN ODSBGMuaHang AS danhsach ON baogia.IFID_M406 = danhsach.IFID_M406
        
                    LEFT JOIN OKeHoachMuaSam AS kehoach ON baogia.Ref_SoKeHoach = kehoach.IOID
                    LEFT JOIN ODSKeHoachMuaSam AS dskehoach ON kehoach.IFID_M716 = dskehoach.IFID_M716
                        AND dskehoach.Ref_MaSP = danhsach.Ref_MaSP
                        AND dskehoach.Ref_DonViTinh = danhsach.Ref_DonViTinh
        
                    WHERE baogia.Ref_SoKeHoach = %1$d
                    ORDER BY 
                        baogia.Ref_MaNCC
                        , danhsach.MaSP
                        , danhsach.DonViTinh
                        , CASE WHEN ifnull(baogia.NgayBaoGia, \'\') != \'\' AND ifnull(baogia.NgayBaoGia, \'\') != \'0000-00-00\' THEN baogia.NgayBaoGia
                        WHEN ifnull(baogia.NgayYeuCau, \'\') != \'\' AND ifnull(baogia.NgayYeuCau, \'\') != \'0000-00-00\' THEN baogia.NgayYeuCau END DESC
                ) AS t1 
                GROUP BY PartnerIOID
                        , ItemCode
                        , UOM
            ) AS BaoGia
    
    
            LEFT JOIN (
                SELECT
                    ifnull(kehoach.IOID, 0) AS PlanIOID
                    , ifnull(danhsach.Ref_SoYeuCau, 0) AS RequestIOID
                    , ifnull(danhsach.Ref_MaSP, 0) AS ItemIOID
                    , ifnull(danhsach.Ref_DonViTinh, 0) AS UomIOID
                    , sum(danhsach.SoLuong) AS SoLuongDatHang
                FROM ODonMuaHang AS donhang
                INNER JOIN ODSDonMuaHang AS danhsach ON donhang.IFID_M401 = danhsach.IFID_M401
                INNER JOIN OKeHoachMuaSam AS kehoach ON donhang.Ref_SoKeHoach = kehoach.IOID
                INNER JOIN ODSKeHoachMuaSam AS danhsachkehoach ON kehoach.IFID_M716 = danhsachkehoach.IFID_M716
                    AND danhsach.Ref_MaSP = danhsachkehoach.Ref_MaSP
                    AND danhsach.Ref_DonViTinh = danhsachkehoach.Ref_DonViTinh
                    AND danhsach.Ref_SoYeuCau = danhsachkehoach.Ref_SoYeuCau
    
                WHERE kehoach.IOID = %1$d
                GROUP BY kehoach.IOID, danhsach.Ref_SoYeuCau,  danhsach.Ref_MaSP, danhsach.Ref_DonViTinh
            ) AS DonMuaHang ON BaoGia.PlanIOID = DonMuaHang.PlanIOID
                AND BaoGia.RequestIOID = DonMuaHang.RequestIOID
                AND BaoGia.ItemIOID = DonMuaHang.ItemIOID
                AND BaoGia.UomIOID = DonMuaHang.UomIOID
    
        ', $planIOID);
        
        //echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }    
    

    public function getQuotationBySession($sessionIFID)
    {
        $sql = sprintf('
            SELECT
                baogia.*, qsiforms.Status
            FROM OBaoGiaMuaHang AS baogia
            INNER JOIN qsiforms ON baogia.IFID_M406 = qsiforms.IFID
            INNER JOIN OPhienXuLyMuaHang AS phien ON baogia.Ref_SoKeHoach = phien.Ref_SoKeHoach
            WHERE phien.IFID_M415 = %1$d
        ', $sessionIFID);
        //echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }
    
    
    /**
     * Lay bao gia theo phien xu ly mua hang
     * @param unknown $sessionIFID
     */
    public function getQuotationDetailBySession($sessionIFID, $partnerIOID = 0)
    {
        $filter = $partnerIOID?sprintf(' and BaoGia.Ref_MaNCC = %1$d ',$partnerIOID):'';    
        
        $sql = sprintf('
            SELECT BaoGia.*, DanhSach.*, DanhSach.IOID AS QuoteLineIOID, BaoGia.IFID_M406
            FROM OBaoGiaMuaHang AS BaoGia
            INNER JOIN OPhienXuLyMuaHang AS Phien On BaoGia.Ref_SoKeHoach = Phien.Ref_SoKeHoach
            LEFT JOIN ODSBGMuaHang AS DanhSach ON BaoGia.IFID_M406 = DanhSach.IFID_M406
            WHERE Phien.IFID_M415 = %1$d %2$s
            ORDER BY BaoGia.Ref_MaNCC, BaoGia.IFID_M406 DESC, DanhSach.MaSP, DanhSach.DonViTinh
        ', $sessionIFID, $filter);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);        
    }
    
    public function getQuotationCurrencyBySession($sessionIFID)
    {
        $sql = sprintf('
            SELECT qscurrencies.*
            FROM OBaoGiaMuaHang AS BaoGia
            INNER JOIN OPhienXuLyMuaHang AS Phien On BaoGia.Ref_SoKeHoach = Phien.Ref_SoKeHoach
            INNER JOIN qscurrencies ON (ifnull(BaoGia.LoaiTien, \'\') = \'\' AND qscurrencies.Primary = 1)
                OR (ifnull(BaoGia.LoaiTien, \'\') = qscurrencies.Code)
                OR ifnull(BaoGia.LoaiTien, \'\') not in (SELECT Code FROM qscurrencies)
            WHERE Phien.IFID_M415 = %1$d AND ifnull(qscurrencies.Primary, 0) != 1
            GROUP BY qscurrencies.CID
            ORDER BY ifnull(qscurrencies.Primary, 0) DESC, qscurrencies.Code
        ', $sessionIFID);
        return $this->_o_DB->fetchAll($sql);
    }

    public function getLastQuotesOfPlan($planIOID)
    {
        $sql = sprintf('
            SELECT
                DSBaoGia.*,
                DSKeHoach.Ref_MaSP,
                DSKeHoach.Ref_DonViTinh,
                IFNULL(DSKeHoach.SoLuongYeuCau, 0) AS SoLuongYeuCau,
                ifnull(BaoGia.IFID_M406, 0) AS IFID_M406,
                BaoGia.IFID_M406 AS BaoGiaIFID,
                BaoGia.IOID AS BaoGiaIOID,
                BaoGia.Ref_MaNCC,
                BaoGia.LoaiTien,
                BaoGia.SoChungTu AS SoPhieuBaoGia,
                KeHoachMuaSam.SoPhieu AS SoPhieuKeHoach,
                IFNULL(DSBaoGia.SoLuong, 0) AS SoLuongBaoGia,
                IFNULL(DSBaoGia.ThoiGian, 0) AS ThoiGianGiaoHangTungSP,
                SanPham.IFID_M113,
                ifnull(qscurrencies.CID, 0) AS CID,
                SanPham.DacTinhKyThuat,
                BaoGia.DiaDiemGiaoHang,
                IFNULL(BaoGia.ThoiGianGiaoHang, 0) AS ThoiGianGiaoHang,
                BaoGia.ThoiGianBaoHanh,
                BaoGia.HinhThucThanhToan,
                IFNULL(BaoGia.KhongHopLe, 0) AS KhongHopLe

            FROM
            (
                SELECT  * FROM OKeHoachMuaSam WHERE IOID = %1$d
            ) AS KeHoachMuaSam
            INNER JOIN ODSKeHoachMuaSam AS DSKeHoach ON KeHoachMuaSam.IFID_M716 = DSKeHoach.IFID_M716
            INNER  JOIN OSanPham AS SanPham ON DSKeHoach.Ref_MaSP = SanPham.IOID

            INNER JOIN (
                SELECT *
                FROM
                (
                    SELECT BaoGia1.*
                    FROM OBaoGiaMuaHang AS BaoGia1
                    WHERE IFNULL(BaoGia1.Ref_SoKeHoach, 0) = %1$d
                    ORDER BY  ifnull(BaoGia1.Ref_MaNCC, 0) DESC
                        , IF(ifnull(BaoGia1.NgayBaoGia, \'\') != \'\' AND ifnull(BaoGia1.NgayBaoGia, \'\') != \'0000-00-00\', BaoGia1.NgayBaoGia,  \'\') DESC
                        , ifnull(BaoGia1.IFID_M406, 0) DESC
                ) AS BaoGia2
                GROUP BY BaoGia2.Ref_MaNCC
            ) AS BaoGia ON KeHoachMuaSam.IOID = BaoGia.Ref_SoKeHoach

            LEFT JOIN ODSBGMuaHang AS DSBaoGia ON
                BaoGia.IFID_M406 = DSBaoGia.IFID_M406
                AND DSKeHoach.Ref_DonViTinh = DSBaoGia.Ref_DonViTinh
                AND DSKeHoach.Ref_MaSP = DSBaoGia.Ref_MaSP

            LEFT JOIN qscurrencies ON BaoGia.LoaiTien = qscurrencies.Code



            ORDER BY DSKeHoach.MaSP, DSKeHoach.Ref_MaSP, DSKeHoach.Ref_DonViTinh
        ', $planIOID);
        // echo '<pre>'; print_r($sql); die;

        $dataSql     = $this->_o_DB->fetchAll($sql);
        $retval      = new stdClass();
        $i           = -1;
        $tempRefItem = '';
        $tempRefUom  = '';

        foreach($dataSql as $item)
        {
            if($tempRefItem != $item->Ref_MaSP || ($tempRefItem == $item->Ref_MaSP && $tempRefUom != $item->Ref_DonViTinh))
            {
                $i++;
                $retval->{$i} = new stdClass();
                $retval->{$i} = $item;
                $retval->{$i}->ChonBaoGiaSanPham = 0;
            }

            $retval->{$i}->{'BaoGiaIFID_'.$item->Ref_MaNCC}     = $item->BaoGiaIFID;
            $retval->{$i}->{'BaoGiaIOID_'.$item->Ref_MaNCC}     = $item->BaoGiaIOID;
            $retval->{$i}->{'DonGia_'.$item->Ref_MaNCC}         = $item->DonGia;
            $retval->{$i}->{'GiaThuongLuong_'.$item->Ref_MaNCC} = $item->GiaThuongLuong;
            $retval->{$i}->{'RefKyThuat_'.$item->Ref_MaNCC}     = $item->Ref_KyThuat;
            $retval->{$i}->{'KyThuat_'.$item->Ref_MaNCC}        = $item->KyThuat;
            $retval->{$i}->{'ThanhTien_'.$item->Ref_MaNCC}      = $item->ThanhTien;
            $retval->{$i}->{'CoBaoGiaCho_'.$item->Ref_MaNCC}    = 1;
            $retval->{$i}->{'KhongHopLe_'.$item->Ref_MaNCC}     = $item->KhongHopLe;
            $retval->{$i}->{'KhongChaoGia_'.$item->Ref_MaNCC}   = (int)$item->KhongChaoGia;
            $retval->{$i}->{'SoNgayGiaoHang_'.$item->Ref_MaNCC} = $item->ThoiGianGiaoHangTungSP?$item->ThoiGianGiaoHangTungSP:$item->ThoiGianGiaoHang;
            $retval->{$i}->CoBaoGia                             = 1;

            if($item->ChonBaoGia)
            {
                $retval->{$i}->ChonBaoGiaSanPham   = $item->Ref_MaNCC;
            }


            $tempRefItem = $item->Ref_MaSP;
            $tempRefUom  = $item->Ref_DonViTinh;
        }
        // echo '<pre>'; print_r($retval); die;
        return $retval;
    }

    public function getSelectedQuotesByPlan($planIOID)
    {
        $sql = sprintf('
            SELECT DanhSach.*
            FROM OBaoGiaMuaHang AS BaoGia
            INNER JOIN ODSBGMuaHang AS DanhSach ON BaoGia.IFID_M406 = DanhSach.IFID_M406
            WHERE BaoGia.Ref_SoKeHoach = %1$d AND IFNULL(DanhSach.ChonBaoGia, 0) = 1
        ', $planIOID);
        return $this->_o_DB->fetchAll($sql);
    }
}

?>