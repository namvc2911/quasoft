<?php
class Qss_View_Event_Select_Month extends Qss_View_Abstract
{

	public function __doExecute ($user,$m,$y)
	{
		$sonar = new Qss_Model_Calendar_Solar();
		//$this->html->data = $form->getCalendar(Qss_Register::get('userinfo'),0,0,$m,$y);
		$this->html->prev = $sonar->adjustDate($m - 1, $y);
		$this->html->next = $sonar->adjustDate($m + 1, $y);
		//$this->html->startdate = Qss_Lib_Date::getDateByWeek($w,$y);
		
	}
}
?>