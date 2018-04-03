<?php
class Qss_Service_Maintenance_WorkOrder_CreateOrders extends Qss_Service_Abstract
{
    /**/
    public function __doExecute ($equipIOIDs, $componentIOIDs = array(), $extData = array())
    {
        // Luu y hai mang equip va component phai tuong ho voi nhau
        // Thiet bi: equipIOID
        // Bo phan: componentIOID
        // Muc do uu tien: trung binh => Lay tu ky
        // Loai bao tri: Sua chua, khong dinh ky => Lay tu phan loai bao tri
        // Ngay yeu cau + ngay bat dau + ngay du kien hoan thanh: Theo ngay hien tai
        // Cac tham so khac tren doi tuong chinh: $extData => Code truong du lieu => du lieu (MaDVBT => 1221)

        $mOrder   = new Qss_Model_Maintenance_Workorder();
        $priority = $mOrder->getDefaultPriority();
        $type     = $mOrder->getDefaultCorrective();
        $comTrue  = count($componentIOIDs)?true:false;
        $i        = 0;
        $insert   = array();

        if(!$priority || !$type || !count($equipIOIDs))
        {
            $this->setMessage('Cập nhật không thành công!');
            $this->setError();
        }

        if(!$this->isError())
        {
            foreach($equipIOIDs as $equip)
            {
                $insert['OPhieuBaoTri'][0]['MaThietBi']           = (int)$equip;
                $insert['OPhieuBaoTri'][0]['MucDoUuTien']         = (int)$priority->IOID;
                $insert['OPhieuBaoTri'][0]['LoaiBaoTri']          = (int)$type->IOID;
                $insert['OPhieuBaoTri'][0]['NgayYeuCau']          = date('Y-m-d');
                $insert['OPhieuBaoTri'][0]['NgayBatDauDuKien']          = date('Y-m-d');
                $insert['OPhieuBaoTri'][0]['NgayDuKienHoanThanh'] = date('Y-m-d');
                $insert['OPhieuBaoTri'][0]['MoTa']         = 'N/A';

                if($comTrue)
                {
                    $insert['OPhieuBaoTri'][0]['BoPhan'] = (int)$componentIOIDs[$i];
                }

                $service = $this->services->Form->Manual('M759',  0,  $insert, false);
                if ($service->isError())
                {
                    $this->setError();
                    $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                }

                $i++;
            }

            if(!$this->isError())
            {
                $order = $mOrder->getOrderByIFID($service->getData());
                $this->setMessage('Cập nhật thành công! Phiếu bảo trì "'. $order->SoPhieu.'" đã được tạo!');
            }
        }
    }
}
?>