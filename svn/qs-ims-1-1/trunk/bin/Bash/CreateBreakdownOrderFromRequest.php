<?php
class Qss_Bin_Bash_CreateBreakdownOrderFromRequest extends Qss_Lib_Bin
{
    public function __doExecute()
    {
        /*
        $mOrder   = new Qss_Model_Maintenance_Workorder();
        $mBreak   = new Qss_Model_Maintenance_Breakdown();
        $mRequest = new Qss_Model_Maintenance_Request();
        $ifid     = $this->_params->IFID_M747;
        $request  = $mRequest->getRequestByIFID($ifid);
        $created  = ($request && $request->SoLuongPhieuSuCo > 0)?true:false;
        $priority = $mOrder->getDefaultPriority();
        $type     = $mOrder->getDefaultCorrective();

        if(!$created)
        {
            if($request)
            {
                $insert                                           = array();
                $insert['OPhieuSuCo'][0]['MaThietBi']           = (int)$request->Ref_MaThietBi;
                $insert['OPhieuSuCo'][0]['MucDoUuTien']         = (int)$priority->IOID;
                $insert['OPhieuSuCo'][0]['LoaiBaoTri']          = (int)$type->IOID;
                $insert['OPhieuSuCo'][0]['NgayYeuCau']          = date('Y-m-d');
                $insert['OPhieuSuCo'][0]['NgayBatDau']          = date('Y-m-d');
                $insert['OPhieuSuCo'][0]['NgayDuKienHoanThanh'] = date('Y-m-d');
                $insert['OPhieuSuCo'][0]['SoYeuCau']            = (int)$request->IOID;

                $service = $this->services->Form->Manual('M707',  0,  $insert, false);
                if ($service->isError())
                {
                    $this->setError();
                    $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                }

                if(!$this->isError())
                {
                    $order = $mBreak->getOrderByIFID($service->getData());
                    $this->setMessage('Cập nhật thành công! Phiếu sự cố "'. $order->SoPhieu.'" đã được tạo!');
                }
            }
        }
        else
        {
            $this->setError();

            $msg = 'Yêu cầu đã được tạo phiếu sự cố trước đó! <br/> ';
            $i   = 0;

            foreach($createdIFID as $item)
            {
                $msg .= '<a href="/user/form/edit?ifid='.$item.'&deptid='.$deptid.'" style="color:blue;" target="_blank">Click để xem chi tiết phiếu '.$createdNo[$i].'!</a><br/>';
                $i++;
            }

            $this->setMessage($msg);
        }
        */
    }
}