<?php
/**
 * Phân tích lịch sửa bảo trì định kỳ
 */
class Static_M733Controller extends Qss_Lib_Controller
{  

	public function init()
   	{
       	$this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
   		parent::init();
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
   	}

    public function indexAction()
    {
       
    }

	public function showAction()
	{
        $arrData    = array();
		$start      = $this->params->requests->getParam('start');
		$end        = $this->params->requests->getParam('end');
        $mStart     = Qss_Lib_Date::displaytomysql($start);
        $mEnd       = Qss_Lib_Date::displaytomysql($end);
		$refEq      = $this->params->requests->getParam('eq', 0);

        $mCommon    = new Qss_Model_Extra_Extra();
        $mWorkorder = new Qss_Model_Maintenance_Workorder();
        $planModel  = new Qss_Model_Maintenance_Plan();
		$locModel   = new Qss_Model_Maintenance_Location();

        $history    = $mWorkorder->getHistoryByEquipment($refEq, $mStart, $mEnd);
        $plan       = $planModel->getActivePlanByEquipment($refEq);

        foreach ($history as $item)
        {
            $arrData[$item->Ref_LoaiBaoTri][$item->Ref_ChuKy][$item->STT] = $item;
        }

		$this->html->eq         = $mCommon->getTableFetchOne('ODanhSachThietBi', array('IOID'=>$refEq));
		$this->html->workcenter = $locModel->getManageDepOfEquip($refEq);
		$this->html->start      = $mStart;
		$this->html->end        = $mEnd;
		$this->html->plan       = $plan;
        $this->html->data       = $arrData;
	}
	
}