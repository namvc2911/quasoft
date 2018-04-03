<?php
/**
 *
 * @author HuyBD
 *
 */
class Static_M021Controller extends Qss_Lib_Controller
{

	/**
	 *
	 * @return unknown_type
	 */
	public function init ()
	{
		$this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/wide.php';
		parent::init();
		$this->headScript($this->params->requests->getBasePath() . '/js/admin-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/jquery.bgiframe.min.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/ajaxfileupload.js');
	}

	/**
	 *
	 * @return void
	 */
	public function indexAction ()
	{
		$userinfo = $this->params->registers->get('userinfo');
		$fid = $this->params->requests->getParam('fid',0);
		$this->html->fid = $fid;
		$model = new Qss_Model_Report();
		$this->html->forms = $model->getReportForm($this->_user);
	}
	public function reloadAction ()
	{
		$fid = $this->params->requests->getParam('fid',0);
		$model = new Qss_Model_Dashboad();
		$designs = $model->getReportsByForm($this->_user,$fid);

        $valArrary = array('FormCode'=>'FormCode', 'FName'=>'FName', 'Name'=>'Name', 'Params'=>'Params', 'Active'=>'Active', 'Mobile'=>'Mobile');

		echo $this->views->Common->List($designs, 'URID', $valArrary);
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

}
?>