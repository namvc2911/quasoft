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
	
	public function getMucNuocByWorksationGroupByYear($workstation)
	{
		$sql = sprintf('select *, year(Ngay) as `Year`
                                     from MucNuoc
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
		$where = '';
		if($month)
		{
			$where .= sprintf(' and month(Ngay) = %1$d',$month);
		}
		if($year)
		{
			$where .= sprintf(' and year(Ngay) = %1$d',$year);
		}
		$sql = sprintf('select avg(ifnull(LuuLuong,0)) AS `AVG`, Ngay as `Day` 
                                     from LuuLuong
                                     where Ref_Tram = %1$d
									 %2$s
                                     group by day(Ngay)
                                     order by Ngay'
			, $workstation
			, $where);
		return $this->_o_DB->fetchAll($sql);		
	}
	
	public function getAvgLuongMuaByDay($workstation, $month, $year)
	{
		$where = '';
		if($month)
		{
			$where .= sprintf(' and month(Ngay) = %1$d',$month);
		}
		if($year)
		{
			$where .= sprintf(' and year(Ngay) = %1$d',$year);
		}
		$sql = sprintf('select avg(ifnull(LuongMua,0)) AS `AVG`, Ngay as `Day` 
                                     from LuongMua
                                     where Ref_Tram = %1$d									
									 %2$s
                                     group by Ngay
                                     order by Ngay'
			, $workstation
			, $where);
		//echo $sql;die;
		return $this->_o_DB->fetchAll($sql);		
	}
	
	public function getAvgMucNuocByDay($workstation, $month, $year)
	{
		$where = '';
		if($month)
		{
			$where .= sprintf(' and month(Ngay) = %1$d',$month);
		}
		if($year)
		{
			$where .= sprintf(' and year(Ngay) = %1$d',$year);
		}
		$sql = sprintf('select avg(ifnull(MucNuoc,0)) AS `AVG`, Ngay as `Day` 
                                     from MucNuoc
                                     where Ref_Tram = %1$d
									 %2$s
                                     group by day(Ngay)
                                     order by Ngay'
			, $workstation
			, $where);
		return $this->_o_DB->fetchAll($sql);		
	}
	public function getLuongMuaByRange($start, $end)
	{
		$sql = sprintf('select LuongMua as Data, concat(Ngay," ",ifnull(Gio,"00:00:00")) as Ngay ,Tram,Ref_Tram
                                     from LuongMua
                                     where Ngay between %1$s and %2$s
                                     order by Ref_Tram,Ngay
                                     limit 10000'
			, $this->_o_DB->quote($start)
			, $this->_o_DB->quote($end));
		return $this->_o_DB->fetchAll($sql);		
	}
	public function getLuuLuongByRange($start, $end)
	{
		$sql = sprintf('select LuuLuong as Data, concat(Ngay," ",ifnull(Gio,"00:00:00")) as Ngay ,Tram,Ref_Tram
                                     from LuuLuong
                                     where Ngay between %1$s and %2$s
                                     order by Ref_Tram,Ngay
                                     limit 10000'
			, $this->_o_DB->quote($start)
			, $this->_o_DB->quote($end));
		return $this->_o_DB->fetchAll($sql);		
	}
	public function getMucNuocByRange($start, $end)
	{
		$sql = sprintf('select MucNuoc as Data, concat(Ngay," ",ifnull(Gio,"00:00:00")) as Ngay,Tram,Ref_Tram 
                                     from MucNuoc
                                     where Ngay between %1$s and %2$s
                                     order by Ref_Tram,Ngay
                                     limit 10000'
			, $this->_o_DB->quote($start)
			, $this->_o_DB->quote($end));
		return $this->_o_DB->fetchAll($sql);		
	}
}
