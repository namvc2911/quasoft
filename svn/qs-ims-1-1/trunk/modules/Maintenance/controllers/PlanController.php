<?php
/**
 *
 * @author HuyBD
 *
 */
class Maintenance_PlanController extends Qss_Lib_Controller
{
	protected $_model;
	/**
	 *
	 * @return unknown_type
	 */
	public function init()
	{
		parent::init();
	}

	/**
	 * Nhân đôi kế hoạch bảo trì (Extra Button: Nhân đôi kế hoạch - M724)
	 */
	public function duplicateIndexAction()
	{
		$ifid       = $this->params->requests->getParam('ifid', 0);
		$equipModel = new Qss_Model_Maintenance_Equipment();
		$equips     = $equipModel->getEquipsByEquipTypeOfEquipOfPlan($ifid);

		$this->html->ifid   = $ifid;
		$this->html->equips = $equips;
	}

	public function duplicateSaveAction()
	{
		$params = $this->params->requests->getParams();
		if ($this->params->requests->isAjax())
		{
			$service = $this->services->Extra->Maintenance->Plan->Duplicate($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
}
?>