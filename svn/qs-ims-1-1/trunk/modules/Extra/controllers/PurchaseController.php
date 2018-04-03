<?php
/**
 *
 * @author ThinhTNM
 *
 */
class Extra_PurchaseController extends Qss_Lib_Controller
{
	public $_form;

	public function init ()
	{
		//$this->i_SecurityLevel = 15;
		parent::init();
		$ifid   = $this->params->requests->getParam('ifid',0);
		$fid	= $this->params->requests->getParam('fid',0);
		$deptid = $this->params->requests->getParam('deptid',$this->_user->user_dept_id);
		$this->headScript($this->params->requests->getBasePath() . '/js/common.js');
		$form 	= new Qss_Model_Form();
		if($ifid)
		{
			$form->initData($ifid, $deptid);
		}
		else
		{
			$form->init($fid,$deptid, $this->_user->user_id);
		}
		$this->b_fCheckRightsOnForm($form,2);
		$this->_form = $form;
		$this->html->form = $form;
	}

	public function	 CreatepoRequisitionIndexAction()
	{
		$model            	= new Qss_Model_Extra_Purchase();
		$extra            	= new Qss_Model_Extra_Extra();

		$this->html->docNo  = Qss_Lib_Extra::getDocumentNo($this->_form->o_fGetObjectByCode('ODonMuaHang'));
		$this->html->partner = $extra->getTable(array('*'), 'ODoiTac',
		array('NhaCungCap'=>1) , array('MaDoiTac ASC'),
												'NO_LIMIT'
												);
	}

	public function	 CreatepoRequisitionSearchAction()
	{
		$model            	= new Qss_Model_Extra_Purchase();
		$params             = $this->params->requests->getParams();
		$this->html->search = $model->getRequisition(Qss_Lib_Date::displaytomysql($params['fromDateFilter']),
		Qss_Lib_Date::displaytomysql($params['toDateFilter']),
		$params['pageFilter'],
		$params['perpageFilter']);
	}

