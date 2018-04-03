<?php
class Qss_View_Calendar_Content_Month extends Qss_View_Abstract
{

	public function __doExecute ($form,$m,$y,$filter,$owner)
	{
		$sonar = new Qss_Model_Calendar_Solar();
		$a = $sonar->adjustDate($m, $y);
		$month = $a[0];
		$year = $a[1];
		$daysInMonth = $sonar->getDaysInMonth($month, $year);
		$startdate = sprintf('%3$s-%2$s-%1$s','01',str_pad($m, 2, '0', STR_PAD_LEFT),$y);
		$enddate = sprintf('%3$s-%2$s-%1$s',$daysInMonth,str_pad($m, 2, '0', STR_PAD_LEFT),$y);
		$this->html->data = $form->getCalendar(Qss_Register::get('userinfo'),$startdate,$enddate,$filter,$owner);
		$this->html->prev = $sonar->adjustDate($m - 1, $y);
		$this->html->next = $sonar->adjustDate($m + 1, $y);
		$this->html->startdate = $startdate;
		$this->html->enddate = $enddate;
		$this->html->daysInMonth = $daysInMonth;
		$this->html->configs = $form->get($form->FormCode);
	}
}
?>