<?php

/**
 *
 * @author: ThinhTuan
 * @todo: bo ca ham thua, /cost/manufacturing, /cost/group/
 * @todo: gop ba bao cao ve dung may theo ky, nhom, khu vuc
 * @todo: Can them dieu kien cho mot so bao cao ve pbt voi tinh trang pbt la hoan thanh
 * @todo: Sua bao cao m750
 */
class Static_M798Controller extends Qss_Lib_Controller
{
	// property
	protected $_model;  /* Remove */

	public function init()
	{
		$this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
		parent::init();

		/* @todo: Remove headScript */
		$this->headScript($this->params->requests->getBasePath() . '/js/report-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');

		/* @todo: Remove $_model, $_params, $_common */
		$this->_model     = new Qss_Model_Maintenance_Equip_Operation();

		/* @todo: Remove curl */
		//$this->html->curl = $this->params->requests->getRequestUri();
	}
	

/**
     * Mẫu in xác nhận giờ chạy máy
     */
    public function indexAction()
    {

    }

    public function showAction()
    {
        $start        = $this->params->requests->getParam('start_date', '');
        $end          = $this->params->requests->getParam('end_date', '');
        $locationIOID = $this->params->requests->getParam('location_ioid', 0);
        $eqGroupIOID  = $this->params->requests->getParam('eq_group_ioid', 0);
        $eqTypeIOID   = $this->params->requests->getParam('eq_type_ioid', 0);
        $retval       = array();
       
        $this->html->report = $this->_model->getWorkingNumberByRange(Qss_Lib_Date::displaytomysql($start)
        			,Qss_Lib_Date::displaytomysql($end)
        			,$locationIOID
        			,$eqTypeIOID
        			,$eqGroupIOID);
    }
}

?>