<?php

//statistics
/**
 *
 * @author ThinhTuan
 *
 */
class Report_ProductionController extends Qss_Lib_Controller {

	/**
	 *
	 * @return unknown_type
	 */
	public $_params;
	public $_common;
	public $_production;
	public $limitByPeriod;

	public function init() {
		//$this->i_SecurityLevel = 15;
		parent::init();
		$this->_params = $this->params->requests->getParams();
		$this->_common = new Qss_Model_Extra_Extra();
		$this->_production = new Qss_Model_Extra_Production();
		$this->limitByPeriod = Qss_Lib_Extra_Const::$DATE_LIMIT;

		$this->headScript($this->params->requests->getBasePath() . '/js/report-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');
		$this->html->curl = $this->params->requests->getRequestUri();
	}

	public function volumeAction() {

	}

	public function volume1Action() {
		$model = new Qss_Model_Extra_Production();
		$this->html->date = $this->_params['date'];
		$date = Qss_Lib_Date::displaytomysql($this->_params['date']);
		$this->html->merge = $model->mergeLinesWithShifts();
		$this->html->volumes = $this->groupItemForVolume($model->getProductionVolume($date, 1));
		$this->html->materials = $this->groupItemForVolume($model->getProductionVolume($date, 2));
	}

	public function paretoAction() {
		$model = new Qss_Model_Extra_Production();
		$this->html->items = $this->_common->getDataset(array('module'=>'OSanPham', 'where'=>'SanXuat = 1', 'order'=>'MaSanPham'));
		//$model->getProductionItems();
	}

	public function pareto1Action() {
		$model = new Qss_Model_Extra_Production();
		$params = $this->params->requests->getParams();
		$limit = array('D' => 100); // Limit 100 day
		$start = Qss_Lib_Date::displaytomysql($params['start']);
		$end = Qss_Lib_Date::displaytomysql($params['end']);
		$end = Qss_Lib_Extra::getEndDate($start, $end, 'D', $limit);
		$reason = $model->getDefectItemsGroupByReason($start, $end, $params['item']);
		$type = $model->getDefectItemsGroupByType($start, $end, $params['item']);

		$this->html->typeLabel = $this->_common->getDataset(array('module'=>'OLoi'));//$model->getAllDefectType();
		$this->html->reasonLabel = $this->_common->getDataset(array('module'=>'ONguyenNhanLoi'));//$model->getAllDefectReason();
		$this->html->type = $type;
		$this->html->reason = $reason;
		$this->html->start = $params['start'];
		$this->html->end = date('d-m-Y', strtotime($end));
	}

	public function statisticsAction() {
		//	$this->v_fCheckRightsOnForm(155);
		$model = new Qss_Model_Extra_Warehouse();
		$items = $model->getAllItems(0, array('SanXuat' => 1));
		$this->html->items = $this->groupItemByGroups($items);
		$this->html->limit = $this->limitByPeriod;
	}

