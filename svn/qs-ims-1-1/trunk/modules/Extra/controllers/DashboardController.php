<?php

class Extra_DashboardController extends Qss_Lib_Controller {

	public function init()
	{
		parent::init();
		// Model
		$this->_common = new Qss_Model_Extra_Extra();


		// Load script (Co ve khong hoat dong)
		$this->headScript($this->params->requests->getBasePath() . '/js/common.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/tabs.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/ajaxfileupload.js');

	}
}