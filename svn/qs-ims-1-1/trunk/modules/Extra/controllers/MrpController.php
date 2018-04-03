<?php

class Extra_MrpController extends Qss_Lib_Controller {

	public $_form;
	private $extra; // sau khi sua xong $_common xoa bien $extra di
	private $_model;
	private $_common;
	private $_params;

	//private $_requirement = array('M505'=>array('main'=>'ODonBanHang', 'plan'=>'OKeHoachGiaoHang'));
	//private $_requirement = array('M505');
	// @Note: Chay ke hoach cung ung bao gom primary va detail
	// @Note: Tao yeu cau cung ung requirement

	public function init() {
		//$this->i_SecurityLevel = 15;
		parent::init();
		$ifid = $this->params->requests->getParam('ifid', 0);
		$fid = $this->params->requests->getParam('fid', 0);
		$deptid = $this->params->requests->getParam('deptid', $this->_user->user_dept_id);
		$form = new Qss_Model_Form();
		if ($ifid) {
			$form->initData($ifid, $deptid);
		} else {
			$form->v_fInit($fid, $deptid, $this->_user->user_id);
		}
		$this->b_fCheckRightsOnForm($form, 2);
		$this->_form = $form;
		$this->extra = new Qss_Model_Extra_Extra();
		$this->_model = new Qss_Model_Extra_Mrp();
		$this->_common = new Qss_Model_Extra_Extra();
		$this->_params = $this->params->requests->getParams();

		$this->html->form = $form;
		$this->headScript($this->params->requests->getBasePath() . '/css/button.css');
		$this->headScript($this->params->requests->getBasePath() . '/js/form-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');
	}

	public function primaryIndexAction() {
		// $info: Thong tin xu ly yeu cau
		$info = $this->_common->getTable(array('*'), 'OKeHoachCungUng', array('IFID_M901' => $this->_params['ifid']), array(), 'NO_LIMIT', 1);
		// $startDate: Xac dinh ngay bat dau xu ly mrp
		$startDate = ($info->NgayBatDau == '' || $info->NgayBatDau == '0000-00-00') ? date('Y-m-d', strtotime('tomorrow')) : $info->NgayBatDau;

		$this->html->ifid = $this->_params['ifid'];
		$this->html->ioid = $this->_params['ioid'];
		$this->html->module = $this->_form->FormCode;
		$this->html->startDate = $startDate;
	}

	public function primarySearchAction() {
		$outputPlan = $this->_model->getRequireDetail($this->_params['ifid']); // Lay chi tiet yeu cau

		if ($outputPlan) {
			$bomArray = array(); // mang chua bom voi key la sp
			//$operationArray = array();
			//$info   = $this->extra->getTable(array('*'), 'OKeHoachCungUng', array('IFID_M901'=>$this->_params['ifid']), array(), 'NO_LIMIT');
			//@todo: Doan code nay bi loi chua co bien info
			$ngayBatDau = (@$info->NgayBatDau == '' || @$info->NgayBatDau == '0000-00-00') ? date('Y-m-d', strtotime('tomorrow')) : @$info->NgayBatDau;
			//$ngayXuatHangDauTien = $outputPlan->NgayXuatHang;

			$oldBom = '';
			$tonKho = $this->_model->getInventoryOfMrpItem($this->_params['ifid']);
			$tonKhoArr = array();


			foreach ($tonKho as $item) {
				$tonKhoArr[$item->RefItem][$item->RefAttr] = $item->Inventory;
			}

			// Lay danh sach bom cua tung san pham
			foreach ($outputPlan as $item) {
				$item->Ref_ThuocTinh = (int) $item->Ref_ThuocTinh;
				if ($item->BomID && $oldBom != $item->BomID) {
					$bomArray[$item->Ref_MaSP][$item->Ref_ThuocTinh][$item->BomID]['BomName'] = $item->BomName;
					$bomArray[$item->Ref_MaSP][$item->Ref_ThuocTinh][$item->BomID]['All'] = $item->AllAttributes;
					$bomArray[$item->Ref_MaSP][$item->Ref_ThuocTinh][$item->BomID]['Assembly'] = (int) $item->Assembly;
				}

				//if(!key_exists($item->RefOperation, $operationArray))
				//{
				//	$operationArray[$item->RefOperation] = $item->Operation;
				//}

				$oldBom = $item->BomID;
			}
			//echo '<pre>'; print_r($bomArray); die;

			$this->html->startDate = $ngayBatDau;
			$this->html->searchItem = $outputPlan;
			$this->html->bomArray = $bomArray;
			//$this->html->operation  = $operationArray;
			$this->html->inventory = $tonKhoArr;
			$this->html->lamLai = $this->_params['lamLai'];
			$this->html->hasDetailReq = true;
			//$this->html->dateError  = Qss_Lib_Date::compareTwoDate($ngayXuatHangDauTien, $ngayBatDau);
			$this->html->dateError = 1;
			//echo '<pre>'; print_r($operationTimeArray); die;
		} else {
			$this->html->hasDetailReq = false;
		}
	}

	public function primaryChildrenAction() {
		$model = new Qss_Model_Extra_Mrp();
		$params = $this->params->requests->getParams();
		$currentLevel = $params['currentLevel'] + 1; // Level hien tai
		$prevLevel = $params['currentLevel']; // Level can lay children
		$getChildIndex = 0;
		$getParentHasBomIndex = 0;
		$children = array(); // Mang thong tin ve children
		$keepRefBomOfProductionItem = array();
		$keepIndexOfParentProductionItem = array();
		$keepDateOf = array();
		$inTheEnd[$params['markerGroupID']] = 1;
		$tonKho = array();
		$datSanXuat = array();
		$datMua = array();
		$keepItemCheckIncommingAndWarehouse = array();
		//echo '<pre>'; print_r($params); die;
		// Lay san pham san xuat co bom
		// Dung so luong san xuat de xac dinh xem co dung bom hay ko?
		// Dung level de xac dinh co phai la cap can lay kho? cap can lay la cap cuoi cung
		foreach ($params['productionQty'] as $proQty) {
			if ($params['refBom'][$getParentHasBomIndex] &&
			$proQty > 0 &&
			$prevLevel == $params['level'][$getParentHasBomIndex]) {
				$keepRefBomOfProductionItem[] = $params['refBom'][$getParentHasBomIndex];

				$code = $params['refItem'][$getParentHasBomIndex] . '_' . $params['refBom'][$getParentHasBomIndex] . '_' . $params['refAttributes'][$getParentHasBomIndex];
				$keepIndexOfParentProductionItem[$code] = $getParentHasBomIndex;
				$realCode = $params['refItem'][$getParentHasBomIndex] . '_' . $params['refBom'][$getParentHasBomIndex] . '_' . $params['refAttributesReal'][$getParentHasBomIndex];
				$keepDateOf[$realCode]['issueDate'] = $params['issueDate'][$getParentHasBomIndex];
				$keepDateOf[$realCode]['eDate'] = $params['eDate'][$getParentHasBomIndex];
				$keepDateOf[$realCode]['sDate'] = $params['sDate'][$getParentHasBomIndex];
				//$keepDateOf[$realCode]['deliveryDate'] = $params['deliveryDate'][$getParentHasBomIndex];
			}
			$getParentHasBomIndex++;
		}
		//echo '<pre>'; print_r($keepIndexOfParentProductionItem); die;

		$keepCode = array();
		// Tao mang children
		//echo '<pre>'; print_r($model->getChildrenMrp($keepRefBomOfProductionItem)); die;
		foreach ($model->getChildrenMrp($keepRefBomOfProductionItem) as $member) {

			$clearDuplicateCode = $member->RefThanhPhan . '_' . $member->RefThuocTinhThanhPhan;
			$realCode = $member->RefSanPham . '_' . $member->CauThanhIOID . '_' . $member->RefThuocTinh;
			$member->RefThuocTinh = ($member->AllAttributesParent == 1) ? 0 : $member->RefThuocTinh;
			$code = $member->RefSanPham . '_' . $member->CauThanhIOID . '_' . $member->RefThuocTinh;
			$clearDuplicateCode = $code . '_' . $clearDuplicateCode;
			// real code: la code lay tu parent, voi ref thuoc tinh bang ref thuoc tinh cua sp
			// real code chua thuc su duoc dung o dau
			// code:  la code lay tu parent, voi ref thuoc tinh bang ref thuoc tinh cua sp va n
			// neu sp co cau thanh ap dung cho tat ca thuoc tinh thi ref thuoc tinh = 0
			// dung de xu ly truong hop cau thanh ko can tuan thu ref thuoc tinh, ap dung cho toan bo tt
			// clear duplicate code: giup gop nhom cac dong trung lap lay code cua parent
			// gop voi filter cua children


			if (!key_exists($clearDuplicateCode, $keepCode)) {
				$keepCode[$clearDuplicateCode]['Index'] = $getChildIndex;
				$keepCode[$clearDuplicateCode]['Code'] = $code;

				$children[$code][$getChildIndex]['itemName'] = $member->TenThanhPhan;
				$children[$code][$getChildIndex]['itemCode'] = $member->MaThanhPhan;
				$children[$code][$getChildIndex]['refItem'] = $member->RefThanhPhan;
				$children[$code][$getChildIndex]['qty'] = $member->SoLuongThanhPhan; // Số lượng trong thành phần sp
				$children[$code][$getChildIndex]['parentQty'] = $member->SoLuongCha; // Số lượng trong cấu thành sp
				$children[$code][$getChildIndex]['attributes'] = $member->ThuocTinhThanhPhan;
				$children[$code][$getChildIndex]['refAttributes'] = ($member->AllAttributesChildren == 1) ? 0 : $member->RefThuocTinhThanhPhan;
				$children[$code][$getChildIndex]['refAttributesReal'] = $member->RefThuocTinhThanhPhan;
				$children[$code][$getChildIndex]['uom'] = $member->DonViTinhThanhPhan;
				$children[$code][$getChildIndex]['code'] = $code;
				$children[$code][$getChildIndex]['bom'][] = $member->CauThanhThanhPhan;
				$children[$code][$getChildIndex]['refBom'][] = $member->RefCauThanhThanhPhan;
				$children[$code][$getChildIndex]['allAttr'][] = $member->AllAttributesChildren;
				//$children[$code][$getChildIndex]['requireQtyForAssembly'] = $member->SoLuongYeuCau;


				$children[$code][$getChildIndex]['purchase'] = $member->MuaVaoThanhPhan;
				$children[$code][$getChildIndex]['manufacturing'] = $member->SanXuatThanhPhan;
				$children[$code][$getChildIndex]['level'] = $currentLevel;
				$children[$code][$getChildIndex]['hasBOM'] = $member->RefCauThanhThanhPhan ? 1 : 0;
				$children[$code][$getChildIndex]['hasChildren'] = 0;
				$children[$code][$getChildIndex]['assembly'] = $member->ChildAssembly;
				$children[$code][$getChildIndex]['directlyUnder'] = $code;
				$children[$code][$getChildIndex]['parentManuQty'] = 0; // Số lượng cha trong kế hoạch chi tiết
				$children[$code][$getChildIndex]['issueDate'] = @$keepDateOf[$realCode]['issueDate'];
				$children[$code][$getChildIndex]['deliveryDate'] = @$keepDateOf[$realCode]['deliveryDate'];
				$children[$code][$getChildIndex]['eDate'] = @$keepDateOf[$realCode]['eDate'];
				$children[$code][$getChildIndex]['sDate'] = @$keepDateOf[$realCode]['sDate'];
				$children[$code][$getChildIndex]['count'] = 1;
				$children[$code][$getChildIndex]['countBom'] = 1;
				$keepItemCheckIncommingAndWarehouse[$getChildIndex]['Item'] = $member->RefThanhPhan;
				$keepItemCheckIncommingAndWarehouse[$getChildIndex]['Attr'] = $member->RefThuocTinhThanhPhan;

				if (isset($keepIndexOfParentProductionItem[$code])) {
					$params['hasChildren'][$keepIndexOfParentProductionItem[$code]] = 1;
					$children[$code][$getChildIndex]['parentManuQty'] = $params['productionQty'][$keepIndexOfParentProductionItem[$code]];
				}
				$getChildIndex++;
			} else {
				if (!in_array($member->RefCauThanhThanhPhan, $children[$keepCode[$clearDuplicateCode]['Code']][$keepCode[$clearDuplicateCode]['Index']]['refBom'])) {
					$children[$keepCode[$clearDuplicateCode]['Code']][$keepCode[$clearDuplicateCode]['Index']]['bom'][] = $member->CauThanhThanhPhan;
					$children[$keepCode[$clearDuplicateCode]['Code']][$keepCode[$clearDuplicateCode]['Index']]['refBom'][] = $member->RefCauThanhThanhPhan;
					$children[$keepCode[$clearDuplicateCode]['Code']][$keepCode[$clearDuplicateCode]['Index']]['allAttr'][] = $member->AllAttributesChildren;
					$children[$keepCode[$clearDuplicateCode]['Code']][$keepCode[$clearDuplicateCode]['Index']]['countBom'] ++;
				}
				$children[$keepCode[$clearDuplicateCode]['Code']][$keepCode[$clearDuplicateCode]['Index']]['count'] ++;
			}

			if ($member->RefCauThanhThanhPhan) {
				$inTheEnd[$params['markerGroupID']] = 0;
			}
		}

		// set in the end
		$index = 0;
		foreach ($params['hasBOM'] as $item) {
			if ($item == 1 && $params['hasChildren'] == 0) {
				$inTheEnd[$params['markerGroupID']] = 0;
			}
		}

		if (isset($params['hasBOMButNoChildren']) && $params['hasBOMButNoChildren'] == 1) {
			$this->html->hasBOMButNoChildren = 1;
			$inTheEnd[$params['markerGroupID']] = 0;
		}
		//echo '<pre>'; print_r($keepItemCheckIncommingAndWarehouse); die;
		foreach ($model->getInventoryOfChild($keepItemCheckIncommingAndWarehouse) as $item) {
			$tonKho[$item->Ref_MaSP][$item->Ref_ThuocTinh] = $item->SoLuong;
		}

		//
		//foreach ($model->getPurchaseOfChild($keepItemCheckIncommingAndWarehouse, $params['startDate']) as $item)
		//{
		//	$datMua[$item->Ref_MaSP][$item->Ref_ThuocTinh] = $item->SoLuong;
		//}
		//foreach ($model->getManufacturingOfChild($keepItemCheckIncommingAndWarehouse, $params['startDate']) as $item)
		//{
		//	$datSanXuat[$item->Ref_MaSP][$item->Ref_ThuocTinh] = $item->SoLuong;
		//}

		$this->html->parent = $params; // Truyen sang parent
		$this->html->children = $children; // Truyen sang chidren
		$this->html->level = $currentLevel; // Truyen sang level
		$this->html->inTheEnd = $inTheEnd;
		$this->html->inventory = $tonKho;
		//$this->html->purchase = $datMua;
		//$this->html->production = $datSanXuat;
	}

