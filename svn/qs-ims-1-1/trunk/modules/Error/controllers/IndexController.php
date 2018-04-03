<?php
/**
 b
 */
class Error_IndexController extends Qss_Lib_Controller
{
	public function init ()
	{
		$this->i_SecurityLevel = 15;
		parent::init();
		//$this->headScript($this->params->requests->getBasePath() . '/js/extra/calendar.js');
	}
	public function indexAction()
	{

	}
}