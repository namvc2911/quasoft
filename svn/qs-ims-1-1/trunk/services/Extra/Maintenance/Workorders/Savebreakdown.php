<?php
Class Qss_Service_Extra_Maintenance_Workorders_Save extends  Qss_Service_Abstract
{
    public function __doExecute($params)
    {
        if(isset($params['stt']))
        {
            $common = new Qss_Model_Extra_Extra();
            $model  = new Qss_Model_Extra_Maintenance();
            $data   = array();
            $i      = 0;

            foreach ($params['stt'] as $stt)
            {
                $data['OPhieuBaoTri'][0]['SoYeuCau']    = $params['notificationNo'][$i];
                $data['OPhieuBaoTri'][0]['Ngay']        = $params['date'][$i];
                $data['OPhieuBaoTri'][0]['Ca']          = $params['shift'][$i];
                $data['OPhieuBaoTri'][0]['MaThietBi']   = $params['eqCode'][$i];
                $data['OPhieuBaoTri'][0]['MaDVBT']      = $params['workCenter'][$i];
                $data['OPhieuBaoTri'][0]['MucDoUuTien'] = $params['priority'][$i];
                $data['OPhieuBaoTri'][0]['LoaiBaoTri']  = $params['type'][$i];
                $data['OPhieuBaoTri'][0]['ioidlink']    = $params['ioid'][$i];

                
                $service = $this->services->Form->Manual('M759',0,$data,false);
                if($service->isError())
                {
                    $this->setError();
                    $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                }
//                else
//                {
//                    $common->setStatus($params['rifid'], 2);
//                }
            }
        }
        else
        {
            $this->setError();
            $this->setMessage('No notification was selected!');
        }
    }
}