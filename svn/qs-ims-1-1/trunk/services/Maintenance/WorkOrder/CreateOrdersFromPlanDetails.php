<?php
class Qss_Service_Maintenance_WorkOrder_CreateOrdersFromPlanDetails extends Qss_Service_Abstract
{
	private $lastDocNo;
	
    public function __doExecute ($params)
    {
    	$this->lastDocNo = array();
        // Lưu ý: Nếu không có dòng nào được chọn hoặc không tồn tại ifid hoặc không có ngày bắt đầu thì return;

        $planDetailIFIDs   = isset($params['detail_ifid'])?$params['detail_ifid']:array();
        $mWorkorder        = new Qss_Model_Maintenance_Workorder();

        if(!count($planDetailIFIDs))
        {
            $this->setError();
            $this->setMessage('Cần chọn ít nhất một dòng để tạo phiếu!');
            return;
        }

        $planDetailIFIDsTemp = implode(', ', $planDetailIFIDs);
        $mImport             = new Qss_Model_Import_Form('M759', false, false);

        $insert     = array();
        $mCommon    = new Qss_Model_Extra_Extra();
        $main       = $mCommon->getTableFetchAll('OKeHoachBaoTri', sprintf(' IFID_M837 IN (%1$s) ', $planDetailIFIDsTemp));

        if(!count($main))
        {
            $this->setError();
            $this->setMessage('Phiếu không tồn tại!');
            return;
        }

        $task       = $mCommon->getTableFetchAll('OCongViecKeHoach', sprintf(' IFID_M837 IN (%1$s) ', $planDetailIFIDsTemp));
        $material   = $mCommon->getTableFetchAll('OVatTuKeHoach', sprintf(' IFID_M837 IN (%1$s) ', $planDetailIFIDsTemp));
        $service1   = $mCommon->getTableFetchAll('ODichVuKeHoach', sprintf(' IFID_M837 IN (%1$s) ', $planDetailIFIDsTemp));

        if(Qss_Lib_System::objectInForm('M759', 'OGiamSatChiTiet'))
        {
            $monitors   = $mCommon->getTableFetchAll('OGiamSatChiTiet', sprintf(' IFID_M837 IN (%1$s) ', $planDetailIFIDsTemp));
        }

        $arrDonVi   = array();
        $object     = new Qss_Model_Object();
        $object->v_fInit('OPhieuBaoTri', 'M759');
        $document   = new Qss_Model_Extra_Document($object);
        $i          = 0;
        $tempDate   = array();

        foreach($planDetailIFIDs as $item)
        {
            if(!isset($params['start'][$i]) || !$params['start'][$i])
            {
                $this->setError();
                $this->setMessage('Ngày bắt đầu yêu cầu bắt buộc!');
                return;
            }

            $tempDate[$item]              = new stdClass();
            $tempDate[$item]->NgayBatDau  = Qss_Lib_Date::displaytomysql($params['start'][$i]);
            $tempDate[$item]->NgayKetThuc = Qss_Lib_Date::displaytomysql($params['end'][$i]);
            $i++;
        }


        foreach($main as $item)
        {
            //set prefix nếu có 
	        $customDocNo   = 'Qss_Bin_Custom_M759_Document';
            if(class_exists($customDocNo)) 
            {
		    	$docNoCustomClass = new $customDocNo($mImport->o_fGetMainObject());
                $item->NgayYeuCau = $tempDate[$item->IFID_M837]->NgayBatDau;
		        $prefix = $docNoCustomClass->getPrefix($item);
				if($prefix)
		        {
		        	$document->setPrefix($prefix);
		    	}
		    }
		    //lấy số phiếu
		    $last = (!isset($this->lastDocNo[$document->getPrefix()]))?$document->getLast():$this->lastDocNo[$document->getPrefix()];
		   	$this->lastDocNo[$document->getPrefix()] = ++$last;
            // $documentNo  = $document->writeDocumentNo($iDocumentNo);

            $insert[$item->IFID_M837]['OPhieuBaoTri'][0]['SoPhieu']             = $document->writeDocumentNo($this->lastDocNo[$document->getPrefix()]);
            $insert[$item->IFID_M837]['OPhieuBaoTri'][0]['NgayYeuCau']          = $tempDate[$item->IFID_M837]->NgayBatDau;
            $insert[$item->IFID_M837]['OPhieuBaoTri'][0]['NgayBatDauDuKien']    = $tempDate[$item->IFID_M837]->NgayBatDau;
            $insert[$item->IFID_M837]['OPhieuBaoTri'][0]['NgayDuKienHoanThanh'] = $tempDate[$item->IFID_M837]->NgayKetThuc;
            $insert[$item->IFID_M837]['OPhieuBaoTri'][0]['LoaiBaoTri']          = (int) $item->Ref_LoaiBaoTri;
            $insert[$item->IFID_M837]['OPhieuBaoTri'][0]['MaDVBT']              = (int) $item->Ref_MaDVBT;
            $insert[$item->IFID_M837]['OPhieuBaoTri'][0]['TenDVBT']             = (int) $item->Ref_MaDVBT;
            $insert[$item->IFID_M837]['OPhieuBaoTri'][0]['MucDoUuTien']         = $item->MucDoUuTien;
            $insert[$item->IFID_M837]['OPhieuBaoTri'][0]['MaThietBi']           = (int) $item->Ref_MaThietBi;
            $insert[$item->IFID_M837]['OPhieuBaoTri'][0]['TenThietBi']      	=  $item->TenThietBi;
            $insert[$item->IFID_M837]['OPhieuBaoTri'][0]['Ref_TenThietBi']      = (int) $item->Ref_MaThietBi;
            $insert[$item->IFID_M837]['OPhieuBaoTri'][0]['BoPhan']              = (int) $item->Ref_BoPhan;
            $insert[$item->IFID_M837]['OPhieuBaoTri'][0]['ChuKy']               = (int) $item->Ref_ChuKy;
            $insert[$item->IFID_M837]['OPhieuBaoTri'][0]['SoKeHoach']           = (int) $item->Ref_KeHoachTongThe;
            $insert[$item->IFID_M837]['OPhieuBaoTri'][0]['NgayKeHoach']         = (int) $item->IOID;//$tempDate[$item->IFID_M837]->NgayBatDau;
            $insert[$item->IFID_M837]['OPhieuBaoTri'][0]['MoTa']                = $item->MoTa;
        }

        $o = 0;
        foreach ($task as $ta)
        {
            $insert[$ta->IFID_M837]['OCongViecBTPBT'][$o]['ViTri']          = (int) $ta->Ref_ViTri;
            $insert[$ta->IFID_M837]['OCongViecBTPBT'][$o]['BoPhan']         = (int) $ta->Ref_ViTri;
            $insert[$ta->IFID_M837]['OCongViecBTPBT'][$o]['Ten']            = $ta->Ten;
            $insert[$ta->IFID_M837]['OCongViecBTPBT'][$o]['MoTa']           = $ta->MoTa;
            $insert[$ta->IFID_M837]['OCongViecBTPBT'][$o]['ThoiGianDuKien'] = $ta->ThoiGian?$ta->ThoiGian:0;
            $insert[$ta->IFID_M837]['OCongViecBTPBT'][$o]['ThoiGian']       = $ta->ThoiGian?$ta->ThoiGian:0;
            $insert[$ta->IFID_M837]['OCongViecBTPBT'][$o]['NhanCongDuKien'] = $ta->NhanCong?$ta->NhanCong:0;
            $insert[$ta->IFID_M837]['OCongViecBTPBT'][$o]['NhanCong']       = $ta->NhanCong?$ta->NhanCong:0;

            $insert[$ta->IFID_M837]['OCongViecBTPBT'][$o]['MoTa']           = $ta->MoTa;

            if(Qss_Lib_System::fieldActive('OCongViecBT', 'NguoiThucHien'))
            {
                $insert['OCongViecBTPBT'][$o]['NguoiThucHien'] = $ta->NguoiThucHien;
            }

            $o++;
        }

        $n = 0;
        foreach ($material as $mat)
        {
            $insert[$mat->IFID_M837]['OVatTuPBT'][$n]['HinhThuc']      = @(int) $mat->HinhThuc;
            $insert[$mat->IFID_M837]['OVatTuPBT'][$n]['ViTri']         = (int) $mat->Ref_ViTri;
            $insert[$mat->IFID_M837]['OVatTuPBT'][$n]['BoPhan']        = (int) $mat->Ref_ViTri;
            $insert[$mat->IFID_M837]['OVatTuPBT'][$n]['MaVatTu']       = (int) $mat->Ref_MaVatTu;
            $insert[$mat->IFID_M837]['OVatTuPBT'][$n]['Ref_TenVatTu']  = (int) $mat->Ref_MaVatTu;
            $insert[$mat->IFID_M837]['OVatTuPBT'][$n]['TenVatTu']      = $mat->TenVatTu;
            $insert[$mat->IFID_M837]['OVatTuPBT'][$n]['DonViTinh']     = (int) $mat->Ref_DonViTinh;
            $insert[$mat->IFID_M837]['OVatTuPBT'][$n]['SoLuongDuKien'] = $mat->SoLuongDuKien;
            $insert[$mat->IFID_M837]['OVatTuPBT'][$n]['SoLuong']       = ($mat->SoLuongDuKien != 0)?$mat->SoLuongDuKien:0;
            $insert[$mat->IFID_M837]['OVatTuPBT'][$n]['CongViec']      = $mat->CongViec;
            $n++;
        }


        if(Qss_Lib_System::objectInForm('M759', 'OVatTuPBTDK')) {
            $n = 0;
            foreach ($material as $mat)
            {
                $insert[$mat->IFID_M837]['OVatTuPBTDK'][$n]['HinhThuc']      = @(int) $mat->HinhThuc;
                $insert[$mat->IFID_M837]['OVatTuPBTDK'][$n]['ViTri']         = (int) $mat->Ref_ViTri;
                $insert[$mat->IFID_M837]['OVatTuPBTDK'][$n]['BoPhan']        = (int) $mat->Ref_ViTri;
                $insert[$mat->IFID_M837]['OVatTuPBTDK'][$n]['MaVatTu']       = (int) $mat->Ref_MaVatTu;
                $insert[$mat->IFID_M837]['OVatTuPBTDK'][$n]['Ref_TenVatTu']  = (int) $mat->Ref_MaVatTu;
                $insert[$mat->IFID_M837]['OVatTuPBTDK'][$n]['TenVatTu']      = $mat->TenVatTu;
                $insert[$mat->IFID_M837]['OVatTuPBTDK'][$n]['DonViTinh']     = (int) $mat->Ref_DonViTinh;
                $insert[$mat->IFID_M837]['OVatTuPBTDK'][$n]['SoLuongDuKien'] = $mat->SoLuongDuKien;
                $insert[$mat->IFID_M837]['OVatTuPBTDK'][$n]['SoLuong']       = ($mat->SoLuongDuKien != 0)?$mat->SoLuongDuKien:0;
                $insert[$mat->IFID_M837]['OVatTuPBTDK'][$n]['CongViec']      = $mat->CongViec;
                $n++;
            }
        }


        if(Qss_Lib_System::objectInForm('M759', 'ODichVuPBT')) {
            $o = 0;
            foreach ($service1 as $ta) {
                $insert[$ta->IFID_M837]['ODichVuPBT'][$o]['MaNCC'] = $ta->MaNCC;
                $insert[$ta->IFID_M837]['ODichVuPBT'][$o]['DichVu'] = $ta->DichVu;
                $insert[$ta->IFID_M837]['ODichVuPBT'][$o]['ChiPhiDuKien'] = $ta->ChiPhiDuKien;
                $insert[$ta->IFID_M837]['ODichVuPBT'][$o]['ChiPhi'] = $ta->ChiPhi;
                $insert[$ta->IFID_M837]['ODichVuPBT'][$o]['GhiChu'] = $ta->GhiChu;
                $o++;
            }
        }


        if(Qss_Lib_System::objectInForm('M759', 'OGiamSatChiTiet'))
        {
            $o = 0;
            foreach ($monitors as $ta)
            {
                $insert[$ta->IFID_M837]['OGiamSatBaoTri'][$o]['DiemDo']   = (int) $ta->Ref_DiemDo;
                $insert[$ta->IFID_M837]['OGiamSatBaoTri'][$o]['BoPhan']   = (int) $ta->Ref_BoPhan;
                $insert[$ta->IFID_M837]['OGiamSatBaoTri'][$o]['ChiSo']    = (int) $ta->Ref_ChiSo;

                $o++;
            }
        }

        // echo '<pre>'; print_r($insert); die;

        if(count($insert))
        {
            foreach($insert as $item)
            {
                if(isset($item['OPhieuBaoTri']) && count($item['OPhieuBaoTri']))
                {
                    $mImport->setData($item);
                }

            }
            $mImport->generateSQL();


            // echo '<pre>'; print_r($mImport->getImportRows()); die;

            $errorForm   = (int)$mImport->countFormError();
            $errorObject = (int)$mImport->countObjectError();
            $error       = $errorForm + $errorObject;

            if($error > 0)
            {
                $this->setError();
                $this->setMessage('Cập nhật không thành công!');


                // echo '<pre>'; print_r($mImport->getErrorRows());
//                echo '<pre>'; print_r($mImport->getImportRows());
//                echo '<pre>'; print_r($main);
//                echo '<pre>'; print_r($insert);
            }
        }
    }
}
?>