<?php
class Qss_View_Calendar_Content_Day extends Qss_View_Abstract
{

	public function __doExecute ($form,$d,$m,$y,$filter,$owner)
	{
		$sonar = new Qss_Model_Calendar_Solar();
		$this->html->prev = $sonar->adjustDay($d-1,$m,$y);
		$this->html->next = $sonar->adjustDay($d+1,$m,$y);
		$sonar = new Qss_Model_Calendar_Solar();
		$startdate = sprintf('%3$s-%2$s-%1$s',str_pad($d, 2, '0', STR_PAD_LEFT),str_pad($m, 2, '0', STR_PAD_LEFT),$y);
		$enddate = sprintf('%3$s-%2$s-%1$s',str_pad($d, 2, '0', STR_PAD_LEFT),str_pad($m, 2, '0', STR_PAD_LEFT),$y);
		$this->html->data = $form->getCalendar(Qss_Register::get('userinfo'),$startdate,$enddate,$filter,$owner);
		$this->html->configs = $form->get($form->FormCode);
	}
}
?>