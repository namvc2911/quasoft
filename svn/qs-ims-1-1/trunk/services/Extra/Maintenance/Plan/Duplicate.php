<?php
Class Qss_Service_Extra_Maintenance_Plan_Duplicate extends  Qss_Service_Abstract
{
    public function __doExecute($params)
    {
        $common     = new Qss_Model_Extra_Extra();
        $equipModel = new Qss_Model_Maintenance_Equipment();
        
        $wherekh  = array('IFID_M724'=>$params['ifid']);
        $kehoach  = $common->getTableFetchOne('OBaoTriDinhKy', $wherekh);
        $congviec = $common->getTableFetchAll('OCongViecBT', $wherekh);
        $chuky 	  = $common->getTableFetchAll('OChuKyBaoTri', $wherekh);
        $vattu    = $common->getTableFetchAll('OVatTu', $wherekh);
        $dichvu   = $common->getTableFetchAll('ODichVuBT', $wherekh);
        $vitri    = $equipModel->getComponentOfEquip(@(int)$kehoach->Ref_MaThietBi);

        $arrViTri = array();
        $insert   = array();
        
        foreach($vitri as $item)
        {
            $arrViTri[] = $item->ViTri;
        }
        
        if(!$kehoach->BoPhan ||  in_array($kehoach->BoPhan, $arrViTri))
        {
            $insert['OBaoTriDinhKy'][0]['MaThietBi']   = $params['equip_code'];
            $insert['OBaoTriDinhKy'][0]['MaKhuVuc']    = $kehoach->MaKhuVuc;
            $insert['OBaoTriDinhKy'][0]['BoPhan']      = $kehoach->BoPhan;
            $insert['OBaoTriDinhKy'][0]['DVBT']        = $kehoach->DVBT;
            $insert['OBaoTriDinhKy'][0]['BenNgoai']    = $kehoach->BenNgoai;
            $insert['OBaoTriDinhKy'][0]['MucDoUuTien'] = $kehoach->MucDoUuTien;
            $insert['OBaoTriDinhKy'][0]['Ca']          = $kehoach->Ca;
            $insert['OBaoTriDinhKy'][0]['SoNgay']      = $kehoach->SoNgay;
            $insert['OBaoTriDinhKy'][0]['SoPhut']      = $kehoach->SoPhut;
            $insert['OBaoTriDinhKy'][0]['DungMay']     = $kehoach->DungMay;
            $insert['OBaoTriDinhKy'][0]['NgayBatDau']  = $kehoach->NgayBatDau?$kehoach->NgayBatDau:date('Y-m-d');
            $insert['OBaoTriDinhKy'][0]['NgayKetThuc'] = $kehoach->NgayKetThuc;
            $insert['OBaoTriDinhKy'][0]['MoTa']        = $kehoach->MoTa;
        
         	$i = 0;
            foreach($chuky as $item)
            {
                $insert['OChuKyBaoTri'][$i]['CanCu']       		= $item->CanCu; 
                $insert['OChuKyBaoTri'][$i]['KyBaoDuong']     	= $item->KyBaoDuong;  
                $insert['OChuKyBaoTri'][$i]['LapLai']  			= $item->LapLai;  
                $insert['OChuKyBaoTri'][$i]['Thu']      		= $item->Thu;  
                $insert['OChuKyBaoTri'][$i]['Ngay'] 			= $item->Ngay;  
                $insert['OChuKyBaoTri'][$i]['Thang']     		= $item->Thang;   
                $insert['OChuKyBaoTri'][$i]['ChiSo']     		= $item->ChiSo;
                $insert['OChuKyBaoTri'][$i]['GiaTri']     		= $item->GiaTri;
                $insert['OChuKyBaoTri'][$i]['DieuChinhTheoPBT'] = $item->DieuChinhTheoPBT;
                $i++;
            }
            
            $i = 0;
            foreach($congviec as $item)
            {
                if(!$item->Ref_ViTri || in_array($item->ViTri, $arrViTri))
                {
                    $insert['OCongViecBT'][$i]['Ten']       = $item->Ten; 
                    $insert['OCongViecBT'][$i]['ViTri']     = $item->ViTri;  
                    $insert['OCongViecBT'][$i]['ThoiGian']  = $item->ThoiGian;
                    $insert['OCongViecBT'][$i]['NhanCong']  = $item->NhanCong;
                    $insert['OCongViecBT'][$i]['MoTa']      = $item->MoTa;  
                    //$insert['OCongViecBT'][$i]['ThueNgoai'] = $item->ThueNgoai;  
                    $insert['OCongViecBT'][$i]['MaNCC']     = @$item->MaNCC;
                    $i++;
                }
            }
            
            $i = 0;
            foreach($vattu as $item)
            {
                if(!$item->Ref_ViTri || in_array($item->ViTri, $arrViTri))
                {
                    $insert['OVatTu'][$i]['ViTri']     = $item->ViTri;  
                    $insert['OVatTu'][$i]['MaVatTu']   = $item->MaVatTu;  
                    $insert['OVatTu'][$i]['CongViec']   = $item->CongViec;
                    $insert['OVatTu'][$i]['DonViTinh'] = $item->DonViTinh;  
                    $insert['OVatTu'][$i]['SoLuong']   = $item->SoLuong;  
                    $i++;
                }
            }       

        	$i = 0;
            foreach($dichvu as $item)
            {
                $insert['ODichVuBT'][$i]['MaNCC']   = (int)$item->Ref_MaNCC;
                $insert['ODichVuBT'][$i]['DichVu']  = $item->DichVu;
                $insert['ODichVuBT'][$i]['ChiPhi']  = $item->ChiPhi;
                $insert['ODichVuBT'][$i]['GhiChu']  = $item->GhiChu;
                $i++;
            }
            $service = $this->services->Form->Manual('M724', 0 , $insert, false);

            if($service->isError())
            {
                $this->setError();
                $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
            }            
            
            if(!$this->isError())
            {
                //$this->setMessage('Nhân đôi bản ghi thành công!');
                $service->setRedirect('/user/form/edit?ifid='.$service->getData().'&deptid=1');
            }            
        }
    }
}