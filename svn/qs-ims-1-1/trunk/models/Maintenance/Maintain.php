<?php
/**
 * 
 * Phiếu bảo trì
 * Kế hoạch bảo trì
 * Danh sách thiết bị
 */
class Qss_Model_Maintenance_Maintain extends Qss_Model_Abstract
{
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * @param string $fieldWithAlias
	 * @param int $locIOID
	 */
	public function getFilterByLocIOIDStr($fieldWithAlias, $locIOID)
	{
		if($locIOID)
		{
			$findLocSql = sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locIOID);
			$findLoc    = $this->_o_DB->fetchOne($findLocSql);
			
			return ($findLoc)?sprintf(' %3$s in (select IOID from ODanhSachThietBi where Ref_MaKhuVuc in 
						(select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))'
					, $findLoc->lft, $findLoc->rgt, $fieldWithAlias):'';
		}
		return '';
	}
	
	/**
	 * @require: yêu cầu phải chọn từ "Kế hoạch bảo trì" (Alias: btdk)
	 * join với bảng kỳ (alias:k) và thứ (alias:t)
	 * @param date $date
	 * @param string $planOrderAlias
	 * @param string $periodAlias
	 * @param string $dayAlias
	 * @return string sql
	 */
	public function getFilterByPeriodForPlanOrder($date
		, $planOrderAlias = 'btdk'
		, $periodAlias ='k'
		, $dayAlias = 't')
	{
		if ($date)
		{
			$solar   = new Qss_Model_Calendar_Solar();
			$date    = date_create($date);
			$day     = $date->format('d');
			$month   = $date->format('m');
			$monthNo = $solar->montnhNo[(int) $month];
			$wday    = $date->format('w');

			return sprintf('
				 (
					(
						%6$s.MaKy = \'D\'
						AND IFNULL(
							TIMESTAMPDIFF(DAY, %5$s.NgayBatDau ,%1$s) %% %5$s.LapLai,
							0) = 0
					)
					OR (
						%6$s.MaKy = \'W\'
						AND %7$s.GiaTri =%2$d
						AND IFNULL(
							TIMESTAMPDIFF(WEEK, %5$s.NgayBatDau ,%1$s) %% %5$s.LapLai,
							0) = 0
					)
					OR (
						%6$s.MaKy = \'M\'
						AND %5$s.Ngay =%3$d
						AND IFNULL(
							TIMESTAMPDIFF(MONTH, %5$s.NgayBatDau ,%1$s) %% %5$s.LapLai,
							0) = 0
					)
					OR (
						%6$s.MaKy = \'Y\'
						AND %5$s.Ngay =%3$d
						AND %5$s.Thang =%4$d
						AND IFNULL(
							TIMESTAMPDIFF(YEAR, %5$s.NgayBatDau ,%1$s) %% %5$s.LapLai,
							0) = 0
					)
					/*
					OR IFID_M724 IN (
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
					%1$s >= %5$s.NgayBatDau
					OR %5$s.NgayBatDau IS NULL
					OR %5$s.NgayBatDau = \'\'
				)
				AND (
					%1$s <= %5$s.NgayKetThuc
					OR %5$s.NgayKetThuc IS NULL
					OR %5$s.NgayKetThuc = \'\'
				)', $this->_o_DB->quote($date->format('Y-m-d')), $wday, $day, $month
				,$planOrderAlias, $periodAlias, $dayAlias);			
		}
		return '';
	}
}