	public function statistics1Action() {

		$statistics   = array();
		$i            = 0;
		$old          = '';
		$start        = Qss_Lib_Date::displaytomysql($this->_params['start']);
		$end          = Qss_Lib_Date::displaytomysql($this->_params['end']);
		$end          = Qss_Lib_Extra::getEndDate($start, $end, $this->_params['period'], $this->limitByPeriod);
		$output       = $this->_production->getOutputStatistics($start, $end, $this->_params['period']
		, $this->_params['items'], $this->_params['groupby']);
		$defaceType   = $this->_production->getDefaceOutputStatistics($start, $end, $this->_params['period']
		, $this->_params['items'], $this->_params['groupby'], 1);
		$defaceReason = $this->_production->getDefaceOutputStatistics($start, $end, $this->_params['period']
		, $this->_params['items'], $this->_params['groupby'], 2);
		$displayTime  = Qss_Lib_Extra::displayRangeDate($start, $end, $this->_params['period']);


		if ($this->_params['groupby'] == 'item') {
			foreach ($output as $val) {
				$code = $this->getArrayCodeByPeriod($this->_params['period'], $val->Ngay, $val->WeekOf
				, $val->MonthOf, $val->QuarterOf, $val->YearOf);
				if ($val->Ref_MaSP != $old) {
					$statistics[$val->Ref_MaSP]['Group'] = "{$val->MaSP} - {$val->TenSP}";
				}

				$statistics[$val->Ref_MaSP]['Params'][$code]['Time'] = $displayTime[$code]['FullDisplay'];
				$statistics[$val->Ref_MaSP]['Params'][$code]['ChartTime'] = $displayTime[$code]['Display'];
				$statistics[$val->Ref_MaSP]['Params'][$code]['ItemCode'] = $val->MaSP;
				$statistics[$val->Ref_MaSP]['Params'][$code]['ItemName'] = $val->TenSP;
				$statistics[$val->Ref_MaSP]['Params'][$code]['TotalQty'] = $val->TongSo;
				$statistics[$val->Ref_MaSP]['Params'][$code]['Qty'] = $val->SoLuong;
				$statistics[$val->Ref_MaSP]['Params'][$code]['DefaceQty'] = $val->SoLuongLoi;
				$statistics[$val->Ref_MaSP]['Params'][$code]['TimeCode'] = $code;
				$old = $val->Ref_MaSP;
				$i++;
			}


			foreach ($defaceType as $dt) {
				$code = $this->getArrayCodeByPeriod($this->_params['period'], $dt->Ngay, $dt->WeekOf
				, $dt->MonthOf, $dt->QuarterOf, $dt->YearOf);
				$statistics[$dt->Ref_MaSP]['Params'][$code]['DefaceType'][$dt->Ref_MaLoi]['Code'] = $dt->MaLoi;
				$statistics[$dt->Ref_MaSP]['Params'][$code]['DefaceType'][$dt->Ref_MaLoi]['Qty'] = $dt->SoLuongLoi;
			}

			foreach ($defaceReason as $ds) {
				$code = $this->getArrayCodeByPeriod($this->_params['period'], $ds->Ngay, $ds->WeekOf
				, $ds->MonthOf, $ds->QuarterOf, $ds->YearOf);

				$statistics[$ds->Ref_MaSP]['Params'][$code]['DefaceReason'][$ds->Ref_NguyenNhan]['Code'] = $ds->NguyenNhan;
				$statistics[$ds->Ref_MaSP]['Params'][$code]['DefaceReason'][$ds->Ref_NguyenNhan]['Qty'] = $ds->SoLuongLoi;
			}
		} elseif ($this->_params['groupby'] == 'time') {
			foreach ($output as $val) {
				$code = $this->getArrayCodeByPeriod($this->_params['period'], $val->Ngay, $val->WeekOf
				, $val->MonthOf, $val->QuarterOf, $val->YearOf);
				if ($val->Ref_MaSP != $old) {
					$statistics[$code]['Group'] = $displayTime[$code]['FullDisplay'];
				}

				$statistics[$code]['Params'][$val->Ref_MaSP]['Time'] = $displayTime[$code]['FullDisplay'];
				$statistics[$code]['Params'][$val->Ref_MaSP]['ChartTime'] = $displayTime[$code]['Display'];
				$statistics[$code]['Params'][$val->Ref_MaSP]['ItemCode'] = $val->MaSP;
				$statistics[$code]['Params'][$val->Ref_MaSP]['ItemName'] = $val->TenSP;
				$statistics[$code]['Params'][$val->Ref_MaSP]['TotalQty'] = $val->TongSo;
				$statistics[$code]['Params'][$val->Ref_MaSP]['Qty'] = $val->SoLuong;
				$statistics[$code]['Params'][$val->Ref_MaSP]['DefaceQty'] = $val->TongSo - $val->SoLuong;
				$statistics[$code]['Params'][$val->Ref_MaSP]['TimeCode'] = $code;
				$old = $code;
				$i++;
			}

			foreach ($defaceType as $dt) {
				$code = $this->getArrayCodeByPeriod($this->_params['period'], $dt->Ngay, $dt->WeekOf
				, $dt->MonthOf, $dt->QuarterOf, $dt->YearOf);
				$statistics[$code]['Params'][$dt->Ref_MaSP]['DefaceType'][$dt->Ref_MaLoi]['Code'] = $dt->MaLoi;
				$statistics[$code]['Params'][$dt->Ref_MaSP]['DefaceType'][$dt->Ref_MaLoi]['Qty'] = $dt->SoLuongLoi;
			}

			foreach ($defaceReason as $ds) {
				$code = $this->getArrayCodeByPeriod($this->_params['period'], $ds->Ngay, $ds->WeekOf
				, $ds->MonthOf, $ds->QuarterOf, $ds->YearOf);
				$statistics[$code]['Params'][$ds->Ref_MaSP]['DefaceReason'][$ds->Ref_NguyenNhan]['Code'] = $ds->NguyenNhan;
				$statistics[$code]['Params'][$ds->Ref_MaSP]['DefaceReason'][$ds->Ref_NguyenNhan]['Qty'] = $ds->SoLuongLoi;
			}
		}


		$this->html->type = $defaceType;
		$this->html->reason = $defaceReason;
		$this->html->typeLabel = $this->_common->getDataset(array('module'=>'OLoi'));//$this->_production->getAllDefectType();
		$this->html->reasonLabel = $this->_common->getDataset(array('module'=>'ONguyenNhanLoi'));//$model->getAllDefectReason();
		$this->html->startDate = $this->_params['start'];
		$this->html->endDate = date('d-m-Y', strtotime($end));
		$this->html->report = $statistics;
		$this->html->groupby = $this->_params['groupby'];
		$this->html->time = $displayTime;
		$this->html->items = $this->_params['items'];
	}

