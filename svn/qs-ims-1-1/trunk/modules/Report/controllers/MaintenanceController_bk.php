<?php

/**
 *
 * @author: ThinhTuan
 * @todo: bo ca ham thua, /cost/manufacturing, /cost/group/
 * @todo: gop ba bao cao ve dung may theo ky, nhom, khu vuc
 * @todo: Can them dieu kien cho mot so bao cao ve pbt voi tinh trang pbt la hoan thanh
 * @todo: Sua bao cao m750
 */
class Report_MaintenanceController extends Qss_Lib_Controller
{
	// property
	protected $_model;  /* Remove */
	protected $_params; /* Remove */
	protected $_common; /* Remove */

	public function init()
	{
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
		$this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
	}
	
	public function maintainIncompleteAction()
	{

	}

	public function maintainIncomplete1Action()
	{
		$workordercmodel = new Qss_Model_Maintenance_Workorder();
		$location   = $this->params->requests->getParam('location', 0);
		$workcenter = $this->params->requests->getParam('workcenter', 0);
		$date = $this->params->requests->getParam('date','');
		$date = $date?Qss_Lib_Date::displaytomysql($date):'';

		$worksOfMaintainOrder  = $workordercmodel->getInCompletedTasksOfWorkOrder($date, $location, 0, 0, $workcenter);
		$maintainReturn        = $this->getMaintainDataForMaintainOrder(
		$date, $location
		);
		//array(), $worksOfMaintainOrder);
		$this->html->report    = $maintainReturn;
	}

	// ***********************************************************************************************
	// *** PM - M733 Bao cao yeu cau bao tri theo ngay
	// *** Hien thi yeu cau bao tri theo ke hoach (Neu chon ke hoach)
	// *** Hien thi phieu bao tri theo ngay (Neu chon phieu bao tri)
	// ***********************************************************************************************
	

	public function maintainResultAction()
	{
		$loaiBaoTriDialBoxData = array();
		$loaiBaoTri            = $this->_common->getTable(array('*'), 'OPhanLoaiBaoTri', array(), array('Loai'));
		$i                     = 0;

		foreach($loaiBaoTri as $dat)
		{
			if(!$dat->DungTaoPhieu)
			{
				$loaiBaoTriDialBoxData[0]['Dat'][$i]['ID']      = $dat->IOID;
				$loaiBaoTriDialBoxData[0]['Dat'][$i]['Display'] = $dat->Loai;
				$i++;
			}
		}
		$rights = $this->getFIDRights(197);
		$where = '';
		if(!($rights & 2))
		{
			$location = new Qss_Model_Maintenance_Location();
			$locations = $location->getLocationByCurrentUser();
			foreach ($locations as $item)
			{
				if($where)
				{
					$where .= ' or ';
				}
				$where .= sprintf('(lft >= %1$d and rgt <= %2$d)',$item->lft,$item->rgt);
			}
		}
		if($where)
		{
			$where = sprintf('and (%1$s)',$where);
		}
		$this->html->where = $where;
		$this->html->loaiBaoTriDialBoxData = $loaiBaoTriDialBoxData;
	}

