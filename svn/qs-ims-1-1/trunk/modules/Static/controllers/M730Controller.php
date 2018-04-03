<?php

class Static_M730Controller extends Qss_Lib_Controller
{
	// property
	protected $_model;  /* Remove */

	public function init()
	{
		$this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
		parent::init();
		$this->headScript($this->params->requests->getBasePath() . '/js/report-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');

		$this->_model     = new Qss_Model_Maintenance_Workorder();
	}
	public function indexAction()
	{

	}
	public function showAction()
	{
		$start = $this->params->requests->getParam('start');
		$end = $this->params->requests->getParam('end');
		$location = $this->params->requests->getParam('location');
		$group = $this->params->requests->getParam('group');
		$type = $this->params->requests->getParam('type');
		
		$end = Qss_Lib_Extra::getEndDate($start, $end, 'D'); // Ngay ket thuc da duoc gioi han
		$this->html->report = $this->getEquipmentHistoryReportData(
		Qss_Lib_Date::displaytomysql($start), Qss_Lib_Date::displaytomysql($end), //$end,
		$location, $group, $type);
		$this->html->aliasForBType = '';
		$this->html->defaultCurrency = Qss_Lib_Extra::getDefaultCurrency();
		$this->html->start = $start;
		$this->html->end = Qss_Lib_Date::mysqltodisplay($end);

	}
	private function getEquipmentHistoryReportData($start, $end, $locID = 0, $eqGroupID = 0, $eqTypeID = 0)
	{
		$retval = array();
		$mainHistory = $this->_model->getWorkOrderHistory(
		$start, $end, $locID, $eqGroupID, $eqTypeID);

		foreach ($mainHistory as $item)
		{
			if ($item->IOID)
			{
				if (!isset($retval[$item->Ref_MaThietBi]))
				{
					$retval[$item->Ref_MaThietBi]['Code'] = $item->MaThietBi;
					$retval[$item->Ref_MaThietBi]['Name'] = $item->TenThietBi;
					$retval[$item->Ref_MaThietBi]['Group1'] = 0;
					$retval[$item->Ref_MaThietBi]['Total'] = 0;
				}
				$temp = array(
	                'IOID' => $item->IOID
				, 'No' => $item->DocNo
				, 'Date' => $item->DocDate
				, 'Type' => $item->Type
				, 'Class' => $item->Class
				, 'Step' => $item->Step
				, 'Class' => $item->Class
				, 'MType' => $item->LoaiBaoTri
				, 'Priority' => $item->MucDoUuTien
				, 'MaterialCost' => $item->GiaVatTu
				, 'ServiceCost' => $item->GiaDichVu
				, 'EmployeeCost' => $item->GiaNhanCong
				, 'OvertimeCost' => $item->ChiPhiThemGio
				, 'OtherCost' => $item->ChiPhiPhatSinh
				);
				$retval[$item->Ref_MaThietBi]['Detail'][$item->IOID] = $temp;
				$retval[$item->Ref_MaThietBi]['Detail'][$item->IOID]['Group2'] = 0;
				$retval[$item->Ref_MaThietBi]['Group1'] += 1;
				$retval[$item->Ref_MaThietBi]['Total'] +=$item->GiaVatTu;
				$retval[$item->Ref_MaThietBi]['Total'] +=$item->GiaDichVu;
				$retval[$item->Ref_MaThietBi]['Total'] +=$item->GiaNhanCong;
				$retval[$item->Ref_MaThietBi]['Total'] +=$item->ChiPhiPhatSinh;
				$retval[$item->Ref_MaThietBi]['Total'] +=$item->ChiPhiThemGio;
			}
		}
		return $retval;
	}
}

?>