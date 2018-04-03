<?php

/**
 * Class Qss_Model_Maintenance_Equip_List - Danh sach thiet bi
 * alias - ODanhSachThietBi: thietbi
 *
 */
class Qss_Model_Maintenance_Equip_List extends Qss_Model_Abstract
{
    public $filter = array();

    public function __construct()
    {
        parent::__construct();
        $this->filter[] = sprintf(' thietbi.DeptID in (%1$s) ', $this->_user->user_dept_list);
    }

    public function countEquip($deptID)
    {
        $where = ($deptID)?sprintf(' AND DeptID = %1$d ', $deptID):'';
        $sql   = sprintf('SELECT COUNT(1) AS TotalEquip FROM ODanhSachThietBi WHERE IFNULL(TrangThai, 0) = 0 %1$s', $where);
        $dat   = $this->_o_DB->fetchOne($sql);

        return $dat?$dat->TotalEquip:0;
    }

    public function getEquipParams()
    {
        $sql = sprintf('SELECT * FROM OChiSoMayMoc;');
        return $this->_o_DB->fetchAll($sql);
    }

    public function getEquipByIOID($ioid)
    {
        $sql = sprintf('
            SELECT ThietBi.*
            FROM ODanhSachThietBi AS ThietBi
            WHERE ThietBi.IOID = %1$d
        ', $ioid);
        return $this->_o_DB->fetchOne($sql);
    }

    public function getEquipsTreeByLocation($locationIOID, $parentEquip = 0)
    {
        $where    = '';
        $locName  = $locationIOID?$this->_o_DB->fetchOne(sprintf('select * from OKhuVuc where IOID = %1$d', @(int)$locationIOID)):false;
        $where   .= ($locName)
            ?sprintf(' AND (ifnull(ThietBi.Ref_MaKhuVuc, 0) IN  (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d)) ', $locName->lft, $locName->rgt)
            :' AND 1=0';

        $equipName = $parentEquip?$this->_o_DB->fetchOne(sprintf('select * from ODanhSachThietBi where IOID = %1$d', @(int)$parentEquip)):false;
        $where2    = $equipName?sprintf(' AND (ifnull(ThietBiCon.IOID, 0) IN  (select IOID from ODanhSachThietBi where lft> %1$d and  rgt < %2$d)) ', $equipName->lft, $equipName->rgt)
            :'';

        if(Qss_Lib_System::fieldActive('TrucThuoc', 'ODanhSachThietBi')) {
            $sql = sprintf('
            SELECT
                ThietBiCon.*
                , (SELECT count(*) FROM ODanhSachThietBi AS u WHERE u.lft <= ThietBiCon.lft AND u.rgt >= ThietBiCon.rgt) AS Level
            FROM
            (
                SELECT ThietBi.*
                FROM ODanhSachThietBi AS ThietBi
                INNER JOIN OKhuVuc AS KhuVuc ON ifnull(ThietBi.Ref_MaKhuVuc, 0) = KhuVuc.IOID
                WHERE ifnull(ThietBi.Ref_TrucThuoc, 0) = 0 %1$s
                ORDER BY KhuVuc.lft, ThietBi.MaThietBi
            ) AS ThietBiCha
            INNER JOIN ODanhSachThietBi AS ThietBiCon ON ThietBiCha.lft <= ThietBiCon.lft AND ThietBiCha.rgt >= ThietBiCon.rgt
            WHERE 1=1 %2$s
            ORDER BY ThietBiCon.lft
        ', $where, $where2);
        }
        else {
            $sql = sprintf('
                SELECT ThietBi.*
                FROM ODanhSachThietBi AS ThietBi
                INNER JOIN OKhuVuc AS KhuVuc ON ifnull(ThietBi.Ref_MaKhuVuc, 0) = KhuVuc.IOID
                WHERE ifnull(ThietBi.Ref_TrucThuoc, 0) = 0 %1$s
                ORDER BY KhuVuc.lft, ThietBi.MaThietBi
        ', $where, $where2);
        }



        // echo '<pre>'; print_r($sql); die;

        return $this->_o_DB->fetchAll($sql);
    }

    public function getEquipByLocation($locationIOID = 0, $equipStr = '')
    {
        $where    = '';
        $locName  = $locationIOID?$this->_o_DB->fetchOne(sprintf('select * from OKhuVuc where IOID = %1$d', @(int)$locationIOID)):'';
        $where   .= ($locName)
            ?sprintf(' AND (ifnull(ThietBi.Ref_MaKhuVuc, 0) IN  (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d)) ', $locName->lft, $locName->rgt)
            :'';
        $where   .= $equipStr?sprintf(' AND (MaThietBi like "%%%1$s%%" or TenThietBi like "%%%1$s%%")', $equipStr):'';

        $sql = sprintf('
            SELECT ThietBi.*
            FROM ODanhSachThietBi AS ThietBi
            INNER JOIN OKhuVuc AS KhuVuc ON ifnull(ThietBi.Ref_MaKhuVuc, 0) = KhuVuc.IOID
            WHERE 1=1 %1$s
            ORDER BY KhuVuc.lft, ThietBi.MaThietBi
        ', $where);
        return $this->_o_DB->fetchAll($sql);
    }

    public function getEquipByLine($lineIOID)
    {
        $where    = '';
        $where   .= ($lineIOID)?sprintf(' AND ifnull(ThietBi.Ref_DayChuyen, 0) = %1$d ', $lineIOID):' AND 1=0';

        $sql = sprintf('
            SELECT ThietBi.*
            FROM ODanhSachThietBi AS ThietBi
            INNER JOIN ODayChuyen AS DayChuyen ON ifnull(ThietBi.Ref_DayChuyen, 0) = DayChuyen.IOID
            WHERE 1=1 %1$s
            ORDER BY DayChuyen.IOID, ThietBi.MaThietBi
        ', $where);
        return $this->_o_DB->fetchAll($sql);
    }

    public function getEquipByCostCenter($costcenterIOID)
    {
        $where    = '';
        $where   .= ($costcenterIOID)?sprintf(' AND ifnull(ThietBi.Ref_TrungTamChiPhi, 0) = %1$d ', $costcenterIOID):' AND 1=0';

        $sql = sprintf('
            SELECT ThietBi.*
            FROM ODanhSachThietBi AS ThietBi
            INNER JOIN OTrungTamChiPhi AS TrungTamChiPhi ON ifnull(ThietBi.Ref_TrungTamChiPhi, 0) = TrungTamChiPhi.IOID
            WHERE 1=1 %1$s
            ORDER BY TrungTamChiPhi.IOID, ThietBi.MaThietBi
        ', $where);
        return $this->_o_DB->fetchAll($sql);
    }

    public function getEquipByManager($managerIOID)
    {
        $where    = '';
        $where   .= ($managerIOID)?sprintf(' AND ifnull(ThietBi.Ref_QuanLy, 0) = %1$d ', $managerIOID):'';

        $sql = sprintf('
            SELECT ThietBi.*
            FROM ODanhSachThietBi AS ThietBi
            LEFT JOIN ODanhSachNhanVien AS NhanVien ON ifnull(ThietBi.Ref_QuanLy, 0) = NhanVien.IOID
            WHERE 1=1 %1$s
            ORDER BY NhanVien.IOID, ThietBi.MaThietBi
        ', $where);
        return $this->_o_DB->fetchAll($sql);
    }

    public function getEquipsByCodeOrName($search, $inDepartment = false)
    {
        $where = '';
        $where.= $inDepartment?sprintf(' AND DeptID in (%1$s) ', $this->_user->user_dept_list):'';

        if($search)
        {
            $search = "%{$search}%";

            $sql = sprintf('
                select *
                from ODanhSachThietBi
                where
                    (MaThietBi like %1$s OR TenThietBi Like %1$s)
                    %2$s
                ORDER BY MaThietBi
                LIMIT 20
            ', $this->_o_DB->quote($search), $where);
        }
        else
        {
            $sql = sprintf('
                select *
                from ODanhSachThietBi
                WHERE 1=1 %1$s
                ORDER BY MaThietBi
                LIMIT 20'
            , $where);
        }

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getEquipments($locationIOID = 0, $equipGroupIOID = 0, $equipTypeIOID = 0, $lineIOID = 0, $costcenterIOID =0)
    {
        $where    = '';
        $locName  = $locationIOID?$this->_o_DB->fetchOne(sprintf('select * from OKhuVuc where IOID = %1$d', @(int)$locationIOID)):false;
        $where   .= ($locName)?sprintf(' AND (ThietBi.Ref_MaKhuVuc IN  (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d)) ',  $locName->lft, $locName->rgt):'';
        $where   .= ($equipGroupIOID)?sprintf(' AND ThietBi.Ref_NhomThietBi = %1$d ', $equipGroupIOID):'';
        $eqTypes  = $equipTypeIOID?$this->_o_DB->fetchOne(sprintf('select * from OLoaiThietBi where IOID = %1$d', @(int)$equipTypeIOID)):false;
        $where   .= ($eqTypes)?sprintf(' AND (ThietBi.Ref_LoaiThietBi IN  (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d)) ',  $eqTypes->lft, $eqTypes->rgt):'';
        $where   .= ($lineIOID)?sprintf(' AND ThietBi.Ref_DayChuyen = %1$d ', $lineIOID):'';
        $where   .= ($costcenterIOID)?sprintf(' AND ThietBi.Ref_TrungTamChiPhi = %1$d ', $costcenterIOID):'';

        if(Qss_Lib_System::fieldActive('ODanhSachThietBi', 'TrucThuoc'))
        {
            $orderby = 'ORDER BY ThietBi.lft';
        }
        else
        {
            $orderby = 'ORDER BY ThietBi.TenThietBi';
        }

        $sql = sprintf('
            SELECT ThietBi.*
            FROM ODanhSachThietBi AS ThietBi
            WHERE 1=1 %1$s
            %2$s
        ', $where, $orderby);
        return $this->_o_DB->fetchAll($sql);
    }

    public function getEquipsOrderByGroupAndTypeOfEquip(
        $locIOID = 0
        , $eqGroupIOID = 0
        , $eqTypeIOID = 0
        , $eqIOID = 0
        , $start = ''
        , $end = ''
    )
    {
        $where    = '';
        $locName  = $locIOID?$this->_o_DB->fetchOne(sprintf('select * from OKhuVuc where IOID = %1$d', @(int)$locIOID)):false;
        $where   .= ($locName)?sprintf(' AND (ThietBi1.RefKhuVuc IN  (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d)) ',  $locName->lft, $locName->rgt):'';
        $where   .= ($eqGroupIOID)?sprintf(' AND ThietBi1.RefNhomTB = %1$d ', $eqGroupIOID):'';
        $eqTypes  = $eqTypeIOID?$this->_o_DB->fetchOne(sprintf('select * from OLoaiThietBi where IOID = %1$d', @(int)$eqTypeIOID)):false;
        $where   .= ($eqTypes)?sprintf(' AND (ThietBi1.RefLoaiTB IN  (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d)) ',  $eqTypes->lft, $eqTypes->rgt):'';
        $where   .= ($eqIOID)?sprintf(' AND ThietBi1.RefThietBi = %1$d ', $eqIOID):'';
        $where   .= ($start && $end)?sprintf(' AND (ifnull(ThietBi1.NgayDuaVaoSuDung, \'\') = \'\' OR ThietBi1.NgayDuaVaoSuDung between %1$s and %2$s )', $this->_o_DB->quote($start), $this->_o_DB->quote($end)):($start?sprintf(' AND (ifnull(ThietBi1.NgayDuaVaoSuDung, \'\') = \'\' OR ThietBi1.NgayDuaVaoSuDung >= %1$s  )', $this->_o_DB->quote($start)):($end?sprintf(' AND ( ifnull(ThietBi1.NgayDuaVaoSuDung, \'\') = \'\' OR ThietBi1.NgayDuaVaoSuDung <= %1$s  )', $this->_o_DB->quote($end)):''));

        $sql = sprintf('
            SELECT
                ThietBi2.IOID
                , ThietBi2.NhomThietBiChinh
                , ThietBi2.DuAn
                , ThietBi2.XuatXu
                , ThietBi2.NamSanXuat
                , ThietBi2.NgayDuaVaoSuDung
                , ThietBi2.GhiChu
                , ThietBi2.MaKhuVuc
                , ThietBi2.DacTinhKT
                , ThietBi2.RefNhomTB
                , ThietBi2.RefLoaiTB
                , ThietBi2.Ref_TrucThuoc
                , ThietBi2.TenThietBi
                , ThietBi2.MaThietBi
                , ThietBi2.MaTaiSan
                , ThietBi2.Serial
                , LoaiTB1.Level AS LevelLoaiTBJoin
                , LoaiTB1.TenLoai AS LoaiThietBiJoin
                , LoaiTB1.IOID AS RefLoaiTBJoin
            FROM
            (
                SELECT ThietBi1.*
                FROM
                (
                    SELECT
                        ThietBi.*
                        , IF( ifnull(ThietBi.Ref_TrucThuoc, 0) = 0, @LftLoaiTB := ThietBi.LftLoaiTBIn, @LftLoaiTB := @LftLoaiTB) AS LftLoaiTB
                        , IF( ifnull(ThietBi.Ref_TrucThuoc, 0) = 0, @RgtLoaiTB := ThietBi.RgtLoaiTBIn, @RgtLoaiTB := @RgtLoaiTB) AS RgtLoaiTB
                        , IF( ifnull(ThietBi.Ref_TrucThuoc, 0) = 0, @LftNhomTB := ThietBi.LftNhomTBIn, @LftNhomTB := @LftNhomTB) AS LftNhomTB
                        , IF( ifnull(ThietBi.Ref_TrucThuoc, 0) = 0, @IOIDNhomTB := ThietBi.Ref_NhomThietBi, @IOIDNhomTB := @IOIDNhomTB) AS RefNhomTB
                        , IF( ifnull(ThietBi.Ref_TrucThuoc, 0) = 0, @IOIDLoaiTB := ThietBi.Ref_LoaiThietBi, @IOIDLoaiTB := @IOIDLoaiTB) AS RefLoaiTB
                        , IF( ifnull(ThietBi.Ref_TrucThuoc, 0) = 0, @IOIDKhuVuc := ThietBi.Ref_MaKhuVuc, @IOIDKhuVuc := @IOIDKhuVuc) AS RefKhuVuc
                        , IF( ifnull(ThietBi.Ref_TrucThuoc, 0) = 0, @IOIDThietBi := ThietBi.IOID, @IOIDThietBi := @IOIDThietBi) AS RefThietBi
                        , IF( ifnull(ThietBi.Ref_TrucThuoc, 0) = 0, @NhomTB := ThietBi.NhomThietBi, @NhomTB := @NhomTB) AS NhomThietBiChinh

                    FROM
                    (
                        SELECT ThietBi.*, NhomTB.lft AS LftNhomTBIn, LoaiTB.lft AS LftLoaiTBIn, LoaiTB.rgt AS RgtLoaiTBIn
                        FROM ODanhSachThietBi AS ThietBi
                        INNER JOIN ONhomThietBi AS NhomTB ON ThietBi.Ref_NhomThietBi = NhomTB.IOID
                        INNER JOIN OLoaiThietBi AS LoaiTB ON ThietBi.Ref_LoaiThietBi = LoaiTB.IOID
                        ORDER BY ThietBi.lft
                    ) AS ThietBi
                    JOIN (
                        SELECT
                            @LftLoaiTB:=0
                            , @LftNhomTB:=0
                            , @IOIDNhomTB:=0
                            , @IOIDLoaiTB:=0
                            , @IOIDKhuVuc:=0
                            , @IOIDThietBi:=0
                            ,@NhomTB := 0
                    ) AS temp1

                    ORDER BY LftNhomTB, LftLoaiTB, ThietBi.lft
                ) AS ThietBi1
                WHERE ThietBi1.DeptID in (%2$s) %1$s
            ) AS ThietBi2

            INNER JOIN
            (
                SELECT
                    v.*,
                    (SELECT count(*) FROM OLoaiThietBi AS u WHERE u.lft <= v.lft AND u.rgt >= v.rgt AND u.DeptID in (%2$s) AND v.DeptID in (%2$s)) AS Level
                FROM OLoaiThietBi AS v
                WHERE v.DeptID in (%2$s)
                ORDER BY lft
            ) AS LoaiTB1  ON ThietBi2.LftLoaiTB >= LoaiTB1.lft AND ThietBi2.RgtLoaiTB <= LoaiTB1.rgt

            ORDER BY ThietBi2.LftNhomTB, LoaiTB1.lft, ThietBi2.lft, ThietBi2.MaThietBi
        ', $where, $this->_user->user_dept_list);

        //echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * Lấy danh sách phụ tùng theo thiết bị bộ phận
     * @param int $locationIOID
     * @param int $groupIOID
     * @param int $typeIOID
     * @param int $equipIOID
     */
    public function getEquipmentsWithItsComponents(
        $locationIOID = 0
        , $groupIOID = 0
        , $typeIOID = 0
        , $equipIOID = 0
    )
    {
        $where    = '';
        $locName  = $locationIOID?$this->_o_DB->fetchOne(sprintf('select * from OKhuVuc where IOID = %1$d', @(int)$locationIOID)):false;
        $where   .= $locName?sprintf(' AND (ThietBi.Ref_MaKhuVuc IN  (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d)) ',  $locName->lft, $locName->rgt):'';
        $where   .= $groupIOID?sprintf(' AND ThietBi.Ref_NhomThietBi = %1$d ', $groupIOID):'';
        $eqTypes  = $typeIOID?$this->_o_DB->fetchOne(sprintf('select * from OLoaiThietBi where IOID = %1$d', @(int)$typeIOID)):false;
        $where   .= $eqTypes?sprintf(' AND (ThietBi.Ref_LoaiThietBi IN  (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d)) ',  $eqTypes->lft, $eqTypes->rgt):'';
        $where   .= $equipIOID?sprintf(' AND ThietBi.IOID = %1$d ', $equipIOID):'';

        $sql = sprintf('
            SELECT
                CauTruc.MaSP
                , CauTruc.TenSP
                , CauTruc.DonViTinh
                , CauTruc.SoLuongChuan
                , CauTruc.SoLuongHC
                , (
                    select group_concat( if( ifnull(v1.Ref_MaSP, 0) != 0,\', \', \'\'), v1.MaSP)
                    from ODanhSachPhuTung as v1
                    where v1.IFID_M705 = CauTruc.IFID_M705
                        and v1.Ref_ViTri = CauTruc.IOID
                        and v1.Ref_MaSP != CauTruc.Ref_MaSP
                ) AS ThayThe
                , CauTruc.SoNgayCanhBao
                , ThietBi.IOID AS EquipIOID
                , ThietBi.MaThietBi
                , ThietBi.TenThietBi
                , CauTruc.IOID AS ComponentIOID
                , CauTruc.ViTri
                , CauTruc.BoPhan
            FROM ODanhSachThietBi AS ThietBi
            INNER JOIN OCauTrucThietBi AS CauTruc ON ThietBi.IFID_M705 = CauTruc.IFID_M705
            WHERE ifnull(CauTruc.PhuTung, 0) != 0 AND ThietBi.DeptID in (%2$s) %1$s
            ORDER BY ThietBi.lft, CauTruc.lft
        ', $where, $this->_user->user_dept_list);
        return $this->_o_DB->fetchAll($sql);
    }

    public function getComponentsByIFID($equipIFID)
    {
        $sql = sprintf('
            SELECT
                CauTruc.*,
                (
                    SELECT count(*) FROM OCauTrucThietBi AS TempCauTruc WHERE TempCauTruc.IFID_M705 = %1$d
                        AND  TempCauTruc.lft <= CauTruc.lft AND TempCauTruc.rgt >= CauTruc.rgt
                ) AS LEVEL
            FROM OCauTrucThietBi AS CauTruc
            WHERE CauTruc.IFID_M705 = %1$d
            ORDER BY CauTruc.lft
        ', $equipIFID);
        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * Lấy danh sách phụ tùng thay thế
     * @param $equipIFID
     * @return mixed
     */
    public function getReplaceSparepartsByIFID($equipIFID)
    {
        $sql = sprintf('
            SELECT
                PhuTung.*
            FROM ODanhSachPhuTung AS PhuTung
            WHERE PhuTung.IFID_M705 = %1$d
        ', $equipIFID);
        return $this->_o_DB->fetchAll($sql);
    }

    public function getSparepartInfo($refEquip, $refItem, $refPosition)
    {
        $sql = sprintf('
            SELECT
                PhuTung.*
            FROM ODanhSachPhuTung AS PhuTung
            INNER JOIN ODanhSachThietBi AS ThietBi On PhuTung.IFID_M705 = ThietBi.IFID_M705
            WHERE
                ThietBi.IOID = %1$d
                AND PhuTung.Ref_MaSP = %2$d
                AND IFNULL(PhuTung.Ref_ViTri, 0) =%3$d
        ', $refEquip, $refItem, $refPosition);
//        echo $sql; die;
        return $this->_o_DB->fetchOne($sql);
    }

    public function getMaterialsByEquip($equip, $componentIOID = 0, $materialCodeOrName = '', $type = 0)
    {
        $where = '';
        $where.= $materialCodeOrName?sprintf(' AND ( MatHang.MaSanPham like "%%%1$s%%" OR MatHang.TenSanPham like "%%%1$s%%" )', $materialCodeOrName):'';
        $where.= $componentIOID?sprintf(' AND CauTruc.IOID = %1$d ', $componentIOID):'';

        $sql = sprintf('
            SELECT MatHang.*, CauTruc.IOID AS CauTrucIOID, ThietBi.IOID AS ThietBiIOID
            FROM OCauTrucThietBi AS CauTruc
            INNER JOIN ODanhSachThietBi AS ThietBi On CauTruc.IFID_M705 = ThietBi.IFID_M705
            INNER JOIN OSanPham AS MatHang ON IFNULL(CauTruc.Ref_MaSP, 0) = MatHang.IOID
            WHERE ThietBi.IOID = %1$d %2$s            
            GROUP BY MatHang.IOID
            ORDER BY MatHang.MaSanPham
        ', $equip, $where);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }
}