	public function primaryOldAction() {
		$model = new Qss_Model_Extra_Mrp();
		$params = $this->params->requests->getParams();
		$oldGeneral = $model->getOldGeneralPlan($params['ifid'], $params['khioid']);
		$keepItemArray = array();
		$oldGeneralIndex = 0;
		$inventory = array(); // Ton kho
		$production = array(); // San xuat
		$purchase = array(); // Dat mua
		// Tao mang lay du lieu
		foreach ($oldGeneral as $item) {
			$keepItemArray[$oldGeneralIndex]['Item'] = $member->RefThanhPhan;
			$keepItemArray[$oldGeneralIndex]['Attr'] = $member->RefThuocTinhThanhPhan;
			$oldGeneralIndex++;
		}

		// Get inventory
		foreach ($model->getInventoryOfChild($keepItemCheckIncommingAndWarehouse) as $item) {
			$inventory[$item->Ref_MaSP][$item->Ref_ThuocTinh] = $item->SoLuong;
		}

		$this->html->oldGeneral = $oldGeneral;
		$this->html->inventory = $inventory;
	}

	public function primarySaveAction() {
		$params = $this->params->requests->getParams();
		if ($this->params->requests->isAjax()) {
			$service = $this->services->Extra->Mrp->Primary->Save($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	/// Validate data
	public function primaryChangeAction() {
		//1. Plan
		//2. Labor & Machine requirement (L&M each operator)
		//3. Work center performance (L&M)
		//4.
		$params = $this->params->requests->getParams();
		$operationTimeArray = array();
		$outputPlan = $this->_model->getRequireDetail($params['ifid']); // Lay chi tiet yeu cau
		$i = 0;
		//$info         = $this->_model->getMRPInfo($params['ifid']);
		//echo '<pre>'; print_r($params);die;

		foreach ($outputPlan as $item) {
			// Thoi gian cho san pham tren tung cong doan
			//$operationTimeArray[$item->NgayXuatHang][$item->RefOperation][$i]['issue'] = $item->NgayXuatHang;
			//$operationTimeArray[$item->NgayXuatHang][$item->RefOperation][$i]['item'] = $item->Ref_MaSP;
			//$operationTimeArray[$item->NgayXuatHang][$item->RefOperation][$i]['attr'] = $item->Ref_ThuocTinh;
			//$operationTimeArray[$item->NgayXuatHang][$item->RefOperation][$i]['bom'] = $item->BomID;
			//$operationTimeArray[$item->NgayXuatHang][$item->RefOperation][$i]['time'] = $item->OperationTime;
			if (isset($operationTimeArray[$item->NgayXuatHang][$item->RefOperation])) {
				$operationTimeArray[$item->NgayXuatHang][$item->RefOperation] += round(($item->OperationTime * $item->SoLuong) / $item->BOMQty, 2);
			} else {
				$operationTimeArray[$item->NgayXuatHang][$item->RefOperation] = round(($item->OperationTime * $item->SoLuong) / $item->BOMQty, 2);

			}
			//            echo '<br/>';echo round(($item->OperationTime * $item->SoLuong) / $item->BOMQty, 2);
		}

		//echo '<pre>';
		//print_r($outputPlan);die;

		//$refOperationArr = array();
		$operationArr = array();

		foreach ($this->_model->getOperationsByBOM($params['refBom']) as $obj) {
			//$refOperationArr[] = $obj->Ref_Ten;
			$operationArr[$obj->Ref_Ten] = $obj->Ten;
		}

		$khaNang = $this->_model->getKhaNangSanXuat(array_keys($operationArr));
		$khaNangArray = array();
		$khaNangReturn = array();
		$khaNangIndex = 0;

		foreach ($khaNang as $item) {

			$khaNangArray[$item->Ref_CongDoan][$khaNangIndex]['HieuSuat'] = $item->HieuSuat;
			$khaNangArray[$item->Ref_CongDoan][$khaNangIndex]['ThoiGian'] = Qss_Lib_Extra::getWorkingHoursOfOneWeek($item->Ref_LichLamViec);
			$khaNangIndex++;
		}

		$sDate = $this->getStartDate($params['ifid']);
		$solar = new Qss_Model_Calendar_Solar();
		$dateRange = $solar->createDateRangeArray($sDate['start'], $sDate['end']);


		//echo '<pre>'; print_r($khaNangArray); die;

		foreach ($dateRange as $date) {
			$wDay = date("w", strtotime($date));

			foreach ($khaNangArray as $key => $item) {
				$index = 0;
				foreach ($item as $in2) {
					//echo $item[$index]['ThoiGian'][$wDay].'<br/>';
					$thoigian = isset($in2['ThoiGian'][$wDay]) ? $in2['ThoiGian'][$wDay] : 0;
					$hieusuat = isset($in2['HieuSuat']) ? $in2['HieuSuat'] : 0;


					if (isset($khaNangReturn[$key])) {
						$khaNangReturn[$key] += ($thoigian * $hieusuat) / 100;
					} else {
						$khaNangReturn[$key] = ($thoigian * $hieusuat) / 100;
					}
					$index++;
				}
			}
		}
		//die;
		//echo '<pre>'; print_r($khaNangReturn); die;

		$daNenKeHoach = array();
		$daNenKeHoachObj = $this->_model->getDaNenKeHoach(array_keys($operationArr), $params['beginDate'], $params['finishDate']);

		foreach ($daNenKeHoachObj as $item) {
			if (isset($daNenKeHoach[$item->RefOperation])) {
				$daNenKeHoach[$item->RefOperation] += ($item->Time * $item->Performance) / 100;
			} else {
				$daNenKeHoach[$item->RefOperation] = ($item->Time * $item->Performance) / 100;
			}
		}

		if (isset($params['operationTimeStorage'])) {
			foreach ($params['operationTimeStorage'] as $key => $items) {
				foreach ($items as $keyin => $item) {
					if ($keyin < $params['lineNo']) {
						$daNenKeHoach[$key] += $item;
					}
				}
			}
		}

		//$this->html->refOperation = $refOperationArr;
		$this->html->operation = $operationArr;
		$this->html->begin = $params['beginDate'];
		$this->html->issueDate = $params['issueDatex'];
		$this->html->operationTime = $operationTimeArray;
		$this->html->KhaNang = $khaNangReturn;
		$this->html->DaNenKeHoach = $daNenKeHoach;
		$this->html->lineNo = $params['lineNo'];
		$this->html->BOMs = $params['refBom'];
		$this->html->outputPlan = $outputPlan;
	}

	public function detailIndexAction() {
		$model = new Qss_Model_Extra_Mrp();
		$params = $this->params->requests->getParams();
		$params['module'] = $this->_form->FormCode;
		$this->html->params = $params;
	}

	private function convertShiftIDToInfo() {
		$strCa = array();
		// Lay mang ca de chuyen ca id thanh ma ca va lay thoi gian bat dau ket thuc cua ca
		foreach ($this->extra->getTable(array('*'), 'OCa', array(), array(), 'NO_LIMIT') as $item) {
			$strCa[$item->IOID]['Ma'] = $item->MaCa;
			$strCa[$item->IOID]['Start'] = $item->GioBatDau;
			$strCa[$item->IOID]['End'] = $item->GioKetThuc;
			//$to_time   = strtotime($item->GioKetThuc);
			//$from_time = strtotime($item->GioBatDau);
			//$time_diff = abs($to_time - $from_time)/3600;
			//$strCa[$item->IOID]['Diff']   = $time_diff;
		}
		return $strCa;
	}

	private function convertLineIDToInfo() {
		$strDayChuyen = array();
		// Lay mang day chuyen de chuyen day chuyen id thanh ma day chuyen
		foreach ($this->extra->getTable(array('*'), 'ODayChuyen', array(), array(), 'NO_LIMIT') as $item) {
			$strDayChuyen[$item->IOID]['Ma'] = $item->MaDayChuyen;
		}
		return $strDayChuyen;
	}

	private function convertOperationIDToInfo() {
		$strCongDoan = array();
		// Lay mang chuyen cong doan id thanh ten cong doan
		foreach ($this->extra->getTable(array('*'), 'OCongDoan', array(), array(), 'NO_LIMIT') as $item) {
			$strCongDoan[$item->IOID]['Ma'] = $item->TenCongDoan;
		}
		return $strCongDoan;
	}

	private function convertWorkCenterIDToInfo() {
		$strDonViThucHien = array();
		// Lay mang chuyen don vi thuc hien thanh ten don vi thuc hien
		foreach ($this->extra->getTable(array('*'), 'ODonViSanXuat', array(), array(), 'NO_LIMIT') as $item) {
			$strDonViThucHien[$item->IOID]['Ma'] = $item->Ma;
		}
		return $strDonViThucHien;
	}

	// Thoi gian da xu dung cho cac ngay day chuyen ca cong doan
	private function getProductionPlaned($ifid, $startDate) {
		$daDatSanXuat = array();
		/*
		 $daLay = $this->_model->getDaLay($ifid, $startDate); // Nhung san pham da len ke hoach san xuat truoc do
		 $daDatSanXuat = array();
		 // $daDatSanXuat -> Thoi gian da su dung cho tung ngay ca day chuyen cong doan
		 foreach ($daLay as $datTruoc)
		 {

		 if(isset($daDatSanXuat[$datTruoc->Ngay][$datTruoc->Ref_DayChuyen][$datTruoc->Ref_Ca][$datTruoc->RefOperation]))
		 {
		 $daDatSanXuat[$datTruoc->Ngay][$datTruoc->Ref_DayChuyen][$datTruoc->Ref_Ca][$datTruoc->RefOperation] +=  ($datTruoc->SoGioKeHoach * $datTruoc->Performance)/100;
		 }
		 else
		 {
		 $daDatSanXuat[$datTruoc->Ngay][$datTruoc->Ref_DayChuyen][$datTruoc->Ref_Ca][$datTruoc->RefOperation]  =  ($datTruoc->SoGioKeHoach * $datTruoc->Performance)/100;
		 }



		 } // End loop da lay
		 //echo '<pre>'; print_r($daDatSanXuat); die;
		 */
		return $daDatSanXuat;
	}

	// $allManuLine: object, dc, cong doan, lich lam viec, hieu suat
	private function getInfoFromManuLine($allManuLine, $ifid) {
		// #WCalendarByLineAndShift #PerformanceByWorkcenter
		$retval = array();
		$wCalList = array();
		$operationInLine = array();
		$operationPerformance = array();

		foreach ($allManuLine as $item) {
			// Lay lich lam viec
			if (!in_array($item->Ref_LichLamViec, $wCalList)) {
				$wCalList[] = $item->Ref_LichLamViec; // working cal list
			}

			$operationInLine[$item->DCIOID][$item->CDIOID] = $item->Ref_LichLamViec; // operations in line
			$operationPerformance[$item->DCIOID][$item->CDIOID][$item->DVIOID] = $item->HieuSuat; // performance
		}
		$retval = array('wCalList' => $wCalList, 'operationInLine' => $operationInLine, 'performance' => $operationPerformance);
		return $retval;
		// #EWCalendarByLineAndShift #EPerformanceByWorkcenter
	}

	/// $wCalendar[lich][weekday[ca] = gio
	// $specialWCalendar[lich][ngay[ca] = gio
	// $operationPerformance[dc][cd][dvth] = hieu suat
	private function getCalendarAndPerformance($operationInLine, $wCalendar, $specialWCalendar, $operationPerformance) {
		// #WHoursByWCal #WHoursBySpecialWCal #TotalPerformanceByWCal #TotalPerformanceBySpecialWCal
		// Lay thoi gian hoat dong theo lich lam viec va lich dac biet cua day chuyen
		$lonNhat = array(); // Tong thoi gian cua tung ngay thu, day chuyen, ca, cong doan theo lich lam viec
		$lonNhatTheoLichDacBiet = array(); // Giong lon nhat nhung theo lich dac biet, key ko phai ngay thu ma la ngay cu the
		$tongHieuSuat = array();
		$tongHieuSuatDacBiet = array();
		$keepDayChuyenCongDoan = array();
		$keepDayChuyenCongDoanDB = array();
		//$lonNhatUTCD = array();// Lay cd lam key chinh sau ngay
		//$lonNhatTheoLichDacBietUTCD = array(); // Lay cd lam key chinh sau ngay
		$retval = array();
		$wHoursByShift = array();
		$wHoursByShiftBySpecial = array();

		foreach ($operationInLine as $dayChuyen => $congDoanDC) {
			foreach ($congDoanDC as $cd => $refLichLamViec) {
				// Lay thoi gian hoat dong theo lich lam viec cua day chuyen
				if (isset($wCalendar[$refLichLamViec])) {
					foreach ($wCalendar[$refLichLamViec] as $weekday => $ca) {
						foreach ($ca as $refCa => $soGio) {
							$wHoursByShift[$refCa] = $soGio;
							if (!isset($lonNhat[$weekday][$dayChuyen][$refCa][$cd])) {
								$lonNhat[$weekday][$dayChuyen][$refCa][$cd] = 0;
								//$lonNhatUTCD[$weekday][$cd][$dayChuyen][$refCa] = 0;
							}

							foreach ($operationPerformance[$dayChuyen][$cd] as $donVi => $hieuSuat) {
								$lonNhat[$weekday][$dayChuyen][$refCa][$cd] += ($soGio * $hieuSuat) / 100;
								//$lonNhatUTCD[$weekday][$cd][$dayChuyen][$refCa] += ($soGio * $hieuSuat) / 100;

								$code = $cd . '_' . $donVi;
								if (!in_array($code, $keepDayChuyenCongDoan)) {
									if (!isset($tongHieuSuat[$dayChuyen][$cd])) {
										$tongHieuSuat[$dayChuyen][$cd] = $hieuSuat;
									} else {
										$tongHieuSuat[$dayChuyen][$cd] += $hieuSuat;
									}
									$keepDayChuyenCongDoan[] = $code;
								}
							}// End loop ca don vi thuc hien
						} // End loop ca
					} // End lich lam viec theo ca
				} //End if isset lich lam viec theo ca
				// Lay thoi gian hoat dong theo lich dac biet
				if (isset($specialWCalendar[$refLichLamViec])) {
					foreach ($specialWCalendar[$refLichLamViec] as $ngay => $ca) {
						foreach ($ca as $refCa => $soGio) {
							$wHoursByShiftBySpecial[$refCa] = $soGio;
							if (!isset($lonNhatTheoLichDacBiet[$ngay][$dayChuyen][$refCa][$cd])) {
								$lonNhatTheoLichDacBiet[$ngay][$dayChuyen][$refCa][$cd] = 0;
								//$lonNhatTheoLichDacBietUTCD[$ngay][$cd][$dayChuyen][$refCa] = 0;
							}

							foreach ($operationPerformance[$dayChuyen][$cd] as $donVi => $hieuSuat) {
								$lonNhatTheoLichDacBiet[$ngay][$dayChuyen][$refCa][$cd] += ($soGio * $hieuSuat) / 100;
								//$lonNhatTheoLichDacBietUTCD[$ngay][$cd][$dayChuyen][$refCa] += ($soGio * $hieuSuat) / 100;

								$code = $cd . '_' . $donVi;
								if (!in_array($code, $keepDayChuyenCongDoanDB)) {
									if (!isset($tongHieuSuatDacBiet[$dayChuyen][$cd])) {
										$tongHieuSuatDacBiet[$dayChuyen][$cd] = $hieuSuat;
									} else {
										$tongHieuSuatDacBiet[$dayChuyen][$cd] += $hieuSuat;
									}
									$keepDayChuyenCongDoanDB[] = $code;
								}
							}// End loop ca don vi thuc hien
						} // End loop ca
					}
				}// End if isset lich dac biet theo ca
			} // En loop cong doan dc
		} // En loop day chuyen
		// #EWHoursByWCal #EWHoursBySpecialWCal #ETotalPerformanceByWCal #ETotalPerformanceBySpecialWCal
		//echo '<pre>'; print_r($lonNhatUTCD); die;
		// #TotalPerformance
		$tongHieuSuat = $tongHieuSuat + $tongHieuSuatDacBiet; // Merge ra mang tong hieu suat
		$wHoursByShift = $wHoursByShift + $wHoursByShiftBySpecial;
		// #ETotalPerformance
		$retval = array('TotalPerformance' => $tongHieuSuat, 'WorkingCalendar' => $lonNhat, 'SpecialWorkingCalendar' => $lonNhatTheoLichDacBiet, 'WHoursByShift' => $wHoursByShift);
		return $retval;
	}

	// @todo: Sau phai sua lay ton kho ban dau va da su dung chi cua thanh phan hoac dong chinh
	// cua ke hoach san pham cua xu ly don hang hien tai
	private function getInventoryOfBeginDate($beginDate, $ifid) {//$params['ifid']
		// #Inventory
		// Ton kho lay truc tiep tu kho chua tru ke hoach khac
		$tonKhoBanDauTmp = array(); // Mang tmp ton kho ban dau
		$tonKhoTheoNgay = array();

		foreach ($this->_model->getTonKhoBanDau() as $item) {
			$tonKhoBanDauTmp[$item->Ref_MaSP][$item->Ref_ThuocTinh] = $item->SoLuong;
		}

		// Tru di kho da dat truoc tinh tu ngay hien tai
		foreach ($this->_model->getStockUsedFromPurchasePlan($ifid) as $item) {
			if (isset($tonKhoBanDauTmp[$item->Ref_MaSP][$item->Ref_ThuocTinh])) {
				$tonKhoBanDauTmp[$item->Ref_MaSP][$item->Ref_ThuocTinh] -= $item->KhauTruKho;
				//$tonKhoBanDau[$item->Ref_MaSP][$item->Ref_ThuocTinh] -= $item->KhauTruKho;
			}
		}

		// Ton kho theo ngay cua ngay dau tien
		foreach ($tonKhoBanDauTmp as $item => $attrs) {
			foreach ($attrs as $attr => $qty) {
				$tonKhoTheoNgay[$beginDate][$item][$attr] = $qty;
			}
		}

		return $tonKhoTheoNgay;
		// #EInventory
	}

	private function getBOMConfig($generalPlan, $outputGeneral) {
		$keepThanhPhanCongDoan = array();
		$keepSanPhamDauRa = array();
		$outItemIndex = 0;
		$BOMConfig = array(); // Mang cong doan cua thiet ke san pham
		$operationKey = array(); // Mang chua cong doan theo bom theo thu tu dinh nghia trong bom
		$oldBom = '';
		$prevNext = array();
		$lastLevel = array();


		foreach ($generalPlan as $planItem) {

			if ($oldBom != $planItem->Ref_BOM) {
				$oldLevel = ''; // reset lai old level de tinh level cho tung bom
				$oldLevelTwo = 1;
			}
			$oldBom = $planItem->Ref_BOM;

			if (!isset($BOMConfig[$planItem->Ref_BOM]))
			$BOMConfig[$planItem->Ref_BOM] = array();
			if (!key_exists($planItem->RefOperation, $BOMConfig[$planItem->Ref_BOM])) {
				$currentLevel = ($oldLevel == '' || $oldLevel == $planItem->OperationLevel) ? $oldLevelTwo :  ++$oldLevelTwo;
				$thanhPhanIndex = 0;
				$oldLevel = $planItem->OperationLevel;

				$BOMConfig[$planItem->Ref_BOM][$planItem->RefOperation]['CongDoan'] = $planItem->RefOperation;
				$BOMConfig[$planItem->Ref_BOM][$planItem->RefOperation]['TenCongDoan'] = $planItem->Operation;
				$BOMConfig[$planItem->Ref_BOM][$planItem->RefOperation]['SoGio'] = $planItem->SoGioCongDoan;
				$BOMConfig[$planItem->Ref_BOM][$planItem->RefOperation]['TonTai'] = 0;
				$BOMConfig[$planItem->Ref_BOM][$planItem->RefOperation]['CoSoLuongChuyen'] = 0;
				$BOMConfig[$planItem->Ref_BOM][$planItem->RefOperation]['Level'] = $currentLevel;
				$BOMConfig[$planItem->Ref_BOM][$planItem->RefOperation]['ThanhPhan'] = array();
				$BOMConfig[$planItem->Ref_BOM][$planItem->RefOperation]['SanPhamDauRa'] = array();

				// Cau hinh san pham dong chinh. @todo: nen tach rieng ra thanh mot mang khong nen gan vao cong doan se lam cham
				$BOMConfig[$planItem->Ref_BOM][$planItem->RefOperation]['BOM'] = $planItem->BOM;
				$BOMConfig[$planItem->Ref_BOM][$planItem->RefOperation]['MainKey'] = $planItem->RefMainItem . '-' . $planItem->RefMainAttribute;
				$BOMConfig[$planItem->Ref_BOM][$planItem->RefOperation]['RefItem'] = $planItem->RefMainItem;
				$BOMConfig[$planItem->Ref_BOM][$planItem->RefOperation]['ItemCode'] = $planItem->MainItem;
				$BOMConfig[$planItem->Ref_BOM][$planItem->RefOperation]['RefAttribute'] = $planItem->RefMainAttribute;
				$BOMConfig[$planItem->Ref_BOM][$planItem->RefOperation]['Attribute'] = $planItem->MainAttribute;
				$BOMConfig[$planItem->Ref_BOM][$planItem->RefOperation]['SoLuongBOM'] = $planItem->SoLuongBOM;
				$BOMConfig[$planItem->Ref_BOM][$planItem->RefOperation]['SoLuongToiThieu'] = $planItem->SoLuongToiThieu;
				$BOMConfig[$planItem->Ref_BOM][$planItem->RefOperation]['ThaoDo'] = $planItem->ThaoRoLapDat;
				$BOMConfig[$planItem->Ref_BOM][$planItem->RefOperation]['ChinhMuaHang'] = $planItem->ChinhMuaHang;
				$BOMConfig[$planItem->Ref_BOM][$planItem->RefOperation]['ChinhSanXuat'] = $planItem->ChinhSanXuat;

				// cau hinh khac
				$prevNext[$planItem->Ref_BOM][$currentLevel] = $planItem->RefOperation;
				$lastLevel[$planItem->Ref_BOM] = $currentLevel;
				$operationKey[$planItem->Ref_BOM][] = $planItem->RefOperation;
			}

			$code = ($planItem->Ref_BOM . '-' . $planItem->RefOperation . '-' . $planItem->Ref_MaThanhPhan . '-' . $planItem->RefThuocTinhThanhPhan);
			// #LayThanhPhanTheoCongDoan
			// Lay thanh phan san pham cua tung cong doan theo thiet ke

			if (!in_array($code, $keepThanhPhanCongDoan)) {
				//CongDoan MaThanhPhan TenThanhPhan ThuocTinh  	ThaoRoLapDat  	DonViTinh  	SoLuong
				if ($planItem->CoThanhPhan) {
					$BOMConfig[$planItem->Ref_BOM][$planItem->RefOperation]['ThanhPhan'][$thanhPhanIndex]['MaThanhPhan'] = $planItem->MaThanhPhan;
					$BOMConfig[$planItem->Ref_BOM][$planItem->RefOperation]['ThanhPhan'][$thanhPhanIndex]['TenThanhPhan'] = $planItem->TenThanhPhan;
					$BOMConfig[$planItem->Ref_BOM][$planItem->RefOperation]['ThanhPhan'][$thanhPhanIndex]['CongDoan'] = $planItem->CongDoanThanhPhan;
					$BOMConfig[$planItem->Ref_BOM][$planItem->RefOperation]['ThanhPhan'][$thanhPhanIndex]['RefOperation'] = $planItem->Ref_CongDoanThanhPhan;
					$BOMConfig[$planItem->Ref_BOM][$planItem->RefOperation]['ThanhPhan'][$thanhPhanIndex]['ThuocTinh'] = $planItem->ThuocTinhThanhPhan;
					$BOMConfig[$planItem->Ref_BOM][$planItem->RefOperation]['ThanhPhan'][$thanhPhanIndex]['ThaoRoLapDat'] = $planItem->ThaoRoLapDat;
					$BOMConfig[$planItem->Ref_BOM][$planItem->RefOperation]['ThanhPhan'][$thanhPhanIndex]['BanThanhPham'] = $planItem->BanThanhPham;
					$BOMConfig[$planItem->Ref_BOM][$planItem->RefOperation]['ThanhPhan'][$thanhPhanIndex]['SoLuong'] = $planItem->SoLuongThanhPhan;
					$BOMConfig[$planItem->Ref_BOM][$planItem->RefOperation]['ThanhPhan'][$thanhPhanIndex]['SoGio'] = $planItem->SoGioCongDoan;
					$BOMConfig[$planItem->Ref_BOM][$planItem->RefOperation]['ThanhPhan'][$thanhPhanIndex]['SoLuongBOM'] = $planItem->SoLuongBOM;
					$BOMConfig[$planItem->Ref_BOM][$planItem->RefOperation]['ThanhPhan'][$thanhPhanIndex]['Key'] = $planItem->Ref_MaThanhPhan . '-' . $planItem->RefThuocTinhThanhPhan;
					$BOMConfig[$planItem->Ref_BOM][$planItem->RefOperation]['ThanhPhan'][$thanhPhanIndex]['RefMaThanhPhan'] = $planItem->Ref_MaThanhPhan;
					$BOMConfig[$planItem->Ref_BOM][$planItem->RefOperation]['ThanhPhan'][$thanhPhanIndex]['RefThuocTinhThanhPhan'] = (int) $planItem->RefThuocTinhThanhPhan;
					$BOMConfig[$planItem->Ref_BOM][$planItem->RefOperation]['ThanhPhan'][$thanhPhanIndex]['SoLuongThanhPhan'] = $planItem->SoLuongThanhPhan;
					$BOMConfig[$planItem->Ref_BOM][$planItem->RefOperation]['ThanhPhan'][$thanhPhanIndex]['ChinhMuaHang'] = $planItem->ChinhMuaHang;
					$BOMConfig[$planItem->Ref_BOM][$planItem->RefOperation]['ThanhPhan'][$thanhPhanIndex]['ChinhSanXuat'] = $planItem->ChinhSanXuat;
					$BOMConfig[$planItem->Ref_BOM][$planItem->RefOperation]['ThanhPhan'][$thanhPhanIndex]['ThanhPhanMuaHang'] = $planItem->ThanhPhanMuaHang;
					$BOMConfig[$planItem->Ref_BOM][$planItem->RefOperation]['ThanhPhan'][$thanhPhanIndex]['ThanhPhanSanXuat'] = $planItem->ThanhPhanSanXuat;
					$thanhPhanIndex++;
					$keepThanhPhanCongDoan[] = $code;
				}
			}
		}

		$keepIndexStartAtZero = array();
		foreach ($outputGeneral as $item) {
			if ($item->HasOutput && isset($BOMConfig[$item->RefBOM][$item->RefOperation])) {
				if (!isset($keepIndexStartAtZero[$planItem->Ref_BOM]))
				$keepIndexStartAtZero[$planItem->Ref_BOM] = array();
				if (!in_array($planItem->RefOperation, $keepIndexStartAtZero[$planItem->Ref_BOM])) {
					//$planItem->Ref_MaThanhPhan
					$outItemIndex = 0;
					$keepIndexStartAtZero[$planItem->Ref_BOM][] = $planItem->RefOperation;
				}
				$code = ($item->RefBOM . '-' . $item->RefOperation . '-' . $item->RefOutputItem . '-' . $item->RefOutputAttribute);
				if (!in_array($code, $keepSanPhamDauRa)) {
					$BOMConfig[$item->RefBOM][$item->RefOperation]['SanPhamDauRa'][$outItemIndex]['Key'] = $item->RefOutputItem . '-' . $item->RefOutputAttribute;
					$BOMConfig[$item->RefBOM][$item->RefOperation]['SanPhamDauRa'][$outItemIndex]['RefItem'] = $item->RefOutputItem;
					$BOMConfig[$item->RefBOM][$item->RefOperation]['SanPhamDauRa'][$outItemIndex]['ItemCode'] = $item->ItemCode;
					$BOMConfig[$item->RefBOM][$item->RefOperation]['SanPhamDauRa'][$outItemIndex]['ItemName'] = $item->ItemName;
					$BOMConfig[$item->RefBOM][$item->RefOperation]['SanPhamDauRa'][$outItemIndex]['RefAttribute'] = (int) $item->RefOutputAttribute;
					$BOMConfig[$item->RefBOM][$item->RefOperation]['SanPhamDauRa'][$outItemIndex]['Attribute'] = $item->Attribute;
					$BOMConfig[$item->RefBOM][$item->RefOperation]['SanPhamDauRa'][$outItemIndex]['UOM'] = $item->UOM;
					$BOMConfig[$item->RefBOM][$item->RefOperation]['SanPhamDauRa'][$outItemIndex]['Qty'] = $item->Qty;
					$BOMConfig[$item->RefBOM][$item->RefOperation]['SanPhamDauRa'][$outItemIndex]['BatDauTruoc'] = $item->QtyToNext;
					if (is_numeric($item->QtyToNext) && $item->QtyToNext) {
						$BOMConfig[$item->RefBOM][$item->RefOperation]['CoSoLuongChuyen'] = 1;
					}
					$outItemIndex++;
					$keepSanPhamDauRa[] = $code;
				}
			}
		}

		$retval = array('Config' => $BOMConfig, 'Key' => $operationKey, 'PrevNext' => $prevNext, 'LastLevel' => $lastLevel);
		return $retval;
	}

	private function getAvailableTime($beginDate, $wHoursByDate, $planedProduction) {
		$soGioConThua = array();
		foreach ($wHoursByDate as $dc => $ca) {
			foreach ($ca as $refCa => $cd) {
				if ($refCa != 0) {
					foreach ($cd as $cdk => $soGio) {
						$datSanXuatTmp = (isset($planedProduction[$dc][$refCa][$cdk]) && $planedProduction[$dc][$refCa][$cdk]) ? $planedProduction[$dc][$refCa][$cdk] : 0;
						$tmpSoGioConThua = $wHoursByDate[$dc][$refCa][$cdk] - $datSanXuatTmp;
						$soGioConThua[$beginDate][$dc][$refCa][$cdk] = $tmpSoGioConThua;
					}
				}
				//$soGioConThua[$beginDate][$dc][$ca]
			}// End loop ca theo lich su dung cho ngay
		} // End loop lich su dung cho ngya
		return $soGioConThua;
	}

	private function createArrayForgetMaterialByBOMQtyFunc($BomID, $key, $mainOrSub, $data) {
		$retval = array();
		if ($mainOrSub == 1) { // Gan thanh phan la dong chinh
			$retval['Key'] = $data['MainKey'];
			$retval['RefItem'] = $data['RefItem'];
			$retval['Item'] = $data['ItemCode'];
			$retval['RefAttribute'] = $data['RefAttribute'];
			$retval['Attribute'] = $data['Attribute'];
			$retval['Qty'] = $data['SoLuongBOM'];
			$retval['BomQTY'] = $data['SoLuongBOM'];
			$retval['Purchase'] = $data['ChinhMuaHang'];
			$retval['Production'] = $data['ChinhSanXuat'];
			$retval['Operation'] = $data['CongDoan'];
			$retval['Time'] = $data['SoGio'];
		} elseif ($mainOrSub == 2) { // Gan thanh phan la thanh phan san pham
			$retval['Key'] = $data['Key'];
			$retval['RefItem'] = $data['RefMaThanhPhan'];
			$retval['Item'] = $data['MaThanhPhan'];
			$retval['RefAttribute'] = $data['RefThuocTinhThanhPhan'];
			$retval['Attribute'] = $data['ThuocTinh'];
			$retval['Qty'] = $data['SoLuong'];
			$retval['BomQTY'] = $data['SoLuongBOM'];
			$retval['Purchase'] = $data['ThanhPhanMuaHang'];
			$retval['Production'] = $data['ThanhPhanSanXuat'];
			$retval['Operation'] = $data['RefOperation'];
			$retval['Time'] = $data['SoGio'];
		} else { // Gan san pham dau ra
			$retval['Key'] = $data['Key'];
			$retval['Item'] = $data['ItemCode'];
			$retval['Attribute'] = $data['Attribute'];
			$retval['Qty'] = $data['Qty'];
		}
		return $retval;
	}

	// Ham lay nguyen vat lieu yeu cau theo bom config
	// so luong tra ve trong mang co the la 0, duong hoac am
	// 0 -> khau tru het san pham dau ra cua cong doan truoc (voi thanh phan la san pham dau ra cd truoc)
	// duong -> so luong thanh phan thieu can lay them trong kho (voi thanh phan la san pham dau ra cd truoc hoac ko  phai)
	// am -> so luong thanh phan thua can nhap kho (voi thanh phan la san pham dau ra cd truoc)
	// @todo: cong don so luong cua thanh phan giong nhau
	private function getMaterialByBOMQty($BOMConfig) {
		// @note: luu y san pham dau ra co the lon hon, nho hon hoac bang
		// thanh phan tuonng ung cua no o cong doan sau
		$retval = array();
		$output = array(); // Set lai san pham dau ra cho tung bom
		// @note: San pham dau ra khong bao gom san pham cuoi cung doi voi lap dat
		// @note: Voi thao ro cong doan cuoi cung van co san pham dau ra

		foreach ($BOMConfig as $BomID => $operation) { // Lap qua cac bom
			$key = array();
			foreach ($operation as $operationID => $info) { // Cong doan cua mot bom
				if ($info['ThaoDo']) { // Neu la thao ro thi dong chinh la dau vao
					// Tru di san pham dau ra cua
					// Neu bom la thao do thi sp dong chinh
					// va thanh phan san pham la nguyen vat lieu
					$key[] = $info['MainKey'];
					$retval[$BomID][$info['MainKey']] = $this->createArrayForgetMaterialByBOMQtyFunc($BomID, $info['MainKey'], 1, $info);

					foreach ($info['ThanhPhan'] as $memberIndex => $memberInfo) {
						$key[] = $memberInfo['Key'];
						$retval[$BomID][$memberInfo['Key']] = $this->createArrayForgetMaterialByBOMQtyFunc($BomID, $memberInfo['Key'], 2, $memberInfo);
					}

					// Lay san pham dau ra voi truong hop thao do
					foreach ($info['SanPhamDauRa'] as $oItem) {
						$output[$BomID][$oItem['Key']] = $this->createArrayForgetMaterialByBOMQtyFunc($BomID, $oItem['Key'], 3, $oItem);
					}
				} else { // Neu bom la lap dat thi nguyen vat lieu lay trong thanh phan san pham
					foreach ($info['ThanhPhan'] as $memberIndex => $memberInfo) {
						$key[] = $memberInfo['Key'];
						$retval[$BomID][$memberInfo['Key']] = $this->createArrayForgetMaterialByBOMQtyFunc($BomID, $memberInfo['Key'], 2, $memberInfo);
					}

					// Lay san pham dau ra voi truong hop lap dat
					foreach ($info['SanPhamDauRa'] as $oItem) {
						$output[$BomID][$oItem['Key']] = $this->createArrayForgetMaterialByBOMQtyFunc($BomID, $oItem['Key'], 3, $oItem);
					}
				} // Kiem tra thao do hay lap dat de lay thanh phan
			} // lap qua cong doan cua mot bom

			foreach ($key as $k) {
				if (isset($output[$BomID][$k]) && isset($retval[$BomID][$k])) {
					$retval[$BomID][$k]['Qty'] = $retval[$BomID][$k]['Qty'] - $output[$BomID][$k]['Qty'];
					$retval[$BomID][$k]['Qty'] = ($retval[$BomID][$k]['Qty'] >= 0) ? $retval[$BomID][$k]['Qty'] : 0;
				}
			}
		}  // Lap qua cac bom
		return $retval;
	}

	private function testing_getMaterialByBOMQty($retval, $output, $key) {
		foreach ($retval[$key] as $item) {
			foreach ($output[$key] as $o) {

				if ($item['Key'] == $o['Key']) {
					echo '<div class="bggreen bold white">' . $item['Key'] . '-' . $o['Key'] . '</div>';
				} else {
					echo '<div class="bgred bold yellow">' . $item['Key'] . '-' . $o['Key'] . '</div>';
				}
			}
			echo '<div class="bgyellow">&nbsp;</div>';
		}
	}

	private function sortArrayByKey($keys, $origarray) {
		$res = array();
		foreach ($keys as $key) {
			if (isset($origarray[$key]) && $origarray[$key]) {
				$res[$key] = $origarray[$key];
			}
		}
		return $res;
	}

	private function getStartDate($ifid) {
		$planItems = $this->_common->getTable(array('*'), 'OKeHoachSanPham'
		, array('IFID_M901' => $ifid), array(), 'NO_LIMIT');

		$min = '';
		$max = '';
		foreach ($planItems as $item) {
			if ($min == '' || $min > $item->NgayBatDau) {
				$min = $item->NgayBatDau;
			}

			if($max == '' || $max < $item->NgayKetThuc) {
				$max = $item->NgayKetThuc;
			}
		}
		$ret = array('start'=>$min, 'end'=>$max);
		return $ret;
	}

	// @todo: Xu ly cong doan song song va cong doan noi tiep, do neu cong doan noi tiep thi
	// cong doan truoc do phai duoc thuc hien truoc moi den cong doan sau, doi voi song song
	// thi hai cong doan co the dong thoi xay ra
	// @todo: Mot cong doan can phai co dau ra va dau vao
	// @todo: Cac san pham duoc tao thanh tu mot cong doan (ko phai la sp cuoi cung, chi la ban thanh pham)
	// thi co the la ko mua cung ko sx hay la phai co cau thanh rieng?
	public function detailRunAction() {
		$model = new Qss_Model_Extra_Mrp();
		$common = new Qss_Model_Extra_Extra();
		$params = $this->params->requests->getParams();
		$params['module'] = $this->_form->FormCode;
		//		$beginDate = $params['startDate']; // Lay ngay bat dau la ngay bat dau sx trong ke hoach
		$productionPlan = array(); // Mang ke hoach san xuat
		$ppIndex = 0;
		$purchasePlan = array(); // Ke hoach mua hang
		$purIndex = 0;
		$nextOperationArr = array(); // xac dinh thoi gian bat dau cua cdoan tiep theo, dvt la gio
		$startDate = $this->getStartDate($params['ifid']);
		$startDate = $startDate['start'];

		// #echo (testing)
		//echo '<pre>'; print_r($params); die;
		// @todo: chuyen $msg ve dang array('error'=>number, 'info', 'info') de chuyen bao loi sang file phtml
		$msg = ''; // In loi ko the san xuat trong mot gioi han ngay
		$coTheSXCuaDonVi = array();
		$minCongDoan = array(); // Kha nang
		$oldPlanItem = array(); // Chi dung trong vong lap duoi
		$timeLimit = 100; // So ngay neu ko co thay doi ve so luong thi se bao ko the san xuat
		// #ConvertIDToDisplay: Lay mang chuyen ca, day chuyen, cong doan, don vi thuc hien tu id ve dang hien thi
		// -> Chuyen nhung thong so nay ve dang co the nhap duoc vao csdl
		$strDayChuyen = $this->convertLineIDToInfo(); // Quy doi id ve ten
		$strCa = $this->convertShiftIDToInfo(); // Quy doi id ve ten
		$strCongDoan = $this->convertOperationIDToInfo(); // Quy doi id ve ten
		$strDonViThucHien = $this->convertWorkCenterIDToInfo(); // Quy doi id ve ten
		// #echo (testing)
		//echo '<pre>'; echo 'Day chuyen:'; echo '<pre>'; print_r($strDayChuyen);
		//echo '<pre>'; echo 'Ca:'; echo '<pre>'; print_r($strCa);
		//echo '<pre>'; echo 'Cong doan:'; echo '<pre>'; print_r($strCongDoan);
		//echo '<pre>'; echo 'Don vi thuc hien:'; echo '<pre>'; print_r($strDonViThucHien);
		//die;
		#echo (testing)
		//echo '<pre>'; print_r($daDatSanXuat); die;
		// #WCalendarByLineAndShift #PerformanceByWorkcenter
		// -> $allManuLine - Lich lam viec theo d.chuyen, hieu suat theo don vit
		// -> Lay duoc mang lich lam viec, cong doan day chuyen, hieu suat don vi
		// --> lich lam viec hang ngay va lich lam viec dac biet, sau se suy tiep ra thoi gian cho tung ngay
		// day chuyen, ca, cong doan theo hai lich sau dung de tinh dung thoi gian cua lich hang ngay hay
		// lich dac biet cho tung ngay
		$allManuLine = $this->_model->getCalendarsByItemsOfLine($params['ifid']); // Lay day chuyen lich lam viec, cong doan day chuyen
		$getInfoFromManuLine = $this->getInfoFromManuLine($allManuLine, $params['ifid']); // cong doan/day chuyen, hieu suat don vi, lich lam viec
		$workingHoursPerShift = Qss_Lib_Extra::getWorkingHoursPerShiftByCal($getInfoFromManuLine['wCalList']); //TG NgayThu>DC>Ca
		$lichDacBiet = Qss_Lib_Extra::getLichDacBiet($getInfoFromManuLine['wCalList'], $startDate); // LDB Ngay>DC>Ca
		// Tinh ra duoc thoi gian lam viec theo lich hang ngay va lich dac biet theo ngay day chuyen ca cong doan
		// va tong hieu suat cua tung cong doan
		$wHoursAndPerformance = $this->getCalendarAndPerformance($getInfoFromManuLine['operationInLine'], $workingHoursPerShift, $lichDacBiet, $getInfoFromManuLine['performance']);

		// #Inventory: Lay ton kho ban dau theo ngay bang tong kho hien tai tru cho so luong dat cho lenh
		// $tonKhoTheoNgay[ngay][S.pham id][t.tinh id]
		// ton kho hien tai truoc luc chay mrp cua ke hoach
		// co the xung dot voi kho khi nhap kho va xuat kho dung luc thuc hien ke hoach @Note
		//		$tonKhoTheoNgay = $this->getInventoryOfBeginDate($beginDate, $params['ifid']);
		// $BOMConfig: BOm config lay cau hinh cua mot bom gom thong tin chinh, thanh phan va san pham dau ra
		$generalPlan = $model->getGeneralPlanForDetail($params['ifid']); // Ke hoach san pham bao gom bom cong doan
		$outputGeneral = $model->getGeneralPlanOutputItemsForDetail($params['ifid']); // San pham dau ra cho mrp
		$BOMConfigure = $this->getBOMConfig($generalPlan, $outputGeneral);
		//$BOMConfig =  $BOMConfigure['Config'];
		//$operationKey = $BOMConfigure['Key'];
		//$prevNextOperation = $BOMConfigure['PrevNext'];
		//$lastLevel = $BOMConfigure['LastLevel'];
		// So luong thanh phan yeu cau dua tren so luong cai dat cua bom
		$getMaterialByBOMQty = $this->getMaterialByBOMQty($BOMConfigure['Config']);

		// #TimeToUsed: Thoi gian da su dung de san xuat trong cac ke hoach khac
		// -> De tinh thoi gian con thua cua tung dc, ca, đv theo ngay
		// -> $daLay - Thoi gian da lay => $daDatSanXuat[Ngay][D.Chuyen ID][Ca ID][C.Đoạn ID]
		$daDatSanXuat = $this->getProductionPlaned($params['ifid'], $params['startDate']); // So gio da dat san xuat
		//#Block0: Xu ly theo dong ke hoach san pham voi tung ngay de lay ra san xuat va mua hang cho tung ngay
		// #Block1: Kiem tra bao loi neu sau n lan so luong san pham ko thay doi
		// #Block2: Tim min thoi gian doi ra so luong cua tat ca cong doan dua vao thoi gian con thua cho cong doan
		// #Block3: Kha nang thanh phan co the dap ung, lay theo thanh phan dap ung nho nhat
		// #Block4: Tinh ra tong so luong co the san xuat theo ngay va theo day chuyen ca
		//#Block0: Xu ly theo dong ke hoach san pham voi tung ngay de lay ra san xuat va mua hang cho tung ngay


		foreach ($generalPlan as $planItem) {


			if (!in_array($planItem->KHSPIOID, $oldPlanItem)) {

				$beginDate = $planItem->NgayBatDau;
				$tonKhoTheoNgay = $this->getInventoryOfBeginDate($beginDate, $params['ifid']);

				// Thiet lap gia tri ban dau cho mang cau hinh bom, bao gom tat ca thong tin cua bom san pham
				$BOMConfigure['Config'][$planItem->Ref_BOM] = isset($BOMConfigure['Config'][$planItem->Ref_BOM]) ? $BOMConfigure['Config'][$planItem->Ref_BOM] : array();  // Toan bo cong doan theo thiet ke
				// Thiet lap mang thanh phan yeu cau
				$materialRequired = isset($getMaterialByBOMQty[$planItem->Ref_BOM]) ? $getMaterialByBOMQty[$planItem->Ref_BOM] : array();


				if ($planItem->SoLuongSX > 0) {
					// while(het san pham)
					while ($planItem->SoLuongSX > 0) {
						// #Block1: Kiem tra bao loi neu sau n lan so luong san pham ko thay doi
						if (!isset($time)) {
							$time = 0;
						}
						if ($time == $timeLimit) { // Bao loi neu sau mot $timeLimit vong lap
							if ($old == $planItem->SoLuongSX) {
								// @todo: chuyen qua file phtml
								$msg .= "Trong vòng 100 ngày, mặt hàng {$planItem->MaSP} không thể sản xuất được {$planItem->SoLuongSX} {$planItem->DonViTinh}<br/>"; //$this->_translate(1);
								$msg .= "- Kiểm tra lại số lượng tối thiểu cho một lệnh sản xuất.<br/>"; //$this->_translate(1);
								$msg .= "- Kiểm tra lại số lượng của chi tiết.<br/>"; //$this->_translate(1);
								break;
							} else {
								$time = 0;
							}
						}
						$old = $planItem->SoLuongSX;
						$time++;
						// End #Block1: Kiem tra bao loi neu sau n lan so luong san pham ko thay doi


						$sanXuatTheoNgay = array();  // Reset lai san xuat theo ngay
						$wWeekday = date('w', strtotime($beginDate));
						$nextDate = date('Y-m-d', strtotime('+1 day', strtotime($beginDate)));
						$prevDate = date('Y-m-d', strtotime('-1 day', strtotime($beginDate)));

						// Lich su dung theo ngay
						$wHoursByDate = (isset($wHoursAndPerformance['SpecialWorkingCalendar'][$beginDate])) ? $wHoursAndPerformance['SpecialWorkingCalendar'][$beginDate] : @$wHoursAndPerformance['WorkingCalendar'][$wWeekday];
						// Da dat san xuat cho tung day chuyen ca cong doan theo ngay
						$datSanXuat = (isset($daDatSanXuat[$beginDate])) ? $daDatSanXuat[$beginDate] : array();
						// So gio con thua cho day chuyen ca cong doan theo ngay
						$soGioConThua = $this->getAvailableTime($beginDate, $wHoursByDate, $datSanXuat);


						// Neu co ton tai gio thua ta bat dau tinh
						// @Note: ngay nghi ko co thoi gian lam viec
						if (isset($soGioConThua[$beginDate])) {

							//#Block2: Tim min thoi gian doi ra so luong cua tat ca cong doan dua vao thoi gian con thua cho cong doan
							// @NOte: doi voi thao do thi tuong ung voi co the thao ro duoc bao nhieu
							// @Note: doi voi lap dat thi la co the lap dat duoc bao nhieu
							foreach ($soGioConThua[$beginDate] as $dc => $ca) {
								foreach ($ca as $refCa => $cd) {
									$min = '';
									$congDoanTemp = '';
									foreach ($BOMConfigure['Config'][$planItem->Ref_BOM] as $cdk) {
										if (isset($soGioConThua[$beginDate][$dc][$refCa][$cdk['CongDoan']])) {
											$timeToQty = 0;
											// @todo: Can phai xem lai doan code nay, soGioChoCongDoan khong ro rang
											$soChia = $wHoursAndPerformance['TotalPerformance'][$dc][$cdk['CongDoan']] ? ($wHoursAndPerformance['TotalPerformance'][$dc][$cdk['CongDoan']] / 100) : 1;
											$soBiChia = $wHoursAndPerformance['TotalPerformance'][$dc][$cdk['CongDoan']] ? $soGioConThua[$beginDate][$dc][$refCa][$cdk['CongDoan']] : 0;

											$soGioChoCongDoan = $soBiChia / $soChia;
											//$soGioChoCongDoan = $wHoursAndPerformance['TotalPerformance'][$dc][$cdk['CongDoan']]?$soGioConThua[$beginDate][$dc][$refCa][$cdk['CongDoan']]:0;

											foreach ($getInfoFromManuLine['performance'][$dc][$cdk['CongDoan']] as $donViTH => $hieuSuat) {
												if ($cdk['SoGio']) {
													//$tmp = ((($soGioChoCongDoan)/($cdk['SoGio']))/$cdk['SoLuongBOM']);
													// 100
													$hieuSuat = $hieuSuat / 100;
													$tmp = (($cdk['SoLuongBOM'] * $soGioChoCongDoan ) / $cdk['SoGio']);
													$tmp = $tmp ? $tmp : 0;
													$timeToQty += $tmp;
													$coTheSXCuaDonVi[$dc][$refCa][$cdk['CongDoan']][$donViTH] = $tmp;
												}
											}

											if ($timeToQty < $min || $min == '') {
												$min = $timeToQty;
												$congDoanTemp = $cdk['CongDoan'];
											}

											// Neu ngay day chuyen ca nao ko co cong doan nao thi bo qua
											if ($min != '') {
												$minCongDoan[$beginDate][$dc][$refCa]['SoLuong'] = $min; // Sau bien nay co the mang gia tri null, phai quy doi ve 0 o buoc lay
												$minCongDoan[$beginDate][$dc][$refCa]['CongDoan'] = $congDoanTemp;
											}
											$BOMConfigure['Config'][$planItem->Ref_BOM][$cdk['CongDoan']]['TonTai'] = 1;
										}
									}
								}
							} // End foreach so gio con thua de lay min va cong doan theo thiet ke
							// #End Block2: Tinh min kha nang cua cong doan
							//echo '<pre>'; print_r($minCongDoan); die;
							// #Block3: Kha nang thanh phan co the dap ung, lay theo thanh phan dap ung nho nhat
							// @todo: Co the voi lap dat khong co thanh phan se xay ra loi, b1 da bao loi nay
							// den buoc 2 co the phat sinh loi nay do sau khi kiem tra xong thi co the thanh
							// phan bi xoa di
							$minThanhPhanDapUng = '';
							foreach ($materialRequired as $mt) {
								// Neu thanh phan la mua hang thi coi nhu co the san xuat het
								if ($mt['Purchase']) {
									$dapUngThanhPhan = $planItem->SoLuongSX;
								} else { // Doi voi truong hop khong mua hang thi chi co the dua vao ton kho theo ngay
									if (isset($tonKhoTheoNgay[$beginDate][$mt['RefItem']][$mt['RefAttribute']])) {
										$soLuongTonKhoTemp = $tonKhoTheoNgay[$beginDate][$mt['RefItem']][$mt['RefAttribute']];
										if ($mt['Qty'] > 0) { // So  luong thanh phan can co
											$dapUngThanhPhan = ($soLuongTonKhoTemp * $mt['BomQTY']) / $mt['Qty'];
										}
										// Doi voi truong hop = 0, thi tuc la ban thanh pham la thanh phan vua du
										// Doi voi truong hop am, thi tuc la ban thanh pham du ra so voi thanh phan
									} else {
										$dapUngThanhPhan = 0;
									}
								}

								if ($minThanhPhanDapUng == '' || $minThanhPhanDapUng > $dapUngThanhPhan) {
									$minThanhPhanDapUng = $dapUngThanhPhan;
								}
							}
							$minThanhPhanDapUng = $minThanhPhanDapUng ? $minThanhPhanDapUng : 0;
							/*
							 // Sau khi ket thuc mot cong doan can cong san pham dau ra vao mot bien temp
							 // de tinh thanh phan cho cong doan sau
							 $minThanhPhanDapUng = '';
							 foreach ($BOMConfigure['Config'][$planItem->Ref_BOM] as $cd=>$cdArr)
							 {
							 if(isset($cdArr['ThanhPhan']) && count($cdArr['ThanhPhan']))
							 { // nEU co thanh phan
							 foreach ($cdArr['ThanhPhan'] as $index=>$tp)
							 {
							 //echo '<pre>'; print_r($tp); die;
							 // Tinh So luong yeu cau
							 $soLuongYeuCau = ($planItem->SoLuongSX*$planItem->SoLuongThanhPhan)/$planItem->SoLuongBOM;
							 // $cdArr['ThanhPhan'][$index]['SoLuongYeuCau'] = $soLuongYeuCau;

							 // Lay ton kho theo ngay cua sp
							 $tonKhoTmp = isset($tonKhoTheoNgay[$beginDate][$tp['RefMaThanhPhan']][$tp['RefThuocTinhThanhPhan']])?$tonKhoTheoNgay[$beginDate][$tp['RefMaThanhPhan']][$tp['RefThuocTinhThanhPhan']]:0;
							 $coTheSanXuat = 0;

							 // Tinh so luong co the sx
							 if($tp['ThanhPhanMuaHang'])
							 {
							 $coTheSanXuat = $planItem->SoLuongSX;
							 }
							 else
							 {
							 //$muaHang = 0;
							 if($tonKhoTmp != 0)
							 {
							 if($tonKhoTmp >= $soLuongYeuCau)
							 {
							 $coTheSanXuat = $planItem->SoLuongSX;
							 }
							 else
							 {
							 $coTheSanXuat = ($tonKhoTmp * $planItem->SoLuongBOM)/$planItem->SoLuongThanhPhan;
							 }
							 }
							 } /// Ket thuc kiem tra xem so luong kho, co the san xuat va can mua

							 if($minThanhPhanDapUng > $coTheSanXuat || $minThanhPhanDapUng === '')
							 {

							 $minThanhPhanDapUng = $coTheSanXuat;
							 $congDoan = $cd;
							 }

							 }// End loop thanh phan
							 }// if neu co thanh phan
							 else
							 {
							 $coTheSanXuat = $planItem->SoLuongSX;
							 if($minThanhPhanDapUng === '' || $minThanhPhanDapUng > $planItem->SoLuongSX)
							 {
							 $minThanhPhanDapUng = $planItem->SoLuongSX;
							 $congDoan = $cd;
							 }
							 }// if neu ko co thanh phan
							 } // End loop tinh kha nang thanh phan
							 */
							// End #Block3: Kha nang thanh phan co the dap ung, lay theo thanh phan dap ung nho nhat
							// #Block4: Tinh ra tong so luong co the san xuat theo ngay va theo day chuyen ca
							$tongSanXuatTheoNgay = 0;
							foreach ($minCongDoan[$beginDate] as $dc => $shifts) {
								foreach ($shifts as $ca => $caInfo) {
									$caInfo['SoLuong'] = ($caInfo['SoLuong'] != '') ? $caInfo['SoLuong'] : 0;
									//echo "Đáp ứng vt cho: {$minThanhPhanDapUng} sp -"."Tối thiểu {$planItem->SoLuongToiThieu} -"."Khả năng: {$caInfo['SoLuong']}<br/>";

									if ($minThanhPhanDapUng >= $planItem->SoLuongToiThieu && $caInfo['SoLuong'] >= $planItem->SoLuongToiThieu) {
										if ($caInfo['SoLuong'] < $minThanhPhanDapUng) {
											$sanXuatTheoNgay[$dc][$ca] = $caInfo['SoLuong'];
											$tongSanXuatTheoNgay += $caInfo['SoLuong'];
											$minThanhPhanDapUng -= $caInfo['SoLuong'];
										} else {
											$sanXuatTheoNgay[$dc][$ca] = $minThanhPhanDapUng;
											$tongSanXuatTheoNgay += $minThanhPhanDapUng;
											$minThanhPhanDapUng = 0;
										}
									}// endif: Kiem tra co the san xuat cho ca ko
									//echo 'San xuat:'.$sanXuatTheoNgay[$dc][$ca].'<br/>';
								}// endforeach: ca
							}// endforeach: minCongDoan
							// End #Block4: Tinh ra tong so luong co the san xuat theo ngay va theo day chuyen ca
							// #Block5: Gan mang ke hoach
							// Loop qua cac cong doan cua bom duoc sap xep theo thu tu
							// $BOMConfigure['PrevNext'][$planItem->Ref_BOM][($info['Level'] - 1)] prev operation
							// $BOMConfigure['PrevNext'][$planItem->Ref_BOM][($info['Level'] + 1)] next operation
							//echo '<pre>'; print_r($BOMConfigure['Config']); die;
							//echo '<pre>'; print_r($getInfoFromManuLine['performance']); die;
							$khaNangSXConLaiCuaDV = array(); // @todo: set lai array o day co hop ly hay ko?
							foreach ($wHoursByDate as $dc => $ca) {// Day chuyen
								foreach ($ca as $refCa => $cd) {  // Ca
									if ($refCa != 0) {
										// Sap xep cong doan cua day chuyen theo bom (thiet ke)
										if (!isset($operationKeySorted[$dc][$planItem->Ref_BOM])) {
											$operationKeySorted[$dc][$planItem->Ref_BOM] = $this->sortArrayByKey($BOMConfigure['Key'][$planItem->Ref_BOM], $cd);
										}

										// Lap qua cong doan da sap xep cua bom

										foreach ($operationKeySorted[$dc][$planItem->Ref_BOM] as $cdk => $soGio) {
											//echo '<pre>'; print_r($BOMConfigure['Config'][$planItem->Ref_BOM][$cdk]); die;
											// $getInfoFromManuLine['performance'][$dc][$cdk] -> So don vi thuc hien
											// echo '<pre>'; print_r($strDayChuyen); die;
											//if($BOMConfigure['Config'][$planItem->Ref_BOM][$cdk]['CoSoLuongChuyen'])
											//{
											// Tinh so gio bat dau cua cong doan voi begin time la so gio
											// bat dau tinh theo so luong chuyen cua cong doan truoc
											// Phai tru di so gio tieu ton cho ke hoach khac
											$beginTime = (isset($nextOperationArr[$beginDate][$planItem->KHSPIOID][$cdk])) ? $nextOperationArr[$beginDate][$planItem->KHSPIOID][$cdk] : 0;
											$beginTime += ($soGio - $soGioConThua[$beginDate][$dc][$refCa][$cdk]);
											// So sanh begin time voi so gio cua ca de xac dinh thoi gian bat dau cua cong doan
											// $nextOperationArr thoi gian venh len cho cong doan duoc tinh o sau, cd. dau tien bang 0
											if ($wHoursAndPerformance['WHoursByShift'][$refCa] <= $beginTime) {
												// Neu lon hon thi phai chuyen sang ca tiep theo va tru di so gio cua ca nay
												// Khong tao lenh san xuat
												if ((isset($nextOperationArr[$beginDate][$planItem->KHSPIOID][$cdk]))) {
													$nextOperationArr[$beginDate][$planItem->KHSPIOID][$cdk] -= $wHoursAndPerformance['WHoursByShift'][$refCa];
													$nextOperationArr[$beginDate][$planItem->KHSPIOID][$cdk] = ($nextOperationArr[$beginDate][$planItem->KHSPIOID][$cdk] > 0) ? $nextOperationArr[$beginDate][$planItem->KHSPIOID][$cdk] : 0;
												}

												$startOfShift = 0;
												$shiftTimeDiff = 0;
											} else {  // truog hop begin time nho hon gio cua ca thuc hien tao kh san xuat
												// Thi tao lenh san xuat cho ca va tru di so gio cua ca nay
												if ((isset($nextOperationArr[$beginDate][$planItem->KHSPIOID][$cdk]))) {
													$nextOperationArr[$beginDate][$planItem->KHSPIOID][$cdk] = 0;
												}
												$startOfShift = $beginTime ? date("H:m:s", strtotime($strCa[$refCa]['Start']) + $beginTime * 60 * 60) : $strCa[$refCa]['Start'];
												$shiftTimeDiff = (strtotime($strCa[$refCa]['End']) - strtotime($startOfShift)) / 3600;
											}

											// #echo
											echo '<pre>';
											print_r('Line 1376: Thoi gian bat dau ca ' . $strCa[$refCa]['Ma'] . ':' . $startOfShift);
											echo '<pre>';
											print_r('Line 1377: Thoi gian cua ca' . $strCa[$refCa]['Ma'] . ':' . $shiftTimeDiff);
											echo '<pre>';
											echo '--------------------------';


											if ($startOfShift !== 0) {
												$getInfoFromManuLine['performance'][$dc][$cdk] = isset($getInfoFromManuLine['performance'][$dc][$cdk]) ? $getInfoFromManuLine['performance'][$dc][$cdk] : array();
												$tempSanXuatTheoNgay = isset($sanXuatTheoNgay[$dc][$refCa]) ? $sanXuatTheoNgay[$dc][$refCa] : 0;
												// de ra duoc thoi gian san xuat theo ngay cua cong doan
												// phai lay so luong san xuat lien tuc ko co so luong chuyen (max)
												// tru di thoi gian venh cua cd de quy doi ra so luong
												$tempCD = 0;
												if ($beginTime > 0) {
													foreach ($getInfoFromManuLine['performance'][$dc][$cdk] as $dv => $hs) {
														$hs = $hs / 100; // @todo: Xem lai cong thuc duoi
														$tempCD += ($beginTime * $hs) / $BOMConfigure['Config'][$planItem->Ref_BOM][$cdk]['SoGio'];
													}
												}
												$tempSanXuatTheoNgay -= $tempCD;

												if ($tempSanXuatTheoNgay > 0) { // Theo day chuyen va ca
													foreach ($getInfoFromManuLine['performance'][$dc][$cdk] as $dvth => $hs) {// Don vi thuc hien
														$daDatSXTheoTungDV = 0;
														// Neu chua ton tai kha nang sx con lai cu don vi, set kha nhang bang kha nang co the sx cua don vi
														if (!isset($khaNangSXConLaiCuaDV[$beginDate][$dc][$refCa][$cdk][$dvth])) {
															$khaNangSXConLaiCuaDV[$beginDate][$dc][$refCa][$cdk][$dvth] = @(int) $coTheSXCuaDonVi[$dc][$refCa][$cdk][$dvth];
														}

														// So luong can san xuat cua dv bang kha nang con lai tru cho so luong yeu cau theo ngay dc ca
														if ($khaNangSXConLaiCuaDV[$beginDate][$dc][$refCa][$cdk][$dvth] >= $tempSanXuatTheoNgay) {
															$daDatSXTheoTungDV = $tempSanXuatTheoNgay;
															$khaNangSXConLaiCuaDV[$beginDate][$dc][$refCa][$cdk][$dvth] -= $tempSanXuatTheoNgay;
															$tempSanXuatTheoNgay = 0;
														} else {
															$daDatSXTheoTungDV = $khaNangSXConLaiCuaDV[$beginDate][$dc][$refCa][$cdk][$dvth];
															$tempSanXuatTheoNgay -= $khaNangSXConLaiCuaDV[$beginDate][$dc][$refCa][$cdk][$dvth];
															$khaNangSXConLaiCuaDV[$beginDate][$dc][$refCa][$cdk][$dvth] = 0;
														}


														if ($daDatSXTheoTungDV > 0) {
															if ($BOMConfigure['Config'][$planItem->Ref_BOM][$cdk]['TonTai']) { // Co cong doan trong it nhat mot day chuyen
																// @note: Gan mang
																// Lap dat: lay san pham dau ra voi cong doan level < last level, lay them san pham chinh voi truong hop = last level
																// Thao ro: Lay san pham dong chinh
																// Doi voi tru di so luong sx, lap dat va thao ro cung tru o cd cuoi cung
																// Neu la san xuat xong phai nhap ton kho cho san pham dau ra, tru ton kho cho tat ca san pham dau vao
																if ($BOMConfigure['Config'][$planItem->Ref_BOM][$cdk]['ThaoDo']) {
																	$productionPlan[$ppIndex]['DayChuyen'] = $strDayChuyen[$dc]['Ma'];
																	$productionPlan[$ppIndex]['Ca'] = $strCa[$refCa]['Ma'];
																	$productionPlan[$ppIndex]['CongDoan'] = $strCongDoan[$cdk]['Ma'];
																	$productionPlan[$ppIndex]['DonViThucHien'] = $strDonViThucHien[$dvth]['Ma'];
																	$productionPlan[$ppIndex]['Ngay'] = $beginDate;
																	$productionPlan[$ppIndex]['MaSP'] = $planItem->MaSP;
																	$productionPlan[$ppIndex]['TenSP'] = $planItem->SanPham;
																	$productionPlan[$ppIndex]['DonViTinh'] = $planItem->DonViKH;
																	$productionPlan[$ppIndex]['ThuocTinh'] = $planItem->ThuocTinh;
																	$productionPlan[$ppIndex]['ThietKe'] = $planItem->BOM;
																	$productionPlan[$ppIndex]['SoLuong'] = $daDatSXTheoTungDV;
																	$productionPlan[$ppIndex]['ThoiGian'] = round(abs(strtotime($strCa[$refCa]['End']) - strtotime($startOfShift)) / 60, 2);
																	$productionPlan[$ppIndex]['ThoiGianBatDau'] = $startOfShift;
																	//ceil( (($daDatSXTheoTungDV * $BOMConfig[$planItem->Ref_BOM][$cdk]['SoGio'])/$planItem->SoLuongBOM) * 60); // Chua tinh
																	$productionPlan[$ppIndex]['ThoiGianKetThuc'] = $strCa[$refCa]['End'];
																	$ppIndex++;

																	// Cong ton kho cho san pham dau ra o cac cong doan
																	foreach ($BOMConfigure['Config'][$planItem->Ref_BOM][$cdk]['SanPhamDauRa'] as $oItem) {
																		$tempQty = ($daDatSXTheoTungDV * $oItem['Qty']) / $BOMConfigure['Config'][$planItem->Ref_BOM][$cdk]['SoLuongBOM'];
																		if (isset($tonKhoTheoNgay[$beginDate][$planItem->Ref_MaSP][$planItem->Ref_ThuocTinh])) {
																			$tonKhoTheoNgay[$beginDate][$planItem->Ref_MaSP][$planItem->Ref_ThuocTinh] += $tempQty;
																		} else {
																			$tonKhoTheoNgay[$beginDate][$planItem->Ref_MaSP][$planItem->Ref_ThuocTinh] = $tempQty;
																		}
																	}

																	// Tru di thanh phan su dung, doi voi cd dau tien thi thanh phan bao gom ca san pham chinh (chi tru mot lan)
																	foreach ($BOMConfigure['Config'][$planItem->Ref_BOM][$cdk]['ThanhPhan'] as $member) {
																		$tempQty = ($daDatSXTheoTungDV * $member['Qty']) / $BOMConfigure['Config'][$planItem->Ref_BOM][$cdk]['SoLuongBOM'];

																		// Neu ton kho theo ngay cua thanh phan lon hon or = so luong can thi ko co mua
																		$tonKhoTheoNgayTmp = isset($tonKhoTheoNgay[$beginDate][$member['RefMaThanhPhan']][$member['RefThuocTinhThanhPhan']]) ? $tonKhoTheoNgay[$beginDate][$member['RefMaThanhPhan']][$member['RefThuocTinhThanhPhan']] : 0;
																		if ($tonKhoTheoNgayTmp >= $tempQty) {
																			$soLuongMua = 0;
																			$khauTruKho = $tempQty;
																		} else { // Khong du kho phai di mua phai can cu co cho mua hay ko
																			$soLuongMua = $member['ThanhPhanMuaHang'] ? ($tempQty - $tonKhoTheoNgayTmp) : 0;
																			$khauTruKho = $tonKhoTheoNgayTmp;
																		}

																		// Giam so luong trong kho cua sp
																		if (isset($tonKhoTheoNgay[$beginDate][$member['RefMaThanhPhan']][$member['RefThuocTinhThanhPhan']]) && $khauTruKho > 0) {
																			$tonKhoTheoNgay[$beginDate][$member['RefMaThanhPhan']][$member['RefThuocTinhThanhPhan']] -= $khauTruKho;
																		}


																		// @todo: Can cong them kho cho cac san pham dau ra trong obj san pham dau ra

																		$soSanhCungCap = $soLuongMua + $khauTruKho;
																		if ($soSanhCungCap == $tempQty) {
																			$purchasePlan[$purIndex]['Ngay'] = $beginDate;
																			$purchasePlan[$purIndex]['MaSP'] = $member['MaThanhPhan'];
																			$purchasePlan[$purIndex]['TenSP'] = $member['TenThanhPhan'];
																			$purchasePlan[$purIndex]['ThuocTinh'] = $member['ThuocTinh'];
																			$purchasePlan[$purIndex]['DonViTinh'] = $member['DonViTinh'];
																			$purchasePlan[$purIndex]['SoLuong'] = $soLuongMua;
																			$purchasePlan[$purIndex]['KhauTruKho'] = $khauTruKho;
																			$purIndex++;
																		} else {
																			//err: Khong cung cap du nguyen vat lieu
																			// Loi nay da thong bao o tren
																		}

																		/*
																		 * bỏ
																		 if(isset($tonKhoTheoNgay[$beginDate][$planItem->Ref_MaSP][$planItem->Ref_ThuocTinh]))
																		 {
																		 $tonKhoTheoNgay[$beginDate][$planItem->Ref_MaSP][$planItem->Ref_ThuocTinh] -= $tempQty;
																		 }
																		 else
																		 {
																		 $tonKhoTheoNgay[$beginDate][$planItem->Ref_MaSP][$planItem->Ref_ThuocTinh] = -$tempQty;
																		 }
																		 */
																	}

																	// Tru di san pham chinh voi cd 1 cua bom thao ro chi tru mot lan
																	$breakForFirstLevel = 1;
																	if ($BOMConfigure['Config'][$planItem->Ref_BOM][$cdk]['Level'] == 1) {
																		if ($breakForFirstLevel > 1) {
																			break;
																		}

																		// Neu ton kho theo ngay cua thanh phan lon hon or = so luong can thi ko co mua
																		$tonKhoTheoNgayTmp = isset($tonKhoTheoNgay[$beginDate][$planItem->Ref_MaSP][$planItem->Ref_ThuocTinh]) ? $tonKhoTheoNgay[$beginDate][$planItem->Ref_MaSP][$planItem->Ref_ThuocTinh] : 0;
																		if ($tonKhoTheoNgayTmp >= $daDatSXTheoTungDV) {
																			$soLuongMua = 0;
																			$khauTruKho = $daDatSXTheoTungDV;
																		} else { // Khong du kho phai di mua phai can cu co cho mua hay ko
																			$soLuongMua = $BOMConfigure['Config'][$planItem->Ref_BOM][$cdk]['ChinhMuaHang'] ? ($daDatSXTheoTungDV - $tonKhoTheoNgayTmp) : 0;
																			$khauTruKho = $tonKhoTheoNgayTmp;
																		}

																		// Giam so luong trong kho cua sp
																		if (isset($tonKhoTheoNgay[$beginDate][$planItem->Ref_MaSP][$planItem->Ref_ThuocTinh]) && $khauTruKho > 0) {
																			$tonKhoTheoNgay[$beginDate][$planItem->Ref_MaSP][$planItem->Ref_ThuocTinh] -= $khauTruKho;
																		}


																		// @todo: Can cong them kho cho cac san pham dau ra trong obj san pham dau ra

																		$soSanhCungCap = $soLuongMua + $khauTruKho;
																		if ($soSanhCungCap == $daDatSXTheoTungDV) {
																			$purchasePlan[$purIndex]['Ngay'] = $beginDate;
																			$purchasePlan[$purIndex]['MaSP'] = $planItem->MaSP;
																			$purchasePlan[$purIndex]['TenSP'] = $planItem->SanPham;
																			$purchasePlan[$purIndex]['DonViTinh'] = $planItem->DonViKH;
																			$purchasePlan[$purIndex]['ThuocTinh'] = $planItem->ThuocTinh;
																			$purchasePlan[$purIndex]['SoLuong'] = $soLuongMua;
																			$purchasePlan[$purIndex]['KhauTruKho'] = $khauTruKho;
																			$purIndex++;
																		} else {
																			//err: Khong cung cap du nguyen vat lieu
																			// Loi nay da thong bao o tren
																		}
																		/*
																		 * bỏ

																		 if(isset($tonKhoTheoNgay[$beginDate][$planItem->Ref_MaSP][$planItem->Ref_ThuocTinh]))
																		 {
																		 $tonKhoTheoNgay[$beginDate][$planItem->Ref_MaSP][$planItem->Ref_ThuocTinh] -= $daDatSXTheoTungDV;
																		 }
																		 else
																		 {
																		 $tonKhoTheoNgay[$beginDate][$planItem->Ref_MaSP][$planItem->Ref_ThuocTinh] = -$daDatSXTheoTungDV;
																		 }
																		 */
																		$breakForFirstLevel++;
																	}
																} else { // Endif: Gan mang cho thao ro
																	foreach ($BOMConfigure['Config'][$planItem->Ref_BOM][$cdk]['SanPhamDauRa'] as $oItem) {
																		$tempQty = ($daDatSXTheoTungDV * $oItem['Qty']) / $BOMConfigure['Config'][$planItem->Ref_BOM][$cdk]['SoLuongBOM'];

																		$productionPlan[$ppIndex]['DayChuyen'] = $strDayChuyen[$dc]['Ma'];
																		$productionPlan[$ppIndex]['Ca'] = $strCa[$refCa]['Ma'];
																		$productionPlan[$ppIndex]['CongDoan'] = $strCongDoan[$cdk]['Ma'];
																		$productionPlan[$ppIndex]['DonViThucHien'] = $strDonViThucHien[$dvth]['Ma'];
																		$productionPlan[$ppIndex]['Ngay'] = $beginDate;
																		$productionPlan[$ppIndex]['MaSP'] = $oItem['ItemCode'];
																		$productionPlan[$ppIndex]['TenSP'] = $oItem['ItemName'];
																		$productionPlan[$ppIndex]['DonViTinh'] = $oItem['UOM'];
																		$productionPlan[$ppIndex]['ThuocTinh'] = $oItem['Attribute'];
																		$productionPlan[$ppIndex]['ThietKe'] = $BOMConfigure['Config'][$planItem->Ref_BOM][$cdk]['BOM'];
																		$productionPlan[$ppIndex]['SoLuong'] = $tempQty; //$oItem->Qty
																		$productionPlan[$ppIndex]['ThoiGianBatDau'] = $startOfShift;
																		$productionPlan[$ppIndex]['ThoiGianKetThuc'] = $strCa[$refCa]['End'];
																		$ppIndex++;

																		// cong ton kho cho san pham dau ra
																		if (isset($tonKhoTheoNgay[$beginDate][$planItem->Ref_MaSP][$planItem->Ref_ThuocTinh])) {
																			$tonKhoTheoNgay[$beginDate][$planItem->Ref_MaSP][$planItem->Ref_ThuocTinh] += $tempQty;
																		} else {
																			$tonKhoTheoNgay[$beginDate][$planItem->Ref_MaSP][$planItem->Ref_ThuocTinh] = $tempQty;
																		}
																	}
																	/// Tru di thanh phan su dung, doi voi cd dau tien thi thanh phan bao gom ca san pham chinh (chi tru mot lan)
																	foreach ($BOMConfigure['Config'][$planItem->Ref_BOM][$cdk]['ThanhPhan'] as $member) {
																		$tempQty = ($daDatSXTheoTungDV * $member['Qty']) / $BOMConfigure['Config'][$planItem->Ref_BOM][$cdk]['SoLuongBOM'];

																		// Neu ton kho theo ngay cua thanh phan lon hon or = so luong can thi ko co mua
																		$tonKhoTheoNgayTmp = isset($tonKhoTheoNgay[$beginDate][$member['RefMaThanhPhan']][$member['RefThuocTinhThanhPhan']]) ? $tonKhoTheoNgay[$beginDate][$member['RefMaThanhPhan']][$member['RefThuocTinhThanhPhan']] : 0;
																		if ($tonKhoTheoNgayTmp >= $tempQty) {
																			$soLuongMua = 0;
																			$khauTruKho = $tempQty;
																		} else { // Khong du kho phai di mua phai can cu co cho mua hay ko
																			$soLuongMua = $member['ThanhPhanMuaHang'] ? ($tempQty - $tonKhoTheoNgayTmp) : 0;
																			$khauTruKho = $tonKhoTheoNgayTmp;
																		}

																		// Giam so luong trong kho cua sp
																		if (isset($tonKhoTheoNgay[$beginDate][$member['RefMaThanhPhan']][$member['RefThuocTinhThanhPhan']]) && $khauTruKho > 0) {
																			$tonKhoTheoNgay[$beginDate][$member['RefMaThanhPhan']][$member['RefThuocTinhThanhPhan']] -= $khauTruKho;
																		}


																		// @todo: Can cong them kho cho cac san pham dau ra trong obj san pham dau ra

																		$soSanhCungCap = $soLuongMua + $khauTruKho;
																		if ($soSanhCungCap == $tempQty) {
																			$purchasePlan[$purIndex]['Ngay'] = $beginDate;
																			$purchasePlan[$purIndex]['MaSP'] = $member['MaThanhPhan'];
																			$purchasePlan[$purIndex]['TenSP'] = $member['TenThanhPhan'];
																			$purchasePlan[$purIndex]['ThuocTinh'] = $member['ThuocTinh'];
																			$purchasePlan[$purIndex]['DonViTinh'] = $member['DonViTinh'];
																			$purchasePlan[$purIndex]['SoLuong'] = $soLuongMua;
																			$purchasePlan[$purIndex]['KhauTruKho'] = $khauTruKho;
																			$purIndex++;
																		} else {
																			//err: Khong cung cap du nguyen vat lieu
																			// Loi nay da thong bao o tren
																		}

																		/*
																		 * bỏ
																		 if(isset($tonKhoTheoNgay[$beginDate][$planItem->Ref_MaSP][$planItem->Ref_ThuocTinh]))
																		 {
																		 $tonKhoTheoNgay[$beginDate][$planItem->Ref_MaSP][$planItem->Ref_ThuocTinh] -= $tempQty;
																		 }
																		 else
																		 {
																		 $tonKhoTheoNgay[$beginDate][$planItem->Ref_MaSP][$planItem->Ref_ThuocTinh] = -$tempQty;
																		 }
																		 */
																	}

																	// Neu la level cuoi cua lap dat lay them san pham dong chinh
																	// Neu la cong doan cuoi cung thi moi tru so luong san pham chinh can sx
																	// Cong ton kho theo dong sp chinh
																	// @todo: Viet trigger cho bom de san pham dong chinh voi lap dat se khong xuat hien o san pham dau ra
																	if ($BOMConfigure['LastLevel'][$planItem->Ref_BOM] == $BOMConfigure['Config'][$planItem->Ref_BOM][$cdk]['Level']) {
																		$productionPlan[$ppIndex]['DayChuyen'] = $strDayChuyen[$dc]['Ma'];
																		$productionPlan[$ppIndex]['Ca'] = $strCa[$refCa]['Ma'];
																		$productionPlan[$ppIndex]['CongDoan'] = $strCongDoan[$cdk]['Ma'];
																		$productionPlan[$ppIndex]['DonViThucHien'] = $strDonViThucHien[$dvth]['Ma'];
																		$productionPlan[$ppIndex]['Ngay'] = $beginDate;
																		$productionPlan[$ppIndex]['MaSP'] = $planItem->MaSP;
																		$productionPlan[$ppIndex]['TenSP'] = $planItem->SanPham;
																		$productionPlan[$ppIndex]['DonViTinh'] = $planItem->DonViKH;
																		$productionPlan[$ppIndex]['ThuocTinh'] = $planItem->ThuocTinh;
																		$productionPlan[$ppIndex]['ThietKe'] = $planItem->BOM;
																		$productionPlan[$ppIndex]['SoLuong'] = $daDatSXTheoTungDV;
																		$productionPlan[$ppIndex]['ThoiGianBatDau'] = $startOfShift;
																		$productionPlan[$ppIndex]['ThoiGianKetThuc'] = $strCa[$refCa]['End'];
																		$ppIndex++;

																		// Cong ton kho dong chinh
																		if (isset($tonKhoTheoNgay[$beginDate][$planItem->Ref_MaSP][$planItem->Ref_ThuocTinh])) {
																			$tonKhoTheoNgay[$beginDate][$planItem->Ref_MaSP][$planItem->Ref_ThuocTinh] += $daDatSXTheoTungDV;
																		} else {
																			$tonKhoTheoNgay[$beginDate][$planItem->Ref_MaSP][$planItem->Ref_ThuocTinh] = $daDatSXTheoTungDV;
																		}
																	}
																} // Endif: Gan mang cho lap dat
																// Tru so luong da san xuat duoc cho san pham chi tinh o cong doan cuoi cung
																if ($BOMConfigure['LastLevel'][$planItem->Ref_BOM] == $BOMConfigure['Config'][$planItem->Ref_BOM][$cdk]['Level']) {
																	$planItem->SoLuongSX -= $daDatSXTheoTungDV; // Phai la cong doan cuoi thi moi tru di so luong
																}

																//$tonKhoTheoNgay[$nextDate][$planItem->Ref_MaSP][$planItem->Ref_ThuocTinh] = $tonKhoTruocNgayHT + $tongSanXuatTheoNgay ;
																// Neu cong doan da thuc hien du so luong san pham dau ra thi ngung cong doan lai

																/*
																 foreach ($BOMConfigure['Config'][$planItem->Ref_BOM][$cdk]['ThanhPhan'] as $tpsp)
																 {
																 $soLuongCanCungCap = ($daDatSXTheoTungDV * $tpsp['SoLuong'])/$planItem->SoLuongBOM;
																 // Neu ton kho theo ngay cua thanh phan lon hon or = so luong can thi ko co mua
																 $tonKhoTheoNgayTmp = isset($tonKhoTheoNgay[$beginDate][$tpsp['RefMaThanhPhan']][$tpsp['RefThuocTinhThanhPhan']])?$tonKhoTheoNgay[$beginDate][$tpsp['RefMaThanhPhan']][$tpsp['RefThuocTinhThanhPhan']]:0;
																 if($tonKhoTheoNgayTmp >= $soLuongCanCungCap)
																 {
																 $soLuongMua = 0;
																 $khauTruKho = $soLuongCanCungCap;
																 }
																 else // Khong du kho phai di mua phai can cu co cho mua hay ko
																 {
																 $soLuongMua = $tpsp['ThanhPhanMuaHang']?($soLuongCanCungCap - $tonKhoTheoNgayTmp):0;
																 $khauTruKho = $tonKhoTheoNgayTmp;
																 }

																 // Giam so luong trong kho cua sp
																 if(isset($tonKhoTheoNgay[$nextDate][$tpsp['RefMaThanhPhan']][$tpsp['RefThuocTinhThanhPhan']]))
																 {
																 $tonKhoTheoNgay[$nextDate][$tpsp['RefMaThanhPhan']][$tpsp['RefThuocTinhThanhPhan']] -= $khauTruKho;
																 }
																 else
																 {
																 $tonKhoTheoNgay[$nextDate][$tpsp['RefMaThanhPhan']][$tpsp['RefThuocTinhThanhPhan']] = $khauTruKho;
																 }

																 // @todo: Can cong them kho cho cac san pham dau ra trong obj san pham dau ra

																 $soSanhCungCap = $soLuongMua + $khauTruKho;
																 if($soSanhCungCap == $soLuongCanCungCap)
																 {
																 $purchasePlan[$purIndex]['Ngay'] = $beginDate;
																 $purchasePlan[$purIndex]['MaSP'] = $tpsp['MaThanhPhan'];
																 $purchasePlan[$purIndex]['TenSP'] = $tpsp['TenThanhPhan'];
																 $purchasePlan[$purIndex]['ThuocTinh'] = $tpsp['ThuocTinh'];
																 $purchasePlan[$purIndex]['DonViTinh'] = $tpsp['DonViTinh'];
																 $purchasePlan[$purIndex]['SoLuong'] = $soLuongMua;
																 $purchasePlan[$purIndex]['KhauTruKho'] = $khauTruKho;
																 $purIndex++;
																 }
																 else
																 {
																 //err: Khong cung cap du nguyen vat lieu
																 // Loi nay da thong bao o tren
																 }

																 }// End loop thanh phan
																 $ppIndex++;
																 */
															} // if cong doan co it nhat trong mot day chuyen
															else {
																// @todo: Bao loi cong doan ko co trong bat ky day chuyen nao
																// @todo: CHuyen bao loi nay sang file phtml
																$msg .= "Công đoạn {$strCongDoan[$cdk]['Ma']} của mặt hàng {$planItem->MaSP} chưa được gán cho dây chuyền nào.<br/>";
															} // if cong doan ko co trong bat ky day chuyen nao
														} // if Don vi co the san xuat
													}
												}
											}


											// Tinh thoi gian venh ra so voi cong doan tiep theo
											// cd co bao nhieu don vi thuc hien
											// so gio 1 = sluong 1(an 1) * so gio cd/ sl bom * hieu suat 1
											// sluong 1 + ... + sluong n = so luong chuyen
											// so gio 1 = so gio 2 = ... = so gio n => So gio chung
											// tim so gio chung nay
											// ( slbom * hs1 * so gio chung )/ so gio bom + ... + ( slbom * hs n * so gio chung )/ so gio bom = So luong chuyen
											// Lay thoi gian lon nhat neu co nhieu san pham dau ra cho cong doan
											$tongDeTinhSoGio = 0;
											$maxThoiGianChuyen = ''; // Don vi tinh la gio

											foreach ($BOMConfigure['Config'][$planItem->Ref_BOM][$cdk]['SanPhamDauRa'] as $outItem) {

												// $getInfoFromManuLine['performance'];
												// Neu ma co so luong chuyen thi thuc hien tinh max thoi gian chuyen de tinh thoi gian cd tiep
												if ($outItem['BatDauTruoc']) {
													foreach ($getInfoFromManuLine['performance'][$dc][$cdk] as $dvth => $hs) {
														$hs = $hs / 100;
														$tongDeTinhSoGio += $BOMConfigure['Config'][$planItem->Ref_BOM][$cdk]['SoGio'] ? $outItem['Qty'] * $hs / $BOMConfigure['Config'][$planItem->Ref_BOM][$cdk]['SoGio'] : 0;
													}
													$temp = $outItem['BatDauTruoc'] / $tongDeTinhSoGio;

													// so sanh moi max de lay thoi gian lon nhat
													if ($maxThoiGianChuyen == '' || $maxThoiGianChuyen < $temp) {
														$maxThoiGianChuyen = $temp;
													}
												}
											}
											$maxThoiGianChuyen = $maxThoiGianChuyen ? $maxThoiGianChuyen : 0; // convert '' to 0
											//echo '<pre>'; print_r($maxThoiGianChuyen);
											// Neu ton tai cong doan tiep theo thi cong doan tiep theo se bat dau sau thoi gian venh len
											if (isset($BOMConfigure['PrevNext'][$planItem->Ref_BOM][($BOMConfigure['Config'][$planItem->Ref_BOM][$cdk]['Level'] + 1)])) {
												$nextOperation = $BOMConfigure['PrevNext'][$planItem->Ref_BOM][($BOMConfigure['Config'][$planItem->Ref_BOM][$cdk]['Level'] + 1)];
												$nextOperationArr[$beginDate][$planItem->KHSPIOID][$nextOperation] = $maxThoiGianChuyen;
											}

											//} // endif: neu co so luong chuyen else: neu ko co so luong chuyen san xuat lien tuc
											//else
											//{
											//
											//}// Endif: Kiem tra so luong chuyen
										} // Endforeach: Cong doan
									}
								} // Endforeach: Ca
							}// Endforeach: Day chuyen
							// End 	#Block5: Gan mang ke hoach

							/*
							 $khaNangSXConLaiCuaDV = array(); #c1
							 // Cong ton kho cho sx ra
							 foreach ($wHoursByDate as $dc=>$ca)// Day chuyen
							 {
							 foreach ($ca as $refCa=>$cd)  // Ca
							 {
							 // Sap xep lai mang cong doan theo cong doan cua bom
							 // Cac cong doan theo bom va day chuyen
							 // Giao cong doan cua bom va day chuyen
							 if(!isset($operationKeySorted[$dc][$planItem->Ref_BOM]))
							 {
							 $operationKeySorted[$dc][$planItem->Ref_BOM] = $this->sortArrayByKey($BOMConfigure['Key'][$planItem->Ref_BOM], $cd);
							 }

							 if($refCa != 0)
							 {
							 //foreach ($cd as $cdk=>$soGio) // Cong doan
							 foreach ($operationKeySorted[$dc][$planItem->Ref_BOM] as $cdk=>$soGio) // Cong doan
							 {
							 $tempSanXuatTheoNgay = isset($sanXuatTheoNgay[$dc][$refCa])?$sanXuatTheoNgay[$dc][$refCa]:0;
							 //echo $tempSanXuatTheoNgay.'<br/>';

							 #c2
							 if($tempSanXuatTheoNgay > 0) // Theo day chuyen va ca
							 {

							 $getInfoFromManuLine['performance'][$dc][$cdk] = isset($getInfoFromManuLine['performance'][$dc][$cdk])?$getInfoFromManuLine['performance'][$dc][$cdk]:array();
							 foreach ($getInfoFromManuLine['performance'][$dc][$cdk] as $dvth=>$hs)// Don vi thuc hien
							 {
							 $daDatSXTheoTungDV = 0;
							 // Neu chua ton tai kha nang sx con lai cu don vi, set kha nhang bang kha nang co the sx cua don vi
							 if(!isset($khaNangSXConLaiCuaDV[$beginDate][$dc][$refCa][$cdk][$dvth]))
							 {
							 $khaNangSXConLaiCuaDV[$beginDate][$dc][$refCa][$cdk][$dvth] = @(int)$coTheSXCuaDonVi[$dc][$refCa][$cdk][$dvth];
							 }

							 // So luong can san xuat cua dv bang kha nang con lai tru cho so luong yeu cau theo ngay dc ca
							 if($khaNangSXConLaiCuaDV[$beginDate][$dc][$refCa][$cdk][$dvth] >= $tempSanXuatTheoNgay)
							 {
							 $daDatSXTheoTungDV = $tempSanXuatTheoNgay;
							 $khaNangSXConLaiCuaDV[$beginDate][$dc][$refCa][$cdk][$dvth] -= $tempSanXuatTheoNgay;
							 $tempSanXuatTheoNgay = 0;
							 }
							 else
							 {
							 $daDatSXTheoTungDV = $khaNangSXConLaiCuaDV[$beginDate][$dc][$refCa][$cdk][$dvth];
							 $tempSanXuatTheoNgay -= $khaNangSXConLaiCuaDV[$beginDate][$dc][$refCa][$cdk][$dvth];
							 $khaNangSXConLaiCuaDV[$beginDate][$dc][$refCa][$cdk][$dvth] = 0;
							 }


							 if($daDatSXTheoTungDV > 0)
							 {
							 if($BOMConfigure['Config'][$planItem->Ref_BOM][$cdk]['TonTai'])
							 { // Co cong doan trong it nhat mot day chuyen
							 $productionPlan[$ppIndex]['DayChuyen'] = $strDayChuyen[$dc]['Ma'];
							 $productionPlan[$ppIndex]['Ca'] = $strCa[$refCa]['Ma'];
							 $productionPlan[$ppIndex]['CongDoan'] = $strCongDoan[$cdk]['Ma'];
							 $productionPlan[$ppIndex]['DonViThucHien'] = $strDonViThucHien[$dvth]['Ma'];
							 $productionPlan[$ppIndex]['Ngay'] = $beginDate;
							 $productionPlan[$ppIndex]['MaSP'] = $planItem->MaSP;
							 $productionPlan[$ppIndex]['TenSP'] = $planItem->SanPham;
							 $productionPlan[$ppIndex]['DonViTinh'] = $planItem->DonViKH;
							 $productionPlan[$ppIndex]['ThuocTinh'] = $planItem->ThuocTinh;
							 $productionPlan[$ppIndex]['ThietKe'] = $planItem->BOM;
							 $productionPlan[$ppIndex]['SoLuong'] = $daDatSXTheoTungDV;
							 $productionPlan[$ppIndex]['ThoiGian'] = ceil( (($daDatSXTheoTungDV * $BOMConfigure['Config'][$planItem->Ref_BOM][$cdk]['SoGio'])/$planItem->SoLuongBOM) * 60); // Chua tinh
							 $planItem->SoLuongSX  -= $daDatSXTheoTungDV;



							 foreach ($BOMConfigure['Config'][$planItem->Ref_BOM][$cdk]['ThanhPhan'] as $tpsp)
							 {
							 $soLuongCanCungCap = ($daDatSXTheoTungDV * $tpsp['SoLuong'])/$planItem->SoLuongBOM;
							 // Neu ton kho theo ngay cua thanh phan lon hon or = so luong can thi ko co mua
							 $tonKhoTheoNgayTmp = isset($tonKhoTheoNgay[$beginDate][$tpsp['RefMaThanhPhan']][$tpsp['RefThuocTinhThanhPhan']])?$tonKhoTheoNgay[$beginDate][$tpsp['RefMaThanhPhan']][$tpsp['RefThuocTinhThanhPhan']]:0;
							 if($tonKhoTheoNgayTmp >= $soLuongCanCungCap)
							 {
							 $soLuongMua = 0;
							 $khauTruKho = $soLuongCanCungCap;
							 }
							 else // Khong du kho phai di mua phai can cu co cho mua hay ko
							 {
							 $soLuongMua = $tpsp['ThanhPhanMuaHang']?($soLuongCanCungCap - $tonKhoTheoNgayTmp):0;
							 $khauTruKho = $tonKhoTheoNgayTmp;
							 }

							 // Giam so luong trong kho cua sp
							 if(isset($tonKhoTheoNgay[$nextDate][$tpsp['RefMaThanhPhan']][$tpsp['RefThuocTinhThanhPhan']]))
							 {
							 $tonKhoTheoNgay[$nextDate][$tpsp['RefMaThanhPhan']][$tpsp['RefThuocTinhThanhPhan']] -= $khauTruKho;
							 }
							 else
							 {
							 $tonKhoTheoNgay[$nextDate][$tpsp['RefMaThanhPhan']][$tpsp['RefThuocTinhThanhPhan']] = $khauTruKho;
							 }

							 // @todo: Can cong them kho cho cac san pham dau ra trong obj san pham dau ra

							 $soSanhCungCap = $soLuongMua + $khauTruKho;
							 if($soSanhCungCap == $soLuongCanCungCap)
							 {
							 $purchasePlan[$purIndex]['Ngay'] = $beginDate;
							 $purchasePlan[$purIndex]['MaSP'] = $tpsp['MaThanhPhan'];
							 $purchasePlan[$purIndex]['TenSP'] = $tpsp['TenThanhPhan'];
							 $purchasePlan[$purIndex]['ThuocTinh'] = $tpsp['ThuocTinh'];
							 $purchasePlan[$purIndex]['DonViTinh'] = $tpsp['DonViTinh'];
							 $purchasePlan[$purIndex]['SoLuong'] = $soLuongMua;
							 $purchasePlan[$purIndex]['KhauTruKho'] = $khauTruKho;
							 $purIndex++;
							 }

							 }// End loop thanh phan
							 $ppIndex++;

							 // $congDoanTheoThietKe[$cdk['CongDoan']]['TonTai'] = 0;
							 // $congDoanTheoThietKe[$cdk]['ThanhPhan'] = array();
							 } // if cong doan co it nhat trong mot day chuyen
							 else
							 {
							 // @todo: Bao loi cong doan ko co trong bat ky day chuyen nao
							 // @todo: CHuyen bao loi nay sang file phtml
							 $msg .= "Công đoạn {$strCongDoan[$cdk]['Ma']} của mặt hàng {$planItem->MaSP} chưa được gán cho dây chuyền nào.<br/>";
							 } // if cong doan ko co trong bat ky day chuyen nao
							 } // if Don vi co the san xuat

							 } // loop dvth
							 } // if neu co sx
							 } // end loop cong doan
							 } // If co ca
							 }// End loop ca theo lich su dung cho ngay
							 } // End loop lich su dung cho ngya


							 // Cong ton kho cho ngay hom sau
							 $tonKhoTruocNgayHT = isset($tonKhoTheoNgay[$beginDate][$planItem->Ref_MaSP][$planItem->Ref_ThuocTinh])?$tonKhoTheoNgay[$beginDate][$planItem->Ref_MaSP][$planItem->Ref_ThuocTinh]:0;
							 $tonKhoTheoNgay[$nextDate][$planItem->Ref_MaSP][$planItem->Ref_ThuocTinh] = $tonKhoTruocNgayHT + $tongSanXuatTheoNgay ;
							 */
						} // endif, neu con thua thoi gian cho san xuat thi moi thuc hien tinh toan
						// #echo
						//						echo '<pre>'; echo '<pre>'; print_r($tonKhoTheoNgay[$beginDate]); echo '<pre>'; echo 'Het ngay:'.$beginDate;
						//						echo '<Pre>'; echo '-----------------------';
						$beginDate = $nextDate;
					} // endwhile, het so luong sp
				}// endif, NEu co so luong sx thi moi thuc hien

				if ($planItem->SoLuongMH || $planItem->KhauTruKho) {
					$purchasePlan[$purIndex]['Ngay'] = $beginDate; // @todo: lay ngay xuat hang tru cho so ngay cho mua hang
					$purchasePlan[$purIndex]['MaSP'] = $planItem->MaSP;
					$purchasePlan[$purIndex]['TenSP'] = $planItem->SanPham;
					$purchasePlan[$purIndex]['ThuocTinh'] = $planItem->ThuocTinh;
					$purchasePlan[$purIndex]['DonViTinh'] = $planItem->DonViTinh;
					$purchasePlan[$purIndex]['SoLuong'] = $planItem->SoLuongMH;
					$purchasePlan[$purIndex]['KhauTruKho'] = $planItem->KhauTruKho;
				}
				$purIndex++;
				// @todo: Cap nhat lai ngay
				$beginDate = $params['startDate']; // Reset ngay ve ngay ban dau
				// Do ke hoach san pham co the query lap lai nen can loai lap lai
				$oldPlanItem[] = $planItem->KHSPIOID;
			}
		} // End #Block0: Loop cac dong ke hoach san pham
		//                echo '<pre>';
		//                print_r($productionPlan);
		//                echo '<pre>';
		//                print_r($purchasePlan);
		//                die;
		$this->html->production = $productionPlan;
		$this->html->purchase = $purchasePlan;
		$this->html->params = $params;
		$this->html->msg = $msg;
	}

	public function detailSaveAction() {
		$params = $this->params->requests->getParams();
		if ($this->params->requests->isAjax()) {
			$service = $this->services->Extra->Mrp->Detail->Save($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	// Action: Lay chi tiet yeu cau cung ung
	public function requirementIndexAction() {
		$params = $this->params->requests->getParams();
		$this->html->ifid = $params['ifid'];
	}

	// Action: Hien thi yeu cau cung ung
	public function requirementShowAction() {
		// Can hai mang de phan trang va hien thi
		// Mang 1: luu dong don hang => $order
		// Phan trang dua theo mang $order, se la hien thi bao nhieu order tren mot trang
		// Mang 2: luu ke hoach giao hang cua dong don hang => $deliveryPlan
		$order = array();
		$i = 0;
		$deliveryPlan = array();
		$orderKey = array(); // Dung de lay delivery plan
		$params = $this->params->requests->getParams(); // request tai len
		$countRecord = $this->_model->getRequirementInfo($params['module'], true); // Tong so ban ghi
		$totalPage = ceil($countRecord / $params['display']); // Tong so trang
		$page = ($totalPage < $params['page']) ? 1 : $params['page'];
		$orderSql = $this->_model->getRequirementInfo($params['module'], false, array('display' => $params['display'], 'page' => $page));

		// Gan mang $order
		foreach ($orderSql as $item) {
			$order[$item->ID]['DocumentNo'] = $item->DocumentNo;
			$order[$item->ID]['BeginDate'] = $item->BeginDate;
			$orderKey[] = $item->ID;
		}

		// Gan mang deliveryPlan
		foreach ($this->_model->getRequirementDetail($params['module'], $orderKey) as $item) {
			$deliveryPlan[$item->ID][$i]['IOID'] = $item->IOID;
			$deliveryPlan[$item->ID][$i]['FromIOID'] = $item->FromIOID;
			$deliveryPlan[$item->ID][$i]['ToIOID'] = $item->ToIOID;
			$deliveryPlan[$item->ID][$i]['ItemCode'] = $item->ItemCode;
			$deliveryPlan[$item->ID][$i]['ItemName'] = $item->ItemName;
			$deliveryPlan[$item->ID][$i]['Attribute'] = $item->Attribute;
			$deliveryPlan[$item->ID][$i]['ItemUOM'] = $item->ItemUOM;
			$deliveryPlan[$item->ID][$i]['ItemQty'] = $item->ItemQty;
			$deliveryPlan[$item->ID][$i]['EndDate'] = $item->EndDate;
			$i++;
		}

		$this->html->module = $params['module'];
		$this->html->order = $order;
		$this->html->deliveryPlan = $deliveryPlan;
	}

	// Action: Hien thi so trang cua yeu cau cung ung dua tren so dong chinh cua don hang
	public function requirementPageAction() {
		$params = $this->params->requests->getParams();
		$countRecord = $this->_model->getRequirementInfo($params['module'], true); // Tong so ban ghi
		$totalPage = ceil($countRecord / $params['display']); // Tong so trang
		$page = $params['page'];

		$data = array('total' => $totalPage, 'page' => $params['page']);
		echo Qss_Json::encode(array('error' => 0, 'data' => $data));

		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	public function requirementOldAction() {
		$params = $this->params->requests->getParams();

		switch ($params['module']) {
			case 'M505':
				$idCol = 'IFID_M764';
				$idVal = $params['ifid'];
				break;
		}
		$this->html->old = $this->extra->getTable(array(' MaSP as ItemCode', ' TenSp as ItemName '
		, 'DonViTinh as ItemUOM', 'SoLuong as ItemQty'
		, 'ThamChieu as Ref', 'ThuocTinh as Attribute'
		, 'NgayBatDau as BeginDate', 'NgayKetThuc as EndDate'
		, 'IOID as IOID')
		, 'OChiTietYeuCau'
		, array($idCol => $idVal), array(), 'NO_LIMIT');
	}

	public function requirementDeleteAction() {
		$params = $this->params->requests->getParams();
		if ($this->params->requests->isAjax()) {
			$service = $this->services->Extra->Mrp->Requirement->Delete($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	public function requirementSaveAction() {
		$params = $this->params->requests->getParams();
		if ($this->params->requests->isAjax()) {
			$service = $this->services->Extra->Mrp->Requirement->Save($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

}
