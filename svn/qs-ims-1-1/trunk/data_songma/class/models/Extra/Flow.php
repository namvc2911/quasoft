<?php

class Qss_Model_Extra_Flow extends Qss_Model_Abstract
{

	//-----------------------------------------------------------------------
	/**
	 * Build constructor
	 * '
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

	}

	public function getLuuLuongByWorksationGroupByYear($workstation)
	{
		$sql = sprintf('select *, year(Ngay) as `Year`
                                     from LuuLuong
                                     where Ref_Tram = %1$d
                                     group by year(Ngay)', $workstation);
		return $this->_o_DB->fetchAll($sql);

	}

	public function getLuongMuaByWorksationGroupByYear($workstation)
	{
		$sql = sprintf('select *, year(Ngay) as `Year`
                                     from LuongMua
                                     where Ref_Tram = %1$d
                                     group by year(Ngay)', $workstation);
		return $this->_o_DB->fetchAll($sql);

	}
	
	public function getAvgLuuLuongByYear($workstation, $startYear, $endYear)
	{
		$sql = sprintf('select avg(LuuLuong) AS `AVG`, year(Ngay) as `Year` 
                                     from LuuLuong
                                     where Ref_Tram = %1$d
									 and year(Ngay) between %2$s and %3$s
                                     group by year(Ngay)'
			, $workstation
			, $startYear
			, $endYear);
		return $this->_o_DB->fetchAll($sql);		
	}
	
	public function getAvgLuongMuaByYear($workstation, $startYear, $endYear)
	{
		$sql = sprintf('select avg(LuongMua) AS `AVG`, year(Ngay) as `Year` 
                                     from LuongMua
                                     where Ref_Tram = %1$d
									 and year(Ngay) between %2$s and %3$s
                                     group by year(Ngay)'
			, $workstation
			, $startYear
			, $endYear);
		return $this->_o_DB->fetchAll($sql);		
	}
	
	public function getAvgMucNuocByYear($workstation, $startYear, $endYear)
	{
		$sql = sprintf('select avg(MucNuoc) AS `AVG`, year(Ngay) as `Year` 
                                     from MucNuoc
                                     where Ref_Tram = %1$d
									 and year(Ngay) between %2$s and %3$s
                                     group by year(Ngay)'
			, $workstation
			, $startYear
			, $endYear);
		return $this->_o_DB->fetchAll($sql);		
	}

	public function getAvgLuuLuongByDay($workstation, $month, $year)
	{
		$sql = sprintf('select avg(ifnull(LuuLuong,0)) AS `AVG`, day(Ngay) as `Day` 
                                     from LuuLuong
                                     where Ref_Tram = %1$d
									 and month(Ngay) = %2$d and year(Ngay) = %3$d
                                     group by day(Ngay)'
			, $workstation
			, $month
			, $year);
		return $this->_o_DB->fetchAll($sql);		
	}
	
	public function getAvgLuongMuaByDay($workstation, $month, $year)
	{
		$sql = sprintf('select avg(ifnull(LuongMua,0)) AS `AVG`, day(Ngay) as `Day` 
                                     from LuongMua
                                     where Ref_Tram = %1$d
									 and month(Ngay) = %2$d and year(Ngay) = %3$d
                                     group by day(Ngay)'
			, $workstation
			, $month
			, $year);
		return $this->_o_DB->fetchAll($sql);		
	}
	
	public function getAvgMucNuocByDay($workstation, $month, $year)
	{
		$sql = sprintf('select avg(ifnull(MucNuoc,0)) AS `AVG`, day(Ngay) as `Day` 
                                     from MucNuoc
                                     where Ref_Tram = %1$d
									 and month(Ngay) = %2$d and year(Ngay) = %3$d
                                     group by day(Ngay)'
			, $workstation
			, $month
			, $year);
		return $this->_o_DB->fetchAll($sql);		
	}
}
