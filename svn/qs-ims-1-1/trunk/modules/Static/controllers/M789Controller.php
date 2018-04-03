<?php

/**
 *
 * @author: ThinhTuan
 * @todo: bo ca ham thua, /cost/manufacturing, /cost/group/
 * @todo: gop ba bao cao ve dung may theo ky, nhom, khu vuc
 * @todo: Can them dieu kien cho mot so bao cao ve pbt voi tinh trang pbt la hoan thanh
 * @todo: Sua bao cao m750
 */
class Static_M789Controller extends Qss_Lib_Controller
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
    /**
	 * @module Bao cao tong hop theo doi tuong
	 * @path report/maintenance/cost/object
	 */
	public function indexAction()
	{
		$common = new Qss_Model_Extra_Extra();
        $eqs         = $common->getTable(array('*'), 'ODanhSachThietBi', array(), array('MaThietBi'), 100000);
		$eqsDialbox  = array();
		$eqIndex     =   0;

		foreach($eqs as $eq)
		{
			$eqsDialbox[0]['Dat'][$eqIndex]['ID']       = $eq->IOID;
			$eqsDialbox[0]['Dat'][$eqIndex]['Display']  = $eq->MaThietBi
			.' - '. $eq->TenThietBi;
			$eqIndex++;
		}

		$this->html->eqsDialbox = $eqsDialbox;
        $this->html->limit      = Qss_Lib_Extra_Const::$DATE_LIMIT;
	}

	public function loadAction()
    {
        $selected  = $this->params->requests->getParam('selected', 1);
        $this->html->selected = $selected;


    }

    public function showAction()
    {
        $mInout     = new Qss_Model_Warehouse_Inout();
        $start      = $this->params->requests->getParam('start', '');
        $end        = $this->params->requests->getParam('end', '');
        $eqs        = $this->params->requests->getParam('eqs', array());
        $display    = $this->params->requests->getParam('display', 0);
        $location   = $this->params->requests->getParam('location', 0);
        $workcenter = $this->params->requests->getParam('workcenter', 0);
        $equipment  = $this->params->requests->getParam('equipment', 0);
        $costcenter = $this->params->requests->getParam('costcenter', 0);
        $mStart     = Qss_Lib_Date::displaytomysql($start);
        $mEnd       = Qss_Lib_Date::displaytomysql($end);

        if($display == 1)
        {
            $data = $mInout->getVatTuByLocation($mStart, $mEnd, $location);
        }
        elseif($display == 2)
        {
            $data = $mInout->getVatTuByUnit($mStart, $mEnd, $workcenter);
        }
        elseif($display == 3)
        {
            $data = $mInout->getVatTuByEquipment($mStart, $mEnd, $equipment);
        }
        else
        {
            $data = $mInout->getVatTuByTrungTamChiPhi($mStart, $mEnd, $costcenter);
        }

        $this->html->report = $data;
        $this->html->start  = $start;
        $this->html->end    = Qss_Lib_Date::mysqltodisplay($end);
    }

	// @todo: chua tinh chi phi sua chua
	public function show2Action()
	{
		$report = array();
		$start  = $this->params->requests->getParam('start', '');
		$end    = $this->params->requests->getParam('end', '');
		$eqs    = $this->params->requests->getParam('eqs', array());
		$floc   = $this->params->requests->getParam('flocation', 0);
		$fwc    = $this->params->requests->getParam('fworkcenter', 0);
		$feq    = $this->params->requests->getParam('fequip', 0);

		$filter          = array();
		$filter['start'] = Qss_Lib_Date::displaytomysql($start);
		$filter['end']   = Qss_Lib_Date::displaytomysql($end);;
		$filter['eqs']   = $eqs;

		// Tong hop chi phi theo don vi thuc hien
		if($fwc)
		{
			$fwcData = $this->_model->getCostFromOutputGroupByWorkcenter($filter);
			$report['GroupWC'] = 'Đơn vị';
			$report['DatWC']     = array();
			$fwcDataIndex        = 0;
				
			foreach ($fwcData as $dat)
			{
				$report['DatWC'][$fwcDataIndex]['Name']          = $dat->WCCode
				.' - '. $dat->WCName;
				$report['DatWC'][$fwcDataIndex]['MaterialCost']  = $dat->MaterialCost;
				$fwcDataIndex++;
			}
			if(!count($report['DatWC'])) unset($report['GroupWC']);
		}
		// Tong hop chi phi theo cac khu vuc
		if($floc)
		{
			$flocData = $this->_model->getCostFromOutputGroupByLocation($filter);
			$report['GroupLoc'] = 'Khu vực';
			$report['DatLoc']    = array();
			$retval              = array();
				
			foreach($flocData as $index=>$dat)
			{
				$devideRL = $dat->LocRight - $dat->LocLeft;


				// Neu ko co chi phi va ko co cay con thi bo qua
				if($devideRL == 1 && $dat->MaterialCost == 0)
				{
					continue;
				}


				$retval['loc'][$index] = $dat;
				if(!isset($retval['MaterialCost'][$dat->LocID]))
				{
					$retval['MaterialCost'][$dat->LocID] = $dat->MaterialCost;

				}
				else
				{
					$retval['MaterialCost'][$dat->LocID] += $dat->MaterialCost;
				}

				unset($flocData->{$index}); // loai bo phan tu hien tai ra khoi cay

				if($devideRL > 1)
				{
					foreach ($flocData as $index2 => $a2)
					{
						// giam thieu so vong lap
						if(($retval['loc'][$index]->LocRight - $a2->LocRight) < 0)
						{
							break;
						}

						if($retval['loc'][$index]->LocLeft < $a2->LocLeft
						&& $retval['loc'][$index]->LocRight >  $a2->LocRight)
						{
							$retval['loc'][$index]->MaterialCost += $a2->MaterialCost;
						}
					}
						
					// Neu co cay con nhung chi phi bang 0 cung bo di
					if($retval['loc'][$index]->MaterialCost == 0)
					{
						unset($retval['loc'][$index]);
					}
				}


			}
				
			$report['DatLoc'] = isset($retval['loc'])?$retval['loc']:array();
			if(!count($report['DatLoc'])) unset($report['GroupLoc']);
		}
        //echo '<pre>'; print_r($report); die;

		// Tong hop chi phi theo tung thiet bi
		if($feq)
		{
			$feqData = $this->_model->getCostFromOutputGroupByEquip($filter);
			$report['GroupEq'] = 'Thiết bị';
			$report['DatEq']  = array();
			$oldEqID = '';
			$eqGroupIDIndex = -1;
			$feqDataIndex = 0;
				
			foreach($feqData as $dat)
			{
				if($oldEqID != $dat->EqGroupID)
				{
					$eqGroupIDIndex++;
					$report['DatEq'][$eqGroupIDIndex]['Type'] = $dat->EqType;
				}

				$report['DatEq'][$eqGroupIDIndex]['Dat'][$feqDataIndex]['Name'] = $dat->EqCode;
				$report['DatEq'][$eqGroupIDIndex]['Dat'][$feqDataIndex]['MaterialCost'] = $dat->MaterialCost;
				$feqDataIndex++;
				$oldEqID = $dat->EqGroupID;
			}
				
			if(!count($report['DatEq'])) unset($report['GroupEq']);
				
		}

		$this->html->report = $report;
		$this->html->start  = $start;
		$this->html->end    = Qss_Lib_Date::mysqltodisplay($end);
	}
}

?>