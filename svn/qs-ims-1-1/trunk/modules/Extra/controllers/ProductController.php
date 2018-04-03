<?php

/**
 *
 * @author ThinhTuan
 *
 */
class Extra_ProductController extends Qss_Lib_Controller
{

	public $_form;
	public $_model;
	public $_common;
	public $_params;

	/**
	 *
	 * @return unknown_type
	 */
	public function init()
	{
		parent::init();

		// Param
		$ifid   = $this->params->requests->getParam('ifid', 0);
		$fid    = $this->params->requests->getParam('fid', 0);
		$deptid = $this->params->requests->getParam('deptid', $this->_user->user_dept_id);

		// Init form
		$form   = new Qss_Model_Form();
		if ($ifid)
		{
			$form->initData($ifid, $deptid);
		}
		else
		{
			$form->v_fInit($fid, $deptid, $this->_user->user_id);
		}
		$this->b_fCheckRightsOnForm($form, 2);

		// Set property
		$this->_model  = new Qss_Model_Extra_Products();
		$this->_common = new Qss_Model_Extra_Extra();
		$this->_params = $this->params->requests->getParams();
		$this->_form   = $form;

		// Script (Ko hoat dong :<)
		$this->headScript($this->params->requests->getBasePath() . '/css/button.css');
		$this->headScript($this->params->requests->getBasePath() . '/js/form-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');

		// TO HTML
		$this->html->form = $form;

	}

	// *****************************************************************
	// === FUNCTION: GAN TRANG THAI LUU TRU
	// *****************************************************************
	public function attributeInAddAction()
	{
		// GET PARAMS
		$ifid   = $this->params->requests->getParam('ifid', 0);
		$objid  = $this->params->requests->getParam('objid', 0);
		$ioid   = $this->params->requests->getParam('ioid', 0);

		// CONFIG
		$module         = $this->_form->FormCode;
		$getDataObject  = @$this->_form->a_Objects[$objid]->ObjectCode;

		// $ioidEnd: Lay ioid theo ifid trong truong hop ko co ioid.
		$ioidEnd        = ($ioid) ? $ioid : $this->_model->getIOIDfromIFID($module, $ifid);

		// $itemConfig: Thong tin san pham; cau hinh lot, serial; thong tin kho tren dong sp
		$itemConfig     = $this->_model->getSerialLot($module, $ioid, $ifid, $getDataObject);

		// $attributes: Thong tin thuoc tinh cua san pham
		$attributes     = $this->_model->getAttributes($module, $ioid, $ifid, $getDataObject);

		// $oldStockStatus: Trang thai luu tru hien thoi cua dong san pham
		$oldStockStatus = $this->_model->getOldStockStatus($module, $ioidEnd, $ifid, $getDataObject);

		// TRANSFER
		$this->html->ioid            = $ioid;
		$this->html->ifid            = $ifid;
		$this->html->objid           = $objid;
		$this->html->module          = $module;
		$this->html->obj             = $getDataObject;
		$this->html->itemConfig      = $itemConfig;
		$this->html->attributes      = $attributes;
		$this->html->oldStockStatus  = $this->sortOldStockStatus($oldStockStatus);
		$this->html->binConfig       = $this->_model->getBinByWarehouse(
		$itemConfig->refWarehouse
		, $itemConfig->refItem
		, $itemConfig->itemUOM);

	}

	/// Generate stock status
	public function attributeInShowAction()
	{
		// @note: Viec lay lai mot so gia tri da lay truoc do ben addAction,
		// de dam bao du lieu luon dung

		// GET PARAMS
		$ifid   = $this->params->requests->getParam('ifid', 0);
		$objid  = $this->params->requests->getParam('objid', 0);
		$ioid   = $this->params->requests->getParam('ioid', 0);
		$lotQty = $this->params->requests->getParam('lotQty', 0);

		// CONFIG
		$module         = $this->_form->FormCode;
		$getDataObject  = @$this->_form->a_Objects[$objid]->ObjectCode;

		// $ioidEnd: Lay ioid theo ifid trong truong hop ko co ioid.
		$ioidEnd        = ($ioid) ? $ioid : $this->_model->getIOIDfromIFID($module, $ifid);

		// $itemConfig: Thong tin san pham; cau hinh lot, serial; thong tin kho tren dong sp
		$itemConfig     = $this->_model->getSerialLot($module, $ioid, $ifid, $getDataObject);

		// $lastLot: So Lot cuoi cung duoc nhap vao kho, can cu tinh so lot tiep theo
		$lastLot        = $this->_model->getLastLot($itemConfig->preLot);

		// $lastSerail: So serial cuoi cung nhap vao kho, can cu tinh so serial tiep theo
		$lastSerail     = $this->_model->getLastSerial($itemConfig->preSerial);

		// $attributes: Thong tin thuoc tinh cua san pham
		$attributes     = $this->_model->getAttributes($module, $ioid, $ifid, $getDataObject);

		// $oldStockStatus: Trang thai luu tru hien thoi cua dong san pham
		$oldStockStatus = $this->_model->getOldStockStatus($module, $ioidEnd, $ifid, $getDataObject);

		// $bin: Cau hinh bin cua kho
		$bin            = $this->_model->getBinByWarehouse(
		$itemConfig->refWarehouse
		, $itemConfig->refItem
		, $itemConfig->itemUOM);

		// $lines: So dong, moi dong co so luong mac dinh la bao nhieu
		$lines          = $this->getLines(
		$itemConfig->lot
		, $itemConfig->serial
		, $itemConfig->itemQty
		, $lotQty
		, count((array)$bin));

		/** html */
		$this->html->itemConfig      = $itemConfig;
		$this->html->lines           = $lines;
		$this->html->lastLot         = $lastLot;
		$this->html->lastSerial      = $lastSerail;
		$this->html->lotQty          = $lotQty;
		$this->html->oldValue        = $this->sortOldStockStatus($oldStockStatus);
		$this->html->binConfig       = $bin;
		$this->html->hasBin          = $zoneAndBin['hasBin'];
		$this->html->zoneBinSort     = $zoneAndBin['arrayWhereOnly'];
		$this->html->attributes      = $attributes;

	}

