<?php
/**
 * Yeu cau mua sam
 *
 */
class Qss_Model_Purchase_Agreement extends Qss_Model_Abstract
{
    public function __construct ()
    {
        parent::__construct();
    }

    /**
     * Lấy hợp đồng nguyên tắc của các mặt hàng có trong các yêu cầu của một phiên xử lý mua hàng
     * @param unknown $sessionIFID
     * @param unknown $planIOID
     */
    public function getAgreementByRequestsOfSession($sessionIFID)
    {
        $sql = sprintf('
            SELECT 
                t1.*,
                IF(t1.ConLai > 0,  t1.ConLai, 0) AS ConLai
            FROM
            (
                
                SELECT 
                    hopdongnguyentac.*
                    , ifnull(kehoachmuahang.SoLuongKeHoach, 0) AS SoLuongKeHoach
                    , ifnull(donmuahang.SoLuongDatHang, 0) AS SoLuongDatHang
                    , (ifnull(hopdongnguyentac.SoLuong, 0) - ifnull(donmuahang.SoLuongDatHang, 0)) AS ConLai            
                FROM
                (
                    SELECT
                        nguyentac.Ref_NCC
                        , nguyentac.MaNCC
                        , nguyentac.NCC
                        , nguyentac.SoHieu
                        , nguyentac.Ref_NCC AS PartnerIOID
                        , yeucau.IOID AS RequestIOID
                        , cungcap.Ref_MaSanPham AS ItemIOID
                        , cungcap.Ref_DonViTinh AS UomIOID
                        , yeucau.SoPhieu
                        , cungcap.MaSanPham
                        , cungcap.SanPham
                        , cungcap.DonViTinh
                        , ifnull(danhsach.SoLuong, 0) AS SoLuong
                        , cungcap.DonGia
                        , nguyentac.IFID_M407 AS ContractIFID
                        , nguyentac.LoaiTien 
                    FROM OHopDongMuaHang AS nguyentac
                    INNER JOIN OMatHangCungCap AS cungcap ON nguyentac.IFID_M407 = cungcap.IFID_M407
                    INNER JOIN ODSYeuCauMuaSam AS danhsach ON 
                        ifnull(danhsach.Ref_MaSP, 0) = ifnull(cungcap.Ref_MaSanPham, 0)
                        AND ifnull(danhsach.Ref_DonViTinh, 0) = ifnull(cungcap.Ref_DonViTinh, 0)
                    INNER JOIN OYeuCauMuaSam AS yeucau ON danhsach.IFID_M412 = yeucau.IFID_M412
                    INNER JOIN OYeuCauPhienXLMH AS yeucauphien ON yeucau.IOID = yeucauphien.Ref_SoPhieu
                    INNER JOIN OPhienXuLyMuaHang AS phien ON yeucauphien.IFID_M415 = phien.IFID_M415
                    WHERE 
                        (
                            %1$s >= nguyentac.NgayBatDau
                            AND (
                                ifnull(nguyentac.NgayKetThuc, \'\') = \'\'
                                OR ifnull(nguyentac.NgayKetThuc, \'\') = \'0000-00-00\'
                                OR ifnull(nguyentac.NgayKetThuc, \'\') >= %1$s
                            )
                        )
                        AND phien.IFID_M415 = %2$d
                    ORDER BY nguyentac.Ref_NCC, yeucau.IOID, cungcap.MaSanPham, cungcap.DonViTinh
                ) AS hopdongnguyentac
                
                /*Ke hoach theo phien xu ly*/
                LEFT JOIN
                (
                    SELECT
                        ifnull(kehoach.IOID, 0) AS PlanIOID
                        , ifnull(chitiet.Ref_SoYeuCau, 0) AS RequestIOID
                        , ifnull(chitiet.Ref_MaSP, 0) AS ItemIOID
                        , ifnull(chitiet.Ref_DonViTinh, 0) AS UomIOID
                        , ifnull(chitiet.SoLuongYeuCau, 0) AS SoLuongKeHoach
                    FROM OKeHoachMuaSam AS kehoach
                    INNER JOIN ODSKeHoachMuaSam AS chitiet ON kehoach.IFID_M716 = chitiet.IFID_M716
                    WHERE kehoach.IOID in(
                        SELECT Ref_SoKeHoach FROM OPhienXuLyMuaHang WHERE IFID_M415 = %2$d
                    )
                ) AS kehoachmuahang ON hopdongnguyentac.RequestIOID = kehoachmuahang.RequestIOID 
                    AND hopdongnguyentac.ItemIOID = kehoachmuahang.ItemIOID
                    AND hopdongnguyentac.UomIOID = kehoachmuahang.UomIOID
                
                LEFT JOIN
                (
                    SELECT
                        ifnull(yeucau.IOID, 0) AS RequestIOID
                        , ifnull(danhsach.Ref_MaSP, 0) AS ItemIOID
                        , ifnull(danhsach.Ref_DonViTinh, 0) AS UomIOID     
                        , sum(ifnull(danhsach.SoLuong, 0)) AS SoLuongDatHang                           
                    FROM ODonMuaHang AS donhang
                    INNER JOIN ODSDonMuaHang AS danhsach ON donhang.IFID_M401 = danhsach.IFID_M401
                    INNER JOIN OYeuCauPhienXLMH AS yeucaucuaphien ON danhsach.Ref_SoYeuCau = yeucaucuaphien.Ref_SoPhieu
                    INNER JOIN OYeuCauMuaSam AS yeucau ON yeucaucuaphien.Ref_SoPhieu = yeucau.IOID
                    WHERE yeucaucuaphien.IFID_M415 = %2$d
                    GROUP BY yeucau.IOID, danhsach.Ref_MaSP, danhsach.Ref_DonViTinh
                    ORDER BY yeucau.IOID, danhsach.Ref_MaSP, danhsach.Ref_DonViTinh
                ) AS donmuahang ON hopdongnguyentac.RequestIOID = donmuahang.RequestIOID 
                    AND hopdongnguyentac.ItemIOID = donmuahang.ItemIOID
                    AND hopdongnguyentac.UomIOID = donmuahang.UomIOID      
            ) AS t1
            ORDER BY t1.Ref_NCC, t1.RequestIOID, t1.MaSanPham, t1.DonViTinh
        ', $this->_o_DB->quote(date('Y-m-d')), $sessionIFID);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }    
    
}
?>