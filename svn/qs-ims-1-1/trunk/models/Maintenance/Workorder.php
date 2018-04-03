<?php
class Qss_Model_Maintenance_Workorder extends Qss_Model_Abstract
{
    private $lastDocNo;

    public function __construct()
    {
        parent::__construct();
        $this->lastDocNo = array();
    }

    public static function createInstance()
    {
        return new self();
    }

    public function getDocNo()
    {
        $object = new Qss_Model_Object();
        $object->v_fInit('OPhieuBaoTri', 'M759');
        $document = new Qss_Model_Extra_Document($object);
        return $document->getDocumentNo();
    }

    /*public function getBatchDocNo()
    {
        $object   = new Qss_Model_Object();
        $object->v_fInit('OPhieuBaoTri', 'M759');
        $document = new Qss_Model_Extra_Document($object);
        $last = (!$this->lastDocNo)?$document->getLast():$this->lastDocNo;
        $this->lastDocNo = ++$last;

        return $document->writeDocumentNo(++$last);
    }*/

	public function getDefaultPriority()
	{
		return $this->_o_DB->fetchOne('SELECT * FROM OMucDoUuTien ORDER BY TrongSo DESC, IOID DESC LIMIT 1');
	}

	public function getDefaultCorrective()
	{
		$sql = sprintf('SELECT * FROM OPhanLoaiBaoTri WHERE LoaiBaoTri = %1$s LIMIT 1'
            , $this->_o_DB->quote(Qss_Lib_Extra_Const::MAINT_TYPE_BREAKDOWN));
		return $this->_o_DB->fetchOne($sql);
	}

