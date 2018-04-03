<?php

class Qss_Model_Extra_WCalendar extends Qss_Model_Abstract
{

	public function __construct()
	{
		parent::__construct();

	}
	
	/**
	 * Get Work Cals Of Equip By Time (Not Join Equip List)
	 * @param int array $eqIOIDArr equip ioid
	 * @param date $start YYYY-mm-dd
	 * @param date $end YYY-mm-dd
	 * @return object WCals Of Equip By Time
	 */
	public function getWCalOfEquipByTimeByEqCal($eqIOIDArr, $start, $end)
	{
		$sql = sprintf(' SELECT ltb.*, dsdieudongtb.MaThietBi, dsdieudongtb.Ref_MaThietBi
			FROM  OLichThietBi AS ltb
			INNER JOIN ODanhSachDieuDongThietBi AS dsdieudongtb ON ltb.IFID_M706 = dsdieudongtb.IFID_M706
			INNER JOIN qsiforms AS ifo ON ltb.IFID_M706 = ifo.IFID
			WHERE dsdieudongtb.Ref_MaThietBi in (%1$s)
			AND ifnull(ifo.Status, 0) in (2, 3)
			AND 
				!(%3$s < ltb.NgayBatDau 
				OR %2$s > ltb.NgayKetThuc)
			'
			, implode(', ', $eqIOIDArr)
			, $this->_o_DB->quote($start)
			, $this->_o_DB->quote($end));
		return $this->_o_DB->fetchAll($sql);
	}	

