<?php
/**
 * @author: Thinh Tuan
 * @date: 2015-07-28
 * @class: Class Qss_Model_Maintenance_Equip_Requiremove - Yêu cầu điều động thiết bị
 *
 * + F1: countEquipsInProjectsForReqMov($requireMovIFID)
 * Dem so luong thiet bi dang khong trong du an va trong du an dang hoat dong theo loai thiet bi trong yeu cau dieu dong
 */
class Qss_Model_Maintenance_Equip_Requiremove extends Qss_Model_Abstract
{
    public function __construct()
    {
        parent::__construct();
    }

    /*
    // @todo: Viet thanh cac ham lay cong cu
    public function getTotalToolsByReqToolLines($reqIFID)
    {
        $sql = sprintf('
            SELECT
                yeucautb.*,
                SUM(CASE WHEN ifnull(thietbi.IOID, 0) = 0 THEN 0 ELSE 1 END) AS TongSoThietBiTheoDong
            FROM OYeuCauCongCu AS yeucautb
            INNER JOIN OLoaiThietBi AS loaitb ON yeucautb.Ref_LoaiThietBi = loaitb.IOID
            INNER JOIN OLoaiThietBi AS loaitbcon ON loaitb.lft <= loaitbcon.lft AND loaitb.rgt >= loaitbcon.rgt
            LEFT JOIN ODanhSachThietBi AS thietbi ON loaitbcon.IOID = thietbi.Ref_LoaiThietBi
            WHERE yeucautb.IFID_M751 = %1$d
            GROUP BY yeucautb.IOID
        ', $reqIFID);
        return $this->_o_DB->fetchAll($sql);
    }

    public function getTotalToolsRequireInOtherLine($reqIFID)
    {
        $sql = sprintf('
            SELECT
                t1.IOID,
                t1.Ref_LoaiThietBi,
                t1.LoaiThietBi,
                ifnull(t1.SoLuong, 0) AS SoLuongTrenDongDangXet,
                SUM(ifnull(t2.SoLuong, 0)) AS TongSoTrenDongKhac

            FROM

            (
            SELECT yeucautb.*, loaitb.lft, loaitb.rgt
            FROM OYeuCauTrangThietBi AS yeucautb
            INNER JOIN OLoaiThietBi AS loaitb ON yeucautb.Ref_LoaiThietBi = loaitb.IOID
            WHERE yeucautb.IFID_M751 = %1$d
            ) AS t1

            LEFT JOIN

            (
            SELECT yeucautb.*, loaitb.lft, loaitb.rgt
            FROM OYeuCauTrangThietBi AS yeucautb
            INNER JOIN OLoaiThietBi AS loaitb ON yeucautb.Ref_LoaiThietBi = loaitb.IOID
            WHERE yeucautb.IFID_M751 = %1$d
            ) AS t2

            ON
              t1.IOID != t2.IOID
              AND t1.lft <= t2.lft
              AND t1.rgt >= t2.rgt
              AND t1.NgayBatDau <= t2.NgayBatDau
              AND t1.NgayKetThuc >= t2.NgayKetThuc

            GROUP BY t1.IOID
        ', $reqIFID);
        return $this->_o_DB->fetchAll($sql);
    }

    public function getTotalToolsRequireInOtherRequire($reqIFID)
    {
        $sql = sprintf('
            SELECT
                t1.IOID,
                t1.Ref_LoaiThietBi,
                t1.LoaiThietBi,
                ifnull(t1.SoLuong, 0) AS SoLuongTrenDongDangXet,
                SUM(ifnull(t2.SoLuong, 0)) AS TongSoTrenDongKhac

            FROM

            (
            SELECT yeucautb.*, loaitb.lft, loaitb.rgt
            FROM OYeuCauTrangThietBi AS yeucautb
            INNER JOIN OLoaiThietBi AS loaitb ON yeucautb.Ref_LoaiThietBi = loaitb.IOID
            INNER JOIN qsiforms ON yeucautb.IFID_M751 = qsiforms.IFID
            WHERE yeucautb.IFID_M751 = %1$d
            ) AS t1

            LEFT JOIN

            (
            SELECT yeucautb.*, loaitb.lft, loaitb.rgt
            FROM OYeuCauTrangThietBi AS yeucautb
            INNER JOIN OLoaiThietBi AS loaitb ON yeucautb.Ref_LoaiThietBi = loaitb.IOID
            INNER JOIN qsiforms ON yeucautb.IFID_M751 = qsiforms.IFID
            WHERE yeucautb.IFID_M751 != %1$d AND qsiforms.Status IN (2,3)
            ) AS t2

            ON
              t1.lft <= t2.lft
              AND t1.rgt >= t2.rgt
              AND t1.NgayBatDau <= t2.NgayBatDau
              AND t1.NgayKetThuc >= t2.NgayKetThuc

            GROUP BY t1.IOID
        ', $reqIFID);
        return $this->_o_DB->fetchAll($sql);
    }
    */

