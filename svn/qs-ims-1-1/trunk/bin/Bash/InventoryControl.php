<?php
/**
 * @author: ThinhTuan
 */
class Qss_Bin_Bash_InventoryControl extends Qss_Lib_Bin
{
    /**
     * Thực hiện xóa chi tiết kiểm kê kho có sẵn, sau đó thêm vào tình trạng kho hiện tại.
     */
	public function __doExecute()
	{
		$model        = new Qss_Model_Extra_Warehouse();
		$common       = new Qss_Model_Extra_Extra();
		$newWarehouse = $this->_params->Ref_MaKho;
		$newDate      = $this->_params->Ngay;
        $remove       = array();
		$insert       = array();
		$insertIndex  = 0;
        $removeIndex  = 0;
        $import       = new Qss_Model_Import_Form('M612',false, false);
        $user         = Qss_Register::get('userinfo');
        $dataInsert   = $model->getInventoryStatitics($newWarehouse, $newDate);
        $dataRemove   = $common->getTableFetchAll('OChiTietKiemKe', array('IFID_M612'=>$this->_params->IFID_M612));

        // Lấy kiểm kê kho
        foreach ($dataInsert as $item)
        {
            if($item->ItemCode)
            {
                $insert['OChiTietKiemKe'][$insertIndex]['MaSanPham'] = $item->ItemCode;
                $insert['OChiTietKiemKe'][$insertIndex]['SanPham']   = $item->ItemName;
                $insert['OChiTietKiemKe'][$insertIndex]['SoLo']      = $item->Lot;
                $insert['OChiTietKiemKe'][$insertIndex]['SoSerial']  = $item->Serial;
                $insert['OChiTietKiemKe'][$insertIndex]['DonViTinh'] = $item->ItemUOM;
                $insert['OChiTietKiemKe'][$insertIndex]['ThuocTinh'] = $item->Attribute;
                $insert['OChiTietKiemKe'][$insertIndex]['Bin']       = $item->Bin;
                $insert['OChiTietKiemKe'][$insertIndex]['SoLuong']   = $item->Qty;
                $insert['OChiTietKiemKe'][$insertIndex]['SoLuongTT'] = $item->Qty;
                $insert['OChiTietKiemKe'][$insertIndex]['ifid']      = $this->_params->IFID_M612;
                $insertIndex++;
            }
        }

        // Lấy mảng xóa kiểm kê kho cũ
        foreach ($dataRemove as $item)
        {
            $remove['OChiTietKiemKe'][$removeIndex] = $item->IOID;
            $removeIndex++;
        }

        // Xóa dữ liệu trước đó trước khi chèn kiểm kê kho ban đầu
        if(count($remove))
        {
            $service = $this->services->Form->Remove('M612' , $this->_params->IFID_M612, $remove);
            if($service->isError())
            {
                $this->setError();
                $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
            }
        }

        // Thêm kiểm kê kho ban đầu
        if(count($insert))
        {
            $import->setData($insert);
            $import->generateSQL();
            $error = $import->countFormError() + $import->countObjectError();

            if($error)
            {
                $this->setError();
                $this->setMessage('Có '.$error.' dòng lỗi!');
            }
        }

        // Refresh lại trang
        if(!$this->isError())
        {
            Qss_Service_Abstract::$_redirect = '/user/form/edit?ifid='.$this->_params->IFID_M612.'&deptid='.$user->user_dept_id;
        }
    }
}