	/**
	 * Get Work Cals Of Equip By Time (Join Equip List)
	 * @param int array $eqIOIDArr equip ioid
	 * @param date $start YYYY-mm-dd
	 * @param date $end YYY-mm-dd
	 * @return object WCals Of Equip By Time
	 */
	public function getWCalOfEquipByTimeByEqList($eqIOIDArr, $start, $end)
	{
		$sql = sprintf(' SELECT 
			ltb.*
			, ifnull(ltb.Ref_LichLamViec, dstb.Ref_LichLamViec) AS Ref_LichLamViec
			, ifnull(ltb.Ref_MaKhuVuc, dstb.Ref_MaKhuVuc) AS Ref_MaKhuVuc
			, if(ifnull(ltb.LichLamViec, \'\') = \'\', dstb.LichLamViec, ltb.LichLamViec) 
				AS LichLamViec
			, if(ifnull(ltb.MaKhuVuc, \'\') = \'\', dstb.MaKhuVuc, ltb.MaKhuVuc) 
				AS MaKhuVuc						
			FROM ODanhSachThietBi AS dstb  
			LEFT JOIN OLichThietBi AS ltb ON dstb.IOID = ltb.Ref_MaThietBi
				AND 
				!(%3$s < ltb.NgayBatDau 
				OR %2$s > ltb.NgayKetThuc)
			LEFT JOIN qsiforms AS ifo ON ltb.IFID_M706 = ifo.IFID
			WHERE dstb.IOID in (%1$s)
			AND (ifnull(ifo.Status, 0) = 2 OR ifnull(ifo.Status, 0) = 0)
			'
			, implode(', ', $eqIOIDArr)
			, $this->_o_DB->quote($start)
			, $this->_o_DB->quote($end));
		return $this->_o_DB->fetchAll($sql);
	}

	/**
	 * Lay thoi gian lam viec cua tung ca theo lich lam viec (Không bao gồm lịch
	 * đặc biệt)
	 * @param mix $calendar working calendar ioid
	 * @return object working hours by weekdays (not include special calendar)
	 * ngay{day_of_week_0_6} = tổng thời gian lv của ngày;
	 * Ngay{day_of_week_0_6}_RefCa{Shift_1_4} = thời gian làm việc của ca;
	 * Ngay{day_of_week_0_6}_Ca{Shift_1_4} = Ref IOID của ca;
	 * LLVIOID = IOID của lịch làm việc;
	 */
	public function getWorkingHoursPerWeekdays($calendar)
	{
		if(!count($calendar)) return array();
		$tmp = '';
		$comma = 0;
		for ($i = 0; $i < 7; $i++)
		{
			if ($comma)
			{
				$tmp .= ',';
			} else
			{
				$comma = 1;
			}
			$tmp .= '(';
			$start = 0;
			for ($j = 1; $j <= 4; $j++)
			{
				if ($start)
				{
					$tmp .= ' + ';
				} else
				{
					$start = 1;
				}
				$tmp .= " IFNULL(c{$i}{$j}.SoGio, 0) ";
			}
			$tmp .= ") as Ngay{$i} ";


			///
			for ($j = 1; $j <= 4; $j++)
			{
				$tmp .= ", c{$i}{$j}.GioBatDau AS Ngay{$i}_Ca{$j}_Start";
				$tmp .= ", c{$i}{$j}.GioKetThuc AS Ngay{$i}_Ca{$j}_End";
				$tmp .= ", IFNULL(c{$i}{$j}.SoGio, 0) as Ngay{$i}_Ca{$j}";
				$tmp .= ", llvn{$i}.Ref_Shift{$j} as Ngay{$i}_RefCa{$j}";
			}
		}
		
		$tmp .= ($tmp) ? ' , llv.IOID as LLVIOID ' : ' 0 as LLVIOID '; // :' null ';
		$sql = sprintf('select 
							%1$s
						from OLichLamViec as llv
						left join OLichLamViecNgay as llvn1
						on llv.Ref_ThuHai = llvn1.IOID
								left join OCa as c11
								on  c11.IOID = llvn1.Ref_Shift1
								left join OCa as c12
								on  c12.IOID = llvn1.Ref_Shift2
								left join OCa as c13
								on  c13.IOID = llvn1.Ref_Shift3
								left join OCa as c14
								on  c14.IOID = llvn1.Ref_Shift4
						left join OLichLamViecNgay as llvn2
						on llv.Ref_ThuBa = llvn2.IOID
								left join OCa as c21
								on  c21.IOID = llvn2.Ref_Shift1
								left join OCa as c22
								on  c22.IOID = llvn2.Ref_Shift2
								left join OCa as c23
								on  c23.IOID = llvn2.Ref_Shift3
								left join OCa as c24
								on  c24.IOID = llvn2.Ref_Shift4							
						left join OLichLamViecNgay as llvn3
						on llv.Ref_ThuTu = llvn3.IOID
								left join OCa as c31
								on  c31.IOID = llvn3.Ref_Shift1
								left join OCa as c32
								on  c32.IOID = llvn3.Ref_Shift2
								left join OCa as c33
								on  c33.IOID = llvn3.Ref_Shift3
								left join OCa as c34
								on  c34.IOID = llvn3.Ref_Shift4							
						left join OLichLamViecNgay as llvn4
						on llv.Ref_ThuNam = llvn4.IOID
								left join OCa as c41
								on  c41.IOID = llvn4.Ref_Shift1
								left join OCa as c42
								on  c42.IOID = llvn4.Ref_Shift2
								left join OCa as c43
								on  c43.IOID = llvn4.Ref_Shift3
								left join OCa as c44
								on  c44.IOID = llvn4.Ref_Shift4		
						left join OLichLamViecNgay as llvn5
						on llv.Ref_ThuSau = llvn5.IOID
								left join OCa as c51
								on  c51.IOID = llvn5.Ref_Shift1
								left join OCa as c52
								on  c52.IOID = llvn5.Ref_Shift2
								left join OCa as c53
								on  c53.IOID = llvn5.Ref_Shift3
								left join OCa as c54
								on  c54.IOID = llvn5.Ref_Shift4	
						left join OLichLamViecNgay as llvn6
						on llv.Ref_ThuBay = llvn6.IOID
								left join OCa as c61
								on  c61.IOID = llvn6.Ref_Shift1
								left join OCa as c62
								on  c62.IOID = llvn6.Ref_Shift2
								left join OCa as c63
								on  c63.IOID = llvn6.Ref_Shift3
								left join OCa as c64
								on  c64.IOID = llvn6.Ref_Shift4							
						left join OLichLamViecNgay as llvn0
						on llv.Ref_ChuNhat = llvn0.IOID
								left join OCa as c01
								on  c01.IOID = llvn0.Ref_Shift1
								left join OCa as c02
								on  c02.IOID = llvn0.Ref_Shift2
								left join OCa as c03
								on  c03.IOID = llvn0.Ref_Shift3
								left join OCa as c04
								on  c04.IOID = llvn0.Ref_Shift4							
						', $tmp);
		$tmp2 = '';
		foreach ($calendar as $cl)
		{
			$tmp2 .= ($tmp2 ? ' or ' : '') . sprintf(' llv.IOID = %1$d ', $cl);
		}
		$sql .= ($tmp2) ? ' where ' . $tmp2 : '';
		$sql .= ' order by llv.IOID ';
		// echo $sql; die;
		return ($tmp2) ? $this->_o_DB->fetchAll($sql) : array();

	}

	/**
	 * Trả về lịch đặc biệt (Không bao gồm lịch làm việc thông thường)
	 * @param array $refLichLamViecArray 
	 * @param date $startDate
	 * @return object Lich Đặc biệt
	 */
	public function getSpecialCalendars($refLichLamViecArray, $startDate)
	{
		$tmp = '';
		$dateTmp = '';
		$sql = sprintf('select llv.IOID as LLVIOID, ldb.Ref_LichNgay, ldb.Ngay,
						c1.GioBatDau AS Ca1_Start,
						c1.GioKetThuc AS Ca1_End,
						IFNULL(c1.SoGio, 0) as Ca1,
						llvn.Ref_Shift1 as RefCa1,
						c2.GioBatDau AS Ca2_Start,
						c2.GioKetThuc AS Ca2_End,						
						IFNULL(c2.SoGio, 0) as Ca2,
						llvn.Ref_Shift2 as RefCa2,
						c3.GioBatDau AS Ca3_Start,
						c3.GioKetThuc AS Ca3_End,							
						IFNULL(c3.SoGio, 0) as Ca3,
						llvn.Ref_Shift3 as RefCa3,
						c4.GioBatDau AS Ca4_Start,
						c4.GioKetThuc AS Ca4_End,							
						IFNULL(c4.SoGio, 0) as Ca4,
						llvn.Ref_Shift4 as RefCa4
						from OLichLamViec as llv
						inner join OLichDacBiet as ldb on llv.IFID_M107 = ldb.IFID_M107
						inner join OLichLamViecNgay as llvn on ldb.Ref_LichNgay = llvn.IOID
						left join OCa as c1
						on  c1.IOID = llvn.Ref_Shift1
						left join OCa as c2
						on  c2.IOID = llvn.Ref_Shift2
						left join OCa as c3
						on  c3.IOID = llvn.Ref_Shift3
						left join OCa as c4
						on  c4.IOID = llvn.Ref_Shift4
						where 1 = 1
						');

		if ($startDate)
		{
			$dateTmp .= sprintf(' and ldb.Ngay >= %1$s ', $this->_o_DB->quote($startDate));
		}
		$sql .= $dateTmp;

		foreach ($refLichLamViecArray as $refLichLamViec)
		{
			$tmp .= $tmp ? ' or ' : '';
			$tmp .= sprintf(' llv.IOID = %1$d ', $refLichLamViec);
		}

		$sql .= $tmp ? "and ({$tmp})" : '';
		//echo $sql; die;		
		return (is_array($refLichLamViecArray) && count($refLichLamViecArray)) ? $this->_o_DB->fetchAll($sql) : array();

	}

	public function getDowntimePlans($date, $eqIOIDArr)
	{
		$sql = sprintf('
			SELECT 
				dm.NgayDungMay AS StopDate
				, dm.NgayKetThucDungMay AS RunDate
				, dm.ThoiGianDungMay AS StopTime
				, dm.ThoiGianKetThucDungMay AS RunTime
				, dm.Ref_MaThietBi AS EquipIOID
				, dm.MaThietBi AS EquipCode
				, dm.TenThietBi AS EquipName
				, CASE WHEN %1$s = dm.NgayDungMay 
				THEN (TIME_TO_SEC(ifnull(TIMEDIFF(\'24:00:00\', ifnull(dm.ThoiGianDungMay, \'00:00:00\')),0))/3600)
				WHEN %1$s = dm.NgayKetThucDungMay THEN (TIME_TO_SEC(ifnull(dm.ThoiGianDungMay, \'00:00:00\'))/3600)
				ELSE 24 END AS `Time`
				, CASE WHEN %1$s = dm.NgayDungMay 
				THEN %3$s
				WHEN %1$s = dm.NgayKetThucDungMay THEN %4$s
				ELSE %5$s END AS `Type`
			FROM OPhieuBaoTri AS dm
			INNER JOIN qsiforms AS qsif ON dm.IFID_M759 = qsif.IFID
			LEFT JOIN ODanhSachThietBi AS dstb ON dm.Ref_MaThietBi = dstb.IOID 
			LEFT JOIN OKhuVuc AS kv ON kv.IOID = dstb.Ref_MaKhuVuc
			WHERE (%1$s between dm.NgayDungMay AND dm.NgayKetThucDungMay)
				AND 
				(
					dm.Ref_MaThietBi in (%2$s) 
				)
			ORDER BY dm.Ref_MaThietBi
			'
		, $this->_o_DB->quote($date)
		, implode(', ', $eqIOIDArr)
		, $this->_o_DB->quote(Qss_Lib_Extra_WCalendar::EQUAL_START)
		, $this->_o_DB->quote(Qss_Lib_Extra_WCalendar::EQUAL_END)
		, $this->_o_DB->quote(Qss_Lib_Extra_WCalendar::IN_RANGE));
		//echo $sql;die;
		return $this->_o_DB->fetchAll($sql);
	}
	
	public function getEquipsStopInfo($eqIOIDArr = array())
	{
		$eqIOIDArr[] = 0;
		$sql = sprintf('
			SELECT 
				dstb.IOID AS EQIOID
				, CASE WHEN ifnull(kv2.NgungHoatDong, 0) = 1 Then 1
				WHEN ifnull(kv1.NgungHoatDong, 0) = 1 Then 1
				WHEN ifnull(dstb.TrangThai, 0)  in (%2$s) Then 1
				ELSE 0 END AS `Stop`
			FROM ODanhSachThietBi AS dstb
			LEFT JOIN OKhuVuc AS kv1 ON kv1.IOID = dstb.Ref_MaKhuVuc
			LEFT JOIN OKhuVuc AS kv2 ON 
				kv1.lft >= kv2.lft 
				and kv1.rgt <= kv2.rgt
				and ifnull(kv2.NgungHoatDong, 0) = 1 /* Giam so luong tra ve */
			WHERE dstb.IOID in (%1$s)
			ORDER BY dstb.IOID
		', implode(', ', $eqIOIDArr), implode(', ',Qss_Lib_Extra_Const::$EQUIP_STATUS_STOP));
		return $this->_o_DB->fetchAll($sql);
	}
	
	public function getLocationsStopInfo($locIOIDArr = array())
	{
		$locIOIDArr[] = 0;
		$sql = sprintf('
			SELECT 
				kv2.IOID,
				if( ifnull(kv3.NgungHoatDong, 0) = 0, ifnull(kv2.NgungHoatDong,0), kv3.NgungHoatDong) AS `Stop`
			FROM
				(SELECT kv1.*
				FROM OKhuVuc AS kv1
				WHERE IOID in (%1$s)) AS kv2
			INNER JOIN OKhuVuc AS kv3 ON 
				kv2.lft >= kv3.lft and kv2.rgt <= kv3.rgt 
			ORDER BY kv2.IOID, kv2.lft
		', implode(', ', $locIOIDArr));
		return $this->_o_DB->fetchAll($sql);
	}	
}