	//	// @Remove
	//	/// Show bin base on zone
	//	public function attributeInBinAction()
	//	{
	//		$zone = $this->params->requests->getParam('zone');
	//		$zoneAndBin = $this->getBinByWarehouse($this->params->requests->getParam('refWarehouse'), $this->params->requests->getParam('refItem')
	//			, $serialLot->itemUOM);
	//		$this->html->zoneAndBin = $zoneAndBin['defaultArray'][$zone]['bin'];
	//		$this->html->zone = $zone;
	//
	//	}

	/// Save stock status
	public function attributeInSaveAction()
	{
		$params = $this->params->requests->getParams();
		if ($this->params->requests->isAjax())
		{
			$service = $this->services->Extra->Warehouse->Receive->Stockstatus->Save($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);

	}

	/**
	 * @remove
	 * Lay danh sach bin (vi tri) cua kho duoi dang mang
	 * @param int $refWarehouse
	 * @param int $refItem
	 * @param string $itemUOM
	 * @return array
	 */
	private function getBinByWarehouse($refWarehouse, $refItem, $itemUOM = '')
	{
		$bins = $this->_model->getBinByWarehouse($refWarehouse, $refItem, $itemUOM);
		$result         = array();
		$zoneAndBinArray = array();
		$zoneBinSort = array();
		$capacity = array();
		$oldZoneValue = '';
		$hasBin = 0;
		//$hasZone		 = 0;
		//$zone            = 0;
		$bin = 0;
		$line = 0;


		$i = 0;
		foreach ($bins as $v)
		{
			//$zoneBinSort[$i]['zoneCode'] = $v->zoneCode;
			$zoneBinSort[$i]['binCode'] = $v->binCode;
			$zoneBinSort[$i]['binName'] = $v->binName;
			$capacity[$v->binCode] = $v->binCapacity;
			$i++;

			if ($v->binCode)
			{
				$zoneAndBinArray[$v->binCode] = $v->binName;
				$hasBin = 1;
				$bin++;
			}
			$line++;
			$oldZoneValue = $v->binCode;
		}

		$result['hasBin'] = $hasBin;
		$result['defaultArray'] = $zoneAndBinArray;
		$result['bin'] = $bin;
		$result['lineWhereOnly'] = $line;
		$result['arrayWhereOnly'] = $zoneBinSort;
		$result['capacity'] = $capacity;


		return $result;

	}

	/**
	 * getOldValues()
	 * Do $oldValue được trả về nhiều dòng giống nhau khác thuộc tính sản phẩm
	 * Nên cần loại bỏ các giá trị trùng lặp, để in ra dễ dàng hơn
	 */
	private function sortOldStockStatus($oldValue)
	{
		$arrAttrTmp = array();
		$old        = 0;
		$i          = -1;

		if (count((array)$oldValue))
		{
			foreach ($oldValue as $val)
			{
				// Nếu giá trị IOID khác nhau ta sang một dòng mới
				if ($old != $val->IOID)
				{
					$i++;
				}

				// Khởi tạo mảng lưu giá trị cho từng dòng thứ i
				if (!isset($arrAttrTmp[$i]))
				{
					$arrAttrTmp[$i] = array();
				}

				$arrAttrTmp[$i]['SoLuong']     = $val->SoLuong;
				$arrAttrTmp[$i]['SoLo']        = isset($val->SoLo) ? $val->SoLo : '';
				$arrAttrTmp[$i]['SoSerial']    = isset($val->SoSerial) ? $val->SoSerial : '';
				$arrAttrTmp[$i]['MaThuocTinh'] = isset($val->MaThuocTinh) ? $val->MaThuocTinh : '';
				$arrAttrTmp[$i]['Zone']        = isset($val->Zone) ? $val->Zone : '';
				$arrAttrTmp[$i]['Bin']         = isset($val->Bin) ? $val->Bin : '';
				$arrAttrTmp[$i]['IOID']        = $val->IOID;
				$arrAttrTmp[$i]['ReceiveDate'] = $val->NgayNhan;
				$arrAttrTmp[$i]['ProductDate'] = $val->NgaySX;
				$arrAttrTmp[$i]['ExpiryDate']  = $val->NgayHan;
				$old = $val->IOID;
			}
		}
		return $arrAttrTmp;

	}

	/**
	 * Thông tin về số dòng, số lượng mặc định trên dòng, quyền thay đổi số lượng trên dòng
	 * Trả về một mảng gồm ba phần tử :
	 * - lines : Tổng số dòng
	 * - defaultQty : Số lượng mặc định trên dòng
	 * , có thể thay đổi khi sang đoạn code khác
	 * - lineQtyAccess : Quyền thay đổi số lượng trên một dòng
	 * @param unknown_type $lot : Quản lý theo lô 0/1
	 * @param unknown_type $serial : Quản lý theo mã 0/1
	 * @param unknown_type $attributes : Thuộc tính sản phẩm array
	 * @param unknown_type $itemQty : Số lượng sản phẩm number
	 * @param unknown_type $lotQty : Số lượng sản phẩm trên lô number
	 */
	private function getLines($lot, $serial, $itemQty, $lotQty, $countBinLine)
	{
		$array = array();
		$array['lines'] = 0;
		$array['defaultLineQty'] = 0;
		$array['lineQtyAccess'] = '';

		/**
		 * Xử lý dữ kiện
		 */
		if ($serial) // --- Quản lý theo serial
		{
			$array['lines'] = $itemQty;
			$array['defaultLineQty'] = 1;
			$array['lineQtyAccess'] = 'readonly="readonly" class="readonly"';
		} else // --- Không quản lý theo serial
		{
			if ($lot) // -- Quản lý theo lô (Không quản lý theo serial)
			{
				$lotQty = (int) ((is_numeric($lotQty) && $lotQty > 0) ? $lotQty : 1);
				$array['lines'] = ceil($itemQty / $lotQty);
				$array['defaultLineQty'] = ($lotQty < $itemQty) ? $lotQty : $itemQty;
				$array['lineQtyAccess'] = '';
			}
			else // -- Không quản lý theo lô (Không quản lý theo serial) -> Có thuộc tính
			{
				if ($countBinLine)
				{
					$array['lines'] = $countBinLine;
					$array['defaultLineQty'] = 0;
					$array['lineQtyAccess'] = '';
				}
			}
		}
		/* Kết thúc xử lý dữ kiện */
		return $array;

	}

	//======================================================================================
	/**
	 * attributeOutGetAction()
	 * Được sử dụng để lấy lô và serial đã có trong kho
	 * Như chuyển hàng, trả hàng
	 */
	public function attributeOutGetAction()
	{
		$this->_params['ioid'] = isset($this->_params['ioid']) ? $this->_params['ioid'] : 0;
		$module = $this->_form->FormCode;

		$getDataObject = @$this->_form->a_Objects[$this->_params['objid']]->ObjectCode;
		$serialLot = $this->_model->getSerialLot($module
		, $this->_params['ioid'], $this->_params['ifid'], $getDataObject);
		$attributes = $this->_model->getAttributes($module
		, $this->_params['ioid'], $this->_params['ifid'], $getDataObject);
		$ioidEnd = ($this->_params['ioid']) ?
		$this->_params['ioid'] :
		$this->_model->getIOIDfromIFID($module
		, $this->_params['ifid']);  // main object ioid
		// Bảng tr.th.lưu trữ hiện tại
		$exist = $this->_model->getOldStockStatus($module
		, $ioidEnd, $this->_params['ifid'], $getDataObject);
		$zoneAndBin = $this->getBinByWarehouse($serialLot->refWarehouse, $serialLot->refItem
		, $serialLot->itemUOM);


		/* Truyền html */
		if ($serialLot->SelectWarehouse)
		{
			$this->html->warehouses = $this->getWarehouses();
		}
		$this->html->ifid = $this->_params['ifid'];
		$this->html->ioid = $this->_params['ioid'];
		$this->html->objid = $this->_params['objid'];
		$this->html->getDataObject = $getDataObject;
		$this->html->serialLot = $serialLot;
		$this->html->attributes = $attributes;
		$this->html->module = $module;
		$this->html->exists = $exist;
		$this->html->zoneAndBin = $zoneAndBin['defaultArray'];
		$this->html->hasBin = $zoneAndBin['hasBin'];
		$this->html->attributeExists = $this->_model->checkAttributeExists($module
		, $this->_params['ioid']
		, $this->_params['ifid'], $getDataObject);

	}

	public function attributeOutPageAction()
	{
		$module = $this->_form->FormCode; // Tên module string 'Mxxx'
		$params = $this->params->requests->getParams();
		//$attributes = unserialize(Qss_Lib_Extra::formatUnSerialize($params['attributes']));
		$attributes = unserialize($params['attributes']);
		$attributes = ($attributes) ? $attributes->attributes : '';
		$beginLot = isset($params['beginLot']) ? $params['beginLot'] : '';
		$endLot = isset($params['endLot']) ? $params['endLot'] : '';
		$beginSerial = isset($params['beginSerial']) ? $params['beginSerial'] : '';
		$endSerial = isset($params['endSerial']) ? $params['endSerial'] : '';
		//$serialLot = unserialize(Qss_Lib_Extra::formatUnSerialize($params['serialLot']));
		$serialLot = unserialize($params['serialLot']);
		//$zone             = isset($params['zoneFilter'])?$params['zoneFilter']:'';
		$bin = isset($params['binFilter']) ? $params['binFilter'] : '';

		$this->html->totalPage = ceil($this->_model->searchMovementAttrCount(
		$params['ifid'], $serialLot->itemCode
		, $serialLot->warehouse
		, $beginLot, $endLot
		, $beginSerial, $endSerial
		, $module
		, $attributes
		, $bin, $getDataObject
		) / $params['perPage']
		);
		$this->html->page = $params['page'];

	}

	public function attributeOutSearchAction()
	{
		$module = $this->_form->FormCode; // Tên module string 'Mxxx'
		$ioid = $this->params->requests->getParam('ioid'); // lấy ioid
		$ifid = $this->params->requests->getParam('ifid'); // lấy ifid
		$objid = $this->params->requests->getParam('objid', 0);
		$getDataObject = @$this->_form->a_Objects[$objid]->ObjectCode;
		$params = $this->params->requests->getParams();
		$beginLot = isset($params['beginLot']) ? $params['beginLot'] : '';
		$endLot = isset($params['endLot']) ? $params['endLot'] : '';
		$beginSerial = isset($params['beginSerial']) ? $params['beginSerial'] : '';
		$endSerial = isset($params['endSerial']) ? $params['endSerial'] : '';
		$serialLot = $this->_model->getSerialLot($module
		, $this->_params['ioid'], $this->_params['ifid'], $getDataObject);
		$attributes = $params['attributes'];
		$warehouse = isset($params['warehouseFilter']) ? $params['warehouseFilter'] : $serialLot->warehouse;
		$bin = isset($params['binFilter']) ? $params['binFilter'] : '';

		$total = ceil($this->_model->searchMovementAttrCount($params['ifid'], $serialLot->itemCode
		, $warehouse
		, $beginLot, $endLot
		, $beginSerial, $endSerial
		, $module, $attributes
		, $bin, $getDataObject
		) / $params['perPage']);



		$pagex = (isset($params['page']) && $params['page'] <= $total) ? $params['page'] : 1;

		/* Truyền html */
		$this->html->searchMovementAttr = $this->_model->searchMovementAttrLimit($params['ifid']
		, $serialLot->itemCode, $warehouse
		, $beginLot, $endLot
		, $beginSerial, $endSerial
		, ($pagex - 1) * $params['perPage']
		, $params['perPage']
		, $module, $attributes
		, $bin, '', $getDataObject);

		$zoneAndBin = $this->getBinByWarehouse($serialLot->refWarehouse, $serialLot->refItem, $serialLot->itemUOM);


		$this->html->serialLot = $serialLot;
		$this->html->attributes = $params['attributes'];
		$this->html->attributeExists = $this->_model->checkAttributeExists($module, $ioid, $ifid, $getDataObject);
		$this->html->zoneAndBin = $zoneAndBin['defaultArray'];
		$this->html->hasBin = $zoneAndBin['hasBin'];
		$this->html->hasZone = $zoneAndBin['hasZone'];

	}

	public function attributeOutSaveAction()
	{
		$params = $this->params->requests->getParams();
		if ($this->params->requests->isAjax())
		{
			$service = $this->services->Extra->Warehouse->Shipment->Stockstatus->Save($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);

	}

	public function attributeOutChangeAction()
	{
		$module = $this->_form->FormCode; // Tên module string 'Mxxx'
		$ioid = $this->params->requests->getParam('ioid', 0); // lấy ioid
		$getDataObject = @$this->_form->a_Objects[$this->_params['objid']]->ObjectCode;
		$serialLot = $this->_model->getSerialLot($module
		, $this->_params['ioid'], $this->_params['ifid'], $getDataObject);
		$zoneAndBin = $this->getBinByWarehouse($serialLot->refWarehouse, $serialLot->refItem
		, $serialLot->itemUOM);

		/* Truyen HTML */
		$this->html->serialLot = $serialLot;
		$this->html->attributes = $this->_params['attributes'];
		$ioidSecond = ($ioid) ? $ioid : $this->_model->getIOIDfromIFID($module, $this->_params['ifid']);
		$this->html->exists = $this->_model->getOldStockStatus($module, $ioidSecond
		, $this->_params['ifid'], $getDataObject);
		$this->html->attributeExists = $this->_model->checkAttributeExists($module, $ioid
		, $this->_params['ifid'], $getDataObject);
		$this->html->zoneAndBin = $zoneAndBin['defaultArray'];
		$this->html->hasBin = $zoneAndBin['hasBin'];
		$this->html->hasZone = $zoneAndBin['hasZone'];

	}

	//======================================================================================
	public function attributeMovementCreateAction()
	{
		$module = $this->_form->FormCode;
		$serialLot = $this->_model->getSerialLot($module
		, $this->_params['ioid'], $this->_params['ifid']);
		$attributes = $this->_model->getAttributes($module, $this->_params['ioid']);
		// Kho lấy hàng
		$zoneAndBinFrom = $this->getBinByWarehouse($serialLot->refWarehouse
		, $serialLot->refItem, $serialLot->itemUOM);
		// Kho chuyển đến
		$zoneAndBinTo = $this->getBinByWarehouse($serialLot->refToWarehouse
		, $serialLot->refItemCode, $serialLot->itemUOM);

		$this->html->module = $module;
		$this->html->ifid = $this->_params['ifid'];
		$this->html->ioid = $this->_params['ioid'];
		$this->html->zoneAndBinFrom = $zoneAndBinFrom;
		$this->html->zoneAndBinTo = $zoneAndBinTo;
		$this->html->serialLot = $serialLot;
		$this->html->attributes = $attributes;
		$this->html->oldValue = $this->_model->getOldStockStatus($module, $this->_params['ioid'], $this->_params['ifid']);

	}

	public function attributeMovementChangeAction()
	{
		$ioid = $this->params->requests->getParam('ioid');
		$ifid = $this->params->requests->getParam('ifid');
		$module = $this->_form->FormCode;
		$serialLot = $this->_model->getSerialLot($module, $ioid, $ifid);
		$attributes = $this->_model->getAttributes($module, $ioid, $ifid);

		$this->html->serialLot = $serialLot;
		$this->html->attributes = $attributes;
		$this->html->zoneAndBinFrom = $this->getBinByWarehouse($serialLot->refWarehouse
		, $serialLot->refItem, $serialLot->itemUOM);
		$this->html->zoneAndBinTo = $this->getBinByWarehouse($serialLot->refToWarehouse
		, $serialLot->refItem, $serialLot->itemUOM);
		$this->html->oldValue = $this->_model->getOldStockStatus($module, $ioid, $ifid);

	}

	public function attributeMovementSearchAction()
	{
		$module = $this->_form->FormCode;
		$serialLot = $this->_model->getSerialLot($module, $this->_params['ioid'], $this->_params['ifid']);
		$beginLot = isset($this->_params['beginLot']) ? $this->_params['beginLot'] : '';
		$endLot = isset($this->_params['endLot']) ? $this->_params['endLot'] : '';
		$beginSerial = isset($this->_params['beginSerial']) ? $this->_params['beginSerial'] : '';
		$endSerial = isset($this->_params['endSerial']) ? $this->_params['endSerial'] : '';
		$binFilter = isset($this->_params['binFilter']) ? $this->_params['binFilter'] : '';
		$attributes = $this->_model->getAttributes($module, $this->_params['ioid'], $this->_params['ifid']);
		$totalPage = ceil($this->_model->searchMovementAttrCount($this->_params['ifid']
		, $serialLot->itemCode, $serialLot->warehouse
		, $beginLot, $endLot
		, $beginSerial, $endSerial
		, $module, $attributes
		, $binFilter, $serialLot->toWarehouse) / $this->_params['perPage']);

		$pagex = (isset($this->_params['page']) && $this->_params['page'] <= $totalPage) ?
		$this->_params['page'] : 1;
		$limit = $this->_model->searchMovementAttrLimit($this->_params['ifid']
		, $serialLot->itemCode, $serialLot->warehouse
		, $beginLot, $endLot
		, $beginSerial, $endSerial
		, ($pagex - 1) * $this->_params['perPage']
		, $this->_params['perPage']
		, $module, $attributes
		, $binFilter, $serialLot->toWarehouse);

		/** html */
		$this->html->module = $module;
		$this->html->ifid = $this->_params['ifid'];
		$this->html->ioid = $this->_params['ioid'];
		$this->html->serialLot = $serialLot;
		$this->html->attributes = $attributes;
		$this->html->lineInfo = $this->_common->getTable(array('*'), 'ODanhSachCK'
		, array('IFID_M603' => $this->_params['ifid']), array(), 'NO_LIMIT', 1);
		;
		$this->html->limit = $limit;
		$this->html->zoneAndBinFrom = $this->getBinByWarehouse($serialLot->refWarehouse
		, $serialLot->refItem, $serialLot->itemUOM);
		$this->html->zoneAndBinTo = $this->getBinByWarehouse($serialLot->refToWarehouse
		, $serialLot->refItem, $serialLot->itemUOM);

	}

	public function attributeMovementPageAction()
	{
		$params = $this->params->requests->getParams();
		$ioid = $params['ioid'];
		$ifid = $params['ifid'];
		$module = $this->_form->FormCode;
		$serialLot = $this->_model->getSerialLot($module, $ioid, $ifid);
		$beginLot = isset($params['beginLot']) ? $params['beginLot'] : '';
		$endLot = isset($params['endLot']) ? $params['endLot'] : '';
		$beginSerial = isset($params['beginSerial']) ? $params['beginSerial'] : '';
		$endSerial = isset($params['endSerial']) ? $params['endSerial'] : '';
		$zoneFilter = isset($params['zoneFilter']) ? $params['zoneFilter'] : '';
		$binFilter = isset($params['binFilter']) ? $params['binFilter'] : '';
		$attributes = $this->_model->getAttributes($module, $ioid, $ifid);
		$totalPage = ceil($this->_model->searchMovementAttrCount($ifid
		, $serialLot->itemCode, $serialLot->warehouse
		, $beginLot, $endLot
		, $beginSerial, $endSerial
		, $module, $attributes
		, $binFilter) / $params['perPage']);

		$this->html->totalPage = $totalPage;
		$this->html->page = $params['page'];

	}

	/**
	 * Hiển thị bin theo zone
	 */
	public function attributeMovementBinAction()
	{
		$zone = $this->params->requests->getParam('zone');
		$zoneAndBin = $this->getBinByWarehouse($this->params->requests->getParam('refToWarehouse'), $this->params->requests->getParam('refItem')
		, $this->params->requests->getParam('itemUOM', ''));
		$this->html->zoneAndBin = $zoneAndBin['defaultArray'][$zone]['bin'];
		$this->html->zone = $zone;
		$this->html->fromZone = $this->params->requests->getParam('fromZone', 0);
		$this->html->fromBin = $this->params->requests->getParam('fromBin', 0);

	}

	public function attributeMovementSaveAction()
	{
		$params = $this->params->requests->getParams();
		if ($this->params->requests->isAjax())
		{
			$service = $this->services->Extra->Warehouse->Movement->Stockstatus->Save($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);

	}

	/** --- Thong ke san xuat --- */
	public function attributeStatisticIndexAction()
	{
		$module = $this->_form->FormCode;
		$info = $this->_common->getTable(array('*'), 'OThongKeSanLuong', array('IFID_M717' => $this->_params['ifid']), array(), 1, 1);
		$stockInfo = $this->getBinConfigForStatistic(array('line' => $info->DayChuyen
		, 'wc' => $info->Ref_DonViThucHien));
		$serialLot = $this->_model->getSerialLot($module, @(int) $this->_params['ioid']
		, $this->_params['ifid']);
		$attributes = $this->_model->getAttributes($module, @(int) $this->_params['ioid']
		, $this->_params['ifid']);


		// ----- html ------
		$this->html->ioid = @(int) $this->_params['ioid'];
		$this->html->ifid = $this->_params['ifid'];
		$this->html->serialLot = $serialLot;
		$this->html->attributes = $attributes;
		$this->html->info = $stockInfo;

	}

	public function attributeStatisticAddAction()
	{
		$module = $this->_form->FormCode;
		$getDataObject = @$this->_form->a_Objects[$this->_params['objid']]->ObjectCode;
		$ioid = $this->params->requests->getParam('ioid', 0);
		$serialLot = $this->_model->getSerialLot($module, $ioid, $this->_params['ifid']
		, $getDataObject);
		$ioidEnd = ($ioid) ? $ioid : $this->_model->getIOIDfromIFID($module
		, $this->_params['ifid']);
		$attributes = $this->_model->getAttributes($module, $ioidEnd, $this->_params['ifid']
		, $getDataObject);
		$oldValue = $this->_model->getOldStockStatus($module, $ioidEnd
		, $this->_params['ifid'], $getDataObject);
		$zoneAndBin = $this->getBinByWarehouse($serialLot->refWarehouse, $serialLot->refItem
		, $serialLot->itemUOM);
		//$deface       = $this->_model->countDeface($module, $this->_params['ifid']);
		//$itemQty      = $serialLot->itemQty - $deface;
		$itemQty = $serialLot->itemQty;
		$info = $this->_common->getTable(array('*'), 'OThongKeSanLuong', array('IFID_M717' => $this->_params['ifid']), array(), 1, 1);
		$stockInfo = $this->getBinConfigForStatistic(array('line' => $info->DayChuyen
		, 'wc' => $info->Ref_DonViThucHien));

		$this->html->ioid = $ioid;
		$this->html->ifid = $this->_params['ifid'];
		$this->html->objid = @$this->_params['objid'];
		$this->html->getDataObject = $getDataObject;
		$this->html->module = $module;
		$this->html->serialLot = $serialLot;
		$this->html->attributes = $attributes;
		$this->html->itemQty = $itemQty;
		$this->html->oldValue = $this->sortOldStockStatus($oldValue);
		$this->html->attributeExists = $this->_model->checkAttributeExists($module, $ioid
		, $this->_params['ifid'], $getDataObject);
		$this->html->zoneAndBin = $zoneAndBin;
		$this->html->stockInfo = $stockInfo;

	}

	public function attributeStatisticShowAction()
	{
		//$serialLot = unserialize(Qss_Lib_Extra::formatUnSerialize($this->params->requests->getParam('serialLot')));
		$serialLot = unserialize($this->params->requests->getParam('serialLot'));
		$module = $this->_form->FormCode;
		$getDataObject = @$this->_form->a_Objects[$this->_params['objid']]->ObjectCode;
		$lastLot = $this->_model->getLastLot($serialLot->preLot); // last lost
		//, array(1=>array('module'=>'M402', 'ifid'=>$this->_params['ifid'])));
		$lastSerail = $this->_model->getLastSerial($serialLot->preSerial); // last serial
		// , array(1=>array('module'=>'M402', 'ifid'=>$this->_params['ifid'])));
		$ioidEnd = ($this->_params['ioid']) ?
		$this->_params['ioid'] : $this->_model->getIOIDfromIFID($module
		, $this->_params['ifid']);
		$oldValue = $this->_model->getOldStockStatus($module
		, $ioidEnd, $this->_params['ifid'], $getDataObject);
		$zoneAndBin = $this->getBinByWarehouse($serialLot->refWarehouse, $serialLot->refItem
		, $serialLot->itemUOM);
		$lines = $this->getLines($serialLot->lot, $serialLot->serial, $serialLot->itemQty
		, $this->_params['lotQty'], $zoneAndBin['lineWhereOnly']);
		$info = $this->_common->getTable(array('*'), 'OThongKeSanLuong', array('IFID_M717' => $this->_params['ifid']), array(), 1, 1);
		$stockInfo = $this->getBinConfigForStatistic(array('line' => $info->DayChuyen
		, 'wc' => $info->Ref_DonViThucHien));

		/** html */
		$this->html->serialLot = $serialLot;
		$this->html->lines = $lines;
		$this->html->lastLot = $lastLot;
		$this->html->lastSerial = $lastSerail;
		$this->html->lotQty = $this->_params['lotQty'];
		$this->html->oldValue = $this->sortOldStockStatus($oldValue);
		$this->html->zoneAndBin = $zoneAndBin['defaultArray'];
		$this->html->hasBin = $zoneAndBin['hasBin'];
		$this->html->zoneBinSort = $zoneAndBin['arrayWhereOnly'];
		$this->html->itemQty = $this->_params['itemQty'];
		$this->html->attributeExists = $this->_model->checkAttributeExists($module, $this->_params['ioid']
		, $this->_params['ifid'], $getDataObject);
		$this->html->attributes = $this->_model->getAttributes($module
		, $this->_params['ioid'], $this->_params['ifid']
		, $getDataObject);
		$this->html->stockInfo = $stockInfo;

	}

	public function attributeStatisticSaveAction()
	{
		$params = $this->params->requests->getParams();
		if ($this->params->requests->isAjax())
		{
			$service = $this->services->Extra->Warehouse->Receive->Stockstatus->Save($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);

	}

	public function attributeStatisticGetAction()
	{
		$module = $this->_form->FormCode;
		$serialLot = $this->_model->getSerialLot($module
		, @(int) $this->_params['ioid'], $this->_params['ifid']);
		$attributes = $this->_model->getAttributes($module, $this->_params['ioid']
		, $this->_params['ifid']);
		// Kho lấy hàng
		$zoneAndBinFrom = $this->getBinByWarehouse($serialLot->refWarehouse
		, $serialLot->refItem, $serialLot->itemUOM);
		// Kho chuyển đến
		$zoneAndBinTo = $this->getBinByWarehouse($serialLot->refToWarehouse
		, $serialLot->refItem, $serialLot->itemUOM);
		$info = $this->_common->getTable(array('*'), 'OThongKeSanLuong', array('IFID_M717' => $this->_params['ifid']), array(), 1, 1);
		$stockInfo = $this->getBinConfigForStatistic(array('line' => $info->DayChuyen
		, 'wc' => $info->Ref_DonViThucHien));

		$this->html->module = $module;
		$this->html->ifid = $this->_params['ifid'];
		$this->html->ioid = $this->_params['ioid'];
		$this->html->zoneAndBinFrom = $zoneAndBinFrom;
		$this->html->zoneAndBinTo = $zoneAndBinTo;
		$this->html->serialLot = $serialLot;
		$this->html->attributes = $attributes;
		$this->html->oldValue = $this->_model->getOldStockStatus($module, $this->_params['ioid']
		, $this->_params['ifid']);
		$this->html->stockInfo = $stockInfo;

	}

	public function attributeStatisticSearchAction()
	{
		$module = $this->_form->FormCode;
		$serialLot = $this->_model->getSerialLot($module, $this->_params['ioid']
		, $this->_params['ifid']);
		$beginLot = isset($this->_params['beginLot']) ? $this->_params['beginLot'] : '';
		$endLot = isset($this->_params['endLot']) ? $this->_params['endLot'] : '';
		$beginSerial = isset($this->_params['beginSerial']) ? $this->_params['beginSerial'] : '';
		$endSerial = isset($this->_params['endSerial']) ? $this->_params['endSerial'] : '';
		$binFilter = isset($this->_params['binFilter']) ? $this->_params['binFilter'] : '';
		$attributes = $this->_model->getAttributes($module, $this->_params['ioid']
		, $this->_params['ifid']);
		$info = $this->_common->getTable(array('*'), 'OThongKeSanLuong', array('IFID_M717' => $this->_params['ifid']), array(), 1, 1);
		$stockInfo = $this->getBinConfigForStatistic(array('line' => $info->DayChuyen
		, 'wc' => $info->Ref_DonViThucHien));
		$totalPage = ceil($this->_model->searchMovementAttrCount($this->_params['ifid']
		, $serialLot->itemCode, $serialLot->warehouse
		, $beginLot, $endLot
		, $beginSerial, $endSerial
		, $module, $attributes
		, $stockInfo['FromBin']) / $this->_params['perPage']);


		$pagex = (isset($this->_params['page']) && $this->_params['page'] <= $totalPage) ?
		$this->_params['page'] : 1;
		$limit = $this->_model->searchMovementAttrLimit($this->_params['ifid']
		, $serialLot->itemCode, $serialLot->warehouse
		, $beginLot, $endLot
		, $beginSerial, $endSerial
		, ($pagex - 1) * $this->_params['perPage']
		, $this->_params['perPage']
		, $module, $attributes
		, $stockInfo['FromBin']);

		/** html */
		$this->html->module = $module;
		$this->html->ifid = $this->_params['ifid'];
		$this->html->ioid = $this->_params['ioid'];
		$this->html->serialLot = $serialLot;
		$this->html->attributes = $attributes;
		$this->html->lineInfo = $this->_common->getTable(array('*'), 'OThongKeSanLuong'
		, array('IFID_M717' => $this->_params['ifid'])
		, array(), 'NO_LIMIT', 1);
		;
		$this->html->limit = $limit;
		$this->html->zoneAndBinFrom = $this->getBinByWarehouse($serialLot->refWarehouse
		, $serialLot->refItem, $serialLot->itemUOM);
		$this->html->zoneAndBinTo = $this->getBinByWarehouse($serialLot->refToWarehouse
		, $serialLot->refItem, $serialLot->itemUOM);
		$this->html->stockInfo = $stockInfo;

	}

	public function attributeStatisticPageAction()
	{
		$params = $this->params->requests->getParams();
		$ioid = $params['ioid'];
		$ifid = $params['ifid'];
		$module = $this->_form->FormCode;
		$serialLot = $this->_model->getSerialLot($module, $ioid, $ifid);
		$beginLot = isset($params['beginLot']) ? $params['beginLot'] : '';
		$endLot = isset($params['endLot']) ? $params['endLot'] : '';
		$beginSerial = isset($params['beginSerial']) ? $params['beginSerial'] : '';
		$endSerial = isset($params['endSerial']) ? $params['endSerial'] : '';
		$zoneFilter = isset($params['zoneFilter']) ? $params['zoneFilter'] : '';
		$binFilter = isset($params['binFilter']) ? $params['binFilter'] : '';
		$attributes = $this->_model->getAttributes($module, $ioid, $ifid);
		$totalPage = ceil($this->_model->searchMovementAttrCount($ifid
		, $serialLot->itemCode, $serialLot->warehouse
		, $beginLot, $endLot
		, $beginSerial, $endSerial
		, $module, $attributes
		, $binFilter) / $params['perPage']);

		$this->html->totalPage = $totalPage;
		$this->html->page = $params['page'];

	}

	public function attributeStatisticSave2Action()
	{
		$params = $this->params->requests->getParams();
		if ($this->params->requests->isAjax())
		{
			$service = $this->services->Extra->Warehouse->Movement->Stockstatus->Save($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);

	}

	public function attributeStatisticChangeAction()
	{
		$ioid = $this->params->requests->getParam('ioid');
		$ifid = $this->params->requests->getParam('ifid');
		$module = $this->_form->FormCode;
		$serialLot = $this->_model->getSerialLot($module, $ioid, $ifid);
		$attributes = $this->_model->getAttributes($module, $ioid, $ifid);

		$this->html->serialLot = $serialLot;
		$this->html->attributes = $attributes;
		$this->html->zoneAndBinFrom = $this->getBinByWarehouse($serialLot->refWarehouse
		, $serialLot->refItem, $serialLot->itemUOM);
		$this->html->zoneAndBinTo = $this->getBinByWarehouse($serialLot->refToWarehouse
		, $serialLot->refItem, $serialLot->itemUOM);
		$this->html->oldValue = $this->_model->getOldStockStatus($module, $ioid, $ifid
		, '', 'OTrangThaiLuuTruCK');

	}

	/**
	 * @param array $filter, array('line'=>, 'wc'=>id)
	 */
	private function getBinConfigForStatistic($filter)
	{
		$retval = array();
		$operator = $this->_common->getObjectByIDArr($filter['line'], array('code' => 'M702'
		, 'table' => 'OCongDoanDayChuyen'
		, 'main' => 'ODayChuyen'), 3, 'MaDayChuyen'
		);

		// Lay bin lay sp, bin nhan sp, danh lot thoe cd, danh serial theo cd
		// theo cong doan va don vi thuc hien
		foreach ($operator as $opt)
		{
			if ($filter['wc'] == $opt->IOID)
			{
				$retval['FromBin'] = $opt->TuBin;
				$retval['ToBin'] = $opt->DenBin;
				$retval['Lot'] = $opt->Lot;
				$retval['Serial'] = $opt->Serial;
			}
		}

		return $retval;

	}

	private function getWarehouses($filter = '', $order = array())
	{
		$order = (is_array($order) && count($order)) ? $order : array('MaKho');
		$filterArr = (is_array($filter) && count($filter)) ? $filter : array();
		$filterStr = (is_string($filter)) ? $filter : '';

		$warehouses = $this->_common->getTable(array('MaKho as WCode', 'TenKho as WName', 'IOID as WID'), 'ODanhSachKho', $filterArr, $order, 'NO_LIMIT', 2);
		return $warehouses;

	}

}

?>