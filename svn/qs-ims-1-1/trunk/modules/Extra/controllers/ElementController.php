<?php

class Extra_ElementController extends Qss_Lib_Controller {

	public $_form;

	/**
	 *
	 * @return unknown_type
	 */
	public function init() {
		//$this->i_SecurityLevel = 15;
		parent::init();
	}

	/**
	 * Function: Gan thuoc tinh.
	 * Description: Hien bang gan thuoc tinh.
	 */
	public function attrAction() {
		// get params
		$params = $this->params->requests->getParams();
		$fid = $this->params->requests->getParam('fid', 0);
		$ifid = $this->params->requests->getParam('ifid', 0);
		$ioid = $this->params->requests->getParam('ioid', 0);
		$deptid = $this->params->requests->getParam('deptid', 0);
		$fieldid = $this->params->requests->getParam('fieldid', 0);

		// Init form
		$form = new Qss_Model_Form();
		if ($ifid) {
			$form->initData($ifid, $deptid);
		} else {
			$form->v_fInit($fid, $deptid, $this->_user->user_id);
		}

		// Get field, object, product field, uom field
		$object = $form->o_fGetObjectByCode($field->ObjectCode);
		$field = $object->getFieldByCode($fieldid);

		$productfield = $object->getFieldByCode($field->szTValue); // Field San pham
		$uomfield = $object->getFieldByCode($field->szFValue); // Field San pham
		//echo $uomfield->FieldCode; die;

		// field code
		$element_name = $productfield->ObjectCode . '_' . $productfield->FieldCode;
		$uom_name     = $uomfield->ObjectCode . '_' . $uomfield->FieldCode;


		$productcode   = '';
		$uomRefIOID        = '';
		$selected_name = $field->ObjectCode . '_' . $field->FieldCode;
		$selected      = ''; // IOID của bảng thuộc tính
		$model         = new Qss_Model_Extra_Products();
		$deleteAttributeBl = false;

		// Get product code
		if (isset($params[$element_name])) {
			//get product code
			$vid = explode(',', $params[$element_name]);
			$productcode = $productfield->getDataById($vid[0]);
		} else {
			if ($object->b_Main) {
				//select from view where ifid = ifid
				//get product code
				$productcode = $model->getItemValueByIFID($object->ObjectCode, $object->code, $ifid)->{$field->szTValue};
			} else {
				//select from view where ioid = ioid
				//get product code
				$productcode = $model->getItemValueByIOID($object->ObjectCode, $ioid)->{$field->szTValue};
			}
		}

		 
		// Get uom ref ioid
		if(isset($params[$uom_name]))
		{
			$vid        = explode(',', $params[$uom_name]);
			$uomRefIOID = $vid[1];
		}
		else
		{
			if ($object->b_Main) {
				//select from view where ifid = ifid
				//get product code
				$uomRefIOID = $model->getItemValueByIFID($object->ObjectCode, $object->code, $ifid)->Ref_{$field->szFValue};
			} else {
				//select from view where ioid = ioid
				//get product code
				$uomRefIOID = $model->getItemValueByIOID($object->ObjectCode, $ioid)->Ref_{$field->szFValue};
			}
		}



		// Get attribute ref ioid ~ attr table ioid => find exists line
		if (isset($params[$selected_name])) {
			$vid = explode(',', $params[$selected_name]);
			$selected = $vid[1];
		} else {
			if ($object->b_Main) {
				$selected = $model->getItemValueByIFID($object->ObjectCode, $object->code, $ifid)->{'Ref_' . $field->FieldCode};
			} else {
				//select from view where ioid = ioid
				$selected = $model->getItemValueByIOID($object->ObjectCode, $ioid)->{'Ref_' . $field->FieldCode};
			}
		}




		//echo '<pre>';print_r($selected);
		//echo '<pre>';var_dump($model->checkAttributeAndProductExists($selected));die;


		// Transfer old value
		// $selected ~ attr table ioid
		if ($selected && $model->checkAttributeAndProductExists($selected)) {
			$oldValue = $model->getOldAttributeValues($selected);
			$tmp = array();
			foreach ($oldValue as $v) {
				$tmp['MaThuocTinh']                  = $v->BTTMaThuocTinh;
				$tmp['ThuocTinh'][$v->GTMaThuocTinh] = $v->GTGiaTri;
				$tmp['SoLuong']                      = $v->BTTSoLuong;
			}

			// Neu co thay doi ve san pham va thuoc tinh ko con khop voi thuoc tinh
			$this->html->oldValue = $tmp;
		} else {
			$this->html->oldValue = array();
		}


		if (!$productcode || !$uomRefIOID) {
			//you've not choosen product code or uom
		} else {
			$model     = new Qss_Model_Extra_Products();
			$attribute = $model->getAttributeByItemCode($productcode, $uomRefIOID);
			$uoms      = $model->getUomsByItemCode($productcode);
			$extra     = new Qss_Model_Extra_Extra();
			$old       = array(); /* ioid attr arr */
			$attrVal   = array(); /* attr val arr */
			$arrAttr   = array(); /* item's attr config */
			$uomInfo   = array(); /* uom config */
			$uomVal    = array(); /* uoms val arr */
			/* Note: Hai mang $attrVal, $arrAttr cung key la IOID de lay thong tin cua thuoc tinh, gia tri */

			/* Description: Lay toan bo thuoc tinh cai dat trong cau hinh thuoc tinh (1) */
			foreach ($attribute as $v)
			{

				if (!in_array($v->IOID, $old))/* Lay thong tin thuoc tinh cua tung thuoc tinh */
				{
					// Thong tin cau hinh thuoc tinh
					$arrAttr[$v->IOID]['ThuocTinh']   = $v->ThuocTinh;
					$arrAttr[$v->IOID]['MaThuocTinh'] = $v->MaThuocTinh;
					$arrAttr[$v->IOID]['NhapTuDo']    = $v->NhapTuDo;
					$arrAttr[$v->IOID]['CongThuc']    = $v->CongThuc;
					$arrAttr[$v->IOID]['Checkbox']    = $v->Checkbox;
					$arrAttr[$v->IOID]['BatBuoc']     = $v->BatBuoc;
					$arrAttr[$v->IOID]['HoatDong']    = $v->HoatDong;
					$arrAttr[$v->IOID]['KieuSo']      = $v->KieuSo;
					$arrAttr[$v->IOID]['TTIFID']      = $v->TTIFID;

					// Thong tin cau hinh don vi tinh
					$uomInfo['HeSo']        = $v->ConversionRate;
					$uomInfo['TTSL']        = $v->QtyAttribute;
					$uomInfo['MacDinh']     = $v->uomDefautl;
					$old[] = $v->IOID;
				}

				if ($v->GiaTri) /* Chi luu lai khi co gia tri */
				{
					$attrVal[$v->IOID][] = $v->GiaTri; /* Lay mang gia tri thuoc tinh theo tung thuoc tinh */
				}
				else
				{
					$attrVal[$v->IOID] = array();
				}
				 
			}

			/* End (1) */
			// $attrVal luu lai gia tri
			// $attAttr luu lai config

			// Get all uom vals of item exclude current uom
			$i = 0;
			$hasBaseUOM = false;
			$defaultUOM = array();

			foreach ($uoms as $item)
			{
				$uomVal[$i]['IOID']             = $item->I;
				$uomVal[$i]['DonViTinh']        = $item->DonViTinh;
				$uomVal[$i]['QuyDoi']           = $item->HeSoQuyDoi;
				$uomVal[$i]['MacDinh']          = $item->MacDinh;
				$uomVal[$i]['ThuocTinhSoLuong'] = $item->SuDungTTSL;


				if($item->MacDinh)
				{
					$hasBaseUOM = true;
					$defaultUOM = $uomVal[$i];
				}
				$i++;
			}

			/* Note: Hai mang $attrVal $arrAttr co the gop lai lam mot nhung ko lam vay vi
			 * khong muon lap hai lan de lay gia tri */
			$this->html->attributes = $attrVal; /* Attribute value for select box */
			$this->html->attrs      = $arrAttr; /* Attribute info */
			$this->html->uomInfo    = $uomInfo; /* current uom info */
			$this->html->uoms       = $uomVal; /* another uom infos */
			$this->html->hasBaseUOM = $hasBaseUOM;
			$this->html->defaultUOM = $defaultUOM;
		}

		$this->html->fieldid     = $fieldid;
		$this->html->productcode = $productcode;
		$this->html->uomID       = $uomRefIOID;
		$this->html->params      = $params;
		$this->setLayoutRender(false);
	}

