<?php

class Qss_Model_Maintenance_GeneralPlans extends Qss_Model_Abstract
{
    protected $user;

    public function __construct()
    {
        parent::__construct();
        $this->user = Qss_Register::get('userinfo');
    }

    /**
     * Đếm số lần bảo trì theo nhóm thiết bị
     * @todo: Thiết bị con cần cho thiết bị cha
     * @param $start
     * @param $end
     * @param int $deptID
     * @param int $location
     * @param int $equipGroup
     * @param int $equipType
     * @param int $equip
     * @return mixed
     */
    public function countPlansByEquipGroups(
        $start
        , $end
        , $deptID = 0
        , $location = 0
        , $equipGroup = 0
        , $equipType = 0
        , $equip = 0)
    {

        $where  = '';
        $loc    = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $location));
        $type   = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OLoaiThietBi WHERE IOID = %1$d', $equipType));

        if($deptID)
        {
            $mDept    = new Qss_Model_Admin_Department();
            $rDept    = array($deptID);
            $depts    = $mDept->getChildDepartments($deptID);

            foreach($depts as $item)
            {
                $rDept[] = $item->DepartmentID;
            }

            $where .= sprintf(' AND ThietBi.DeptID IN (%1$s) ', implode(', ', $rDept));
        }

        $where .= $loc?sprintf(' AND (ThietBi.Ref_MaKhuVuc IN (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))', $loc->lft, $loc->rgt):'';
        $where .= $type?sprintf(' AND (ThietBi.Ref_LoaiThietBi IN (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d))', $type->lft, $type->rgt):'';
        $where .= ($equipGroup)?sprintf(' AND ThietBi.Ref_NhomThietBi = %1$d  ', $equipGroup):'';
        $where .= ($equip)?sprintf(' AND ThietBi.IOID = %1$d  ', $equip):'';

        $sql = sprintf('
            SELECT COUNT(1) AS SoLuongTheoNhom, ThietBi.Ref_NhomThietBi
            FROM OKeHoachBaoTri AS PhieuBaoTriKeHoach
            INNER JOIN qsiforms AS IFormPhieuKeHoach ON PhieuBaoTriKeHoach.IFID_M837 = IFormPhieuKeHoach.IFID
            INNER JOIN OKeHoachTongThe AS KeHoachTongThe ON PhieuBaoTriKeHoach.Ref_KeHoachTongThe = KeHoachTongThe.IOID
            INNER JOIN qsiforms AS IFormKeHoach ON KeHoachTongThe.IFID_M838 = IFormKeHoach.IFID
            INNER JOIN ODanhSachThietBi AS ThietBi ON IFNULL(PhieuBaoTriKeHoach.Ref_MaThietBi, 0) = ThietBi.IOID
            WHERE IFormKeHoach.Status = 3 AND IFormPhieuKeHoach.Status = 2
                AND (PhieuBaoTriKeHoach.NgayBatDau BETWEEN %1$s AND %2$s) %3$s
            GROUP BY ThietBi.Ref_NhomThietBi
        ', $this->_o_DB->quote($start), $this->_o_DB->quote($end), $where);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getPlansByStatus($status)
    {
        $lang = ($this->user->user_lang != 'vn') ? '_' . $this->user->user_lang : '';

        $sql = sprintf('
            SELECT
            KeHoach.*
            , WFlowStep.Color
            , WFlowStep.Name%1$s AS StepName
            , IFNULL(IForm.Status, 0) AS StepNo
            FROM OKeHoachTongThe AS KeHoach
            INNER JOIN qsiforms AS IForm ON KeHoach.IFID_M838 = IForm.IFID
            INNER JOIN qsworkflows AS WFlow ON IForm.FormCode = WFlow.FormCode
            INNER JOIN qsworkflowsteps AS WFlowStep ON WFlow.WFID = WFlowStep.WFID
                AND IFNULL(IForm.Status, 0) = IFNULL(WFlowStep.StepNo, 0)
            WHERE IFNULL(IForm.Status, 0) = %2$d
        ', $lang, $status);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * @note:Không sửa left join ở đây, HFH module M838 không có quy trình
     * @param $ifid
     * @return mixed
     */
    public function getGeneralPlanByIFID($ifid)
    {
        $lang = ($this->user->user_lang != 'vn') ? '_' . $this->user->user_lang : '';

        $sql = sprintf('
            SELECT
                KeHoach.*
                , WFlowStep.Color
                , WFlowStep.Name%2$s AS StepName
                , IFNULL(IForm.Status, 0) AS StepNo
            FROM OKeHoachTongThe AS KeHoach
            LEFT JOIN qsiforms AS IForm ON KeHoach.IFID_M838 = IForm.IFID
            LEFT JOIN qsworkflows AS WFlow ON IForm.FormCode = WFlow.FormCode
            LEFT JOIN qsworkflowsteps AS WFlowStep ON WFlow.WFID = WFlowStep.WFID
                AND IFNULL(IForm.Status, 0) = IFNULL(WFlowStep.StepNo, 0)
            WHERE KeHoach.IFID_M838 = %1$d
        ', $ifid, $lang);

        return $this->_o_DB->fetchOne($sql);
    }

    public function getGeneralPlansByYear($year = 2016)
    {
        $lang  = ($this->user->user_lang != 'vn') ? '_' . $this->user->user_lang : '';
        $where = $year?sprintf(' WHERE YEAR(KeHoach.NgayBatDau) = %1$d ', $year):'';

        $sql = sprintf('
            SELECT
                KeHoach.*
                , WFlowStep.Color
                , WFlowStep.Name%2$s AS StepName
                , (
                    SELECT COUNT(1)
                    FROM OKeHoachBaoTri AS ChiTiet1
                    INNER JOIN qsiforms AS IForm1 ON ChiTiet1.IFID_M837 = IForm1.IFID
                    WHERE IFNULL(ChiTiet1.Ref_KeHoachTongThe, 0) = KeHoach.IOID
                        AND IFNULL(IForm1.Status, 0) = 2
                    GROUP BY IFNULL(ChiTiet1.Ref_KeHoachTongThe, 0)
                ) AS SoChiTietDuocDuyet
                , (
                    SELECT COUNT(1)
                    FROM OKeHoachBaoTri AS ChiTiet2
                    INNER JOIN qsiforms AS IForm2 ON ChiTiet2.IFID_M837 = IForm2.IFID
                    WHERE IFNULL(ChiTiet2.Ref_KeHoachTongThe, 0) = KeHoach.IOID
                    GROUP BY IFNULL(ChiTiet2.Ref_KeHoachTongThe, 0)
                ) AS TongSoChiTiet
            FROM OKeHoachTongThe AS KeHoach
            LEFT JOIN qsiforms AS IForm ON KeHoach.IFID_M838 = IForm.IFID
            LEFT JOIN qsworkflows AS WFlow ON IForm.FormCode = WFlow.FormCode
            LEFT JOIN qsworkflowsteps AS WFlowStep ON WFlow.WFID = WFlowStep.WFID
                AND IFNULL(IForm.Status, 0) = IFNULL(WFlowStep.StepNo, 0)
            %1$s
            ORDER BY KeHoach.NgayBatDau DESC
        ', $where, $lang);

        // echo '<pre>'; print_r($sql); die;

        return $this->_o_DB->fetchAll($sql);
    }

    public function getDetailPlanByGeneralIFID($generalIFID, $location = 0, $equiptype = 0)
    {
        $lang   = ($this->user->user_lang != 'vn') ? '_' . $this->user->user_lang : '';
        $where  = ''; // Điều kiện lọc

        if(Qss_Lib_System::fieldActive('OKhuVuc', 'TrucThuoc'))
        {
            $loc    = ($location != 0)?$this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $location)):array();
            $where .= count((array)$loc)?sprintf('and IFNULL(ThietBi.Ref_MaKhuVuc, 0) in (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d)', $loc->lft, $loc->rgt):'';
        }
        else
        {
            $where .= ($location != 0)?sprintf(' and IFNULL(ThietBi.Ref_MaKhuVuc, 0) = %1$d ', $location):'';
        }

        if(Qss_Lib_System::fieldActive('OLoaiThietBi', 'TrucThuoc'))
        {
            $eqType = ($equiptype != 0)?$this->_o_DB->fetchOne(sprintf('SELECT * FROM OLoaiThietBi WHERE IOID = %1$d', $equiptype)):array();
            $where .= count((array)$eqType)?sprintf('and (IFNULL(ThietBi.Ref_LoaiThietBi, 0) IN  (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d)) ',$eqType->lft, $eqType->rgt):'';
        }
        else
        {
            $where .= ($equiptype != 0)?sprintf(' and IFNULL(ThietBi.Ref_LoaiThietBi, 0) = %1$d ', $equiptype):'';
        }

        $sql = sprintf('
            SELECT
                KeHoachChiTiet.*
                , CONCAT(MONTH(KeHoachChiTiet.NgayBatDau), \'-\', YEAR(KeHoachChiTiet.NgayBatDau)) AS ThangBatDau
                , WFlowStep.Color
                , WFlowStep.Name%2$s AS StepName
                , IFNULL(IForm.Status, 0) AS StepNo
                , CauTruc.IOID AS Ref_ViTri
                , CauTruc.ViTri
                , CauTruc.BoPhan
                , PhieuBaoTri.SoPhieu AS SoPhieuBaoTri
                , PhieuBaoTri.IFID_M759
                , PhieuBaoTri.NgayBatDau AS NgayBatDauPhieu
                , PhieuBaoTri.Ngay AS NgayKetThucPhieu
            FROM OKeHoachBaoTri AS KeHoachChiTiet
            INNER JOIN OKeHoachTongThe AS KeHoachTongThe ON KeHoachChiTiet.Ref_KeHoachTongThe = KeHoachTongThe.IOID
            LEFT JOIN ODanhSachThietBi AS ThietBi ON IFNULL(KeHoachChiTiet.Ref_MaThietBi, 0) = ThietBi.IOID
            LEFT JOIN qsiforms AS IForm ON KeHoachChiTiet.IFID_M837 = IForm.IFID
            LEFT JOIN qsworkflows AS WFlow ON IForm.FormCode = WFlow.FormCode
            LEFT JOIN qsworkflowsteps AS WFlowStep ON WFlow.WFID = WFlowStep.WFID
                AND IFNULL(IForm.Status, 0) = IFNULL(WFlowStep.StepNo, 0)
            LEFT JOIN OCauTrucThietBi AS CauTruc ON IFNULL(KeHoachChiTiet.Ref_BoPhan, 0) = CauTruc.IOID
            LEFT JOIN OPhieuBaoTri AS PhieuBaoTri ON                 
                IFNULL(KeHoachChiTiet.Ref_KeHoachTongThe, 0) = IFNULL(PhieuBaoTri.Ref_SoKeHoach, 0)
                AND KeHoachChiTiet.Ref_MaThietBi = IFNULL(PhieuBaoTri.Ref_MaThietBi, 0)
                AND IFNULL(KeHoachChiTiet.Ref_BoPhan, 0) = IFNULL(PhieuBaoTri.Ref_BoPhan, 0)
                AND IFNULL(KeHoachChiTiet.Ref_LoaiBaoTri, 0) = IFNULL(PhieuBaoTri.Ref_LoaiBaoTri, 0)
                AND IFNULL(KeHoachChiTiet.MoTa, "") = IFNULL(PhieuBaoTri.MoTa, "")
                AND IFNULL(KeHoachChiTiet.Ref_ChuKy, 0) = IFNULL(PhieuBaoTri.Ref_ChuKy, 0)
                AND IFNULL(KeHoachChiTiet.NgayBatDau, "") = IFNULL(PhieuBaoTri.NgayYeuCau, "")
                -- AND IFNULL(KeHoachChiTiet.LanBaoTri, \'\') = IFNULL(PhieuBaoTri.LanBaoTri, \'\')
            WHERE KeHoachTongThe.IFID_M838 = %1$d %3$s            
        ', $generalIFID, $lang, $where);

        if(Qss_Lib_System::fieldActive('OKeHoachBaoTri', 'LoaiKeHoach')) {
            $sql .=  ' ORDER BY KeHoachChiTiet.LoaiKeHoach, KeHoachChiTiet.TenThietBi';
        }
        else {
            $sql .=  ' ORDER BY KeHoachChiTiet.NgayBatDau DESC, KeHoachChiTiet.TenThietBi';
        }


        // echo '<pre>'; print_r($sql); die;

        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * Hàm được sử dụng trong dự án HFH
     * @param $start
     * @param $end
     * @param int $locIOID
     * @param int $eqTypeIOID
     * @param int $eqGroupIOID
     * @return mixed
     */
    public function getDetail($start, $end, $locIOID = 0, $eqTypeIOID = 0, $eqGroupIOID = 0)
    {
        $where  = ''; // Điều kiện lọc


        if(Qss_Lib_System::fieldActive('OKhuVuc', 'TrucThuoc'))
        {
            $loc    = ($locIOID != 0)?$this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locIOID)):array();
            $where .= count((array)$loc)?sprintf('and IFNULL(ThietBi.Ref_MaKhuVuc, 0) in (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d)', $loc->lft, $loc->rgt):'';
        }
        else
        {
            $where .= ($locIOID != 0)?sprintf(' and IFNULL(ThietBi.Ref_MaKhuVuc, 0) = %1$d ', $locIOID):'';
        }

        if(Qss_Lib_System::fieldActive('OLoaiThietBi', 'TrucThuoc'))
        {
            $eqType = ($eqTypeIOID != 0)?$this->_o_DB->fetchOne(sprintf('SELECT * FROM OLoaiThietBi WHERE IOID = %1$d', $eqTypeIOID)):array();
            $where .= count((array)$eqType)?sprintf('and (IFNULL(ThietBi.Ref_LoaiThietBi, 0) IN  (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d)) ',$eqType->lft, $eqType->rgt):'';
        }
        else
        {
            $where .= ($eqTypeIOID != 0)?sprintf(' and IFNULL(ThietBi.Ref_LoaiThietBi, 0) = %1$d ', $eqTypeIOID):'';
        }

        $where .= ($eqGroupIOID != 0)?sprintf(' and ThietBi.Ref_NhomThietBi = %1$d ', $eqGroupIOID):'';



        $sql = sprintf('
            SELECT KeHoachChiTiet.*, ThietBi.MaTaiSan
            FROM OKeHoachBaoTri AS KeHoachChiTiet
            INNER JOIN OKeHoachTongThe AS KeHoachTongThe ON KeHoachChiTiet.Ref_KeHoachTongThe = KeHoachTongThe.IOID
            LEFT JOIN ODanhSachThietBi AS ThietBi ON IFNULL(KeHoachChiTiet.Ref_MaThietBi, 0) = ThietBi.IOID
            WHERE IFNULL(KeHoachChiTiet.Ref_KeHoachTongThe, 0) != 0 AND (KeHoachTongThe.NgayBatDau BETWEEN %1$s AND %2$s) %3$s
            ORDER BY TRIM(ThietBi.LoaiThietBi), TRIM(KeHoachChiTiet.TenThietBi)
        ', $this->_o_DB->quote($start), $this->_o_DB->quote($end), $where);
        // echo '<pre>'; print_r($sql); die;

        return $this->_o_DB->fetchAll($sql);
    }
}