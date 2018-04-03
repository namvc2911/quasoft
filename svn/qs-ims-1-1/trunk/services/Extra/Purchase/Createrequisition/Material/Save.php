<?php

class Qss_Service_Extra_Purchase_Createrequisition_Material_Save extends Qss_Service_Abstract
{
	public $purchaseRequireModule = 'M716';
	
	public function __doExecute ($params)
	{		
		if($this->validate($params))
		{
			// Model
//			$commonModel   = new Qss_Model_Extra_Extra();
            $mTable = Qss_Model_Db::Table('OKeHoachMuaSam');
            $mTable->where(sprintf(' IFID_M716 = %1$d ', $purchaseRequireIFID ));
			
			// Init
			$LinkFromIOID        = isset($params['LinkIOID'])?$params['LinkIOID']:array();
			$purchaseRequireIFID = isset($params['ifid'])?$params['ifid']:0;
//			$purchaseRequireLine = $commonModel->getDataset(
//				array(
//					'module'=>'OKeHoachMuaSam',
//					'where'=>array('IFID_M716'=>$purchaseRequireIFID),
//					'return'=>1));
            $purchaseRequireLine = $mTable->fetchOne();
			$purchaseRequireIOID = $purchaseRequireLine?$purchaseRequireLine->IOID:0;

			// Lay du lieu cap nhat
			$insert = $this->getDataInsertToPurchaseRequire($params);
			
			// Xoa ban ghi cu
			$this->deletePurchaseRequire($purchaseRequireIFID);
			
			// Insert du lieu moi
			$this->savePurchaseRequire($purchaseRequireIFID, $insert);
			
			// Khong cap nhat ioidlink voi truong hop cap nhat lai
			if(count($LinkFromIOID))
			{
				// Xoa ioid link cu
				$this->deleteIOIDLinkFromMaterialToPurchaseRequire($purchaseRequireIFID);

				// Save ioid link moi
				$this->saveIOIDLinkFromMaterialToPurchaseRequire($LinkFromIOID, $purchaseRequireIOID);
			}
			
			if(!$this->isError())
			{
				$this->setMessage($this->_translate(6));
			}
		}
		else 
		{
			$this->setError();
		}
	}
	
	private function getDataInsertToPurchaseRequire($params)
	{
		// Init
		$insert        = array();
		$lineNum       = 0;
		$insertNum     = 0;
		$itemCodes     = isset($params['itemCodes'])?$params['itemCodes']:array();
		$itemUOMs      = isset($params['itemUOMs'])?$params['itemUOMs']:array();
		$itemBaseUOMs  = isset($params['itemBaseUOMs'])?$params['itemBaseUOMs']:array();
		$purchaseQtys  = isset($params['purchaseQtys'])?$params['purchaseQtys']:array();
		$requireQtys   = isset($params['requireQtys'])?$params['requireQtys']:array();
		$inventoryQtys = isset($params['inventoryQtys'])?$params['inventoryQtys']:array();
		$minQtys       = isset($params['minQtys'])?$params['minQtys']:array();
		$purposes      = isset($params['purposes'])?$params['purposes']:array();
		$uomConvertRates = isset($params['uomConvertRates'])?$params['uomConvertRates']:array();
		$congDonArr      = array();

		// Get data insert
		foreach($itemCodes as $itemCode)
		{
			// So luong can mua
			$purchaseQty = isset($purchaseQtys[$lineNum])?$purchaseQtys[$lineNum]:0; 
			
			// Lay key cho mang cong don
			$congDonKey = $itemCode;// co the co thuoc tinh

			// Chi cap nhat nhung dong co so luong
			if($purchaseQty > 0)
			{
				// Lay mot phan tu moi cho mang cap nhat
				if(!key_exists($itemCode, $congDonArr))
				{
					// Luu lai vi tri de cong don
					$congDonArr[$congDonKey]['No']      = $lineNum;
					$congDonArr[$congDonKey]['Convert'] = 
						(isset($purchaseQtys[$lineNum]) && isset($uomConvertRates[$lineNum]))
						?($purchaseQtys[$lineNum] * $uomConvertRates[$lineNum]):0;

					// Lay phan tu moi
					$insert['ODSKeHoachMuaSam'][$insertNum]['MaSP']      = $itemCode;
					$insert['ODSKeHoachMuaSam'][$insertNum]['DonViTinh'] = isset($itemUOMs[$lineNum])?$itemUOMs[$lineNum]:'';
					$insert['ODSKeHoachMuaSam'][$insertNum]['MucDich'] = isset($purposes[$lineNum])?$purposes[$lineNum]:'';	
					$insert['ODSKeHoachMuaSam'][$insertNum]['TonKho'] = isset($inventoryQtys[$lineNum])?$inventoryQtys[$lineNum]:0;
					$insert['ODSKeHoachMuaSam'][$insertNum]['DiemDo'] = isset($minQtys[$lineNum])?$minQtys[$lineNum]:0;
					$insert['ODSKeHoachMuaSam'][$insertNum]['NhuCauPhatSinh'] = isset($requireQtys[$lineNum])?$requireQtys[$lineNum]:0;
					$insert['ODSKeHoachMuaSam'][$insertNum]['SoLuongYeuCau'] = isset($purchaseQtys[$lineNum])?$purchaseQtys[$lineNum]:0;					
					$insertNum++;
				}
				// Cong don so luong theo don vi tinh co so
				else
				{
					// Cong tiep vao mang cong don cho phan tu sau do
					$congDonArr[$congDonKey]['Convert'] += (isset($purchaseQtys[$lineNum]) && isset($uomConvertRates[$lineNum]))
						?($purchaseQtys[$lineNum] * $uomConvertRates[$lineNum]):0;

					// Thay doi gia tri cap nhat
					$insert['ODSKeHoachMuaSam'][$congDonArr[$congDonKey]['No']]['SoLuongYeuCau']   = $congDonArr[$congDonKey]['Convert'];
					$insert['ODSKeHoachMuaSam'][$congDonArr[$congDonKey]['No']]['DonViTinh'] = isset($itemBaseUOMs[$lineNum])?$itemBaseUOMs[$lineNum]:'';
				}
			}
			$lineNum++;
		}

		return $insert;
	}	
	
