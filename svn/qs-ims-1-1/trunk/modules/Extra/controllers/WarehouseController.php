<?php
/**
 * @author: ThinhTuan
 * @component: Warehouse
 * @place: modules/Extra/controllers/WarehouseController.php
 */
class Extra_WarehouseController extends Qss_Lib_Controller
{
	public $_form;
	public $_common;
	public $_warehouse;
	public $_params;
	private $incomingObject = 'OHangDoiNhap';
	private $outgoingObject = 'OHangDoiXuat';
	private $inputObject    = 'ONhapKho';
	private $outputObject   = 'OXuatKho';
	private $outputLockStep = array(2);
	private $inputLockStep  = array(2);


	public function init ()
	{
		//$this->i_SecurityLevel = 15;
		parent::init();


		$ifid   = $this->params->requests->getParam('ifid',0);
		$fid	= $this->params->requests->getParam('fid',0);
		$deptid = $this->params->requests->getParam('deptid',$this->_user->user_dept_id);
		$form 	= new Qss_Model_Form();

		if($ifid)
		{
			$form->initData($ifid, $deptid);
		}
		else
		{
			$form->init($fid,$deptid, $this->_user->user_id);
		}
		//$this->b_fCheckRightsOnForm($form,2);

		$this->_form = $form;
		$this->_common = new Qss_Model_Extra_Extra();
		$this->_warehouse = new Qss_Model_Extra_Warehouse();
		$this->_params = $this->params->requests->getParams();
		$this->html->form = $form;
	}

	/**
	 * Tao nhan hang va chuyen hang tu hang doi.
	 * Loc theo doi tac hoac kho
	 */

	// @Action: input/create/barcode/index
	// @Description: tao nhan hang bang barcode
	public function inputCreateBarcodeIndexAction()
	{

	}

