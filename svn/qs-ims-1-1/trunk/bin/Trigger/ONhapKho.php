<?php
class Qss_Bin_Trigger_ONhapKho extends Qss_Lib_Trigger
{
	
	/**
	 * - Không được thay đổi đối tác khi có danh sách
	 */
	public function onUpdate($object)
	{
		parent::init();
		$this->checkPartnerChangeAvailable($object);
	}
	
	private function checkPartnerChangeAvailable(Qss_Model_Object $object)
	{
		// Lay doi tac cu va cap nhat, neu bang nhau thi khong lam gi
		// Neu doi tac duoc cap nhat bang mot doi tac khac va co danh sach ko cho thay doi doi tac
        $newPartner = $object->getFieldByCode('MaNCC')->intRefIOID;
        $oldPartner = $this->_params->Ref_MaNCC;
        $oldPartner = $oldPartner?$oldPartner:0;
		
		if(count((array)$this->_params->ODanhSachNhapKho) && $oldPartner != 0 && ($oldPartner != $newPartner))
		{
			$this->setError();
			$this->setMessage($this->_translate(1));
		}
	}
}
?>