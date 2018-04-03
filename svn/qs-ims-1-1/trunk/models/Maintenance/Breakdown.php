<?php
class Qss_Model_Maintenance_Breakdown extends Qss_Model_Abstract
{
	public function __construct()
	{
		parent::__construct();
	}

    public function countBreakDown(
        $startDate
        , $endDate
        , $breakReasonIOID = 0
        , $locationIOID = 0
        , $equipTypeIOID = 0
        , $equipGroupIOID = 0
        , $equipIOID = 0
    )
    {
        $filterDowntime = ''; // Loc dung may
        $filterOrder    = ''; // Loc phieu bao tri
        $filter         = '';

        // Loc theo thoi gian
        if($startDate && $endDate)
        {
            $filterDowntime .= sprintf(' AND (DungMayKeHoach.NgayDungMay <= %2$s AND DungMayKeHoach.NgayKetThucDungMay >= %1$s ) '
                , $this->_o_DB->quote($startDate), $this->_o_DB->quote($endDate));
            $filterOrder    .= sprintf(' AND (DungMayBaoTri.NgayDungMay <= %2$s AND DungMayBaoTri.NgayKetThucDungMay >= %1$s )'
                , $this->_o_DB->quote($startDate), $this->_o_DB->quote($endDate));
        }

        // Loc theo nguyen nhan
        $filterDowntime .= $breakReasonIOID?sprintf(' AND NguyenNhan.IOID = %1$d ', $breakReasonIOID):'';
        $filterOrder    .= $breakReasonIOID?sprintf(' AND NguyenNhan.IOID = %1$d ', $breakReasonIOID):'';

        // Loc theo khu vuc
        $locName         = $locationIOID?$this->_o_DB->fetchOne(sprintf('select * from OKhuVuc where IOID = %1$d', @(int)$locationIOID)):false;
        $filterDowntime .= $locName?sprintf(' AND (ifnull(ThietBi.Ref_MaKhuVuc, 0) IN  (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d)) ', $locName->lft, $locName->rgt):'';
        $filterOrder    .= $locName?sprintf(' AND (ifnull(ThietBi.Ref_MaKhuVuc, 0) IN  (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d)) ', $locName->lft, $locName->rgt):'';

        // Loc theo loai thiet bi
        $equpType        = $equipTypeIOID?$this->_o_DB->fetchOne(sprintf('select * from OLoaiThietBi where IOID = %1$d', @(int)$equipTypeIOID)):false;
        $filterDowntime .= $equpType?sprintf(' AND (ifnull(ThietBi.Ref_LoaiThietBi, 0) IN  (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d)) ', $equpType->lft, $equpType->rgt):'';
        $filterOrder    .= $equpType?sprintf(' AND (ifnull(ThietBi.Ref_LoaiThietBi, 0) IN  (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d)) ', $equpType->lft, $equpType->rgt):'';

        // Loc theo nhom thiet bi
        $filterDowntime .= $equipGroupIOID?sprintf(' AND ThietBi.Ref_NhomThietBi = %1$d ', $equipGroupIOID):'';
        $filterOrder    .= $equipGroupIOID?sprintf(' AND ThietBi.Ref_NhomThietBi = %1$d ', $equipGroupIOID):'';

        // Loc theo thiet bi
        $filterDowntime .= $equipIOID?sprintf(' AND ThietBi.IOID = %1$d ', $equipIOID):'';
        $filterOrder    .= $equipIOID?sprintf(' AND ThietBi.IOID = %1$d ', $equipIOID):'';

        $sql = sprintf('
			SELECT count(1) AS Total
			FROM
			(
                -- Dung may
				(
					SELECT
						\'DOWNTIME\' AS `Type`
						, DungMayKeHoach.IFID_M707 AS IFID
					FROM OPhieuSuCo AS DungMayKeHoach
					LEFT JOIN ONguyenNhanSuCo AS NguyenNhan ON DungMayKeHoach.Ref_MaNguyenNhanSuCo = NguyenNhan.IOID
					LEFT JOIN ODanhSachThietBi AS ThietBi ON DungMayKeHoach.Ref_MaThietBi = ThietBi.IOID
					LEFT JOIN OCauTrucThietBi AS CauTruc ON ThietBi.IFID_M705 = CauTruc.IFID_M705
						AND IFNULL(DungMayKeHoach.Ref_BoPhan, 0) = CauTruc.IOID
					WHERE 1=1 %1$s

				)
				UNION ALL
				-- Dung may phieu bao tri
				(
					SELECT
					    \'WORKORDER\' AS `Type`
						, DungMayBaoTri.IFID_M759 AS IFID
					FROM OPhieuBaoTri AS DungMayBaoTri
					LEFT JOIN ONguyenNhanSuCo AS NguyenNhan ON DungMayBaoTri.Ref_MaNguyenNhanSuCo = NguyenNhan.IOID
					LEFT JOIN ODanhSachThietBi AS ThietBi ON DungMayBaoTri.Ref_MaThietBi = ThietBi.IOID
					LEFT JOIN OCauTrucThietBi AS CauTruc ON ThietBi.IFID_M705 = CauTruc.IFID_M705
						AND IFNULL(DungMayBaoTri.Ref_BoPhan, 0) = CauTruc.IOID
					WHERE IFNULL(DungMayBaoTri.Ref_MaNguyenNhanSuCo, 0) != 0 %2$s
				)
			) AS DungMay
		', $filterDowntime, $filterOrder);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchOne($sql);
    }

	public function getBreakDown(
		$startDate
		, $endDate
		, $breakReasonIOID = 0
		, $locationIOID = 0
		, $equipTypeIOID = 0
		, $equipGroupIOID = 0
		, $equipIOID = 0
        , $page = 0
        , $display = 0)
	{
        $limit          = ''; // Phan trang
		$filterDowntime = ''; // Loc dung may
		$filterOrder    = ''; // Loc phieu bao tri
		$filter         = '';

		// Loc theo thoi gian
		if($startDate && $endDate)
		{
			$filterDowntime .= sprintf(' AND (DungMayKeHoach.NgayDungMay <= %2$s AND DungMayKeHoach.NgayKetThucDungMay >= %1$s ) '
				, $this->_o_DB->quote($startDate), $this->_o_DB->quote($endDate));
			$filterOrder    .= sprintf(' AND (DungMayBaoTri.NgayDungMay <= %2$s AND DungMayBaoTri.NgayKetThucDungMay >= %1$s )'
				, $this->_o_DB->quote($startDate), $this->_o_DB->quote($endDate));
		}

		// Loc theo nguyen nhan
		$filterDowntime .= $breakReasonIOID?sprintf(' AND NguyenNhan.IOID = %1$d ', $breakReasonIOID):'';
		$filterOrder    .= $breakReasonIOID?sprintf(' AND NguyenNhan.IOID = %1$d ', $breakReasonIOID):'';

		// Loc theo khu vuc
		$locName         = $locationIOID?$this->_o_DB->fetchOne(sprintf('select * from OKhuVuc where IOID = %1$d', @(int)$locationIOID)):false;
		$filterDowntime .= $locName?sprintf(' AND (ifnull(ThietBi.Ref_MaKhuVuc, 0) IN  (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d)) ', $locName->lft, $locName->rgt):'';
		$filterOrder    .= $locName?sprintf(' AND (ifnull(ThietBi.Ref_MaKhuVuc, 0) IN  (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d)) ', $locName->lft, $locName->rgt):'';

		// Loc theo loai thiet bi
		$equpType        = $equipTypeIOID?$this->_o_DB->fetchOne(sprintf('select * from OLoaiThietBi where IOID = %1$d', @(int)$equipTypeIOID)):false;
		$filterDowntime .= $equpType?sprintf(' AND (ifnull(ThietBi.Ref_LoaiThietBi, 0) IN  (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d)) ', $equpType->lft, $equpType->rgt):'';
		$filterOrder    .= $equpType?sprintf(' AND (ifnull(ThietBi.Ref_LoaiThietBi, 0) IN  (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d)) ', $equpType->lft, $equpType->rgt):'';

		// Loc theo nhom thiet bi
		$filterDowntime .= $equipGroupIOID?sprintf(' AND ThietBi.Ref_NhomThietBi = %1$d ', $equipGroupIOID):'';
		$filterOrder    .= $equipGroupIOID?sprintf(' AND ThietBi.Ref_NhomThietBi = %1$d ', $equipGroupIOID):'';

		// Loc theo thiet bi
		$filterDowntime .= $equipIOID?sprintf(' AND ThietBi.IOID = %1$d ', $equipIOID):'';
		$filterOrder    .= $equipIOID?sprintf(' AND ThietBi.IOID = %1$d ', $equipIOID):'';

        // Phan trang
        if($page && $display)
        {
            $start   = ceil(($page - 1) * $display);
            $limit   = " limit {$start}, {$display}";
        }

		$sql = sprintf('
			SELECT *
			FROM
			(
				-- Dung may
				(
					SELECT
						null AS SoPhieu
						, ThietBi.MaThietBi
						, ThietBi.TenThietBi
						, ThietBi.IOID AS Ref_MaThietBi
						, CauTruc.ViTri
						, CauTruc.BoPhan
						, CauTruc.IOID AS Ref_BoPhan
						, CauTruc.lft AS BoPhanLft
						, DungMayKeHoach.NgayDungMay
						, DungMayKeHoach.NgayKetThucDungMay
						, NguyenNhan.Ma AS MaNguyenNhanSuCo
						, NguyenNhan.Ten AS TenNguyenNhanSuCo
						, NguyenNhan.IOID AS Ref_MaNguyenNhanSuCo
						, \'DOWNTIME\' AS `Type`
						, DungMayKeHoach.IFID_M707 AS IFID
						, DungMayKeHoach.DeptID
					FROM OPhieuSuCo AS DungMayKeHoach
					LEFT JOIN ONguyenNhanSuCo AS NguyenNhan ON DungMayKeHoach.Ref_MaNguyenNhanSuCo = NguyenNhan.IOID
					LEFT JOIN ODanhSachThietBi AS ThietBi ON DungMayKeHoach.Ref_MaThietBi = ThietBi.IOID
					LEFT JOIN OCauTrucThietBi AS CauTruc ON ThietBi.IFID_M705 = CauTruc.IFID_M705
						AND IFNULL(DungMayKeHoach.Ref_BoPhan, 0) = CauTruc.IOID
					WHERE 1=1 %1$s

				)
				UNION ALL
				-- Dung may phieu bao tri
				(
					SELECT
						DungMayBaoTri.SoPhieu
						, ThietBi.MaThietBi
						, ThietBi.TenThietBi
						, ThietBi.IOID AS Ref_MaThietBi
						, CauTruc.ViTri
						, CauTruc.BoPhan
						, CauTruc.IOID AS Ref_BoPhan
						, CauTruc.lft AS BoPhanLft
						, DungMayBaoTri.NgayDungMay
						, DungMayBaoTri.NgayKetThucDungMay
						, NguyenNhan.Ma AS MaNguyenNhanSuCo
						, NguyenNhan.Ten AS TenNguyenNhanSuCo
						, NguyenNhan.IOID AS Ref_MaNguyenNhanSuCo
						, \'WORKORDER\' AS `Type`
						, DungMayBaoTri.IFID_M759 AS IFID
						, DungMayBaoTri.DeptID
					FROM OPhieuBaoTri AS DungMayBaoTri
					LEFT JOIN ONguyenNhanSuCo AS NguyenNhan ON DungMayBaoTri.Ref_MaNguyenNhanSuCo = NguyenNhan.IOID
					LEFT JOIN ODanhSachThietBi AS ThietBi ON DungMayBaoTri.Ref_MaThietBi = ThietBi.IOID
					LEFT JOIN OCauTrucThietBi AS CauTruc ON ThietBi.IFID_M705 = CauTruc.IFID_M705
						AND IFNULL(DungMayBaoTri.Ref_BoPhan, 0) = CauTruc.IOID
					WHERE IFNULL(DungMayBaoTri.Ref_MaNguyenNhanSuCo, 0) != 0 %2$s
				)
			) AS DungMay
			ORDER BY MaThietBi, BoPhanLft
			%3$s
		', $filterDowntime, $filterOrder, $limit);
		// echo '<pre>'; print_r($sql); die;
		return $this->_o_DB->fetchAll($sql);
	}


    public function getTotalDowntime($startDate, $endDate)
    {
        if(Qss_Lib_System::fieldActive('OPhieuBaoTri', 'MaNguyenNhanSuCo'))
        {
            $sql = sprintf('
			SELECT SUM(IFNULL(ThoiGianDungMay, 0)) AS TongThoiGianDungMay
			FROM
			(
				-- Dung may
				(
					SELECT
						IFNULL(DungMayKeHoach.ThoiGianDungMay, 0) AS ThoiGianDungMay
					FROM OPhieuSuCo AS DungMayKeHoach
					WHERE (DungMayKeHoach.NgayDungMay <= %2$s AND DungMayKeHoach.NgayKetThucDungMay >= %1$s )

				)
				UNION ALL
				-- Dung may phieu bao tri
				(
					SELECT
						IFNULL(DungMayBaoTri.ThoiGianDungMay, 0) AS ThoiGianDungMay
					FROM OPhieuBaoTri AS DungMayBaoTri
					WHERE IFNULL(DungMayBaoTri.Ref_MaNguyenNhanSuCo, 0) != 0
					    AND (DungMayBaoTri.NgayDungMay <= %2$s AND DungMayBaoTri.NgayKetThucDungMay >= %1$s )
				)
			) AS DungMay
		    ', $this->_o_DB->quote($startDate), $this->_o_DB->quote($endDate));
        }
        else
        {
            $sql = sprintf('
			SELECT SUM(IFNULL(ThoiGianDungMay, 0)) AS TongThoiGianDungMay
			FROM
			(
				-- Dung may
				(
					SELECT
						IFNULL(DungMayKeHoach.ThoiGianDungMay, 0) AS ThoiGianDungMay
					FROM OPhieuSuCo AS DungMayKeHoach
					WHERE (DungMayKeHoach.NgayDungMay <= %2$s AND DungMayKeHoach.NgayKetThucDungMay >= %1$s )

				)
				UNION ALL
				-- Dung may phieu bao tri
				(
					SELECT
						IFNULL(DungMayBaoTri.ThoiGianDungMay, 0) AS ThoiGianDungMay
					FROM OPhieuBaoTri AS DungMayBaoTri
					WHERE IFNULL(DungMayBaoTri.Ref_MaNguyenNhanSuCo, 0) != 0
					    AND (DungMayBaoTri.NgayDungMay <= %2$s AND DungMayBaoTri.NgayKetThucDungMay >= %1$s )
				)
			) AS DungMay
		', $this->_o_DB->quote($startDate), $this->_o_DB->quote($endDate));
        }

        // echo '<pre>'; print_r($sql); die;
        $dataSql = $this->_o_DB->fetchOne($sql);

        return $dataSql?$dataSql->TongThoiGianDungMay:0;
    }

	/**
	 * @use-in: M707
	 * @return mixed
	 */
	public function getReasonList()
	{
		$sql = sprintf('
			SELECT *
			FROM ONguyenNhanSuCo
			ORDER BY Ma
		');
		return $this->_o_DB->fetchAll($sql);
	}

    /**
     * @param $start
     * @param $end
     * @param int $locIOID
     * @param int $eqIOID
     * @param int $eqGroupIOID
     * @param int $eqTypeIOID
     * @param int $breakdownIOID
     * @return mixed
     */
    public function getDowntimeByCause($start, $end, $locIOID = 0, $eqIOID = 0, $eqGroupIOID = 0, $eqTypeIOID = 0, $breakdownIOID = 0)
    {
        $where   = '';
        $locName = $locIOID?$this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locIOID)):'';
        $eqTypes = $eqTypeIOID?$this->_o_DB->fetchOne(sprintf('select * from OLoaiThietBi where IOID = %1$d', $eqTypeIOID)):'';
        $where  .= sprintf(' AND (phieubt.NgayBatDau between %1$s and %2$s)', $this->_o_DB->quote($start), $this->_o_DB->quote($end));
        $where  .= $locName?sprintf('
            AND IFNULL(thietbi.Ref_MaKhuVuc, 0) IN (SELECT IOID FROM OKhuVuc WHERE lft >= %1$d AND rgt <= %2$d)'
            , $locName->lft, $locName->rgt):'';
        $where  .= $eqTypes?sprintf('
            AND thietbi.Ref_LoaiThietBi IN (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d) '
            ,$eqTypes->lft, $eqTypes->rgt):'';
        $where  .= $eqGroupIOID?sprintf(' AND thietbi.Ref_NhomThietBi = %1$d ', $eqGroupIOID):'';
        $where  .= $eqIOID?sprintf(' AND thietbi.IOID = %1$d ', $eqIOID):'';
        $where  .= $breakdownIOID?sprintf(' AND phieubt.Ref_MaNguyenNhanSuCo = %1$d ', $breakdownIOID):'';

        $sql = sprintf('
				SELECT 					
					phieubt.*
					, nguyennhan.Ma as MaNguyenNhanSuCo
					, nguyennhan.Ten as TenNguyenNhanSuCo
					, thietbi.MaKhuVuc AS KhuVucHienTai
				FROM OPhieuBaoTri as phieubt				
				INNER JOIN ODanhSachThietBi as thietbi on thietbi.IOID = phieubt.Ref_MaThietBi
				INNER JOIN OPhanLoaiBaoTri AS LoaiBaoTri ON phieubt.Ref_LoaiBaoTri = LoaiBaoTri.IOID
				LEFT JOIN ONguyenNhanSuCo as nguyennhan on nguyennhan.IOID = phieubt.Ref_MaNguyenNhanSuCo 
				WHERE LoaiBaoTri.LoaiBaoTri = %2$s  %1$s
				ORDER BY phieubt.Ngay DESC, phieubt.Ref_MaThietBi'
            , $where, $this->_o_DB->quote(Qss_Lib_Extra_Const::MAINT_TYPE_BREAKDOWN));
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);

        /**
         * phieubt.SoPhieu as SoPhieu
        , phieubt.Ngay as NgayYeuCauBaoTri
        , phieubt.MaThietBi as MaThietBiKhuVuc
        , phieubt.TenThietBi as TenThietBiKhuVuc

        , phieubt.MaDVBT as MaDonViBaoTri
        , phieubt.TenDVBT as TenDonViBaoTri
        , phieubt.NgayDungMay as NgayBatDauDungMay
        , phieubt.NgayKetThucDungMay as NgayKetThucDungMay
        , phieubt.ThoiGianDungMay as ThoiGianDungMay
        , phieubt.MaNguyenNhanSuCo as MaNguyenNhanSuCo
        , phieubt.Ngay as NgayPhieuBaoTri
         */
    }
	
	/**
	 * @note: Han che cua bao cao la chua loai dc thiet bi o trong khu vuc dung may
	 * trong cung mot khoang thoi gian neu ton tai ca hai phieu cho ca khu vuc 
	 * va thiet bi thuoc khu vuc do
	 * @use-in: M743
	 * @param int  $eqIOID Loc theo thiet bi
	 * @param int  $eqTypeIOID Loc theo loai thiet bi
	 * @param int  $eqGroupIOID Loc theo nhom thiet bi
	 * @param int  $locIOID Loc theo khu vuc
	 * @param int  $locStopIOID Loc theo khu vuc dung may
	 * @param date $start Loc theo thoi gian
	 * @param date $end Loc theo thoi gian
	 */	
	public function getBreakdownByReason($start, $end, $eqIOID, $eqTypeIOID, $eqGroupIOID, $locIOID)
	{
		$where = '';
		if($eqIOID){		
			if(is_array($eqIOID)){
				$where .= sprintf(' and dstb.IOID in (%1$s)'
					, implode(',', $eqIOID));
			}
			else{
				$where .= sprintf(' and dstb.IOID = %1$d', $eqIOID);
			}	
		}		
		if($eqTypeIOID 
			&& Qss_Lib_Extra::checkFieldExists('ODanhSachThietBi', 'Ref_LoaiThietBi'))
		{
			$where .= sprintf(' and dstb.Ref_LoaiThietBi = %1$d', $eqTypeIOID);			
		}
		if($eqGroupIOID)
		{
			$where .= sprintf(' and dstb.Ref_NhomThietBi = %1$d', $eqGroupIOID);			
		}
		if($locIOID){
			$findLocSql = sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locIOID);
			$findLoc    = $this->_o_DB->fetchOne($findLocSql);
			if($findLoc){
				$where .= sprintf(' and pbt.Ref_MaThietBi in (
						select IOID from ODanhSachThietBi where Ref_MaKhuVuc in 
						(select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))'
					, $findLoc->lft, $findLoc->rgt);
			}
		}
		$sql = sprintf('
			SELECT 
				nnsc.IOID
				, nnsc.Ma
				, nnsc.Ten
				, count(pbt.IOID) as total
				, sum(IFNULL(pbt.ThoiGianDungMay, 0)) as ThoiGianDungMay
				, sum(IFNULL(pbt.ThoiGianXuLy, 0)) as ThoiGianXuLy
			FROM ONguyenNhanSuCo as nnsc 
			LEFT JOIN
			(
					SELECT pbt.Ref_MaNguyenNhanSuCo, pbt.IOID, ThoiGianDungMay, ThoiGianXuLy
					FROM OPhieuBaoTri AS pbt
					INNER JOIN ODanhSachThietBi AS dstb ON pbt.Ref_MaThietBi = dstb.IOID
					WHERE ifnull(pbt.Ref_MaNguyenNhanSuCo, 0) <> 0
						and (pbt.NgayDungMay between %1$s and %2$s)
						%3$s
			) AS pbt ON pbt.Ref_MaNguyenNhanSuCo = nnsc.IOID
			GROUP BY nnsc.IOID
			ORDER BY count(pbt.IOID) desc'
		, $this->_o_DB->quote($start)
		, $this->_o_DB->quote($end)
		, $where);
		//echo '<pre>';echo $sql;die;
		return $this->_o_DB->fetchAll($sql);
	}

	
	/**
	 * @note: Han che cua bao cao la chua loai dc thiet bi o trong khu vuc dung may
	 * trong cung mot khoang thoi gian neu ton tai ca hai phieu cho ca khu vuc 
	 * va thiet bi thuoc khu vuc do
	 * @use-in: M744 M750
	 * @param int  $eqIOID Loc theo thiet bi
	 * @param int  $eqTypeIOID Loc theo loai thiet bi
	 * @param int  $eqGroupIOID Loc theo nhom thiet bi
	 * @param int  $locIOID Loc theo khu vuc
	 * @param int  $locStopIOID Loc theo khu vuc dung may
	 * @param date $start Loc theo thoi gian
	 * @param date $end Loc theo thoi gian
	 */	
	public function getDowntimeStatisticsByPeriod(
		$start
		, $end
		, $period
		, $eqIOID
		, $eqTypeIOID = 0
		, $eqGroupIOID = 0
		, $locIOID = 0)
	{
	$where = '';
		if($eqIOID){		
			if(is_array($eqIOID)){
				$where .= sprintf(' and pbt.Ref_MaThietBi in (%1$s)'
					, implode(',', $eqIOID));
			}
			else{
				$where .= sprintf(' and pbt.Ref_MaThietBi = %1$d', $eqIOID);
			}	
		}		
		if($eqTypeIOID 
			&& Qss_Lib_Extra::checkFieldExists('ODanhSachThietBi', 'Ref_LoaiThietBi'))
		{
			$where .= sprintf(' and pbt.Ref_LoaiThietBi = %1$d', $eqTypeIOID);			
		}
		if($eqGroupIOID)
		{
			$where .= sprintf(' and pbt.Ref_NhomThietBi = %1$d', $eqGroupIOID);			
		}
		if($locIOID){
			$findLocSql = sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locIOID);
			$findLoc    = $this->_o_DB->fetchOne($findLocSql);
			if($findLoc){
				$where .= sprintf(' and pbt.IOID in (
						select IOID from ODanhSachThietBi where Ref_MaKhuVuc in 
						(select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))'
					, $findLoc->lft, $findLoc->rgt);
			}
		}
		
		$sql = sprintf('
			SELECT 
				pbt.NgayYeuCau as NgayThu
				, pbt.NgayYeuCau as Ngay
				, week(pbt.NgayYeuCau) as Tuan
				, month(pbt.NgayYeuCau) as Thang
				, quarter(pbt.NgayYeuCau) as Quy
				, year(pbt.NgayYeuCau) as Nam
				, pbt.MaThietBi
				, pbt.TenThietBi
				, sum(pbt.ThoiGianDungMay) as ThoiGianDungMay
				, sum(pbt.ThoiGianXuLy) as ThoiGianXuLy
				, count(*) as SoLanDungMay
                , pbt.EQIOID as Ref_MaThietBi 				
			FROM
			(
				/* Lay ra loi theo thiet bi */
				(
					SELECT pbt.IOID
					, ifnull(pbt.ThoiGianDungMay, 0) AS ThoiGianDungMay
					, 0 AS ThoiGianXuLy
					, pbt.Ref_MaThietBi AS EQIOID
					, pbt.NgayDungMay as NgayYeuCau
					, pbt.MaThietBi
					, pbt.TenThietBi
					FROM OPhieuSuCo AS pbt
					INNER JOIN ODanhSachThietBi AS dstb ON pbt.Ref_MaThietBi = dstb.IOID
					WHERE (pbt.NgayDungMay between %1$s and %2$s)
						%3$s
				)
				UNION ALL
				/* Lay ra loi theo dung may */
				(
					SELECT pbt.IOID
					, ifnull(pbt.ThoiGianDungMay, 0) AS ThoiGianDungMay
					, ifnull(pbt.ThoiGianXuLy, 0) AS ThoiGianXuLy
					, pbt.Ref_MaThietBi AS EQIOID
					, pbt.NgayDungMay as NgayYeuCau
					, pbt.MaThietBi
					, pbt.TenThietBi
					FROM OPhieuBaoTri AS pbt
					INNER JOIN ODanhSachThietBi AS dstb ON pbt.Ref_MaThietBi = dstb.IOID
					WHERE (pbt.NgayDungMay between %1$s and %2$s)
						%3$s
				)
			) AS pbt
			%4$s /* GROUP BY THEO THOI GIAN */'
		, $this->_o_DB->quote($start)
		, $this->_o_DB->quote($end)
		, $where
		, $this->getGroupByForGetDowntimeStatisticsByPeriodFunc($period));
		//echo '<pre>';echo $sql;die;
		return $this->_o_DB->fetchAll($sql);
	}	
	
	private function getGroupByForGetDowntimeStatisticsByPeriodFunc($period)
	{
		$sql = '';
		switch ($period)
		{
			case Qss_Lib_Extra_Const::PERIOD_TYPE_DAILY:
				$sql  = ' GROUP BY day(pbt.NgayYeuCau), pbt.EQIOID';
				$sql .= ' ORDER BY pbt.EQIOID, day(pbt.NgayYeuCau)';
			break;
			case Qss_Lib_Extra_Const::PERIOD_TYPE_WEEKLY:
				$sql = ' GROUP BY pbt.EQIOID, Year(pbt.NgayYeuCau),  WEEK(pbt.NgayYeuCau, 3) ';
				$sql .= ' ORDER BY pbt.EQIOID, Year(pbt.NgayYeuCau),  WEEK(pbt.NgayYeuCau, 3)';
			break;
			case Qss_Lib_Extra_Const::PERIOD_TYPE_MONTHLY:
				$sql = ' GROUP BY Year(pbt.NgayYeuCau), month(pbt.NgayYeuCau), pbt.EQIOID';
				$sql .= ' ORDER BY pbt.EQIOID, Year(pbt.NgayYeuCau), month(pbt.NgayYeuCau)';
			break;
			case Qss_Lib_Extra_Const::PERIOD_TYPE_QUARTERLY:
				$sql = ' GROUP BY year(pbt.NgayYeuCau), quarter(pbt.NgayYeuCau), pbt.EQIOID';
				$sql .= ' ORDER BY pbt.EQIOID, year(pbt.NgayYeuCau), quarter(pbt.NgayYeuCau)';
			break;		
			case Qss_Lib_Extra_Const::PERIOD_TYPE_YEARLY:
				$sql = ' GROUP BY year(pbt.NgayYeuCau),pbt.EQIOID ';
				$sql .= ' ORDER BY pbt.EQIOID, year(pbt.NgayYeuCau)';
			break;
		}
		return $sql;
	}


	/**
	 * @use-in: M755
	 * @param $start
	 * @param $end
	 * @param $period
	 * @param array $equipFilter
	 * @return mixed
	 */
	public function getDowntimeFromWorkOrder($start, $end, $period, $equipFilter = array())
	{
	    $where = '';
	    switch ($period)
	    {
	        case 'D':
	            $timeSelectBreakdown = ', pbt.NgayDungMay as NgayDungMay ';
	            $timeGroupCorrect = ', pbt.NgayDungMay';
	            $timeOrderCorrect = ', pbt.NgayDungMay';
	            break;
	        case 'W':
	            $timeSelectBreakdown = ', year(pbt.NgayDungMay) as Nam, week(pbt.NgayDungMay) as Tuan ';
	            $timeGroupCorrect = ', year(pbt.NgayDungMay) , week(pbt.NgayDungMay)  ';
	            $timeOrderCorrect = ', year(pbt.NgayDungMay) , week(pbt.NgayDungMay)  ';
	            break;
	        case 'M':
	            $timeSelectBreakdown = ', year(pbt.NgayDungMay) as Nam, month(pbt.NgayDungMay) as Thang ';
	            $timeGroupCorrect = ', year(pbt.NgayDungMay) , month(pbt.NgayDungMay)  ';
	            $timeOrderCorrect = ', year(pbt.NgayDungMay) , month(pbt.NgayDungMay)  ';
	            break;
	        case 'Q':
	            $timeSelectBreakdown = ', year(pbt.NgayDungMay) as Nam, quarter(pbt.NgayDungMay) as Quy ';
	            $timeGroupCorrect = ', year(pbt.NgayDungMay) , quarter(pbt.NgayDungMay)  ';
	            $timeOrderCorrect = ', year(pbt.NgayDungMay) , quarter(pbt.NgayDungMay)  ';
	            break;
	        case 'Y':
	            $timeSelectBreakdown = ', year(pbt.NgayDungMay) as Nam ';
	            $timeGroupCorrect = ', year(pbt.NgayDungMay)  ';
	            $timeOrderCorrect = ', year(pbt.NgayDungMay)   ';
	            break;
	        default:
	            $timeSelectBreakdown = '';
	            $timeGroupCorrect = '';
	            $timeOrderCorrect = '';
	
	            break;
	    }
	
	    if (is_array($equipFilter) && count($equipFilter))
	    {
	        $where = sprintf(' WHERE pbt.EQIOID in (%1$s)', implode(', ', $equipFilter));
	    }
	
	    // $sql breakdown
	    $sqlCorrect = sprintf('
			SELECT
				sum(ifnull(ThoiGianDungMay,0)) as DungMay
				, pbt.EQIOID AS Ref_MaThietBi
				%3$s
			FROM
			(
				/* Lay ra loi theo thiet bi */
				(
					SELECT pbt.IOID
					, ifnull(pbt.ThoiGianDungMay, 0) AS ThoiGianDungMay
					, pbt.Ref_MaThietBi AS EQIOID
					, pbt.Ngay as NgayYeuCau
					, pbt.MaThietBi
					, pbt.TenThietBi
					%3$s
					FROM OPhieuBaoTri AS pbt
					INNER JOIN ODanhSachThietBi AS dstb ON pbt.Ref_MaThietBi = dstb.IOID
					WHERE (pbt.NgayDungMay between %1$s and %2$s) /* Dieu kien loc dung may theo thiet bi*/
				)
				UNION ALL
				/* Lay ra loi theo  dung may */
				(
					SELECT pbt.IOID
					, ifnull(pbt.ThoiGianDungMay, 0) AS ThoiGianDungMay
					, pbt.Ref_MaThietBi AS EQIOID
					, pbt.NgayYeuCau
					, pbt.MaThietBi
					, pbt.TenThietBi
					%3$s
					FROM OPhieuSuCo AS pbt
					INNER JOIN ODanhSachThietBi AS dstb ON pbt.Ref_MaThietBi = dstb.IOID
					WHERE (pbt.NgayDungMay between %1$s and %2$s) /* Dieu kien loc dung may theo thiet bi*/
				)
			) AS pbt
        '
        , $this->_o_DB->quote($start)
        , $this->_o_DB->quote($end)
        , $timeSelectBreakdown);
	    
	    $sqlCorrect .= $where;
	    $sqlCorrect .= ' group by pbt.EQIOID ' . $timeGroupCorrect;
	    $sqlCorrect .= ' order by pbt.EQIOID ' . $timeOrderCorrect;
	    return $this->_o_DB->fetchAll($sqlCorrect);
	
	}

	/**
	 * @use-in: M780
	 * @param $equipIOID
	 * @param string $startDate
	 * @param string $endDate
	 * @return mixed
	 */
	public function getFailureListByEquip($equipIOID, $startDate = '', $endDate = '')
	{
        $conJoin = '';
        if($startDate && $endDate)
        {
            $conJoin = sprintf(' AND SuCo.NgayYeuCau between %1$s and %2$s ', $this->_o_DB->quote($startDate), $this->_o_DB->quote($endDate));
        }
        elseif($startDate)
        {
            $conJoin = sprintf(' AND SuCo.NgayYeuCau >= %1$s', $this->_o_DB->quote($startDate));
        }
        elseif($endDate)
        {
            $conJoin = sprintf(' AND SuCo.NgayYeuCau <= %1$s ', $this->_o_DB->quote($endDate));
        }


		$sql = sprintf('
			SELECT Loai.*, sum(case when ifnull(DSHuHong.IOID,0) != 0 then 1 else 0 end) AS TanSuat
			FROM ODanhSachThietBi AS ThietBi
			INNER JOIN OClassHuHong AS Class ON ThietBi.Ref_ClassHuHong = Class.IOID
			INNER JOIN OLoaiHuHong AS Loai ON Class.IFID_M172 = Loai.IFID_M172
			LEFT JOIN OPhieuBaoTri AS SuCo On ThietBi.IOID = SuCo.Ref_MaThietBi %2$s
			LEFT JOIN ODanhMucBoPhanHuHong AS DSHuHong ON SuCo.IFID_M759 = DSHuHong.IFID_M759
			    AND Loai.IOID = ifnull(DSHuHong.Ref_LoaiHuHong, 0)
			WHERE ThietBi.IOID = %1$d
			GROUP BY Loai.IOID
			/*having TanSuat > 0*/
			ORDER BY TanSuat DESC
		', $equipIOID, $conJoin);
        // echo '<pre>'; print_r($sql); die;
		return $this->_o_DB->fetchAll($sql);
	}

	/**
	 * @use-in: M780
	 * @param int $equipIOID
	 * @param int $componentIOID
	 * @param string $startDate
	 * @param string $endDate
	 * @return mixed
	 */
    public function getFailureListOfComponents($equipIOID = 0, $componentIOID = 0, $startDate = '', $endDate = '')
    {
        $conJoin = '';
        if($startDate && $endDate)
        {
            $conJoin = sprintf(' AND SuCo.NgayYeuCau between %1$s and %2$s ', $this->_o_DB->quote($startDate), $this->_o_DB->quote($endDate));
        }
        elseif($startDate)
        {
            $conJoin = sprintf(' AND SuCo.NgayYeuCau >= %1$s', $this->_o_DB->quote($startDate));
        }
        elseif($endDate)
        {
            $conJoin = sprintf(' AND SuCo.NgayYeuCau <= %1$s ', $this->_o_DB->quote($endDate));
        }

        $where  = $componentIOID?sprintf(' AND CauTruc.IOID = %1$d ',$componentIOID):'';
        $where .= $equipIOID?sprintf(' AND ThietBi.IOID = %1$d ',$equipIOID):'';

        $sql = sprintf('
			SELECT Loai.*, sum(case when ifnull(DSHuHong.IOID,0) != 0 then 1 else 0 end) AS TanSuat
				FROM ODanhSachThietBi AS ThietBi
				INNER JOIN OCauTrucThietBi AS CauTruc ON ThietBi.IFID_M705 = CauTruc.IFID_M705
				INNER JOIN OClassHuHong AS Class ON CauTruc.Ref_ClassHuHong = Class.IOID
				INNER JOIN OLoaiHuHong AS Loai ON Class.IFID_M172 = Loai.IFID_M172
				LEFT JOIN OPhieuBaoTri AS SuCo On ThietBi.IOID = SuCo.Ref_MaThietBi %2$s
				LEFT JOIN ODanhMucBoPhanHuHong AS DSHuHong ON SuCo.IFID_M759 = DSHuHong.IFID_M759
			    AND CauTruc.IOID = ifnull(DSHuHong.Ref_ViTri, 0)
			    AND Loai.IOID = ifnull(DSHuHong.Ref_LoaiHuHong, 0)
			WHERE  1=1 %1$s
			GROUP BY CauTruc.IOID, Loai.IOID
			having TanSuat > 0
			ORDER BY CauTruc.lft, TanSuat DESC, Loai.IOID
		', $where, $conJoin);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }


	/**
	 * @use-in: M780
	 * @param $equipIOID
	 * @param $failureIOID
	 * @param string $startDate
	 * @param string $endDate
	 * @return mixed
	 */
    public function getReasonListByEquip($equipIOID, $failureIOID, $startDate = '', $endDate = '')
    {
        $conJoin = '';
        if($startDate && $endDate)
        {
            $conJoin = sprintf(' AND SuCo.NgayYeuCau between %1$s and %2$s ', $this->_o_DB->quote($startDate), $this->_o_DB->quote($endDate));
        }
        elseif($startDate)
        {
            $conJoin = sprintf(' AND SuCo.NgayYeuCau >= %1$s', $this->_o_DB->quote($startDate));
        }
        elseif($endDate)
        {
            $conJoin = sprintf(' AND SuCo.NgayYeuCau <= %1$s ', $this->_o_DB->quote($endDate));
        }

        $sql = sprintf('
            SELECT
                NguyenNhanHuHongThietBi.*
                , sum(case when ifnull(DSHuHong.IOID,0) != 0 then 1 else 0 end) AS TanSuat
            FROM
            (
                SELECT
                    NguyenNhan.*
                    , ThietBi.IOID AS ThietBiIOID
                    , NguyenNhan.IOID AS NguyenNhanTheoLoaiIOID
                FROM ODanhSachThietBi AS ThietBi
                INNER JOIN OClassHuHong AS Class ON ThietBi.Ref_ClassHuHong = Class.IOID
                INNER JOIN OLoaiHuHong AS Loai ON Class.IFID_M172 = Loai.IFID_M172
                INNER JOIN ONguyenNhanHuHong AS NguyenNhan ON Class.IFID_M172 = NguyenNhan.IFID_M172 and NguyenNhan.Ref_MaLoai = Loai.IOID
                WHERE ThietBi.IOID = %1$d AND Loai.IOID = %2$d
			) AS NguyenNhanHuHongThietBi
			LEFT JOIN OPhieuBaoTri AS SuCo ON NguyenNhanHuHongThietBi.ThietBiIOID = SuCo.Ref_MaThietBi %3$s
			LEFT JOIN ODanhMucBoPhanHuHong AS DSHuHong ON SuCo.IFID_M759 = DSHuHong.IFID_M759
			    AND NguyenNhanHuHongThietBi.NguyenNhanTheoLoaiIOID = DSHuHong.Ref_NguyenNhan
            GROUP BY NguyenNhanHuHongThietBi.IOID
		', $equipIOID, $failureIOID, $conJoin);
        //echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

	/**
	 * @use-in: M780
	 * @param $equipIOID
	 * @param $failureIOID
	 * @param int $componentIOID
	 * @param string $startDate
	 * @param string $endDate
	 * @return mixed
	 */
    public function getReasonListOfComponents($equipIOID, $failureIOID, $componentIOID = 0, $startDate = '', $endDate = '')
    {
        $conJoin = '';
        if($startDate && $endDate)
        {
            $conJoin = sprintf(' AND SuCo.NgayYeuCau between %1$s and %2$s ', $this->_o_DB->quote($startDate), $this->_o_DB->quote($endDate));
        }
        elseif($startDate)
        {
            $conJoin = sprintf(' AND SuCo.NgayYeuCau >= %1$s', $this->_o_DB->quote($startDate));
        }
        elseif($endDate)
        {
            $conJoin = sprintf(' AND SuCo.NgayYeuCau <= %1$s ', $this->_o_DB->quote($endDate));
        }

        $where  = $componentIOID?sprintf(' AND CauTruc.IOID = %1$d ',$componentIOID):'';
        $where .= $equipIOID?sprintf(' AND ThietBi.IOID = %1$d ',$equipIOID):'';

        $sql = sprintf('
            SELECT
                NguyenNhanHuHongThietBi.*
                , sum(case when ifnull(DSHuHong.IOID,0) != 0 then 1 else 0 end) AS TanSuat
            FROM
            (
               SELECT
                    NguyenNhan.*
                    , ThietBi.IOID AS ThietBiIOID
                    , CauTruc.IOID AS CauTrucIOID
                    , NguyenNhan.IOID AS NguyenNhanTheoLoaiIOID
                FROM ODanhSachThietBi AS ThietBi
                INNER JOIN OCauTrucThietBi AS CauTruc ON ThietBi.IFID_M705 = CauTruc.IFID_M705
                INNER JOIN OClassHuHong AS Class ON CauTruc.Ref_ClassHuHong = Class.IOID
                INNER JOIN OLoaiHuHong AS Loai ON Class.IFID_M172 = Loai.IFID_M172
                INNER JOIN ONguyenNhanHuHong AS NguyenNhan ON Class.IFID_M172 = NguyenNhan.IFID_M172 and NguyenNhan.Ref_MaLoai = Loai.IOID
                WHERE  Loai.IOID = %1$d %3$s
			) AS NguyenNhanHuHongThietBi
			LEFT JOIN OPhieuBaoTri AS SuCo ON NguyenNhanHuHongThietBi.ThietBiIOID = SuCo.Ref_MaThietBi %2$s
			LEFT JOIN ODanhMucBoPhanHuHong AS DSHuHong ON SuCo.IFID_M759 = DSHuHong.IFID_M759
			    AND NguyenNhanHuHongThietBi.NguyenNhanTheoLoaiIOID = DSHuHong.Ref_NguyenNhan
			    AND NguyenNhanHuHongThietBi.CauTrucIOID = DSHuHong.Ref_ViTri
            GROUP BY NguyenNhanHuHongThietBi.IOID
            having TanSuat > 0
		', $failureIOID, $conJoin, $where);
         //echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

	/**
	 * @use-in: M780
	 * @param $equipIOID
	 * @param $reasonIOID
	 * @param string $startDate
	 * @param string $endDate
	 * @return mixed
	 */
    public function getRemedyListByEquip($equipIOID, $reasonIOID, $startDate = '', $endDate = '')
    {
        $conJoin = '';
        if($startDate && $endDate)
        {
            $conJoin = sprintf(' AND SuCo.NgayYeuCau between %1$s and %2$s ', $this->_o_DB->quote($startDate), $this->_o_DB->quote($endDate));
        }
        elseif($startDate)
        {
            $conJoin = sprintf(' AND SuCo.NgayYeuCau >= %1$s', $this->_o_DB->quote($startDate));
        }
        elseif($endDate)
        {
            $conJoin = sprintf(' AND SuCo.NgayYeuCau <= %1$s ', $this->_o_DB->quote($endDate));
        }

        $sql = sprintf('
            SELECT
                DSHuHong.BienPhapKhacPhuc
                , count(*) AS TanSuat
            FROM
            OPhieuBaoTri AS SuCo 
			INNER JOIN ODanhMucBoPhanHuHong AS DSHuHong ON SuCo.IFID_M759 = DSHuHong.IFID_M759
			WHERE SuCo.Ref_MaThietBi=%1$d and DSHuHong.Ref_NguyenNhan = %2$d %3$s 
            GROUP BY DSHuHong.BienPhapKhacPhuc
		', $equipIOID, $reasonIOID, $conJoin);
		//echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }


	/**
	 * @use-in: M780
	 * @param $equipIOID
	 * @param $reasonIOID
	 * @param int $componentIOID
	 * @param string $startDate
	 * @param string $endDate
	 * @return mixed
	 */
    public function getRemedyListOfComponents($equipIOID, $reasonIOID, $componentIOID = 0, $startDate = '', $endDate = '')
    {
        $conJoin = '';
        if($startDate && $endDate)
        {
            $conJoin = sprintf(' AND SuCo.NgayYeuCau between %1$s and %2$s ', $this->_o_DB->quote($startDate), $this->_o_DB->quote($endDate));
        }
        elseif($startDate)
        {
            $conJoin = sprintf(' AND SuCo.NgayYeuCau >= %1$s', $this->_o_DB->quote($startDate));
        }
        elseif($endDate)
        {
            $conJoin = sprintf(' AND SuCo.NgayYeuCau <= %1$s ', $this->_o_DB->quote($endDate));
        }

        $where = $componentIOID?sprintf(' AND CauTruc.IOID = %1$d ',$componentIOID):'';
        $where .= $equipIOID?sprintf(' AND ThietBi.IOID = %1$d ',$equipIOID):'';

        $sql = sprintf('
            SELECT
                DSHuHong.BienPhapKhacPhuc
                , count(*) AS TanSuat
            FROM OPhieuBaoTri AS SuCo 
			INNER JOIN ODanhMucBoPhanHuHong AS DSHuHong ON SuCo.IFID_M759 = DSHuHong.IFID_M759
			WHERE SuCo.Ref_MaThietBi = %3$d AND SuCo.Ref_BoPhan = %4$d 
			%2$s
            GROUP BY DSHuHong.BienPhapKhacPhuc %2$s
		', $reasonIOID, $conJoin, $equipIOID,$componentIOID);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }


	/**
	 * @use-in: M743
	 * @param $start
	 * @param $end
	 * @param $eqIOID
	 * @param $eqTypeIOID
	 * @param $eqGroupIOID
	 * @param $locIOID
	 * @return mixed
	 */
	public function getBreakdownByLine($start, $end, $eqIOID, $eqTypeIOID, $eqGroupIOID, $locIOID)
	{
		$where = '';
		if($eqIOID){		
			if(is_array($eqIOID)){
				$where .= sprintf(' and dstb.IOID in (%1$s)'
					, implode(',', $eqIOID));
			}
			else{
				$where .= sprintf(' and dstb.IOID = %1$d', $eqIOID);
			}	
		}		
		if($eqTypeIOID 
			&& Qss_Lib_Extra::checkFieldExists('ODanhSachThietBi', 'Ref_LoaiThietBi'))
		{
			$where .= sprintf(' and dstb.Ref_LoaiThietBi = %1$d', $eqTypeIOID);			
		}
		if($eqGroupIOID)
		{
			$where .= sprintf(' and dstb.Ref_NhomThietBi = %1$d', $eqGroupIOID);			
		}
		if($locIOID){
			$findLocSql = sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locIOID);
			$findLoc    = $this->_o_DB->fetchOne($findLocSql);
			if($findLoc){
				$where .= sprintf(' and pbt.IOID in (
						select IOID from ODanhSachThietBi where Ref_MaKhuVuc in 
						(select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))'
					, $findLoc->lft, $findLoc->rgt);
			}
		}
		$sql = sprintf('
			SELECT 
				dc.TenDayChuyen
				, count(pbt.IOID) as total
				, sum(IFNULL(pbt.ThoiGianDungMay, 0)) as ThoiGianDungMay
				, sum(IFNULL(pbt.ThoiGianXuLy, 0)) as ThoiGianXuLy
			FROM ODayChuyen as dc 
			LEFT JOIN
			(
					SELECT Ref_DayChuyen, pbt.IOID, ThoiGianDungMay, ThoiGianXuLy
					FROM OPhieuBaoTri AS pbt
					INNER JOIN ODanhSachThietBi AS dstb ON pbt.Ref_MaThietBi = dstb.IOID
					WHERE (pbt.NgayDungMay between %1$s and %2$s)
						%3$s
			) AS pbt ON pbt.Ref_DayChuyen = dc.IOID
			GROUP BY dc.IOID
			ORDER BY count(pbt.IOID) desc'
		, $this->_o_DB->quote($start)
		, $this->_o_DB->quote($end)
		, $where);
		//echo '<pre>';echo $sql;die;
		return $this->_o_DB->fetchAll($sql);
	}

	/**
	 * @use-in: M743
	 * @param $start
	 * @param $end
	 * @param $eqIOID
	 * @param $eqTypeIOID
	 * @param $eqGroupIOID
	 * @param $locIOID
	 * @return mixed
	 */
	public function getBreakdownByEquipmentType($start, $end, $eqIOID, $eqTypeIOID, $eqGroupIOID, $locIOID)
	{
		$where = '';
		if($eqIOID){		
			if(is_array($eqIOID)){
				$where .= sprintf(' and dstb.IOID in (%1$s)'
					, implode(',', $eqIOID));
			}
			else{
				$where .= sprintf(' and dstb.IOID = %1$d', $eqIOID);
			}	
		}		
		if($eqTypeIOID 
			&& Qss_Lib_Extra::checkFieldExists('ODanhSachThietBi', 'Ref_LoaiThietBi'))
		{
			$where .= sprintf(' and dstb.Ref_LoaiThietBi = %1$d', $eqTypeIOID);			
		}
		if($eqGroupIOID)
		{
			$where .= sprintf(' and dstb.Ref_NhomThietBi = %1$d', $eqGroupIOID);			
		}
		if($locIOID){
			$findLocSql = sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locIOID);
			$findLoc    = $this->_o_DB->fetchOne($findLocSql);
			if($findLoc){
				$where .= sprintf(' and pbt.IOID in (
						select IOID from ODanhSachThietBi where Ref_MaKhuVuc in 
						(select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))'
					, $findLoc->lft, $findLoc->rgt);
			}
		}
		$sql = sprintf('
			SELECT 
				loai.TenLoai
				, count(pbt.IOID) as total
				, sum(IFNULL(pbt.ThoiGianDungMay, 0)) as ThoiGianDungMay
				, sum(IFNULL(pbt.ThoiGianXuLy, 0)) as ThoiGianXuLy
			FROM OLoaiThietBi as loai 
			LEFT JOIN
			(
					SELECT Ref_LoaiThietBi, pbt.IOID, ThoiGianDungMay, ThoiGianXuLy
					FROM OPhieuBaoTri AS pbt
					INNER JOIN ODanhSachThietBi AS dstb ON pbt.Ref_MaThietBi = dstb.IOID
					where (pbt.NgayDungMay between %1$s and %2$s)
					%3$s /*@todo ktra khu vuc dung may*/
			) AS pbt ON pbt.Ref_LoaiThietBi = loai.IOID
			GROUP BY loai.IOID
			ORDER BY count(pbt.IOID) desc'
		, $this->_o_DB->quote($start)
		, $this->_o_DB->quote($end)
		, $where);
		// echo '<pre>';echo $sql;die;
		return $this->_o_DB->fetchAll($sql);
	}

	/**
	 * @use-in: M743
	 * @param $start
	 * @param $end
	 * @param $eqIOID
	 * @param $eqTypeIOID
	 * @param $eqGroupIOID
	 * @param $locIOID
	 * @return mixed
	 */
	public function getBreakdownByEquipment($start, $end, $eqIOID, $eqTypeIOID, $eqGroupIOID, $locIOID)
	{
		$where = '';
		if($eqIOID){		
			if(is_array($eqIOID)){
				$where .= sprintf(' and dstb.IOID in (%1$s)'
					, implode(',', $eqIOID));
			}
			else{
				$where .= sprintf(' and dstb.IOID = %1$d', $eqIOID);
			}	
		}		
		if($eqTypeIOID 
			&& Qss_Lib_Extra::checkFieldExists('ODanhSachThietBi', 'Ref_LoaiThietBi'))
		{
			$where .= sprintf(' and dstb.Ref_LoaiThietBi = %1$d', $eqTypeIOID);			
		}
		if($eqGroupIOID)
		{
			$where .= sprintf(' and dstb.Ref_NhomThietBi = %1$d', $eqGroupIOID);			
		}
		if($locIOID){
			$findLocSql = sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locIOID);
			$findLoc    = $this->_o_DB->fetchOne($findLocSql);
			if($findLoc){
				$where .= sprintf(' and pbt.IOID in (
						select IOID from ODanhSachThietBi where Ref_MaKhuVuc in 
						(select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))'
					, $findLoc->lft, $findLoc->rgt);
			}
		}
		$sql = sprintf('
			SELECT 
				MaThietBi
				, TenThietBi
				, count(pbt.IOID) as total
				, sum(IFNULL(pbt.ThoiGianDungMay, 0)) as ThoiGianDungMay
				, sum(IFNULL(pbt.ThoiGianXuLy, 0)) as ThoiGianXuLy
			FROM 
			(
					SELECT dstb.IOID, dstb.MaThietBi,dstb.TenThietBi,  ThoiGianDungMay, ThoiGianXuLy
					FROM OPhieuBaoTri AS pbt
					INNER JOIN ODanhSachThietBi AS dstb ON pbt.Ref_MaThietBi = dstb.IOID
					WHERE (pbt.NgayDungMay between %1$s and %2$s)
						%3$s
			) AS pbt
			GROUP BY pbt.IOID
			ORDER BY count(pbt.IOID) desc'
		, $this->_o_DB->quote($start)
		, $this->_o_DB->quote($end)
		, $where);
		//echo '<pre>';echo $sql;die;
		return $this->_o_DB->fetchAll($sql);
	}

    public function getFailureByWorkorder($ifID)
    {
        $sql = sprintf('SELECT DanhMucHuHong.*,LoaiHuHong.Ten AS Tenhuhong
                        FROM ODanhMucBoPhanHuHong AS DanhMucHuHong
                        INNER JOIN OLoaiHuHong AS LoaiHuHong ON DanhMucHuHong.Ref_LoaiHuHong = LoaiHuHong.IOID
                        WHERE DanhMucHuHong.IFID_M759 = %1$d',$ifID);
        return $this->_o_DB->fetchAll($sql);
    }
}