	// @Action: input/create/barcode/get
	// @Description: Lay thong tin theo barcode nhap vao
	public function inputCreateBarcodeGetAction()
	{
		$filter = array('Barcode'=>$this->_params['barcode']);
		$data   = $this->getInfoByBarcode($filter);
		echo Qss_Json::encode(array('error' => 0,'data'=>$data));
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	// @Action: input/create/barcode/save
	// @Description: Luu lai thong tin nhan hang
	public function inputCreateBarcodeSaveAction()
	{
		$params = $this->params->requests->getParams();
		if ( $this->params->requests->isAjax() )
		{
			$service = $this->services->Extra->Warehouse->Input->Barcode->Save($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	public function inputCreateIndexAction()
	{
		$model = new Qss_Model_Extra_Warehouse();
		$extra = new Qss_Model_Extra_Extra();
		$params = $this->params->requests->getParams();
		$status = $this->_form->i_fGetCerrentStep();
		//$extra->getStatusByIFID('M402', $this->inputObject, $params['ifid']);

		// Get partners
		$this->html->partner = $extra->getTable(array('Ref_MaNCC as IOID, MaNCC as MaDoiTac, TenNCC as TenDoiTac'),
                                                'ONhapKho', array('IFID_M402'=>$params['ifid']),
		array(), 1, 1
		);
		// Get warehouses
		$this->html->warehouses = $extra->getTable(array('*'), 'ODanhSachKho', array(),
		array('MaKho ASC'), 'NO_LIMIT');
		// Default warehouses
		$this->html->defaultWarehouse = @(int)$extra->getTable(array('Ref_Kho as Kho'),
                                                'ONhapKho',array('IFID_M402'=>$params['ifid']),
		array(), 1, 1)->Kho;
		$this->html->ifid = $params['ifid'];
		$this->html->insertModule = $this->_form->FormCode;
		$this->html->insertObject  = $this->inputObject;
		$this->html->unlock = (in_array($status, $this->inputLockStep))?false:true;
	}

	public function inputCreateSearchAction()
	{
		$model  = new Qss_Model_Extra_Warehouse();
		$params = $this->params->requests->getParams();
		$formDate = Qss_Lib_Date::displaytomysql($params['fromDateFilter']);
		$toDate = Qss_Lib_Date::displaytomysql($params['toDateFilter']);
		$this->html->search = $model->getComingShipments($this->incomingObject, $this->inputObject,
		$params['partnerFilter'],
		$params['warehouseFilter'], $formDate,
		$toDate,$params['pageFilter'],
		$params['perpageFilter']);
	}

	public function inputCreatePageAction()
	{
		$model   = new Qss_Model_Extra_Warehouse();
		$params  = $this->params->requests->getParams();
		$ifid    = $this->params->requests->getParam('ifid');
		$perpage = $this->params->requests->getParam('perpageFilter');
		$page    = $this->params->requests->getParam('pageFilter');
		$formDate = Qss_Lib_Date::displaytomysql($params['fromDateFilter']);
		$toDate = Qss_Lib_Date::displaytomysql($params['toDateFilter']);

		$form    = new Qss_Model_Form();
		$form->initData($ifid,$this->_user->user_dept_id);
		if($this->b_fCheckRightsOnForm($form,2))
		{
			$totalPage = ceil(@(int)$model->countComingShipment($this->incomingObject,
			$params['partnerFilter'], $params['warehouseFilter'],
			$formDate, $toDate)/$perpage);

			$data = array('totalPage'=>$totalPage, 'page'=>$page);
			echo Qss_Json::encode(array('error' => 0,'data'=>$data));
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	public function inputCreateLineAction()
	{
		$extra = new Qss_Model_Extra_Extra();
		$params  = $this->params->requests->getParams();
		$status = $this->_form->i_fGetCerrentStep();
		//$extra->getStatusByIFID('M402', $this->inputObject, $params['ifid']);

		$this->html->line = $extra->getTable(array('*'), 'ODanhSachNhapKho',
		array('IFID_M402'=>$params['ifid']),
		array(), 'NO_LIMIT');
		$this->html->unlock = (in_array($status, $this->inputLockStep))?false:true;
	}

	public function inputCreateSaveAction()
	{
		$params = $this->params->requests->getParams();
		if ( $this->params->requests->isAjax() )
		{
			$service = $this->services->Extra->Warehouse->Input->Save($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	public function inputCreateDeleteAction()
	{
		$params = $this->params->requests->getParams();
		if ( $this->params->requests->isAjax() )
		{
			$service = $this->services->Extra->Warehouse->Input->Delete($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}


	// @Action: output/create/barcode/index
	// @Description: tao xuat hang bang barcode
	public function outputCreateBarcodeIndexAction()
	{

	}

	// @Action: output/create/barcode/get
	// @Description: Lay thong tin theo barcode nhap vao
	public function outputCreateBarcodeGetAction()
	{
		$filter = array('Barcode'=>$this->_params['barcode']);
		$data   = $this->getInfoByBarcode($filter);
		echo Qss_Json::encode(array('error' => 0,'data'=>$data));
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	// @Action: output/create/barcode/save
	// @Description: Luu lai thong tin xuat hang
	public function outputCreateBarcodeSaveAction()
	{
		$params = $this->params->requests->getParams();
		if ( $this->params->requests->isAjax() )
		{
			$service = $this->services->Extra->Warehouse->Output->Barcode->Save($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}


	public function outputCreateIndexAction()
	{
		$model = new Qss_Model_Extra_Warehouse();
		$extra = new Qss_Model_Extra_Extra();
		$params = $this->params->requests->getParams();
		$status = $this->_form->i_fGetCerrentStep();
		//$extra->getStatusByIFID('M506', $this->outputObject, $params['ifid']);

		// Get partners
		$this->html->partner = $extra->getTable(array('Ref_MaKH as IOID, MaKH as MaDoiTac, TenKhachHang as TenDoiTac'),
												'OXuatKho',array('IFID_M506'=>$params['ifid']), array(),1, 1
		);
		// Get warehouses
		$this->html->warehouses = $extra->getTable(array('*'), 'ODanhSachKho',
		array(), array('MaKho ASC'), 'NO_LIMIT');
		// Default warehouses
		$this->html->defaultWarehouse = @(int)$extra->getTable(array('Ref_Kho as Kho'), 'OXuatKho',
		array('IFID_M506'=>$params['ifid']), array(), 1, 1)->Kho;
		$this->html->ifid = $params['ifid'];
		$this->html->insertModule = $this->_form->FormCode;
		$this->html->insertObject  = $this->outputObject;
		$this->html->unlock = (in_array($status, $this->outputLockStep))?false:true;
	}

	public function outputCreateSearchAction()
	{
		$model  = new Qss_Model_Extra_Warehouse();
		$params = $this->params->requests->getParams();
		$formDate = Qss_Lib_Date::displaytomysql($params['fromDateFilter']);
		$toDate = Qss_Lib_Date::displaytomysql($params['toDateFilter']);

		$this->html->search = $model->getComingShipments($this->outgoingObject, $this->outputObject,
		$params['partnerFilter'],
		$params['warehouseFilter'], $formDate,
		$toDate,$params['pageFilter'],
		$params['perpageFilter']);
	}

	public function outputCreatePageAction()
	{
		$model   = new Qss_Model_Extra_Warehouse();
		$params  = $this->params->requests->getParams();
		$ifid    = $this->params->requests->getParam('ifid');
		$perpage = $this->params->requests->getParam('perpageFilter');
		$page    = $this->params->requests->getParam('pageFilter');
		$formDate = Qss_Lib_Date::displaytomysql($params['fromDateFilter']);
		$toDate = Qss_Lib_Date::displaytomysql($params['toDateFilter']);

		$form    = new Qss_Model_Form();
		$form->initData($ifid,$this->_user->user_dept_id);
		if($this->b_fCheckRightsOnForm($form,2))
		{
			$totalPage = ceil(@(int)$model->countComingShipment($this->outgoingObject,
			$params['partnerFilter'], $params['warehouseFilter'],
			$formDate, $toDate)/$perpage);

			$data = array('totalPage'=>$totalPage, 'page'=>$page);
			echo Qss_Json::encode(array('error' => 0,'data'=>$data));
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	public function outputCreateLineAction()
	{
		$extra   = new Qss_Model_Extra_Extra();
		$params  = $this->params->requests->getParams();
		$status  = $this->_form->i_fGetCerrentStep();
		//$extra->getStatusByIFID('M506', $this->outputObject, $params['ifid']);
		$this->html->line = $extra->getTable(array('*'), 'ODanhSachXuatKho',
		array('IFID_M506'=>$params['ifid']), array(), 'NO_LIMIT'
		);
		$this->html->unlock = (in_array($status, $this->outputLockStep))?false:true;
	}

	public function outputCreateSaveAction()
	{
		$params = $this->params->requests->getParams();
		if ( $this->params->requests->isAjax() )
		{
			$service = $this->services->Extra->Warehouse->Output->Save($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	public function outputCreateDeleteAction()
	{
		$params = $this->params->requests->getParams();
		if ( $this->params->requests->isAjax() )
		{
			$service = $this->services->Extra->Warehouse->Output->Delete($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	/**
	 * End Tao nhan hang va chuyen hang tu hang doi.
	 */


	// @Action: control/create/barcode/index
	// @Description: tao kiem ke bang barcode
	public function controlCreateBarcodeIndexAction()
	{

	}

	// @Action: control/create/barcode/get
	// @Description: Lay thong tin theo barcode nhap vao
	public function controlCreateBarcodeGetAction()
	{
		$filter = array('Barcode'=>$this->_params['barcode']);
		$data   = $this->getInfoByBarcode($filter);
		echo Qss_Json::encode(array('error' => 0,'data'=>$data));
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	// @Action: control/create/barcode/save
	// @Description: Luu lai thong tin kiem ke
	public function controlCreateBarcodeSaveAction()
	{
		$params = $this->params->requests->getParams();
		if ( $this->params->requests->isAjax() )
		{
			$service = $this->services->Extra->Warehouse->Control->Barcode->Save($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}


	/** Private function */

	/**
	 * @param array $filter, mang chua thong tin loc, array('Barcode'=>barcode)
	 * @return array, tra ve thong tin dua tren barcode
	 */
	private function getInfoByBarcode($filter)
	{
		$data    = array();
		$info    = $this->_warehouse->getInfoByBarocde($filter);
		$oldIFID = '';
		$retval  = array();
		$i       = -1;

		foreach($info as $val)
		{
			if($oldIFID != $val->IFID_M113)
			{
				$i++;
				$j = 0;
				$retval[$i]['Barcode']    = $val->Barcode;
				$retval[$i]['MaSanPham']  = $val->MaSanPham;
				$retval[$i]['TenSanPham'] = $val->TenSanPham;
				$retval[$i]['GiaVon']     = $val->GiaVon;
			}

			$retval[$i]['DonViTinh'][$j]['Ten'] = $val->DonViTinh;
			$retval[$i]['DonViTinh'][$j]['MacDinh'] = (int)$val->MacDinh;
			$j++;

			$oldIFID = $val->IFID_M113;
		}

		$data   = array('info'=>$retval);
		return $data;
	}

	/**
	 * Nut extra lay gia xuat kho trong module Xuat kho M506
	 */
	public function outputPriceSelectIndexAction()
	{
		// models
		$common    = new Qss_Model_Extra_Extra();
		$warehouse = new Qss_Model_Extra_Warehouse();
        $inout     = new Qss_Model_Warehouse_Inout();
		$field     = new Qss_Model_Field();
		$field->ObjectCode = 'ODanhSachXuatKho';
			
		// params
		$item_code_id     = $this->params->requests->getParam('ODanhSachXuatKho_MaSP', 0);
		$qty              = $this->params->requests->getParam('ODanhSachXuatKho_SoLuong', 0);
		$item_uom_id      = $this->params->requests->getParam('ODanhSachXuatKho_DonViTinh', 0);
		$ifid             = $this->params->requests->getParam('ifid', 0 );
		$ioid             = $this->params->requests->getParam('ioid', 0 );
			
		// vid[0]
//		$item_uom_id_arr  = explode(',', $item_uom_id);

		// vid=>value
//		$field_item_code = $field->b_fInit('ODanhSachXuatKho','MaSP');
//		$item_code       = $field->getDataById($item_code_id);
//		$field_uom       = $field->b_fInit('ODanhSachXuatKho','DonViTinh');
//		$uom             = $field->getDataById($item_uom_id);
			
			
		// Tinh gia cuoi cung: Lay gia nhap kho mua hang lan cuoi cung
		$last = $inout->getLastInputOfItem($item_code_id);

        $this->html->last = $last?($last->DonGia?$last->DonGia:$last->GiaMua):0;
						
        // Tinh gia trung binh: Lay gia trung binh cua cac lan nhap kho tu dau thang
        $main_line = $common->getTableFetchOne('OXuatKho', array('IFID_M506'=>$ifid));
						
        // Lay ngay chung tu neu ko co lay ngay hien tai
        //			$main_line = new stdClass();
        //			$main_line->NgayChungTu = '2014-09-18';
        $doc_date = $main_line?
        ((!$main_line->NgayChungTu
        || $main_line->NgayChungTu == '0000-00-00')?date('Y-m-d'):$main_line->NgayChungTu):date('Y-m-d');

        $doc_date_microtime = strtotime($doc_date);
        $doc_month  = date('m', $doc_date_microtime);
        $first_date = date('Y-m-01', $doc_date_microtime);

        $total_output_val = $inout->getTotalOutputVal($first_date,$doc_date,$item_code_id);

        $this->html->avg = $total_output_val?($total_output_val->TotalQty?($total_output_val->TotalVal/$total_output_val->TotalQty):0):0;

        // field id cua truong gia
        $this->html->priceid = 'DonGia';
        $this->html->totalid = 'ThanhTien';
        $this->html->qty     = $qty;
        $this->html->ioid    = $ioid;
	}

}
?>