	public function maintainResult1Action()
	{
		$coreMaintain = new Qss_Model_Extra_Maintenance();
		$workordercmodel = new Qss_Model_Maintenance_Workorder();
		$date = $this->params->requests->getParam('date', '');
		$location = $this->params->requests->getParam('location', 0);
		$maintype = $this->params->requests->getParam('maintype', array(0));

		$locName = $this->_common->getTable(array('*')
		,  'OKhuVuc'
		, array('IOID' => $location)
		, array(), 'NO_LIMIT', 1);

		// *Dem so luong comment cho moi dong phieu bao tri
		$commentAmountObj = $coreMaintain->countMaintainRequirementsComment(Qss_Lib_Date::displaytomysql($date),$location, $maintype);
		$commentAmountArr = array();

		foreach ($commentAmountObj as $ca)
		{
			$commentAmountArr[$ca->IFID] = $ca->CommentAmount;
		}

		$maintainReturn           = $this->getMaintainDataForMaintainOrder(Qss_Lib_Date::displaytomysql($date), $location, $maintype);

		// *Truyen tham so cho bao cao
		$this->html->date = $date;
		$this->html->loc = $location;
		$this->html->locName = $locName ? "{$locName->MaKhuVuc} - {$locName->Ten}" : '';
		$this->html->report =$maintainReturn; // Lay noi dung phieu bao tri de hien thi
		$this->html->commentAmount = $commentAmountArr;

		// Lấy thông tin đánh giá
		$systemFieldModel = new Qss_Model_System_Field();
		$systemFieldModel->init('OCongViecBTPBT','DanhGia');

		$this->html->reviewField = $systemFieldModel->getJsonRegx();

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
		$maintype = $this->params->requests->getParam('maintype', 0);

		// *Dem so luong comment cho moi dong phieu bao tri
		$commentAmountObj = $coreMaintain->countMaintainRequirementsComment(Qss_Lib_Date::displaytomysql($date),$location, $maintype, $ifidMO);
		$commentAmount    = count((array)$commentAmountObj)?@(int)$commentAmountObj[0]->CommentAmount:0;


		echo Qss_Json::encode(array('count'=>$commentAmount, 'id'=>$ifidMO, 'error'=>false));
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	/**
	 * Hien thi comment
	 */
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
			} else
			{
				$this->html->status = '';
			}
			$form->read(Qss_Register::get('userinfo')->user_id);
			///$this->html->sharings = $form->a_fGetSharing();
			$this->html->traces = $form->a_fGetTrace();
			///$this->html->readers = $form->getReaders();
			$this->html->comments = $form->getComments();
			$mainobject = $form->o_fGetMainObject();
			$mainobject->initData($form->i_IFID, $form->i_DepartmentID, 0);
			//$this->html->events = $mainobject->getEvents();
			$bash = new Qss_Model_Bash();
			$this->html->history = $bash->getHistoryByToIFID($ifid);
			$this->html->transfer = $bash->getHistoryByIFID($ifid);
			$this->html->step = $step;
		}
		$this->setLayoutRender(false);

	}

	/**
	 * Bao cao tuoi tho phu tung
	 * @Module
	 */
	public function maintainRecycleAction()
	{

	}

	public function maintainRecycle1Action()
	{
		$start  = $this->params->requests->getParam('start', 0);
		$end    = $this->params->requests->getParam('end', 0);
		$group  = $this->params->requests->getParam('group', 0);
		$type   = $this->params->requests->getParam('type', 0);
		$eq     = $this->params->requests->getParam('eq', 0);
		$bytype = $this->params->requests->getParam('bytype', 1);

		$startMysql = Qss_Lib_Date::displaytomysql($start);
		$endMysql   = Qss_Lib_Date::displaytomysql($end);

		if($bytype == 1)
		{
			$data  = $this->_model->getMaterialRecycle($startMysql, $endMysql
			, $group, $type, $eq);
		}
		else
		{
			$data  = $this->_model->getMaterialRecycleFromWarhouseInOut($startMysql, $endMysql
			, $group, $type, $eq);
		}
		$ret   = array();
		$temp2 = array(); // Mang trung gian chua cac lan bao tri
		$date  = '';

		/**
		 * + Lay ra query xep phu tung theo ngay, thiet bi, bo phan
		 * + Lap data tu query
		 *   - Lap mang tra ve voi key la "Thiet bi - Bo Phan - Phu Tung"
		 *   - Lay ra ngay dau tien co phieu bao tri
		 *   - Lien tuc lay ngay cuoi bao tri cho den khi co ngay cuoi cung
		 *   - Tinh Min bang khoang cach ngay nho nhat giua cac lan bao tri
		 *   - Tinh Max bang khoang cach lon nhat giua cac lan bao tri
		 *   - Tinh AVG bang tong so luong su dung chia cho so ngay(so ngay bang
		 * ngay cuoi bao tri tru cho ngay dau tien bao tri trong thoi gian loc)
		 */
		foreach($data as $dat)
		{
			// Ma phan biet may moc - bo phan - phu tung
			$code = @(int)$dat->RefEq .'-'.
			@(int)$dat->RefComponent .'-'.
			@(int)$dat->RefSparepart;
				
			// Luu ngay dau tien thay phu tung trong chuoi ngay
			if(!isset($ret[$code]['Start']))
			{
				$ret[$code]['Start']     = $dat->Date;
				$ret[$code]['EqCode']    = $dat->ECode;
				$ret[$code]['EqName']    = $dat->ECode;
				$ret[$code]['Component'] = $dat->Component;
				$ret[$code]['Position']  = $dat->Position;
				$ret[$code]['ItemCode']  = $dat->SCode;
				$ret[$code]['ItemName']  = $dat->SName;
				$ret[$code]['WIFIDS']    = $dat->WIFID; // Start Line
				$ret[$code]['Use']       = 0;
				//				$ret[$code]['Return']    = 0;
				//				$ret[$code]['Lost']      = 0;
				// Tinh theo ngay, '' la ko co so lieu, 0 la thay cung ngay
				$ret[$code]['Min']       = ''; // Tinh theo ngay
				$ret[$code]['Max']       = ''; // Tinh theo ngay
				$ret[$code]['AVG']       = ''; // Tinh theo so luong/ngay
			}
				
			// Luu ngay cuoi cung thay vat tu
			$ret[$code]['End']    = $dat->Date;
			$ret[$code]['WIFIDE'] = $dat->WIFID; // End Line
			$ret[$code]['Use']    += $dat->Use;
			//			$ret[$code]['Return'] += $dat->Return;
			//			$ret[$code]['Lost']   += $dat->Lost;
				
				
			// Tinh khoang thoi gian bao tri min va max
			if(isset($temp2[$code]))
			{
				// Chi tinh khi bao tri cho cung mot "may moc,bo phan, phu tung"
				// o hai phieu bao tri khac nhau
				if($temp2[$code]['WIFID'] != $ret[$code]['WIFIDE'])
				{
					// Tim min
					if($ret[$code]['Min'] != '')
					{
						$tempMin = Qss_Lib_Date::divDate($temp2[$code]['Date']
						, $ret[$code]['End'], 'D');

						if($ret[$code]['Min'] > $tempMin)
						{
							$ret[$code]['Min'] = $tempMin;
						}
					}
					else
					{
						$ret[$code]['Min'] = Qss_Lib_Date::divDate(
						$temp2[$code]['Date'], $ret[$code]['End'], 'D');
					}
						
					// Tim Max
					if($ret[$code]['Max'] != '')
					{
						$tempMax = Qss_Lib_Date::divDate($temp2[$code]['Date']
						, $ret[$code]['End'], 'D');

						if($ret[$code]['Max'] < $tempMax)
						{
							$ret[$code]['Max'] = $tempMax;
						}
					}
					else
					{
						$ret[$code]['Max'] = Qss_Lib_Date::divDate(
						$temp2[$code]['Date'], $ret[$code]['End'], 'D');
					}
				}
			}
				
			// Luu lai ngay bao tri vao mot mang trung gian
			// de lay khoang thoi gian bao tri ngan nhat va lon nhat
			$temp2[$code]['Date']  = $dat->Date;
			$temp2[$code]['WIFID'] = $dat->WIFID;
		}

		// Tinh so luong trung binh tren ngay (So su dung chi cho so ngay bt)
		// Voi so ngay la ngay bao tri cuoi tru cho ngay bao tri dau tien
		foreach($ret as $key=>$t)
		{
			$range = Qss_Lib_Date::divDate($t['Start'], $t['End']);

			$ret[$key]['AVG'] = $range?$t['Use']/$range:$t['Use'];
			$ret[$key]['NextDate'] = Qss_Lib_Date::add_date(date_create($t['End']), (int)$ret[$key]['AVG']) ;
			$ret[$key]['NextDate'] = $ret[$key]['NextDate']->format('Y-m-d');
		}
		$this->html->report = $ret;

	}


	// Báo cáo khả năng sẵn sàng của thiết bị theo kỳ
	public function maintainAvailabilityAction()
	{

	}

	public function maintainAvailability1Action()
	{
		#1
		$start = Qss_Lib_Date::displaytomysql($this->_params['start']);
		$end = Qss_Lib_Date::displaytomysql($this->_params['end']);
		$end = Qss_Lib_Extra::getEndDate($start, $end, $this->_params['period']);
		$aTime = Qss_Lib_Extra::displayRangeDate($start, $end, $this->_params['period']); // Range time
		$this->_params['end'] = Qss_Lib_Date::mysqltodisplay($end);


		// *****************************************************************
		// === Lay danh sach thiet bi theo dieu kien loc
		// *****************************************************************
		$where = array();
		// loc thiet bi theo dieu kien
		if ($this->_params['group'])
		{
			$where['Ref_NhomThietBi'] = $this->_params['group'];
		}

		if ($this->_params['type'])
		{
			$where['Ref_LoaiThietBi'] = $this->_params['type'];
		}

		if ($this->_params['equip'])
		{
			$where['IOID'] = $this->_params['equip'];
		}
		$equipmentConfig = $this->_common->getTable(array('*'), 'ODanhSachThietBi', $where, array(), 'NO_LIMIT');



		// *****************************************************************
		// === Lay lich lam viec cua thiet bi
		// *****************************************************************
		$workingCalendarsArr = array();
		$refEquipArr = array();

		foreach ($equipmentConfig as $item)
		{
			// lay lich lam viec cua cac thiet bi
			if (!in_array($item->Ref_LichLamViec, $workingCalendarsArr))
			{
				$workingCalendarsArr[] = $item->Ref_LichLamViec;
			}

			// lay arr ref ma thiet bi
			if (!in_array($item->IOID, $refEquipArr))
			{
				$refEquipArr[] = $item->IOID;
			}
		}



		// *****************************************************************
		// === Tong thoi gian lam viec cua thiet bi tinh theo ky
		// *****************************************************************
		$totalWorkingHoursArr = array(); //Qss_Lib_Extra::getWorkingHoursOfManyCalendar($workingCalendarsArr
		//      , Qss_Lib_Date::displaytomysql($this->_params['start'])
		//      , Qss_Lib_Date::displaytomysql($this->_params['end']));
		// @todo: Viet mot ham de lay thoi gian lam viec chia theo ky
		// @todo: Can xet den lich dac biet
		foreach ($aTime as $item)
		{
			$totalWorkingHoursArr[$item['Key']] = Qss_Lib_Extra::getTotalWCal($workingCalendarsArr
			, $item['Start'], $item['End']);
		}


		$downtime = $this->getDowntime($start, $end, $this->_params['period'], $refEquipArr);
		$this->html->start = $this->_params['start'];
		$this->html->end = Qss_Lib_Date::mysqltodisplay($end);
		$this->html->time = $aTime;
		$this->html->eq = $equipmentConfig;
		$this->html->countEq = count($refEquipArr);
		$this->html->workingHours = $totalWorkingHoursArr;
		$this->html->past = $downtime['past'];
		$this->html->future = $downtime['future'];

	}

	public function maintainMaterialAction()
	{
		// Field VatTu khong ton tai o mot so ban

	}

	public function maintainMaterial1Action()
	{
		$this->html->maintain = $this->_model->getMaterialByRangeTime(
		Qss_Lib_Date::displaytomysql($this->_params['start'])
		, Qss_Lib_Date::displaytomysql($this->_params['end'])
		, $this->_params['material']
		, $this->_params['location']
		, $this->_params['type']
		, $this->_params['group']);
		$this->html->start = $this->_params['start'];
		$this->html->end = $this->_params['end'];
		$this->html->defaultCurrency = $this->_common->getDefaultCurrency();

	}

	public function maintainPeriodAction()
	{

	}


	public function maintainPeriod1Action()
	{
		$group              = $this->params->requests->getParam('group');
		$type               = $this->params->requests->getParam('type');
		$location           = $this->params->requests->getParam('location');
		$maintType          =  $this->params->requests->getParam('maint_type', 0);
		$planModel          = new Qss_Model_Maintenance_Plan();
		$this->html->report = $planModel->getEquipsWithItsPlan(
		$location
		, $group
		, $type
		, $maintType
		);
		$this->html->planMaint = $this->getNextPrevMaintainOfEquipByMaintainType();
	  
	  

	}

	public function maintainPlanAction()
	{

	}

	public function maintainPlan1Action()
	{
		$maintPlanModel    = new Qss_Model_Maintenance_Plan();
		$end               = Qss_Lib_Extra::getEndDate($this->_params['start'], $this->_params['end'], 'D');
		$shift             = $this->_common->getTable(array('*'), 'OCa');
	  
		$this->html->dates = Qss_Lib_Extra::displayRangeDate(
		Qss_Lib_Date::displaytomysql($this->_params['start']),
		$end,
		Qss_Lib_Extra_Const::PERIOD_TYPE_DAILY
		);
	  
		$this->html->start = date_create($this->_params['start']);
		$this->html->end   = date_create(Qss_Lib_Date::mysqltodisplay($end));
		$this->html->count = count((array) $shift);
		$this->html->shift = $shift;

		$maintPlanModel->setFilterByLocIOID(@(int)$this->_params['location']);
		$maintPlanModel->setFilterByEqGroupIOID(@(int)$this->_params['group']);
		$maintPlanModel->setFilterByEqTypeIOID(@(int)$this->_params['type']);
		$this->html->data = $maintPlanModel->getPlans();

	}

	public function maintainMaterialplanAction()
	{
		//	$this->v_fCheckRightsOnForm(155);

	}

	/**
	 * Lấy ngày bảo trì đặc biệt cho từng thiết bị cho bao cao M768: Ke hoach su dung vat tu
	 * @param object $materialPlans Qss_Model_Extra_Maintenance::getMaterialPlans
	 * @return array Ngay bào trì đặc biệt theo từng thiết bị
	 */
	private function getSpecialDateByEquipForMaterialPlanReport($materialPlans)
	{
		$specialDateByEquip = array();

		foreach($materialPlans as $mp)
		{
			if($mp->SDIOID)
			{
				if(!isset($specialDateByEquip[$mp->EQIOID]))
				{
					$specialDateByEquip[$mp->EQIOID] = array();
				}

				if(!in_array($mp->NgayDacBiet, $specialDateByEquip[$mp->EQIOID]))
				{
					$specialDateByEquip[$mp->EQIOID][] = $mp->NgayDacBiet;
				}
			}
		}
		return $specialDateByEquip;
	}

	// @todo: doi voi chi so, can dua vao dinh muc thiet bi de tinh ra thoi gian hoat dong
	// tinh ra thoi gian bao tri tiep theo
	// @todo: Kiem tra thiet bi co ngung hoat dong trong ngay khong?
	// @todo: Kiem tra thiet bi co ke hoach dung may trong ngay khong?
	// @todo: Kiem tra xem ke hoach co con hieu luc trong ngay hay không?
	// @todo: Can tinh ca ky lap vao phan tinh ky
	private function getMaterialPlanReportData($mSDate, $mEDate, $locIOID , $eqGroupIOID , $eqTypeIOID)
	{
		// Get plans
		$maintainModel = new Qss_Model_Extra_Maintenance();
		$materialPlans = $maintainModel->getMaterialPlans($mSDate, $mEDate, $locIOID , $eqGroupIOID , $eqTypeIOID);

		$specialDateByEquip = $this->getSpecialDateByEquipForMaterialPlanReport($materialPlans);
		$retval = array();
		$oldEqIOID = '';
		$oldComIOID  = '';

		$cEDate = date_create($mEDate);

		foreach ($materialPlans as $mp)
		{
			// Khoi tao mang
			if(!isset($retval[$mp->IIOID]))
			{
				$retval[$mp->IIOID]['Code'] = $mp->MaSanPham;
				$retval[$mp->IIOID]['Name'] = $mp->TenSanPham;
				$retval[$mp->IIOID]['UOM'] = $mp->DonViTinh;
				$retval[$mp->IIOID]['Inventory'] = $mp->TonKho;
				$retval[$mp->IIOID]['Eqs']  = array();
			}

			if(!isset($retval[$mp->IIOID]['Eqs'][$mp->EQIOID]))
			{
				$retval[$mp->IIOID]['Eqs'][$mp->EQIOID]['Code'] = $mp->MaThietBi;
				$retval[$mp->IIOID]['Eqs'][$mp->EQIOID]['Name'] = $mp->TenThietBi;
				$retval[$mp->IIOID]['Eqs'][$mp->EQIOID]['Coms'] = array();
			}

			if(!isset($retval[$mp->IIOID]['Eqs'][$mp->EQIOID]['Coms'][$mp->CIOID]))
			{
				$retval[$mp->IIOID]['Eqs'][$mp->EQIOID]['Coms'][$mp->CIOID]['Com'] = $mp->BoPhan;
				$retval[$mp->IIOID]['Eqs'][$mp->EQIOID]['Coms'][$mp->CIOID]['Pos'] = $mp->ViTri;
				$retval[$mp->IIOID]['Eqs'][$mp->EQIOID]['Coms'][$mp->CIOID]['Qty'] = 0;
			}

			// Lay vat tu theo bao tri theo ngay dac biet
			if($mp->SDIOID)
			{
				$retval[$mp->IIOID]['Eqs'][$mp->EQIOID]['Coms'][$mp->CIOID]['Qty'] += $mp->SoLuong;
			}

			// Lay vat tu theo ke hoach co tru cho cac ngay dac biet cua tung thiet bi

			$cSDate = date_create($mSDate);

			if($oldEqIOID  != $mp->EQIOID || $oldComIOID != $mp->CIOID)
			{
				while($cSDate <= $cEDate)
				{
					$day     = $cSDate->format('d');
					$month   = $cSDate->format('m');
					$weekday = $cSDate->format('w');
					$date    = $cSDate->format('Y-m-d');

					if(
					(
					(
					$mp->MaKy == Qss_Lib_Extra_Const::PERIOD_TYPE_DAILY
					)
					|| (
					$mp->MaKy == Qss_Lib_Extra_Const::PERIOD_TYPE_MONTHLY
					&& $day == $mp->Ngay
					)
					|| (
					$mp->MaKy == Qss_Lib_Extra_Const::PERIOD_TYPE_WEEKLY
					&& $day == $mp->NgayThu
					)
					|| (
					$mp->MaKy == Qss_Lib_Extra_Const::PERIOD_TYPE_YEARLY
					&& $day == $mp->NgayThu && $month == $mp->Thang
					)
					)
					&&
					(
					!isset($specialDateByEquip[$mp->EQIOID])
					|| !in_array($date, $specialDateByEquip[$mp->EQIOID])
					)
					)
					{
						$retval[$mp->IIOID]['Eqs'][$mp->EQIOID]['Coms'][$mp->CIOID]['Qty'] += $mp->SoLuong;
					}

					$cSDate = Qss_Lib_Date::add_date($cSDate, 1);
				}
			}

			$oldEqIOID  = $mp->EQIOID;
			$oldComIOID = $mp->CIOID;
		}

		return $retval;
	}

	public function maintainMaterialplan1Action()
	{
		// Hien thi vat tu tieu hao gop theo vat tu
		// Get Filter
		$sDate         = $this->params->requests->getParam('start');
		$eDate         = $this->params->requests->getParam('end');
		$eqGroupIOID   = $this->params->requests->getParam('group');
		$eqTypeIOID    = $this->params->requests->getParam('type');
		$locIOID       = $this->params->requests->getParam('location');

		// Get time
		$mSDate        = Qss_Lib_Date::displaytomysql($sDate);
		$mEDate        = Qss_Lib_Date::displaytomysql($eDate);
		//$mEDate        = Qss_Lib_Extra::getEndDate($mSDate, $mEDate);
		//$eDate         = Qss_Lib_Date::mysqltodisplay($mEDate);

		$this->html->print = $this->getMaterialPlanReportData(
		$mSDate, $mEDate, $locIOID, $eqGroupIOID, $eqTypeIOID
		);
		$this->html->start = $sDate;
		$this->html->end   = $eDate;

		//echo '-------------------------';
		//echo '<pre>'; print_r($retval); die;


		/*
		 if (Qss_Lib_Extra::checkFieldExists('ODanhSachThietBi', 'Ref_LoaiThietBi'))
		 {
		 $filter = array('order' => true
		 , 'loc' => $this->_params['location']
		 , 'eqgroup' => $this->_params['group']
		 , 'eqtype' => $this->_params['type']);
		 } else
		 {
		 $filter = array('order' => true
		 , 'loc' => $this->_params['location']
		 , 'eqgroup' => $this->_params['group']
		 );
		 }
		 $periodWorkOrederRequire = $this->_model->getRequireMaterialAndSparePart($filter); // Vat tu va phu tung yeu cau
		 $temp = array();
		 $print = array(); // Mang dung de chiet xuat bao cao
		 $end = Qss_Lib_Extra::getEndDate($this->_params['start'], $this->_params['end'], 'D'); // Ngay ket thuc da duoc gioi han
		 $startFirst = date_create($this->_params['start']);
		 $endFirst = date_create($end);
		 $microStart = strtotime($this->_params['start']);
		 $microEnd = strtotime($this->_params['end']);
		 //echo '<pre>'; print_r($periodWorkOrederRequire); die;

		 foreach ($periodWorkOrederRequire as $item)
		 {
		 $microDate = strtotime($item->NgayBTKDK);
		 if ($item->NgayBTKDK && ($microStart <= $microDate) && ($microDate <= $microEnd))
		 {
		 if ($item->VTIOID)
		 {
		 $temp['Ngay'][$item->NgayBTKDK][$item->BTIOID][$item->RefMaterial]['Code'] = $item->MaVatTu;
		 $temp['Ngay'][$item->NgayBTKDK][$item->BTIOID][$item->RefMaterial]['Name'] = $item->TenVatTu;
		 $temp['Ngay'][$item->NgayBTKDK][$item->BTIOID][$item->RefMaterial]['Qty'] = $item->SoLuongVatTu;
		 $temp['Ngay'][$item->NgayBTKDK][$item->BTIOID][$item->RefMaterial]['Attr'] = $item->ThuocTinhVatTu;
		 $temp['Ngay'][$item->NgayBTKDK][$item->BTIOID][$item->RefMaterial]['Uom'] = $item->DonViTinhVatTu;
		 $temp['Ngay'][$item->NgayBTKDK][$item->BTIOID][$item->RefMaterial]['Type'] = 1;
		 }

		 if ($item->PTIOID)
		 {
		 $temp['Ngay'][$item->NgayBTKDK][$item->BTIOID][$item->RefSparePart]['Code'] = $item->MaPhuTung;
		 $temp['Ngay'][$item->NgayBTKDK][$item->BTIOID][$item->RefSparePart]['Name'] = $item->TenPhuTung;
		 $temp['Ngay'][$item->NgayBTKDK][$item->BTIOID][$item->RefSparePart]['Qty'] = 1;
		 $temp['Ngay'][$item->NgayBTKDK][$item->BTIOID][$item->RefSparePart]['Attr'] = '';
		 $temp['Ngay'][$item->NgayBTKDK][$item->BTIOID][$item->RefSparePart]['Uom'] = $item->DonViTinhPhuTung;
		 $temp['Ngay'][$item->NgayBTKDK][$item->BTIOID][$item->RefSparePart]['Type'] = 2;
		 }
		 }

		 if ($item->MaKy != 'S')
		 {
		 if($item->Interval > 1)
		 {

		 }

		 if ($item->VTIOID)
		 {
		 $temp['Ky'][$item->MaKy][$item->BTIOID][$item->RefMaterial]['Code'] = $item->MaVatTu;
		 $temp['Ky'][$item->MaKy][$item->BTIOID][$item->RefMaterial]['Start'] = $item->NgayBatDau;
		 $temp['Ky'][$item->MaKy][$item->BTIOID][$item->RefMaterial]['End'] = $item->NgayKetThuc;
		 $temp['Ky'][$item->MaKy][$item->BTIOID][$item->RefMaterial]['Interval'] = $item->Interval;
		 $temp['Ky'][$item->MaKy][$item->BTIOID][$item->RefMaterial]['Name'] = $item->TenVatTu;
		 $temp['Ky'][$item->MaKy][$item->BTIOID][$item->RefMaterial]['Qty'] = $item->SoLuongVatTu;
		 $temp['Ky'][$item->MaKy][$item->BTIOID][$item->RefMaterial]['Attr'] = $item->ThuocTinhVatTu;
		 $temp['Ky'][$item->MaKy][$item->BTIOID][$item->RefMaterial]['Uom'] = $item->DonViTinhVatTu;
		 $temp['Ky'][$item->MaKy][$item->BTIOID][$item->RefMaterial]['Type'] = 1;
		 $temp['Ky'][$item->MaKy][$item->BTIOID][$item->RefMaterial]['Period'] = $item->MaKy;
		 $temp['Ky'][$item->MaKy][$item->BTIOID][$item->RefMaterial]['Weekday'] = $item->GiaTriThu;
		 $temp['Ky'][$item->MaKy][$item->BTIOID][$item->RefMaterial]['Day'] = $item->Ngay;
		 $temp['Ky'][$item->MaKy][$item->BTIOID][$item->RefMaterial]['Month'] = $item->Thang;
		 }

		 if ($item->PTIOID)
		 {
		 $temp['Ky'][$item->MaKy][$item->BTIOID][$item->RefSparePart]['Code'] = $item->MaPhuTung;
		 $temp['Ky'][$item->MaKy][$item->BTIOID][$item->RefSparePart]['Start'] = $item->NgayBatDau;
		 $temp['Ky'][$item->MaKy][$item->BTIOID][$item->RefSparePart]['End'] = $item->NgayKetThuc;
		 $temp['Ky'][$item->MaKy][$item->BTIOID][$item->RefSparePart]['Interval'] = $item->Interval;
		 $temp['Ky'][$item->MaKy][$item->BTIOID][$item->RefSparePart]['Name'] = $item->TenPhuTung;
		 $temp['Ky'][$item->MaKy][$item->BTIOID][$item->RefSparePart]['Qty'] = 1;
		 $temp['Ky'][$item->MaKy][$item->BTIOID][$item->RefSparePart]['Attr'] = '';
		 $temp['Ky'][$item->MaKy][$item->BTIOID][$item->RefSparePart]['Uom'] = $item->DonViTinhPhuTung;
		 $temp['Ky'][$item->MaKy][$item->BTIOID][$item->RefSparePart]['Type'] = 2;
		 $temp['Ky'][$item->MaKy][$item->BTIOID][$item->RefSparePart]['Period'] = $item->MaKy;
		 $temp['Ky'][$item->MaKy][$item->BTIOID][$item->RefSparePart]['Weekday'] = $item->GiaTriThu;
		 $temp['Ky'][$item->MaKy][$item->BTIOID][$item->RefSparePart]['Day'] = $item->Ngay;
		 $temp['Ky'][$item->MaKy][$item->BTIOID][$item->RefSparePart]['Month'] = $item->Thang;
		 }
		 }
		 }


		 foreach ($temp['Ngay'] as $date => $wo)
		 {
		 foreach ($wo as $woid => $rid)
		 {
		 foreach ($rid as $id => $info)
		 {
		 if (!isset($print[$date][$id]))
		 {
		 $print[$date][$id] = $info;
		 } else
		 {
		 $print[$date][$id]['Qty'] += $info['Qty'];
		 }
		 }
		 }
		 }

		 $tempKy = array();
		 foreach ($temp['Ky'] as $period => $wo)
		 {
		 foreach ($wo as $woid => $rid)
		 {
		 foreach ($rid as $id => $info)
		 {

		 $start = $startFirst;
		 while ($start <= $endFirst)
		 {
		 $day = $start->format('d');
		 $weekday = $start->format('w');
		 $month = $start->format('m');
		 $startToDate = $start->format('Y-m-d');

		 //$datediv = Qss_Lib_Date::divDate($info['Start'], $startToDate);

		 // Neu chua co bao tri cua san pham voi loai bao tri cung ngay thi them dong
		 if (!isset($print[$startToDate][$id]))
		 {
		 if (($info['Period'] == 'D')
		 || ($info['Period'] == 'M' && $day == $info['Day'])
		 || ($info['Period'] == 'W' && $weekday == $info['Weekday'])
		 || ($info['Period'] == 'Y' && $day == $info['Day'] && $month == $info['Month']))
		 {
		 if (!isset($tempKy[$startToDate][$id]))
		 {
		 $tempKy[$startToDate][$id] = $info;
		 } else
		 {
		 $tempKy[$startToDate][$id]['Qty'] += $info['Qty'];
		 }
		 }
		 }
		 $start = Qss_Lib_Date::add_date($start, 1);
		 }
		 }
		 }
		 }
		 $print = array_merge($print, $tempKy);

		 //echo '<pre>'; print_r($print); die;

		 $printLast = array();
		 foreach ($print as $date => $itemID)
		 {
		 foreach ($itemID as $id => $info)
		 {
		 if ($info['Type'] == 1) // Vat tu
		 {
		 if (!isset($printLast['Material'][$id]))
		 {
		 $printLast['Material'][$id] = $info;
		 } else
		 {
		 $printLast['Material'][$id]['Qty'] += $info['Qty'];
		 }
		 } else
		 {
		 if (!isset($printLast['SparePart'][$id]))
		 {
		 $printLast['SparePart'][$id] = $info;
		 } else
		 {
		 $printLast['SparePart'][$id]['Qty'] += $info['Qty'];
		 }
		 }
		 }
		 }
		 $this->html->print = $printLast;
		 $this->html->start = $this->_params['start'];
		 $this->html->end = $this->_params['end'];
		 *
		 */




	}


	public function breakdownCauseAction()
	{

	}

	public function breakdownCause1Action()
	{
		$downtimeModel       = new Qss_Model_Maintenance_Breakdown();
		$start               = $this->params->requests->getParam('start', '');
		$end                 = $this->params->requests->getParam('end', '');

		$this->html->start   = $start;
		$this->html->end     = $end;
		$this->html->reports = $downtimeModel->getDowntimeByCause(
		Qss_Lib_Date::displaytomysql($start),
		Qss_Lib_Date::displaytomysql($end),
		$this->params->requests->getParam('location', 0),
		$this->params->requests->getParam('equipment', 0),
		$this->params->requests->getParam('eqgroup', 0),
		$this->params->requests->getParam('eqtype', 0),
		$this->params->requests->getParam('reason', 0)
		);

	}

	public function breakdownMtbfmttrAction()
	{

	}

	public function breakdownMtbfmttr1Action()
	{
		$start      = $this->params->requests->getParam('start');
		$end        = $this->params->requests->getParam('end');
		$startMysql = Qss_Lib_Date::displaytomysql($start);
		$endMysql   = Qss_Lib_Date::displaytomysql($end);
		$eqOrLoc    = $this->params->requests->getParam('equipment');
		$data       = $this->_model->getMTTRAndMTBF(
		$startMysql,
		$endMysql,
		$this->params->requests->getParam('location', 0),
		$this->params->requests->getParam('group', 0),
		$this->params->requests->getParam('type', 0),
		$this->params->requests->getParam('equipment', 0)
		);

		//echo '<pre>'; print_r($data); die;
		// *****************************************************************
		// === Lay lich lam viec cua thiet bi
		// *****************************************************************
		$workingCalendarsArr = array();
		$refEquipArr = array();

		foreach ($data as $item)
		{
			// lay lich lam viec cua cac thiet bi
			if (!in_array($item->Ref_LichLamViec, $workingCalendarsArr))
			{
				$workingCalendarsArr[] = $item->Ref_LichLamViec;
			}

			// lay arr ref ma thiet bi
			if (!in_array($item->Ref_MaThietBi, $refEquipArr))
			{
				$refEquipArr[] = $item->Ref_MaThietBi;
			}
		}


		// 		// *****************************************************************
		// 		// === Tong thoi gian lam viec cua thiet bi tinh theo ky
		// 		// *****************************************************************
		// 		$totalWorkingHoursArr = array(); //Qss_Lib_Extra::getWorkingHoursOfManyCalendar($workingCalendarsArr
		// 		//      , Qss_Lib_Date::displaytomysql($this->_params['start'])
		// 		//      , Qss_Lib_Date::displaytomysql($this->_params['end']));


		// 		$totalWorkingHoursArr[] = Qss_Lib_Extra::getTotalWCal($workingCalendarsArr, $startMysql, $endMysql);


		$downtime = $this->getDowntime($startMysql, $endMysql, '', $refEquipArr, true);


		$this->html->downtime  = $downtime;
		$this->html->totalWCal = Qss_Lib_Extra::getTotalWCal($workingCalendarsArr, $startMysql, $endMysql);;
		$this->html->report    = $data;
		$this->html->start     = $start;
		$this->html->end       = $end;

	}

	public function breakdownParetoAction()
	{
		//	$this->v_fCheckRightsOnForm(155);

	}

	public function breakdownPareto1Action()
	{
		$breakdownModel = new Qss_Model_Maintenance_Breakdown();
		$locIOID        = $this->params->requests->getParam('location', 0);
		$eqGroupIOID    = $this->params->requests->getParam('group', 0);
		$eqTypeIOID     = $this->params->requests->getParam('type', 0);
		$eqIOID         = $this->params->requests->getParam('equipment', 0);
		$start          = $this->params->requests->getParam('start', '');
		$end            = $this->params->requests->getParam('end', '');


		$this->html->cause = $breakdownModel->countBreakdownForParetoChart(
		Qss_Lib_Date::displaytomysql($start)
		, Qss_Lib_Date::displaytomysql($end)
		, $eqIOID
		, $eqTypeIOID
		, $eqGroupIOID
		, $locIOID);
		$this->html->start = $start;
		$this->html->end = $end;

	}

	public function breakdownPeriodAction()
	{

	}

	public function breakdownPeriod1Action()
	{
		$breakdownModel = new Qss_Model_Maintenance_Breakdown();
		$locIOID        = $this->params->requests->getParam('location', 0);
		$eqGroupIOID    = $this->params->requests->getParam('group', 0);
		$eqTypeIOID     = $this->params->requests->getParam('type', 0);
		$eqIOID         = $this->params->requests->getParam('equipment', 0);
		$period         = $this->params->requests->getParam('period', 'D');
		$start          = $this->params->requests->getParam('start', '');
		$end            = $this->params->requests->getParam('end', '');
		$startMysql     = Qss_Lib_Date::displaytomysql($start);
		$endMysql       = Qss_Lib_Date::displaytomysql($end);
		$end            = Qss_Lib_Extra::getEndDate($startMysql, $endMysql, $period);

		$this->html->period    = $period;
		$this->html->startDate = $start;
		$this->html->endDate   = Qss_Lib_Date::mysqltodisplay($end);
		$this->html->time      = Qss_Lib_Extra::displayRangeDate($startMysql, $end, $period);
		$this->html->breakdown = $breakdownModel->getDowntimeStatisticsByPeriod(
		$startMysql, $endMysql, $period, $eqIOID, $eqTypeIOID, $eqGroupIOID, $locIOID);
	}

	



	public function equipmentLocationAction()
	{
		//	$this->v_fCheckRightsOnForm(155);

	}

	public function equipmentLocation1Action()
	{
		// GET FILTER DATA
		// DIEU KIEN LOC THEO THOI GIAN
		$start          = $this->params->requests->getParam('start', '');
		$end            = $this->params->requests->getParam('end', '');
		// DIEU KIEN LOC THEO THIET BI
		$eqIOID         = $this->params->requests->getParam('equipment', 0);
		$eq             = $this->params->requests->getParam('equipmentStr', 0);
		// DIEU KIEN LOC THEO KHU VUC
		$locationIOID   = $this->params->requests->getParam('location', 0);
		$location       = $this->params->requests->getParam('locationStr', 0);
		// DIEU KIEN LOC THEO NHOM THIET BI
		$eqGroupIOID    = $this->params->requests->getParam('group', 0);
		$eqGroup        = $this->params->requests->getParam('groupStr', 0);
		// DIEU KIEN LOC THEO LOAI THIET BI
		$eqTypeIOID     = $this->params->requests->getParam('type', 0);
		$eqType         = $this->params->requests->getParam('typeStr', 0);
		$all            = $this->params->requests->getParam('all', 0);
		$projectIOID    = $this->params->requests->getParam('project', 0);
		$employeeIOID   = $this->params->requests->getParam('employee', 0);

		$equipModel     = new Qss_Model_Maintenance_Equipment();


		if ($start && $end)
		{
			// LAY LICH SU CAI DAT THIET BI THEO CAC DIEU KIEN LOC
			$equipments= $equipModel->getInstallHistoryOfEquipmentByLocation(
			Qss_Lib_Date::displaytomysql($start)
			, Qss_Lib_Date::displaytomysql($end)
			, $eqIOID
			, $locationIOID
			, $eqGroupIOID
			, $eqTypeIOID
			, $projectIOID
			, $employeeIOID
			, $all);
				
			// DANH SACH LICH SU CAI DAT CAC THIET BI
			$this->html->eqs          = $this->getEquipmentForIntallHistoryReport($equipments, $start, $end);
			// THONG SO CUA CAC THIET BI
			//$this->html->param        = $this->getParamOfEquip($equipments);
			// LOC THEO KHU VUC
			$this->html->locationIOID = $locationIOID;
			$this->html->location     = $location;
			// LOC THEO NHOM THIET BI
			$this->html->eqGroupIOID  = $eqGroupIOID;
			$this->html->eqGroup      = $eqGroup;
			// LOC THEO LOAI THIET BI
			$this->html->eqTypeIOID   = $eqTypeIOID;
			$this->html->eqType       = $eqType;
			// LOC THEO THIET BI
			$this->html->eqIOID       = $eqIOID;
			$this->html->eq           = $eq;
			// LOC THEO THOI GIAN
			$this->html->start        = $start;
			$this->html->end          = $end;
		}
		else
		{
			$this->setHtml('Date is required!');
		}
	}


	public function equipmentCostcenterAction()
	{

	}

	public function equipmentCostcenter1Action()
	{
		$eqModel        = new Qss_Model_Extra_Equip();
		$mEqModel       = new Qss_Model_Maintenance_Equipment();
		// DIEU KIEN LOC THEO THIET BI
		$eqIOID         = $this->params->requests->getParam('equip', 0);
		$eq             = $this->params->requests->getParam('equipmentStr', 0);
		// DIEU KIEN LOC THEO KHU VUC
		$locationIOID   = $this->params->requests->getParam('location', 0);
		$location       = $this->params->requests->getParam('locationStr', 0);
		// DIEU KIEN LOC THEO NHOM THIET BI
		$eqGroupIOID    = $this->params->requests->getParam('group', 0);
		$eqGroup        = $this->params->requests->getParam('groupStr', 0);
		// DIEU KIEN LOC THEO LOAI THIET BI
		$eqTypeIOID     = $this->params->requests->getParam('type', 0);
		$eqType         = $this->params->requests->getParam('typeStr', 0);
		// DIEU KIEN LOC THEO TRUNG TAM CHI PHI
		$costcenterIOID = $this->params->requests->getParam('costcenter', 0);
		$costcenter     = $this->params->requests->getParam('costcenterStr', 0);

		$sort           = $this->params->requests->getParam('sort', 0);

		$eqArr = array();
		if($eqIOID)
		{
			$eqArr = array($eqIOID);
		}


		$eqs = $eqModel->getEquipments(
		$eqArr,
		$locationIOID,
		$costcenterIOID,
		$eqGroupIOID,
		$eqTypeIOID,
		$sort
		);

		// Lấy ioid danh sách thiết bị
		$resultEqIOIDArr = array(0);
		foreach($eqs as $e)
		{
			$resultEqIOIDArr[] = $e->HPEQIOID;

			if($e->CEQIOID)
			$resultEqIOIDArr[] = $e->CEQIOID;
		}

		$lichdieudong    = $mEqModel->getMoveCalByDateOfEquips(date('Y-m-d'), $resultEqIOIDArr);
		$lichdieudongArr = array();

		foreach($lichdieudong as $item)
		{
			$lichdieudongArr[$item->Ref_MaThietBi]['Project'] = $item->DuAn;
		}

		$this->html->lichdieudong    = $lichdieudongArr;
		$this->html->planMaint       = $this->getCalibration($resultEqIOIDArr);
		$this->html->eqs             = $eqs;
		$this->html->tech            = $this->params->requests->getParam('tech', 0);
		$this->html->sort            = $sort;
		// LOC THEO KHU VUC
		$this->html->locationIOID    = $locationIOID;
		$this->html->location        = $location;
		// LOC THEO NHOM THIET BI
		$this->html->eqGroupIOID     = $eqGroupIOID;
		$this->html->eqGroup         = $eqGroup;
		// LOC THEO LOAI THIET BI
		$this->html->eqTypeIOID      = $eqTypeIOID;
		$this->html->eqType          = $eqType;
		// LOC THEO THIET BI
		$this->html->eqIOID          = $eqIOID;
		$this->html->eq              = $eq;
		// LOC THEO TTCP
		$this->html->costcenterIOID  = $costcenterIOID;
		$this->html->costcenter      = $costcenter;
	}

	public function equipmentCostcenter2Action()
	{
		//$eqModel        = new Qss_Model_Extra_Equip();
		$mEqModel       = new Qss_Model_Maintenance_Equipment();
		// DIEU KIEN LOC THEO THIET BI
		$eqIOID         = $this->params->requests->getParam('equip', 0);
		$eq             = $this->params->requests->getParam('equipmentStr', 0);
		// DIEU KIEN LOC THEO KHU VUC
		$locationIOID   = $this->params->requests->getParam('location', 0);
		$location       = $this->params->requests->getParam('locationStr', 0);
		// DIEU KIEN LOC THEO NHOM THIET BI
		$eqGroupIOID    = $this->params->requests->getParam('group', 0);
		$eqGroup        = $this->params->requests->getParam('groupStr', 0);
		// DIEU KIEN LOC THEO LOAI THIET BI
		$eqTypeIOID     = $this->params->requests->getParam('type', 0);
		$eqType         = $this->params->requests->getParam('typeStr', 0);
		// DIEU KIEN LOC THEO TRUNG TAM CHI PHI
		$costcenterIOID = $this->params->requests->getParam('costcenter', 0);
		$costcenter     = $this->params->requests->getParam('costcenterStr', 0);

        $start = $this->params->requests->getParam('start', '');
        $end   = $this->params->requests->getParam('end', '');

		$sort           = $this->params->requests->getParam('sort', 0);

		$eqArr = array();
		if($eqIOID)
		{
			$eqArr = array($eqIOID);
		}

        $eqListModel = new Qss_Model_Maintenance_Equip_List();
        $eqListModel->setFilterByEqIOID($eqIOID);
        $eqListModel->setFilterByEqTypeIOID($eqTypeIOID);
        $eqListModel->setFilterByEqGroupIOID($eqGroupIOID);
        $eqListModel->setFilterByLocationIOID($locationIOID);
        if($start && $end)
        {
            $eqListModel->setFilterByBeginDate(Qss_Lib_Date::displaytomysql($start), Qss_Lib_Date::displaytomysql($end));
        }
        elseif($start)
        {
            $eqListModel->setFilterByBeginDate(Qss_Lib_Date::displaytomysql($start));
        }
        elseif($end)
        {
            $eqListModel->setFilterByBeginDate('', Qss_Lib_Date::displaytomysql($end));
        }

        $eqs = $eqListModel->getEquipsOrderByGroupAndTypeOfEquip();

		// Lấy ioid danh sách thiết bị
		$resultEqIOIDArr = array(0);
		foreach($eqs as $e)
		{
			$resultEqIOIDArr[] = $e->HPEQIOID;

			if($e->CEQIOID)
				$resultEqIOIDArr[] = $e->CEQIOID;
		}

		$lichdieudong    = $mEqModel->getMoveCalByDateOfEquips(date('Y-m-d'), $resultEqIOIDArr);
		$lichdieudongArr = array();

		foreach($lichdieudong as $item)
		{
			$lichdieudongArr[$item->Ref_MaThietBi]['Project'] = $item->DuAn;
		}

		$this->html->lichdieudong    = $lichdieudongArr;
		//$this->html->planMaint       = $this->getCalibration($resultEqIOIDArr);
		$this->html->eqs             = $eqs;
		$this->html->tech            = $this->params->requests->getParam('tech', 0);
		$this->html->sort            = $sort;
		// LOC THEO KHU VUC
		$this->html->locationIOID    = $locationIOID;
		$this->html->location        = $location;
		// LOC THEO NHOM THIET BI
		$this->html->eqGroupIOID     = $eqGroupIOID;
		$this->html->eqGroup         = $eqGroup;
		// LOC THEO LOAI THIET BI
		$this->html->eqTypeIOID      = $eqTypeIOID;
		$this->html->eqType          = $eqType;
		// LOC THEO THIET BI
		$this->html->eqIOID          = $eqIOID;
		$this->html->eq              = $eq;
		// LOC THEO TTCP
		$this->html->costcenterIOID  = $costcenterIOID;
		$this->html->costcenter      = $costcenter;
	}
	private function getCalibration($resultEqIOIDArr)
	{
		$workorderModel = new Qss_Model_Maintenance_Workorder();
		$planModel      = new Qss_Model_Maintenance_Plan();
		$plan           = $planModel->getCalibrationPlan($resultEqIOIDArr);
		$workorder      = $workorderModel->getCalibrationOrder($resultEqIOIDArr);
		$today          = date_create(date('Y-m-d'));
		$retval         = array();
		$planArr        = array();
		$excludeStatus  = array(1, 2);
		$dueDays        = 15;
	  
		//echo '<pre>'; print_r($plan); die;
	  
		// [Ref-Thietbi][status] + [past] + [next]
	  
		foreach($plan as $item)
		{
			$require      = date('Y-m-d');
			$requireMicro = Qss_Lib_Date::i_fMysql2Time(date('Y-m-d'));
			$next         = '';
			$today        = date('Y-m-d');
			$interval     = $item->LapLai?(int)$item->LapLai:1;
			$start        = $item->NgayBatDau?$item->NgayBatDau:$today;
			$end          = $item->NgayKetThuc?$item->NgayKetThuc:'';
			 
			// Kiem dinh da ket thuc khong lay kiem dinh nay
			if($end && Qss_Lib_Date::compareTwoDate($today, $end) == 1)
			{
				continue;
			}
			else
			{
				$next = '';
				$past = '';
				 
				$item->Thang = $item->Thang?( ((int)$item->Thang > 9) ? (int)$item->Thang : '0'.(int)$item->Thang ):'';
				$item->Ngay  = $item->Ngay?( ((int)$item->Ngay > 9) ? (int)$item->Ngay : '0'.(int)$item->Ngay ):'';
				 
				switch ($item->MaKy)
				{
					case 'D':
						// Tính số ngày từ ngày bắt đầu
						$diffDate = Qss_Lib_Date::divDate($start, $today);
						$sodu     = $diffDate%$interval;
						$next     = ($sodu)?date('Y-m-d',strtotime($today . ' + '.$sodu.' days')):$today;
						//$past     = date('Y-m-d',strtotime($next . ' - '.$interval.' days'));
						break;
	      
					case 'W':
						$diffWeek = Qss_Lib_Date::divDate($start, $today, 'W');
						$sodu     = $diffWeek%$interval;
						$next     = ($sodu)?date('W',strtotime($today . ' + '.$sodu.' weeks')):$today;
						$timetamp = $this->change_date($next, (int)$item->ThuMay);
						$next     = date('Y-m-d', $timetamp);
						$next     = (Qss_Lib_Date::compareTwoDate($next, $today) == -1)? date('Y-m-d',strtotime($next . ' + '.$interval.' weeks')): $next;
						//$past     = date('Y-m-d',strtotime($next . ' - '.$interval.' weeks'));
						break;

					case 'M':
						$diffMonth = Qss_Lib_Date::divDate($start, $today, 'MO');
						$sodu      = $diffMonth%$interval;
						$sodu      = (Qss_Lib_Date::compareTwoDate(date('Y-m-'.$item->Ngay), $today) == -1)? ($sodu + $interval): $sodu;
						$next      = ($sodu)?date('Y-m-'.$item->Ngay,strtotime($today . ' + '.$sodu.' months')):date('Y-m-'.$item->Ngay);
						//$past      = date('Y-m-'.$item->Ngay,strtotime($next . ' - '.$interval.' months'));
						break;

					case 'Y':
						$diffYear  = Qss_Lib_Date::divDate($start, $today, 'Y');
						$sodu      = $diffYear%$interval;
						$sodu      = (Qss_Lib_Date::compareTwoDate(date('Y-'.$item->Thang.'-'.$item->Ngay), $today) == -1)? ($sodu + $interval): $sodu;
						$next      = ($sodu)?date('Y-'.$item->Thang.'-'.$item->Ngay,strtotime($today . ' + '.$sodu.' years')):date('Y-'.$item->Thang.'-'.$item->Ngay);
						//$past      = date('Y-'.$item->Thang.'-'.$item->Ngay,strtotime($next . ' - '.$interval.' years'));
						break;
				}

				$planArr[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri]['name']   = $item->LoaiBaoTri;
				$planArr[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri]['next']   = $next;
				$planArr[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri]['past']   = '';
				$planArr[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri]['status'] = 0; // chua co tinh trang
			}
		}
	  
		// Kiem tra xem da co phieu bao tri cho ke hoach hay chua
		foreach($workorder AS $item)
		{
			/**
			 * + N/A : thiết bị không cần hiệu chuẩn
			 + Còn hạn : còn hiệu lực kiểm định đến > 15 ngày kiểm định kế tiếp theo thời hạn
			 + Hết hạn : Quá hạn kiểm định
			 + Sắp hết hạn : 15 ngày đổ lại tới hạn kiểm định kế tiếo
			 */
			// co phieu bao tri
			if(isset($planArr[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri]))
			{
				// Phieu bao tri da hoan thanh hoac dong
				if(!in_array($item->Status, $excludeStatus) )
				{
					$planArr[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri]['status'] = 1; // dung han
				}
				else // Phieu bao tri dang thuc hien
				{
					$planArr[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri]['past']   = $item->NgaySoSanh;
					$diff = Qss_Lib_Date::divDate($item->NgaySoSanh, $planArr[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri]['next']);
					if($diff > $dueDays)
					{
						$planArr[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri]['status'] = 2; // con han
					}
					elseif($diff < $dueDays && $diff > 0)
					{
						$planArr[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri]['status'] = 3; // sap het han
					}
					else
					{
						$planArr[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri]['status'] = 4; // qua han
					}
				}
			}
		}
		//echo '<pre>'; print_r($planArr); die;
		return $planArr;
	}

	function change_date($week_num, $day) {
		$timestamp    = strtotime(date('Y') . '-W' . $week_num . '-' . $day);
		return $timestamp;
	}


	/**
	 * @module: M757 - Tình trạng bảo hành thiết bị - params
	 */
	public function equipmentWarrantyAction()
	{

	}

	/**
	 * @module: M757 - Tình trạng bảo hành thiết bị - report
	 */
	public function equipmentWarranty1Action()
	{
		// DIEU KIEN LOC THEO KHU VUC
		$locationIOID   = $this->params->requests->getParam('location', 0);
		$location       = $this->params->requests->getParam('locationStr', 0);
		// DIEU KIEN LOC THEO NHOM THIET BI
		$eqGroupIOID    = $this->params->requests->getParam('group', 0);
		$eqGroup        = $this->params->requests->getParam('groupStr', 0);
		// DIEU KIEN LOC THEO LOAI THIET BI
		$eqTypeIOID     = $this->params->requests->getParam('type', 0);
		$eqType         = $this->params->requests->getParam('typeStr', 0);
			
		$eqModel        = new Qss_Model_Extra_Equip();
		$retData        = array(); // mang bao hanh
		$wIndex         = 0;
		$nowMicro       = Qss_Lib_Date::i_fString2Time(date('d-m-Y'));
		$data           = $eqModel->getEquipments(
		array(),
		$locationIOID,
		0,
		$eqGroupIOID,
		$eqTypeIOID
		);


		// Lay mang du lieu bao hanh
		foreach ($data as $d)
		{
			$retData[$wIndex]['ECode']  = $d->MaThietBi;
			$retData[$wIndex]['EType']  = $d->LoaiThietBi;
			$retData[$wIndex]['EGroup'] = $d->NhomThietBi;
			$retData[$wIndex]['EDate']  = $d->HanBaoHanh?Qss_Lib_Date::mysqltodisplay($d->HanBaoHanh):'';
			$retData[$wIndex]['PCode']  = $d->HangBaoHanh;
			$retData[$wIndex]['PName']  = $d->HangBaoHanh;

			// Kiem tra ngay thang
			if ($retData[$wIndex]['EDate'])
			{
				$dueMicro = Qss_Lib_Date::i_fString2Time($retData[$wIndex]['EDate']);

				if ($nowMicro == $dueMicro)
				{
					$retData[$wIndex]['EStatus'] = 1; // Chính hôm nay đây
					$retData[$wIndex]['EClass'] = 'now_16';
				} elseif ($nowMicro < $dueMicro)
				{
					$interval = floor(($dueMicro - $nowMicro) / (60 * 60 * 24));
					if ($interval <= 7)
					{
						$retData[$wIndex]['EStatus'] = 2; // due soon
						$retData[$wIndex]['EClass'] = 'duesoon_16';
					} else
					{
						$retData[$wIndex]['EStatus'] = 3; // normal
						$retData[$wIndex]['EClass'] = 'normal_16';
					}
				} elseif ($nowMicro > $dueMicro)
				{
					$retData[$wIndex]['EStatus'] = 4; // over due
					$retData[$wIndex]['EClass'] = 'overdue_16';
				}
			} else // Neu ko co han bao hanh o dong chinh
			{
				$retData[$wIndex]['EStatus'] = 0; // không có hạn bảo hành
				$retData[$wIndex]['EClass'] = 'question_16';
			}
			$wIndex++;
		}

		$this->html->report       = $retData;
		// LOC THEO KHU VUC
		$this->html->locationIOID = $locationIOID;
		$this->html->location     = $location;
		// LOC THEO NHOM THIET BI
		$this->html->eqGroupIOID  = $eqGroupIOID;
		$this->html->eqGroup      = $eqGroup;
		// LOC THEO LOAI THIET BI
		$this->html->eqTypeIOID   = $eqTypeIOID;
		$this->html->eqType       = $eqType;
	}

	/**
	 * @module Bao cao tong hop theo doi tuong
	 * @path report/maintenance/cost/object
	 */
	public function costObjectAction()
	{
        $eqs         = $this->_common->getTable(array('*'), 'ODanhSachThietBi', array(), array('MaThietBi'), 100000);
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

	// @todo: chua tinh chi phi sua chua
	public function costObject1Action()
	{
		$report = array();
		$start  = $this->params->requests->getParam('start', '');
		$end    = $this->params->requests->getParam('end', '');
		$eqs    = $this->params->requests->getParam('eqs', array());
		$floc   = $this->params->requests->getParam('flocation', 0);
		$fwc    = $this->params->requests->getParam('fworkcenter', 0);
		$feq    = $this->params->requests->getParam('fequip', 0);

//		$end    = Qss_Lib_Extra::getEndDate(Qss_Lib_Date::displaytomysql($start)
//		, Qss_Lib_Date::displaytomysql($end)
//		, 'D'
//		, Qss_Lib_Extra_Const::$DATE_LIMIT);

		$filter          = array();
		$filter['start'] = Qss_Lib_Date::displaytomysql($start);
		$filter['end']   = $end;
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
		//echo '<pre>'; print_r($report); die;
		$this->html->report = $report;
		$this->html->start  = $start;
		$this->html->end    = Qss_Lib_Date::mysqltodisplay($end);
	}

	public function costAnalysisAction()
	{

	}

	public function costAnalysis1Action()
	{
		$start = Qss_Lib_Date::displaytomysql($this->_params['start']);
		$end = Qss_Lib_Date::displaytomysql($this->_params['end']);
		$end = Qss_Lib_Extra::getEndDate($start, $end, $this->_params['period']);
		$period = $this->params->requests->getParam('period');
		$aTime = Qss_Lib_Extra::displayRangeDate($start, $end, $this->_params['period']);


		$this->html->time = $aTime;
		$this->html->period = $period;
		$this->html->startDate = $this->_params['start'];
		$this->html->endDate = Qss_Lib_Date::mysqltodisplay($end);
		$this->html->groupBy = $this->_params['groupBy'];
		$this->html->cost = $this->_model->getCostAnalysis($start, $end
		, $period, $this->_params['groupBy']);

	}

	/* cost service hiện tại không được dùng */

	public function costServiceAction()
	{
		$suppliers = $this->_common->getTable(array('*'), 'ODoiTac', array('NhaCungCap' => 1)
		, array(), 'NO_LIMIT');
		$this->html->suppliers = $suppliers;

	}

	public function costService1Action()
	{
		$startDate = $this->params->requests->getParam('start');
		$endDate = $this->params->requests->getParam('end');
		$supplier = $this->params->requests->getParam('suppl');
		$this->html->start = $startDate;
		$this->html->end = $endDate;
		$this->html->cost = $this->_model->getServiceCosts(Qss_Lib_Date::displaytomysql($startDate)
		, Qss_Lib_Date::displaytomysql($endDate), $supplier);

	}

	
	/* #Move  */
	public function resourceEmpAction()
	{

	}
	/* #Move  */
	public function resourceEmp1Action()
	{
		$maint = new Qss_Model_Extra_Maintenance();
		$start = $this->params->requests->getParam('start', '');
		$end = $this->params->requests->getParam('end', '');
		$refWorkcenter = $this->params->requests->getParam('workcenter', 0);
		$emplTime = $maint->getWorkingHourByEmployee(Qss_Lib_Date::displaytomysql($start)
		, Qss_Lib_Date::displaytomysql($end), $refWorkcenter);
		$empl = $maint->resourceEmpReportGetEmployee($refWorkcenter);
		$eTime = array();
		//$this->_common->showTableStucture('OCongViecBaoTri');
		foreach ($emplTime as $et)
		{
			if ($et->Ref_Worker && $et->Time)
			{
				$eTime[$et->Ref_Worker]['Work'][$et->Ref_Work] = $et->Time;
			}
		}

		foreach ($empl as $e)
		{
			$eTime[$e->IOID]['Group']['ID'] = @(int) $e->DVIOID;
			$eTime[$e->IOID]['Group']['Code'] = $e->Ma;
			$eTime[$e->IOID]['Group']['Name'] = $e->Ten;
			$eTime[$e->IOID]['Emp']['Code'] = $e->MaNhanVien;
			$eTime[$e->IOID]['Emp']['Name'] = $e->TenNhanVien;
		}

		$this->html->works = $this->getMaintWork();
		$this->html->report = $eTime;
		$this->html->start = $start;
		$this->html->end = $end;

	}

	/* #Move  */
	/* Move to Maintenancereport/EmployeeController */
	public function resourceTimeAction()
	{

	}
	/* #Move  */
	public function resourceTime1Action()
	{
		$maint = new Qss_Model_Extra_Maintenance();
		$start = $this->params->requests->getParam('start', '');
		$end = $this->params->requests->getParam('end', '');
		$end = Qss_Lib_Extra::getEndDate($start, $end, 'D', Qss_Lib_Extra_Const::$DATE_LIMIT);
		$endDisplayDateFormat = Qss_Lib_Date::mysqltodisplay($end);

		$this->html->available = $this->getResouceTimeAvailableData($start, $endDisplayDateFormat);
		$this->html->plan = $this->getResouceTimePlanData($start, $endDisplayDateFormat);
		$this->html->works = $this->getMaintWork();
		$this->html->start = $start;
		$this->html->end = $endDisplayDateFormat;

	}

	/**
	 *
	 */
	/* #Move  */
	public function resourceWorkcenterAction()
	{

	}
	/* #Move  */
	public function resourceWorkcenter1Action()
	{
		// Lay cac tham so truyen len
		$start      = $this->params->requests->getParam('start', '');
		$end        = $this->params->requests->getParam('end', '');
		$workcenter = $this->params->requests->getParam('workcenter', '');
		$end        = Qss_Lib_Extra::getEndDate(
		Qss_Lib_Date::displaytomysql($start)
		, Qss_Lib_Date::displaytomysql($end)
		, 'D'
		, Qss_Lib_Extra_Const::$DATE_LIMIT);

		// Lay tong so gio yeu cau theo ke hoach
		$this->html->plan = $this->getResouceTimePlanDataForWorkcenterByDay(
		$start, $end, $workcenter);

		// Lay tong so gio theo so nhan vien lam viec
		$this->html->emp = $this->getResouceTimeAvailableDataForWorkcenterByDay(
		$start, $end, $workcenter);

		$this->html->date = Qss_Lib_Extra::displayRangeDate(
		Qss_Lib_Date::displaytomysql($start)
		, Qss_Lib_Date::displaytomysql($end), 'D');
	}

	/* #Move  */
	private function getMaintWork()
	{
//		$filter['module'] = 'OCongViecBaoTri';
//		$works = $this->_common->getDataset($filter);
        $mTable = Qss_Model_Db::Table('OCongViecBaoTri');
		$retval = array();

		foreach ($mTable->fetchAll() as $w)
		{
			$retval[$w->IOID] = $w;
		}
		return $retval;

	}

	/* #Move  */
	// @todo: Khong can group theo don vi lam viec co the tinh tong luon
	private function getResouceTimePlanDataForWorkcenterByDay($start, $end, $workcenter)
	{
		$maint = new Qss_Model_Extra_Maintenance();
		$countDate = 0;

		$startTmp = date_create($start);
		$endTmp = date_create($end);
		$workArr = array();

		while ($startTmp <= $endTmp)
		{
			if ($countDate == Qss_Lib_Extra_Const::$DATE_LIMIT['D']) // gioi han 60 ngay
			{
				break;
			}
			$countDate++;
			$date = $startTmp->format('Y-m-d');
			$plan = $maint->getMaintPlanWorkTime($date
			, array('workcenter'=>$workcenter, 'group'=>'wc'));

			$workArr[$date] = 0;
				
			foreach ($plan as $item)
			{
				if ($item->Work)
				{
					$workArr[$date] += $item->ThoiGian;
				}
			}
			$startTmp = Qss_Lib_Date::add_date($startTmp, 1); // add start one day
		}
		return $workArr;

	}

	/* #Move  */
	private function getResouceTimeAvailableDataForWorkcenterByDay($start, $end, $workcenter)
	{
		$RefWCal = array();
		$TimeByWC = array();
		$TimeByWCRet = array();

		$start = Qss_Lib_Date::displaytomysql($start);
		$end   = Qss_Lib_Date::displaytomysql($end);

		$maint = new Qss_Model_Extra_Maintenance();
		$empl  = $maint->getWorkingEmplFromWorkCenter($start, $end
		, $workcenter);
		//getWCalByDay

		// Lay ra lich lam viec cua nhan vien
		foreach($empl as $e)
		{
			if(!in_array($e->RefWCal, $RefWCal))
			{
				$RefWCal[] = $e->RefWCal;
			}
				
			// Tinh tong cac lich lam viec theo tung don vi
			// dung de xac dinh tung ngay don vi lam bao nhieu tg
			if(!isset($TimeByWC[$e->RefWC][$e->RefWCal]))
			{
				$TimeByWC[$e->RefWC][$e->RefWCal] = 1;
			}
			else
			{
				$TimeByWC[$e->RefWC][$e->RefWCal]++;
			}
		}

		// Lay thoi gian lam viec tung ngay theo lich lam viec
		$WCalByDay = Qss_Lib_Extra::getWCalByDay($RefWCal, $start, $end);
		//		echo '<pre>';
		//		print_r($TimeByWC);
		//		echo '<pre>';
		//		print_r($WCalByDay);
		//		die;

		// Lay tong thoi gian lam viec cua tung to bao tri
		$startTem = date_create($start);
		$endTem   = date_create($end);

		while ($startTem <= $endTem)
		{
			$startToDate = $startTem->format('Y-m-d'); // Ngay
			$TimeByWCRet[$startToDate] = 0;
				
			foreach($TimeByWC as $refWC1 => $Cal1)
			{

				foreach($Cal1 as $refWCal1 => $countWCal1)
				{
					if(isset($WCalByDay[$refWCal1][$startToDate]))
					{
						$TimeByWCRet[$startToDate] += $countWCal1
						* array_sum($WCalByDay[$refWCal1][$startToDate]);
					}
				}
				//$TimeByWCRet
			}
			$startTem = Qss_Lib_Date::add_date($startTem, 1);
		}
		return $TimeByWCRet;
	}

	/* #Move  */
	private function getResouceTimePlanData($start, $end)
	{
		$maint = new Qss_Model_Extra_Maintenance();
		$countDate = 0;

		$startTmp = date_create($start);
		$endTmp = date_create($end);
		$workArr = array();

		while ($startTmp <= $endTmp)
		{
			if ($countDate == Qss_Lib_Extra_Const::$DATE_LIMIT['D']) // gioi han 60 ngay
			{
				break;
			}
			$countDate++;

			$plan = $maint->getMaintPlanWorkTime($startTmp->format('Y-m-d'));
			foreach ($plan as $item)
			{
				if ($item->Work)
				{
					if (!isset($workArr[$item->Work]))
					$workArr[$item->Work] = 0;
					$workArr[$item->Work] += $item->ThoiGian;
				}
			}
			$startTmp = Qss_Lib_Date::add_date($startTmp, 1); // add start one day
		}
		return $workArr;

	}

	/* #Move  */
	private function getResouceTimeAvailableData($start, $end)
	{

		$maint = new Qss_Model_Extra_Maintenance();
		$EmplWorkCal = array();
		$TotalByWorkCal = array();
		$totalTimeEmpl = 0;
		$totalTimeByWCal = array();
		$work = array(); // cong viec
		$maxTimeByWork = array(); // Thoi gian lon nhat theo lich lam viec(Cong tong lich lam viec cua cong viec)
		$minTimeByWork = array(); // Thoi gian nho nhat theo lich lam viec(lay lich lam viec nho nhat ung voi cong viec)
		//$timeByWork       = array(); // Cac lich lam viec cho tung ky nang
		$minTimeByWorkTemp = array();


		// ***********************************************************************************************
		$empl = $maint->getWorkingEmpl(); // Lay nhan vien va cong viec cua nhan vien con hoat dong
		$emplTemp = array();
		$oldEmp = '';
		$onlyOneWork = 0; // Dung de tinh min, neu lam mot duy nhat cong viec => min, lon hon => min =0
		// Lay mang lich lam viec khong lap lai cua nhan vien $EmplWorkCal
		// Dem so luong moi lich lam viec $TotalByWorkCal
		foreach ($empl as $ep)
		{
			if ($ep->Ref_LichLamViec)
			{
				$emplTemp[$ep->IFID] = $ep;
				if ($oldEmp == $ep->IFID)
				{
					unset($emplTemp[$ep->IFID]);
				}

				// dem theo lich lam viec
				if ($oldEmp != $ep->IFID)
				{
					if (isset($TotalByWorkCal[$ep->Ref_LichLamViec]))
					{
						$TotalByWorkCal[$ep->Ref_LichLamViec] += 1;
					} else
					{
						$TotalByWorkCal[$ep->Ref_LichLamViec] = 1;
					}
				}


				// Lay mang lich lam viec
				if (!in_array($ep->Ref_LichLamViec, $EmplWorkCal))
				{
					$EmplWorkCal[] = $ep->Ref_LichLamViec;
				}

				// Dem theo cong viec + lich lam viec
				if (isset($work[$ep->Ref_LichLamViec][$ep->Ref_KyNang]))
				{
					$work[$ep->Ref_LichLamViec][$ep->Ref_KyNang] += 1;
				} else
				{
					$work[$ep->Ref_LichLamViec][$ep->Ref_KyNang] = 1;
				}
			}
			$oldEmp = $ep->IFID;
		}

		// thoi gian theo lich lam viec $wCal
		$wCal = Qss_Lib_Extra::getTotalWCal($EmplWorkCal
		, Qss_Lib_Date::displaytomysql($start)
		, Qss_Lib_Date::displaytomysql($end));

		// Tong thoi gian cua tat ca nhan vien co the lam $totalTimeEmpl
		foreach ($TotalByWorkCal as $refWCal => $count)
		{
			if (isset($wCal[$refWCal]))
			{
				// init tong thoi gian theo lich lam viec
				if (!isset($totalTimeByWCal[$refWCal]))
				{
					$totalTimeByWCal[$refWCal] = 0;
				}


				$totalTimeEmpl += $count * $wCal[$refWCal];
				$totalTimeByWCal[$refWCal] = $count * $wCal[$refWCal];


				if (isset($work[$refWCal]))
				{
					foreach ($work[$refWCal] as $refKyNang => $count)
					{
						// init tong thoi gian cho tung cong viec (cong tong khong xet cac nhan vien cung lam 1 cong viec)
						if (!isset($maxTimeByWork[$refKyNang]))
						{
							$maxTimeByWork[$refKyNang] = 0;
						}

						$maxTimeByWork[$refKyNang] += $count * $wCal[$refWCal];
						//$timeByWork[$refKyNang][] = $wCal[$refWCal];
					}
				}
			}
		}

		foreach ($emplTemp as $ep)
		{
			if (!isset($minTimeByWork[$ep->Ref_KyNang]))
			{
				$minTimeByWork[$ep->Ref_KyNang] = 0;
			}
			if (isset($wCal[$ep->Ref_LichLamViec]))
			{
				$minTimeByWork[$ep->Ref_KyNang] += $wCal[$ep->Ref_LichLamViec];
			}
		}

		// Lay thoi gian min cua tung cong viec
		//                foreach($timeByWork as $refKyNang=>$time)
		//                {
		//                        if(count($timeByWork[$refKyNang]) > 1)
		//                        {
		//                                $minTimeByWork[$refKyNang] = 0;
		//                        }
		//                        else
		//                        {
		//                                $minTimeByWork[$refKyNang] = $timeByWork[$refKyNang][0];
		//                        }
		//                }
		return array('total' => $totalTimeEmpl, 'max' => $maxTimeByWork, 'min' => $minTimeByWork);

	}

	// ***********************************************************
	// === Get downtime
	// ***********************************************************
	private function getDowntime($start, $end, $period = '', $refEquipArr = array(), $add = false)
	{
		$retval = array();
		$now = date('Y-m-d');
		$compareEndWithNow = Qss_Lib_Date::compareTwoDate($end, $now);
		$compareStartWithNow = Qss_Lib_Date::compareTwoDate($start, $now);


		if ($compareStartWithNow == 1 || $compareStartWithNow == 0)
		{
			// Tinh hoan toan trong luong lai, dua vao bao tri dinh ki
			$past = array();
			$future = $this->getDowntimeFromPriorityMaintainPlan($start, $end, $period, $refEquipArr);
		} elseif (($compareStartWithNow == 0 || $compareStartWithNow == -1) && ($compareEndWithNow == 1 || $compareEndWithNow == 0))
		{
			// Tinh trong ca hien tai lan tuong lai
			// $now = date('Y-m-d');
			$nearNow = date('Y-m-d', strtotime($now) - 86400);
			$past = $this->getDowntimeFromWorkOrder($start, $nearNow, $period, $refEquipArr);
			$future = $this->getDowntimeFromPriorityMaintainPlan($now, $end, $period, $refEquipArr);
		} elseif ($compareEndWithNow == -1)
		{
			// Tinh trong qua khu, dua vao hai phieu bao tri
			$past = $this->getDowntimeFromWorkOrder($start, $end, $period, $refEquipArr);
			$future = array();
		}

		if ($add) // Cong don qua khu va tuong lai
		{
			if ($period)
			{
				foreach ($past as $refEq => $valByPeriod)
				{
					foreach ($valByPeriod as $period => $val)
					{
						if (!isset($retval[$refEq][$period]))
						{
							$retval[$refEq][$period] = 0;
						}
						$retval[$refEq][$period] += $val;
					}
				}

				foreach ($future as $refEq => $valByPeriod)
				{


					foreach ($valByPeriod as $period => $val)
					{
						if (!isset($retval[$refEq][$period]))
						{
							$retval[$refEq][$period] = 0;
						}
						$retval[$refEq][$period] += $val;
					}
				}
			} else
			{
				foreach ($past as $refEq => $val)
				{
					if (!isset($retval[$refEq]))
					{
						$retval[$refEq] = 0;
					}

					$retval[$refEq] += $val;
				}

				foreach ($future as $refEq => $val)
				{
					if (!isset($retval[$refEq]))
					{
						$retval[$refEq] = 0;
					}

					$retval[$refEq] += $val;
				}
			}
		} else // tra ve 2 mang rieng le ve qua khu va tuong lai
		{
			$retval = array('past' => $past, 'future' => $future);
		}
		return $retval;

	}

	private function getDowntimeFromPriorityMaintainPlan($start, $end, $period, $refEquipArr = array())
	{
		$startFormat = date_create($start);
		$endFormat = date_create($end);
		$data = $this->_model->getMaintenancePlanFull($refEquipArr);
		$shift = $this->_common->getTable(array('*'), 'OCa');

		$solar = new Qss_Model_Calendar_Solar();
		$print = array();
		$i = 0;
		$oldPlanMainTainIOID = '';


		if ($period)
		{
			foreach ($data as $item)
			{
				// Them bao tri ko dinh ky
				// Them bao tri dinh ky

				if ($item->NgayBTKDK)
				{
					if (!isset($removeDuplicate[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri][$item->NgayBTKDK][$item->Ref_Ca]))
					{
						if (Qss_Lib_Date::checkInRangeTime($item->NgayBTKDK, $this->_params['start'], $this->_params['end']))
						{
							$startSpe = date_create($item->NgayBTKDK);
							$day = (int) $startSpe->format('d');
							$week = (int) $startSpe->format('w');
							$month = (int) $startSpe->format('m');
							$year = $startSpe->format('Y');
							$quarter = (int) $solar->getQuarter((int) $month);
							$monthNo = $solar->getMonthNo((int) $month);

							switch ($period)
							{
								case 'D': $key = $startSpe->format('d-m-Y');
								break;
								case 'W': $key = $week . '.' . $year;
								break;
								case 'M': $key = $month . '.' . $year;
								break;
								case 'Q': $key = $quarter . '.' . $year;
								break;
								case 'Y': $key = $year;
								break;
							}
							$removeDuplicate[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri][$item->NgayBTKDK][$item->Ref_Ca] = 1;
							$print[$item->Ref_MaThietBi][$key] = $item->DungMay;
							$i++;
						}
					}
				} // End bao tri ko dinh ky

				if ($item->IOID != $oldPlanMainTainIOID)
				{
					$start = $startFormat;
					while ($start <= $endFormat)
					{
						$date = $start->format('Y-m-d');


						if (!isset($removeDuplicate[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri][$date][$item->Ref_Ca]))
						{
							$day = (int) $start->format('d');
							$week = (int) $start->format('w');
							$month = (int) $start->format('m');
							$year = $start->format('Y');
							$quarter = (int) $solar->getQuarter((int) $month);
							$monthNo = $solar->getMonthNo((int) $month);

							switch ($period)
							{
								case 'D': $key = $start->format('d-m-Y');
								break;
								case 'W': $key = $week . '.' . $year;
								break;
								case 'M': $key = $month . '.' . $year;
								break;
								case 'Q': $key = $quarter . '.' . $year;
								break;
								case 'Y': $key = $year;
								break;
							}

							if (!isset($print[$item->Ref_MaThietBi][$key]))
							{
								$print[$item->Ref_MaThietBi][$key] = 0;
							}

							if (
							(($item->MaKy == 'D') || ($item->MaKy == 'W' && $week == $item->GiaTri) || ($item->MaKy == 'M' && $day == $item->Ngay) || ($item->MaKy == 'Q' && $monthNo == $item->ThangThu) || ($item->MaKy == 'Y' && $day == $item->Ngay && $month == $item->Thang)
							)
							)
							{
								$item->DungMay = $item->DungMay ? $item->DungMay : 0;
								$print[$item->Ref_MaThietBi][$key] += $item->DungMay;
								$removeDuplicate[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri][$date][$item->Ref_Ca] = 1;
							}
						}
						$start = Qss_Lib_Date::add_date($start, 1);
					}
				}
				$oldPlanMainTainIOID = $item->IOID;
			}
		} else
		{
			foreach ($data as $item)
			{
				// Them bao tri ko dinh ky
				// Them bao tri dinh ky

				if (!isset($print[$item->Ref_MaThietBi]))
				{
					$print[$item->Ref_MaThietBi] = 0;
				}

				if ($item->NgayBTKDK)
				{
					if (!isset($removeDuplicate[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri][$item->NgayBTKDK][$item->Ref_Ca]))
					{
						if (Qss_Lib_Date::checkInRangeTime($item->NgayBTKDK, $this->_params['start'], $this->_params['end']))
						{
							$item->DungMay = $item->DungMay ? $item->DungMay : 0;
							$removeDuplicate[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri][$item->NgayBTKDK][$item->Ref_Ca] = 1;
							$print[$item->Ref_MaThietBi] += $item->DungMay;
							$i++;
						}
					}
				} // End bao tri ko dinh ky

				if ($item->IOID != $oldPlanMainTainIOID)
				{
					$start = $startFormat;
					while ($start <= $endFormat)
					{
						$date = $start->format('Y-m-d');


						if (!isset($removeDuplicate[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri][$date][$item->Ref_Ca]))
						{
							$item->DungMay = $item->DungMay ? $item->DungMay : 0;
							$print[$item->Ref_MaThietBi] += $item->DungMay;
							$removeDuplicate[$item->Ref_MaThietBi][$item->Ref_LoaiBaoTri][$date][$item->Ref_Ca] = 1;
						}
						$start = Qss_Lib_Date::add_date($start, 1);
					}
				}
				$oldPlanMainTainIOID = $item->IOID;
			}
		}
		return $print;

	}

	private function getDowntimeFromWorkOrder($start, $end, $period, $refEquipArr = array())
	{

		$ret = array();

		if ($period)
		{
			$data = $this->_model->getDowntimeFromWorkOrder($start, $end, $period, $refEquipArr);
			foreach ($data as $item)
			{
				$key = '';
				switch ($period)
				{
					case 'D':
						$key = (int) $item->NgayDungMay;
						break;
					case 'W':
						$key = (int) $item->Tuan . '.' . (int) $item->Nam;
						break;
					case 'M':
						$key = (int) $item->Thang . '.' . (int) $item->Nam;
						break;
					case 'Q':
						$key = (int) $item->Quy . '.' . (int) $item->Nam;
						break;
					case 'Y':
						$key = (int) $item->Nam;
						break;
				}
				$ret[$item->Ref_MaThietBi][$key] = $item->DungMay;
			}
		} else
		{
			$data = $this->_model->getDowntimeFromWorkOrder($start, $end, 'D', $refEquipArr);
			foreach ($data as $item)
			{
				if (!isset($ret[$item->Ref_MaThietBi]))
				{
					$ret[$item->Ref_MaThietBi] = 0;
				}
				$ret[$item->Ref_MaThietBi] += $item->DungMay;
			}
		}
		return $ret;

	}


	/**
	 * Plan Downtime by equip (or line)
	 * @param inte $eqIOID 1 eq = 1 manufacturing line
	 * @param date $start
	 * @param date $end
	 * @return array [itemIOID] => Number
	 */
	private function getScheduleDowntimeOfLine($eqIOID, $start, $end, $totalWorkHoursByCal = array())
	{
		$maintPlanModel   = new Qss_Model_Maintenance_Plan();
		$maintPlanModel->setFilterByEqIOID(@(int)$eqIOID);

		$periodic         = $maintPlanModel->getPlans();
		$solar            = new Qss_Model_Calendar_Solar();
		$scheduleDowntime = array();
		$lineID           = '';
		$dateRange        = $solar->createDateRangeArray($start, $end);
		$interval         = 1;
		$keepSpecialDate  = array();
		$tongSoGio        = count($dateRange) * 24;


		// Lay theo ngay dac biet
		foreach($periodic as $p)
		{
			if(!isset($scheduleDowntime[$p->LIOID])) $scheduleDowntime[$p->LIOID]= 0;
			if($p->NgayDacBiet != '' && $p->NgayDacBiet != '0000-00-00' )
			{
				$keepSpecialDate[$p->IFID][] = $p->NgayDacBiet;
			}
			$scheduleDowntime[$p->LIOID] += $p->DungMay;
		}

		// Lay theo dinh ky co tru ngay dac biet neu trung lap
		foreach ($periodic as $p)
		{
			$p->LapLai = isset($p->LapLai)?$p->LapLai:1;
				
			// Dung may cong them thoi gian nghi
			if(isset($totalWorkHoursByCal[$p->Ref_MaThietBi]))
			{
				if(!isset($scheduleDowntime[$p->LIOID]))
				{
					$scheduleDowntime[$p->LIOID] = $tongSoGio - $totalWorkHoursByCal[$p->Ref_MaThietBi];
				}
				else
				{
					$scheduleDowntime[$p->LIOID] += $tongSoGio - $totalWorkHoursByCal[$p->Ref_MaThietBi];
				}
			}
				
			if(!isset($scheduleDowntime[$p->LIOID])) $scheduleDowntime[$p->LIOID]= 0;
			if ($lineID != $p->IFID && !isset($scheduleDowntime[$p->LIOID]))
			{
				$scheduleDowntime[$p->LIOID] = 0;
			}
			switch ($p->MaKy)
			{
				case 'D':
					$interval = ($p->LapLai?$p->LapLai:1) + 1;
					$next     = 0;
					foreach ($dateRange as $date)
					{
						$next++;
						if(( (($interval%$next)==0) || $next == 1)
						&& !in_array(date('Y-m-d', strtotime($date)), $keepSpecialDate))
						{
							$scheduleDowntime[$p->LIOID] += $p->DungMay;
						}
					}
					break;
				case 'W':
					$interval = ($p->LapLai?$p->LapLai:1) + 1;
					$next     = 0;
					foreach ($dateRange as $date)
					{
						if (date('w', strtotime($date)) == $p->ThuTrongTuan)
						{
							$next++;
						}

						if (date('w', strtotime($date)) == $p->ThuTrongTuan
						&& ( (($interval%$next)==0)  || $next == 1 )
						&& !in_array(date('Y-m-d', strtotime($date)), $keepSpecialDate))
						{
							$scheduleDowntime[$p->LIOID] += $p->DungMay;
						}
					}
					break;
				case 'M':
					$interval = ($p->LapLai?$p->LapLai:1) + 1;
					$next     = 0;
					foreach ($dateRange as $date)
					{
						if (date('d', strtotime($date)) == $p->NgayTrongThang)
						{
							$next++;
						}

						if (date('d', strtotime($date)) == $p->NgayTrongThang
						&& ( (($interval%$next)==0)  || $next == 1 )
						&& !in_array(date('Y-m-d', strtotime($date)), $keepSpecialDate))
						{
							$scheduleDowntime[$p->LIOID] += $p->DungMay;
						}
					}
					break;
				case 'Y':
					$interval = ($p->LapLai?$p->LapLai:1) + 1;
					$next     = 0;
					foreach ($dateRange as $date)
					{
						$dateMicro = strtotime($date);
						$monthOf = date('m', $dateMicro);
						$dateOf = date('d', $dateMicro);

						if (($monthOf == $p->ThangTrongNam) && ($dateOf == $p->NgayTrongThang))
						{
							$next++;
						}

						if ((($monthOf == $p->ThangTrongNam) && ($dateOf == $p->NgayTrongThang))
						&& ( (($interval%$next)==0)  || $next == 1 )
						&& !in_array(date('Y-m-d', strtotime($date)), $keepSpecialDate))
						{
							$scheduleDowntime[$p->LIOID] += $p->DungMay;
						}
					}
					break;
			}
			$lineID = $p->IFID;
		}
		return $scheduleDowntime;

	}


	private function countTimeForDailyRecord($period, $start, $end)
	{
		$ret = 0;
		switch ($period)
		{
			case Qss_Lib_Extra_Const::PERIOD_TYPE_DAILY:
				$ret = Qss_Lib_Date::divDate($start, $end);
				break;

			case Qss_Lib_Extra_Const::PERIOD_TYPE_WEEKLY:
				$range  = $solar->createWeekRangeArray($start, $end);
				$ret = count((array)$range);
				break;

			case Qss_Lib_Extra_Const::PERIOD_TYPE_MONTHLY:
				$range = $solar->createMonthRangeArray($start, $end);

				foreach ($range as $year=>$monthArr)
				{
					$ret += count($monthArr);
				}
				break;

			case Qss_Lib_Extra_Const::PERIOD_TYPE_QUARTERLY:
				$range = $solar->createQuarterRangeArray($start, $end);
				$ret = count((array)$range);
				break;

			case Qss_Lib_Extra_Const::PERIOD_TYPE_YEARLY:
				$range = $solar->createYearRangeArray($start, $end);
				$ret = count((array)$range);
				break;
		}
		return $ret;
	}

	/**
	 * Get tasks, materials by plan (Note: materials group by ifid, eq position)
	 * @param date $date YYYY-mm-dd
	 * @param int $location IOID
	 * @param int $maintype IOID
	 * @return array
	 */
	private function getMaintainDataForMaintainPlan($date, $location, $maintype)
	{
		$planModel    = new Qss_Model_Maintenance_Plan();

		$planModel->setFilterByLocIOID($location);
		$planModel->setFilterByMaintainTypeIOID($maintype);
		$planModel->setFilterByDate($date);

		$plans        = $planModel->getPlans();
		$retval       = array();
		$planIFIDArr  = array();
		$oldIFID      = '';
		$oldPosition  = '';
		$mOldIFID     = ''; // Material
		$mOldPosition = ''; // Material
		$tIndex       = 0;
		$mIndex       = 0;

		// ===== INIT MAINT PLAN ARRAY =====
		foreach ($plans as $item)
		{
			$planIFIDArr[]               = $item->IFID;
			$tempInfo                    = array();
			$tempInfo['IFID']            = $item->IFID;
			$tempInfo['DocNo']           = @$item->SoPhieu;
			$tempInfo['Code']            = $item->MaThietBi;
			$tempInfo['Name']            = $item->TenThietBi;
			$tempInfo['Type']            = $item->LoaiBaoTri;
			$tempInfo['Shift']           = $item->Ca;
			$tempInfo['WorkCenter']      = $item->TenDVBT;
			$tempInfo['Employee']        = $item->NguoiThucHien;
			$tempInfo['Line']            = 0;
			$retval[$item->IFID]['Info'] = $tempInfo;
			$retval[$item->IFID]['Component'] = array();
		}

		// ===== GET TASKS AND MATERIALS =====
		$tasks        = $planModel->getTasksByPlanIFID($planIFIDArr);
		$materials    = $planModel->getMaterialsByPlanIFIDGroupByPosition($planIFIDArr);

		// ===== ADD TASKS MAINT TO PLAN ARRAY  =====
		foreach($tasks as $item)
		{
			if($oldIFID != $item->IFID || $oldPosition != $item->Ref_ViTri)
			{
				$tIndex = 0;
			}

			$oldIFID     = $item->IFID;
			$oldPosition = $item->Ref_ViTri;

			if(!isset($retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]))
			{
				$tempCom            = array();
				$tempCom['BoPhan']  = $item->BoPhan;
				$tempCom['ViTri']   = $item->ViTri;
				$tempCom['RowSpan'] = 0;
				$retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri] = $tempCom;
			}

			$temp            = array();
			$temp['MoTa']    = $item->MoTaCongViec;
			$temp['GhiChu']  = $item->GhiChuCongViec;
			$temp['DanhGia'] = $item->DanhGia;
			$temp['Dat']     = $item->Dat;

			$retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['Work'][$tIndex] = $temp;
			$tIndex++;
			$retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['RowSpan']       = $tIndex;
		}

		// ===== ADD MATERIALS TO MAINT PLAN ARRAY  =====
		foreach($materials as $item)
		{
			if($mOldIFID != $item->IFID || $mOldPosition != $item->Ref_ViTri)
			{
				$mIndex = 0;
			}
			$mOldIFID     = $item->IFID;
			$mOldPosition = $item->Ref_ViTri;

			if(!isset($retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]))
			{
				$tempCom = array();
				$tempCom['BoPhan']    = $item->BoPhan;
				$tempCom['ViTri']     = $item->ViTri;
				$tempCom['RowSpan']   = 0;
				$retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri] = $tempCom;
			}

			$temp           = array();
			$temp['VatTu']  = $item->VatTu;

			$retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['Material'][$mIndex] = $temp;
			$mIndex++;
		}
		return $retval;
	}

	private function getMaintainDataForMaintainOrder($date, $location, $maintype)
	{
		$orderModel   = new Qss_Model_Maintenance_Workorder();
		$orderModel->setFilterTimeForGetWorkOrdersFunc($date, $date);
		$orderModel->setFilterLocIOIDForGetWorkOrdersFunc($location);
		$orderModel->setFilterMaintTypeIOIDForGetWorkOrdersFunc($maintype);
		$orderModel->setOrderByEquipForGetWorkOrdersFunc();
		$orders       = $orderModel->getWorkOrders();
		//$date, $date, $location, $maintype, 0, 'vn', false, 0, 1, '', true, false

		$retval       = array();
		$ordersIFIDArr = array();
		$oldIFID      = '';
		$oldPosition  = '';
		$mOldIFID     = ''; // Material
		$mOldPosition = ''; // Material
		$oOldIFID     = '';
		$oOldPosition = '';
		$tIndex       = 0;
		$oIndex       = 0;
		$mIndex       = 0;

		// ===== INIT MAINT ORDER ARRAY =====
		foreach ($orders as $item)
		{
			$ordersIFIDArr[]             = $item->IFID_M759;
			$tempInfo                    = array();
			$tempInfo['IFID']            = $item->IFID_M759;
			$tempInfo['DocNo']           = @$item->SoPhieu;
			$tempInfo['Code']            = $item->MaThietBi;
			$tempInfo['Name']            = $item->TenThietBi;
			$tempInfo['Type']            = $item->LoaiBaoTri;
			$tempInfo['Shift']           = $item->Ca;
			$tempInfo['WorkCenter']      = $item->TenDVBT;
			$tempInfo['Employee']        = $item->NguoiThucHien;
			$tempInfo['Line']            = 0;
			$tempInfo['Status']          = $item->Name;
			$tempInfo['Review']          = $item->DanhGia;
			$retval[$item->IFID_M759]['Info'] = $tempInfo;
			$retval[$item->IFID_M759]['Component'] = array();
		}

		// ===== GET TASKS AND MATERIALS =====
		$tasks        = $orderModel->getTasksByIFID($ordersIFIDArr);
		$materials    = $orderModel->getMaterialsByIFIDGroupByIFID($ordersIFIDArr);
		// 		$outsources   = $orderModel->getOutsourcesByIFID($ordersIFIDArr);

		// ===== ADD TASKS TO MAINT ARRAY  =====
		foreach($tasks as $item)
		{
			if($oldIFID != $item->IFID || $oldPosition != $item->Ref_ViTri)
			{
				$tIndex = 0;
			}

			$oldIFID     = $item->IFID;
			$oldPosition = $item->Ref_ViTri;

			if(!isset($retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]))
			{
				$tempCom            = array();
				$tempCom['BoPhan']  = $item->BoPhan;
				$tempCom['ViTri']   = $item->ViTri;
				$tempCom['RowSpan'] = 0;
				$retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri] = $tempCom;
			}

			$temp            = array();
			$temp['MoTa']    = $item->MoTaCongViec;
			$temp['GhiChu']  = $item->GhiChuCongViec;
			$temp['DanhGia'] = $item->DanhGia;
			$temp['Dat']     = $item->Dat;

			$retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['Work'][$tIndex] = $temp;
			$tIndex++;
			$retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['TIndex']  = $tIndex;
			$retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['RowSpan'] = $tIndex;
		}

		// ===== ADD OUTSOURCES TO MAINT ARRAY  =====
		// 		foreach($outsources as $item)
		// 		{
		// 			if($oOldIFID != $item->IFID || $oOldPosition != @(int)$item->Ref_ViTri)
		// 			{
		// 				if(isset($retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['TIndex']))
		// 				{
		// 					$oIndex = $retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['TIndex'];
		// 				}
		// 				else
		// 				{
		// 					$oIndex = 0;
		// 				}
		// 			}

		// 			$oOldIFID     = $item->IFID;
		// 			$oOldPosition = @(int)$item->Ref_ViTri;

		// 			if(!isset($retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]))
		// 			{
		// 				$tempCom            = array();
		// 				$tempCom['BoPhan']  = @(string)$item->BoPhan;
		// 				$tempCom['ViTri']   = @(string)$item->ViTri;
		// 				$tempCom['RowSpan'] = 0;
		// 				$retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri] = $tempCom;
		// 			}

		// 			$temp           = array();
		// 			$temp['MoTa']   = $item->MoTaCongViec;
		// 			$temp['GhiChu'] = '';
		// 			$temp['Dat']    = $item->HoanThanh;

		// 			$retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['Work'][$oIndex] = $temp;
		// 			$oIndex++;

		// 			// Cộng dồn với phần công việc bảo trì
		// 			$retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['RowSpan']  = $oIndex;
		// 		}

		// ===== ADD MATERIALS TO MAINT ORDER ARRAY  =====
		foreach($materials as $item)
		{
			if($mOldIFID != $item->IFID || $mOldPosition != $item->Ref_ViTri)
			{
				$mIndex = 0;
			}
			$mOldIFID     = $item->IFID;
			$mOldPosition = $item->Ref_ViTri;

			if(!isset($retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]))
			{
				$tempCom = array();
				$tempCom['BoPhan']    = $item->BoPhan;
				$tempCom['ViTri']     = $item->ViTri;
				$tempCom['RowSpan']   = 0;
				$retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri] = $tempCom;
			}

			$temp           = array();
			$temp['VatTu']  = $item->VatTu;

			$retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['Material'][$mIndex] = $temp;
			$mIndex++;
		}
		return $retval;
	}
	/**
	 * @note: Dung rieng cho in lich su cai dat thiet bi
	 * @param object $equipments
	 * @param date $start Ngay bat dau tim kiem lich su cai dat (dd-mm-YYYY)
	 * @param date $end Ngay ket thuc tim kiem lich su cai dat (dd-mm-YYYY)
	 * @retun array tra ve mang cai dat di chuyen thiet bi da duoc sap xep ngay thang
	 * @use-in: equipmentLocation1Action()
	 */
	private function getEquipmentForIntallHistoryReport($equipments, $start, $end)
	{

		if(!count((array)$equipments)) return array();
		$eqArray = array(); // Mang thiet bi da sap xep
		$i       = 0;

		foreach($equipments as $item)
		{
			$item->NgayBatDau  = $item->NgayBatDau?$item->NgayBatDau:$start;
			$item->NgayKetThuc = $item->NgayKetThuc?$item->NgayKetThuc:$end;
			$eqArray[$i]['MaThietBi']        = $item->MaThietBi;
			$eqArray[$i]['TenThietBi']        = $item->TenThietBi;
			$eqArray[$i]['Ref_MaThietBi']    = $item->Ref_MaThietBi;
			$eqArray[$i]['LoaiThietBi']      = $item->LoaiThietBi;
			$eqArray[$i]['LichLamViec']      = $item->CDLich;
			$eqArray[$i]['KhuVuc']           = $item->MaKVMoi;
			$eqArray[$i]['TuNgay']           = Qss_Lib_Date::mysqltodisplay($item->NgayBatDau);
			$eqArray[$i]['DenNgay']          = Qss_Lib_Date::mysqltodisplay($item->NgayKetThuc);
			//$eqArray[$i]['ThoiGian']         = $item->ThoiGian;
			$eqArray[$i]['Model']            = $item->Model;
			$eqArray[$i]['NhomThietBi']      = $item->NhomThietBi;
			$eqArray[$i]['HanBaoHanh']       = Qss_Lib_Date::mysqltodisplay($item->HanBaoHanh);
			$eqArray[$i]['HangBaoHanh']      = $item->HangBaoHanh;
			$eqArray[$i]['NgayDuaVaoSuDung'] = $NgayDuaVaoSuDung;
			$eqArray[$i]['NgayMua']          = $item->NgayMua;
			$eqArray[$i]['XuatXu']           = $item->XuatXu;
			$eqArray[$i]['NamSanXuat']       = $item->NamSanXuat;
			$eqArray[$i]['TBIOID']           = $item->TBIOID;
			$eqArray[$i]['ThoVanHanh']       = $item->ThoVanHanh;
			$eqArray[$i]['DuAn']             = $item->DuAn;
			$eqArray[$i]['SoGioHoatDong']    = $item->SoGioHoatDong;
			$i++;
		}
		return $eqArray;
	}

	/**
	 * @note: Dung rieng cho in lich su cai dat thiet bi
	 * @param object $equipments Danh sach thiet bi
	 * @return array Thong so ky thuat cua thiet bi
	 * @use-in: equipmentLocation1Action()
	 */
	private function getParamOfEquip($equipments)
	{
		if(!count((array)$equipments)) return array();

		$equipModel     = new Qss_Model_Maintenance_Equipment();
		$parameterArray = array();
		$TBIOID         = array();

		foreach ($equipments as $item)
		{
			$TBIOID[] = $item->TBIOID;
		}

		// Thong so thiet bi
		$parametersSql  = (count($TBIOID))?$equipModel->getTechnicalParameterValues($TBIOID) : array();

		// Lay mang thong so theo thiet bi
		foreach ($parametersSql as $p)
		{
			$paramsPrintFormat = sprintf('- %1$s: %2$s %3$s (%4$s - %5$s) <br/>', $p->Ten, $p->GiaTri, $p->ChiSo
			, $p->GiaTriNN, $p->GiaTriLN);

			if (isset($parameterArray[$p->TBIOID]))
			{
				$parameterArray[$p->TBIOID] .= $paramsPrintFormat;
			} else
			{
				$parameterArray[$p->TBIOID]  = $paramsPrintFormat;
			}
		}

		return $parameterArray;
	}
}

?>