    //------------------------------------------------------------------------------------------------------------------

    public function getTotalEquipByReqEquipLine($reqIFID)
    {
        $sql = sprintf('
            SELECT
                yeucautb.*,
                SUM(CASE WHEN ifnull(thietbi.IOID, 0) = 0 THEN 0 ELSE 1 END) AS TongSoThietBiTheoDong
            FROM OYeuCauTrangThietBi AS yeucautb
            INNER JOIN OLoaiThietBi AS loaitb ON yeucautb.Ref_LoaiThietBi = loaitb.IOID
            INNER JOIN OLoaiThietBi AS loaitbcon ON loaitb.lft <= loaitbcon.lft AND loaitb.rgt >= loaitbcon.rgt
            LEFT JOIN ODanhSachThietBi AS thietbi ON loaitbcon.IOID = thietbi.Ref_LoaiThietBi
            WHERE yeucautb.IFID_M751 = %1$d
            GROUP BY yeucautb.IOID
        ', $reqIFID);
        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * Lấy tổng số lượng theo loại thiết bị (Cha con, trùng nhau) trong cùng một phiếu yêu cầu với điều kiện
     * IOID khác nhau (hai dòng khác nhau), ngày của hai bản ghi giao nhau, loại thiết bị giống nhau hoặc là con của
     * loại thiết bị của dòng đang xét
     * @param $reqIFID
     */
    public function getTotalEquipTypeRequireInOtherLine($reqIFID)
    {
        $sql = sprintf('
            SELECT
                t1.IOID,
                t1.Ref_LoaiThietBi,
                t1.LoaiThietBi,
                ifnull(t1.SoLuong, 0) AS SoLuongTrenDongDangXet,
                SUM(ifnull(t2.SoLuong, 0)) AS TongSoTrenDongKhac

            FROM

            (
            SELECT yeucautb.*, loaitb.lft, loaitb.rgt
            FROM OYeuCauTrangThietBi AS yeucautb
            INNER JOIN OLoaiThietBi AS loaitb ON yeucautb.Ref_LoaiThietBi = loaitb.IOID
            WHERE yeucautb.IFID_M751 = %1$d
            ) AS t1

            LEFT JOIN

            (
            SELECT yeucautb.*, loaitb.lft, loaitb.rgt
            FROM OYeuCauTrangThietBi AS yeucautb
            INNER JOIN OLoaiThietBi AS loaitb ON yeucautb.Ref_LoaiThietBi = loaitb.IOID
            WHERE yeucautb.IFID_M751 = %1$d
            ) AS t2

            ON
              t1.IOID != t2.IOID
              AND t1.lft <= t2.lft
              AND t1.rgt >= t2.rgt
              AND t1.NgayBatDau <= t2.NgayBatDau
              AND t1.NgayKetThuc >= t2.NgayKetThuc

            GROUP BY t1.IOID
        ', $reqIFID);
        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * Lấy tổng số loại thiết bị trong yêu cầu điều động khác với điều kiện ngày giao nhau, loại thiệt bị trùng hoặc là
     * loại thiêt bị đang xét
     * @param $reqIFID
     */
    public function getTotalEquipTypeRequireInOtherRequire($reqIFID)
    {
        $sql = sprintf('
            SELECT
                t1.IOID,
                t1.Ref_LoaiThietBi,
                t1.LoaiThietBi,
                ifnull(t1.SoLuong, 0) AS SoLuongTrenDongDangXet,
                SUM(ifnull(t2.SoLuong, 0)) AS TongSoTrenDongKhac

            FROM

            (
            SELECT yeucautb.*, loaitb.lft, loaitb.rgt
            FROM OYeuCauTrangThietBi AS yeucautb
            INNER JOIN OLoaiThietBi AS loaitb ON yeucautb.Ref_LoaiThietBi = loaitb.IOID
            INNER JOIN qsiforms ON yeucautb.IFID_M751 = qsiforms.IFID
            WHERE yeucautb.IFID_M751 = %1$d
            ) AS t1

            LEFT JOIN

            (
            SELECT yeucautb.*, loaitb.lft, loaitb.rgt
            FROM OYeuCauTrangThietBi AS yeucautb
            INNER JOIN OLoaiThietBi AS loaitb ON yeucautb.Ref_LoaiThietBi = loaitb.IOID
            INNER JOIN qsiforms ON yeucautb.IFID_M751 = qsiforms.IFID
            WHERE yeucautb.IFID_M751 != %1$d AND qsiforms.Status IN (2,3)
            ) AS t2

            ON
              t1.lft <= t2.lft
              AND t1.rgt >= t2.rgt
              AND t1.NgayBatDau <= t2.NgayBatDau
              AND t1.NgayKetThuc >= t2.NgayKetThuc

            GROUP BY t1.IOID
        ', $reqIFID);
        return $this->_o_DB->fetchAll($sql);
    }
}