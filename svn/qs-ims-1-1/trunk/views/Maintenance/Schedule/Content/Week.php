<?php
class Qss_View_Maintenance_Schedule_Content_Week extends Qss_View_Abstract
{

	public function __doExecute ($user,$w,$m,$y,$workcenter,$responseid,$assignid)
	{
		$model = new Qss_Model_Maintenance_Calendar();
		$sonar = new Qss_Model_Calendar_Solar();
		$startd = Qss_Lib_Date::getDateByWeek($w,$y);
		$startdate = $startd->format('Y-m-d');
		$enddate = Qss_Lib_Date::add_date($startd,6)->format('Y-m-d');
		
		$this->html->data = $model->getCalendar($user,$startdate,$enddate,$workcenter,$responseid,$assignid);
		$this->html->prev = $sonar->adjustWeek($w - 1, $m, $y);
		$this->html->next = $sonar->adjustWeek($w + 1, $m, $y);
		$this->html->startdate1 = $startd;
		$this->html->startdate = $startdate;
		$this->html->enddate = $enddate;
	}
}
?>