	private function deletePurchaseRequire($purchaseRequireIFID)
	{
		// Model
//		$commonModel   = new Qss_Model_Extra_Extra();
        $mTable = Qss_Model_Db::Table('ODSKeHoachMuaSam');
        $mTable->where(' IFID_M716 = %1$d ', $purchaseRequireIFID);
		
		// Init
		$removeIOIDArr   = array();
//		$oldPurchaseRequireDetail = $commonModel->getDataset(
//			array(
//				'module'=>'ODSKeHoachMuaSam',
//				'where'=>array('IFID_M716'=>$purchaseRequireIFID)));
        $oldPurchaseRequireDetail = $mTable->fetchAll();
		
		// Get IOID Remove
		foreach ($oldPurchaseRequireDetail as $old)
		{
			$removeIOIDArr['ODSKeHoachMuaSam'][] = $old->IOID;
		}
		
		// Remove
		if(!$this->isError() && count($removeIOIDArr))
		{
			$removeService = $this->services->Form->Remove($this->purchaseRequireModule , $purchaseRequireIFID, $removeIOIDArr);
			if($removeService->isError())
			{
				$this->setError();
				$this->setMessage($removeService->getMessage(Qss_Service_Abstract::TYPE_TEXT));
			}	
		}
	}	
	
	private function savePurchaseRequire($purchaseRequireIFID, $insertArr)
	{
		// Insert data
		if(!$this->isError())
		{
			$service = $this->services->Form->Manual($this->purchaseRequireModule , $purchaseRequireIFID, $insertArr, false);
			if($service->isError())
			{
				$this->setError();
				$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
			}	
		}
	}
	
	private function deleteIOIDLinkFromMaterialToPurchaseRequire($purchaseRequireIFID)
	{
		// Model
		$formModel     = new Qss_Model_Form();
		$purchaseModel = new Qss_Model_Extra_Purchase();
		
		// Init
		$oldIOIDLinks  = $purchaseModel->getOldLinkFromMaterialRequireToPurchaseRequire($purchaseRequireIFID);
		
		// Delete old ioid link
		foreach ($oldIOIDLinks as $link)
		{
			$formModel->deleteIOIDLink($link->FromIOID, $link->ToIOID);
		}
	}		

	private function saveIOIDLinkFromMaterialToPurchaseRequire($FromIOID, $purchaseRequireIOID)
	{
		// Model
		$formModel     = new Qss_Model_Form();
		
		// Init
		$lineNum = 0;
		$linkToPurchase = array();
		
		// Lay mot phan tu moi cho mang link
		foreach($FromIOID as $IOID)
		{
			if(!in_array($IOID, $linkToPurchase))
			{
				$linkToPurchase[] =  $IOID;
			}		
			$lineNum++;
		}
		
		// Insert link object
		if(!$this->isError())
		{
			foreach ($linkToPurchase as $l) 
			{
				$formModel->saveIOIDLink($l, $purchaseRequireIOID);
			}
		}
	}


	/**
	 * - Kiem tra so luong co dung ko?
	 * @param type $params
	 * @return boolean
	 */
	private function validate($params)
	{
		$return = true;
		$purchaseQty  = isset($params['purchaseQtys'])
			?$params['purchaseQtys']:array();
		$lineNum   = 0;
		
		if(!count($purchaseQty))
		{
			$this->setMessage($this->_translate(3));
			return false;
		}
		
		foreach($purchaseQty as $qty)
		{
			if(!is_numeric($qty) || $qty < 0)
			{
				$return = FALSE;
				$this->setMessage($this->_translate(1).($lineNum+1).$this->_translate(2));
			}
			$lineNum++;
		}
		
		return $return;
	}
}
?>