	public function costPlanAction() {
		$this->html->requireOrder = $this->_common->getTable(array('*'), 'OTinhGiaThanh'
		, ' IFID_M761 is not null ', array('IOID DESC', 'Code ASC'), 'NO_LIMIT');
	}

	public function costPlan1Action() {

		$productCosts = $this->_common->getTable(array('*'), 'OGiaThanhSanXuat'
		, array('IFID_M761' => $this->_params['ro'])
		, array('Ref_MaSanPham', 'Ref_CongDoan', 'IOID')
		, 'NO_LIMIT'
		);
		$this->html->defaultCurrency = $this->_common->getDefaultCurrency('VND');
		$this->html->report = $this->getPlanCostArr($productCosts);
	}

	public function costProductAction() {
		$this->html->requireOrder = $this->_common->getTable(array('*'), 'OTinhGiaThanh'
		, ' IFID_M713 is not null ', array('IOID DESC', 'Code ASC'), 'NO_LIMIT');
	}

	public function costProduct1Action() {
		$productCosts = $this->_production->getProductionCosts($this->_params['ro']);
		$this->html->report = $this->getProductionCostArr($productCosts);
		$this->html->defaultCurrency = $this->_common->getDefaultCurrency('VND');
	}

	/** private function */

	/**
	 *
	 * @param object $productCosts
	 * @return ordered array for cost plan report
	 */

	// Xử lý riêng cho volume
	private function groupItemForVolume($itemArr) {
		$retval = array();
		$keeper = array();
		$i = 0;

		foreach ($itemArr as $item) {
			$code = "{$item->LineID}0{$item->ShiftID}0{$item->RefItem}0{$item->RefAttribute}";

			if (!key_exists($code, $keeper)) {
				$retval[$i]['ItemCode'] = $item->ItemCode;
				$retval[$i]['ItemName'] = $item->ItemName;
				$retval[$i]['Attribute'] = $item->Attribute;
				$retval[$i]['UOM'] = $item->UOM;
				$retval[$i]['Qty'] = $item->Qty;
				$retval[$i]['Key'] = "{$item->LineID}-{$item->ShiftID}";
				$keeper[$code] = $i;
				$i++;
			} else {
				$retval[$keeper[$code]]['Qty'] = $item->Qty;
			}
		}
		return $retval;
	}

