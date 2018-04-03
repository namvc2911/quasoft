<?php
class Qss_View_Maintenance_Schedule_Content_Month extends Qss_View_Abstract
{

	public function __doExecute ($user,$m,$y,$workcenter,$responseid,$assignid)
	{
		$model = new Qss_Model_Maintenance_Calendar();
		$sonar = new Qss_Model_Calendar_Solar();
		$a = $sonar->adjustDate($m, $y);
		$month = $a[0];
		$year = $a[1];
		$daysInMonth = $sonar->getDaysInMonth($month, $year);
		$startdate = sprintf('%3$s-%2$s-%1$s','01',str_pad($m, 2, '0', STR_PAD_LEFT),$y);
		$enddate = sprintf('%3$s-%2$s-%1$s',$daysInMonth,str_pad($m, 2, '0', STR_PAD_LEFT),$y);
		$this->html->data = $model->getCalendar($user,$startdate,$enddate,$workcenter,$responseid,$assignid);
		$this->html->prev = $sonar->adjustDate($m - 1, $y);
		$this->html->next = $sonar->adjustDate($m + 1, $y);
		$this->html->startdate = $startdate;
		$this->html->enddate = $enddate;
		$this->html->daysInMonth = $daysInMonth;
	}
}
?>