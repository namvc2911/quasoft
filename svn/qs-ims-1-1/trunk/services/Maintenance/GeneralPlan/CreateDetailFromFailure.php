<?php
class Qss_Service_Maintenance_GeneralPlan_CreateDetailFromFailure extends Qss_Service_Abstract
{
    /**/
    public function __doExecute ($params)
    {

        $equipIOIDs     = isset($params['Equip'])?$params['Equip']:array();
        $componentIOIDs = isset($params['Component'])?$params['Component']:array();
        $dailyIFIDs     = isset($params['DailyIFID'])?$params['DailyIFID']:array();
        $dailyIOIDs     = isset($params['DailyIFID'])?$params['DailyIOID']:array();

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
                $insert = array();
                $insert['OKeHoachBaoTri'][0]['KeHoachTongThe'] = (int)$params['GeneralPlanIOID'];
                $insert['OKeHoachBaoTri'][0]['MaThietBi']      = (int)$equip;
                $insert['OKeHoachBaoTri'][0]['MucDoUuTien']    = (int)$priority->IOID;
                $insert['OKeHoachBaoTri'][0]['LoaiBaoTri']     = (int)$type->IOID;
                $insert['OKeHoachBaoTri'][0]['NgayBatDau']     = date('Y-m-d');
                $insert['OKeHoachBaoTri'][0]['NgayKetThuc']    = date('Y-m-d');

                if($comTrue)
                {
                    $insert['OKeHoachBaoTri'][0]['BoPhan'] = (int)$componentIOIDs[$i];
                }

                $service = $this->services->Form->Manual('M837',  0,  $insert, false);
                if ($service->isError())
                {
                    $this->setError();
                    $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                }

                if(!$this->isError())
                {
                    $insert = array();
                    $insert['ONhatTrinhThietBi'][0]['TinhTrang'] = (int)2;
                    $insert['ONhatTrinhThietBi'][0]['ioid']      = (int)$dailyIOIDs[$i];

                    // echo '<pre>'; print_r($insert); die;

                    $service = $this->services->Form->Manual('M765',  (int)$dailyIFIDs[$i],  $insert, false);

                    if ($service->isError())
                    {
                        $this->setError();
                        $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                    }
                }

//                if(!$this->setError())
//                {
//                    $order = $mOrder->getOrderByIFID($service->getData());
//                    $this->setMessage('Phiếu bảo trì "'. $order->SoPhieu.'" đã được tạo!');
//                }

                $i++;
            }
        }
    }
}
?>