	private function getPlanCostArr($productCosts) {
		$retval = array();
		foreach ($productCosts as $item) {
			$key = $item->Ref_MaSanPham . '-' . @(int) $item->Ref_ThuocTinh;
			if (isset($retval[$key]['Detail'][$item->Ref_CongDoan])) {
				$retval[$key]['Detail'][$item->Ref_CongDoan]['Qty'] += $item->SoLuong;
				$retval[$key]['Detail'][$item->Ref_CongDoan]['Consumption'] += $item->CPNVL;
				$retval[$key]['Detail'][$item->Ref_CongDoan]['Employee'] += $item->CPNhanCong;
				$retval[$key]['Detail'][$item->Ref_CongDoan]['Machine'] += $item->CPMayMoc;
				$retval[$key]['Detail'][$item->Ref_CongDoan]['Indirect'] += $item->CPGianTiep;
				$retval[$key]['Detail'][$item->Ref_CongDoan]['Total'] += $item->GiaThanh;
			} else {
				$retval[$key]['ItemCode'] = $item->MaSanPham;
				$retval[$key]['ItemName'] = $item->TenSanPham;
				$retval[$key]['Attribute'] = $item->ThuocTinh;
				$retval[$key]['UOM'] = $item->DonViTinh;
				$retval[$key]['Detail'][$item->Ref_CongDoan]['Operator'] = $item->CongDoan;
				$retval[$key]['Detail'][$item->Ref_CongDoan]['Qty'] = $item->SoLuong;
				$retval[$key]['Detail'][$item->Ref_CongDoan]['Consumption'] = $item->CPNVL;
				$retval[$key]['Detail'][$item->Ref_CongDoan]['Employee'] = $item->CPNhanCong;
				$retval[$key]['Detail'][$item->Ref_CongDoan]['Machine'] = $item->CPMayMoc;
				$retval[$key]['Detail'][$item->Ref_CongDoan]['Indirect'] = $item->CPGianTiep;
				$retval[$key]['Detail'][$item->Ref_CongDoan]['Unit'] = $item->GiaThanhDonVi;
				$retval[$key]['Detail'][$item->Ref_CongDoan]['Total'] = $item->GiaThanh;
			}
		}
		return $retval;
	}

