<?php
class Qss_View_Event_Content_Year extends Qss_View_Abstract
{

	public function __doExecute ($user,$y,$filter,$owner)
	{	
		$model = new Qss_Model_Event();
		$sonar = new Qss_Model_Calendar_Solar();
		$daysInMonth = $sonar->getDaysInMonth(12, $y);
		$startdate = sprintf('%3$s-%2$s-%1$s','01','01',$y);
		$enddate = sprintf('%3$s-%2$s-%1$s',$daysInMonth,12,$y);
		$this->html->data = $model->getCalendar($user,$startdate,$enddate,$filter,$owner);
		$this->html->startdate = $startdate;
		$this->html->enddate = $enddate;
	}
}
?>