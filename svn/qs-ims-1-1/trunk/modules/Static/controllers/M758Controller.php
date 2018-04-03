<?php
/**
 * Class Static_M154Controller
 * Báo cáo theo dõi mua điện năng hàng tháng
 */
class Static_M758Controller extends Qss_Lib_Controller
{  
    protected $_model;
    public function init()
    {
        parent::init();
        $this->_model = new Qss_Model_Production_Order();      
		$this->headScript($this->params->requests->getBasePath() . '/js/common.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/tabs.js');
	}
    
		/* Module: Theo doi san xuat /extra/production/info/index */
	/**
	 * Module Theo doi san xuat: Index
	 */
	public function indexAction()
	{
		$po     = $this->params->cookies->get('pinfo_po',0);
		$po     = $po?$po:0;
		$this->html->oldpo = $this->_model->getOrderByIOID($po);
	}

	/**
	 * Module Theo doi san xuat: Work orders
	 * - Hien thi phieu giao viec theo dieu kien loc
	 * - Bieu do tron: dem so phieu giao viec theo tinh trang
	 * - Bieu do cot: hien thi so luong san pham yeu cau sx, da sx, sp loi
	 */
	public function workorderAction()
	{
		$start = $this->params->requests->getParam('start', '');
		$end   = $this->params->requests->getParam('end', '');
		$po    = $this->params->requests->getParam('po', array());
		$page  = $this->params->requests->getParam('page', 1);
		$display = $this->params->requests->getParam('display', 10);

		// hien thi wo
		$this->html->comments = $this->countCommentForWorkOrders($start, $end, $po);
		$this->html->wo = $this->_model->getOrderInfor(Qss_Lib_Date::displaytomysql($start)
								, Qss_Lib_Date::displaytomysql($end)
								, $po
								, $display
								, $page);
		$this->html->deptid = $this->_user->user_dept_id;
		$this->html->uid = $this->_user->user_id;
		$this->html->total = $this->countTotalPageForProductionInfo($start, $end, $po, $display);
		$this->html->page = $page;
		$this->html->wosteps = Qss_Lib_System::getStepsByForm('M712');
		$this->html->statiticschart = $this->_model->getOrderInfor(
		Qss_Lib_Date::displaytomysql($start), Qss_Lib_Date::displaytomysql($end), $po);

	}

	/**
	 * Module Theo doi san xuat: Production order group by line
	 * Hien thi lenh san xuat theo day chuyen
	 */
	public function poAction()
	{
		$start = $this->params->requests->getParam('start', '');
		$end   = $this->params->requests->getParam('end', '');
		$this->html->po = $this->sortProductionOrdersWithLines($start, $end);
	}

	/**
	 * Module Theo doi san xuat: Pie Chart - count by wo step
	 * Bieu do tron: dem so phieu giao viec theo tinh trang
	 */
	public function wobystepchartAction()
	{

	}

	/**
	 * Module Theo doi san xuat: Column chart  item statistics
	 * Bieu do cot: hien thi so luong san pham yeu cau sx, da sx, sp loi
	 */
	public function itemstatisticchartAction()
	{

	}

	/**
	 * Module Theo doi san xuat
	 * Sap xep lenh san xuat theo day chuyen loc theo ngay
	 * @param date $start
	 * @param date $end
	 */
	private function sortProductionOrdersWithLines($start, $end)
	{
		$retval = array();
		$linemodel = new Qss_Model_Production_Line();
		$start  = Qss_Lib_Date::displaytomysql($start);
		$end    = Qss_Lib_Date::displaytomysql($end);
		$i      = 0;
		$lang   = $this->_user->user_lang;

		$po    = $this->_model->getOrderByRange($start, $end, $lang);
		$lines = $linemodel->getAll();

		// Khoi tao mang day chuyen
		foreach($lines as $l)
		{
			$retval[$l->IOID]['Code']  = $l->MaDayChuyen;
			$retval[$l->IOID]['Name']  = $l->TenDayChuyen;
			$retval[$l->IOID]['Count'] = 0;
		}

		// Gan lenh san xuat theo day chuyen
		foreach($po as $p)
		{
			//if(!isset($retval[$l->IOID]))
			//{
			//	// day chuyen da bi xoa
			//}
				
			if(isset($retval[$p->Ref_DayChuyen]))
			{
				$retval[$p->Ref_DayChuyen]['PO'][$i]['IOID']  = $p->IOID;
				$retval[$p->Ref_DayChuyen]['PO'][$i]['Code']  = $p->MaLenhSX;
				$retval[$p->Ref_DayChuyen]['PO'][$i]['Step']  = $p->Step;
				$retval[$p->Ref_DayChuyen]['PO'][$i]['Class'] = $p->Class;
				$retval[$p->Ref_DayChuyen]['PO'][$i]['Start'] = $p->TuNgay;
				$retval[$p->Ref_DayChuyen]['PO'][$i]['End']   = $p->DenNgay;
				$retval[$p->Ref_DayChuyen]['Count']          += 1;
				$i++;
			}
			//echo $p->MaLenhSX.'-'.$p->DayChuyen.'<br/>';
		}
		//die;
		return $retval;
	}

	/**
	 *
	 * Module Theo doi san xuat: dem so luong comment theo phieu giao viec
	 * @param date $start
	 * @param date $end
	 * @param array $po
	 */
	private function countCommentForWorkOrders($start, $end, $po)
	{
		$retval = array();
		$start  = Qss_Lib_Date::displaytomysql($start);
		$end    = Qss_Lib_Date::displaytomysql($end);
		$comments = $this->_model->getOrderComments($start, $end, $po);

		foreach ($comments as $ca)
		{
			$retval[$ca->IFID] = $ca->Total;
		}
		return $retval;
	}

	/**
	 *
	 * Module Theo doi san xuat: dem so trang phieu giao viec.
	 * @param date $start
	 * @param date $end
	 * @param array $po
	 * @param int $display
	 */
	private function countTotalPageForProductionInfo($start, $end, $po, $display)
	{
		$start  = Qss_Lib_Date::displaytomysql($start);
		$end    = Qss_Lib_Date::displaytomysql($end);
		// tong so dong
		$total = $this->_model->getOrderInfor($start, $end,$po,$this->_user->user_lang,true);
		// tong so trang
		return ceil((int)$total /  (int)$display);
	}
    
}