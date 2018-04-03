<?php

/*
 * @todo : Cân chỉnh lại report có kỳ vì có thêm kỳ là quý
 * @todo : Cần sửa lại report có dây chuyền vì khu vực không định nghĩa dây chuyền nữa
 * @todo : Thay thế toàn bộ các hàm viết gộp
 */

class Qss_Model_Extra_Maintenance extends Qss_Model_Abstract
{
	public $_common;

	public function __construct()
	{
		parent::__construct();
		$this->_common = new Qss_Model_Extra_Extra();
	}

	
	/**
	 * Number of work orders of an employee
	 * @param int $employeeIOID 
	 * @param date $start YYYY-mm-dd
	 * @param date $end YYYY-mm-dd
	 * @return int Number of work orders of an employee
	 */
	public function countWorkOrdersByEmployee($employeeIOID, $start = '', $end = '')
	{
		$where = '';
		if($start && $end)
		{
			$where .= sprintf('AND 
				pbt.NgayBatDau >= %1$s
				AND 
				(ifnull(pbt.Ngay, \'\') = \'\'  OR pbt.Ngay <= %2$s)'
			, $this->_o_DB->quote($start)
			, $this->_o_DB->quote($end));
		}
		
		$sql = sprintf('
			SELECT count(1) AS TotalWorkOrders
			FROM OPhieuBaoTri AS pbt
			INNER JOIN qsiforms AS f ON pbt.IFID_M759 = f.IFID
			WHERE 
				pbt.Ref_NguoiThucHien = %1$d
				AND ifnull(f.Status, 1) < 3
				%2$s'
		, $employeeIOID, $where
		);
		
		$dataSql = $this->_o_DB->fetchOne($sql);
		return $dataSql?(int)$dataSql->TotalWorkOrders:0;
	}
	
	/**
	 * Number of work orders by status
	 * @param date $start YYYY-mm-dd
	 * @param date $end YYYY-mm-dd
	 * @param string $lang abbr for lang; exp: vn, en
	 * @return mix Number of work orders by status
	 */
	public function countWorkOrdersByStatus($start = '', $end = '', $lang = 'vn')
	{
		$status_name_lang = ($lang == 'vn')?'':'_'.$lang;
		
		$where = '';
		if($start && $end)
		{
			$where .= sprintf('AND 
				pbt.NgayBatDau >= %1$s
				AND 
				(ifnull(pbt.Ngay, \'\') = \'\'  OR pbt.Ngay <= %2$s)'
			, $this->_o_DB->quote($start)
			, $this->_o_DB->quote($end));
		}		
		
		$sql = sprintf('
			SELECT count(pbt.IOID) AS TotalWorkOrders
				, ifnull(f.Status, 1) AS Status 
				, wfs.Name%1$s AS Name
			FROM OPhieuBaoTri AS pbt
			INNER JOIN qsiforms AS f ON pbt.IFID_M759 = f.IFID
			INNER JOIN qsworkflows AS wf ON f.FormCode = wf.FormCode
			INNER JOIN qsworkflowsteps AS wfs ON wf.WFID = wfs.WFID 
				AND ifnull(f.Status, 1) = wfs.StepNo
			WHERE 
				1 = 1				
				%2$s
			GROUP BY ifnull(f.Status, 1)
			ORDER BY f.Status'
		, $status_name_lang
		, $where);
		return $this->_o_DB->fetchAll($sql);		
	}
	
	/**
	 * Number of work orders by task complete
	 * @param date $start YYYY-mm-dd
	 * @param date $end YYYY-mm-dd
	 * @return object Number of work orders by task complete
	 */
	public function countTasksOfWorkOrders($employeeIOID, $start =  '', $end = '')
	{
		$where = '';
		if($start && $end)
		{
			$where .= sprintf('AND 
				pbt.NgayBatDau >= %1$s
				AND 
				(ifnull(pbt.Ngay, \'\') = \'\'  OR pbt.Ngay <= %2$s)'
			, $this->_o_DB->quote($start)
			, $this->_o_DB->quote($end));
		}				
		
		$sql = sprintf('
			SELECT 
				 
				SUM(
					CASE WHEN ifnull(cv.ThucHien, 0) = 1
					THEN 1
					ELSE 0 END
				) AS `Completed`
				,  
				SUM(
					CASE WHEN  ifnull(cv.ThucHien, 0) = 0
					THEN 1
					ELSE 0 END
				) AS `Pending`
			FROM OPhieuBaoTri AS pbt
			INNER JOIN qsiforms AS f ON pbt.IFID_M759 = f.IFID
			INNER JOIN OCongViecBTPBT AS cv ON pbt.IFID_M759 = cv.IFID_M759
			WHERE cv.Ref_NguoiThucHien = %1$d
				AND ifnull(f.Status, 1) < 3
			%2$s'
		, $employeeIOID
		, $where);
		return $this->_o_DB->fetchOne($sql);		
	}
	

	// @remove
	// $locsLeftRight = array( locID => (lft => number, rgt=> number) );
	// Tra ve mot object bao gom ca khu vuc lan thiet bi trong khu vuc do
	public function getEquipByLocsLeftRigth($locsLeftRight)
	{
		// Chi thuc hien khi co mang khu vuc (chia theo kieu left right)
		if (count($locsLeftRight))
		{
			// Lay cac khu vuc con cua khu vuc truyen vao, khong lay khu vuc truyen vao
			$tempSql = '';
			foreach ($locsLeftRight as $locID => $locLR)
			{
				$tempSql .= $tempSql ? ' or ' : '';
				$tempSql .= sprintf(' ( kv.lft > %1$d and kv.rgt < %2$d) '
					, $locLR['lft'], $locLR['rgt']);
				$tempSql .= $tempSql ? ' or ' : '';
				$tempSql .= sprintf(' ( kv.IOID = %1$d ) '
					, $locID);
			}

			if ($tempSql)
			{
				$sql = $sql = sprintf(' select kv.*, tb.IOID as TBIOID, kv.IOID as KVIOID
                                                        from OKhuVuc as kv
                                                        left join ODanhSachThietBi as tb
                                                        on kv.IOID = tb.Ref_MaKhuVuc
                                                        where %1$s', $tempSql);
				//echo $sql; die;
				return $this->_o_DB->fetchAll($sql);
			}
		}
		return null;

	}


	public function getBreakdownByCause($start, $end, $filter = array())
	{
		$where = '';

		if (isset($filter['eqtype']) && $filter['eqtype'])
		{
			$where .= sprintf(' and dstb.Ref_LoaiThietBi = %1$d', $filter['eqtype']);
		}

		if (isset($filter['eqgroup']) && $filter['eqgroup'])
		{
			$where .= sprintf(' and dstb.Ref_NhomThietBi = %1$d', $filter['eqgroup']);
		}

		if (isset($filter['eq']) && $filter['eq'])
		{
			$where .= sprintf(' and bt.Ref_MaThietBi = %1$d', $filter['eq']);
		}

		if (isset($filter['loc']) && $filter['loc'])
		{
			$locName = $this->_common->getTable(array('*')
				, 'OKhuVuc'
				, array('IOID' => $filter['loc'])
				, array(), 'NO_LIMIT',  1);
			if ($locName)
				$where .= sprintf(' and bt.Ref_MaThietBi in (select IOID from ODanhSachThietBi where Ref_MaKhuVuc in (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))', $locName->lft, $locName->rgt);
		}
		
		$where = $where?sprintf(' WHERE 1=1 %1$s ', $where):'';

		//#left join ONguyenNhanBaoTri as sc on sc.Ref_Ma = loi.IOID
		//#left join OPhieuBaoTri as bt on bt.IFID_M759 = sc.IFID_M759
		$sql = sprintf('select loi.Ten, count(bt.IOID) as total
				from ONguyenNhanSuCo as loi 
				left join OPhieuBaoTri as bt on bt.Ref_MaNguyenNhanSuCo = loi.IOID 
				and (bt.Ngay between %1$s and %2$s) 
				left join ODanhSachThietBi as dstb on bt.Ref_MaThietBi = dstb.IOID
				%3$s
				group by loi.Ma
				order by count(bt.IOID) desc', $this->_o_DB->quote($start), $this->_o_DB->quote($end), $where);
		return $this->_o_DB->fetchAll($sql);

	}


	


	

	/**
	 */
	
	

	/* Remove */ 
	public function getCauseAndEffect($startDate, $endDate, $filter)
	{
		$where = '';
		// Loc theo nhom thiet bi hay loai thiet bi

		if (isset($filter['eqtype']) && $filter['eqtype'])
		{
			$where .= sprintf(' and DSTB.Ref_LoaiThietBi = %1$d', $filter['eqtype']);
		}

		if (isset($filter['eqgroup']) && $filter['eqgroup'])
		{
			$where .= sprintf(' and DSTB.Ref_NhomThietBi = %1$d', $filter['eqgroup']);
		}

		if (isset($filter['eq']) && $filter['eq'])
		{
			$where .= sprintf(' and DSTB.IOID = %1$d', $filter['eq']);
		}

		if (isset($filter['loc']) && $filter['loc'])
		{
			$locName = $this->_common->getTable(array('*')
				, 'OKhuVuc'
				, array('IOID' => $filter['loc'])
				, array(), 'NO_LIMIT', 1);
			if ($locName)
				$where .= sprintf(' and PBT.Ref_MaThietBi in (select IOID from ODanhSachThietBi where Ref_MaKhuVuc in (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))', $locName->lft, $locName->rgt);
		}

		$now = date('Y-mm-dd');
		$sql = sprintf('SELECT PBT.SoPhieu as SoPhieu, PBT.Ngay as NgayYeuCauBaoTri,
                                PBT.MaThietBi as MaThietBiKhuVuc,PBT.TenThietBi as TenThietBiKhuVuc,
                                DSTB.MaKhuVuc as KhuVucHienTai, PBT.MaDVBT as MaDonViBaoTri, PBT.TenDVBT as TenDonViBaoTri,
                                PBT.NgayDungMay as NgayBatDauDungMay, PBT.NgayKetThucDungMay as NgayKetThucDungMay,
                                PBT.ThoiGianDungMay as ThoiGianDungMay, PBT.MaNguyenNhanSuCo as MaNguyenNhanSuCo, NNSC.Ten as TenNguyenNhanSuCo
                                , PBT.Ngay as NgayPhieuBaoTri
                                FROM OPhieuBaoTri as PBT
                                INNER JOIN ODanhSachThietBi as DSTB on DSTB.IOID = PBT.Ref_MaThietBi
                                INNER JOIN ONguyenNhanSuCo as NNSC on NNSC.IOID = PBT.Ref_MaNguyenNhanSuCo 
                                WHERE 
                                #QSI.Status = 2 and 
                                (PBT.Ngay between %1$s and %2$s)
                                %4$s
                                 ', $this->_o_DB->quote($startDate), $this->_o_DB->quote($endDate), $this->_o_DB->quote($now), $where);
		$sql .= ' ORDER BY PBT.Ngay DESC, PBT.Ref_MaThietBi';
		return $this->_o_DB->fetchAll($sql);
	}

	public function getItemsByLine($line)
	{
		$sql = sprintf('select 
                                spcdc.*,
                                #ifnull(spcdc.Ref_CauThanhSanPham,0) as Ref_CauThanhSanPham, 
                                ifnull(spcdc.SoLuongTrenGio,0) as SoLuongTrenGio, 
                                dc.Ref_LichLamViec from ODayChuyen as dc
                                inner join OSanPhamCuaDayChuyen as spcdc
                                on spcdc.IFID_M702 = dc.IFID_M702
                                where dc.IOID = %1$d', $line);
		return $this->_o_DB->fetchAll($sql);

	}

	/*
	 * OEE Report 
	 */

	/*
	 * 
	 * Lấy thời gian kế hoạch và sản lượng kế hoạch
	 */

	/**
	 * 
	 * @param type $line
	 * @param type $item
	 * @param type $start
	 * @param type $end
	 * @param type $fetchType
	 * @return type
	 */
	public function getProductionPlan($line, $item, $start, $end)
	{
		$line = $line ? sprintf('and Ref_DayChuyen = %1$d', $line) : '';
		$item = $item ? sprintf('and Ref_MaSP = %1$d', $item) : '';
		
		$sql = sprintf('
						select 
						sum(ThoiGian) as ThoiGianKeHoach
						, SUM(SoLuong) as SanLuongKeHoach
						, Ref_MaSP as Ref_MaSanPham
						, MaSP
						, TenSP
						, DayChuyen
						, Ref_DayChuyen
						from OSanXuat
						where 
						(
							(TuNgay between %3$s and %4$s)
							or
							(DenNgay between %3$s and %4$s)
						)
						%1$s 
						%2$s 
						group by Ref_DayChuyen,Ref_MaSP
						order by Ref_DayChuyen,Ref_MaSP
						', $line, $item, $this->_o_DB->quote($start), $this->_o_DB->quote($end));
		return $this->_o_DB->fetchOne($sql);
	}
	
	public function getProductionPlans($line, $start, $end)
	{
		$line = $line ? sprintf('and Ref_DayChuyen = %1$d', $line) : '';
		
		$sql = sprintf('
						select 
						sum(ThoiGian) as ThoiGianKeHoach
						, SUM(SoLuong) as SanLuongKeHoach
						, Ref_MaSP as Ref_MaSanPham
						, MaSP
						, TenSP
						, DayChuyen
						, Ref_DayChuyen
						from OSanXuat
						where 
						(
							(TuNgay between %2$s and %3$s)
							or
							(DenNgay between %2$s and %3$s)
						)
						%1$s 
						group by Ref_DayChuyen,Ref_MaSP
						order by Ref_DayChuyen,Ref_MaSP
						', $line, $this->_o_DB->quote($start), $this->_o_DB->quote($end));
		return $this->_o_DB->fetchAll($sql);
	}	

	//public function getProductionDetail($line, $item, $BOM, $start, $end)
	/*
	 * @Huy: Changed 24/12
	 */
	public function getProductionDetail($line, $item, $start, $end)
	{
		$line = $line ? sprintf('and sx.Ref_DayChuyen = %1$d', $line) : '';
		$item = $item ? sprintf('and ctsp.Ref_MaSanPham = %1$d', $item) : '';

		$sql = sprintf(' 
						select SUM(sl.SoLuong) as SanLuongThucTe, t.*
						from 
							(select sx.MaLenhSX, sx.Ref_DayChuyen
						 	,ctsp.Ref_MaSanPham,ctsx.MaSP,ctsx.TenSP, sx.DayChuyen
							from OSanXuat as sx
							inner join OChiTietSanXuat as ctsx on ctsx.IFID_M710 = sx.IFID_M710
							inner join OCauThanhSanPham as ctsp
							on ctsp.Ref_MaSanPham = ctsx.Ref_MaSP
							where 
								(
									(sx.TuNgay between %3$s and %4$s)
									or
									(sx.DenNgay between %3$s and %4$s)
								) 
								%1$s %2$s
							) as t
							inner join OThongKeSanLuong as tksl on tksl.MaLenhSX = t.MaLenhSX
							inner join OSanLuong as sl on sl.IFID_M717 = tksl.IFID_M717
							group by t.Ref_DayChuyen, sl.MaSP 
							order by t.Ref_DayChuyen, sl.MaSP
						 ', $line, $item, $this->_o_DB->quote($start), $this->_o_DB->quote($end));
		return $this->_o_DB->fetchOne($sql);

	}
	
	
	public function getProductionDetails($line, $start, $end)
	{
		$line = $line ? sprintf('and sx.Ref_DayChuyen = %1$d', $line) : '';

		$sql = sprintf(' 
						select SUM(sl.SoLuong) as SanLuongThucTe, t.*
						from 
							(select sx.MaLenhSX, sx.Ref_DayChuyen
						 	,ctsp.Ref_MaSanPham,ctsx.MaSP,ctsx.TenSP, sx.DayChuyen
							from OSanXuat as sx
							inner join OChiTietSanXuat as ctsx on ctsx.IFID_M710 = sx.IFID_M710
							inner join OCauThanhSanPham as ctsp
							on ctsp.Ref_MaSanPham = ctsx.Ref_MaSP
							where 
								(
									(sx.TuNgay between %2$s and %3$s)
									or
									(sx.DenNgay between %2$s and %3$s)
								) 
								%1$s
							) as t
							inner join OThongKeSanLuong as tksl on tksl.MaLenhSX = t.MaLenhSX
							inner join OSanLuong as sl on sl.IFID_M717 = tksl.IFID_M717
							group by t.Ref_DayChuyen, sl.MaSP 
							order by t.Ref_DayChuyen, sl.MaSP
						 ', $line, $this->_o_DB->quote($start), $this->_o_DB->quote($end));
		return $this->_o_DB->fetchAll($sql);

	}	

	// Thời gian dừng máy #group by dstb.IOID, dstb.Ref_MaKhuVuc
	// @todo: Không cho chọn khác dây chuyền cùng khu vực
	public function getProdcutionDownTime($line, $start, $end, $fetchType = 1)
	{
		$line = $line ? sprintf('and dc.IOID = %1$d', $line) : '';
		$sql = sprintf(' 
						select 
						ifnull(sum(case when pbt.ThoiGianDungMay is not null then pbt.ThoiGianDungMay else 0 end),0) as ThoiGianDungMay,
						ifnull(sum(case when pbt.ThoiGianDungMay is not null then 1 else 0 end),0) as SoLanDungMay,
						dc.MaDayChuyen,dc.TenDayChuyen,dc.IOID as DCIOID
						from OPhieuBaoTri as pbt
						inner join ODanhSachThietBi as dstb on pbt.Ref_MaThietBi = dstb.IOID
						inner join ODayChuyen as dc on dc.Ref_MaKhuVuc = dstb.Ref_MaKhuVuc
						where  (pbt.Ngay between %2$s and %3$s) %1$s
						group by dstb.Ref_MaKhuVuc 
						order by dc.IOID, dstb.Ref_MaKhuVuc
						 ', $line, $this->_o_DB->quote($start), $this->_o_DB->quote($end));
		//echo $sql; die;
		return ($fetchType === 1) ? $this->_o_DB->fetchOne($sql) : $this->_o_DB->fetchAll($sql);

	}


	public function getProductionDefect($line, $item, $start, $end)
	{
		$line = $line ? sprintf('and sx.Ref_DayChuyen = %1$d', $line) : '';
		$item = $item ? sprintf('and ctsp.Ref_MaSanPham = %1$d', $item) : '';

		$sql = sprintf('select 
						ifnull(sum(case when spl.SoLuong is not null then spl.SoLuong else 0 end),0) as SoLuongBiLoi,
						t.*
						from 
						(select sx.MaLenhSX, sx.Ref_DayChuyen,ctsp.Ref_MaSanPham,ctsx.MaSP,ctsx.TenSP, sx.DayChuyen
						 from OSanXuat as sx
						 inner join OChiTietSanXuat as ctsx on ctsx.IFID_M710 = sx.IFID_M710
						 inner join OCauThanhSanPham as ctsp
						 on ctsp.Ref_MaSanPham = ctsx.Ref_MaSP
						 where 	(
									(sx.TuNgay between %3$s and %4$s)
									or
									(sx.DenNgay between %3$s and %4$s)
								) 
						 
						 %1$s %2$s) as t
						 inner join OThongKeSanLuong as tksl
						 on tksl.MaLenhSX = t.MaLenhSX	 
						 left join OSanPhamLoi as spl
						 on spl.IFID_M717 = tksl.IFID_M717
						 group by t.Ref_DayChuyen, spl.MaSP
						 order by t.Ref_DayChuyen, spl.MaSP
						 ', $line, $item, $this->_o_DB->quote($start), $this->_o_DB->quote($end));
		return $this->_o_DB->fetchOne($sql);

	}
	
	public function getProductionDefects($line, $start, $end)
	{
		$line = $line ? sprintf('and sx.Ref_DayChuyen = %1$d', $line) : '';

		$sql = sprintf('select 
						ifnull(sum(case when spl.SoLuong is not null then spl.SoLuong else 0 end),0) as SoLuongBiLoi,
						t.*
						from 
						(select sx.MaLenhSX, sx.Ref_DayChuyen,ctsp.Ref_MaSanPham,ctsx.MaSP,ctsx.TenSP, sx.DayChuyen
						 from OSanXuat as sx
						 inner join OChiTietSanXuat as ctsx on ctsx.IFID_M710 = sx.IFID_M710
						 inner join OCauThanhSanPham as ctsp
						 on ctsp.Ref_MaSanPham = ctsx.Ref_MaSP
						 where 	(
									(sx.TuNgay between %2$s and %3$s)
									or
									(sx.DenNgay between %2$s and %3$s)
								) 
						 
						 %1$s) as t
						 inner join OThongKeSanLuong as tksl
						 on tksl.MaLenhSX = t.MaLenhSX	 
						 left join OSanPhamLoi as spl
						 on spl.IFID_M717 = tksl.IFID_M717
						 group by t.Ref_DayChuyen, spl.MaSP
						 order by t.Ref_DayChuyen, spl.MaSP
						 ', $line, $this->_o_DB->quote($start), $this->_o_DB->quote($end));
		return $this->_o_DB->fetchAll($sql);

	}	

	/*
	 * OEE analysis figures
	 */

	// @todo: hàm này có hàm getManufacturingLine nhưng ko dùng lại, có thể merge hai hàm lại
	public function getProductionLine($line = 0)
	{
		$line = $line ? sprintf(' where dc.IOID = %1$d ', $line) : '';
		$sql = sprintf('select *, dc.IOID as DCIOID, spcdc.Ref_MaSanPham as RefSanPham from ODayChuyen as dc
						left join OSanPhamCuaDayChuyen as spcdc
						on spcdc.IFID_M702 = dc.IFID_M702
						%1$s
						order by dc.IOID
						', $line);
		return $this->_o_DB->fetchAll($sql);

	}

	public function getEquipmentByLine($line, $search)
	{
		$where = '';
		if ($line)
		{
			$where .= sprintf(' and dc.IOID = %1$d', $line);
		}
		if ($search)
		{
			$where .= sprintf(' and (tb.Ma like %1$s or tb.Ten like %1$s)', $this->_o_DB->quote('%' . $search . '%'));
		}
		$sql = sprintf('select distinct tb.*
                                from OThietBi as tb 
                                inner join ODonViSanXuat as dvsx on dvsx.IFID_M125  = tb.IFID_M125
                                inner join OCongDoanDayChuyen as cddc on cddc.Ref_DonViThucHien = dvsx.IOID 
                                inner join ODayChuyen as dc on dc.IFID_M702 = cddc.IFID_M702
                                %1$s
                                order by tb.Ma
                                limit 50
						', $where);
		return $this->_o_DB->fetchAll($sql);

	}
	


    /**
     * Lay vat tu thay the tu ke hoach bao tri
     * @param int $locIOID Location IOID
     * @param int $eqGroupIOID Equip Group IOID
     * @param int $eqTypeIOID Equip Type IOID
     */
    public function getMaterialPlans($mSDate, $mEDate, $locIOID = 0, $eqGroupIOID = 0, $eqTypeIOID = 0)
    {
        // Init data
        $common   = new Qss_Model_Extra_Extra();
        $loc      = $locIOID?$common->getTableFetchOne('OKhuVuc', array('IOID' => $locIOID)):FALSE;
        
        // Filter
        $where    = 'WHERE 1 = 1 ';
        $where   .= ($eqGroupIOID)?sprintf(' AND thietbi.Ref_NhomThietBi = %1$d', $eqGroupIOID):'';
        $where   .= ($eqTypeIOID)?sprintf(' AND thietbi.Ref_LoaiThietBi = %1$d', $eqTypeIOID):'';
        $where   .= ($loc)?sprintf(' AND thietbi.Ref_MaKhuVuc IN (SELECT IOID FROM OKhuVuc WHERE lft>= %1$d AND  rgt <= %2$d)', $loc->lft, $loc->rgt):'';

        $sql = sprintf('
            SELECT 
                thietbi.IOID AS EQIOID,
                ifnull(cautrucmay.IOID, 0) AS CIOID,
                /*ifnull(baotringay.IOID, 0)*/ 0 AS SDIOID,
                sanpham.IOID AS IIOID,
                thietbi.MaThietBi,
                thietbi.TenThietBi,
                cautrucmay.ViTri,
                cautrucmay.BoPhan,
                sanpham.MaSanPham,
                sanpham.TenSanPham,
                sanpham.DonViTinh AS DonViTinh, 
                (ifnull(vattu.SoLuong, 0) * ifnull(donvitinh.HeSoQuyDoi, 0))AS SoLuong,
                ifnull(kho.TonKho, 0) AS TonKho,
                /*baotringay.Ngay*/ NULL AS NgayDacBiet,
                ChuKyBaoTri.IOID AS ChuKyIOID,
                ChuKyBaoTri.KyBaoDuong as MaKy,
                ChuKyBaoTri.Ngay,
                ChuKyBaoTri.Thang,
                ChuKyBaoTri.Thu AS NgayThu,    
                ChuKyBaoTri.LapLai,
                kehoach.NgayBatDau,
                kehoach.NgayKetThuc
            FROM OBaoTriDinhKy AS kehoach            
            INNER JOIN OChuKyBaoTri AS ChuKyBaoTri On kehoach.IFID_M724 = ChuKyBaoTri.IFID_M724
            INNER JOIN OVatTu AS vattu ON kehoach.IFID_M724 = vattu.IFID_M724
            INNER JOIN OSanPham AS sanpham ON vattu.Ref_MaVatTu = sanpham.IOID
            INNER JOIN ODonViTinhSP AS donvitinh ON sanpham.IFID_M113 = donvitinh.IFID_M113 
                AND vattu.Ref_DonViTinh = donvitinh.IOID
            LEFT JOIN ODanhSachThietBi AS thietbi ON kehoach.Ref_MaThietBi = thietbi.IOID
            LEFT JOIN OCauTrucThietBi AS cautrucmay ON thietbi.IFID_M705 = cautrucmay.IFID_M705 
                AND IFNULL(vattu.Ref_ViTri, 0) = cautrucmay.IOID
            LEFT JOIN 
            (
                SELECT 
                    sum((ifnull(kho.SoLuongHC, 0) * ifnull(dvtinhsp.HeSoQuyDoi, 0))) AS TonKho
                    , kho.Ref_MaSP
                FROM OKho AS kho 
                LEFT JOIN OSanPham AS sanpham ON kho.Ref_MaSP = sanpham.IOID 
                LEFT JOIN ODonViTinhSP AS dvtinhsp ON sanpham.IFID_M113 = dvtinhsp.IFID_M113 AND kho.Ref_DonViTinh = dvtinhsp.IOID
                GROUP BY kho.Ref_MaSP
            ) AS kho ON vattu.Ref_MaVatTu = kho.Ref_MaSP
            /*LEFT JOIN OBaoTriTheoNgay AS baotringay ON kehoach.IFID_M724 = baotringay.IFID_M724
                AND baotringay.Ngay between %1$s and %2$s*/
            %3$s /*WHERE*/ -- AND TRIM(vattu.MaVatTu) IN("DAU-O05") 
            ORDER BY kehoach.IOID, ChuKyBaoTri.IOID, vattu.IOID/*, baotringay.IOID*/
        ', $this->_o_DB->quote($mSDate), $this->_o_DB->quote($mEDate), $where);

        // echo '<pre>'; print_r($sql); die;

        return $this->_o_DB->fetchAll($sql);
    }

	// Su dung de lay lft va rgt tu bang khu vuc tu mot mang id bao gom ca thiet bi va khu vuc
	public function getLocConfigs($filterID)
	{
		if (!is_array($filterID) && !$filterID)
		{
			return;
		}

		$filterIDArr = Qss_Lib_Extra::changeToArray($filterID);
		$filter = count($filterIDArr) ? sprintf(' where IOID in (%1$s) ', implode(', ', $filterIDArr)) : ' where 1 = 0 ';

		$sql = sprintf('select *
                            from OKhuVuc
                            %1$s
                    ', $filter);
		return $this->_o_DB->fetchAll($sql);

	}
	/**
	 * @remove
	 * @param type $date
	 * @param type $location
	 * @param type $maintype
	 * @param type $ifidMO
	 * @return type
	 */
	public function getMaintainRequirements($date, $location = 0, $maintype  = 0, $ifidMO = 0)
	{
		$where = '';
		$common = new Qss_Model_Extra_Extra();
		if ($location)
		{
			$locName = $this->_common->getTable(array('*')
				, 'OKhuVuc'
				, array('IOID' => $location)
				, array(), 'NO_LIMIT',  1);
			$where .= sprintf(' and pbt.Ref_MaThietBi in (select IOID from ODanhSachThietBi where Ref_MaKhuVuc in (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))', $locName->lft, $locName->rgt);
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
		
		
		$this->_o_DB->execute('SET SESSION group_concat_max_len = 10000;');
		$sql = sprintf('
                            select pbt.*
							, group_concat(DISTINCT cv.MoTa SEPARATOR \'<br>\') as congviec
                            , pbt.XuLy as ThucHienCongViec
                            , group_concat(DISTINCT concat(vt.MaVatTu,\': \', vt.SoLuong, \' \', vt.DonViTinh) SEPARATOR \'<br>\') as VatTu
                            , group_concat(DISTINCT concat(uc.UserName,\' (\', DATE_FORMAT(fc.Date,\'%%d-%%m-%%Y %%H:%%i:%%s\'), \') \',\': \', fc.Comment) SEPARATOR \'<br>\') as Comment
                            from OPhieuBaoTri as pbt
                            left join OCongViecBTPBT as cv on cv.IFID_M759 = pbt.IFID_M759
                            left join OVatTuPBT as vt on vt.IFID_M759 = pbt.IFID_M759
                            left join qsfcomment as fc on pbt.IFID_M759 = fc.IFID
                            left join qsusers as uc on uc.UID = fc.UID
                            where NgayYeuCau = %1$s %2$s
                            /*group by pbt.IOID*/
                            order by pbt.IOID, pbt.Ref_MaThietBi
                            limit 2000;
            			', $this->_o_DB->quote($date), $where);
		//echo $sql;
		return $this->_o_DB->fetchAll($sql);

	}

	public function getMaterialsOfMaintainOrder($date
		, $location = 0
		, $maintype  = 0
		, $ifidMO = 0
		, $lang = 'vn')
	{
		$stepNameField = ($lang == 'vn' || !$lang)?'Name':'Name_'.$lang;
		
		$where = '';
		$common = new Qss_Model_Extra_Extra();
		if ($location)
		{
			$locName = $this->_common->getTable(array('*')
				, 'OKhuVuc'
				, array('IOID' => $location)
				, array(), 'NO_LIMIT',  1);
			$where .= sprintf(' and pbt.Ref_MaThietBi in (select IOID from ODanhSachThietBi where Ref_MaKhuVuc in (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))', $locName->lft, $locName->rgt);
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
		
		$this->_o_DB->execute('SET SESSION group_concat_max_len = 10000;');
		$sql = sprintf('
						select 
						pbt.IFID_M759
						, pbt.SoPhieu
						, pbt.MaThietBi
						, pbt.TenThietBi
						, pbt.LoaiBaoTri
						, pbt.Ca
						, pbt.TenDVBT
						, pbt.NguoiThucHien
						, vt.BoPhan
						, vt.ViTri
						, vt.Ref_ViTri
						, group_concat(DISTINCT concat(vt.MaVatTu,\': \', vt.SoLuong, \' \', vt.DonViTinh) SEPARATOR \'<br>\') as VatTu						
						, wfls.%3$s AS Status
						from OPhieuBaoTri as pbt
						left join qsiforms as qsi on qsi.IFID = pbt.IFID_M759
						left join qsworkflows as wfl on qsi.FormCode = wfl.FormCode
						left join qsworkflowsteps as wfls on wfl.WFID = wfls.WFID
						left join OVatTuPBT as vt on vt.IFID_M759 = pbt.IFID_M759
						where NgayYeuCau = %1$s %2$s
						GROUP BY pbt.IOID, pbt.Ref_MaThietBi, vt.Ref_ViTri
						order by pbt.IOID, pbt.Ref_MaThietBi, vt.Ref_ViTri
						limit 8000;
            			', $this->_o_DB->quote($date), $where, $stepNameField);
		return $this->_o_DB->fetchAll($sql);

	}	
/*	
//	public function getCommentsOfMaintainOrder($date, $location = 0, $maintype  = 0, $ifidMO = 0)
//	{
//		$where = '';
//		$common = new Qss_Model_Extra_Extra();
//		if ($location)
//		{
//			$locName = $this->_common->getTable(array('*')
//				, 'OKhuVuc'
//				, array('IOID' => $location)
//				, array(), 'NO_LIMIT', '', 1);
//			$where .= sprintf(' and pbt.Ref_MaThietBi in (select IOID from ODanhSachThietBi where Ref_MaKhuVuc in (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))', $locName->lft, $locName->rgt);
//		}
//		
//		if($maintype)
//		{
//			$maintypeTemp = Qss_Lib_Extra::changeToArray($maintype);
//			
//			$where .= sprintf('
//				AND pbt.Ref_LoaiBaoTri in (%1$s)
//			', implode(',', $maintypeTemp));
//		}		
//		
//		if($ifidMO)
//		{
//			$where .= sprintf('
//				AND pbt.IFID_M759 = %1$d
//			', $ifidMO);			
//		}
//		
//		
//		$this->_o_DB->execute('SET SESSION group_concat_max_len = 10000;');
//		$sql = sprintf('
//                            select pbt.*
//                            , pbt.XuLy as ThucHienCongViec
//                            , group_concat(DISTINCT concat(uc.UserName,\' (\', DATE_FORMAT(fc.Date,\'%%d-%%m-%%Y %%H:%%i:%%s\'), \') \',\': \', fc.Comment) SEPARATOR \'<br>\') as Comment
//                            from OPhieuBaoTri as pbt
//                            left join qsfcomment as fc on pbt.IFID_M759 = fc.IFID
//                            left join qsusers as uc on uc.UID = fc.UID
//                            where NgayYeuCau = %1$s %2$s
//                            group by pbt.IOID
//                            order by pbt.IOID, pbt.Ref_MaThietBi
//                            limit 2000;
//            			', $this->_o_DB->quote($date), $where);
//		//echo $sql;
//		return $this->_o_DB->fetchAll($sql);
//
//	}	
* 
*/	
	public function countMaintainRequirementsComment($date, $location = 0, $maintype = 0, $ifidMO = 0)
	{
		$where = '';
		$common = new Qss_Model_Extra_Extra();
		if ($location)
		{
			$locName = $this->_common->getTable(array('*')
				, 'OKhuVuc'
				, array('IOID' => $location)
				, array(), 'NO_LIMIT',  1);
			$where = sprintf(' and Ref_MaThietBi in (select IOID from ODanhSachThietBi where Ref_MaKhuVuc in (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))', $locName->lft, $locName->rgt);
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


		$sql = sprintf('
                            select count(fc.IFID) as CommentAmount, IFID_M759 as IFID
                            from OPhieuBaoTri as pbt
                            left join qsfcomment as fc on pbt.IFID_M759 = fc.IFID
                            where NgayYeuCau = %1$s %2$s
                            group by pbt.IFID_M759
                            ;
            			', $this->_o_DB->quote($date), $where);
		//echo $sql;
		return $this->_o_DB->fetchAll($sql);

	}

	public function getWorkOrderHistoryOfEquipment($refEq)
	{
		$profile = '';
		if (Qss_Lib_System::fieldActive('OPhieuBaoTri', 'GhiVaoLyLich'))
		{
			$profile = ' and ifnull(pbt.GhiVaoLyLich,0) = 1 '; // ghi vao ly lich = 1
		}

		$this->_o_DB->execute('SET SESSION group_concat_max_len = 10000;');
		$sql = sprintf('SELECT pbt.*, DATE_FORMAT(pbt.Ngay, \'%%d-%%m-%%Y\') AS Ngay
						, group_concat(DISTINCT concat(vt.MaVatTu,\': \', vt.SoLuong, \' \', vt.DonViTinh) SEPARATOR \'<br>\') as VatTu
						, pbt.XuLy as ThucHienCongViec
						, group_concat(DISTINCT cv.MoTa SEPARATOR \'<br>\') as CongViecBaoTri
						FROM OPhieuBaoTri AS pbt
						INNER JOIN qsiforms AS ifo ON ifo.IFID = pbt.IFID_M759
						left join OVatTuPBT as vt on pbt.IFID_M759 = vt.IFID_M759
						left join OCongViecBTPBT as cv on  pbt.IFID_M759 = cv.IFID_M759
						WHERE pbt.Ref_MaThietBi = %1$d AND ifnull(ifo.Status, 0) >= 3
						%2$s
						group by pbt.IOID
						ORDER BY pbt.Ngay DESC
                            ', $refEq, $profile);
		return $this->_o_DB->fetchAll($sql);

	}

	public function getThongKeSanXuatPM($line, $start, $end)
	{
		$where = '';
		$whereArr = array();
		$whereArr[] = sprintf('( tksl.Ngay between %1$s and %2$s )'
			, $this->_o_DB->quote($start)
			, $this->_o_DB->quote($end));
		if ($line)
		{
			$whereArr[] = sprintf('  tksl.Ref_DayChuyen = %1$d', $line);
		}

		if (count($whereArr))
		{
			$where = sprintf(' where %1$s ', implode(' and ', $whereArr));
		}



		$sql = sprintf('select 
                            tksl.Ref_DayChuyen, tksl.Ref_MaSP, 
                            tksl.SanLuong, tksl.SoLuongLoi,
                            c.GioBatDau, c.GioKetThuc
                            from OThongKeSanLuong as tksl
                            inner join OCa as c on tksl.Ref_Ca = c.IOID
                            %1$s
                           
                    ', $where);
		//echo $sql; die;
		return $this->_o_DB->fetchAll($sql);

	}

	public function getDowntimePM($line, $start, $end)
	{
		$line = $line ? sprintf('  and ddc.Ref_DayChuyen = %1$d', $line) : '';
		$sql = sprintf('select sum(ThoiGian) as ThoiGianDungMay
                            , count(ddc.IOID) as SoLanDungMay
                            , ddc.Ref_DayChuyen as DCIOID
                            from OPhieuBaoTri as pbt
                            inner join ODungMayTheoDC as ddc on pbt.IFID_M759 = ddc.IFID_M759 %3$s
                            where (pbt.Ngay between %1$s and %2$s) 
                            group by ddc.Ref_DayChuyen
                    ', $this->_o_DB->quote($start)
			, $this->_o_DB->quote($end)
			, $line);
		//echo $sql; die;
		return $this->_o_DB->fetchAll($sql);

	}

	/**
	 * @path module->report->maintenanceController.php->getResouceTimeChartDataAboutTime
	 * @return working employee
	 * @ move to models\Maintenance\Employee.php 
	 * @ rename to getTasksByEmployee
	 */
	public function getWorkingEmpl()
	{
		$sql = sprintf('SELECT
                            nv.IFID_M316 as IFID
                            , nv.Ref_LichLamViec
                            , kn.Ref_KyNang
                            , kn.CongViecChinh
						FROM
                            ODanhSachNhanVien as nv
						LEFT JOIN OKyNang as kn on nv.IFID_M316 = kn.IFID_M316
						LEFT JOIN OCongViecBaoTri as cvbt on kn.Ref_KyNang = cvbt.IOID
						WHERE
								ifnull(ThoiViec, 0) = 0
								and ifnull(cvbt.HoatDong, 0) = 1
						ORDER BY nv.IFID_M316');
		return $this->_o_DB->fetchAll($sql);

	}
	
	
	public function getWorkingEmplFromWorkCenter($start, $end, $workcenter = 0)
	{
		if(is_array($workcenter))
		{
			$workcenter = count($workcenter)?
				sprintf(' and dvth.`IOID` in (%1$s)'
					,implode(',',$workcenter)):'';
		}
		else
		{
			$workcenter = ($workcenter)?sprintf(' and dvth.`IOID` = %1$d'
				,$workcenter):'';
		}
		
		$sql = sprintf('
						SELECT 
						dsnv.`Ref_LichLamViec` AS RefWCal
						, dsnv.`IOID` AS RefEmp
						, dvth.`IOID` AS RefWC
						FROM ONhanVien AS nv
						INNER JOIN ODonViSanXuat AS dvth 
							ON nv.`IFID_M125` = dvth.`IFID_M125`
						INNER JOIN ODanhSachNhanVien AS dsnv 
							ON nv.`Ref_MaNV` = dsnv.`IOID`
						WHERE 
						ifnull(dsnv.`ThoiViec`, 0) = 0 
						AND ((NOT ((nv.`NgayKetThuc` < %1$s and nv.`NgayKetThuc` is not null) 
							OR nv.`NgayBatDau` > %2$s))
							OR
							(
							ifnull(nv.`NgayKetThuc`,\'0000-00-00\') = \'0000-00-00\'
							AND nv.`NgayBatDau` >= %1$s 
							)
						)
						%3$s'
				, $this->_o_DB->quote($start)
				, $this->_o_DB->quote($end)
				, $workcenter);
		return $this->_o_DB->fetchAll($sql);

	}

	// Lay tong thoi gian lam viec cua tung cong viec bao tri theo ngay
	public function getMaintPlanWorkTime($date, $filter = array())
	{
		$where = '';
		$group = '';
		$idField = 'cvbt.Ref_Ten';
		
		$solar = new Qss_Model_Calendar_Solar();
		$date  = date_create($date);
		
		$day   = $date->format('d');
		$month = $date->format('m');
		$wday  = $date->format('w');
		
		$quarter = $solar->getQuarter((int) $month);
		$monthNo = $solar->getMonthNo((int) $month);
		
		// Loc theo workcenter
		if(isset($filter['workcenter']))
		{
			if(is_array($filter['workcenter']))
			{
				$where .= count($filter['workcenter'])?
					sprintf(' and cm.Ref_DVBT in (%1$s)',implode(',', $filter['workcenter'])):'';
			}
			else
			{
				$where .= ($filter['workcenter'])?sprintf(' and cm.Ref_DVBT = %1$d',$filter['workcenter']):'';
			}
		}
		
		// Group: Mac dinh theo cong viec
		if(isset($filter['group']))
		{
			switch ($filter['group'])
			{
				case 'wc':
					$group = ' GROUP BY cm.Ref_DVBT ';
					$idField = 'ifnull(cm.Ref_DVBT,0)';
				break;
				default:
					$group = ' GROUP BY cvbt.Ref_Ten ';
				break;
			}
			$group = ' GROUP BY cvbt.Ref_Ten ';
		}
		else
		{
			$group = ' GROUP BY cvbt.Ref_Ten ';
		}

		$sql = sprintf('
			SELECT
				%8$s AS Work
				, sum(ifnull(cvbt.ThoiGian,0)) as ThoiGian
			FROM OBaoTriDinhKy AS cm
			LEFT JOIN OChuKyBaoTri AS ChuKy ON cm.IFID_M724 = ChuKy.IFID_M724
			LEFT JOIN OCongViecBT AS cvbt ON cm.IFID_M724 = cvbt.IFID_M724
			WHERE
				(
				(ChuKy.KyBaoDuong = \'D\' and IFNULL(TIMESTAMPDIFF(DAY,cm.NgayBatDau,%4$s)%%ChuKy.`LapLai`,0) = 0)
				or
				(ChuKy.KyBaoDuong = \'W\' and ChuKy.Thu=%3$d and IFNULL(TIMESTAMPDIFF(WEEK,cm.NgayBatDau,%4$s)%%ChuKy.`LapLai`,0) = 0)
				or
				(ChuKy.KyBaoDuong = \'M\' and ChuKy.Ngay=%1$d and IFNULL(TIMESTAMPDIFF(MONTH,cm.NgayBatDau,%4$s)%%ChuKy.`LapLai`,0) = 0)
				or
				(ChuKy.KyBaoDuong = \'Y\' and ChuKy.Ngay=%1$d and Thang=%2$d and IFNULL(TIMESTAMPDIFF(YEAR,cm.NgayBatDau,%4$s)%%ChuKy.`LapLai`,0) = 0)
				/*or cm.IFID_M724 in (select IFID_M724 from OBaoTriTheoNgay where Ngay = %4$s)*/
				)
				and (%4$s >= cm.NgayBatDau or ifnull(cm.NgayBatDau, 0) = \'\')
				and (%4$s <= cm.NgayKetThuc or ifnull(cm.NgayKetThuc, 0) = \'\')
				%6$s
				%7$s
			LIMIT 8000'
		, $day, $month, $wday
		, $this->_o_DB->quote($date->format('Y-m-d'))
		, $monthNo, $where, $group, $idField);
		// echo '<pre>'; echo $sql;die;
		return $this->_o_DB->fetchAll($sql);

	}

	// Lay cong viec bao tri thuc the theo 
	public function getWorkingHourByEmployee($start, $end, $refworkcenter)
	{
		$refworkcenter = $refworkcenter ? sprintf(' AND pbt.Ref_MaDVBT = %1$d ', $refworkcenter) : '';
		$sql = sprintf('
			SELECT
				ifnull(cv.Ref_NguoiThucHien,pbt.Ref_NguoiThucHien) AS Ref_Worker
				, sum(ifnull(cv.ThoiGian, 0)) AS Time
				, cv.Ref_Ten AS Ref_Work
				, group_concat(concat(\'<b>\', pbt.SoPhieu , \'</b>\', \'<br>\' , cv.MoTa) SEPARATOR \'<br>\' ) AS CongViec
			FROM OPhieuBaoTri AS pbt
			LEFT JOIN OCongViecBTPBT AS cv ON pbt.IFID_M759 = cv.IFID_M759
			WHERE (pbt.NgayYeuCau BETWEEN %1$s AND %2$s) %3$s
			GROUP BY Ref_Worker,Ref_Work
		', $this->_o_DB->quote($start), $this->_o_DB->quote($end), $refworkcenter);
		//echo '<pre>'; print_r($sql); die;
		return $this->_o_DB->fetchAll($sql);

	}

	public function resourceEmpReportGetEmployee($workCenterIOID = 0)
	{
		$filter['select'] = 'cm.*, dvsx.IOID as DVIOID, dvsx.Ma, dvsx.Ten';
		$filter['module'] = 'ODanhSachNhanVien';
		$filter['join'][0]['joinType'] = 1;
		$filter['join'][0]['joinTable'] = 'ONhanVien';
		$filter['join'][0]['joinAlias'] = 'nv';
		$filter['join'][0]['joinCondition'][0]['joinAlias1'] = 'cm';
		$filter['join'][0]['joinCondition'][0]['joinCol1'] = 'IOID';
		$filter['join'][0]['joinCondition'][0]['joinAlias2'] = 'nv';
		$filter['join'][0]['joinCondition'][0]['joinCol2'] = 'Ref_MaNV';
		$filter['join'][1]['joinType'] = 1;
		$filter['join'][1]['joinTable'] = 'ODonViSanXuat';
		$filter['join'][1]['joinAlias'] = 'dvsx';
		$filter['join'][1]['joinCondition'][0]['joinAlias1'] = 'nv';
		$filter['join'][1]['joinCondition'][0]['joinCol1'] = 'IFID_M125';
		$filter['join'][1]['joinCondition'][0]['joinAlias2'] = 'dvsx';
		$filter['join'][1]['joinCondition'][0]['joinCol2'] = 'IFID_M125';
		$filter['where'] = 'ifnull(ThoiViec, 0) = 0';
        $filter['where'] .= $workCenterIOID?sprintf(' and dvsx.IOID = %1$d', $workCenterIOID):'';
		$filter['order'] = 'dvsx.IOID';
		return $this->_common->getDataset($filter);

	}
	
	
	/**
	 * Lay ngay gan day nhat xuat kho mot phu tung cho mot thiet bi nhat dinh
	 * @param type $refItem 
	 * @param type $refEq
	 */
	public function getLastestOutputDateOfItemForEquip($refItem, $refEq, $excludeIfid = 0)
	{
		$where = ($excludeIfid)?' AND ds.IFID_M506 != '.$excludeIfid:'';
		$sql = sprintf('
			SELECT MAX(xk.NgayChungTu) AS `Last`,
			ds.IFID_M506 AS IFID,
			xk.SoChungTu AS DocNo
			FROM ODanhSachXuatKho AS ds
			INNER JOIN OXuatKho AS xk ON ds.IFID_M506 = xk.IFID_M506
			WHERE ds.Ref_MaSP = %1$d
			AND ds.Ref_MaThietBi = %2$d
			%3$s
		', $refItem, $refEq, $where);
		return $this->_o_DB->fetchOne($sql);	
		
	}
	
	/**
	 * 
	 * @param type $filter[start]
	 * @param type $filter[end]
	 * @param type $filter[equipTypeIFID]
	 * @param type $filter[paramIOID] Chi So Thiet bi
	 * @param type $filter[get] = 'TOTAL_TIME'/ 'TOTAL_QTY'
	 */
	public function getTotalQtyDailyRecordData($start , $end, $equipTypeIFID = 0)
	{
		$whereArr = array();
		$where    = '';
		$dateFilter = '';
		
		if($start && $end)
		{
			$dateFilter = sprintf(' AND (nt.`Ngay` between %1$s and %2$s)
				'
				, $this->_o_DB->quote($start)
				, $this->_o_DB->quote($end));
		}
		
		if($equipTypeIFID)
		{
			$whereArr[] = sprintf(' ltb.IOID != %1$d
				', $equipTypeIFID);
		}
		
		$where  = count($whereArr)?sprintf(' where %1$s ', implode(' and ', $whereArr)):'';
		
		$sql = sprintf('
			SELECT dstb.IOID AS EQIOID, dstb.`MaThietBi`, dstb.`LoaiThietBi`
			,dstb.TenThietBi
			,dstb.NhomThietBi
			,cs.`ChiSo`, cs.IOID AS RefParam
			,sum(ifnull(nt.SoHoatDong,0)) AS TotalQty 
			,sum(DATEDIFF(if(%4$s!=\'\',%4$s,now()),if(%3$s!=\'\',%3$s,dstb.NgayDuaVaoSuDung))) * ifnull(diemdo.SoHoatDongNgay,0) as PlanTotalQty
			FROM ODanhSachThietBi AS dstb 
			INNER JOIN OLoaiThietBi as ltb on ltb.IOID = dstb.Ref_LoaiThietBi
			LEFT JOIN ODanhSachDiemDo as diemdo on diemdo.IFID_M705 = dstb.IFID_M705
			LEFT JOIN ONhatTrinhThietBi AS nt ON diemdo.IOID = nt.Ref_DiemDo %2$s
			LEFT JOIN OChiSoMayMoc AS cs ON cs.IOID = diemdo.Ref_ChiSo
			%1$s
			GROUP BY ltb.IOID,dstb.MaThietBi, diemdo.ChiSo'
		, $where
		, $dateFilter
		, $this->_o_DB->quote($start)
		, $this->_o_DB->quote($end));
		//echo '<pre>';
		//echo $sql; die;
		return $this->_o_DB->fetchAll($sql);
	}
	
	public function getTotalTimeDailyRecordData($dailyRecordIFID)
	{
		$dailyRecordIFID[] = 0;		
		$sql = sprintf('SELECT nt.`IFID_M765` AS DRIFID, nt.Ref_MaTB AS EQIOID
			,sum(ifnull(nt.SoHoatDong,0)) AS TotalTime
			FROM ONhatTrinhThietBi AS nt
			INNER JOIN OChiSoMayMoc as cs ON cs.IOID = ct.Ref_ChiSo
			WHERE nt.IFID_M765 in (%1$s) AND nt.Gio = 1
			GROUP BY nt.Ref_MaTB'
		, implode(' , ', $dailyRecordIFID));
		return $this->_o_DB->fetchOne($sql);
	}
	
	public function getDailyRecordDataByIFID($dailyRecordIFID)
	{
		$dailyRecordIFID[] = 0;
		$sql = sprintf('
			SELECT nt.*
			FROM ONhatTrinhThietBi AS nt
			WHERE nt.`IFID_M765` in (%1$s)'
		, implode(' , ', $dailyRecordIFID));
		return $this->_o_DB->fetchOne($sql);
	}	
	
	
	/**
	 * *******************************************************
	 * Refactor: Chinh ly lai cac ham co mat o tre tu phan nay
	 * *******************************************************
	 */
	
	/**
	 * Lay don vi thuc hien theo don vi lam viec
	 * @param string $filterFormCode  Code cua form
	 * @param object $user object user
	 * @return object
	 * @use-in: Xem ke hoach (Extra button - M759)	 
	 */
	public function getWorkCenterByUser($filterFormCode, $user)
	{
		$getJoin  = '';
		$getWhere = '';
		$base     = sprintf('select ODonViSanXuat.* from ODonViSanXuat');

		// Phieu bao tri dinh ky
		$rights = Qss_Lib_System::getFormRights($filterFormCode, $user->user_group_list);
		$getJoin = sprintf('inner join qsrecordrights on qsrecordrights.IFID = ODonViSanXuat.IFID_M125'); 
		$getWhere = sprintf(' and qsrecordrights.FormCode = "M125" and qsrecordrights.UID = %1$d', $user->user_id); //$preventiveOrder->getWhere();
		
		$filterByWCType = Qss_Lib_System::fieldActive('ODonViSanXuat', 'BaoTri') ? sprintf(' and ifnull(ODonViSanXuat.BaoTri,0) = 1 ') : '';

		$sql = sprintf(' %1$s %2$s where  1=1 %4$s %3$s', $base, $getJoin, $getWhere, $filterByWCType);
		return $this->_o_DB->fetchAll($sql);

	}		
	
	/**
	 * Dem so luong ke hoach bao tri duoc lay theo ngay va don vi bao tri (bao gom
	 * ke hoach cua don vi va cac ke hoach khong phan cong don vi thuc hien) 
	 * xac dinh don vi nao
	 * @param date $dateFilter Ngay lay ke hoach bao tri (YYYY-mm-dd)
	 * @param string $workCenterCode Ma don vi bao tri 
	 * @return int so luong ke hoach bao tri
	 * @use-in: Xem ke hoach (Extra button - M759)
	 */
	public function countMaintainPlanByDate($dateFilter, $workCenterCode)
	{
		// Model
		$solar   = new Qss_Model_Calendar_Solar();
		
		// Init
		$date    = date_create($dateFilter);
		$day     = $date->format('d');
		$month   = $date->format('m');
		$monthNo = $solar->montnhNo[(int) $month];
		$wday    = $date->format('w');


		$sql = sprintf(
			'SELECT
				count(1) AS TongSo
			FROM
				OBaoTriDinhKy AS btdk
            left join OPhanLoaiBaoTri AS plbt ON plbt.IOID = btdk.Ref_LoaiBaoTri
			LEFT JOIN OThu AS t ON t.IOID = btdk.Ref_Thu
			LEFT JOIN OKy AS k ON k.IOID = plbt.Ref_KyBaoDuong
			LEFT JOIN ODanhSachThietBi AS dstb ON dstb.`IOID` = btdk.`Ref_MaThietBi`
			LEFT JOIN (
				SELECT
					*
				FROM
					OPhieuBaoTri
				WHERE
					NgayYeuCau = %1$s
			) AS pbt ON pbt.Ref_MaThietBi = btdk.Ref_MaThietBi
			AND pbt.Ref_LoaiBaoTri = btdk.Ref_LoaiBaoTri
			WHERE
				ifnull(dstb.TrangThai, 0) not in (%7$s)
			AND pbt.IOID IS NULL
			and (%1$s >= btdk.NgayBatDau or btdk.NgayBatDau is null or btdk.NgayBatDau = \'\')
           	and (%1$s <= btdk.NgayKetThuc or btdk.NgayKetThuc is null or btdk.NgayKetThuc = \'\')
			AND (
				(k.MaKy = \'D\' and IFNULL(TIMESTAMPDIFF(DAY,btdk.NgayBatDau,%1$s)%%`LapLai`,0) = 0)
				OR (k.MaKy = \'W\' AND t.GiaTri = %2$d and IFNULL(TIMESTAMPDIFF(WEEK,btdk.NgayBatDau,%1$s)%%`LapLai`,0) = 0)
				OR (k.MaKy = \'M\' AND btdk.Ngay = %3$d and IFNULL(TIMESTAMPDIFF(MONTH,btdk.NgayBatDau,%1$s)%%`LapLai`,0) = 0)
				OR (
					k.MaKy = \'Y\'
					AND btdk.Ngay = %3$d
					AND Thang = %5$d and IFNULL(TIMESTAMPDIFF(YEAR,btdk.NgayBatDau,%1$s)%%`LapLai`,0) = 0
				)
				/*
				OR btdk.IFID_M724 IN (
					SELECT
						IFID_M724
					FROM
						OBaoTriTheoNgay
					WHERE
						Ngay = %1$s
				)
				*/
			)
			AND (
				ifnull(btdk.Ref_DVBT, 0) = 0
				OR ifnull(btdk.DVBT, \'\') = %6$s 
			)'
			, $this->_o_DB->quote($dateFilter)
			, $wday
			, $day
			, $monthNo
			, $month
			, $this->_o_DB->quote($workCenterCode)
            , Qss_Lib_Extra_Const::$EQUIP_STATUS_STOP);

		$dataSql = $this->_o_DB->fetchOne($sql);
		return $dataSql ? $dataSql->TongSo : 0;
	}
	
	/**
	 * Lay ke hoach bao tri duoc lay theo ngay va don vi bao tri (bao gom
	 * ke hoach cua don vi va cac ke hoach khong phan cong don vi thuc hien) 
	 * @param date $dateFilter (YYYY-mm-dd)
	 * @param string $workCenterCode ma don vi bao tri
	 * @param int $page trang so
	 * @param int $display so ban ghi tren trang
	 * @return object
	 * @use-in: Xem ke hoach (Extra button - M759)
	 */
	public function getMaintainPlanByDate($dateFilter, $workCenterCode, $page, $display)
	{
		// Model
		$solar   = new Qss_Model_Calendar_Solar();
		
		// Init
		$date    = date_create($dateFilter);
		$day     = $date->format('d');
		$month   = $date->format('m');
		$monthNo = $solar->montnhNo[(int) $month];
		$wday    = $date->format('w');		
		$start   = ceil(($page - 1) * $display);
		
		$sql = sprintf('
				SELECT
					btdk.*, ifnull(btdk.BenNgoai, 0) AS BenNgoai,
					t.GiaTri AS GiaTriThu,
					k.MaKy
				FROM
					OBaoTriDinhKy AS btdk
		        INNER JOIN OPhanLoaiBaoTri AS plbt on plbt.`IOID` = btdk.`Ref_LoaiBaoTri`
		          INNER JOIN ODanhSachThietBi AS dstb ON dstb.`IOID` = btdk.`Ref_MaThietBi`
                INNER JOIN OKhuVuc as kv1 on kv1.IOID = dstb.Ref_MaKhuVuc		    
				LEFT JOIN OThu AS t ON t.IOID = btdk.Ref_Thu
				LEFT JOIN OKy AS k ON k.IOID = plbt.Ref_KyBaoDuong
				
				LEFT JOIN (
					SELECT
						*
					FROM
						OPhieuBaoTri
					WHERE
						NgayYeuCau = %1$s 
				) AS pbt ON pbt.Ref_MaThietBi = btdk.Ref_MaThietBi
				AND pbt.Ref_LoaiBaoTri = btdk.Ref_LoaiBaoTri
                AND ifnull(pbt.Ref_BoPhan, 0) = ifnull(btdk.Ref_BoPhan, 0)
		    
				LEFT JOIN (
					SELECT kv3.IOID, lft,rgt 
					FROM   OKhuVuc AS kv3  
					WHERE  kv3.NgungHoatDong = 1 
				) as khuvucngunghoatdong on kv1.lft >= khuvucngunghoatdong.lft 
					and kv1.rgt <= khuvucngunghoatdong.rgt		    
				WHERE
					ifnull(dstb.TrangThai, 0) not in (%9$s)
					AND IFNULL(plbt.DungTaoPhieu , 0) = 0
					AND khuvucngunghoatdong.IOID is null 		    
				AND pbt.IOID IS NULL
				and (%1$s >= btdk.NgayBatDau or btdk.NgayBatDau is null or btdk.NgayBatDau = \'\')
	           	and (%1$s <= btdk.NgayKetThuc or btdk.NgayKetThuc is null or btdk.NgayKetThuc = \'\')
				AND (
					(k.MaKy = \'D\' and IFNULL(TIMESTAMPDIFF(DAY,btdk.NgayBatDau,%1$s)%%`LapLai`,0) = 0)
					OR (k.MaKy = \'W\' AND t.GiaTri = %2$d and IFNULL(TIMESTAMPDIFF(WEEK,btdk.NgayBatDau,%1$s)%%`LapLai`,0) = 0)
					OR (k.MaKy = \'M\' AND btdk.Ngay = %3$d and IFNULL(TIMESTAMPDIFF(MONTH,btdk.NgayBatDau,%1$s)%%`LapLai`,0) = 0)
					OR (
						k.MaKy = \'Y\'
						AND btdk.Ngay = %3$d
						AND Thang = %5$d and IFNULL(TIMESTAMPDIFF(YEAR,btdk.NgayBatDau,%1$s)%%`LapLai`,0) = 0
					)
					/*
					OR btdk.IFID_M724 IN (
						SELECT
							IFID_M724
						FROM
							OBaoTriTheoNgay
						WHERE
							Ngay = %1$s
					)
					*/
				)
				AND (
					ifnull(btdk.Ref_DVBT, 0) = 0
					OR ifnull(btdk.DVBT, \'\') = %6$s 
				)
				LIMIT %7$s, %8$s'
				, $this->_o_DB->quote($dateFilter)
				, $wday
				, $day
				, $monthNo
				, $month
				, $this->_o_DB->quote($workCenterCode)
				, $start
				, $display
                , implode(',',Qss_Lib_Extra_Const::$EQUIP_STATUS_STOP));
		return $this->_o_DB->fetchAll($sql);
	}
	
	public function getAllMaintainPlanByDate($dateFilter, $locFilter = 0, $maintTypeFilter = 0)
	{
		// Model
		$solar   = new Qss_Model_Calendar_Solar();
		
		
		// Init
		$where   = '';
		
		if($dateFilter)
		{
			$date    = date_create($dateFilter);
			$day     = $date->format('d');
			$month   = $date->format('m');
			$monthNo = $solar->montnhNo[(int) $month];
			$wday    = $date->format('w');
			
			$where .= sprintf('
					AND (
					(k.MaKy = \'D\')
					OR (k.MaKy = \'W\' AND t.GiaTri = %2$d)
					OR (k.MaKy = \'M\' AND btdk.Ngay = %3$d)
					OR (
						k.MaKy = \'Y\'
						AND btdk.Ngay = %3$d
						AND Thang = %5$d
					)
					/*
					OR btdk.IFID_M724 IN (
						SELECT
							IFID_M724
						FROM
							OBaoTriTheoNgay
						WHERE
							Ngay = %1$s
					)
					*/
					)
				',$this->_o_DB->quote($dateFilter)
				, $wday
				, $day
				, $monthNo
				, $month);
			
		}
		
		if ($locFilter)
		{
			$locName = $this->_common->getTable(array('*')
				, 'OKhuVuc'
				, array('IOID' => $locFilter)
				, array(), 1, 1);
			if ($locName)
				$where .= sprintf('				
					AND (btdk.Ref_MaThietBi IN (
					SELECT
						IOID
					FROM
						ODanhSachThietBi
					WHERE
						Ref_MaKhuVuc IN (
							SELECT
								IOID
							FROM
								OKhuVuc
							WHERE
								lft >= %1$s
							AND rgt <= %2$s
						))
				)', $locName->lft, $locName->rgt);
		}		
		
		if($maintTypeFilter)
		{
			$maintypeTemp = Qss_Lib_Extra::changeToArray($maintTypeFilter);
			
			$where .= sprintf('
				AND btdk.Ref_LoaiBaoTri in (%1$s)
			', implode(',', $maintypeTemp));
		}		
		
		
		$sql = sprintf('
				SELECT
					btdk.*, ifnull(btdk.BenNgoai, 0) AS BenNgoai,
					t.GiaTri AS GiaTriThu,
					k.MaKy,
					group_concat(
						DISTINCT cvbt.MoTa SEPARATOR \'<br>\'
					) AS congviec,
					ifnull(pbt.SoPhieu, \'\') AS SoPhieu
				FROM
					OBaoTriDinhKy AS btdk
                left join OPhanLoaiBaoTri AS plbt ON plbt.IOID = btdk.Ref_LoaiBaoTri
				LEFT JOIN OThu AS t ON t.IOID = btdk.Ref_Thu
				LEFT JOIN OKy AS k ON k.IOID = plbt.Ref_KyBaoDuong
				LEFT JOIN ODanhSachThietBi AS dstb ON dstb.`IOID` = btdk.`Ref_MaThietBi`
				LEFT JOIN (
					SELECT
						*
					FROM
						OPhieuBaoTri
					WHERE
						NgayYeuCau = %1$s
				) AS pbt ON pbt.Ref_MaThietBi = btdk.Ref_MaThietBi
				AND pbt.Ref_LoaiBaoTri = btdk.Ref_LoaiBaoTri
				LEFT JOIN OCongViecBT AS cvbt ON cvbt.IFID_M724 = btdk.IFID_M724
				WHERE
					ifnull(dstb.TrangThai, 0) not in (%3$s)
					%2$s
				GROUP BY
					btdk.IFID_M724
				ORDER BY
					btdk.Ref_MaThietBi
				LIMIT 5000			
			', $this->_o_DB->quote($dateFilter), $where, Qss_Lib_Extra_Const::$EQUIP_STATUS_STOP
				);
		$this->_o_DB->execute('SET SESSION group_concat_max_len = 10000;');
		return $this->_o_DB->fetchAll($sql);
	}
	
	/**
	 * 
	 * @param type $dateFilter
	 * @param type $locFilter
	 * @param type $maintTypeFilter
	 * @return type
	 * @use-in: In ke hoach bao tri
	 */
	public function getAllWorksOfMaintainPlanByDate($dateFilter, $locFilter = 0, $maintTypeFilter = 0)
	{
		// Model
		$solar   = new Qss_Model_Calendar_Solar();
		
		
		// Init
		$where   = '';
		
		if($dateFilter)
		{
			$date    = date_create($dateFilter);
			$day     = $date->format('d');
			$month   = $date->format('m');
			$monthNo = $solar->montnhNo[(int) $month];
			$wday    = $date->format('w');
			
			$where .= sprintf('
					AND (
					(k.MaKy = \'D\')
					OR (k.MaKy = \'W\' AND t.GiaTri = %2$d)
					OR (k.MaKy = \'M\' AND btdk.Ngay = %3$d)
					OR (
						k.MaKy = \'Y\'
						AND btdk.Ngay = %3$d
						AND Thang = %5$d
					)
					/*
					OR btdk.IFID_M724 IN (
						SELECT
							IFID_M724
						FROM
							OBaoTriTheoNgay
						WHERE
							Ngay = %1$s
					)*/)
				',$this->_o_DB->quote($dateFilter)
				, $wday
				, $day
				, $monthNo
				, $month);
			
		}
		
		if ($locFilter)
		{
			$locName = $this->_common->getTable(array('*')
				, 'OKhuVuc'
				, array('IOID' => $locFilter)
				, array(), 1, 1);
			if ($locName)
				$where .= sprintf('				
					AND (btdk.Ref_MaThietBi IN (
					SELECT
						IOID
					FROM
						ODanhSachThietBi
					WHERE
						Ref_MaKhuVuc IN (
							SELECT
								IOID
							FROM
								OKhuVuc
							WHERE
								lft >= %1$s
							AND rgt <= %2$s
						))
				)', $locName->lft, $locName->rgt);
		}		
		
		if($maintTypeFilter)
		{
			$maintypeTemp = Qss_Lib_Extra::changeToArray($maintTypeFilter);
			
			$where .= sprintf('
				AND btdk.Ref_LoaiBaoTri in (%1$s)
			', implode(',', $maintypeTemp));
		}		
		
		
		$sql = sprintf('
				SELECT					
						btdk.IFID_M724
						, ifnull(pbt.SoPhieu, \'\') AS SoPhieu
						, btdk.MaThietBi
						, btdk.TenThietBi
						, btdk.LoaiBaoTri
						, btdk.Ca
						, btdk.DVBT as TenDVBT
						, NULL AS NguoiThucHien		
						, cvbt.BoPhan
						, cvbt.ViTri
						, cvbt.Ref_ViTri						
						, cvbt.MoTa AS MoTaCongViec
						, NULL AS GhiChuCongViec
						, NULL AS Dat					
				FROM
					OBaoTriDinhKy AS btdk
				LEFT JOIN OThu AS t ON t.IOID = btdk.Ref_Thu
				LEFT JOIN OKy AS k ON k.IOID = btdk.Ref_KyBaoDuong
				LEFT JOIN ODanhSachThietBi AS dstb ON dstb.`IOID` = btdk.`Ref_MaThietBi`
				LEFT JOIN (
					SELECT
						*
					FROM
						OPhieuBaoTri
					WHERE
						NgayYeuCau = %1$s
				) AS pbt ON pbt.Ref_MaThietBi = btdk.Ref_MaThietBi
				AND pbt.Ref_LoaiBaoTri = btdk.Ref_LoaiBaoTri
				LEFT JOIN OCongViecBT AS cvbt ON cvbt.IFID_M724 = btdk.IFID_M724
				WHERE
					ifnull(dstb.TrangThai, 0) not in (%3$s)
					%2$s
				/*
				GROUP BY
					btdk.IFID_M724
				*/
				ORDER BY
					btdk.IFID_M724, btdk.Ref_MaThietBi, cvbt.Ref_ViTri
				LIMIT 5000			
			', $this->_o_DB->quote($dateFilter), $where, Qss_Lib_Extra_Const::$EQUIP_STATUS_STOP
				);
		return $this->_o_DB->fetchAll($sql);
	}
		
	/**
	 * 
	 * @param type $dateFilter
	 * @param type $locFilter
	 * @param type $maintTypeFilter
	 * @return type
	 * @use-in: In ke hoach bao tri
	 */
	public function getAllMaterialsOfMaintainPlanByDate($dateFilter, $locFilter = 0, $maintTypeFilter = 0)
	{
		// Model
		$solar   = new Qss_Model_Calendar_Solar();
		
		
		// Init
		$where   = '';
		
		if($dateFilter)
		{
			$date    = date_create($dateFilter);
			$day     = $date->format('d');
			$month   = $date->format('m');
			$monthNo = $solar->montnhNo[(int) $month];
			$wday    = $date->format('w');
			
			$where .= sprintf('
					AND (
					(k.MaKy = \'D\')
					OR (k.MaKy = \'W\' AND t.GiaTri = %2$d)
					OR (k.MaKy = \'M\' AND btdk.Ngay = %3$d)
					OR (
						k.MaKy = \'Y\'
						AND btdk.Ngay = %3$d
						AND Thang = %5$d
					)
					/*
					OR btdk.IFID_M724 IN (
						SELECT
							IFID_M724
						FROM
							OBaoTriTheoNgay
						WHERE
							Ngay = %1$s
					)*/)
				',$this->_o_DB->quote($dateFilter)
				, $wday
				, $day
				, $monthNo
				, $month);
			
		}
		
		if ($locFilter)
		{
			$locName = $this->_common->getTable(array('*')
				, 'OKhuVuc'
				, array('IOID' => $locFilter)
				, array(), 1, 1);
			if ($locName)
				$where .= sprintf('				
					AND (btdk.Ref_MaThietBi IN (
					SELECT
						IOID
					FROM
						ODanhSachThietBi
					WHERE
						Ref_MaKhuVuc IN (
							SELECT
								IOID
							FROM
								OKhuVuc
							WHERE
								lft >= %1$s
							AND rgt <= %2$s
						))
				)', $locName->lft, $locName->rgt);
		}		
		
		if($maintTypeFilter)
		{
			$maintypeTemp = Qss_Lib_Extra::changeToArray($maintTypeFilter);
			
			$where .= sprintf('
				AND btdk.Ref_LoaiBaoTri in (%1$s)
			', implode(',', $maintypeTemp));
		}		
		
		
		$sql = sprintf('
				SELECT
						btdk.IFID_M724
						, ifnull(pbt.SoPhieu, \'\') AS SoPhieu
						, btdk.MaThietBi
						, btdk.TenThietBi
						, btdk.LoaiBaoTri
						, btdk.Ca
						, btdk.DVBT as TenDVBT
						, NULL AS NguoiThucHien
						, vt.BoPhan
						, vt.ViTri
						, vt.Ref_ViTri
						, group_concat(DISTINCT concat(vt.MaVatTu,\': \', vt.SoLuong, \' \', vt.DonViTinh) SEPARATOR \'<br>\') as VatTu	
				FROM
					OBaoTriDinhKy AS btdk
					left join OPhanLoaiBaoTri AS plbt ON plbt.IOID = btdk.Ref_LoaiBaoTri
				LEFT JOIN OThu AS t ON t.IOID = btdk.Ref_Thu
				LEFT JOIN OKy AS k ON k.IOID = plbt.Ref_KyBaoDuong
				LEFT JOIN ODanhSachThietBi AS dstb ON dstb.`IOID` = btdk.`Ref_MaThietBi`
				LEFT JOIN (
					SELECT
						*
					FROM
						OPhieuBaoTri
					WHERE
						NgayYeuCau = %1$s
				) AS pbt ON pbt.Ref_MaThietBi = btdk.Ref_MaThietBi
				AND pbt.Ref_LoaiBaoTri = btdk.Ref_LoaiBaoTri
				LEFT JOIN OVatTu AS vt ON vt.IFID_M724 = btdk.IFID_M724
				WHERE
					ifnull(dstb.TrangThai, 0) not in (%3%s)
					%2$s
				GROUP BY
					btdk.IFID_M724
				ORDER BY
					btdk.IFID_M724, btdk.Ref_MaThietBi, vt.Ref_ViTri
				LIMIT 5000			
			', $this->_o_DB->quote($dateFilter), $where, Qss_Lib_Extra_Const::$EQUIP_STATUS_STOP
				);
		$this->_o_DB->execute('SET SESSION group_concat_max_len = 10000;');
		return $this->_o_DB->fetchAll($sql);
	}	
	
	
	/**
	 * Dem so luong phieu bao tri theo loai cua thiet bi
	 * @param int $equipIOID IOID cua thiet bi
	 * @return object
	 * @use-in: Thong tin thiet bi - bieu do thong ke loai bao tri theo thiet bi
	 */
	public function countMaintTypeOfEquip($equipIOID)
	{
		$sql = sprintf('
			SELECT Ref_LoaiBaoTri, count(1) AS Total
			FROM OPhieuBaoTri
			WHERE Ref_MaThietBi = %1$d		
			GROUP BY Ref_LoaiBaoTri
		', $equipIOID);
		return $this->_o_DB->fetchAll($sql);
	}	
	
	public function getSparepartOfItem($ifid, $refcomponent)
	{
		$where = array();
		$sWhere = '';

		if ($ifid)
		{
			$where[] = sprintf(' dspt.IFID_M705 = %1$d', $ifid);
		}
        else
		{
			return;
		}

		if ($ifid && $refcomponent)
		{
			$com = $this->_common->getTable(array('*')
				, 'OCauTrucThietBi'
				, array('IFID_M705' => $ifid, 'IOID' => $refcomponent)
				, array(), 1, 1);
			
			if ($com)
			{
				$where[] = sprintf('
                    dspt.IOID in (
                        select IOID from OCauTrucThietBi where lft>= %1$d and  rgt <= %2$d
                    )'
                , $com->lft, $com->rgt);
			}
		}

		if (count($where))
		{
			$sWhere = sprintf(' where %1$s ', implode(' and ', $where));
		}

		$sql = sprintf('
                        SELECT
                            dspt.IFID_M705,
                            dspt.IOID,
                            dspt.IOID AS Ref_ViTri,
                            sanpham.IOID AS Ref_MaSP,
                            sanpham.MaSanPham AS MaSP,
                            sanpham.TenSanPham AS TenSP,
                            sanpham.DonViTinh AS DonViTinh, 
                            ifnull(dspt.SoNgayCanhBao, 0) AS SoNgayCanhBao,
                            kho.TonKho,
                            (ifnull(dspt.SoLuongChuan, 0) * ifnull(dvtinhsp.HeSoQuyDoi, 0))AS SoLuongChuan,
                            (ifnull(dspt.SoLuongHC, 0) * ifnull(dvtinhsp.HeSoQuyDoi, 0)) AS SoLuongHC,
                            null AS ThayThe,
                            /*ifnull(dspt.ThayThe, \'\') AS ThayThe,*/
                            ThietBi.IOID AS Ref_ThietBi,
                            PhuTungThayThe.MaSP AS MaPhuTungThayThe,
                            PhuTungThayThe.TenSP AS TenPhuTungThayThe,
                            PhuTungThayThe.DonViTinh AS DVTPhuTungThayThe
                        FROM
                            OCauTrucThietBi AS dspt
                        INNER JOIN ODanhSachThietBi AS ThietBi ON dspt.IFID_M705 = ThietBi.IFID_M705
                        LEFT JOIN OSanPham AS sanpham ON dspt.Ref_MaSP = sanpham.IOID
                        LEFT JOIN ODonViTinhSP AS dvtinhsp ON sanpham.IFID_M113 = dvtinhsp.IFID_M113 AND dspt.Ref_DonViTinh = dvtinhsp.IOID
                        LEFT JOIN 
                        (
                            SELECT sum((ifnull(kho.SoLuongHC, 0) * ifnull(dvtinhsp.HeSoQuyDoi, 0))) AS TonKho, kho.Ref_MaSP
                            FROM OKho AS kho 
                            LEFT JOIN OSanPham AS sanpham ON kho.Ref_MaSP = sanpham.IOID
                            LEFT JOIN ODonViTinhSP AS dvtinhsp ON sanpham.IFID_M113 = dvtinhsp.IFID_M113 AND kho.Ref_DonViTinh = dvtinhsp.IOID
                            GROUP BY kho.Ref_MaSP
                        ) AS kho ON dspt.Ref_MaSP = kho.Ref_MaSP

                        LEFT JOIN ODanhSachPhuTung AS PhuTungThayThe ON ThietBi.IFID_M705 = PhuTungThayThe.IFID_M705 AND
                            dspt.IOID = PhuTungThayThe.Ref_ViTri

                        %1$s
                        ORDER BY dspt.lft, dspt.IOID
                        LIMIT 1000
                                ', $sWhere);
        // echo '<pre>'; print_r($sql); die;

		return $this->_o_DB->fetchAll($sql);

	}
    
    public function getInventorySparepartOfItem($ifid, $refcomponent)
    {
        
    }
	public function getMaintainPlanByEquipment($eqIOID)
	{
		$sql = sprintf('
			SELECT kh.*,
                (
                    SELECT COUNT(1) 
                    FROM OCauTrucThietBi as u
                    INNER JOIN ODanhSachThietBi as k on k.IFID_M705=u.IFID_M705
                    WHERE k.IOID = %1$d AND u.lft<=ct.lft AND u.rgt >= ct.rgt
                ) as level
			FROM OBaoTriDinhKy AS kh		  
			LEFT JOIN OCauTrucThietBi as ct ON ct.IOID = kh.Ref_BoPhan
			WHERE kh.Ref_MaThietBi = %1$d
			ORDER BY ct.lft
		', $eqIOID);
		return $this->_o_DB->fetchAll($sql);
	}    
	public function getMaintainPlanByComponent($comIFID, $comIOID)
	{
		$sql = sprintf('
			SELECT kh.*,(SELECT count(*) FROM OCauTrucThietBi as u
				inner join ODanhSachThietBi as k on k.IFID_M705=u.IFID_M705
				WHERE k.IOID = %1$d and u.lft<=ct.lft and u.rgt >= ct.rgt) as level
			FROM OBaoTriDinhKy AS kh
			INNER JOIN ODanhSachThietBi AS tb ON tb.IOID = kh.Ref_MaThietBi
			INNER JOIN OCauTrucThietBi AS ct ON ct.IOID = kh.Ref_BoPhan and tb.IFID_M705 = ct.IFID_M705
			where tb.IFID_M705 = %1$d
			and ct.lft >= (select lft from OCauTrucThietBi where IOID = %2$d) 
			and ct.rgt <= (select rgt from OCauTrucThietBi where IOID = %2$d)
			order by ct.lft   		
		', $comIFID, $comIOID);
		return $this->_o_DB->fetchAll($sql);
	}
	
	public function getWorkOfMaintainPlanByComponent($comIFID, $comIOID)
	{
		$sql = sprintf('
			SELECT cv.*
			FROM OCongViecBT AS cv
			INNER JOIN OBaoTriDinhKy AS kh ON cv.IFID_M724 = kh.IFID_M724
			INNER JOIN ODanhSachThietBi AS tb ON tb.IOID = kh.Ref_MaThietBi
			INNER JOIN OCauTrucThietBi AS ct ON ct.IOID = cv.Ref_ViTri and tb.IFID_M705 = ct.IFID_M705
			where tb.IFID_M705 = %1$d
			and ct.lft >= (select lft from OCauTrucThietBi where IOID = %2$d) 
			and ct.rgt <= (select rgt from OCauTrucThietBi where IOID = %2$d)
			order by ct.lft   
		', $comIFID, $comIOID);
		return $this->_o_DB->fetchAll($sql);
	}
	
	public function getMaterialOfMaintainPlanByComponent($comIFID, $comIOID)
	{
		$sql = sprintf('
			SELECT vt.*
			FROM OVatTu AS vt
			INNER JOIN OBaoTriDinhKy AS kh ON vt.IFID_M724 = kh.IFID_M724
			INNER JOIN ODanhSachThietBi AS tb ON tb.IOID = kh.Ref_MaThietBi
			where tb.IFID_M705 = %1$d
			and vt.Ref_ViTri = %2$d
		', $comIFID, $comIOID);
		return $this->_o_DB->fetchAll($sql);
	}	
	
	public function countMaintainOrderByComponent($comIFID, $comIOID)
	{
		$sql = sprintf('
			SELECT COUNT(1) AS TOTAL
			FROM
			(
			(SELECT kh.*
			FROM OCongViecBTPBT AS cv
			INNER JOIN OPhieuBaoTri AS kh ON cv.IFID_M759 = kh.IFID_M759
			INNER JOIN ODanhSachThietBi AS tb ON tb.IOID = kh.Ref_MaThietBi
			where tb.IFID_M705 = %1$d
			and cv.Ref_ViTri = %2$d)
			UNION
			(SELECT kh.*
			FROM OVatTuPBT AS cv
			INNER JOIN OPhieuBaoTri AS kh ON cv.IFID_M759 = kh.IFID_M759
			INNER JOIN ODanhSachThietBi AS tb ON tb.IOID = kh.Ref_MaThietBi
			where tb.IFID_M705 = %1$d
			and cv.Ref_ViTri = %2$d)
			) AS t
		', $comIFID, $comIOID);
		$dataSql = $this->_o_DB->fetchOne($sql);		
		return $dataSql?$dataSql->TOTAL:0;
	}
	
	public function getMaintainOrderByComponent($comIFID, $comIOID, $display, $page)
	{
		$start = ceil(($page - 1) * $display);
		$sql = sprintf('
			SELECT *
			FROM
			(
			(SELECT kh.*
			FROM OCongViecBTPBT AS cv
			INNER JOIN OPhieuBaoTri AS kh ON cv.IFID_M759 = kh.IFID_M759
			INNER JOIN ODanhSachThietBi AS tb ON tb.IOID = kh.Ref_MaThietBi
			where tb.IFID_M705 = %1$d
			and cv.Ref_ViTri = %2$d)
			UNION
			(SELECT kh.*
			FROM OVatTuPBT AS cv
			INNER JOIN OPhieuBaoTri AS kh ON cv.IFID_M759 = kh.IFID_M759
			INNER JOIN ODanhSachThietBi AS tb ON tb.IOID = kh.Ref_MaThietBi
			where tb.IFID_M705 = %1$d
			and cv.Ref_ViTri = %2$d)
			) AS t
			
			LIMIT %3$d, %4$d
		', $comIFID, $comIOID, $start, $display);
		return $this->_o_DB->fetchAll($sql);		
	}
	
	/**
	 * Lấy đối tượng OCongViecBTPBT của phiếu bảo trì hoặc pbt kế hoạch 
	 * (OPhieuBaoTri)
	 * @param string $module (M759, M729)
	 * @param int $ifid
	 * @return object
	 */
	public function getTasksOfOPhieuBaoTri($module, $ifid)
	{
		$sql = sprintf('
			SELECT 
				congviec.*
				,(
				SELECT count(*)
				FROM OCauTrucThietBi AS u
				WHERE u.lft <= cautructb.lft AND u.rgt >= cautructb.rgt
				AND u.IFID_M705 = thietbi.IFID_M705
				) AS LEVEL
			FROM OCongViecBTPBT AS congviec
			INNER JOIN OPhieuBaoTri AS phieubt ON congviec.IFID_%1$s = phieubt.IFID_%1$s
			INNER JOIN ODanhSachThietBi AS thietbi ON phieubt.Ref_MaThietBi = thietbi.IOID 
			LEFT JOIN OCauTrucThietBi AS cautructb ON congviec.Ref_ViTri = cautructb.IOID 
			/* JOIN de sap xep bo phan vi tri theo hinh cay*/
			WHERE phieubt.IFID_%1$s = %2$d
			ORDER BY cautructb.lft, congviec.Ref_ViTri
		', trim($module), $ifid);
		return $this->_o_DB->fetchAll($sql);
	}
	
	
		
}
