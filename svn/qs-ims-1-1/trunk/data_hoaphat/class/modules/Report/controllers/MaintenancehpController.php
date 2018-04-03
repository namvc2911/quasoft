<?php

/**
 *
 * @author ThinhTuan
 *
 */
class Report_MaintenancehpController extends Qss_Lib_Controller
{
        protected $_model;
        //protected $_modelQuery;
        protected $_common;
        protected $_params;
        protected $_core; // MAINTENANCE MODEL IN CORE

        /**
         *
         * @return unknown_type
         */

        public function init()
        {
                //$this->i_SecurityLevel = 15;
                parent::init();
                $this->_model = new Qss_Model_Hoaphat_Maintenance();
                $this->_core = new Qss_Model_Extra_Maintenance();
                $this->_params = $this->params->requests->getParams();
                $this->_common = new Qss_Model_Extra_Extra();
                //$this->_modelQuery = new Qss_Model_Query();
                $lang = Qss_Lib_Extra::returnString($this->_user->user_lang,
                                'vn');

                $this->headScript($this->params->requests->getBasePath() . '/js/' . $lang . '.js');
                $this->headScript($this->params->requests->getBasePath() . '/js/common.js');
                $this->headScript($this->params->requests->getBasePath() . '/js/report-list.js');
                $this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');
                $this->html->curl = $this->params->requests->getRequestUri();
                $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        }

        public function equipmentsPrintAction()
        {
                $this->html->workCenter = $this->_common->getTable(array('*'),
                        'ODonViSanXuat', array(), array(), 'NO_LIMIT');
        }

        public function equipmentsPrint1Action()
        {
                $equips = $this->_model->getEquipmentsList($this->_params['group'], $this->_params['location']);
                $this->html->equips = $equips;
                $this->html->group = $this->_params['group'];
                $this->html->location = $this->_params['LocName'] ? $this->_params['LocName'] : '';
                $this->html->groupName = ($this->_params['group'] && count((array)$equips)) ? $equips[0]->NhomThietBi : '';
        }

        public function maintainPlanAction()
        {
                
        }

        public function maintainPlan1Action()
        {
                $coreMaint = new Qss_Model_Maintenance_Plan();
                $microStart = strtotime($this->_params['start']);
                //$shift = $this->_common->getTable(array('*'), 'OCa',
                //        array(), array(), 'NO_LIMIT');
                $print = array();


                $coreMaint->setFilterByLocIOID(@(int)$this->_params['location']);
                $coreMaint->setFilterByEqGroupIOID(@(int)$this->_params['group']);
                $coreMaint->setFilterByMaintainTypeIOID(@(int)$this->_params['type']);
                $plan = $coreMaint->getPlans();

                $i = 0;
                $oldItem = '';
                $startFirst = date_create($this->_params['start']);
                $endFirst = date_create($this->_params['end']);
                $removeDuplicate = array();
                $solar = new Qss_Model_Calendar_Solar();
                foreach ($plan as $item)
                {
                        // Lay bao tri theo ngay cua tung dong bao tri dinh ky
                        if ($item->NgayBTKDK)
                        {
                                if (!isset($removeDuplicate[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri][$item->NgayBTKDK][$item->Ref_Ca]))
                                {
                                        if (Qss_Lib_Date::checkInRangeTime($item->NgayBTKDK,
                                                        $this->_params['start'],
                                                        $this->_params['end']))
                                        {
                                                $print[$item->NgayBTKDK][$i]['Code'] = $item->MaThietBi;
                                                $print[$item->NgayBTKDK][$i]['Name'] = $item->TenThietBi;
                                                $print[$item->NgayBTKDK][$i]['Type'] = $item->LoaiBaoTri;
                                                $print[$item->NgayBTKDK][$i]['Loc'] = $item->TenKhuVucTheoDS;
                                                $print[$item->NgayBTKDK][$i]['Qty'] = 1;
                                                $print[$item->NgayBTKDK][$i]['Date'] = $item->NgayBTKDK;
                                                $print[$item->NgayBTKDK][$i]['Out'] = $item->BenNgoai;
                                                $removeDuplicate[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri][$item->NgayBTKDK][$item->Ref_Ca] = 1;
                                                $i++;
                                        }
                                }
                        }
                        
                        // Lay bao tri dinh ky theo ky cu the
                        if ($oldItem != $item->PIOID)
                        {
                                $start = $startFirst;
                                while ($start <= $endFirst)
                                {
                                        $day = $start->format('d');
                                        $weekday = $start->format('w');
                                        $month = $start->format('m');
                                        $startToDate = $start->format('Y-m-d');
                                        $monthNo = $solar->getMonthNo((int) $month);

                                        // Neu chua co bao tri cua san pham voi loai bao tri cung ngay thi them dong
                                        if (!isset($removeDuplicate[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri][$startToDate][$item->Ref_Ca]))
                                        {
                                                if (($item->MaKy == 'D') || ($item->MaKy
                                                        == 'M' && $day == $item->Ngay)
                                                        || ($item->MaKy == 'W' && $weekday
                                                        == $item->GiaTriThu) || ($item->MaKy
                                                        == 'Y' && $day == $item->Ngay
                                                        && $month == $item->Thang))// || ($item->MaKy == 'Q' && $monthNo == $item->ThangThu && $day == $item->Ngay)
                                                {
                                                        $print[$startToDate][$i]['Code'] = $item->MaThietBi;
                                                        $print[$startToDate][$i]['Name'] = $item->TenThietBi;
                                                        $print[$startToDate][$i]['Type'] = $item->LoaiBaoTri;
                                                        $print[$startToDate][$i]['Loc'] = $item->TenKhuVucTheoDS;
                                                        $print[$startToDate][$i]['Qty'] = 1;
                                                        $print[$startToDate][$i]['Date'] = $start->format('Y-m-d');
                                                        $print[$startToDate][$i]['Out'] = $item->BenNgoai;
                                                        $removeDuplicate[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri][$startToDate][$item->Ref_Ca] = 1;
                                                        $i++;
                                                }
                                        }
                                        $start = Qss_Lib_Date::add_date($start,
                                                        1);
                                }
                        }
                        $oldItem = $item->IOID;
                }
                //$sorted = array_orderby($print, 'Date', SORT_ASC, 'Code', SORT_ASC);
                //echo '<pre>'; print_r($print); die;


                $this->html->start = $startFirst;
                $this->html->end = $endFirst;
                $this->html->print = $print;
        }

