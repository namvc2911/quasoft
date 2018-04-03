<?php
class Qss_Bin_Trigger_ONhanHang extends Qss_Lib_Trigger
{
    /**
     * onInserted: Chèn danh sách đơn hàng khi thêm mới.
     */
    public function onInserted($object)
    {
        parent::init();

//        if(!$this->isError())
//        {
//            $this->insertPurchaseOrder($object);
//        }
    }

    /**
     * onUpdate: Kiểm tra danh sách nhận hàng đã có hay chưa để thêm danh sách đơn hàng khi đã update
     */
    public function onUpdate($object)
    {
        parent::init();

        if(!$this->isError())
        {
            $this->checkItemsExists($object);
        }
    }

    /**
     * onUpdated: Chèn danh sách đơn hàng khi cập nhật lại.
     */
    public function onUpdated($object)
    {
        parent::init();

//        if(!$this->isError())
//        {
//            $this->insertPurchaseOrder($object);
//        }
    }

    /**
     * Chèn danh sách đơn  mua hàng vào trong nhận hàng
     * @param $object
     */
//    private function insertPurchaseOrder($object)
//    {
//        $mOrder = new Qss_Model_Purchase_Order();
//        $lines  = $mOrder->getOrderLineByIOID($this->_params->Ref_PO);
//        $ifid   = $this->_params->IFID_M408;
//        $insert = array();
//        $i      = 0;
//
//        foreach($lines as $item)
//        {
//            $insert['ODanhSachNhanHang'][$i]['SoYeuCau']  = (int)$item->Ref_SoYeuCau;
//            $insert['ODanhSachNhanHang'][$i]['MaMatHang'] = (int)$item->Ref_MaSP;
//            $insert['ODanhSachNhanHang'][$i]['DonViTinh'] = (int)$item->Ref_DonViTinh;
//            $insert['ODanhSachNhanHang'][$i]['SoLuong']   = $item->SoLuong;
//            $i++;
//        }
//
//        if(!$this->isError() && count($insert) && $ifid)
//        {
//            $service = $this->services->Form->Manual('M408',  $ifid,  $insert, false);
//
//            if ($service->isError())
//            {
//                $this->setError();
//                $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
//            }
//        }
//    }

    /**
     * Báo lỗi chuyển số đơn hàng nếu đã có danh sách nhận hàng
     * @param $object
     */
    private function checkItemsExists(Qss_Model_Object $object)
    {
        $new = (int)$this->_params->Ref_PO;
        $old = (int)$object->getFieldByCode('PO')->intRefIOID;

        if(count($this->_params->ODanhSachNhanHang) && $new != $old)
        {
            $this->setMessage('Bạn phải xóa toàn bộ danh sách nhận hàng để thay đổi số đơn hàng!');
            $this->setError();
        }
    }
}