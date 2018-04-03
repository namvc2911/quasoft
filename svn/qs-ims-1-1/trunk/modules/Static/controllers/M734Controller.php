<?php

/**
 *
 * @author: ThinhTuan
 * @todo: bo ca ham thua, /cost/manufacturing, /cost/group/
 * @todo: gop ba bao cao ve dung may theo ky, nhom, khu vuc
 * @todo: Can them dieu kien cho mot so bao cao ve pbt voi tinh trang pbt la hoan thanh
 * @todo: Sua bao cao m750
 */
class Static_M734Controller extends Qss_Lib_Controller
{
	public function init()
	{
		$this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
		parent::init();
	}
	

	public function indexAction()
	{

	}

	public function showAction()
	{
		$start      = $this->params->requests->getParam('start', '');
		$end        = $this->params->requests->getParam('end', '');
		$material   = $this->params->requests->getParam('material', 0);
		$location   = $this->params->requests->getParam('location', 0);
		$type       = $this->params->requests->getParam('type', 0);
		$group      = $this->params->requests->getParam('group', 0);
		$equip      = $this->params->requests->getParam('equip', 0);
		$mOMaterial = new Qss_Model_Maintenance_Workorder_Material();

        $this->html->maintain        =$mOMaterial->getUsedMaterialsValue(
			Qss_Lib_Date::displaytomysql($start)
			, Qss_Lib_Date::displaytomysql($end)
			, $material
			, $location
			, $type
			, $group
            , $equip
		);
		$this->html->start           = $start;
		$this->html->end             = $end;
		$this->html->defaultCurrency = Qss_Lib_Extra::getDefaultCurrency();
	}
	
}

?>