        public function maintainHistoryAction() // only for maintain by period
        {
                
        }

        public function maintainHistory1Action()
        {
                $history = $this->_model->getPeriodWorkOrderHistory(Qss_Lib_Date::displaytomysql($this->_params['start']),
                        Qss_Lib_Date::displaytomysql($this->_params['end']),
                        $this->_params['group']);
                $oldEq = '';
                $countGroup = array();

                foreach ($history as $wo)
                {
                        if ($oldEq != $wo->Ref_MaThietBi)
                        {
                                if (isset($count))
                                {
                                        $countGroup[$oldEq] = $count;
                                }
                                $count = 0;
                        }
                        $count++;
                        $oldEq = $wo->Ref_MaThietBi;
                }
                if (isset($oldEq) && isset($count))
                {
                        $countGroup[$oldEq] = $count;
                }


                $this->html->print = $history;
                $this->html->count = $countGroup;
                $this->html->start = Qss_Lib_Date::mysqltodisplay($this->_params['start']);
                $this->html->end = Qss_Lib_Date::mysqltodisplay($this->_params['end']);
        }

        // Bao cao de nghi mua vat tu
        public function materialRequireAction()
        {
                
        }

        public function materialRequire1Action()
        {
                $warehouse = new Qss_Model_Extra_Warehouse();
                $this->html->report = $warehouse->getRecommendMaterials();
        }

        // Bao cao yeu cau bao tri theo ngay
        public function maintainDailyAction()
        {
			$loaiBaoTriDialBoxData = array();
			$loaiBaoTri            = $this->_common->getTable(array('*'), 'OPhanLoaiBaoTri', array(), array('Loai'));
			$i                     = 0;
			
			foreach($loaiBaoTri as $dat)
			{
				$loaiBaoTriDialBoxData[0]['Dat'][$i]['ID']      = $dat->IOID;
				$loaiBaoTriDialBoxData[0]['Dat'][$i]['Display'] = $dat->Loai;
				$i++;
			}
			
            $this->html->loaiBaoTriDialBoxData = $loaiBaoTriDialBoxData;                
        }

        public function maintainDaily1Action()
        {
                //$maintain = new Qss_Model_Hoaphat_Maintenance();
                $coreMaintain = new Qss_Model_Extra_Maintenance();
                $date = $this->params->requests->getParam('date', '');
                $location = $this->params->requests->getParam('location', 0);
				$maintype = $this->params->requests->getParam('maintype', 0);
                //$planOrOrder = $this->params->requests->getParam('planOrOrder');
                //$refEqArr = Qss_Lib_Maintenance_Common::getAllEqByLoc($location);
                $locName = $this->_common->getTable(array('*')
                        ,
                        'OKhuVuc'
                        ,
                        array('IOID' => $location)
                        , array(), 'NO_LIMIT',  1);


                // ***********************************************************
                // ***********************************************************
                $this->html->date = $date;
                $this->html->loc = $location;
                //$this->html->planOrOrder = $planOrOrder;
                $this->html->locName = $locName ? "{$locName->MaKhuVuc} - {$locName->Ten}" : '';
                //$this->html->report = $coreMaintain->getMaintainRequirements(Qss_Lib_Date::displaytomysql($date), $location);

                $this->html->report = $coreMaintain->getAllMaintainPlanByDate(
					Qss_Lib_Date::displaytomysql($date)
					, $location
					, $maintype); 
        }

        public function maintainResultAction()
        {
			$loaiBaoTriDialBoxData = array();
			$loaiBaoTri            = $this->_common->getTable(array('*'), 'OPhanLoaiBaoTri', array(), array('Loai'));
			$i                     = 0;
			
			foreach($loaiBaoTri as $dat)
			{
				$loaiBaoTriDialBoxData[0]['Dat'][$i]['ID']      = $dat->IOID;
				$loaiBaoTriDialBoxData[0]['Dat'][$i]['Display'] = $dat->Loai;
				$i++;
			}
			
            $this->html->loaiBaoTriDialBoxData = $loaiBaoTriDialBoxData;
        }
		
