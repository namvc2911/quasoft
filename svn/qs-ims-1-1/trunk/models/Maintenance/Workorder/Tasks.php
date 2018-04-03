<?php
/**
 * Class Qss_Model_Maintenance_Workorder
 * Danh muc vat tu phieu bao tri
 */
class Qss_Model_Maintenance_Workorder_Tasks extends Qss_Model_Abstract
{
    public function getTasksByEmployee(
        $start
        , $end
        , $refEmployee
        , $eqIOID = 0
        , $shift = 0
    )
    {
        $where  = '';
        $where .= ($eqIOID)?sprintf(' AND thietbi.IOID = %1$d  ', $eqIOID):'';
        $where1 = ''; // Loc rieng cho cau sql dau tien, lay cong viec theo nhan vien

        if($shift)
        {
            $oShift = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OCa WHERE IOID =%1$d ', $shift));

            if($oShift)
            {
                if(!$oShift->GioBatDau || !$oShift->GioKetThuc) // Ca ko lam viec, ko co gio bat dau hoac ket thuc
                {
                    $where .= ' AND 1=0 '; // Không chọn ra bất cứ cái gì
                }
                else
                {
                    $where .= sprintf(' AND (IFNULL(CongViec.GioBatDau, \'\') = \'\' OR (CongViec.GioBatDau BETWEEN %1$s AND %2$s)) '
                        , $this->_o_DB->quote($oShift->GioBatDau)
                        , $this->_o_DB->quote($oShift->GioKetThuc));
                }
            }
        }

        $sql = sprintf('

            SELECT
                CongViec.*
                , CongViec.IOID AS RefCongViec
                , PhieuBaoTri.NgayBatDauDuKien AS NgayBatDau
                , PhieuBaoTri.NgayDuKienHoanThanh AS NgayBatDauDuKien
                , PhieuBaoTri.IFID_M759
                , PhieuBaoTri.SoPhieu
                , PhieuBaoTri.MaThietBi
                , IFNULL(PhieuBaoTri.Ref_ChuKy, 0) AS CoKeHoach
                , PhieuBaoTri.TenThietBi
                , PhieuBaoTri.LoaiBaoTri
                
                
                
                , GROUP_CONCAT( IF(VatTu.IOID, CONCAT(VatTu.MaVatTu, ": " , VatTu.SoLuong, " ", VatTu.DonViTinh), "") SEPARATOR "<br/>") AS VatTuThayThe
                , (
                    select group_concat(concat(Vitri," - ",BoPhan) SEPARATOR "<br>")
                    from OCauTrucThietBi as t
                    where
                        t.IFID_M705 = CauTruc.IFID_M705
                        and t.lft < ifnull(CauTruc.lft,0)
                        and t.rgt > ifnull(CauTruc.rgt,0)
                    order by t.lft
                    limit 1
                ) as BoPhanCha
            FROM OCongViecBTPBT AS CongViec
            INNER JOIN OPhieuBaoTri AS PhieuBaoTri ON CongViec.IFID_M759 = PhieuBaoTri.IFID_M759
            LEFT JOIN OCauTrucThietBi as CauTruc on IFNULL(CongViec.Ref_ViTri, 0) = CauTruc.IOID
            LEFT JOIN OVatTuPBT AS VatTu ON PhieuBaoTri.IFID_M759 = VatTu.IFID_M759
                AND CongViec.IOID = IFNULL(VatTu.Ref_CongViec, 0)
            WHERE (PhieuBaoTri.NgayBatDauDuKien >= %1$s and PhieuBaoTri.NgayBatDauDuKien <= %2$s)
                AND IFNULL(PhieuBaoTri.Ref_NguoiThucHien, 0) = %3$d
                
                %4$s
                %5$s
            GROUP BY CongViec.IOID
            '
            , $this->_o_DB->quote($start)
            , $this->_o_DB->quote($end)
            , $refEmployee
            , $where
            , $where1);

        // echo '<Pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getTasks(
        $start
        , $end
        , $refEmployee
        , $eqIOID = 0
        , $shift = 0
    )
    {
        $where  = '';
        $where .= ($eqIOID)?sprintf(' AND thietbi.IOID = %1$d  ', $eqIOID):'';
        $where1 = ''; // Loc rieng cho cau sql dau tien, lay cong viec theo nhan vien

        if($shift)
        {
            $oShift = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OCa WHERE IOID =%1$d ', $shift));

            if($oShift)
            {
                if(!$oShift->GioBatDau || !$oShift->GioKetThuc) // Ca ko lam viec, ko co gio bat dau hoac ket thuc
                {
                    $where .= ' AND 1=0 '; // Không chọn ra bất cứ cái gì
                }
                else
                {
                    $where .= sprintf(' AND (IFNULL(CongViec.ThoiGianBatDauDuKien, \'\') = \'\' OR (CongViec.ThoiGianBatDauDuKien BETWEEN %1$s AND %2$s)) '
                        , $this->_o_DB->quote($oShift->GioBatDau)
                        , $this->_o_DB->quote($oShift->GioKetThuc));
                }
            }
        }

        $sql = sprintf('
            SELECT
                PhieuBaoTri.IFID_M759
                , IF(IFNULL(PhieuBaoTri.Ref_ChuKy, 0) != 0, 1, 0) AS CoKeHoach
                , PhieuBaoTri.MaThietBi
                , PhieuBaoTri.MoTa AS TenCongViecPhieu
                , PhieuBaoTri.NgayBatDauDuKien AS NgayBatDauDuKienPhieu
                , PhieuBaoTri.NgayDuKienHoanThanh AS NgayHoanThanhDuKienPhieu
                , PhieuBaoTri.ThoiGianBatDauDuKien AS ThoiGianBatDauDuKienPhieu
                , PhieuBaoTri.ThoiGianKetThucDuKien AS ThoiGianKetThucDuKienPhieu
                , PhieuBaoTri.NgayBatDau AS NgayBatDauPhieu
                , PhieuBaoTri.Ngay AS NgayHoanThanhPhieu
                , PhieuBaoTri.GioBatDau AS ThoiGianBatDauPhieu
                , PhieuBaoTri.GioKetThuc AS ThoiGianKetThucPhieu
                , CongViec.IOID AS RefCongViec
                , CongViec.MoTa AS TenCongViec
                , CongViec.NgayDuKien
                , CongViec.ThoiGianBatDauDuKien AS ThoiGianBatDauDuKienCongViec
                , CongViec.ThoiGianKetThucDuKien AS ThoiGianKetThucDuKienCongViec
                , CongViec.Ngay AS NgayCongViec
                , CongViec.GioBatDau AS ThoiGianBatDauCongViec
                , CongViec.GioKetThuc AS ThoiGianKetThucCongViec
            FROM OPhieuBaoTri AS PhieuBaoTri
            LEFT JOIN OCongViecBTPBT AS CongViec ON CongViec.IFID_M759 = PhieuBaoTri.IFID_M759
            WHERE (
                (CongViec.NgayDuKien >= %1$s and CongViec.NgayDuKien <= %2$s)
                OR (PhieuBaoTri.NgayBatDauDuKien >= %1$s and PhieuBaoTri.NgayBatDauDuKien <= %2$s)
            )
            AND (
                IFNULL(CongViec.Ref_NguoiThucHien, 0) = %3$d
                OR
                (IFNULL(CongViec.Ref_NguoiThucHien, 0) = 0 AND IFNULL(PhieuBaoTri.Ref_NguoiThucHien, 0) = %3$d)
            )
            ORDER BY PhieuBaoTri.IFID_M759, CongViec.IOID        
        '
        , $this->_o_DB->quote($start)
        , $this->_o_DB->quote($end)
        , $refEmployee
        , $where
        , $where1);
        // echo '<Pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }
}