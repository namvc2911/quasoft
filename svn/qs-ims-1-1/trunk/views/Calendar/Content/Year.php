<?php
class Qss_View_Calendar_Content_Year extends Qss_View_Abstract
{

	public function __doExecute ($form,$y,$filter,$owner)
	{	
		$sonar = new Qss_Model_Calendar_Solar();
		$daysInMonth = $sonar->getDaysInMonth(12, $y);
		$startdate = sprintf('%3$s-%2$s-%1$s','01','01',$y);
		$enddate = sprintf('%3$s-%2$s-%1$s',$daysInMonth,12,$y);
		$this->html->data = $form->getCalendar(Qss_Register::get('userinfo'),$startdate,$enddate,$filter,$owner);
		$this->html->startdate = $startdate;
		$this->html->enddate = $enddate;
		$this->html->configs = $form->get($form->FormCode);
	}
}
?>