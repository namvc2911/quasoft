<?php
Class Qss_Service_Extra_Maintenance_Requestforequip_Saveequipworking extends  Qss_Service_Abstract
{
    public function __doExecute($params)
    {
        if($this->_validateModule($params))
        {

            $ifid      = $params['ifid'];
            $eqCodeArr = (isset($params['eqcode']) && count((array)$params['eqcode']))?$params['eqcode']:array();
            $insert    = array();
            $removeArr = array();
            $i = 0;
            
            
            foreach($eqCodeArr as $item)
            {
                // Chi insert/replace cac ban ghi co tick
                // va con o buoc duoc sua
                if(@(int)$params['tick'][$i])
                {
                    $insert['ODanhSachDieuDongThietBi'][$i]['MaThietBi']    = @(string)$params['eqcode'][$i];
                    $insert['ODanhSachDieuDongThietBi'][$i]['DonViTinh']    = @(string)$params['uom'][$i];

                    /*
                    $insert['ODanhSachDieuDongThietBi'][0]['PhieuYeuCau']  = @(string)$params['docno'][$i];
                    $insert['ODanhSachDieuDongThietBi'][0]['NgayBatDau']   = @(string)$params['start'][$i];
                    $insert['ODanhSachDieuDongThietBi'][0]['NgayKetThuc']  = @(string)$params['end'][$i];
                    $insert['ODanhSachDieuDongThietBi'][0]['MaKhuVuc']     = @(string)$params['location'][$i];
                    $insert['ODanhSachDieuDongThietBi'][0]['LichLamViec']  = @(string)$params['workingcal'][$i];
                    $insert['ODanhSachDieuDongThietBi'][0]['DuAn']         = @(string)$params['project'][$i];
                    */
                    //$insert['OLichThietBi'][0]['NguoiVanHanh'] = @(string)$params['Employee'][$i];
            
                    if(@(int)$params['ewioid'][$i])
                    {
                        $insert['ODanhSachDieuDongThietBi'][$i]['ioid'] = @(string)$params['ewioid'][$i];
                    }
                }

            
                // Xoa cac ban ghi da tao dieu dong nhung khong duoc tick
                // tuy nhien khong xoa cac ban ghi o buoc khong duoc sua
                if(!@(int)$params['tick'][$i] && @(int)$params['ewioid'][$i])
                {
                    $removeArr['ODanhSachDieuDongThietBi'][] = $params['ewioid'][$i];
                }
            
                $i++;
            }


            if(count($removeArr))
            {
                $remove = $this->services->Form->Remove('M706', $ifid , $removeArr, false);

                if($remove->isError())
                {
                    $this->setError();
                    $this->setMessage($remove->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                }
            }


            if(count($insert))
            {
                $service = $this->services->Form->Manual('M706', $ifid , $insert, false);

                if($service->isError())
                {
                    $this->setError();
                    $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                }
            }

        }
        else
        {
            $this->setError();
        }

    }
    
    private function _validateModule($params)
    {
        $return = true;
        
        $eqCodeArr = (isset($params['eqcode']) && count((array)$params['eqcode']))?$params['eqcode']:array();
        
        if(!count($eqCodeArr))
        {
            $return = false;
            $this->setMessage('Bạn chưa chọn dòng nào!');
        }
        
        return $return;
    }
}