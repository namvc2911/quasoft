<?php

/**
 *
 * @author: ThinhTuan
 * @todo: bo ca ham thua, /cost/manufacturing, /cost/group/
 * @todo: gop ba bao cao ve dung may theo ky, nhom, khu vuc
 * @todo: Can them dieu kien cho mot so bao cao ve pbt voi tinh trang pbt la hoan thanh
 * @todo: Sua bao cao m750
 */
class Static_M777Controller extends Qss_Lib_Controller
{
	// property
	protected $_model;  /* Remove */

	public function init()
	{
		$this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
		parent::init();
		$this->headScript($this->params->requests->getBasePath() . '/js/report-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');

		$this->_model     = new Qss_Model_Maintenance_Equip_Operation();
	}
	/**
	 * M777 - Ma trận thiết bị phụ tùng
	 * Ox: Mã thiết bị
	 * Oy: Vật tư, phụ tùng
	 */
	public function indexAction()
	{

	}

	public function showAction()
	{
		header("Content-type: application/vnd.ms-excel");
		header("Content-disposition: attachment; filename=\"report.xls\"");

		$location = $this->params->requests->getParam('location');
		$model    = new Qss_Model_Extra_Excel();
		$model->genEquipmentMatrix($location);
		die();

		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
}

?>