	/**
	 * Refresh comment
	 */
	public function maintainResult3Action()
	{
		$coreMaintain = new Qss_Model_Extra_Maintenance();
		$date     = $this->params->requests->getParam('date', '');
		$location = $this->params->requests->getParam('location', 0);
		$ifidMO   = $this->params->requests->getParam('ifid', 0);
		$row   = $this->params->requests->getParam('row', 0);
		$col   = $this->params->requests->getParam('col', 0);

		// *Dem so luong comment cho moi dong phieu bao tri
		$commentAmountObj = $coreMaintain->countMaintainRequirementsComment(Qss_Lib_Date::displaytomysql($date),$location, 0, $ifidMO);
		$commentAmount    = count((array)$commentAmountObj)?@(int)$commentAmountObj[0]->CommentAmount:0;

		
		// Lay thong tin bao tri
		$dataTemp = $coreMaintain->getMaintainRequirements(Qss_Lib_Date::displaytomysql($date), $location, 0, $ifidMO); // Lay noi dung phieu bao tri de hien thi
		$data     = count((array)$dataTemp)?$dataTemp[0]:new stdClass();
		
		$this->html->countComment = $commentAmount;
		$this->html->data         = $data;
		$this->html->ifid         = $ifidMO;
		$this->html->row          = $row;
		$this->html->col          = $col;
	}		

        public function maintainResult1Action()
        {
                $coreMaintain = new Qss_Model_Extra_Maintenance();
                $date = $this->params->requests->getParam('date', '');
                $location = $this->params->requests->getParam('location', 0);
				$maintype = $this->params->requests->getParam('maintype', 0);
				
                $locName = $this->_common->getTable(array('*')
                        ,  'OKhuVuc'
                        , array('IOID' => $location)
                        , array(), 'NO_LIMIT',  1);
                
                // *Dem so luong comment cho moi dong phieu bao tri
                $commentAmountObj = $coreMaintain->countMaintainRequirementsComment(Qss_Lib_Date::displaytomysql($date),$location, $maintype);
                $commentAmountArr = array();

                foreach ($commentAmountObj as $ca)
                {
                        $commentAmountArr[$ca->IFID] = $ca->CommentAmount;
                }

                // *Truyen tham so cho bao cao
                $this->html->date = $date;
                $this->html->loc = $location;
                $this->html->locName = $locName ? "{$locName->MaKhuVuc} - {$locName->Ten}" : '';
                $this->html->report = $coreMaintain->getMaintainRequirements(Qss_Lib_Date::displaytomysql($date), $location, $maintype); // Lay noi dung phieu bao tri de hien thi
                $this->html->commentAmount = $commentAmountArr;
        }

        public function maintainResult2Action()
        {
                $ifid = $this->params->requests->getParam('ifid', 0);
                $deptid = $this->params->requests->getParam('deptid', 0);
                $form = new Qss_Model_Form();
                $form->initData($ifid, $deptid);
                if ($this->b_fCheckRightsOnForm($form, 15))
                {
                        $user = new Qss_Model_Admin_User();
                        $user->init($form->i_UserID);
                        $dept = new Qss_Model_Admin_Department();
                        $dept->init($form->i_DepartmentID);
                        $this->html->user = $user;
                        $this->html->cuser = $this->_user;
                        $this->html->dept = $dept;
                        $this->html->form = $form;
                        $step = new Qss_Model_System_Step($form->i_WorkFlowID);
                        if ($form->i_Status)
                        {
                                $step->v_fInitByStepNumber($form->i_Status);
                                $this->html->status = $step->szStepName;
                        }
                        else
                        {
                                $this->html->status = '';
                        }
                        $form->read(Qss_Register::get('userinfo')->user_id);
                        ///$this->html->sharings = $form->a_fGetSharing();
                        $this->html->traces = $form->a_fGetTrace();
                        ///$this->html->readers = $form->getReaders();
                        $this->html->comments = $form->getComments();
                        $mainobject = $form->o_fGetMainObject();
                        $mainobject->initData($form->i_IFID,
                                $form->i_DepartmentID, 0);
                        //$this->html->events = $mainobject->getEvents();
                        $bash = new Qss_Model_Bash();
                        $this->html->history = $bash->getHistoryByToIFID($ifid);
                        $this->html->transfer = $bash->getHistoryByIFID($ifid);
                        $this->html->step = $step;
                }
                $this->setLayoutRender(false);
        }

        public function maintainPeriodAction()
        {
                
        }

        public function maintainPeriod1Action()
        {
			$maintPlanModel = new Qss_Model_Maintenance_Plan();
			$group          = $this->params->requests->getParam('group');
			$location       = $this->params->requests->getParam('location');

			$this->html->list = $maintPlanModel->getEquipmentsMaintenance(
				array()
				, $location
				, 0
				, $group
			);				
        }
}
