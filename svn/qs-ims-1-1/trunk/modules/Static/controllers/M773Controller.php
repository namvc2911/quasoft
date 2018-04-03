<?php

/**
 *
 * @author: ThinhTuan
 * @todo: bo ca ham thua, /cost/manufacturing, /cost/group/
 * @todo: gop ba bao cao ve dung may theo ky, nhom, khu vuc
 * @todo: Can them dieu kien cho mot so bao cao ve pbt voi tinh trang pbt la hoan thanh
 * @todo: Sua bao cao m750
 */
class Static_M773Controller extends Qss_Lib_Controller
{
	// property
	protected $_model;  /* Remove */
	protected $_params; /* Remove */
	protected $_common; /* Remove */

	public function init()
	{
		$this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
		parent::init();

		/* @todo: Remove headScript */
		$this->headScript($this->params->requests->getBasePath() . '/js/report-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');

		/* @todo: Remove $_model, $_params, $_common */
		$this->_params    = $this->params->requests->getParams();
		$this->_common    = new Qss_Model_Extra_Extra();
		$this->_model     = new Qss_Model_Extra_Maintenance();

		/* @todo: Remove curl */
		//$this->html->curl = $this->params->requests->getRequestUri();
	}


	public function indexAction()
	{

	}

	public function showAction()
	{
		$start       = $this->params->requests->getParam('start', '');
		$end         = $this->params->requests->getParam('end', '');
		$eqTypeIFID  = $this->params->requests->getParam('type', 0);
		// Chi so thiet bi (trong loai thiet bi)

		$startMysql  = Qss_Lib_Date::displaytomysql($start);
		$endMysql    = Qss_Lib_Date::displaytomysql($end);
		$maintModel  = new Qss_Model_Extra_Maintenance();
		$sumRealQty  = $maintModel->getTotalQtyDailyRecordData($startMysql , $endMysql, $eqTypeIFID);

		$tempDRIFID  = array();
		$return      = array();

		/*foreach($sumRealQty as $item)
		{
			$code = $item->EQIOID.'.'.$item->RefParam;
			$return[$code]['Code']     = $item->MaThietBi;
			$return[$code]['Type']     = $item->LoaiThietBi;
			$return[$code]['Name']     = $item->TenThietBi;
			$return[$code]['Group']    = $item->NhomThietBi;
			$return[$code]['Param']    = $item->ChiSo;
			$return[$code]['TotalQty']      = $item->TotalQty;
			$return[$code]['PlanTotalQty']  = $item->PlanTotalQty;
			$tempDRIFID[]              = $item->DRIFID;
		}

		foreach ($return as $key=>$item)
		{
			$return[$key]['Performance'] = (@(double)$item['PlanTotalQty'] && @(double)$item['Amount'])?
			(@(double)$item['Qty']/(@(double)$item['PlanQty']* @(double)$item['Amount']) )*100:0;
			$return[$key]['Capacity']    =
			(@(double)$item['Time'] && @(double)$item['PlanTime'])?
			((@(double)$item['Qty']/@(double)$item['Time'])/(@(double)$item['PlanQty']/@(double)$item['PlanTime']))*100:0;
		}*/
		$this->html->print = $sumRealQty;
	}
}

?>