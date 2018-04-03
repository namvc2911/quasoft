<?php
/**
 * Class Qss_Bin_Trigger_OXuatKho
 * @author: Thinh Tuan
 * @created: 11-01-2016 9:51 AM
 * @updated: 27-01-2016 9:54 AM
 */
class Qss_Bin_Trigger_OXuatKho extends Qss_Lib_Trigger
{
	/**
	 * OnUpdate
	 * - Kiểm tra nếu thay đổi khách hàng thì cần nhập lại danh sách
	 */
	public function onUpdate($object)
	{
		parent::init();
        $this->checkWorkOrderNoChangeAvailable($object); // Kiểm tra số phiếu bảo trì thay đổi
		$this->checkPartnerChangeAvailable($object);     // Kiểm tra khách hàng thay đổi
	}

	/**
	 * OnUpdated
	 * Kiểm tra nếu thay đổi số phiếu bảo trì thì cần nhập lại danh sách, nếu không lỗi thì cập nhật ds từ phiếu bt
	 */
	public function onUpdated($object)
	{
		parent::init();
		// $this->insertItemsFromWorkOrder($object); // Cập nhật danh sách tư phiếu bảo trì
	}

	/**
	 * OnInserted
	 * Cập nhật danh sách từ vật tư của phiếu bảo trì
	 */
	public function onInserted($object)
	{
		parent::init();
		// $this->insertItemsFromWorkOrder($object); // Cập nhật danh sách tư phiếu bảo trì
	}

	/**
	 * Cập nhật danh sách từ phiếu bảo trì
	 * @param Qss_Model_Object $object
	 */
	private function insertItemsFromWorkOrder(Qss_Model_Object $object)
	{
		parent::init();
		if(!$this->isError() && Qss_Lib_System::fieldActive('OXuatKho', 'PhieuBaoTri') && @(int)$this->_params->Ref_PhieuBaoTri)
		{
			$insert     = array(); // Mảng dữ liệu cập nhật phiếu bảo trì
			$i          = 0; // Thứ tự đối tượng phụ mảng $insert
			$workorder  = @(int)$this->_params->Ref_PhieuBaoTri; // Ref số phiếu bảo trì
			$mWorkorder = new Qss_Model_Maintenance_Workorder(); // Model phiếu bảo trì
			$materials  = $workorder?$mWorkorder->getMaterials(false, $workorder):array(); // Lấy vật tư theo ref số phiếu bảo trì

			foreach($materials as $item)
			{
				$insert['ODanhSachXuatKho'][$i]['MaSP']      = (int)$item->Ref_MaVatTu;
				$insert['ODanhSachXuatKho'][$i]['DonViTinh'] = (int)$item->Ref_DonViTinh;
				$insert['ODanhSachXuatKho'][$i]['SoLuong']   = $item->SoLuong;
				$insert['ODanhSachXuatKho'][$i]['ThuocTinh'] = (int)$item->Ref_ThuocTinh;
				$i++;
			}

			if(count($insert))
			{
				$service = $this->services->Form->Manual('M506', $object->i_IFID, $insert ,false);

				if($service->isError())
				{
					$this->setError();
					$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
				}
			}
		}
	}

	/**
	 * Kiểm tra số phiếu có bị thay đổi hay không?
	 * @param $object
	 */
	private function checkWorkOrderNoChangeAvailable($object)
	{
		parent::init();
		if(!$this->isError() && Qss_Lib_System::fieldActive('OXuatKho', 'PhieuBaoTri'))
		{
			$oldWorkorder = $object->getFieldByCode('PhieuBaoTri')->intRefIOID; // Số phiếu bảo trì cũ
			$newWorkorder = $this->_params->Ref_PhieuBaoTri; // Số phiếu bảo trì mới nhập vào

			if(count((array)$this->_params->ODanhSachXuatKho) && $oldWorkorder != $newWorkorder)
			{
				$this->setMessage($this->_translate(2)); // Số phiếu bảo trì đã được thay đổi hãy kiểm tra lại danh sách xuất kho
			}
		}
	}

	/**
	 * Kiểm tra xem khách hàng có bị thay đổi hay không?
	 * @param $object
	 */
	private function checkPartnerChangeAvailable($object)
	{
		parent::init();
		if(!$this->isError())
		{
			$oldPartner = $object->getFieldByCode('MaKH')->intRefIOID; // Khách hàng cũ
			$newPartner = $this->_params->Ref_MaKH; // Khách hàng mới nhập vào

			if(count((array)$this->_params->ODanhSachXuatKho) && $oldPartner != $newPartner)
			{
				$this->setError();
				$this->setMessage($this->_translate(1)); // Để thay đổi khách hàng bạn cần xóa tất cả dòng nhập kho
			}
		}
	}
}
?>