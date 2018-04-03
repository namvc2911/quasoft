<?php
class Qss_Service_Maintenance_GeneralPlan_CreateDetailFromPlan extends Qss_Service_Abstract
{
    /**/
    public function __doExecute ($params)
    {
        $plans    = isset($params['refPlan'])?$params['refPlan']:array();
        $mCommon  = new Qss_Model_Extra_Extra();
        $sPlans   = implode(', ', $plans);


        $mLoaiBaoTri = Qss_Model_Db::Table('OPhanLoaiBaoTri');
        $mLoaiBaoTri->where(sprintf('LoaiBaoTri = "%1$s"', Qss_Lib_Extra_Const::MAINT_TYPE_PREVENTIVE));
        $oLoaiBaoTri = $mLoaiBaoTri->fetchOne();

        if(!$oLoaiBaoTri) // Nếu không có loại bảo trì định kỳ cho là lỗi
        {
            $this->setError();
            $this->setMessage('Chưa cài đặt loại bảo trì định kỳ.');
            return;
        }

        if(!$oLoaiBaoTri) // Nếu không có loại bảo trì định kỳ cho là lỗi
        {
            $this->setError();
            $this->setMessage('Chưa cài đặt loại bảo trì định kỳ.');
            return;
        }

        if(!count($plans)) {
            $this->setError();
            $this->setMessage('Cần chọn ít nhất một dòng để tạo phiếu bảo trì.');
            return;
        }


        $main     = $mCommon->getTableFetchAll('OBaoTriDinhKy', sprintf(' IFID_M724 IN (%1$s) ', $sPlans));
        $task     = $mCommon->getTableFetchAll('OCongViecBT', sprintf(' IFID_M724 IN (%1$s) ', $sPlans));
        $material = $mCommon->getTableFetchAll('OVatTu', sprintf(' IFID_M724 IN (%1$s) ', $sPlans));
        $service  = $mCommon->getTableFetchAll('ODichVuBT', sprintf(' IFID_M724 IN (%1$s) ', $sPlans));
        $monitor  = $mCommon->getTableFetchAll('OGiamSatKeHoach', sprintf(' IFID_M724 IN (%1$s) ', $sPlans));
        $i        = 0;
        $aPlans   = array();
        $insert   = array();
        $subDat   = array();
        $link     = array();
        $model    = new Qss_Model_Import_Form('M837',true, false);

        foreach($main as $item)
        {
            $aPlans[$item->IFID_M724] = $item;
        }

        $j = 0;
        foreach($task as $item)
        {
            $subDat[$item->IFID_M724]['OCongViecKeHoach'][$j]['MoTa']            = $item->MoTa;
            $subDat[$item->IFID_M724]['OCongViecKeHoach'][$j]['Ten']             = (int)$item->Ref_Ten;
            $subDat[$item->IFID_M724]['OCongViecKeHoach'][$j]['ViTri']           = (int)$item->Ref_ViTri;
            $subDat[$item->IFID_M724]['OCongViecKeHoach'][$j]['BoPhan']          = (int)$item->Ref_ViTri;
            $subDat[$item->IFID_M724]['OCongViecKeHoach'][$j]['ThoiGianDuKien']  = (int)$item->ThoiGian;
            $subDat[$item->IFID_M724]['OCongViecKeHoach'][$j]['NhanCongDuKien']  = (int)$item->NhanCong;
            $subDat[$item->IFID_M724]['OCongViecKeHoach'][$j]['NguoiThucHien']   = (int)0;
            $subDat[$item->IFID_M724]['OCongViecKeHoach'][$j]['GhiChu']          = '';
            $j++;
        }

        $j = 0;
        foreach($material as $item)
        {
            $subDat[$item->IFID_M724]['OVatTuKeHoach'][$j]['HinhThuc']        = (int)$item->HinhThuc;
            $subDat[$item->IFID_M724]['OVatTuKeHoach'][$j]['ViTri']           = (int)$item->Ref_ViTri;
            $subDat[$item->IFID_M724]['OVatTuKeHoach'][$j]['BoPhan']          = (int)$item->Ref_ViTri;
            $subDat[$item->IFID_M724]['OVatTuKeHoach'][$j]['CongViec']        = $item->CongViec;
            $subDat[$item->IFID_M724]['OVatTuKeHoach'][$j]['MaVatTu']         = (int)$item->Ref_MaVatTu;
            $subDat[$item->IFID_M724]['OVatTuKeHoach'][$j]['TenVatTu']        = (int)$item->Ref_MaVatTu;
            $subDat[$item->IFID_M724]['OVatTuKeHoach'][$j]['DonViTinh']       = (int)$item->Ref_DonViTinh;
            $subDat[$item->IFID_M724]['OVatTuKeHoach'][$j]['DacTinhKyThuat']  = (int)$item->Ref_MaVatTu;
            $subDat[$item->IFID_M724]['OVatTuKeHoach'][$j]['SoLuongDuKien']   = $item->SoLuong;
            $subDat[$item->IFID_M724]['OVatTuKeHoach'][$j]['DonGia']          = 0;
            $j++;
        }
        
        $j = 0;
        foreach($service as $item)
        {
            $subDat[$item->IFID_M724]['ODichVuKeHoach'][$j]['MaNCC']        = (int)$item->Ref_MaNCC;
            $subDat[$item->IFID_M724]['ODichVuKeHoach'][$j]['TenNCC']       = (int)$item->Ref_MaNCC;
            $subDat[$item->IFID_M724]['ODichVuKeHoach'][$j]['DichVu']       = (int)$item->DichVu;
            $subDat[$item->IFID_M724]['ODichVuKeHoach'][$j]['ChiPhi']       = $item->ChiPhi;
            $subDat[$item->IFID_M724]['ODichVuKeHoach'][$j]['ChiPhiDuKien'] = $item->ChiPhi;
            $subDat[$item->IFID_M724]['ODichVuKeHoach'][$j]['GhiChu']       = '';
            $j++;
        }


        $j = 0;
        foreach($monitor as $item)
        {
            $subDat[$item->IFID_M724]['OGiamSatChiTiet'][$j]['DiemDo'] = (int)$item->Ref_DiemDo;
            $subDat[$item->IFID_M724]['OGiamSatChiTiet'][$j]['BoPhan'] = (int)$item->Ref_BoPhan;
            $subDat[$item->IFID_M724]['OGiamSatChiTiet'][$j]['ChiSo']  = (int)$item->Ref_ChiSo;
            $j++;
        }

        foreach ($plans as $plan)
        {
            $numDay  = @(int)$aPlans[$plan]->SoNgay;
            if($numDay > 0)
            {
            	$numDay--;
            }
            $endDate =  date("Y-m-d", strtotime("+ {$numDay} days", strtotime($params['date'][$i])));

            if((int)$params['GeneralPlanIOID'])
            {
            	$insert[$i]['OKeHoachBaoTri'][0]['KeHoachTongThe'] = (int)$params['GeneralPlanIOID'];
            }
            $insert[$i]['OKeHoachBaoTri'][0]['MaThietBi']      = @(int)$aPlans[$plan]->Ref_MaThietBi;
            $insert[$i]['OKeHoachBaoTri'][0]['TenThietBi']     = @(int)$aPlans[$plan]->Ref_TenThietBi;
            $insert[$i]['OKeHoachBaoTri'][0]['BoPhan']         = @(int)$aPlans[$plan]->Ref_BoPhan;
            $insert[$i]['OKeHoachBaoTri'][0]['NgayBatDau']     = $params['date'][$i];
            $insert[$i]['OKeHoachBaoTri'][0]['NgayKetThuc']    = $endDate;
            $insert[$i]['OKeHoachBaoTri'][0]['MucDoUuTien']    = @$aPlans[$plan]->MucDoUuTien;
            $insert[$i]['OKeHoachBaoTri'][0]['LoaiBaoTri']     = (int)$oLoaiBaoTri->IOID;
            $insert[$i]['OKeHoachBaoTri'][0]['ChuKy']          = @(int)$params['period'][$i];
            $insert[$i]['OKeHoachBaoTri'][0]['LanBaoTri']      = 1;
            $insert[$i]['OKeHoachBaoTri'][0]['MaDVBT']         = @(int)$aPlans[$plan]->Ref_DVBT;
            $insert[$i]['OKeHoachBaoTri'][0]['TenDVBT']        = @(int)$aPlans[$plan]->Ref_DVBT;
            $insert[$i]['OKeHoachBaoTri'][0]['NguoiThucHien']  = (int)0;
			$insert[$i]['OKeHoachBaoTri'][0]['MoTa']           = $aPlans[$plan]->MoTa;
			
			$insert[$i]['OKeHoachBaoTri'][0]['Ref_MoTa']           = $aPlans[$plan]->IOID;

            if(isset($subDat[$plan]['OCongViecKeHoach']))
            {
                $insert[$i]['OCongViecKeHoach'] = $subDat[$plan]['OCongViecKeHoach'];
            }

            if(isset($subDat[$plan]['OVatTuKeHoach']))
            {
                $insert[$i]['OVatTuKeHoach'] = $subDat[$plan]['OVatTuKeHoach'];
            }

            if(isset($subDat[$plan]['ODichVuKeHoach']))
            {
                $insert[$i]['ODichVuKeHoach'] = $subDat[$plan]['ODichVuKeHoach'];
            }

            if(isset($subDat[$plan]['OGiamSatChiTiet']))
            {
                $insert[$i]['OGiamSatChiTiet'] = $subDat[$plan]['OGiamSatChiTiet'];
            }
            $i++;
        }

        if(count($insert))
        {
            foreach($insert as $key=>$line)
            {
                $model->setData($line);
            }

            $model->generateSQL();
            $formError   = $model->countFormError();
            $objectError = $model->countObjectError();
            $error       = $formError + $objectError;

            if($error)
            {
            	print_r($model->getErrorRows());
                $this->setError();
                $this->setMessage('Có '.$error.' dòng lỗi!');
            }
        }






//        if(!$this->isError())
//        {
//            foreach($equipIOIDs as $equip)
//            {
//                $insert = array();
//                $insert['OKeHoachBaoTri'][0]['KeHoachTongThe'] = (int)$params['GeneralPlanIOID'];
//                $insert['OKeHoachBaoTri'][0]['MaThietBi']      = (int)$equip;
//                // $insert['OKeHoachBaoTri'][0]['BoPhan']         = (int)$componentIOIDs[$i];
//                $insert['OKeHoachBaoTri'][0]['MucDoUuTien']    = (int)$priority->IOID;
//                $insert['OKeHoachBaoTri'][0]['LoaiBaoTri']     = (int)$type->IOID;
//                $insert['OKeHoachBaoTri'][0]['NgayBatDau']     = date('Y-m-d');
//                $insert['OKeHoachBaoTri'][0]['NgayKetThuc']    = date('Y-m-d');
//
//
//                $service = $this->services->Form->Manual('M837',  0,  $insert, false);
//                if ($service->isError())
//                {
//                    $this->setError();
//                    $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
//                }
//
//                $i++;
//            }
//        }
    }
}
?>