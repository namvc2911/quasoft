<?php
/**
 * Class Static_M405Controller
 * Xử lý kế hoạch mua sắm
 * Purchase request process
 */
class Static_M787Controller extends Qss_Lib_Controller
{  
	// Model
	public $_model;

   public function init()
   {
        parent::init();
        // Model
		$this->_model  = new Qss_Model_Maintenance_Equip_Operation();

		// Load script (Co ve khong hoat dong)
		$this->headScript($this->params->requests->getBasePath() . '/js/common.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/tabs.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/ajaxfileupload.js');
        
   }
    public function indexAction()
    {
		$this->html->data = $this->_model->getMeasurePoints();
		//echo '<pre>';
		//print_r($this->html->data);die;
    }
	
}