	private function getProductionCostArr($productCosts) {
		$retval = array();

		foreach ($productCosts as $item) {
			$key = $item->RealRefItem . '-' . @(int) $item->RealRefAttribute;
			if (isset($retval[$key]['Detail'][$item->RealRefOperator])) {
				$retval[$key]['Detail'][$item->RealRefOperator]['RealQty'] += $item->RealQty;
				$retval[$key]['Detail'][$item->RealRefOperator]['RealConsumption'] += $item->RealConsumption;
				$retval[$key]['Detail'][$item->RealRefOperator]['RealEmployee'] += $item->RealEmployee;
				$retval[$key]['Detail'][$item->RealRefOperator]['RealMachine'] += $item->RealMachine;
				$retval[$key]['Detail'][$item->RealRefOperator]['RealIndirect'] += $item->RealIndirect;
				$retval[$key]['Detail'][$item->RealRefOperator]['RealTotal'] += $item->RealTotal;

				$retval[$key]['Detail'][$item->RealRefOperator]['PlanQty'] += $item->PlanQty;
				$retval[$key]['Detail'][$item->RealRefOperator]['PlanConsumption'] += $item->PlanConsumption;
				$retval[$key]['Detail'][$item->RealRefOperator]['PlanEmployee'] += $item->PlanEmployee;
				$retval[$key]['Detail'][$item->RealRefOperator]['PlanMachine'] += $item->PlanMachine;
				$retval[$key]['Detail'][$item->RealRefOperator]['PlanIndirect'] += $item->PlanIndirect;
				$retval[$key]['Detail'][$item->RealRefOperator]['PlanTotal'] += $item->PlanTotal;
			} else {
				$retval[$key]['RealItemCode'] = $item->RealItemCode;
				$retval[$key]['RealItemName'] = $item->RealItemName;
				$retval[$key]['RealAttribute'] = $item->RealAttribute;
				$retval[$key]['RealUOM'] = $item->RealUOM;
				$retval[$key]['Detail'][$item->RealRefOperator]['RealOperator'] = $item->RealOperator;
				$retval[$key]['Detail'][$item->RealRefOperator]['RealQty'] = $item->RealQty;
				$retval[$key]['Detail'][$item->RealRefOperator]['RealConsumption'] = $item->RealConsumption;
				$retval[$key]['Detail'][$item->RealRefOperator]['RealEmployee'] = $item->RealEmployee;
				$retval[$key]['Detail'][$item->RealRefOperator]['RealMachine'] = $item->RealMachine;
				$retval[$key]['Detail'][$item->RealRefOperator]['RealIndirect'] = $item->RealIndirect;
				$retval[$key]['Detail'][$item->RealRefOperator]['RealUnit'] = $item->RealUnit;
				$retval[$key]['Detail'][$item->RealRefOperator]['RealTotal'] = $item->RealTotal;


				$retval[$key]['PlanItemCode'] = $item->PlanItemCode;
				$retval[$key]['PlanItemName'] = $item->PlanItemName;
				$retval[$key]['PlanAttribute'] = $item->PlanAttribute;
				$retval[$key]['PlanUOM'] = $item->PlanUOM;
				$retval[$key]['Detail'][$item->RealRefOperator]['PlanOperator'] = $item->PlanOperator;
				$retval[$key]['Detail'][$item->RealRefOperator]['PlanQty'] = $item->PlanQty;
				$retval[$key]['Detail'][$item->RealRefOperator]['PlanConsumption'] = $item->PlanConsumption;
				$retval[$key]['Detail'][$item->RealRefOperator]['PlanEmployee'] = $item->PlanEmployee;
				$retval[$key]['Detail'][$item->RealRefOperator]['PlanMachine'] = $item->PlanMachine;
				$retval[$key]['Detail'][$item->RealRefOperator]['PlanIndirect'] = $item->PlanIndirect;
				$retval[$key]['Detail'][$item->RealRefOperator]['PlanUnit'] = $item->PlanUnit;
				$retval[$key]['Detail'][$item->RealRefOperator]['PlanTotal'] = $item->PlanTotal;
			}
		}
		return $retval;
	}

	private function groupItemByGroups($items) {
		$oldGroup = '';
		$retItems = array();
		$i = 0;

		foreach ($items as $it) {
			if ($oldGroup != $it->Ref_NhomSanPham) {
				$retItems[$it->Ref_NhomSanPham]['TenNhom'] = $it->NhomSanPham;
				$retItems[$it->Ref_NhomSanPham]['RefNhom'] = $it->Ref_NhomSanPham ? $it->Ref_NhomSanPham : 0;
			}
			$retItems[$it->Ref_NhomSanPham]['SanPham'][$i]['Ref'] = $it->IOID;
			$retItems[$it->Ref_NhomSanPham]['SanPham'][$i]['Code'] = $it->MaSanPham;
			$retItems[$it->Ref_NhomSanPham]['SanPham'][$i]['Name'] = $it->TenSanPham;
			$i++;
		}
		return $retItems;
	}

	private function getArrayCodeByPeriod($period, $dateColumn, $weekColumn, $monthColumn, $quarterColumn, $yearColumn) {
		switch ($period) {
			case 'D':
				$code = $dateColumn;
				break;
			case 'W':
				$code = (int) ($weekColumn + 1) . '.' . (int) $yearColumn;
				break;
			case 'M':
				$code = (int) $monthColumn . '.' . (int) $yearColumn;
				break;
			case 'Q':
				$code = (int) $quarterColumn . '.' . (int) $yearColumn;
				break;
			case 'Y':
				$code = (int) $yearColumn;
				break;
		}
		return $code;
	}

	private function getStatisticsArray() {

	}

}

?>