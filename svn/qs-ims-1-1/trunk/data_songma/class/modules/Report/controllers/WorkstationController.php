<?php

/**
  @author: Thinh Tuan
 */
class Report_WorkstationController extends Qss_Lib_Controller
{
        public $common;
        public $worksattion;
        private $_params;

        public function init()
        {
                $this->i_SecurityLevel = 15;
                parent::init();
                //$this->headScript($this->params->requests->getBasePath() . '/js/extra/calendar.js');
                $this->common = new Qss_Model_Extra_Common();
                $this->worksattion = new Qss_Model_Extra_Workstation();
                $this->_params = $this->params->requests->getParams();
        }

        public function indexAction()
        {
                
        }

        public function locAction()
        {
                $this->html->workstation = $this->worksattion->getWorkstation();
        }
}
