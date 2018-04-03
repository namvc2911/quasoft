<?php
class Qss_View_Calendar_Content_Week extends Qss_View_Abstract
{

	public function __doExecute ($form,$w,$m,$y,$filter,$owner)
	{
		$sonar = new Qss_Model_Calendar_Solar();
		$startd = Qss_Lib_Date::getDateByWeek($w,$y);
		$startdate = $startd->format('Y-m-d');
		$enddate = Qss_Lib_Date::add_date($startd,6)->format('Y-m-d');
		
		$this->html->data = $form->getCalendar(Qss_Register::get('userinfo'),$startdate,$enddate,$filter,$owner);
		$this->html->prev = $sonar->adjustWeek($w - 1, $m, $y);
		$this->html->next = $sonar->adjustWeek($w + 1, $m, $y);
		$this->html->startdate1 = $startd;
		$this->html->startdate = $startdate;
		$this->html->enddate = $enddate;
		$this->html->configs = $form->get($form->FormCode);
	}
}
?>