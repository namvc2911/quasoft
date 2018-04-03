<?php
class Qss_Service_Maintenance_WorkOrder_CreateOrderByTask extends Qss_Lib_Service
{
    public function __doExecute($params)
    {
        $mCommon  = new Qss_Model_Extra_Extra();
        $mOrder   = new Qss_Model_Maintenance_Workorder();
        $priority = $mOrder->getDefaultPriority();
        $type     = $mCommon->getTableFetchOne('OCongViecBaoTri');
        $equip    = $mCommon->getTableFetchOne('ODanhSachThietBi', array('IFID_M705'=>$params['OPhieuBaoTri_MaThietBi']));
        $ifid     = ($params['ifid'])?$params['ifid']:0;
        $mEmp     = new Qss_Model_Maintenance_Employee();
        $emp      = $mEmp->getEmployeeByUserID($params['user']->user_id);
        $task     = $mCommon->getTableFetchOne('OCongViecBTPBT', array('IFID_M759'=>$params['ifid']));

        if(!$equip)
        {
            return;
        }

        $insert                                             = array();
        $insert['OPhieuBaoTri'][0]['MaThietBi']             = $equip?$equip->MaThietBi:'';
        $insert['OPhieuBaoTri'][0]['BoPhan']                = (int)$params['OPhieuBaoTri_BoPhan'];
        $insert['OPhieuBaoTri'][0]['MucDoUuTien']           = (int)$priority->IOID;
        $insert['OPhieuBaoTri'][0]['LoaiBaoTri']            = (int)$params['OPhieuBaoTri_LoaiBaoTri'];
        $insert['OPhieuBaoTri'][0]['NgayYeuCau']            = $params['OPhieuBaoTri_NgayBatDau'];
        $insert['OPhieuBaoTri'][0]['NgayBatDauDuKien']      = $params['OPhieuBaoTri_NgayBatDau'];
        $insert['OPhieuBaoTri'][0]['ThoiGianBatDauDuKien']  = $params['OPhieuBaoTri_GioBatDau'];
        $insert['OPhieuBaoTri'][0]['NgayDuKienHoanThanh']   = $params['OPhieuBaoTri_NgayBatDau'];
        $insert['OPhieuBaoTri'][0]['ThoiGianKetThucDuKien'] = $params['OPhieuBaoTri_GioKetThuc'];
        $insert['OPhieuBaoTri'][0]['NgayBatDau']            = $params['OPhieuBaoTri_NgayBatDau'];
        $insert['OPhieuBaoTri'][0]['GioBatDau']             = $params['OPhieuBaoTri_GioBatDau'];
        $insert['OPhieuBaoTri'][0]['Ngay']                  = $params['OPhieuBaoTri_NgayBatDau'];
        $insert['OPhieuBaoTri'][0]['GioKetThuc']            = $params['OPhieuBaoTri_GioKetThuc'];
        $insert['OPhieuBaoTri'][0]['MoTa']                  = $params['OPhieuBaoTri_MoTa'];
        $insert['OPhieuBaoTri'][0]['NguoiThucHien']         = @(int)$emp->IOID;
        $insert['OPhieuBaoTri'][0]['MaDVBT']                = $emp?$emp->MaDonViThucHien:'';


        $insert['OCongViecBTPBT'][0]['MoTa']           = $params['OPhieuBaoTri_MoTa'];
        $insert['OCongViecBTPBT'][0]['ViTri']          = (int)$params['OPhieuBaoTri_BoPhan'];
        $insert['OCongViecBTPBT'][0]['BoPhan']         = (int)$params['OPhieuBaoTri_BoPhan'];
        $insert['OCongViecBTPBT'][0]['NhanCong']       = 1;
        $insert['OCongViecBTPBT'][0]['Ten']            = (int)$type->IOID;
        $insert['OCongViecBTPBT'][0]['NgayDuKien']     = $params['OPhieuBaoTri_NgayBatDau'];
        $insert['OCongViecBTPBT'][0]['ThoiGianBatDauDuKien']     = $params['OPhieuBaoTri_GioBatDau'];
        $insert['OCongViecBTPBT'][0]['ThoiGianKetThucDuKien']     = $params['OPhieuBaoTri_GioKetThuc'];
        $insert['OCongViecBTPBT'][0]['Ngay']     = $params['OPhieuBaoTri_NgayBatDau'];
        $insert['OCongViecBTPBT'][0]['GioBatDau']     = $params['OPhieuBaoTri_GioBatDau'];
        $insert['OCongViecBTPBT'][0]['GioKetThuc']     = $params['OPhieuBaoTri_GioKetThuc'];
        $insert['OCongViecBTPBT'][0]['NguoiThucHien']         = @(int)$emp->IOID;
        $insert['OCongViecBTPBT'][0]['ioid']         = @(int)$task->IOID;
        $insert['OCongViecBTPBT'][0]['ifid']         = (int)$ifid;


        $service = $this->services->Form->Manual('M759',  $ifid,  $insert, false);

        if ($service->isError())
        {
            $this->setError();
            $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
        }

        if(!$this->isError())
        {
            $ifid = $service->getData();
            $form       = new Qss_Model_Form();
            $form->initData($ifid, $params['deptid']);
            $service2 = $this->services->Form->Request($form, 2, $params['user'] , '');


            if ($service2->isError())
            {
                $this->setError();
                $this->setMessage($service2->getMessage(Qss_Service_Abstract::TYPE_TEXT));
            }
        }

        if(!$this->isError()  && (!isset($params['back']) || !$params['back']))
        {
            Qss_Service_Abstract::$_redirect = '/mobile/m759/mytasks/edit?fid=M759&ifid='.$ifid.'&deptid='.$params['deptid'];
        }
    }
}