	/* End Function: Gan thuoc tinh. */

	/**
	 * Function: Luu thuoc tinh
	 */
	public function saveAction() {
		$params = $this->params->requests->getParams();

		if ($this->params->requests->isAjax()) {
			$service = $this->services->Extra->Attributes->Save($params);
			echo $service->getMessage();
		}

		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	/* End Function: Luu thuoc tinh */

	/**
	 * Function: Tim kiem thuoc tinh
	 */
	public function searchAction() {
		$model = new Qss_Model_Extra_Products();
		$params = $this->params->requests->getParams();
		$params['attr'] = (is_array($params['attr']) && count($params['attr'])) ? $params['attr'] : array();
		//echo '<pre>'; print_r($model->searchAttributes($params['product'], $params['attrCode'], $params['attr'])); die;
		$attrCode  = ($params['all'] == 1) ? '' : $params['attrCode'];
		$attrArray = ($params['all'] == 1) ? array() : $params['attr'];

		$this->html->search   = $model->searchAttributes($params['product'], $attrCode, $attrArray);
	}

	/* End Function: Tim kiem thuoc tinh */

	public function checkAction()
	{
		$model = new Qss_Model_Extra_Products();
		//$filter      = array('manuLineID'=>$this->_params['lineFilter']);
		///$totalPage   = ceil($this->_production->getProductionRequirementByLine($filter, true)/$this->_params['perpageFilter']);
		$params   = $this->params->requests->getParams();
		$attr     = (isset($params['attr']) && is_array($params['attr']))?$params['attr']:array();
		$itemCode = $params['product'];
		$attrObj  = $model->checkAttrValueExists($attr, $itemCode);
		$attrCode = $attrObj?$attrObj->MaThuocTinh:'';

		$data = array('attrcode'=>$attrCode);
		echo Qss_Json::encode(array('error' => 0,'data'=>$data));

		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
}
