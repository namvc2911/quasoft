<?php

/**
 *
 * @author: ThinhTuan
 * @todo: bo ca ham thua, /cost/manufacturing, /cost/group/
 * @todo: gop ba bao cao ve dung may theo ky, nhom, khu vuc
 * @todo: Can them dieu kien cho mot so bao cao ve pbt voi tinh trang pbt la hoan thanh
 * @todo: Sua bao cao m750
 */
class Static_M811Controller extends Qss_Lib_Controller
{
	// property
	protected $_model;  /* Remove */

	public function init()
	{
		$this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
		parent::init();
		$this->headScript($this->params->requests->getBasePath() . '/js/report-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');

		$this->_model     = new Qss_Model_Maintenance_Project();
	}
    /**
     * M811: BÁO CÁO TỔNG HỢP XUẤT NHẬP, TỒN VẬT TƯ CỦA DỰ ÁN VÀ DỊCH VỤ O&M
     */
    public function indexAction()
    {

    }

    public function showAction()
    {
        $projectIOID  = $this->params->requests->getParam('project', 0);
        $startDate    = $this->params->requests->getParam('sdate', '');
        $endDate      = $this->params->requests->getParam('edate', '');

        $this->html->report = $this->_model->getMaterialsByProject($projectIOID, Qss_Lib_Date::displaytomysql($startDate),Qss_Lib_Date::displaytomysql($endDate));
    }

}

?>