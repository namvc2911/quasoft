<?php

/**
 * Ke hoach mua sam
 *
 */
class Qss_Model_Purchase_Plan extends Qss_Model_Abstract
{
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Lấy danh sách vật tư của kể hoạch
     * @param int $planIOID
     * @return mixed
     */
    public function getPlanItems($planIOID = 0)
    {
        $sql = sprintf('
            SELECT
                SubObject1.*
            FROM OKeHoachMuaSam AS MainObject
            INNER JOIN ODSKeHoachMuaSam AS SubObject1 ON MainObject.IFID_M716 = SubObject1.IFID_M716
            LEFT JOIN OYeuCauMuaSam AS SubObject2 ON SubObject1.Ref_SoYeuCau = SubObject2.IOID
            WHERE MainObject.IOID = %1$d
            ORDER BY SubObject2.NgayPheDuyet, SubObject1.Ref_SoYeuCau, SubObject1.MaSP
        ', $planIOID);
        //echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);

    }

    public function getPlanByIOID($ioid)
    {
        $sql = sprintf('
            SELECT kehoach.*, qsiforms.Status
            FROM OKeHoachMuaSam AS kehoach
            INNER JOIN qsiforms ON kehoach.IFID_M716 = qsiforms.IFID
            WHERE kehoach.IOID = %1$d
        ', $ioid);
        return $this->_o_DB->fetchOne($sql);
    }

    public function getPlanByIOIDWithEmployeeInfo($ioid)
    {
        $sql = sprintf('
            SELECT kehoach.*, qsiforms.Status, NhanVien.TenNhanVien, NhanVien.ChucDanh, NhanVien.GioiTinh
            FROM OKeHoachMuaSam AS kehoach
            INNER JOIN qsiforms ON kehoach.IFID_M716 = qsiforms.IFID
            LEFT JOIN ODanhSachNhanVien AS NhanVien ON IFNULL(kehoach.Ref_NguoiNhan, 0) = NhanVien.IOID
            WHERE kehoach.IOID = %1$d
        ', $ioid);
        return $this->_o_DB->fetchOne($sql);
    }

    public function getCurrencyList($ioid)
    {
        $sql = sprintf('
            SELECT TyGia.*
            FROM OKeHoachMuaSam AS KeHoach
            INNER JOIN OTyGiaKeHoachMua AS TyGia ON KeHoach.IFID_M716 = TyGia.IFID_M716
            WHERE KeHoach.IOID = %1$d
        ', $ioid);
        return $this->_o_DB->fetchAll($sql);
    }
    
    public function getPlanByIFID($ifid)
    {
        $sql = sprintf('
            SELECT kehoach.*, qsiforms.Status
            FROM OKeHoachMuaSam AS kehoach
            INNER JOIN qsiforms ON kehoach.IFID_M716 = qsiforms.IFID
            WHERE kehoach.IFID_M716 = %1$d
        ', $ifid);
        return $this->_o_DB->fetchOne($sql);
    }    
    
    public function getPlanBySession($sessionIFID)
    {
        $sql = sprintf('
            SELECT kehoach.*, qsworkflowsteps.*, qsiforms.Status, kehoach.IOID AS PlanIOID
            FROM OKeHoachMuaSam AS kehoach
            INNER JOIN qsiforms ON kehoach.IFID_M716 = qsiforms.IFID
            INNER JOIN OPhienXuLyMuaHang AS phien ON kehoach.IOID = phien.Ref_SoKeHoach
            INNER JOIN qsworkflows ON qsiforms.FormCode = qsworkflows.FormCode AND qsworkflows.Actived = 1
            INNER JOIN qsworkflowsteps ON qsworkflows.WFID = qsworkflowsteps.WFID AND qsiforms.Status = qsworkflowsteps.StepNo
            WHERE phien.IFID_M415 = %1$d
        ', $sessionIFID);
        //echo '<Pre>'; print_r($sql); die;
        return $this->_o_DB->fetchOne($sql);        
    }
    
    public function getPlanDetailBySession($sessionIFID)
    {
        $sql = sprintf('
            SELECT danhsach.*
            FROM OKeHoachMuaSam AS kehoach
            INNER JOIN OPhienXuLyMuaHang AS phien ON kehoach.IOID = phien.Ref_SoKeHoach
            INNER JOIN ODSKeHoachMuaSam AS danhsach ON kehoach.IFID_M716 = danhsach.IFID_M716
            WHERE phien.IFID_M415 = %1$d
        ', $sessionIFID);
        
        //echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * Lấy các yêu cầu mua sắm theo kế hoạch
     * @param $planioid
     * @return mixed
     */
    public function getRequiresOfPlan($planioid)
    {
        $sql = sprintf('
            SELECT
                SubObject2.*
            FROM OKeHoachMuaSam AS MainObject
            INNER JOIN ODSKeHoachMuaSam AS SubObject1 ON MainObject.IFID_M716 = SubObject1.IFID_M716
            LEFT JOIN OYeuCauMuaSam AS SubObject2 ON SubObject1.Ref_SoYeuCau = SubObject2.IOID
            WHERE MainObject.IOID = %1$d
        ', $planioid);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function countQuotationsForPlan($planIOID)
    {
        $sql = sprintf('
            SELECT count(1) AS TongSo
            FROM OBaoGiaMuaHang AS BaoGia1
            WHERE IFNULL(BaoGia1.Ref_SoKeHoach, 0) = %1$d
            GROUP BY BaoGia1.IFID_M406
        ', $planIOID);

        $dataSql = $this->_o_DB->fetchOne($sql);
        return $dataSql?$dataSql->TongSo:0;
    }

    public function countSuppliersForPlan($planIOID)
    {
        $sql = sprintf('
            SELECT count(1) AS TongSo
            FROM OBaoGiaMuaHang AS BaoGia1
            WHERE IFNULL(BaoGia1.Ref_SoKeHoach, 0) = %1$d
            GROUP BY BaoGia1.Ref_MaNCC
        ', $planIOID);

        $dataSql = $this->_o_DB->fetchOne($sql);
        return $dataSql?$dataSql->TongSo:0;
    }

    public function getSuppliersForPlan($planIOID)
    {
        $sql = sprintf('
            SELECT
                BaoGia.*
                , DoiTac.IOID AS Ref_DoiTac
                , DoiTac.MaDoiTac
                , DoiTac.TenDoiTac
                , DoiTac.DiaChi
                , DoiTac.DienThoai
                , DoiTac.Fax
                , IF(SoBaoGiaKhongHopLe > 0 AND SoBaoGiaHopLe = 0, 1, 0) AS NhaCungCapKhongHopLe
            FROM
            (
                SELECT  * FROM OKeHoachMuaSam WHERE IOID = %1$d
            ) AS KeHoachMuaSam

            INNER JOIN (
                SELECT
                  BaoGia2.*
                  , sum(case when IFNULL(BaoGia2.KhongHopLe, 0) = 0 then 1 else 0 end) AS SoBaoGiaHopLe
                  , sum(case when IFNULL(BaoGia2.KhongHopLe, 0) != 0 then 1 else 0 end) AS SoBaoGiaKhongHopLe
                FROM
                (
                    SELECT BaoGia1.*
                    FROM OBaoGiaMuaHang AS BaoGia1
                    WHERE IFNULL(BaoGia1.Ref_SoKeHoach, 0) = %1$d
                    ORDER BY  ifnull(BaoGia1.Ref_MaNCC, 0) DESC
                        , IF(ifnull(BaoGia1.NgayBaoGia, \'\') != \'\'
                        AND ifnull(BaoGia1.NgayBaoGia, \'\') != \'0000-00-00\', BaoGia1.NgayBaoGia,  \'\') DESC
                        , ifnull(BaoGia1.IFID_M406, 0) DESC
                ) AS BaoGia2
                GROUP BY BaoGia2.Ref_MaNCC
            ) AS BaoGia ON KeHoachMuaSam.IOID = BaoGia.Ref_SoKeHoach

            INNER JOIN ODoiTac AS DoiTac ON BaoGia.Ref_MaNCC = DoiTac.IOID

        ', $planIOID);



        return $this->_o_DB->fetchAll($sql);
    }
}
?>