    public function getOrderByIOID($ioid)
    {
		$sql = sprintf('
			SELECT OPhieuBaoTri.*, qsiforms.Status
			FROM OPhieuBaoTri
			INNER JOIN qsiforms ON OPhieuBaoTri.IFID_M759 = qsiforms.IFID
			WHERE OPhieuBaoTri.IOID = %1$d
		', $ioid);
        return $this->_o_DB->fetchOne($sql);
    }

    public function getOrderByIFID($ifid)
    {
		$sql = sprintf('
            SELECT qsws.*, pbt.*, iform.Status
			FROM OPhieuBaoTri AS pbt
			INNER JOIN qsiforms AS iform ON pbt.IFID_M759 = iform.IFID
			INNER JOIN qsworkflows AS qsw ON qsw.FormCode = iform.FormCode
			INNER JOIN qsworkflowsteps AS qsws ON qsw.WFID = qsws.WFID AND iform.Status = qsws.StepNo
			WHERE pbt.IFID_M759 = %1$d
		', $ifid);
        return $this->_o_DB->fetchOne($sql);
    }

    /**
     * Hàm này yêu cầu cho HFH, in lý lịch thiết bị
     * @param $equipIOID
     * @param string $start
     * @param string $end
     * @return mixed
     */
    public function getOrderByEquip($equipIOID, $start = '', $end = '')
    {
        $where = '';

        if($start != '' && $end != '')
        {
            $where .= sprintf(' AND ( IFNULL(pbt.NgayBatDau, \'\') BETWEEN %1$s AND %2$s )'
            , $this->_o_DB->quote($start), $this->_o_DB->quote($end));
        }

        $sql = sprintf('
            SELECT pbt.*
		    FROM OPhieuBaoTri AS pbt
			WHERE (pbt.Ref_MaThietBi = %1$d or pbt.Ref_TenThietBi = %1$d) %2$s
			ORDER BY pbt.NgayBatDau DESC
		', $equipIOID, $where);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function countEmployeeTasks($start = '', $end = '' , $employeeIOID = 0, $taskStatus = '')
    {
        $filterTask  = '';
        $filterTask .= $employeeIOID?sprintf(' AND IFNULL(CongViec2.Ref_NguoiThucHien, 0) = %1$d ', $employeeIOID):'';
        $filterTask .= (is_numeric($taskStatus))?sprintf(' AND IFNULL(CongViec2.ThucHien, 0) = %1$d ', $taskStatus):'';

        $sql = sprintf('
            SELECT COUNT(1) AS Total
            FROM OCongViecBTPBT AS CongViec2
            INNER JOIN OPhieuBaoTri AS PhieuBaoTri2 ON CongViec2.IFID_M759 = PhieuBaoTri2.IFID_M759
            WHERE (CongViec2.Ngay BETWEEN %1$s AND %2$s) %3$s
		', $this->_o_DB->quote($start), $this->_o_DB->quote($end), $filterTask);
        return $this->_o_DB->fetchOne($sql);
    }

    public function analyseEmployeeTasks(
        $start = ''
        , $end = ''
        , $employeeIOID = 0)
    {
        $filterEmpTask = '';
        $filterTask    = '';

        // Filter by employee
        $filterEmpTask = $employeeIOID?sprintf(' AND IFNULL(CongViec1.Ref_NhanVien, 0) = %1$d ', $employeeIOID):'';
        $filterTask    = $employeeIOID?sprintf(' AND IFNULL(CongViec2.Ref_NguoiThucHien, 0) = %1$d ', $employeeIOID):'';

        $sql = sprintf('
			SELECT
			    CongViec.Ref_NhanVien
			    , CongViec.NhanVien
			    , CongViec.MaNhanVien
			    , CongViec.TenNhanVien
			    , SUM(IFNULL(CongViec.ChuaThucHien, 0)) AS ChuaThucHien
			    , SUM(IFNULL(CongViec.DaThucHien, 0)) AS DaThucHien
			    , SUM(IFNULL(CongViec.BoQua, 0)) AS BoQua
			    , SUM(IFNULL(CongViec.ThoiGianDuKien, 0)) AS ThoiGianDuKien
			    , SUM(IFNULL(CongViec.ThoiGianThucTe, 0)) AS ThoiGianThucTe
			FROM
			(
			    /*
				(
					SELECT
                        CongViec1.Ref_NhanVien
						, CongViec1.NhanVien
						, NhanVien1.MaNhanVien
						, NhanVien1.TenNhanVien
						, SUM(CASE WHEN IFNULL(CongViec1.TinhTrang, 0) = 0 THEN 1 ELSE 0 END) AS ChuaThucHien
						, SUM(CASE WHEN IFNULL(CongViec1.TinhTrang, 0) = 1 THEN 1 ELSE 0 END) AS DaThucHien
						, SUM(CASE WHEN IFNULL(CongViec1.TinhTrang, 0) = 2 THEN 1 ELSE 0 END) AS BoQua
						, SUM(IFNULL(CongViec1.ThoiGianDuKien, 0)) AS ThoiGianDuKien
						, SUM(IFNULL(CongViec1.ThoiGianThucTe, 0)) AS ThoiGianThucTe
					FROM OCongViecNhanVien AS CongViec1
					LEFT JOIN ODanhSachNhanVien AS NhanVien1 ON CongViec1.Ref_NhanVien = NhanVien1.IOID
					WHERE (CongViec1.Ngay BETWEEN %1$s AND %2$s) %3$s
					GROUP BY CongViec1.Ref_NhanVien
				)
				UNION ALL
				*/
				(
					SELECT
                        CongViec2.Ref_NguoiThucHien AS Ref_NhanVien
						, CongViec2.NguoiThucHien AS NhanVien
					    , NhanVien2.MaNhanVien
						, NhanVien2.TenNhanVien
						, SUM(CASE WHEN IFNULL(CongViec2.ThucHien, 0) = 0 THEN 1 ELSE 0 END) AS ChuaThucHien
						, SUM(CASE WHEN IFNULL(CongViec2.ThucHien, 0) = 1 THEN 1 ELSE 0 END) AS DaThucHien
						, SUM(CASE WHEN IFNULL(CongViec2.ThucHien, 0) = 2 THEN 1 ELSE 0 END) AS BoQua
                        , SUM(IFNULL(CongViec2.ThoiGianDuKien, 0)) AS ThoiGianDuKien
						, SUM(IFNULL(CongViec2.ThoiGian, 0)) AS ThoiGianThucTe
					FROM OCongViecBTPBT AS CongViec2
					INNER JOIN OPhieuBaoTri AS PhieuBaoTri2 ON CongViec2.IFID_M759 = PhieuBaoTri2.IFID_M759
					INNER JOIN qsiforms ON PhieuBaoTri2.IFID_M759 = qsiforms.IFID
					LEFT JOIN ODanhSachNhanVien AS NhanVien2 ON CongViec2.Ref_NguoiThucHien = NhanVien2.IOID
					WHERE
					    (CongViec2.Ngay BETWEEN %1$s AND %2$s )
					    AND qsiforms.Status in (3, 4)
					    %4$s
					GROUP BY CongViec2.Ref_NguoiThucHien
				)
			) AS CongViec
			GROUP BY CongViec.Ref_NhanVien
			ORDER BY CongViec.MaNhanVien
		', $this->_o_DB->quote($start), $this->_o_DB->quote($end), $filterEmpTask, $filterTask);
        // echo '<Pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

	public function getEmployeeTasks(
        $start = ''
        , $end = ''
        , $employeeIOID = 0
        , $page = 0
        , $display = 0
        , $taskStatus = '')
	{
        $limit       = ''; // Phan trang
		$filterTask  = '';
		$filterTask .= $employeeIOID?sprintf(' AND IFNULL(CongViec2.Ref_NguoiThucHien, 0) = %1$d ', $employeeIOID):'';
        $filterTask .= (is_numeric($taskStatus))?sprintf(' AND IFNULL(CongViec2.ThucHien, 0) = %1$d ', $taskStatus):'';


        // Phan trang
        if($page && $display)
        {
            $startLimit = ceil(($page - 1) * $display);
            $limit      = " limit {$startLimit}, {$display}";
        }


		$sql = sprintf('
            SELECT
                CongViec2.IFID_M759  AS IFID
                , CongViec2.IOID
                , CongViec2.MoTa AS CongViec
                , CongViec2.Ref_NguoiThucHien AS Ref_NhanVien
                , CongViec2.NguoiThucHien AS NhanVien
                , CongViec2.ThoiGianDuKien AS ThoiGianDuKien
                , CongViec2.ThoiGian AS ThoiGianThucTe
                , CongViec2.ThucHien AS TinhTrang
                , CongViec2.Ngay
                , PhieuBaoTri2.MaThietBi
                , PhieuBaoTri2.TenThietBi
                , CongViec2.BoPhan
                , IFNULL(CongViec2.ThucHien,0) AS ThucHien
                , \'WORKORDER\' AS Loai
                , PhieuBaoTri2.SoPhieu
            FROM OCongViecBTPBT AS CongViec2
            INNER JOIN OPhieuBaoTri AS PhieuBaoTri2 ON CongViec2.IFID_M759 = PhieuBaoTri2.IFID_M759
            WHERE (CongViec2.Ngay BETWEEN %1$s AND %2$s ) %3$s
            ORDER BY PhieuBaoTri2.NgayBatDau DESC, PhieuBaoTri2.IFID_M759, CongViec2.IOID
			%4$s
		', $this->_o_DB->quote($start), $this->_o_DB->quote($end), $filterTask, $limit);
		// echo '<Pre>'; print_r($sql); die;
		return $this->_o_DB->fetchAll($sql);
	}

	/**
	 * Lay nhung phieu bao tri da ban hanh trong mot khoang thoi gian
	 * @param $mStart
	 * @param $mEnd
	 * @return mixed
	 */
	public function getIssueWorkOrdersInRange($mStart, $mEnd, $locationIOID = 0, $equipIOID = 0, $equipGroup= 0)
	{
		$filter = '';
		$loc    = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locationIOID));
		$filter.= $loc?sprintf(' AND ifnull(ThietBi.Ref_MaKhuVuc, 0) IN (SELECT IOID FROM OKhuVuc WHERE lft >= %1$d AND rgt <= %2$d) ', $loc->lft, $loc->rgt):'';
		$filter.= $equipIOID?sprintf(' AND ThietBi.IOID = %1$d ', $equipIOID):'';
        $filter .= $equipGroup?sprintf(' AND IFNULL(ThietBi.Ref_NhomThietBi, 0) = %1$d ', $equipGroup):'';

        if(Qss_Lib_System::formSecure('M125')) {
            $filter .= sprintf('
            				AND (
					IFNULL(phieuBT.Ref_MaDVBT, 0) in (
						SELECT IOID FROM ODonViSanXuat
						inner join qsrecordrights on ODonViSanXuat.IFID_M125 = qsrecordrights.IFID
						WHERE UID = %1$d
					)
				)
            ', $this->_user->user_id);
        }

		$sql = sprintf('
			SELECT
				phieuBT.*
				, CASE WHEN ifnull(phieuBT.Ngay, \'\') = \'\' THEN \'bgyellow\'
				WHEN ifnull(phieuBT.Ngay, \'\') >= %3$s THEN \'bgyellow\'
				ELSE \'bgpink\' END AS LineClass
			FROM OPhieuBaoTri AS phieuBT
			INNER JOIN qsiforms AS iform ON phieuBT.IFID_M759 = iform.IFID
			INNER JOIN ODanhSachThietBi AS ThietBi ON phieuBT.Ref_MaThietBi = ThietBi.IOID
			WHERE iform.Status = 2
				AND ifnull(phieuBT.NgayDuKienHoanThanh, \'\') between %1$s and %2$s
				%4$s
			ORDER BY phieuBT.NgayBatDau ASC, phieuBT.Ngay ASC, phieuBT.MaThietBi
		',$this->_o_DB->quote($mStart)
		, $this->_o_DB->quote($mEnd)
		, $this->_o_DB->quote(date('Y-m-d'))
		, $filter
		);
		return $this->_o_DB->fetchAll($sql);
	}

	/**
	 * Lay nhung phieu bao tri da ban hanh qua han
	 * cac phieu nay can co ngay ket thuc de lam can cu so sanh
	 * @param $mStart
	 * @param $mEnd
	 * @return mixed
	 */
	public function getOverDueIssueWorkOrders($mStart, $mEnd, $locationIOID = 0, $equipIOID = 0, $equipGroup)
	{
		$filter = '';
		$loc    = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locationIOID));
		$filter.= $loc?sprintf(' AND ifnull(ThietBi.Ref_MaKhuVuc, 0) IN (SELECT IOID FROM OKhuVuc WHERE lft >= %1$d AND rgt <= %2$d) ', $loc->lft, $loc->rgt):'';
		$filter.= $equipIOID?sprintf(' AND ThietBi.IOID = %1$d ', $equipIOID):'';
        $filter .= $equipGroup?sprintf(' AND IFNULL(ThietBi.Ref_NhomThietBi, 0) = %1$d ', $equipGroup):'';

		$sql = sprintf('
			SELECT
				phieuBT.*
				, CASE WHEN ifnull(phieuBT.Ngay, \'\') = \'\' THEN \'bgyellow\'
				ELSE \'bgpink\' END AS LineClass
			FROM OPhieuBaoTri AS phieuBT
			INNER JOIN qsiforms AS iform ON phieuBT.IFID_M759 = iform.IFID
			INNER JOIN ODanhSachThietBi AS ThietBi ON phieuBT.Ref_MaThietBi = ThietBi.IOID
			WHERE iform.Status = 2
				AND
				(
					(ifnull(phieuBT.Ngay, \'\') != \'\' AND phieuBT.Ngay <= %3$s)
					OR
					(ifnull(phieuBT.Ngay, \'\') = \'\' AND (phieuBT.NgayBatDau BETWEEN %1$s AND %2$s) )
				)
				AND (
					IFNULL(phieuBT.Ref_MaDVBT, 0) in (
						SELECT IOID FROM ODonViSanXuat
						inner join qsrecordrights on ODonViSanXuat.IFID_M125 = qsrecordrights.IFID
						WHERE UID = %5$d
					)
				)
				%4$s
			
			ORDER BY phieuBT.NgayBatDau ASC, phieuBT.Ngay ASC, phieuBT.MaThietBi
		', $this->_o_DB->quote($mStart)
		, $this->_o_DB->quote($mEnd)
		, $this->_o_DB->quote(date('Y-m-d'))
		, $filter
		, $this->_user->user_id);
		return $this->_o_DB->fetchAll($sql);
	}

    public function countPMRatio($start = '', $end = '')
    {
        $sql = sprintf('
            SELECT
                SUM(IF(IFNULL(Phieu.Ref_ChuKy,0) != 0, 1, 0)) AS KeHoach
                , SUM(IF(IFNULL(Phieu.Ref_ChuKy,0) = 0, 1, 0)) AS NgoaiKeHoach
            FROM OPhieuBaoTri AS Phieu
            INNER JOIN qsiforms AS Form ON Phieu.IFID_M759 = Form.IFID
            WHERE IFNULL(Form.Status, 0) != 5 AND (IFNULL(Phieu.NgayBatDau, \'\') BETWEEN %1$s AND %2$s)
        ', $this->_o_DB->quote($start), $this->_o_DB->quote($end));
        return $this->_o_DB->fetchOne($sql);
    }

    /**
     * Lay doi tuong chinh phieu bao tri theo dieu kien
     * @note: Luu y muon lay them doi tuong hay co dieu kien dac biet nen viet ra ham moi
     * @param string $start
     * @param string $end
     * @param int $locIOID
     * @param int $maintTypeIOID
     * @param array $maintTypeIOIDArr
     * @param int $employeeTaskIOID
     * @param int $status
     * @return mixed
     */
    public function getOrders(
        $start = ''
        , $end = ''
        , $locIOID = 0
        , $maintTypeIOID = 0
        , $maintTypeIOIDArr = array()
        , $employeeTaskIOID = 0
        , $status = 0
		, $eqIOID = 0
        , $workcenter = 0
    )
    {
        // @note: (phieu.`NgayYeuCau`, phieu.`NgayBatDau`) ngay co nen su dung ca ngay bat dau?
        $where  = '';
        $where .= ($start && $end)?sprintf(' AND (PhieuBaoTri.NgayBatDauDuKien >= %1$s and PhieuBaoTri.NgayBatDauDuKien <= %2$s)', $this->_o_DB->quote($start), $this->_o_DB->quote($end)):'';
        $loc    = $locIOID?$this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locIOID)):'';
        $where .= $loc?sprintf(' AND (ThietBi.Ref_MaKhuVuc IN (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))', $loc->lft, $loc->rgt):'';
        $where .= ($maintTypeIOID && is_numeric($maintTypeIOID))?sprintf(' AND PhieuBaoTri.Ref_LoaiBaoTri = %1$d', $maintTypeIOID):'';
        $where .= ($maintTypeIOIDArr && is_array($maintTypeIOIDArr))?sprintf(' AND PhieuBaoTri.Ref_LoaiBaoTri in (%1$s)', implode(',', $maintTypeIOIDArr)):'';
        $where .= ($employeeTaskIOID)?sprintf(' AND PhieuBaoTri.IFID_M759 in (SELECT IFID_M759 FROM OCongViecBTPBT WHERE ifnull(Ref_NguoiThucHien, 0) = %1$d ) ', $employeeTaskIOID):'';
        $where .= ($status)?sprintf(' AND iform.Status = %1$d  ', $status):'';
		$where .= ($eqIOID)?sprintf(' AND ThietBi.IOID = %1$d  ', $eqIOID):'';
        $wc     = $workcenter?$this->_o_DB->fetchOne(sprintf('SELECT * FROM ODonViSanXuat WHERE IOID = %1$d', $workcenter)):'';
        $where .= $wc?sprintf(' AND (PhieuBaoTri.Ref_MaDVBT IN (select IOID from ODonViSanXuat where lft>= %1$d and  rgt <= %2$d))', $wc->lft, $wc->rgt):'';

        $sql = sprintf('
			SELECT qsws.*, PhieuBaoTri.*, LoaiBaoTri.Loai AS LoaiBaoTri, LoaiBaoTri.LoaiBaoTri AS Loai, ChuKy.ChuKy
			FROM OPhieuBaoTri AS PhieuBaoTri
			INNER JOIN ODanhSachThietBi AS ThietBi ON PhieuBaoTri.Ref_MaThietBi = ThietBi.IOID
			INNER JOIN qsiforms AS iform ON iform.IFID = PhieuBaoTri.IFID_M759
			LEFT JOIN OChuKyBaoTri AS ChuKy ON ChuKy.IOID = PhieuBaoTri.Ref_ChuKy  
			LEFT JOIN OPhanLoaiBaoTri AS LoaiBaoTri ON LoaiBaoTri.IOID = PhieuBaoTri.Ref_LoaiBaoTri
			LEFT JOIN qsworkflows AS qsw ON qsw.FormCode = iform.FormCode
			LEFT JOIN qsworkflowsteps AS qsws ON qsw.WFID = qsws.WFID AND iform.Status = qsws.StepNo
			WHERE PhieuBaoTri.DeptID IN (%2$s) %1$s
			ORDER BY PhieuBaoTri.NgayBatDauDuKien DESC, PhieuBaoTri.SoPhieu, PhieuBaoTri.MaThietBi ', $where, $this->_user->user_dept_list);

        // echo '<Pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    // @refactor
    public function getMaterials(
        $ifid = 0
        , $orderIOID = 0
        , $mStart = ''
        , $mEnd = ''
        , $maintainTypeIOID = 0
        , $arrMaintainTypeIOID = array()
        , $locationIOID = 0
        , $equipTypeIOID = 0
        , $equipGroupIOID = 0
        , $materialIOID = 0
    )
    {
        $where  = '';
        $where .= ($ifid && is_numeric($ifid))?sprintf(' AND VatTu.IFID_M759 = %1$d ', $ifid):'';
        $where .= ($ifid && is_array($ifid))?sprintf(' AND VatTu.IFID_M759 in (%1$s) ', implode(', ', $ifid)):'';
        $where .= ($orderIOID)?sprintf(' AND PhieuBT.IOID = %1$d ', $orderIOID):'';
        $where .= ($mStart && $mEnd)?sprintf(' AND PhieuBT.NgayBatDau BETWEEN %1$s AND %2$s', $this->_o_DB->quote($mStart), $this->_o_DB->quote($mEnd)):'';
        $where .= ($maintainTypeIOID)?sprintf(' and PhieuBT.Ref_LoaiBaoTri = %1$d ', $maintainTypeIOID):'';
        $where .= (count($arrMaintainTypeIOID))?sprintf(' and PhieuBT.Ref_LoaiBaoTri IN (%1$s) ', implode(', ', $arrMaintainTypeIOID)):'';

        $loc    = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locationIOID));
        $where .= $loc?sprintf(' AND (ThietBi.Ref_MaKhuVuc IN (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))', $loc->lft, $loc->rgt):'';
        $type   = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OLoaiThietBi WHERE IOID = %1$d', $equipTypeIOID));
        $where .= $type?sprintf(' AND (ThietBi.Ref_LoaiThietBi IN (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d))', $type->lft, $type->rgt):'';
        $where .= ($equipGroupIOID)?sprintf(' AND IFNULL(ThietBi.Ref_NhomThietBi, 0) = %1$d  ', $equipGroupIOID):'';
        $where .= ($materialIOID)?sprintf(' AND IFNULL(VatTu.Ref_MaVatTu = %1$d, 0)  ', $materialIOID):'';

        $sql = sprintf('
            SELECT
                VatTu.*
                , PhieuBT.*
                , VatTu.IOID AS MaterialIOID
                , PhieuBT.IOID AS WorkOrderIOID
                , VatTu.Ngay AS MaterialDate
            FROM OVatTuPBT AS VatTu
            INNER JOIN OPhieuBaoTri AS PhieuBT ON IFNULL(VatTu.IFID_M759, 0) = IFNULL(PhieuBT.IFID_M759, 0)
            INNER JOIN ODanhSachThietBi AS ThietBi on PhieuBT.Ref_MaThietBi = ThietBi.IOID
            WHERE 1=1 %1$s
        ', $where);
		// echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getTasksByIFID($orderIFIDArr = array(), $employeeIOID = 0)
    {
        $orderIFIDArr[] = 0;
        $where          = $employeeIOID?sprintf(' AND cv.Ref_NguoiThucHien = %1$d ', $employeeIOID):'';

        $sql = sprintf('
            SELECT
                cv.*
                , cv.IFID_M759 AS IFID
                , (
                    select group_concat(concat(Vitri,\' - \',BoPhan) SEPARATOR \'<br>\')
                    from OCauTrucThietBi as t
                    where t.IFID_M705 = ct.IFID_M705 and t.lft < ifnull(ct.lft,0) and t.rgt > ifnull(ct.rgt,0)
                    order by t.lft
                    limit 1
                ) as BoPhanCha
            FROM OCongViecBTPBT AS cv
            LEFT JOIN OCauTrucThietBi as ct on ct.IOID = cv.Ref_ViTri
            WHERE cv.IFID_M759 in (%1$s) %2$s
            ORDER BY cv.IFID_M759, cv.Ref_ViTri, cv.IOID
            LIMIT 100000
		', implode(', ', $orderIFIDArr), $where);

        //echo '<pre>';echo $sql;die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getMaterialsByIFID($orderIFIDArr = array())
    {
        $orderIFIDArr[] = 0;
        $sql = sprintf('
            SELECT vt.*, vt.IFID_M759 AS IFID, sp.DacTinhKyThuat
            FROM OVatTuPBT AS vt
            INNER JOIN OSanPham AS sp ON vt.Ref_MaVatTu = sp.IOID
            WHERE vt.IFID_M759 in (%1$s)
            ORDER BY vt.IFID_M759, vt.Ref_ViTri
            LIMIT 100000'
		, implode(', ', $orderIFIDArr));
        return $this->_o_DB->fetchAll($sql);
    }

    public function getMaterialsByIFIDOrderByDate($orderIFIDArr = array())
    {
        $orderIFIDArr[] = 0;
        $sql = sprintf('
            SELECT vt.*, vt.IFID_M759 AS IFID, sp.DacTinhKyThuat, CongViec.GhiChu
            FROM OVatTuPBT AS vt
            INNER JOIN OSanPham AS sp ON vt.Ref_MaVatTu = sp.IOID
            LEFT JOIN OCongViecBTPBT AS CongViec ON IFNULL(vt.Ref_CongViec, 0) = CongViec.IOID
            WHERE vt.IFID_M759 in (%1$s)
            ORDER BY vt.IFID_M759, vt.Ngay DESC
            LIMIT 100000'
            , implode(', ', $orderIFIDArr));
        return $this->_o_DB->fetchAll($sql);
    }

    public function getMaterialsByIFIDGroupByIFID($orderIFIDArr = array())
    {
        $orderIFIDArr[] = 0;

        $sql = sprintf('
			SELECT
				vt.*
				, vt.IFID_M759 AS IFID
				, group_concat(DISTINCT concat(vt.MaVatTu,\': \', vt.SoLuong, \' \', vt.DonViTinh) SEPARATOR \'<br>\') as VatTu
				, group_concat(DISTINCT concat(vt.TenVatTu,\' (\', vt.MaVatTu ,\') : \', vt.SoLuong, \' \', vt.DonViTinh) SEPARATOR \'<br>\') as VatTu2
			FROM OVatTuPBT AS vt 
			WHERE vt.IFID_M759 in (%1$s)
			GROUP BY vt.IFID_M759
			ORDER BY vt.IFID_M759, vt.Ref_ViTri
			LIMIT 100000
		', implode(', ', $orderIFIDArr));

        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * Number of work orders by status
     * @param date $start YYYY-mm-dd
     * @param date $end YYYY-mm-dd
     */
    public function countWorkOrdersByStatus($start = '' , $end = '')
    {
        $where = ($start && $end)?sprintf('WHERE pbt.NgayBatDau between %1$s and %2$s'
            , $this->_o_DB->quote($start)
            , $this->_o_DB->quote($end)):'';

        $sql = sprintf('
            SELECT
                sum(CASE WHEN ifnull(iform.Status, 0) = 1 THEN 1 ELSE 0 END) AS CountStep1,
                sum(CASE WHEN ifnull(iform.Status, 0) = 2 THEN 1 ELSE 0 END) AS CountStep2,
                sum(CASE WHEN ifnull(iform.Status, 0) = 3 THEN 1 ELSE 0 END) AS CountStep3,
                sum(CASE WHEN ifnull(iform.Status, 0) = 4 THEN 1 ELSE 0 END) AS CountStep4,
                sum(CASE WHEN ifnull(iform.Status, 0) = 5 THEN 1 ELSE 0 END) AS CountStep5
            FROM OPhieuBaoTri AS pbt
            INNER JOIN qsiforms AS iform ON iform.IFID = pbt.IFID_M759
            %1$s
            ', $where);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchOne($sql);
    }

    /**
     * Tim kiem cac cong viec khong nam trong khoang thoi gian cua phieu bao tri
     * @param type $ifid
     * @param type $start
     * @param type $end
     * @return type
     */
    public function getTasksOfWokrOrderNotInOrderRangeTime($ifid, $start = '', $end = '')
    {
        $sql = sprintf('
			SELECT cv1.* 
			FROM 
			(SELECT *
			FROM OPhieuBaoTri AS pbt
			WHERE pbt.IFID_M759 = %1$d) AS pbt1
			INNER JOIN 
			(SELECT *
			FROM OCongViecBTPBT AS cv
			WHERE cv.IFID_M759 = %1$d) AS cv1 ON pbt1.IFID_M759 = cv1.IFID_M759
			WHERE 
			ifnull(cv1.Ngay, \'\') <> \'\' AND
			((cv1.Ngay < %2$s) OR (cv1.Ngay > %3$s AND ifnull(%3$s, \'\') <> \'\'))
		', $ifid
            , $this->_o_DB->quote($start)
            , $this->_o_DB->quote($end));
        return $this->_o_DB->fetchAll($sql);
    }

    public function getInCompletedTasksOfWorkOrder(
        $date = ''
        , $location = 0
        , $maintype  = 0
        , $ifidMO = 0
        , $workCenter = 0)
    {
        $where = '';
        $common = new Qss_Model_Extra_Extra();
        if ($location)
        {
            $locName = $common->getTable(array('*')
                , 'OKhuVuc'
                , array('IOID' => $location)
                , array(), 'NO_LIMIT',  1);
            $where .= sprintf(' and pbt.Ref_MaThietBi in (select IOID from ODanhSachThietBi where Ref_MaKhuVuc in (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))', $locName->lft, $locName->rgt);
        }

        if($date)
        {
            $where .= sprintf('AND NgayYeuCau = %1$s', $this->_o_DB->quote($date) );
        }

        if($maintype)
        {
            $maintypeTemp = Qss_Lib_Extra::changeToArray($maintype);

            $where .= sprintf('
				AND pbt.Ref_LoaiBaoTri in (%1$s)
			', implode(',', $maintypeTemp));
        }

        if($ifidMO)
        {
            $where .= sprintf('
				AND pbt.IFID_M759 = %1$d
			', $ifidMO);
        }
        if($workCenter)
        {
            $where .= sprintf('
				AND ifnull(pbt.Ref_MaDVBT, 0) = %1$d
			', $workCenter);
        }
        $sql = sprintf('
						select 
						pbt.*	
						, cv.BoPhan
						, cv.ViTri
						, cv.Ref_ViTri						
						, cv.MoTa AS MoTaCongViec
						, cv.GhiChu AS GhiChuCongViec
						, cv.ThucHien AS Dat
						from OPhieuBaoTri as pbt
						inner join qsiforms on qsiforms.IFID = pbt.IFID_M759
						left join OCongViecBTPBT as cv on cv.IFID_M759 = pbt.IFID_M759
						where 
						qsiforms.Status < 4 AND ifnull(cv.ThucHien, 0) = 0
						%1$s
						order by pbt.IOID desc, pbt.Ref_MaThietBi, cv.Ref_ViTri
						limit 8000;
            			', $where);
        return $this->_o_DB->fetchAll($sql);
    }

    public function countWorkOrderByEquip($eqIOID)
    {
        $sql = sprintf('
            SELECT 
                sum( case when ifnull(pbt.Ref_MaNguyenNhanSuCo ,0) = 0 then 0 else 1  end ) AS NumOfBreak
                , count(1) AS NumOfMaintain
                , max(Ngay) AS MaxStart
                , min(Ngay) AS MinStart
            FROM OPhieuBaoTri AS pbt
            WHERE pbt.Ref_MaThietBi = %1$d
        ', $eqIOID);
        return $this->_o_DB->fetchOne($sql);
    }

    public function getClosedWorkOrderByEquipment($eqID)
    {
        $sql = sprintf('
			SELECT
				pbt.*,
                plbt.Loai AS LoaiBaoTri,
				plbt.LoaiBaoTri AS Loai,
				qsws.StepNo,
				qsws.Color
			FROM
				OPhieuBaoTri AS pbt
			INNER JOIN qsiforms AS qsiforms ON qsiforms.IFID = pbt.IFID_M759
			LEFT JOIN OPhanLoaiBaoTri AS plbt ON plbt.IOID = pbt.Ref_LoaiBaoTri
			LEFT JOIN qsworkflows AS qsw ON qsw.FormCode = qsiforms.FormCode
			LEFT JOIN qsworkflowsteps AS qsws ON qsw.WFID = qsws.WFID
				AND qsiforms.Status = qsws.StepNo
			WHERE 
			IFNULL(qsw.Actived, 0) = 1 AND qsiforms.Status = 4  
			and pbt.Ref_MaThietBi = %1$d'
            /* ORDER BY */
            , $eqID);
        return $this->_o_DB->fetchAll($sql);
    }

    public function checkWorkOrderExists($ioid
        , $EquipID
        , $DateSql
        , $componentID
        , $chukyID)
    {
        $date = date_create($DateSql);
        $day = $date->format('d');
        $month = $date->format('m');
        $wday = $date->format('w');
        $year = $date->format('Y');
        $weekno = $date->format('W');
        $sql = sprintf('select pbt.* from OPhieuBaoTri as pbt
				inner join OChuKyBaoTri as chuky
				on chuky.IOID = pbt.Ref_ChuKy
				where 
				    ifnull(Ref_MaThietBi,0) = %1$d 
					and ifnull(Ref_BoPhan,0) = %3$d 
					and ifnull(Ref_ChuKy,0) = %4$d
					and (
						(chuky.KyBaoDuong = \'D\' AND ((chuky.LapLai = 1 and pbt.NgayYeuCau = %2$s) or (chuky.LapLai > 1 and pbt.NgayYeuCau between DATE_ADD(%2$s, INTERVAL -(chuky.LapLai-1)/2 DAY) and DATE_ADD(%2$s, INTERVAL (chuky.LapLai-1)/2 DAY))))
	                    or (chuky.KyBaoDuong = \'W\' AND ((chuky.LapLai = 1 and %5$d = WEEK(pbt.NgayYeuCau, 3) AND  %7$d = YEAR(pbt.NgayYeuCau)) or (chuky.LapLai > 1 and pbt.NgayYeuCau between DATE_ADD(%2$s, INTERVAL -(chuky.LapLai-1)/2 WEEK) and DATE_ADD(%2$s, INTERVAL (chuky.LapLai-1)/2 WEEK))))
	                    or (chuky.KyBaoDuong = \'M\' AND ((chuky.LapLai = 1 and %6$d = MONTH(pbt.NgayYeuCau) AND  %7$d = YEAR(pbt.NgayYeuCau)) or (chuky.LapLai > 1 and pbt.NgayYeuCau between DATE_ADD(%2$s, INTERVAL -(chuky.LapLai-1)/2 MONTH) and DATE_ADD(%2$s, INTERVAL (chuky.LapLai-1)/2 MONTH))))
	                    or (chuky.KyBaoDuong = \'Y\' AND  ((chuky.LapLai = 1 and %7$d = YEAR(pbt.NgayYeuCau)) or  (chuky.LapLai > 1 and pbt.NgayYeuCau between DATE_ADD(%2$s, INTERVAL -(chuky.LapLai-1)/2 YEAR) and DATE_ADD(%2$s, INTERVAL (chuky.LapLai-1)/2 YEAR))))
						)
					and pbt.IOID != %8$d
					'
            , $EquipID
            , $this->_o_DB->quote($date->format('Y-m-d'))
            , $componentID
            , $chukyID
            , $weekno
            , $month
            , $year
            , $ioid);
        return $this->_o_DB->fetchOne($sql);
    }

	/**
	 * @param $start
	 * @param $end
	 * @param int $locID
	 * @param int $eqGroupID
	 * @param int $eqTypeID
	 * @return mixed
	 */
    public function getWorkOrderHistory(
		$start
		, $end
		, $locID = 0
		, $eqGroupID = 0
		, $eqTypeID = 0
        , $eqIOID = 0)
    {
        // Trong hoa phat doi tuong vattu dung chung giua pbt m759 va ke hoach m724

        $common = new Qss_Model_Extra_Extra();
        $lang  = Qss_Translation::getInstance()->getLanguage();
        $lang  = ($lang == 'vn') ? '' : '_' . $lang;
        $where = '';

        $where .= $eqIOID?sprintf(' AND IFNULL(DSTB.IOID, 0) = %1$d', $eqIOID):'';

        if ($locID)
        {
            $locName = $common->getTable(array('*'), 'OKhuVuc', array('IOID' => $locID), array(), 'NO_LIMIT', 1);
            if ($locName)
			{
				$where .= sprintf('
					and DSTB.Ref_MaKhuVuc in (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d)'
				, $locName->lft, $locName->rgt);
			}

        }

        if ($eqGroupID)
        {
            $where .= sprintf(' and DSTB.Ref_NhomThietBi = %1$d ', $eqGroupID);
        }

//        if (Qss_Lib_Extra::checkFieldExists('ODanhSachThietBi', 'Ref_LoaiThietBi') && $eqTypeID)
//        {
//            $where .= sprintf(' and DSTB.Ref_LoaiThietBi = %1$d ', $eqTypeID);
//        }

		if (is_numeric($eqTypeID) && $eqTypeID)
		{
			$sql     = sprintf('select * from OLoaiThietBi where IOID = %1$d', $eqTypeID);
			$eqTypes = $this->_o_DB->fetchOne($sql);

			if ($eqTypes)
			{
				$where .= sprintf('
					and (DSTB.Ref_LoaiThietBi IN  (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d)) '
				,$eqTypes->lft, $eqTypes->rgt);
			}
		}


//        $startdate = date_create($start);
//        $enddate = date_create($end);
//        $sqltemp = sprintf('CREATE TEMPORARY TABLE tmp
//					(Ref_MaSanPham int NOT NULL,
//        			Thang int NOT NULL,
//        			Gia int NOT NULL)
//        			ENGINE = MEMORY');
//        $this->_o_DB->execute($sqltemp);
//        while ($startdate < $enddate)
//        {
//            $tablename = 'tblcost'.$startdate->format('m').$startdate->format('Y');
//            if($this->_o_DB->tableExists($tablename))
//            {
//                $sqltemp = sprintf('insert into tmp select Ref_MaSanPham,Gia,%2$d
//							from %1$s'
//                    ,$tablename
//                    ,$startdate->format('m'));
//                $this->_o_DB->execute($sqltemp);
//            }
//            $startdate = Qss_Lib_Date::add_date($startdate, 1,'month');
//        }

        $sql = sprintf('
			SELECT
                PBT.*              
                , KhuVuc.Ten AS TenKhuVuc
				, PBT.SoPhieu as DocNo
				, PBT.Ngay as DocDate
				, DSTB.NhomThietBi
				, DSTB.LoaiThietBi
				, ifnull(QSWS.Name%3$s, \'\') as Step
				, ifnull(QSWS.Color, \'\') as Class
				, ChiPhi.ChiPhiDichVu AS GiaDichVu
				, ChiPhi.ChiPhiNhanCong AS GiaNhanCong
				, ChiPhi.ChiPhiVatTu AS GiaVatTu
				, ChiPhi.ChiPhiPhatSinh
				, ChiPhi.ChiPhiThemGio
				, 1 as Type
			FROM OPhieuBaoTri as PBT
			INNER JOIN ODanhSachThietBi as DSTB on DSTB.IOID = PBT.Ref_MaThietBi
			INNER JOIN qsiforms as QSI ON QSI.IFID = PBT.IFID_M759
			LEFT JOIN qsworkflows as QSW ON QSW.FormCode = QSI.FormCode AND Actived = 1
			LEFT JOIN qsworkflowsteps as QSWS  ON QSWS.WFID = QSW.WFID AND QSI.Status = QSWS.StepNo			
			LEFT JOIN OChiPhiPBT AS ChiPhi ON PBT.IFID_M759 = ChiPhi.IFID_M759
			LEFT JOIN OKhuVuc AS KhuVuc ON DSTB.Ref_MaKhuVuc = KhuVuc.IOID
			WHERE
			(PBT.Ngay BETWEEN %1$s AND %2$s) %4$s
			GROUP BY PBT.IOID
			ORDER BY PBT.Ref_MaThietBi, PBT.IOID, PBT.Ngay'
            , $this->_o_DB->quote($start)
            , $this->_o_DB->quote($end)
            , $lang
            , $where);
        //echo '<pre>'; print_r($sql); die;

        $retval = $this->_o_DB->fetchAll($sql);
        $sqltemp = sprintf('DROP TABLE IF EXISTS tmp');
        $this->_o_DB->execute($sqltemp);
        return $retval;

    }

    /**
     * Bao cao tuoi tho phu tung
     * @Path: /report/maintenance/maintain/recycle
     * @Module:
     * @todo chi lay phieu bao tri da ket thuc
     * @param int $eqGroup Nhom Thiet Bi
     * @param int $eqType Loai Thiet Bi
     * @param int $eqID Thiet Bi
     */
    public function getMaterialRecycle(
        $start
        , $end
        , $eqGroup = 0
        , $eqType = 0
        , $eqID = 0
        , $itemID = 0
        , $componentID = 0)
    {
        $db     = $this->_o_DB;
        $where  = '';
        $where .= ($start && $end)?sprintf( ' AND ( IFNULL(VatTu.Ngay, PhieuBaoTri.Ngay) BETWEEN %1$s AND %2$s) ', $db->quote($start), $db->quote($end)):'';
        $where .= $eqGroup?sprintf(' AND ThietBi.Ref_NhomThietBi = %1$d', $eqGroup):'';
        $types  = $eqType?$db->fetchOne(sprintf(' select * from OLoaiThietBi where IOID = %1$d ', $eqType)):'';
        $where .= $types?sprintf(' AND ThietBi.Ref_LoaiThietBi IN  (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d ) ', $types->lft, $types->rgt):'';
        $where .= $eqID?sprintf(' AND ThietBi.IOID = %1$d ', $eqID):'';
        $where .= $itemID?sprintf(' AND VatTu.Ref_MaVatTu = %1$d ', $itemID):'';
        $eq     = $eqID?$this->_o_DB->fetchOne(sprintf(' select * FROM ODanhSachThietBi AS ThietBi WHERE IOID = %1$d ', $eqID)):'';
        $eqIFID = $eq?$eq->IFID_M705:0;
        $coms   = ($eqID && $componentID)?$db->fetchOne(sprintf(' select * from OCauTrucThietBi where IOID = %1$d AND IFID_M705 = %2$d ', $componentID, $eqIFID)):'';
        $where .= $coms?sprintf(' AND CauTruc.IOID IN  (select IOID from OCauTrucThietBi where lft>= %1$d and  rgt <= %2$d AND IFID_M705 = %3$d) ', $coms->lft, $coms->rgt, $eqIFID):'';

        $sql = sprintf('
            SELECT
                ThietBi.IOID AS Ref_MaThietBi
                , ThietBi.MaThietBi
                , ThietBi.TenThietBi
                , IFNULL(CauTruc.IOID, 0) AS Ref_ViTri
                , CauTruc.ViTri
                , CauTruc.BoPhan
                , VatTu.Ref_MaVatTu
                , VatTu.MaVatTu
                , VatTu.TenVatTu
                , VatTu.SoLuong
                , IFNULL(VatTu.Ngay, PhieuBaoTri.Ngay) AS NgayThayThe
                , PhieuBaoTri.IFID_M759
                , CauTruc.SoNgayCanhBao
            FROM OPhieuBaoTri AS PhieuBaoTri
            INNER JOIN qsiforms ON PhieuBaoTri.IFID_M759 = qsiforms.IFID
            INNER JOIN ODanhSachThietBi AS ThietBi ON PhieuBaoTri.Ref_MaThietBi = ThietBi.IOID
            INNER JOIN OVatTuPBT AS VatTu ON PhieuBaoTri.IFID_M759 = VatTu.IFID_M759
            LEFT JOIN OCauTrucThietBi AS CauTruc ON ThietBi.IFID_M705 = CauTruc.IFID_M705
                AND IFNULL(VatTu.Ref_ViTri, 0) = CauTruc.IOID
            WHERE qsiforms.Status IN (3,4) -- Phieu bao tri dong hoac hoan thanh
                %1$s
            ORDER BY ThietBi.MaThietBi, CauTruc.ViTri, VatTu.MaVatTu,  IFNULL(VatTu.Ngay, PhieuBaoTri.Ngay) DESC




            /*
            SELECT
                pbt.IFID_M759 AS WIFID
                , pbt.Ngay AS Date
                , pbt.Ref_MaThietBi AS RefEq
                , dstb.`MaThietBi` AS ECode
                , dstb.`TenThietBi` AS EName
                , vt.Ref_BoPhan AS RefComponent
                , cttb.`BoPhan` AS Component
                , cttb.`ViTri` AS Position
                , vt.Ref_MaVatTu AS RefSparepart
                , sp.MaSanPham AS SCode
                , sp.TenSanPham AS SName
                , vt.`SoLuong` AS `Use`
            FROM OPhieuBaoTri AS pbt
            INNER JOIN ODanhSachThietBi AS dstb ON pbt.Ref_MaThietBi = dstb.IOID
            INNER JOIN OVatTuPBT AS vt ON pbt.IFID_M759 = vt.IFID_M759
            INNER JOIN OSanPham AS sp ON vt.Ref_MaVatTu = sp.IOID
            LEFT JOIN OCauTrucThietBi AS cttb ON cttb.`IFID_M705` = dstb.`IFID_M705` AND vt.`Ref_BoPhan` = cttb.`IOID`
            %1$s
            ORDER BY pbt.MaThietBi, pbt.Ngay, pbt.IFID_M759, vt.Ref_BoPhan
            */'
            ,  $where);
        // echo '<pre>'; print_r($sql); die;
        return $db->fetchAll($sql);
    }

    public function getCostFromOutputGroupByLocation($filter)
    {
        // xu ly loi
        $filter['eqs'][] = 0; // loi ko co thiet bi

        $sql = sprintf('
			SELECT
				kv.IOID AS LocID,
				kv.MaKhuVuc AS LocCode,
				kv.Ten AS LocName,
				kv.lft AS LocLeft,
				kv.rgt AS LocRight,
				SUM(ifnull(t.MaterialCost, 0)) AS MaterialCost,
				(SELECT
					count(1)
				FROM
					OKhuVuc AS u
				WHERE
					u.lft <= kv.lft
				AND u.rgt >= kv.rgt
				) AS LEVEL
			FROM
				(
					SELECT
						dstb.Ref_MaKhuVuc AS LocID,
						SUM(ifnull(dsxk.ThanhTien, 0)) AS MaterialCost
					FROM
						OXuatKho AS xk
					INNER JOIN ODanhSachXuatKho AS dsxk ON xk.IFID_M506 = dsxk.IFID_M506
					INNER JOIN OPhieuBaoTri AS Phieu ON xk.Ref_PhieuBaoTri = Phieu.IOID
					INNER JOIN ODanhSachThietBi AS dstb ON Phieu.Ref_MaThietBi = dstb.IOID
					INNER JOIN OLoaiThietBi AS ltb ON dstb.Ref_LoaiThietBi = ltb.IOID
					WHERE
					(xk.NgayChungTu BETWEEN %1$s AND %2$s)
					AND dstb.IOID IN (%3$s)
					GROUP BY dstb.IOID
				) AS t
			RIGHT JOIN OKhuVuc AS kv ON kv.IOID = t.LocID
			GROUP BY
				kv.IOID
			ORDER BY kv.lft				'
            , $this->_o_DB->quote($filter['start'])
            , $this->_o_DB->quote($filter['end'])
            , implode(', ', $filter['eqs']));
        //echo $sql; die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getCostByPeriod(
        $period
        , $start
        , $end
        , $eqTypeIOID = 0
        , $eqGroupIOID = 0
        , $eqIOID = 0
        , $locIOID = 0
        , $limit = 8000)
    {
        $common = new Qss_Model_Extra_Extra();
        // Trong hoa phat doi tuong vattu dung chung giua pbt m759 va ke hoach m724
        //$vattuObject = (_QSS_DATA_FOLDER_ == 'data_hoaphat')?'OVatTu':'OVatTuPBT';

        // join
        $whereOfCorrect = '';
        // where
        $whereOfCorrect = sprintf('(PBT.Ngay BETWEEN %1$s AND %2$s)', $this->_o_DB->quote($start)
            , $this->_o_DB->quote($end));
        // Loc theo nhom thiet bi hay loai thiet bi
        if ($eqTypeIOID)
        {
            $whereOfCorrect .= sprintf(' and DSTB.Ref_LoaiThietBi = %1$d', $eqTypeIOID);
        }

        if ($eqGroupIOID)
        {
            $whereOfCorrect .= sprintf(' and DSTB.Ref_NhomThietBi = %1$d', $eqGroupIOID);
        }

        if ($eqIOID)
        {
            $whereOfCorrect .= sprintf(' and DSTB.IOID = %1$d', $eqIOID);
        }

        if ($locIOID)
        {
            $locName = $common->getTable(array('*')
                , 'OKhuVuc'
                , array('IOID' => $locIOID)
                , array(), 'NO_LIMIT', 1);
            if ($locName)
                $whereOfCorrect .= sprintf(' and PBT.Ref_MaThietBi in (select IOID from ODanhSachThietBi where Ref_MaKhuVuc in (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))', $locName->lft, $locName->rgt);
        }
        // group by
        $groupOfCorrect = 'PBT.Ref_MaThietBi';

        switch ($period)
        {
            case 'D': // day
                // group by
                $groupOfCorrect .= sprintf(', PBT.Ngay');
                break;
            case 'W': // weekday
                // group by
                $groupOfCorrect .= sprintf(', year(PBT.Ngay), week(PBT.Ngay)');
                break;
            case 'M': // month
                $groupOfCorrect .= sprintf(', year(PBT.Ngay),month(PBT.Ngay)');
                break;
            case 'Q': // quarter
                $groupOfCorrect .= sprintf(', year(PBT.Ngay), quarter(PBT.Ngay)');
                break;
            case 'Y': // year
                $groupOfCorrect .= sprintf(', year(PBT.Ngay)');
                break;
        }
/*
        $startdate = date_create($start);
        $enddate = date_create($end);
        $sqltemp = sprintf('CREATE TEMPORARY TABLE tmp
					(Ref_MaSanPham int NOT NULL, 
        			Thang int NOT NULL,
        			Gia bigint NOT NULL)
        			ENGINE = MEMORY');
        $this->_o_DB->execute($sqltemp);
        while ($startdate < $enddate)
        {
            $tablename = 'tblcost'.$startdate->format('m').$startdate->format('Y');
            if($this->_o_DB->tableExists($tablename))
            {
                $sqltemp = sprintf('insert into tmp select Ref_MaSanPham,Gia,%2$d
							from %1$s'
                    ,$tablename
                    ,$startdate->format('m'));
                $this->_o_DB->execute($sqltemp);
            }
            $startdate = Qss_Lib_Date::add_date($startdate, 1,'month');
        }*/

        $sql = sprintf('select PBT.Ref_MaThietBi, PBT.MaThietBi as MaThietBi, PBT.TenThietBi as TenThietBi
                                 ,DSTB.NhomThietBi ,DSTB.Ref_NhomThietBi , 
                                 DSTB.LoaiThietBi 
                                 ,
                                 (sum(ifnull(cp.ChiPhiVatTu,0)) +
                                 sum(ifnull(cp.ChiPhiNhanCong,0)) +
                                 sum(ifnull(cp.ChiPhiDichVu,0)) +
                                 sum(ifnull(cp.ChiPhiThemGio,0)) +
                                 sum(ifnull(cp.ChiPhiPhatSinh,0)) )
                                as ThanhTien
                                 , PBT.Ngay AS Ngay, week(PBT.Ngay) as Tuan, month(PBT.Ngay) as Thang
                                 , quarter(PBT.Ngay) as Quy, year(PBT.Ngay) as Nam
                                     from OPhieuBaoTri as PBT
                                     INNER JOIN ODanhSachThietBi AS DSTB ON PBT.Ref_MaThietBi = DSTB.IOID
                                     left join OChiPhiPBT as cp on cp.IFID_M759 = PBT.IFID_M759
                                     Where %2$s
                                     GROUP BY %3$s
                                     LIMIT %4$s
                            ', $this->_o_DB->quote($end)
            , $whereOfCorrect
            , $groupOfCorrect
            , $limit);
        //echo '<pre>';echo $sql;die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getCostAnalysis($start, $end, $period, $groupBy, $filter = array())
    {
        $common = new Qss_Model_Extra_Extra();
        // Trong hoa phat doi tuong vattu dung chung giua pbt m759 va ke hoach m724
        //$vattuObject = (_QSS_DATA_FOLDER_ == 'data_hoaphat')?'OVatTu':'OVatTuPBT';

        $order = '';
        $where = '';
        switch ($groupBy)
        {
            case 1:// work center
                $order = ' PBT.Ref_MaDVBT ,';
                break;
            case 2:// eq group
                $order = ' DSTB.Ref_NhomThietBi ,';
                break;
        }

        $groupBy = ''; // group by trong sql
        $orderBy = '';
        $datetime = '';

        switch ($period)
        {
            case 'D':
                $groupBy = ' GROUP  BY ' . $order . '  PBT.Ngay ';
                $orderBy = ' ORDER BY  ' . $order . ' PBT.Ngay  DESC';
                $datetime = ' ,PBT.Ngay as Ngay ';
                break;
            case 'W':
                $groupBy = ' GROUP  BY ' . $order . '  year(PBT.Ngay), week(PBT.Ngay,3) ';
                $orderBy = ' ORDER BY  ' . $order . ' year(PBT.Ngay) DESC, week(PBT.Ngay,3)  DESC';
                $datetime = ', year(PBT.Ngay) as Nam, week(PBT.Ngay,3) as Tuan';
                break;
            case 'M':
                $groupBy = ' GROUP  BY ' . $order . '  year(PBT.Ngay),month(PBT.Ngay) ';
                $orderBy = ' ORDER BY  ' . $order . ' year(PBT.Ngay) DESC, month(PBT.Ngay) DESC';
                $datetime = ', year(PBT.Ngay) as Nam, month(PBT.Ngay) as Thang';
                break;
            case 'Q':
                $groupBy = ' GROUP  BY ' . $order . '  year(PBT.Ngay), quarter(PBT.Ngay)  ';
                $orderBy = ' ORDER BY  ' . $order . ' year(PBT.Ngay) DESC, quarter(PBT.Ngay)   DESC';
                $datetime = ', year(PBT.Ngay) as Nam, quarter(PBT.Ngay) as Quy';
                break;
            case 'Y':
                $groupBy = ' GROUP  BY ' . $order . '  year(PBT.Ngay)';
                $orderBy = ' ORDER BY  ' . $order . ' year(PBT.Ngay)  DESC';
                $datetime = ', year(PBT.Ngay) as Nam';
                break;
        }
        // Loc theo nhom thiet bi hay loai thiet bi
        if (isset($filter['eqtype']) && $filter['eqtype'])
        {
            $where .= sprintf(' and DSTB.Ref_LoaiThietBi = %1$d', $filter['eqtype']);
        }

        if (isset($filter['eqgroup']) && $filter['eqgroup'])
        {
            $where .= sprintf(' and DSTB.Ref_NhomThietBi = %1$d', $filter['eqgroup']);
        }

        if (isset($filter['loc']) && $filter['loc'])
        {
            $locName = $common->getTable(array('*')
                , 'OKhuVuc'
                , array('IOID' => $filter['loc'])
                , array(), 'NO_LIMIT',1);
            if ($locName)
                $where .= sprintf(' AND DSTB.Ref_MaKhuVuc
							IN (SELECT IOID FROM OKhuVuc
								WHERE lft >= %1$d AND rgt <= %2$d)'
                    , $locName->lft, $locName->rgt);
        }

        /*$startdate = date_create($start);
        $enddate = date_create($end);
        $sqltemp = sprintf('CREATE TEMPORARY TABLE tmp
					(Ref_MaSanPham int NOT NULL, 
        			Thang int NOT NULL,
        			Gia bigint NOT NULL)
        			ENGINE = MEMORY');
        $this->_o_DB->execute($sqltemp);
        while ($startdate < $enddate)
        {
            $tablename = 'tblcost'.$startdate->format('m').$startdate->format('Y');
            if($this->_o_DB->tableExists($tablename))
            {
                $sqltemp = sprintf('insert into tmp select Ref_MaSanPham,Gia,%2$d
							from %1$s'
                    ,$tablename
                    ,$startdate->format('m'));
                $this->_o_DB->execute($sqltemp);
            }
            $startdate = Qss_Lib_Date::add_date($startdate, 1,'month');
        }*/
        $sql = sprintf('SELECT DSTB.Ref_NhomThietBi, DSTB.NhomThietBi,
                                 sum(ifnull(cp.ChiPhiVatTu,0)) as GiaVatTu,
                                 sum(ifnull(cp.ChiPhiNhanCong,0)) as GiaNhanCong,
                                 sum(ifnull(cp.ChiPhiDichVu,0)) as GiaDichVu,
                                 sum(ifnull(cp.ChiPhiThemGio,0)) as ChiPhiThemGio,
                                 sum(ifnull(cp.ChiPhiPhatSinh,0)) as ChiPhiKhac
                                , PBT.TenDVBT as WorkCenter
                                , PBT.MaDVBT as WorkCenterCode
                                , PBT.Ref_MaDVBT as Ref_WorkCenter, Ref_MaDVBT
                                %3$s
                                FROM OPhieuBaoTri AS PBT
                                INNER JOIN ODanhSachThietBi AS DSTB ON PBT.Ref_MaThietBi = DSTB.IOID
                                left join OChiPhiPBT as cp on cp.IFID_M759 = PBT.IFID_M759
                                WHERE (PBT.Ngay
                                BETWEEN %1$s
                                AND %2$s) %6$s %4$s %5$s'
            , $this->_o_DB->quote($start)
            , $this->_o_DB->quote($end)
            , $datetime, $groupBy, $orderBy, $where
        );
        //echo '<pre>';echo $sql;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getHistoryByEquipment($ioid,$start,$end)
    {
        $sql = sprintf('select pbt.*,
					case when chuky.KyBaoDuong = \'D\' then ceil(TIMESTAMPDIFF(DAY,%2$s,NgayBatDau)/LapLai)
					when chuky.KyBaoDuong = \'W\' then ceil(TIMESTAMPDIFF(WEEK,%2$s,NgayBatDau)/LapLai)
					when chuky.KyBaoDuong = \'M\' then ceil(TIMESTAMPDIFF(MONTH,%2$s,NgayBatDau)/LapLai)
					when chuky.KyBaoDuong = \'Y\' then ceil(TIMESTAMPDIFF(YEAR,%2$s,NgayBatDau)/LapLai)
					end as STT
					from OPhieuBaoTri as pbt
					inner join ODanhSachThietBi as dstb on dstb.IOID = pbt.Ref_MaThietBi
					inner join OChuKyBaoTri as chuky on chuky.IOID = pbt.Ref_ChuKy
					where dstb.IOID = %1$s
					and NgayYeuCau between %2$s and %3$s'
            , $ioid
            , $this->_o_DB->quote($start)
            , $this->_o_DB->quote($end));
        //		echo '<pre>';echo $sql;die;
        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * @module Bao cao tong hop theo doi tuong
     * Tong hop chi phi thong qua xuat kho gop theo tung thiet bi
     * @param date $filter['start']  (R) yeu cau, ngay bat dau (yyyy-mm-dd)
     * @param date $filter['end'] (R) yeu cau, ngay ket thuc (yyyy-mm-dd)
     * @param array $filter['eqs'] (R) yeu cau, mang danh muc thiet bi ([0]=>eq_id)
     * @todo can loc theo phieu xuat kho da ket thuc
     */
    public function getCostFromOutputGroupByEquip($filter)
    {
        // xu ly loi
        $filter['eqs'][] = 0; // loi ko co thiet bi

        $sql = sprintf('
			SELECT 
			dstb.IOID AS EqID
			, dstb.MaThietBi AS EqCode
			, ltb.IOID AS EqTypeID
			, ltb.TenLoai AS EqType
			, ntb.IOID AS EqGroupID
			, ntb.LoaiThietBi AS EqGroup
			, SUM(ifnull(dsxk.ThanhTien, 0)) AS MaterialCost
			FROM OXuatKho AS xk
			INNER JOIN ODanhSachXuatKho AS dsxk ON xk.IFID_M506 = dsxk.IFID_M506
			INNER JOIN OPhieuBaoTri AS Phieu ON xk.Ref_PhieuBaoTri = Phieu.IOID
			INNER JOIN ODanhSachThietBi AS dstb ON Phieu.Ref_MaThietBi = dstb.IOID
			INNER JOIN OLoaiThietBi AS ltb ON dstb.Ref_LoaiThietBi = ltb.IOID
			INNER JOIN ONhomThietBi AS ntb ON dstb.Ref_NhomThietBi = ntb.IOID
			WHERE 
				(xk.NgayChungTu BETWEEN %1$s AND %2$s)
				AND dstb.IOID IN (%3$s)
			GROUP BY dstb.IOID
			ORDER BY ltb.IOID	
		'
            , $this->_o_DB->quote($filter['start'])
            , $this->_o_DB->quote($filter['end'])
            , implode(', ', $filter['eqs']));
		//echo '<Pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * @module Bao cao tong hop theo doi tuong
     * Tong hop chi phi thong qua xuat kho gop theo tung don vi thuc hien
     * @param date $filter['start']  (R) yeu cau, ngay bat dau (yyyy-mm-dd)
     * @param date $filter['end'] (R) yeu cau, ngay ket thuc (yyyy-mm-dd)
     * @param array $filter['eqs'] (R) yeu cau, mang danh muc thiet bi ([0]=>eq_id)
     * @todo can loc theo phieu xuat kho da ket thuc
     */

    public function getCostFromOutputGroupByWorkcenter($filter)
    {
        // xu ly loi
        $filter['eqs'][] = 0; // loi ko co thiet bi

        $sql = sprintf('
			SELECT 
			dvth.IOID AS WCID
			, dvth.Ma AS WCCode
			, dvth.Ten AS WCName
			, SUM(ifnull(dsxk.ThanhTien, 0)) AS MaterialCost
			FROM OXuatKho AS xk
			INNER JOIN ODanhSachXuatKho AS dsxk ON xk.IFID_M506 = dsxk.IFID_M506
			INNER JOIN OPhieuBaoTri AS Phieu ON xk.Ref_PhieuBaoTri = Phieu.IOID
			INNER JOIN ODanhSachThietBi AS dstb ON Phieu.Ref_MaThietBi = dstb.IOID
			INNER JOIN ODonViSanXuat AS dvth ON xk.Ref_DonViThucHien = dvth.IOID
			WHERE 
				(xk.NgayChungTu BETWEEN %1$s AND %2$s)
				AND dstb.IOID IN (%3$s)
			GROUP BY xk.Ref_DonViThucHien	
		'
            , $this->_o_DB->quote($filter['start'])
            , $this->_o_DB->quote($filter['end'])
            , implode(', ', $filter['eqs']));
			//echo '<Pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getCalculateCostByID($id)
    {
        $sql = sprintf('
            SELECT
                IFNULL(ChiPhiVatTu.ChiPhiVatTu, 0) AS ChiPhiVatTu,
                IFNULL(ChiPhiVatTu.ChiPhiVatTuDK, 0) AS ChiPhiVatTuDK,
                IFNULL(ChiPhiNhanCong.ChiPhiNhanCong, 0) AS ChiPhiNhanCong,
                IFNULL(ChiPhiNhanCong.ChiPhiNhanCongDK, 0) AS ChiPhiNhanCongDK,
                IFNULL(ChiPhiDichVu.ChiPhiDichVu, 0) AS ChiPhiDichVu,
                IFNULL(ChiPhiDichVu.ChiPhiDichVuDK, 0) AS ChiPhiDichVuDK
            FROM OPhieuBaoTri AS PhieuBaoTri

            INNER JOIN (
				SELECT
					SUM(
					    CASE WHEN TinhLuong = 1 THEN (ThoiGian/60) * LuongTheoGio
					    WHEN  TinhLuong = 2 THEN NhanCong * LuongTheoCong
					    ELSE NhanCong * LuongTheoCong  END
                    ) AS ChiPhiNhanCong
					, SUM(
					    CASE WHEN TinhLuong = 1 THEN (ThoiGianDuKien/60) * LuongTheoGio
					    WHEN  TinhLuong = 2 THEN NhanCongDuKien * LuongTheoCong
					    ELSE NhanCongDuKien * LuongTheoCong  END
                    ) AS ChiPhiNhanCongDK
				FROM (
					SELECT *
					FROM (
						SELECT
							CongViec.IOID, CongViec.ThoiGian, CongViec.ThoiGianDuKien, CongViec.NhanCong, CongViec.NhanCongDuKien, BangLuong.LuongTheoGio, BangLuong.LuongTheoCong, PhanLoaiCongViec.TinhLuong
						FROM OCongViecBTPBT AS CongViec
						INNER JOIN OPhieuBaoTri AS PhieuBaoTri ON CongViec.IFID_M759 = PhieuBaoTri.IFID_M759
						INNER JOIN OCongViecBaoTri AS PhanLoaiCongViec ON CongViec.Ref_Ten = PhanLoaiCongViec.IOID
						INNER JOIN OBangLuongTheoCongViec AS BangLuong ON PhanLoaiCongViec.IFID_M714 = BangLuong.IFID_M714
							AND IF(IFNULL(CongViec.Ngay, \'\') != \'\', CongViec.Ngay, PhieuBaoTri.NgayBatDau) >= BangLuong.Ngay
							AND (IFNULL(BangLuong.Ref_DonVi, 0) = 0 OR IFNULL(BangLuong.Ref_DonVi, 0) = PhieuBaoTri.DeptID)
						WHERE CongViec.IFID_M759 = %1$d
						ORDER BY CongViec.IOID, BangLuong.Ngay DESC, BangLuong.IOID DESC
					) AS CongViec
					GROUP BY CongViec.IOID
				) AS ChiPhiNhanCong
            ) AS ChiPhiNhanCong

            INNER JOIN (
                SELECT
                     SUM( IFNULL(VatTu.SoLuong, 0) * IFNULL(DSXuatKho.DonGia, 0)) AS ChiPhiVatTu
                     , SUM(IFNULL(VatTu.SoLuongDuKien,0) * IFNULL(DSXuatKho.DonGia, 0)) as ChiPhiVatTuDK
                FROM OVatTuPBT AS VatTu
                INNER JOIN OPhieuBaoTri AS PhieuBaoTri ON VatTu.IFID_M759 = PhieuBaoTri.IFID_M759
                INNER JOIN OXuatKho AS XuatKho ON PhieuBaoTri.IOID = IFNULL(XuatKho.Ref_PhieuBaoTri, 0)
                INNER JOIN ODanhSachXuatKho AS DSXuatKho ON XuatKho.IFID_M506 = DSXuatKho.IFID_M506
                    AND VatTu.Ref_MaVatTu = DSXuatKho.Ref_MaSP
                    AND VatTu.Ref_DonViTinh = DSXuatKho.Ref_DonViTinh
                WHERE VatTu.IFID_M759 = %1$d
            ) AS ChiPhiVatTu

            INNER JOIN (
				SELECT
					SUM(ifnull(DichVu.ChiPhi,0)) as ChiPhiDichVu
					, SUM(ifnull(DichVu.ChiPhiDuKien,0)) as ChiPhiDichVuDK
				FROM ODichVuPBT AS DichVu
				WHERE DichVu.IFID_M759 = %1$d
            ) AS ChiPhiDichVu
            WHERE PhieuBaoTri.IFID_M759 = %1$d'
            , $id);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchOne($sql);
    }

    public function updateCost($id,$chiphithemgio=0,$chiphiphatsinh=0,$ghichu='')
    {
    	$cost = $this->getCalculateCostByID($id);
    	if($cost)
    	{
    		$sql = sprintf('select 1 from OChiPhiPBT where IFID_M759=%1$d'
    				, $id);
    		$check = $this->_o_DB->fetchOne($sql);
    		if($check)
    		{
//    			$sql = sprintf('update OChiPhiPBT set
//    						ChiPhiVatTu = %2$.0f,
//    						ChiPhiNhanCong = %3$.0f,
//    						ChiPhiDichVu = %4$.0f,
//    						ChiPhiVatTuDK = %5$.0f,
//    						ChiPhiNhanCongDK = %6$.0f,
//    						ChiPhiDichVuDK = %7$.0f,
//    						ChiPhiPhatSinh = %8$.0f,
//    						ChiPhiThemGio = %9$.0f,
//    						GhiChu = %10$s
//			    			where IFID_M759 = %1$d'
//    				, $id
//    				, $cost->ChiPhiVatTu
//    				, $cost->ChiPhiNhanCong
//    				, $cost->ChiPhiDichVu
//    				, $cost->ChiPhiVatTuDK
//    				, $cost->ChiPhiNhanCongDK
//    				, $cost->ChiPhiDichVuDK
//    				, $chiphiphatsinh * 1000
//    				, $chiphithemgio * 1000
//    				, $this->_o_DB->quote($ghichu));

                $sql = sprintf('update OChiPhiPBT set
    						ChiPhiVatTu = %2$.0f,
    						ChiPhiNhanCong = %3$.0f,
    						ChiPhiDichVu = %4$.0f,
    						ChiPhiPhatSinh = %5$.0f,
    						ChiPhiThemGio = %6$.0f,
    						GhiChu = %7$s
			    			where IFID_M759 = %1$d'
                    , $id
                    , $cost->ChiPhiVatTu
                    , $cost->ChiPhiNhanCong
                    , $cost->ChiPhiDichVu
                    , $chiphiphatsinh * 1000
                    , $chiphithemgio * 1000
                    , $this->_o_DB->quote($ghichu));
    		}
    		else
    		{
//    			$sql = sprintf('INSERT INTO OChiPhiPBT(IFID_M759,ChiPhiVatTu,ChiPhiNhanCong,ChiPhiDichVu,ChiPhiVatTuDK,ChiPhiNhanCongDK,ChiPhiDichVuDK,ChiPhiPhatSinh,ChiPhiThemGio,GhiChu)
//			    			VALUES(%1$d,%2$.0f,%3$.0f,%4$.0f,%5$.0f,%6$.0f,%7$.0f,%8$.0f,%9$.0f,%10$s)'
//    				, $id
//    				, $cost->ChiPhiVatTu
//    				, $cost->ChiPhiNhanCong
//    				, $cost->ChiPhiDichVu
//    				, $cost->ChiPhiVatTuDK
//    				, $cost->ChiPhiNhanCongDK
//    				, $cost->ChiPhiDichVuDK
//    				, $chiphiphatsinh * 1000
//    				, $chiphithemgio * 1000
//    				, $this->_o_DB->quote($ghichu));

                $sql = sprintf('INSERT INTO OChiPhiPBT(IFID_M759,ChiPhiVatTu,ChiPhiNhanCong,ChiPhiDichVu,ChiPhiPhatSinh,ChiPhiThemGio,GhiChu)
			    			VALUES(%1$d,%2$.0f,%3$.0f,%4$.0f,%5$.0f,%6$.0f,%7$s)'
                    , $id
                    , $cost->ChiPhiVatTu
                    , $cost->ChiPhiNhanCong
                    , $cost->ChiPhiDichVu
                    , $chiphiphatsinh * 1000
                    , $chiphithemgio * 1000
                    , $this->_o_DB->quote($ghichu));
    		}
			//echo '<pre>';echo $sql;die;
    		$this->_o_DB->execute($sql);
    	}
		 		   	
    }

    public function getCostByID($id)
    {
        $sql = sprintf('SELECT  *,ifnull(ChiPhiVatTu,0)+ ifnull(ChiPhiNhanCong,0)+ifnull(ChiPhiDichVu,0)+ifnull(ChiPhiThemGio,0)+ifnull(ChiPhiPhatSinh,0) as TongChiPhi
         						,ifnull(ChiPhiVatTuDK,0)+ ifnull(ChiPhiNhanCongDK,0)+ifnull(ChiPhiDichVuDK,0) as TongChiPhiDK
        						from OChiPhiPBT
                                WHERE IFID_M759 = %1$d'
            , $id);
		return $this->_o_DB->fetchOne($sql);
    }

    public function getResourceByDate($start, $end, $workcenter = 0)
	{
		$where = '';
		if($workcenter)
		{
			$where = sprintf('and Ref_MaDVBT = %1$d',$workcenter);
		}
		$sql = sprintf('
			SELECT
			cv.Ngay,
			sum(cv.ThoiGian) as ThoiGian
			FROM OPhieuBaoTri AS pbt
			INNER JOIN OCongViecBTPBT as cv on cv.IFID_M759 = pbt.IFID_M759
			WHERE 
				cv.Ngay between %1$s and %2$s %3$s
			GROUP BY cv.Ngay	
		'
		, $this->_o_DB->quote($start)
		, $this->_o_DB->quote($end)
		, $where);
		return $this->_o_DB->fetchAll($sql);		
	}

	/**
	 * Dự trù vật tư theo các phiếu bảo trì soạn thảo & ban hành trong 1 khoảng thời gian.
	 * @param $startdate
	 * @param $enddate
	 * @return mixed
	 */
	public function getRequiredMaterialsInRangeDate($startdate, $enddate)
	{
		$sqlwhere = '';

		$sqlwhere .= sprintf('
		and
		if(ifnull(NgayBatDau, \'\') != \'\' AND ifnull(NgayBatDau, \'\') != \'0000-00-00\',NgayBatDau,NgayYeuCau)
		<= %2$s
		and
		if(ifnull(Phieu.Ngay, \'\') != \'\' AND ifnull(Phieu.Ngay, \'\') != \'0000-00-00\',Phieu.Ngay,if(ifnull(NgayDuKienHoanThanh, \'\') != \'\' AND ifnull(Phieu.NgayDuKienHoanThanh, \'\') != \'0000-00-00\',Phieu.NgayDuKienHoanThanh,NgayBatDau))
		>=%1$s'
			,$this->_o_DB->quote($startdate),$this->_o_DB->quote($enddate));

		$sqlwhere .= sprintf(' and Phieu.DeptID in (%1$s) ', $this->_user->user_dept_id . ',' . $this->_user->user_dept_list);

		// Luu y cau sql co dieu kien va join giong lay vat tu phieu bao tri cua cau sql ben duoi
		$sqlItems = sprintf('
			SELECT VatTu.Ref_MaVatTu
			from OPhieuBaoTri as Phieu
			INNER JOIN OVatTuPBT AS VatTu ON Phieu.IFID_M759 = VatTu.IFID_M759
			INNER JOIN qsiforms AS iform ON Phieu.IFID_M759 = iform.IFID
			WHERE (ifnull(Phieu.IFID_M759, 0) != 0) AND iform.Status in (1,2)  %1$s
		', $sqlwhere);

		$dataItems = $this->_o_DB->fetchAll($sqlItems);
		$items     = array(0);

		if($dataItems)
		{
			foreach($dataItems as $item)
			{
				$items[] = $item->Ref_MaVatTu;
			}
		}

		$sql = sprintf('
			SELECT
				VatTu.*
				, IFNULL(Kho.SoLuongHC, 0) AS TongTonKho
				, IFNULL(YeuCauMuaSam.TongYeuCauConLai, 0) AS TongYeuCauMua
			FROM
			(
				SELECT
					VatTu.*
					, SUM( IFNULL(VatTu.SoLuong,0) * IFNULL(DonViTinh.HeSoQuyDoi, 0)) AS TongSoLuong
				from OPhieuBaoTri as Phieu
				INNER JOIN OVatTuPBT AS VatTu ON Phieu.IFID_M759 = VatTu.IFID_M759
				INNER JOIN ODonViTinhSP AS DonViTinh ON IFNULL(VatTu.Ref_DonViTinh, 0) = DonViTinh.IOID
				INNER JOIN qsiforms AS iform ON Phieu.IFID_M759 = iform.IFID
				WHERE iform.Status in (1,2) %1$s
				GROUP BY VatTu.Ref_MaVatTu				
			) AS VatTu
            LEFT JOIN (
                SELECT 
                  SUM(IF( IFNULL(ODanhSachKho.IOID, 0) != 0, IFNULL(OKho.SoLuongHC,0) * IFNULL(ODonViTinhSP.HeSoQuyDoi, 0), 0)) AS  SoLuongHC
                  , OSanPham.IOID AS Ref_MaSanPham
				FROM OKho
                INNER JOIN ODanhSachKho ON OKho.Ref_Kho = ODanhSachKho.IOID AND ODanhSachKho.LoaiKho = %3$s
				INNER JOIN OSanPham ON  OSanPham.IOID = OKho.Ref_MaSP 
				INNER JOIN ODonViTinhSP ON OSanPham.IFID_M113 = ODonViTinhSP.IFID_M113 AND ODonViTinhSP.IOID = OKho.Ref_DonViTinh
				WHERE OSanPham.IOID IN (%2$s)
				GROUP BY OKho.Ref_MaSP            
            ) AS Kho ON VatTu.Ref_MaVatTu = Kho.Ref_MaSanPham            
            LEFT JOIN
            (
                SELECT 
                    YeuCau.Ref_MaSP
                    , SUM(IFNULL(YeuCau.TongYeuCauMua, 0) - IFNULL(NhapKho.TongNhapKho, 0)) AS TongYeuCauConLai
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
                    WHERE DanhSach.Ref_MaSP IN (%2$s) AND iform.Status IN (3) 
                    GROUP BY YeuCauMS.IOID, DanhSach.Ref_MaSP 
                ) AS YeuCau
                LEFT JOIN
                (
                    SELECT
                        DanhSach.Ref_MaSanPham AS Ref_MaSP
                        , DanhSach.Ref_SoYeuCau
                        , SUM(IFNULL(DanhSach.SoLuong, 0) * IFNULL(DonViTinh.HeSoQuyDoi, 0)) AS TongNhapKho
                    FROM ONhapKho AS NhapKho
                    INNER JOIN ODanhSachNhapKho AS DanhSach ON NhapKho.IFID_M402 = DanhSach.IFID_M402
                    INNER JOIN qsiforms AS iform ON NhapKho.IFID_M402 = iform.IFID
                    INNER JOIN ODonViTinhSP AS DonViTinh ON DanhSach.Ref_DonViTinh = DonViTinh.IOID
                    WHERE DanhSach.Ref_MaSanPham IN (%2$s) AND iform.Status = 2
                    GROUP BY DanhSach.Ref_SoYeuCau, DanhSach.Ref_MaSanPham
                ) AS NhapKho ON YeuCau.Ref_SoYeuCau = NhapKho.Ref_SoYeuCau AND YeuCau.Ref_MaSP = NhapKho.Ref_MaSP  
                GROUP BY YeuCau.Ref_MaSP
            ) AS YeuCauMuaSam ON VatTu.Ref_MaVatTu = YeuCauMuaSam.Ref_MaSP 
			ORDER BY VatTu.MaVatTu
			'
			, $sqlwhere, implode(',', $items), $this->_o_DB->quote(Qss_Lib_Extra_Const::WAREHOUSE_TYPE_MATERIAL));

		// echo '<pre>'; print_r($sql); die;
		return $this->_o_DB->fetchAll($sql);
	}

    public function getLastestOrderByMaterial($refEquip, $refItem, $refPosition)
    {
        $sql = sprintf('
            SELECT LastOrder.*
            FROM
            (
                SELECT PhieuBaoTri.*, VatTu.Ref_MaVatTu
                FROM OPhieuBaoTri AS PhieuBaoTri
                INNER JOIN OVatTuPBT AS VatTu On PhieuBaoTri.IFID_M759 = VatTu.IFID_M759
                WHERE
                    PhieuBaoTri.Ref_MaThietBi = %1$d
                    AND VatTu.Ref_MaVatTu = %2$d
                    AND IFNULL(VatTu.Ref_ViTri, 0) = %3$d
                ORDER BY PhieuBaoTri.NgayBatDau DESC, PhieuBaoTri.Ngay DESC
            ) AS LastOrder
            GROUP BY LastOrder.Ref_MaThietBi, LastOrder.Ref_MaVatTu
        ', $refEquip, $refItem, $refPosition);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchOne($sql);
    }

    public function getOrdersByEmployee(
        $start
        , $end
        , $refEmployee
        , $eqIOID = 0
    )
    {
        $where  = '';
        $where .= ($eqIOID)?sprintf(' AND thietbi.IOID = %1$d  ', $eqIOID):'';

        $sql = sprintf('
            SELECT
                pbt.*,
                plbt.Loai AS LoaiBaoTri,
                plbt.LoaiBaoTri AS Loai
            FROM OPhieuBaoTri AS pbt
            INNER JOIN ODanhSachThietBi AS thietbi ON pbt.Ref_MaThietBi = thietbi.IOID
            LEFT JOIN OPhanLoaiBaoTri AS plbt ON plbt.IOID = pbt.Ref_LoaiBaoTri
            WHERE (pbt.NgayBatDau >= %1$s and pbt.NgayBatDau <= %2$s)
                AND
                    (
                        pbt.IFID_M759 in (SELECT IFID_M759 FROM OCongViecBTPBT WHERE ifnull(Ref_NguoiThucHien, 0) = %3$d)
                        OR pbt.Ref_NguoiThucHien = %3$d
                    )
                %4$s
            ORDER BY pbt.NgayBatDau DESC, pbt.MaThietBi'
        , $this->_o_DB->quote($start)
        , $this->_o_DB->quote($end)
        , $refEmployee
        , $where);

        // echo '<Pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getTrackBreakdown($start, $end, $page = 0, $display = 0)
    {
        $lang        = $this->_user->user_lang;
        $lang        = ($lang == 'vn')?'':'_'.$lang;
        $addFieldsYC = '';
        $addFieldsBT = '';
        $whereYC     = '';
        $whereBT     = '';

        if(Qss_Lib_System::fieldActive('OYeuCauBaoTri', 'ThoiGianKetThucDuKien'))
        {
            $addFieldsYC .= ', YeuCau.ThoiGianKetThucDuKien';
            $addFieldsBT .= ', PhieuBaoTri.ThoiGianKetThucDuKien AS ThoiGianKetThucDuKien';
        }

        if(Qss_Lib_System::fieldActive('OYeuCauBaoTri', 'NgayDuKienHoanThanh'))
        {
            $addFieldsYC .= ', YeuCau.NgayDuKienHoanThanh';
            $addFieldsBT .= ', PhieuBaoTri.NgayDuKienHoanThanh AS NgayDuKienHoanThanh';
        }

        if(Qss_Lib_System::fieldActive('OYeuCauBaoTri', 'GhiChu'))
        {
            $addFieldsYC .= ', YeuCau.GhiChu';
        }

        if(Qss_Lib_System::fieldActive('OYeuCauBaoTri', 'NguoiChiuTranhNhiem'))
        {
            $addFieldsYC .= ', YeuCau.NguoiChiuTranhNhiem';
            $addFieldsBT .= ', PhieuBaoTri.NguoiThucHien AS NguoiChiuTranhNhiem';
        }

        if(Qss_Lib_System::fieldActive('OYeuCauBaoTri', 'MucDoUuTien'))
        {
            $addFieldsYC .= ', YeuCau.MucDoUuTien';
            $addFieldsBT .= ', PhieuBaoTri.MucDoUuTien';
        }

        if(Qss_Lib_System::fieldActive('ODanhSachThietBi', 'TenKhac'))
        {
            $addFieldsYC .= ', ThietBi.TenKhac';
            $addFieldsBT .= ', ThietBi.TenKhac';
        }

        // Trong mot ngay an cac phieu hoan thanh va huy sau 2 gio
        if($start == $end)
        {
            // (YeuCau.Ngay BETWEEN %1$s AND %2$s)
            $whereYC .= sprintf(' 
            AND 
            (
                IFNULL(IFormYeuCau.Status, 0) NOT IN (3, 5) )

            
            ', $this->_o_DB->quote($start), $this->_o_DB->quote($end));


            /*
OR
(
    (YeuCau.Ngay BETWEEN %1$s AND %2$s)
    AND (IFNULL(IFormYeuCau.Status, 0) IN (3, 5)
    AND TIMESTAMPDIFF(HOUR, from_unixtime(IFormYeuCau.LastModify), now()) <= 2))
)
*/

            $whereBT .= sprintf(' 
                AND 
                (
                    
                    IFNULL(IFormPhieuBT.Status, 0) NOT IN (3, 4, 5)

                )', $this->_o_DB->quote($start), $this->_o_DB->quote($end));

            /*
OR
(
    (IF(IFNULL(PhieuBaoTri.NgayBatDau, "") != "", PhieuBaoTri.NgayBatDau, PhieuBaoTri.NgayBatDauDuKien) BETWEEN %1$s AND %2$s)
    AND (IFNULL(IFormPhieuBT.Status, 0) IN (3, 4, 5)
    AND TIMESTAMPDIFF(HOUR, from_unixtime(IFormPhieuBT.LastModify), now()) <= 2)
)
*/
        }
        else
        {
            $whereYC .= sprintf(' AND (YeuCau.Ngay BETWEEN %1$s AND %2$s)', $this->_o_DB->quote($start), $this->_o_DB->quote($end));
            $whereBT .= sprintf(' AND (IF(IFNULL(PhieuBaoTri.NgayBatDau, "") != "", PhieuBaoTri.NgayBatDau, PhieuBaoTri.NgayBatDauDuKien) BETWEEN %1$s AND %2$s)', $this->_o_DB->quote($start), $this->_o_DB->quote($end));
        }

        $limit = '';
        if($page && $display)
        {
            $start = ceil($page - 1) * $display;
            $limit = sprintf(' LIMIT %1$d, %2$d ', $start, $display);
        }


        // Chưa xử lý và đang xử lý thì lấy tất
        // Còn lại lọc theo thời gian
        // : yêu cầu mầu đỏ, đang xử lý màu vàng và đã xử lý màu xanh, hủy màu ghi)
        $sql = sprintf('
            SELECT
                ThietBi.MaThietBi
                , ThietBi.TenThietBi
                , ThietBi.MaKhuVuc
                , KhuVuc.Ten AS TenKhuVuc
                , YeuCau.Ngay  AS NgaySuCo
                , YeuCau.NguoiYeuCau AS NguoiYeuCau                
                , IFormYeuCau.Status  AS iTinhTrang                
                , qsws.Name%1$s  AS TinhTrang
                , "REQUEST" AS `Type`
                , qsws.Color
                , IFNULL(
                    TIMESTAMPDIFF(
                        HOUR
                        , CONCAT(IFNULL(YeuCau.Ngay, ""), " ", IFNULL(YeuCau.ThoiGian, ""))
                        , IF(IFNULL(IFormYeuCau.Status, 0) IN (3, 5), from_unixtime(IFormYeuCau.LastModify), NOW())
                    )
                    , 0
                ) AS ThoiGianDungMay                
                , YeuCau.ThoiGian
                , YeuCau.Ngay
                , YeuCau.NguoiYeuCau
                , IFNULL(NhanVien.GioiTinh, 0) AS GioiTinh
                , YeuCau.MoTa
                , YeuCau.IFID_M747 AS IFID
                , PhieuBaoTri.NgayDungMay
                , PhieuBaoTri.ThoiGianBatDauDungMay
                , PhieuBaoTri.NgayKetThucDungMay
                , PhieuBaoTri.ThoiGianKetThucDungMay
                , qsws.OrderNo
                , YeuCau.SoPhieu
                
                %4$s
            FROM OYeuCauBaoTri AS YeuCau
            LEFT JOIN ODanhSachThietBi AS ThietBi ON IFNULL(YeuCau.Ref_MaThietBi, 0) = ThietBi.IOID 
            LEFT JOIN OKhuVuc AS KhuVuc ON IFNULL(ThietBi.Ref_MaKhuVuc, 0) = KhuVuc.IOID
            INNER JOIN qsiforms AS IFormYeuCau ON YeuCau.IFID_M747 = IFormYeuCau.IFID
            INNER JOIN qsworkflows AS qsw ON qsw.FormCode = IFormYeuCau.FormCode
			INNER JOIN qsworkflowsteps AS qsws ON qsw.WFID = qsws.WFID AND IFormYeuCau.Status = qsws.StepNo
			LEFT JOIN ODanhSachNhanVien AS NhanVien ON IFNULL(YeuCau.Ref_NguoiYeuCau, 0) = NhanVien.IOID
			LEFT JOIN OPhieuBaoTri AS PhieuBaoTri ON YeuCau.IOID = IFNULL(PhieuBaoTri.Ref_PhieuYeuCau, 0)
			WHERE 1=1 %6$s
					
            /*
            UNION
            
            SELECT
                ThietBi.MaThietBi
                , ThietBi.TenThietBi
                , ThietBi.MaKhuVuc
                , KhuVuc.Ten AS TenKhuVuc
                , PhieuBaoTri.NgayBatDau AS NgaySuCo
                , NULL AS NguoiYeuCau
                , qsws.StepNo AS iTinhTrang
                , qsws.Name%1$s AS TinhTrang
                , "WORKORDER" AS `Type` 
                , qsws.Color
                , IFNULL(
                    TIMESTAMPDIFF(
                        HOUR
                        , PhieuBaoTri.NgayYeuCau
                        , IF(IFNULL(IFormPhieuBT.Status, 0) IN (4, 5), from_unixtime(IFormPhieuBT.LastModify), NOW())
                    )
                    , 0
                ) AS ThoiGianDungMay                
                            
                , NULL AS ThoiGian
                , PhieuBaoTri.NgayYeuCau AS Ngay
                , NULL AS NguoiYeuCau
                , NULL AS GioiTinh
                , PhieuBaoTri.MoTa
                , PhieuBaoTri.IFID_M759 AS IFID
                , PhieuBaoTri.NgayDungMay
                , PhieuBaoTri.ThoiGianBatDauDungMay
                , PhieuBaoTri.NgayKetThucDungMay
                , PhieuBaoTri.ThoiGianKetThucDungMay         
                , qsws.OrderNo
                , PhieuBaoTri.SoPhieu
                %5$s                           
            FROM ODanhSachThietBi AS ThietBi
            INNER JOIN OKhuVuc AS KhuVuc ON IFNULL(ThietBi.Ref_MaKhuVuc, 0) = KhuVuc.IOID
            INNER JOIN OPhieuBaoTri AS PhieuBaoTri ON ThietBi.IOID = IFNULL(PhieuBaoTri.Ref_MaThietBi, 0)
            INNER JOIN qsiforms AS IFormPhieuBT ON PhieuBaoTri.IFID_M759 = IFormPhieuBT.IFID	
            INNER JOIN qsworkflows AS qsw ON qsw.FormCode = IFormPhieuBT.FormCode
			INNER JOIN qsworkflowsteps AS qsws ON qsw.WFID = qsws.WFID AND IFormPhieuBT.Status = qsws.StepNo            
            INNER JOIN OPhanLoaiBaoTri AS LoaiBaoTri ON PhieuBaoTri.Ref_LoaiBaoTri = LoaiBaoTri.IOID
            LEFT JOIN OYeuCauBaoTri AS YeuCau ON IFNULL(PhieuBaoTri.Ref_PhieuYeuCau, 0) = YeuCau.IOID
            WHERE IFNULL(YeuCau.IOID, 0) = 0 
                AND LoaiBaoTri.LoaiBaoTri = "B"              
                %7$s
            */
            ORDER BY 
                OrderNo,
                Ngay DESC,
                ThoiGian DESC,                
                TenThietBi DESC
            
            %8$s
        ', $lang
            , $this->_o_DB->quote($start)
            , $this->_o_DB->quote($end)
            , $addFieldsYC
            , $addFieldsBT
            , $whereYC
            , $whereBT
            , $limit);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }


    public function countTrackBreakdown($start, $end)
    {
        $whereYC     = '';
        $whereBT     = '';

        // Trong mot ngay an cac phieu hoan thanh va huy sau 2 gio
        if($start == $end)
        {
            // (YeuCau.Ngay BETWEEN %1$s AND %2$s)
            $whereYC .= sprintf(' 
            AND 
            (
                IFNULL(IFormYeuCau.Status, 0) NOT IN (3, 5) )

            
            ', $this->_o_DB->quote($start), $this->_o_DB->quote($end));


            $whereBT .= sprintf(' 
                AND 
                (
                    
                    IFNULL(IFormPhieuBT.Status, 0) NOT IN (3, 4, 5)

                )', $this->_o_DB->quote($start), $this->_o_DB->quote($end));
        }
        else
        {
            $whereYC .= sprintf(' AND (YeuCau.Ngay BETWEEN %1$s AND %2$s)', $this->_o_DB->quote($start), $this->_o_DB->quote($end));
            $whereBT .= sprintf(' AND (IF(IFNULL(PhieuBaoTri.NgayBatDau, "") != "", PhieuBaoTri.NgayBatDau, PhieuBaoTri.NgayBatDauDuKien) BETWEEN %1$s AND %2$s)', $this->_o_DB->quote($start), $this->_o_DB->quote($end));
        }


        // Chưa xử lý và đang xử lý thì lấy tất
        // Còn lại lọc theo thời gian
        // : yêu cầu mầu đỏ, đang xử lý màu vàng và đã xử lý màu xanh, hủy màu ghi)
        $sql = sprintf('
            SELECT
                count(1) as total
            FROM OYeuCauBaoTri AS YeuCau
            INNER JOIN ODanhSachThietBi AS ThietBi ON IFNULL(YeuCau.Ref_MaThietBi, 0) = ThietBi.IOID 
            INNER JOIN OKhuVuc AS KhuVuc ON IFNULL(ThietBi.Ref_MaKhuVuc, 0) = KhuVuc.IOID
            INNER JOIN qsiforms AS IFormYeuCau ON YeuCau.IFID_M747 = IFormYeuCau.IFID
            INNER JOIN qsworkflows AS qsw ON qsw.FormCode = IFormYeuCau.FormCode
			INNER JOIN qsworkflowsteps AS qsws ON qsw.WFID = qsws.WFID AND IFormYeuCau.Status = qsws.StepNo
			LEFT JOIN ODanhSachNhanVien AS NhanVien ON IFNULL(YeuCau.Ref_NguoiYeuCau, 0) = NhanVien.IOID
			LEFT JOIN OPhieuBaoTri AS PhieuBaoTri ON YeuCau.IOID = IFNULL(PhieuBaoTri.Ref_PhieuYeuCau, 0)
			WHERE 1=1 %1$s
						
            UNION
            
            SELECT
                count(1) as total                     
            FROM ODanhSachThietBi AS ThietBi
            INNER JOIN OKhuVuc AS KhuVuc ON IFNULL(ThietBi.Ref_MaKhuVuc, 0) = KhuVuc.IOID
            INNER JOIN OPhieuBaoTri AS PhieuBaoTri ON ThietBi.IOID = IFNULL(PhieuBaoTri.Ref_MaThietBi, 0)
            INNER JOIN qsiforms AS IFormPhieuBT ON PhieuBaoTri.IFID_M759 = IFormPhieuBT.IFID	
            INNER JOIN qsworkflows AS qsw ON qsw.FormCode = IFormPhieuBT.FormCode
			INNER JOIN qsworkflowsteps AS qsws ON qsw.WFID = qsws.WFID AND IFormPhieuBT.Status = qsws.StepNo            
            INNER JOIN OPhanLoaiBaoTri AS LoaiBaoTri ON PhieuBaoTri.Ref_LoaiBaoTri = LoaiBaoTri.IOID
            LEFT JOIN OYeuCauBaoTri AS YeuCau ON IFNULL(PhieuBaoTri.Ref_PhieuYeuCau, 0) = YeuCau.IOID
            WHERE IFNULL(YeuCau.IOID, 0) = 0 
                AND LoaiBaoTri.LoaiBaoTri = "B"              
                %2$s
        ', $whereYC, $whereBT);

        $data  = $this->_o_DB->fetchAll($sql);
        $total = 0;

        foreach($data as $count)
        {
            $total += $count->total;
        }

        // echo '<pre>'; print_r($sql); die;
        return $total;
    }

    public function createWorkOrdersFromPlansArray($data)
    {
        if(!isset($data['ifid']) || count($data['ifid']) == 0)
        {
            return 1;
        }

        //$object   = new Qss_Model_Object(); $object->v_fInit('OPhieuBaoTri', 'M759');
        $model    = new Qss_Model_Import_Form('M759',true, false);
        $model->o_fGetMainObject()->getFieldByCode('MoTa')->intInputType = 3;
        $mPlan    = new Qss_Model_Maintenance_Plan();
        $document = new Qss_Model_Extra_Document($model->o_fGetMainObject());

        $data['ifid'][] = 0;
        $sql = sprintf('
            SELECT * FROM OBaoTriDinhKy WHERE IFID_M724 IN (%1$s)
        ', implode(',', $data['ifid']));

        $plans = $this->_o_DB->fetchAll($sql);


        $ifids = array();
        $i     = 0;
        $j     = 0;
        $k     = 0;
        $starts       = array();
        $ends         = array();
        $chukys       = array();
        $OPhieuBaoTri = array();
        $OCongViecBTPBT = array();
        $OVatTuPBT = array();
        $ODichVuPBT = array();
        $checkNguoiThucHien = Qss_Lib_System::fieldActive('OCongViecBT', 'NguoiThucHien');
        $checkNguoiChiuTrachNhiem = Qss_Lib_System::fieldActive('OBaoTriDinhKy', 'NguoiThucHien');
        $mLoaiBaoTri = Qss_Model_Db::Table('OPhanLoaiBaoTri');
        $mLoaiBaoTri->where(sprintf('LoaiBaoTri = "%1$s"', Qss_Lib_Extra_Const::MAINT_TYPE_PREVENTIVE));
        $oLoaiBaoTri = $mLoaiBaoTri->fetchOne();

        if(!$oLoaiBaoTri) // Nếu không có loại bảo trì định kỳ cho là lỗi
        {
            return 9999;
        }
		foreach($data['start'] as $start)
        {
            $starts[$data['key'][$i]] = $start;
            $i++;
        }

        foreach($data['ref_chuky'] as $ref_chuky)
        {
            $chukys[$data['key'][$k]] = $ref_chuky;
            $k++;
        }

        foreach($data['end'] as $end)
        {
            $ends[$data['key'][$j]] = $end;
            $j++;
        }

        foreach($plans as $item)
        {
            $ifids[] = $item->IFID_M724;
            $OPhieuBaoTri[$item->IFID_M724] = $item;
        }

        if(count($ifids))
        {
            $materials = $this->_o_DB->fetchAll(sprintf('SELECT * FROM OVatTu WHERE IFID_M724 IN (%1$s) ORDER BY IOID', implode(',', $ifids)));
            $tasks     = $this->_o_DB->fetchAll(sprintf('SELECT * FROM OCongViecBT WHERE IFID_M724 IN (%1$s) ORDER BY IOID', implode(',', $ifids)));
            $services  = $this->_o_DB->fetchAll(sprintf('SELECT * FROM ODichVuBT WHERE IFID_M724 IN (%1$s) ORDER BY IOID', implode(',', $ifids)));

            foreach($materials as $item)
            {
                $OVatTuPBT[$item->IFID_M724][] = $item;
            }

            foreach($tasks as $item)
            {
                $OCongViecBTPBT[$item->IFID_M724][] = $item;
            }

            foreach($services as $item)
            {
                $ODichVuPBT[$item->IFID_M724][] = $item;
            }
        }

        $f = 0;
        foreach($data['key'] as $key)
        {

            $ifid = $data['ifid'][$f];
            $item = $OPhieuBaoTri[$ifid];

            $f++;

            if(isset($OPhieuBaoTri[$ifid]) && count($OPhieuBaoTri[$ifid]))
            {
                $startdate    = Qss_Lib_Date::displaytomysql(@$starts[$key]?$starts[$key]:date('d-m-Y'));
                $enddate      = Qss_Lib_Date::displaytomysql(@$ends[$key]?$ends[$key]:date('d-m-Y'));

                $params = array();
		        
		        //set prefix nếu có 
	            $customDocNo   = 'Qss_Bin_Custom_M759_Document';
		        if(class_exists($customDocNo)) {
		            $docNoCustomClass = new $customDocNo($model->o_fGetMainObject());
                    $item->NgayYeuCau = $startdate;
		            $prefix = $docNoCustomClass->getPrefix($item);
		            if($prefix)
		            {
		            	$document->setPrefix($prefix);
		            }
		        }
		        //lấy số phiếu
		        $last = (!isset($this->lastDocNo[$document->getPrefix()]))?$document->getLast():$this->lastDocNo[$document->getPrefix()];
		        $this->lastDocNo[$document->getPrefix()] = ++$last;
                $params['OPhieuBaoTri'][0]['SoPhieu']             = $document->writeDocumentNo($this->lastDocNo[$document->getPrefix()]);
                $params['OPhieuBaoTri'][0]['NgayYeuCau']          = $startdate;
                $params['OPhieuBaoTri'][0]['NgayBatDauDuKien']    = $startdate;
                //$params['OPhieuBaoTri'][0]['Ngay']              = $enddate;
                $params['OPhieuBaoTri'][0]['GhiVaoLyLich']        = 1;

                if(Qss_Lib_System::fieldActive('OBaoTriDinhKy', 'DoiTac')
                    && Qss_Lib_System::fieldActive('OPhieuBaoTri', 'DoiTac'))
                {
                    $params['OPhieuBaoTri'][0]['DoiTac']        = (int) $item->Ref_DoiTac;
                }

                $params['OPhieuBaoTri'][0]['NgayDuKienHoanThanh'] = $enddate;
                $params['OPhieuBaoTri'][0]['MaKhuVuc']            = (int) $item->Ref_MaKhuVuc;
                $params['OPhieuBaoTri'][0]['Ca']                  = (int) $item->Ref_Ca;
                $params['OPhieuBaoTri'][0]['LoaiBaoTri']          = (int) $oLoaiBaoTri->IOID;
                $params['OPhieuBaoTri'][0]['MaDVBT']              = (int) $item->Ref_DVBT;
                $params['OPhieuBaoTri'][0]['TenDVBT']             = (int) $item->Ref_DVBT;
                $params['OPhieuBaoTri'][0]['MucDoUuTien']         = $item->MucDoUuTien;
                $params['OPhieuBaoTri'][0]['MaThietBi']           = (int) $item->Ref_MaThietBi;

                $params['OPhieuBaoTri'][0]['TenThietBi'] = $item->TenThietBi;
                $params['OPhieuBaoTri'][0]['Ref_TenThietBi'] = (int) $item->Ref_MaThietBi;

                $params['OPhieuBaoTri'][0]['BoPhan']              = (int) $item->Ref_BoPhan;
                $params['OPhieuBaoTri'][0]['ThoiGianDungMay']     = $item->DungMay;
                $params['OPhieuBaoTri'][0]['ThoiGianXuLy']        = $item->SoPhut;
                $params['OPhieuBaoTri'][0]['BenNgoai']            = $item->BenNgoai;
                $params['OPhieuBaoTri'][0]['ChuKy']               = (int) @$chukys[$key];
                $params['OPhieuBaoTri'][0]['MoTa']                = $item->MoTa;//$item->MoTa;
                $params['OPhieuBaoTri'][0]['Ref_MoTa']            = (int)$item->IOID;//$item->MoTa;
                //$params['OPhieuBaoTri'][0]['Ref_MoTa']               = (int)$item->IOID;
            	$params['OPhieuBaoTri'][0]['deptid']              = $item->DeptID;
               	if($checkNguoiChiuTrachNhiem)
                {
                	$params['OPhieuBaoTri'][0]['NguoiThucHien'] = (int) $item->Ref_NguoiThucHien;
				}

                if(isset($OCongViecBTPBT[$item->IFID_M724]))
                {
                    $o = 0;
                    foreach($OCongViecBTPBT[$item->IFID_M724] as $ta)
                    {
                        $params['OCongViecBTPBT'][$o]['ViTri']          = (int) $ta->Ref_ViTri;
                        $params['OCongViecBTPBT'][$o]['BoPhan']         = (int) $ta->Ref_ViTri;
                        $params['OCongViecBTPBT'][$o]['Ten']            = (int) $ta->Ref_Ten;
                        $params['OCongViecBTPBT'][$o]['ThoiGianDuKien'] = $ta->ThoiGian;
                        $params['OCongViecBTPBT'][$o]['NhanCongDuKien'] = $ta->NhanCong;
                        $params['OCongViecBTPBT'][$o]['ThoiGian']       = $ta->ThoiGian;
                        $params['OCongViecBTPBT'][$o]['MoTa']           = $ta->MoTa;
                        $params['OCongViecBTPBT'][$o]['NgayDuKien']     = $startdate;

                   	 	if($checkNguoiThucHien)
                        {
                            $params['OCongViecBTPBT'][$o]['NguoiThucHien'] = (int) $ta->Ref_NguoiThucHien;
                        }
						elseif($checkNguoiChiuTrachNhiem)
						{
							$params['OCongViecBTPBT'][$o]['NguoiThucHien'] = (int) $item->Ref_NguoiThucHien;
						}
                        $o++;
                    }
                }

                if(isset($OVatTuPBT[$item->IFID_M724]))
                {
                    $n = 0;
                    foreach($OVatTuPBT[$item->IFID_M724] as $mat)
                    {
                        $params['OVatTuPBT'][$n]['HinhThuc']      = (int) $mat->HinhThuc;
                        $params['OVatTuPBT'][$n]['ViTri']         = (int) $mat->Ref_ViTri;
                        $params['OVatTuPBT'][$n]['BoPhan']        = (int) $mat->Ref_BoPhan;
                        $params['OVatTuPBT'][$n]['MaVatTu']       = (int) $mat->Ref_MaVatTu;

						$params['OVatTuPBT'][$n]['TenVatTu']      = $mat->TenVatTu;
                        $params['OVatTuPBT'][$n]['Ref_TenVatTu']      = (int) $mat->Ref_TenVatTu;

                        $params['OVatTuPBT'][$n]['DonViTinh']     = (int) $mat->Ref_DonViTinh;
                        $params['OVatTuPBT'][$n]['SoLuongDuKien'] = $mat->SoLuong;
                        $params['OVatTuPBT'][$n]['SoLuong']       = $mat->SoLuong;
                        $params['OVatTuPBT'][$n]['CongViec']      = $mat->CongViec;
                        $n++;
                    }

                    if(Qss_Lib_System::objectInForm('M759', 'OVatTuPBTDK')) {
                        // Them vat tu phieu bao tri
                        $n = 0;
                        foreach ($OVatTuPBT[$item->IFID_M724] as $mat) {
                            $params['OVatTuPBTDK'][$n]['HinhThuc']      = (int) @$mat->HinhThuc;
                            $params['OVatTuPBTDK'][$n]['ViTri']         = (int) @$mat->Ref_ViTri;
                            $params['OVatTuPBTDK'][$n]['BoPhan']        = (int) @$mat->Ref_BoPhan;
                            $params['OVatTuPBTDK'][$n]['MaVatTu']       = (int) @$mat->Ref_MaVatTu;

                            $params['OVatTuPBTDK'][$n]['TenVatTu']      = @$mat->TenVatTu;
                            $params['OVatTuPBTDK'][$n]['Ref_TenVatTu']      = (int) @$mat->Ref_TenVatTu;

                            $params['OVatTuPBTDK'][$n]['DonViTinh']     = (int) @$mat->Ref_DonViTinh;
                            $params['OVatTuPBTDK'][$n]['SoLuongDuKien'] = @$mat->SoLuong;
                            $params['OVatTuPBTDK'][$n]['SoLuong']       = @$mat->SoLuong;
                            $params['OVatTuPBTDK'][$n]['CongViec']      = @$mat->CongViec;
                            $n++;
                        }
                    }
                }





                if(isset($ODichVuPBT[$item->IFID_M724]))
                {
                    $o = 0;
                    foreach($ODichVuPBT[$item->IFID_M724] as $se)
                    {
                        $params['ODichVuPBT'][$o]['MaNCC']        = (int) $se->Ref_MaNCC;
                        $params['ODichVuPBT'][$o]['TenNCC']       = (int) $se->Ref_MaNCC;
                        $params['ODichVuPBT'][$o]['DichVu']       = $se->DichVu;
                        $params['ODichVuPBT'][$o]['ChiPhiDuKien'] = $se->ChiPhi;
                        $params['ODichVuPBT'][$o]['ChiPhi']       = $se->ChiPhi;
                        $params['ODichVuPBT'][$o]['GhiChu']       = $se->GhiChu;
                        $o++;
                    }
                }

                $model->setData($params);
            }

        }
        $model->generateSQL();
        $formError   = $model->countFormError();
        $objectError = $model->countObjectError();
		
        $error = $formError + $objectError;
     	if($error)
        {
             echo '<pre>'; print_r($model->getImportRows());
        }

        return $error;
    }

    public function createWorkOrdersFromPlans($start, $end, $location = 0, $wc = 0)
    {
        $arrChiSo   = array();
        $model      = new Qss_Model_Import_Form('M759',true, false);
        $model->o_fGetMainObject()->getFieldByCode('MoTa')->intInputType = 3;
        $document = new Qss_Model_Extra_Document($model->o_fGetMainObject());
        $startdate  = date_create($start);
        $enddate    = date_create($end);

        $checkNguoiThucHien = Qss_Lib_System::fieldActive('OCongViecBT', 'NguoiThucHien');
        $checkNguoiChiuTrachNhiem = Qss_Lib_System::fieldActive('OBaoTriDinhKy', 'NguoiThucHien');
        $mLoaiBaoTri = Qss_Model_Db::Table('OPhanLoaiBaoTri');
        $mLoaiBaoTri->where(sprintf('LoaiBaoTri = "%1$s"', Qss_Lib_Extra_Const::MAINT_TYPE_PREVENTIVE));
        $oLoaiBaoTri = $mLoaiBaoTri->fetchOne();

        if(!$oLoaiBaoTri) // Nếu không có loại bảo trì định kỳ cho là lỗi
        {
            return 9999;
        }



        while($enddate >= $startdate)
        {
            $basicplan  = Qss_Model_Maintenance_Plan::createInstance();
            $dataSQL 	= $basicplan->getPlansByDate($startdate->format('Y-m-d'), $location, $wc);

			foreach ($dataSQL as $item)
            {
                $thietbi = (int) $item->Ref_MaThietBi;
                $bophan  = (int) $item->Ref_BoPhan;
                $chuky   = (int) $item->ChuKyIOID;

                if(($item->CanCu == 1 || $item->CanCu == 2) && isset($arrChiSo[$thietbi][$bophan][$chuky]))
                {
                }
                else
                {
                    $params       = array();//reset
                    $ifid         = $item->IFID_M724;
                    $materialsSql = sprintf('select * from OVatTu   WHERE IFID_M724 = %1$d Group by Ref_CongViec,Ref_ViTri,Ref_MaVatTu,Ref_DonViTinh'
                    	, $ifid);
                    $materials    = $this->_o_DB->fetchAll($materialsSql);
                    $taskSql      = sprintf('select * from OCongViecBT   WHERE IFID_M724 = %1$d Group by MoTa', $ifid);
                    $task         = $this->_o_DB->fetchAll($taskSql);
                    $dichvuSQL    = sprintf('select * from ODichVuBT   WHERE IFID_M724 = %1$d', $ifid);
                    $dichvuParam  = $this->_o_DB->fetchAll($dichvuSQL);
                    $addays       = (int)@$item->SoNgay; // So ngay bao tri du kien
                    $addays       = ($addays > 1)?($addays - 1):0;
                    $done         = Qss_Lib_Date::add_date($startdate, $addays); // Ngay du kien hoan thanh

                    //set prefix nếu có 
		            $customDocNo   = 'Qss_Bin_Custom_M759_Document';
	                if(class_exists($customDocNo)) {
			            $docNoCustomClass = new $customDocNo($model->o_fGetMainObject());
                        $item->NgayYeuCau = $startdate->format('Y-m-d');
			            $prefix = $docNoCustomClass->getPrefix($item);
			            if($prefix)
			            {
			            	$document->setPrefix($prefix);
			            }
			        }
			        //lấy số phiếu
			        $last = (!isset($this->lastDocNo[$document->getPrefix()]))?$document->getLast():$this->lastDocNo[$document->getPrefix()];
			        $this->lastDocNo[$document->getPrefix()] = ++$last;
	
	                $params['OPhieuBaoTri'][0]['SoPhieu']             = $document->writeDocumentNo($this->lastDocNo[$document->getPrefix()]);
                    $params['OPhieuBaoTri'][0]['NgayYeuCau']          = $startdate->format('Y-m-d');
                    $params['OPhieuBaoTri'][0]['NgayBatDauDuKien']    = $startdate->format('Y-m-d');
                    //$params['OPhieuBaoTri'][0]['Ngay']                = $done->format('Y-m-d');
                    $params['OPhieuBaoTri'][0]['GhiVaoLyLich']        = 1;

                    if(Qss_Lib_System::fieldActive('OBaoTriDinhKy', 'DoiTac')
                        && Qss_Lib_System::fieldActive('OPhieuBaoTri', 'DoiTac'))
                    {
                        $params['OPhieuBaoTri'][0]['DoiTac']        = (int) $item->Ref_DoiTac;
                    }

                    $params['OPhieuBaoTri'][0]['NgayDuKienHoanThanh'] = $done->format('Y-m-d');
                    $params['OPhieuBaoTri'][0]['MaKhuVuc']            = (int) $item->Ref_MaKhuVuc;
                    $params['OPhieuBaoTri'][0]['Ca']                  = (int) $item->Ref_Ca;
                    $params['OPhieuBaoTri'][0]['LoaiBaoTri']          = (int) $oLoaiBaoTri->IOID;//(int) $item->Ref_LoaiBaoTri;
                    $params['OPhieuBaoTri'][0]['MaDVBT']              = (int) $item->Ref_DVBT;
                    $params['OPhieuBaoTri'][0]['TenDVBT']             = (int) $item->Ref_DVBT;
                    $params['OPhieuBaoTri'][0]['MucDoUuTien']         = $item->MucDoUuTien;
                    $params['OPhieuBaoTri'][0]['MaThietBi']           = (int) $item->Ref_MaThietBi;

					$params['OPhieuBaoTri'][0]['TenThietBi'] = $item->TenThietBi;
					$params['OPhieuBaoTri'][0]['Ref_TenThietBi'] = (int) $item->Ref_MaThietBi;
					
                    $params['OPhieuBaoTri'][0]['BoPhan']              = (int) $item->Ref_BoPhan;
                    $params['OPhieuBaoTri'][0]['ThoiGianDungMay']     = $item->DungMay;
                    $params['OPhieuBaoTri'][0]['ThoiGianXuLy']        = $item->SoPhut;
                    $params['OPhieuBaoTri'][0]['BenNgoai']            = $item->BenNgoai;
                    $params['OPhieuBaoTri'][0]['ChuKy']               = (int) $item->ChuKyIOID;
                    $params['OPhieuBaoTri'][0]['Ref_MoTa']            = (int)$item->PlanIOID;
                    $params['OPhieuBaoTri'][0]['MoTa']                = $item->MoTaKeHoachBaoTri;
                   	$params['OPhieuBaoTri'][0]['deptid']              = $item->DeptID;
               		if($checkNguoiChiuTrachNhiem)
                    {
                    	$params['OPhieuBaoTri'][0]['NguoiThucHien'] = (int) $item->Ref_NguoiThucHien;
					}
                    // Them cong viec phieu bao tri
                    $o = 0;
                    foreach ($task as $ta)
                    {
                        $params['OCongViecBTPBT'][$o]['ViTri']          = (int) $ta->Ref_ViTri;
                        $params['OCongViecBTPBT'][$o]['BoPhan']         = (int) $ta->Ref_ViTri;
                        $params['OCongViecBTPBT'][$o]['Ten']            = (int) $ta->Ref_Ten;
                        $params['OCongViecBTPBT'][$o]['ThoiGianDuKien'] = $ta->ThoiGian;
                        $params['OCongViecBTPBT'][$o]['ThoiGian']       = $ta->ThoiGian;
                        $params['OCongViecBTPBT'][$o]['NhanCongDuKien']       = $ta->NhanCong;
                        $params['OCongViecBTPBT'][$o]['ThoiGianDuKien']       = $ta->ThoiGian;
                        $params['OCongViecBTPBT'][$o]['MoTa']           = $ta->MoTa;
                        $params['OCongViecBTPBT'][$o]['NgayDuKien']     = $startdate->format('Y-m-d');

                        if($checkNguoiThucHien)
                        {
                            $params['OCongViecBTPBT'][$o]['NguoiThucHien'] = (int) $ta->Ref_NguoiThucHien;
                        }
						elseif($checkNguoiChiuTrachNhiem)
						{
							$params['OCongViecBTPBT'][$o]['NguoiThucHien'] = (int) $item->Ref_NguoiThucHien;
						}
                        $o++;
                    }

                    // Them vat tu phieu bao tri
                    $n = 0;
                    foreach ($materials as $mat)
                    {
                        $params['OVatTuPBT'][$n]['HinhThuc']      = (int) $mat->HinhThuc;
                        $params['OVatTuPBT'][$n]['ViTri']         = (int) $mat->Ref_ViTri;
                        $params['OVatTuPBT'][$n]['BoPhan']        = (int) $mat->Ref_BoPhan;
                        $params['OVatTuPBT'][$n]['MaVatTu']       = (int) $mat->Ref_MaVatTu;

                        $params['OVatTuPBT'][$n]['TenVatTu']      = $mat->TenVatTu;
						$params['OVatTuPBT'][$n]['Ref_TenVatTu']      = (int) $mat->Ref_TenVatTu;
						
                        $params['OVatTuPBT'][$n]['DonViTinh']     = (int) $mat->Ref_DonViTinh;
                        $params['OVatTuPBT'][$n]['SoLuongDuKien'] = $mat->SoLuong;
                        $params['OVatTuPBT'][$n]['SoLuong']       = $mat->SoLuong;
                        $params['OVatTuPBT'][$n]['CongViec']      = $mat->CongViec;
                        $n++;
                    }

                    if(Qss_Lib_System::objectInForm('M759', 'OVatTuPBTDK')) {
                        // Them vat tu phieu bao tri
                        $n = 0;
                        foreach ($materials as $mat) {
                            $params['OVatTuPBTDK'][$n]['HinhThuc']      = (int) @$mat->HinhThuc;
                            $params['OVatTuPBTDK'][$n]['ViTri']         = (int) @$mat->Ref_ViTri;
                            $params['OVatTuPBTDK'][$n]['BoPhan']        = (int) @$mat->Ref_BoPhan;
                            $params['OVatTuPBTDK'][$n]['MaVatTu']       = (int) @$mat->Ref_MaVatTu;

                            $params['OVatTuPBTDK'][$n]['TenVatTu']      = @$mat->TenVatTu;
							$params['OVatTuPBTDK'][$n]['Ref_TenVatTu']      = (int) @$mat->Ref_TenVatTu;
                            
                            $params['OVatTuPBTDK'][$n]['DonViTinh']     = (int) @$mat->Ref_DonViTinh;
                            $params['OVatTuPBTDK'][$n]['SoLuongDuKien'] = @$mat->SoLuong;
                            $params['OVatTuPBTDK'][$n]['SoLuong']       = @$mat->SoLuong;
                            $params['OVatTuPBTDK'][$n]['CongViec']      = @$mat->CongViec;
                            $n++;
                        }
                    }

                    // Them dich vu thue ngoai
                    $o = 0;
                    foreach ($dichvuParam as $ta) {
                        $params['ODichVuPBT'][$o]['MaNCC']        = (int) $ta->Ref_MaNCC;
                        $params['ODichVuPBT'][$o]['TenNCC']       = (int) $ta->Ref_MaNCC;
                        $params['ODichVuPBT'][$o]['DichVu']       = $ta->DichVu;
                        $params['ODichVuPBT'][$o]['ChiPhiDuKien'] = $ta->ChiPhi;
                        $params['ODichVuPBT'][$o]['ChiPhi']       = $ta->ChiPhi;
                        $params['ODichVuPBT'][$o]['GhiChu']       = $ta->GhiChu;
                        $o++;
                    }

                    // Luu dong phieu bao tri ben tren vao trong sql cap nhat du lieu
                    $model->setData($params);
                }

                if($item->CanCu == 1 || $item->CanCu == 2)
                {
                    $arrChiSo[$thietbi][$bophan][$chuky] = 1;
                }
            }

            $startdate = Qss_Lib_Date::add_date($startdate, 1); // Cong ngay len 1 cho vong loop while tu ngay den ngay
        }

        $model->generateSQL();

		$formError   = $model->countFormError();
        $objectError = $model->countObjectError();
        $error       = $formError + $objectError;

        if($error)
        {
             echo '<pre>'; print_r($model->getImportRows());
        }
        
        return $error;
    }

    public function countIssueWorkordersByUser($userID)
    {
        $sql = sprintf('
            SELECT count(OPhieuBaoTri.IFID_M759) AS Total
            FROM OPhieuBaoTri 
            INNER JOIN qsiforms ON OPhieuBaoTri.IFID_M759 = qsiforms.IFID            
            LEFT JOIN ODanhSachNhanVien ON IFNULL(OPhieuBaoTri.Ref_NguoiThucHien, 0) = ODanhSachNhanVien.IOID              
            LEFT JOIN qsusers ON IFNULL(ODanhSachNhanVien.Ref_TenTruyCap, 0) = qsusers.UID
            WHERE qsusers.UID = %1$d AND qsiforms.Status = 2            
        ', $userID);

        $data = $this->_o_DB->fetchOne($sql);
        return $data?$data->Total:0;
    }

    public function countIssuedWorkordersByUser($userID)
    {
        $dataSql = $this->_o_DB->fetchOne(sprintf('
                SELECT count(1) AS Total 
                FROM OPhieuBaoTri
                INNER JOIN qsiforms AS iform ON iform.IFID = OPhieuBaoTri.IFID_M759
                INNER JOIN ODanhSachNhanVien ON IFNULL(OPhieuBaoTri.Ref_NguoiThucHien, 0) = 0
                INNER JOIN qsusers ON IFNULL(ODanhSachNhanVien.Ref_TenTruyCap, 0) = qsusers.UID
                WHERE qsusers.UID = %1$d AND IFNULL(iform.Status, 0) != 1  
            ', $userID)
        );
        return $dataSql?$dataSql->Total: 0;
    }

    public function getWorkorderDetailsGroupByTask(
        $start = ''
        , $end = ''
        , $employeeTaskIOID = 0
    )
    {
        // @note: (phieu.`NgayYeuCau`, phieu.`NgayBatDau`) ngay co nen su dung ca ngay bat dau?
        $where  = '';
        $where .= ($start && $end)?sprintf(' AND (PhieuBaoTri.NgayBatDauDuKien >= %1$s and PhieuBaoTri.NgayBatDauDuKien <= %2$s)', $this->_o_DB->quote($start), $this->_o_DB->quote($end)):'';
        $where .= ($employeeTaskIOID)?sprintf(' AND PhieuBaoTri.IFID_M759 in (SELECT IFID_M759 FROM OCongViecBTPBT WHERE ifnull(Ref_NguoiThucHien, 0) = %1$d ) ', $employeeTaskIOID):'';

        $sql = sprintf('
			SELECT 
			    PhieuBaoTri.*
			    , CongViec.*			  			  			  
			    , PhieuBaoTri.MoTa AS MoTaBaoTri
			    , CongViec.IOID AS CongViecIOID
			    , CongViec.MoTa AS MoTaCongViec
			    , CongViec.GhiChu AS GhiChuCongViec
                , CauTrucVoiDongChinh.IOID AS Ref_ViTriDongChinh
                , CauTrucVoiDongChinh.ViTri AS ViTriDongChinh
                , CauTrucVoiDongChinh.BoPhan AS BoPhanDongChinh
			    , (
                    select group_concat(concat(Vitri,\' - \',BoPhan) SEPARATOR \'<br>\')
                    from OCauTrucThietBi as t
                    where t.IFID_M705 = CauTrucVoiCongViec.IFID_M705 
                        and t.lft < ifnull(CauTrucVoiCongViec.lft,0) 
                        and t.rgt > ifnull(CauTrucVoiCongViec.rgt,0)
                    order by t.lft
                    limit 1
                ) as BoPhanCha
				, group_concat(
				      DISTINCT concat(
				          VatTu.TenVatTu
				          ," (", VatTu.MaVatTu ,") : "
				          , VatTu.SoLuong, " "
				          , VatTu.DonViTinh
                      ) SEPARATOR \'<br>\'
                ) as VatTu
			FROM OPhieuBaoTri AS PhieuBaoTri
			INNER JOIN qsiforms AS iform ON iform.IFID = PhieuBaoTri.IFID_M759
			LEFT JOIN OCongViecBTPBT AS CongViec ON CongViec.IFID_M759 = PhieuBaoTri.IFID_M759
			LEFT JOIN OCauTrucThietBi as CauTrucVoiDongChinh on CauTrucVoiDongChinh.IOID = PhieuBaoTri.Ref_BoPhan
			LEFT JOIN OCauTrucThietBi as CauTrucVoiCongViec on CauTrucVoiCongViec.IOID = CongViec.Ref_ViTri
            LEFT JOIN OVatTuPBT AS VatTu ON PhieuBaoTri.IFID_M759 = VatTu.IFID_M759  AND 
                (VatTu.Ref_ViTri = CongViec.Ref_ViTri OR PhieuBaoTri.Ref_BoPhan = 	VatTu.Ref_ViTri)  	
			WHERE 1=1 %1$s
			GROUP BY PhieuBaoTri.IFID_M759, CongViec.IOID
			ORDER BY PhieuBaoTri.NgayBatDauDuKien DESC, PhieuBaoTri.SoPhieu, PhieuBaoTri.MaThietBi ', $where);
        // echo '<Pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getMaterialByIOID($materialLineIOID)
    {
        $sql = sprintf('
            SELECT vt.*, sp.DacTinhKyThuat, sp.IFID_M113
            FROM OVatTuPBT AS vt
            INNER JOIN OSanPham AS sp ON vt.Ref_MaVatTu = sp.IOID
            WHERE vt.IOID in (%1$s)
            ORDER BY vt.IFID_M759, vt.Ref_ViTri'
            , $materialLineIOID);
        return $this->_o_DB->fetchOne($sql);
    }

    public function getMovingHistoryOfComponets($start, $end, $locIOID = 0, $eqGroupIOID = 0, $eqTypeIOID = 0, $eqIOID = 0)
    {
        $where = '';
        $oLoc  = $locIOID?$this->_o_DB->fetchOne(sprintf('select * from OKhuVuc where IOID = %1$d', $locIOID)):'';
        $where.= $oLoc?sprintf(' AND IFNULL(ThietBi.Ref_MaKhuVuc, 0) in (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d)', $oLoc->lft, $oLoc->rgt):'';
        $where.= $eqGroupIOID?sprintf(' AND IFNULL(ThietBi.Ref_NhomThietBi, 0) = %1$d', $eqGroupIOID):'';
        $oType = $eqTypeIOID?$this->_o_DB->fetchOne(sprintf('select * from OLoaiThietBi where IOID = %1$d', $eqTypeIOID)):'';
        $where.= $oType?sprintf(' AND IFNULL(ThietBi.Ref_LoaiThietBi, 0) IN  (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d) ', $oType->lft, $oType->rgt):'';
        $where.= $eqIOID?sprintf(' AND IFNULL(ThietBi.IOID, 0) = %1$d', $eqIOID):'';

        $sql = sprintf('
            SELECT 
                ThietBi.MaThietBi AS MaThietBiNhan
                , ThietBi.TenThietBi AS TenThietBiNhan
                , CauTruc.ViTri AS ViTriNhan
                , CauTruc.BoPhan AS BoPhanNhan
                , ThietBiKhac.MaThietBi AS MaThietBiChuyen
                , ThietBiKhac.TenThietBi AS TenThietBiChuyen
                , CauTrucKhac.ViTri AS ViTriChuyen
                , CauTrucKhac.BoPhan AS BoPhanChuyen    
                , IF( IFNULL(VatTu.Ngay, "") !=  "", VatTu.Ngay, PhieuBaoTri.NgayBatDau ) AS NgayChuyen
                , VatTu.MaVatTu
                , VatTu.TenVatTu
                , PhieuBaoTri.MoTa AS TenCongViec
                , PhieuBaoTri.SoPhieu
            FROM OVatTuPBT AS VatTu
            INNER JOIN OPhieuBaoTri AS PhieuBaoTri ON VatTu.IFID_M759 = PhieuBaoTri.IFID_M759
            LEFT JOIN ODanhSachThietBi AS ThietBi ON IFNULL(PhieuBaoTri.Ref_MaThietBi, 0) = ThietBi.IOID
            LEFT JOIN OCauTrucThietBi AS CauTruc ON ThietBi.IFID_M705 = CauTruc.IFID_M705
                AND IFNULL(VatTu.Ref_ViTri, 0) = CauTruc.IOID
            LEFT JOIN ODanhSachThietBi AS ThietBiKhac ON IFNULL(VatTu.Ref_MaThietBiKhac, 0) = ThietBiKhac.IOID
            LEFT JOIN OCauTrucThietBi AS CauTrucKhac ON ThietBiKhac.IFID_M705 = CauTrucKhac.IFID_M705
                AND IFNULL(VatTu.Ref_ViTriKhac, 0) = CauTrucKhac.IOID
            WHERE IFNULL(VatTu.HinhThuc, 0) IN (2, 3, 5, 6) 
                AND (IF( IFNULL(VatTu.Ngay, "") !=  "", VatTu.Ngay, PhieuBaoTri.NgayBatDau ) BETWEEN %1$s AND %2$s) 
                %3$s
	    ', $this->_o_DB->quote($start), $this->_o_DB->quote($end), $where);

        // echo "<pre>"; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getUserByEquipOfMaintainOder($equipIOID)
    {
        $thietBi = $this->_o_DB->fetchOne(sprintf('SELECT * FROM ODanhSachThietBi WHERE IOID = %1$d', $equipIOID));
        $khuVuc  = $thietBi?$this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $thietBi->Ref_MaKhuVuc)):'';
        $khuVuc1 = $khuVuc?$this->_o_DB->fetchAll(sprintf('SELECT * FROM OKhuVuc WHERE lft <= %1$d AND rgt >= %2$d', $khuVuc->lft, $khuVuc->rgt)):array();
        $khuVuc2 = $khuVuc?$this->_o_DB->fetchAll(sprintf('SELECT * FROM OKhuVuc WHERE lft > %1$d AND rgt < %2$d', $khuVuc->lft, $khuVuc->rgt)):array();
        $fLoc    = array(0);
        foreach ($khuVuc1 as $item) { $fLoc[] = $item->IOID; }
        foreach ($khuVuc2 as $item) { $fLoc[] = $item->IOID; }
        $khuVucDonVi = $this->_o_DB->fetchAll(sprintf('SELECT * FROM OThietBi WHERE IFNULL(Ref_Ma, 0) IN (%1$s) ', implode(',', $fLoc)));
        $fWorkcenter = array(0);
        foreach ($khuVucDonVi as $item) { $fWorkcenter[] = $item->IFID_M125; }

        $sql = sprintf('
            SELECT qsusers.*, NhanVienBT.QuanLy
            FROM ONhanVien AS NhanVienBT
            INNER JOIN ODanhSachNhanVien AS DanhSachNV ON NhanVienBT.Ref_MaNV = DanhSachNV.IOID
            INNER JOIN qsusers ON IFNULL(DanhSachNV.Ref_TenTruyCap, 0) = qsusers.UID
            WHERE NhanVienBT.IFID_M125 IN (%1$s)
        ', implode(', ', $fWorkcenter));

        return $this->_o_DB->fetchAll($sql);
    }
}