	public function CreatepoRequisitionPageAction()
	{
		$model   = new Qss_Model_Extra_Purchase();
		$params  = $this->params->requests->getParams();
		$totalPage = ceil(@(int)$model->getRequisition(Qss_Lib_Date::displaytomysql($params['fromDateFilter']),
		Qss_Lib_Date::displaytomysql($params['toDateFilter']))
		/$params['perpageFilter']);

		$data = array('totalPage'=>$totalPage, 'page'=>$params['pageFilter']);
		echo Qss_Json::encode(array('error' => 0,'data'=>$data));
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	public function CreatepoRequisitionDocumentnoAction()
	{
		$model   = new Qss_Model_Extra_Purchase();
		$data = array('docNo'=>Qss_Lib_Extra::getDocumentNo($this->_form->o_fGetObjectByCode('ODonMuaHang')));
		echo Qss_Json::encode(array('error' => 0,'data'=>$data));
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}


	public function CreatepoRequisitionSaveAction()
	{
		$params = $this->params->requests->getParams();
		if ( $this->params->requests->isAjax() )
		{
			$service = $this->services->Extra->Purchase->Createpo->Requisition->Save($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	public function	 CreatequotaRequisitionIndexAction()
	{
		$model            	= new Qss_Model_Extra_Purchase();
		$extra            	= new Qss_Model_Extra_Extra();

		$this->html->docNo  = Qss_Lib_Extra::getDocumentNo($this->_form->o_fGetObjectByCode('OBaoGiaMuaHang'));
		$this->html->partner = $extra->getTable(array('*'),'ODoiTac', array('NhaCungCap'=>1),
		array('MaDoiTac ASC'), 'NO_LIMIT');
	}

	public function	 CreatequotaRequisitionSearchAction()
	{
		$model            	= new Qss_Model_Extra_Purchase();
		$params             = $this->params->requests->getParams();
		$this->html->search = $model->getRequisition(Qss_Lib_Date::displaytomysql($params['fromDateFilter']),
		Qss_Lib_Date::displaytomysql($params['toDateFilter']),
		$params['pageFilter'],
		$params['perpageFilter']);
	}

	public function CreatequotaRequisitionPageAction()
	{
		$model   = new Qss_Model_Extra_Purchase();
		$params  = $this->params->requests->getParams();
		$totalPage = ceil(@(int)$model->getRequisition(Qss_Lib_Date::displaytomysql($params['fromDateFilter']),
		Qss_Lib_Date::displaytomysql($params['toDateFilter']))
		/$params['perpageFilter']);

		$data = array('totalPage'=>$totalPage, 'page'=>$params['pageFilter']);
		echo Qss_Json::encode(array('error' => 0,'data'=>$data));
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	public function CreatequotaRequisitionDocumentnoAction()
	{
		$model   = new Qss_Model_Extra_Purchase();
		$data = array('docNo'=>Qss_Lib_Extra::getDocumentNo($this->_form->o_fGetObjectByCode('OBaoGiaMuaHang')));
		echo Qss_Json::encode(array('error' => 0,'data'=>$data));
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}


	public function CreatequotaRequisitionSaveAction()
	{
		$params = $this->params->requests->getParams();
		if ( $this->params->requests->isAjax() )
		{
			$service = $this->services->Extra->Purchase->Createquota->Requisition->Save($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	/**
	 * Lay nhu cau vat tu de chuyen thanh nhu cau mua hang
	 */
	public function createrequisitionMaterialIndexAction()
	{
        // model
//        $commonModel   = new Qss_Model_Extra_Extra();

        // init
        $ifid   = $this->params->requests->getParam('ifid', 0);
        $mTable = Qss_Model_Db::Table('ODSYeuCauMuaSam');
        $mTable->where(sprintf('IFID_M412 = %1$d', $ifid));
        //		$old  = $commonModel->getDataset(
        //		array(
        //				'select' => '1'
        //				, 'module'=>'ODSYeuCauMuaSam'
        //				, 'where'=> array('IFID_M412'=>$ifid)
        //				, 'return'=>1
        //				)
        //				);

        // tranfer
        $this->html->ifid    = $ifid;
        $this->html->oldData = $mTable->fetchOne()?1:0;
	}

	/**
	 * Lay cac dong chinh nhu cau vat tu de chon nhu cau can xu ly
	 */
	public function createrequisitionMaterialSearchAction()
	{
		// params
		$start = $this->params->requests->getParam('start', array());
		$end   = $this->params->requests->getParam('end', array());
		$ifid  = $this->params->requests->getParam('ifid', 0);

		$this->html->data = $this->getMaterialRequisitionDialBoxData(
		Qss_Lib_Date::displaytomysql($start)
		, Qss_Lib_Date::displaytomysql($end)
		, $ifid);
	}

	/**
	 * Lay nhu cau vat tu de chuyen thanh nhu cau mua hang
	 */
	public function createrequisitionMaterialOldAction()
	{
		$ifid  = $this->params->requests->getParam('ifid', 0);
//		$commonModel   = new Qss_Model_Extra_Extra();
        $mTable = Qss_Model_Db::Table('ODSYeuCauMuaSam');
        $mTable->where(sprintf('IFID_M412 = %1$d', $ifid));
        $mTable->orderby('IOID');

//		$this->html->data = $commonModel->getDataset(
//		array(
//				'module'=>'ODSYeuCauMuaSam'
//				, 'where'=> array('IFID_M412'=>$ifid)
//				, 'order'=>'IOID'
//				)
//				);
        $this->html->data = $mTable->fetchAll();
	}

	/**
	 * Lay nhu cau vat tu de chuyen thanh nhu cau mua hang
	 */
	public function createrequisitionMaterialResultAction()
	{
		// params
		$ioid  = $this->params->requests->getParam('create_requisition_search_dialbox', array());

		// model
		$purchaseModel  = new Qss_Model_Extra_Purchase();
		$warehouseModel = new Qss_Model_Extra_Warehouse();

		// Lay yeu cau mua hang tu yeu cau vat tu
		$this->html->data = $purchaseModel->getMaterialRequisitionDetail($ioid);

		// Lay yeu cau mua hang theo so luong min cua san pham
		$requireBaseMin   = $warehouseModel->getItemHasQtyLessThanMin();
		$this->html->min  = $requireBaseMin;

		// tra ve ton kho theo san pham [Ref_Item]
		$this->html->inventory = $this->getInventoryOfMaterialRequisition($ioid);
	}

	public function createrequisitionMaterialSaveAction()
	{

		$params = $this->params->requests->getParams();
		$params['LinkIOID'] = isset($params['create_requisition_search_dialbox'])?$params['create_requisition_search_dialbox']:array();
		if ( $this->params->requests->isAjax() )
		{
			$service = $this->services->Extra->Purchase->Createrequisition->Material->Save($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	/**
	 * Lay ton kho cho nut "Lay nhu cau vat tu" trong nhu cau mua hang (M716)
	 * @param date $start ngay bat dau (YYYY-mm-dd)
	 * @param date $end ngay ket thuc (YYYY-mm-dd)
	 * @return array Mang ton kho theo mat hang $ret[Ref_Item]
	 */
	private function getInventoryOfMaterialRequisition($ioid)
	{
		// Model
		$purchaseModel = new Qss_Model_Extra_Purchase();

		// Init
		$inventory     = $purchaseModel->getInventoryOfMaterialRequisition($ioid);
		$retval        = array();

		// Get inventory return
		foreach($inventory as $in)
		{
			$retval[@(int)$in->RefItem] = $in->Inventory;
		}
		return $retval;
	}

	private function getMaterialRequisitionDialBoxData($start, $end, $ifid)
	{
		// model
		$purchaseModel = new Qss_Model_Extra_Purchase();

		// Init
		$data          = $purchaseModel->getMaterialRequisitionByTime($start, $end, $ifid);
		$retval        = array();
		$retNum        = 0;

		$exists        = $purchaseModel->getMaterialRequisitionOfPurchaseRequire($ifid);
		$exclude       = array();

		// Get data
		/**
		 * $data[0]['GroupID']   = 123;//0 ko phan group
		 * $data[0]['GroupName'] = ABC;
		 * $data[0]['Dat'][0]['Display'] = ABC;
		 * $data[0]['Dat'][0]['ID']      = 123;
		 * $data[0]['Dat'][0]['Selected']= true/false;
		 */
		foreach($data as $dat)
		{
			$retval[0]['Dat'][$retNum]['Display'] = $dat->DocNo
			.' ('. Qss_Lib_Date::mysqltodisplay($dat->DocDate).')';
			$retval[0]['Dat'][$retNum]['ID']      = $dat->IOID;
			$retval[0]['Dat'][$retNum]['Selected']= $dat->HasExists?TRUE:FALSE;
			$exclude[] = $dat->IOID;
			$retNum++;
		}

		foreach($exists as $dat)
		{
			if(!in_array($dat->IOID, $exclude))
			{
				$retval[0]['Dat'][$retNum]['Display'] = $dat->DocNo
				.' ('. Qss_Lib_Date::mysqltodisplay($dat->DocDate).')';
				$retval[0]['Dat'][$retNum]['ID']      = $dat->IOID;
				$retval[0]['Dat'][$retNum]['Selected']= $dat->HasExists?TRUE:FALSE;
				$retNum++;
			}
		}

		return $retval;
	}
}
?>