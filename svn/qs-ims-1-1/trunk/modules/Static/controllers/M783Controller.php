<?php
/**
 * Class Static_M154Controller
 * Báo cáo theo dõi mua điện năng hàng tháng
 */
class Static_M783Controller extends Qss_Lib_Controller
{  
    
    public function init()
    {
        parent::init();         
		$this->headScript($this->params->requests->getBasePath() . '/js/common.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/tabs.js');
	}
    
/**
	 * Module WIP Work In Process
	 * Hien thi ra o text nhap barcode
	 */
	public function indexAction()
	{

	}

	/**
	 * Module WIP Work In Process
	 * Hien thi thong tin phieu giao viec theo barcode
	 */
	public function searchAction()
	{
		$taskmodel = new Qss_Model_Production_Task();
		$output = new Qss_Model_Production_Output();
		$barcode   = $this->params->requests->getParam('barcode', '');
		$task = $taskmodel->getTaskByBarcode($barcode);
		$this->html->wo = $taskmodel->getTaskByBarcode($barcode);
		$this->html->steps = Qss_Lib_System::getStepsByForm('M712');
		$this->html->step = (int) @$task->Status;
		$this->html->statistics = $this->html->wo?
			$output->getProductQuantity($this->html->wo->MaLenhSX,$this->html->wo->Ref_CongDoan,$this->html->wo->Ref_DonViThucHien):new stdClass();
		$this->html->phupham = $this->html->wo?$output->getByProductQuantity($this->html->wo->MaLenhSX,$this->html->wo->Ref_CongDoan,$this->html->wo->Ref_DonViThucHien):new stdClass();
		$this->html->sanphamloi = $this->html->wo?$output->getDefectProductQuantity($this->html->wo->MaLenhSX,$this->html->wo->Ref_CongDoan,$this->html->wo->Ref_DonViThucHien):new stdClass();
		$this->html->lang = $this->_user->user_lang;
		$this->html->deptid = $this->_user->user_dept_id;
		$this->html->uid = $this->_user->user_id;
		$this->html->fid = 'M712';
	}


	/**
	 * Module WIP Work In Process
	 * Luu lai thay doi so luong thuc hien
	 * cua phieu giao viec, chi thuc hien o buoc 1,2 (ko phai so luong yeu cau)
	 */
	public function saveAction()
	{
		$params = $this->params->requests->getParams();
		if ($this->params->requests->isAjax())
		{
			$service = $this->services->Extra->Production->Wo->Wip->Save($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
    
}