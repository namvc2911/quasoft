<?php
class Qss_Service_Maintenance_WorkOrder_CreateOrderFromRequest extends Qss_Lib_Service
{
    public function __doExecute($params)
    {
        $mOrder   = new Qss_Model_Maintenance_Workorder();
        $mRequest = new Qss_Model_Maintenance_Request();
        $ifid     = $params['ifid'];
        $request  = $mRequest->getRequestByIFID($ifid);
        $created  = ($request && $request->SoLuongPhieuBaoTri > 0)?true:false;
        $priority = $mOrder->getDefaultPriority();
        $type     = $mOrder->getDefaultCorrective();

        if(!$created)
        {
            if($request)
            {
                $insert                                           = array();
                $insert['OPhieuBaoTri'][0]['MaKhuVuc']            = (int)$request->Ref_MaKhuVuc;
                $insert['OPhieuBaoTri'][0]['MaThietBi']           = (int)$request->Ref_MaThietBi;
                $insert['OPhieuBaoTri'][0]['TenThietBi']          = $request->TenThietBi;
                $insert['OPhieuBaoTri'][0]['MucDoUuTien']         = (int)$priority->IOID;
                $insert['OPhieuBaoTri'][0]['LoaiBaoTri']          = (int)$type->IOID;
                $insert['OPhieuBaoTri'][0]['NgayYeuCau']          = date('Y-m-d');
                $insert['OPhieuBaoTri'][0]['NgayBatDauDuKien']          = date('Y-m-d');
                $insert['OPhieuBaoTri'][0]['NgayDuKienHoanThanh'] = date('Y-m-d');
                $insert['OPhieuBaoTri'][0]['PhieuYeuCau']         = (int)$request->IOID;
                $insert['OPhieuBaoTri'][0]['MoTa']         = $request->MoTa;

                $service = $this->services->Form->Manual('M759',  0,  $insert, false);
                if ($service->isError())
                {
                    $this->setError();
                    $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                }

                if(!$this->isError())
                {
                    $order = $mOrder->getOrderByIFID($service->getData());
                    $this->setMessage('Cập nhật thành công! Phiếu bảo trì "'. $order->SoPhieu.'" đã được tạo!');
                }
            }
        }
        else
        {
            $this->setError();
            $this->setMessage('Yêu cầu đã được tạo phiếu bảo trì từ trước!');
        }
    }
}