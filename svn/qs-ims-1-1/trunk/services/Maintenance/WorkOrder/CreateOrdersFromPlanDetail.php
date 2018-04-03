<?php
class Qss_Service_Maintenance_WorkOrder_CreateOrdersFromPlanDetail extends Qss_Service_Abstract
{
    public function __doExecute ($params)
    {
        $planDetailIFID = isset($params['ifid'])?$params['ifid']:0;

        $insert     = array();
        $mCommon    = new Qss_Model_Extra_Extra();
        $main       = $mCommon->getTableFetchOne('OKeHoachBaoTri', sprintf(' IFID_M837 = %1$d ', $planDetailIFID));
        $task       = $mCommon->getTableFetchAll('OCongViecKeHoach', sprintf(' IFID_M837 = %1$d ', $planDetailIFID));
        $material   = $mCommon->getTableFetchAll('OVatTuKeHoach', sprintf(' IFID_M837 = %1$d ', $planDetailIFID));
        $service    = $mCommon->getTableFetchAll('ODichVuKeHoach', sprintf(' IFID_M837 = %1$d ', $planDetailIFID));
        $monitors   = $mCommon->getTableFetchAll('OGiamSatChiTiet', sprintf(' IFID_M837 = %1$d ', $planDetailIFID));
//        $arrDonVi   = array();
//        $object     = new Qss_Model_Object();
//        $object->v_fInit('OPhieuBaoTri', 'M759');
//        $document   = new Qss_Model_Extra_Document($object);
        $mWorkorder = new Qss_Model_Maintenance_Workorder();
//        $fPrefix    = $document->getPrefix();


        if($main)
        {
//            if(!isset($arrDonVi[(int) $main->Ref_MaDVBT]))
//            {
//                $temp = (int) $main->Ref_MaDVBT ? $main->MaDVBT.'.'.$fPrefix : $fPrefix;
//                $document->setPrefix($temp);
//
//                $arrDonVi[(int) $main->Ref_MaDVBT]['Last']   = $document->getLast();
//                $arrDonVi[(int) $main->Ref_MaDVBT]['Prefix'] = $temp;
//
//                $document->setPrefix($fPrefix); // reset
//            }
//
//            $iDocumentNo = ++$arrDonVi[(int)$main->Ref_MaDVBT]['Last'];
//            $docPrefix   = $arrDonVi[(int)$main->Ref_MaDVBT]['Prefix'];
//            $docLen      = $document->getLenth();
//            $documentNo  = $document->writeDocumentNo($iDocumentNo, $docPrefix, $docLen);

//            $document->setLenth(3);
//            $document->setDocField('SoPhieu');
//            $document->setPrefix('#');

            $insert['OPhieuBaoTri'][0]['SoPhieu']             = $mWorkorder->getDocNo();
            $insert['OPhieuBaoTri'][0]['NgayYeuCau']          = $main->NgayBatDau;
            $insert['OPhieuBaoTri'][0]['NgayBatDauDuKien']    = $main->NgayBatDau;
            $insert['OPhieuBaoTri'][0]['NgayDuKienHoanThanh'] = $main->NgayKetThuc;
            $insert['OPhieuBaoTri'][0]['LoaiBaoTri']          = (int) $main->Ref_LoaiBaoTri;
            $insert['OPhieuBaoTri'][0]['MaDVBT']              = (int) $main->Ref_MaDVBT;
            $insert['OPhieuBaoTri'][0]['TenDVBT']             = (int) $main->Ref_MaDVBT;
            $insert['OPhieuBaoTri'][0]['MucDoUuTien']         = $main->MucDoUuTien;
            $insert['OPhieuBaoTri'][0]['MaThietBi']           = (int) $main->Ref_MaThietBi;
            $insert['OPhieuBaoTri'][0]['TenThietBi']          = (int) $main->Ref_MaThietBi;
            $insert['OPhieuBaoTri'][0]['BoPhan']              = (int) $main->Ref_BoPhan;
            $insert['OPhieuBaoTri'][0]['ChuKy']               = (int) $main->Ref_ChuKy;
            $insert['OPhieuBaoTri'][0]['SoKeHoach']           = (int) $main->Ref_KeHoachTongThe;
			$insert['OPhieuBaoTri'][0]['MoTa']                = $main->MoTa;

            $o = 0;
            foreach ($task as $ta)
            {
                $insert['OCongViecBTPBT'][$o]['ViTri']          = (int) $ta->Ref_ViTri;
                $insert['OCongViecBTPBT'][$o]['BoPhan']         = (int) $ta->Ref_ViTri;
                $insert['OCongViecBTPBT'][$o]['Ten']            = (int) $ta->Ref_Ten;
                $insert['OCongViecBTPBT'][$o]['ThoiGianDuKien'] = $ta->ThoiGian;
                $insert['OCongViecBTPBT'][$o]['NhanCong']       = $ta->NhanCong;
                $insert['OCongViecBTPBT'][$o]['ThoiGian']       = $ta->ThoiGian;
                $insert['OCongViecBTPBT'][$o]['MoTa']           = $ta->MoTa;

                if(Qss_Lib_System::fieldActive('OCongViecBT', 'NguoiThucHien'))
                {
                    $insert['OCongViecBTPBT'][$o]['NguoiThucHien'] = $ta->NguoiThucHien;
                }

                $o++;
            }

            $n = 0;
            foreach ($material as $mat)
            {
                $insert['OVatTuPBT'][$n]['HinhThuc']      = @(int) $mat->HinhThuc;
                $insert['OVatTuPBT'][$n]['ViTri']         = (int) $mat->Ref_ViTri;
                $insert['OVatTuPBT'][$n]['BoPhan']        = (int) $mat->Ref_ViTri;
                $insert['OVatTuPBT'][$n]['MaVatTu']       = (int) $mat->Ref_MaVatTu;
                $insert['OVatTuPBT'][$n]['TenVatTu']      = (int) $mat->Ref_MaVatTu;
                $insert['OVatTuPBT'][$n]['DonViTinh']     = (int) $mat->Ref_DonViTinh;
                $insert['OVatTuPBT'][$n]['SoLuongDuKien'] = $mat->SoLuongDuKien;
                $insert['OVatTuPBT'][$n]['SoLuong']       = $mat->SoLuongDuKien;
                $insert['OVatTuPBT'][$n]['CongViec']      = (int) $mat->Ref_CongViec;
                $n++;
            }

            $o = 0;
            foreach ($service as $ta)
            {
                $insert['ODichVuPBT'][$o]['MaNCC']        = (int) $ta->Ref_MaNCC;
                $insert['ODichVuPBT'][$o]['DichVu']       = $ta->DichVu;
                $insert['ODichVuPBT'][$o]['ChiPhiDuKien'] = $ta->ChiPhiDuKien;
                $insert['ODichVuPBT'][$o]['ChiPhi']       = $ta->ChiPhi;
                $insert['ODichVuPBT'][$o]['GhiChu']       = $ta->GhiChu;
                $o++;
            }


            $o = 0;
            foreach ($monitors as $ta)
            {
                $insert['OGiamSatBaoTri'][$o]['DiemDo']   = (int) $ta->Ref_DiemDo;
                $insert['OGiamSatBaoTri'][$o]['BoPhan']   = (int) $ta->Ref_BoPhan;
                $insert['OGiamSatBaoTri'][$o]['ChiSo']    = (int) $ta->Ref_ChiSo;

                $o++;
            }

            $service = $this->services->Form->Manual('M759',  0,  $insert, false);
            if ($service->isError())
            {
                $this->setError();
                $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
            }
        }


    }
}
?>