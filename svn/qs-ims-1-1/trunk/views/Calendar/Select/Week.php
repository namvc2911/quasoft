<?php
class Qss_View_Calendar_Select_Week extends Qss_View_Abstract
{

	public function __doExecute ($form,$w,$m,$y)
	{
		$sonar = new Qss_Model_Calendar_Solar();

		$a = $sonar->adjustDate($m, $y);
		$month = $a[0];
		$year = $a[1];
		$daysInMonth=$sonar->getDaysInMonth($month, $year);
		$this->html->daysInMonth = $daysInMonth;
		$this->html->prev = $sonar->adjustDate($month - 1, $year);
		$this->html->next = $sonar->adjustDate($month + 1, $year);
		$this->html->sonar = 	$sonar;
		$lunar = new Qss_Model_Calendar_Lunar();
		$this->html->lunarMonth = $lunar->getMonth($m,$y);
		$this->html->today = getdate();
		$this->html->prev = $sonar->adjustWeek($w - 1, $m, $year);
		$this->html->next = $sonar->adjustWeek($w + 1, $m, $year);
		$this->html->lVacation = array();
		
	}
}
?>