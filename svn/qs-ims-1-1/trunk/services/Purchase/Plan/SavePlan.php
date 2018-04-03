<?php
// Sau khi cap nhat ke hoach, cap nhat lai vao session, sau tra lai IOID cua ke hoach bang setStatus
class Qss_Service_Purchase_Plan_SavePlan extends Qss_Lib_Service
{

	public function __doExecute ($params)
	{
        $status = (isset($params['plan_status']) && $params['plan_status'])?$params['plan_status']:1;

        if(!isset($params['itemioid']) || !count($params['itemioid']))
        {
            $this->setError();
            $this->setMessage('Bạn cần chọn ít nhất một mặt hàng để cập nhật kế hoạch!');
        }
        
        if($status == 1 && (!isset($params['OKeHoachMuaSam_NgayYeuCau']) ||  !$params['OKeHoachMuaSam_NgayYeuCau']))
        {
            $this->setError();
            $this->setMessage('Ngày yêu cầu yêu cầu bắt buộc!');
        }
        
        if(isset($params['qty']))
        {
            $AllQtyZero = 1;
            
            foreach($params['qty'] as $qty)
            {
                if($qty > 0)
                {
                    $AllQtyZero = 0;
                }
            }
             
            if($AllQtyZero == 1)
            {
                $this->setError();
                $this->setMessage('Bạn cần có ít nhất mặt hàng có số lượng lớn hơn 0 để lập kế hoạch!');
            }            
        }
        
        if($this->isError())
        {
            return;
        }        
        
        
        $mCommon  = new Qss_Model_Extra_Extra();
        $mPlan    = new Qss_Model_Purchase_Plan();
        $plan     = $mPlan->getPlanByIOID(@(int)$params['planioid']);
        $planifid = $plan?$plan->IFID_M716:0;
        $insert   = array();
        $insert2  = array();
        $i        = 0;


        if($status == 1)
        {
            if($plan)
            {
                $insert['OKeHoachMuaSam'][0]['ioid'] =    $plan->IOID;
                $insert['OKeHoachMuaSam'][0]['ifid'] =    $planifid;
            }


            $insert['OKeHoachMuaSam'][0]['SoPhieu']                  = $params['OKeHoachMuaSam_SoPhieu'];
            $insert['OKeHoachMuaSam'][0]['NgayYeuCau']               = Qss_Lib_Date::displaytomysql($params['OKeHoachMuaSam_NgayYeuCau']);
            $insert['OKeHoachMuaSam'][0]['NoiDung']                  = $params['OKeHoachMuaSam_NoiDung'];
            $insert['OKeHoachMuaSam'][0]['HinhThuc']                 = @(int)$params['OKeHoachMuaSam_HinhThuc'];
            $insert['OKeHoachMuaSam'][0]['ThoiHanCungCap']           = $params['OKeHoachMuaSam_ThoiHanCungCap'];
            $insert['OKeHoachMuaSam'][0]['GhiChu']                   = $params['OKeHoachMuaSam_GhiChu'];
            $insert['OKeHoachMuaSam'][0]['NgayGuiThuMoi']            = Qss_Lib_Date::displaytomysql($params['OKeHoachMuaSam_NgayGuiThuMoi']);
            $insert['OKeHoachMuaSam'][0]['NgayKetThuc']              = Qss_Lib_Date::displaytomysql($params['OKeHoachMuaSam_NgayKetThuc']);
            $insert['OKeHoachMuaSam'][0]['ThoiGianKetThuc']          = $params['OKeHoachMuaSam_ThoiGianKetThuc'];
            $insert['OKeHoachMuaSam'][0]['NgayMo']                   = Qss_Lib_Date::displaytomysql($params['OKeHoachMuaSam_NgayMo']);
            $insert['OKeHoachMuaSam'][0]['ThoiGianMo']               = $params['OKeHoachMuaSam_ThoiGianMo'];
            $insert['OKeHoachMuaSam'][0]['NgayTrinhDuyetKetQua']     = Qss_Lib_Date::displaytomysql($params['OKeHoachMuaSam_NgayTrinhDuyetKetQua']);
            $insert['OKeHoachMuaSam'][0]['ThoiGianTrinhDuyetKetQua'] = $params['OKeHoachMuaSam_ThoiGianTrinhDuyetKetQua'];
            $insert['OKeHoachMuaSam'][0]['DiaDiemGiaoHang']          = $params['OKeHoachMuaSam_DiaDiemGiaoHang'];
            $insert['OKeHoachMuaSam'][0]['NguoiNhan']                = $params['OKeHoachMuaSam_NguoiNhan'];

            if(!$plan)
            {
                foreach ($params['itemioid'] as $itemioid)
                {
                    if($params['qty'][$i] > 0)
                    {
                        $insert['ODSKeHoachMuaSam'][$i]['SoYeuCau']      = (int)$params['requestioid'][$i];
                        $insert['ODSKeHoachMuaSam'][$i]['MaSP']          = (int)$itemioid;
                        $insert['ODSKeHoachMuaSam'][$i]['DonViTinh']     = (int)$params['uomioid'][$i];
                        $insert['ODSKeHoachMuaSam'][$i]['MucDich']       = $params['reason'][$i];
                        $insert['ODSKeHoachMuaSam'][$i]['SoLuongYeuCau'] = $params['qty'][$i];
                    }

                    $i++;
                }
            }
        }


        //echo '<pre>'; print_r($insert); die;
         
         
        if(count($insert))
        {
            // Cập nhật kế hoạch
            if($status == 1)
            {
                $service = $this->services->Form->Manual('M716',  $planifid,  $insert, false);
                if ($service->isError())
                {
                    $this->setError();
                    $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                }
            }

            if(!$this->isError())
            {
                if(!$planifid)
                {
                    $planifid = $service->getData();
                    $plan     = $mPlan->getPlanByIFID($planifid);
                }

                // Cập nhật vào session
                if($plan)
                {
                    $insert2['OPhienXuLyMuaHang'][0]['SoKeHoach']  = (int)$plan->IOID;

                    $service2 = $this->services->Form->Manual('M415',  $params['sessionifid'],  $insert2, false);


                    if ($service2->isError())
                    {
                        $this->setError();
                        $this->setMessage($service2->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                    }
                }
                 

            }
        }

        // Cập nhật vào báo giá
        if(!$this->isError())
        {
            $mObject = new Qss_Model_Object();
            $mObject->v_fInit('OBaoGiaMuaHang', 'M406');
            $insert   = array();
            $items    = array();
            $keepItem = array();
            $i        = 0;
            $j        = 0;
            $k        = 0;


            if(isset($params['supplier']) && count($params['supplier']))
            {
                foreach ($params['itemioid'] as $itemioid)
                {
                    if($params['qty'][$i]> 0)
                    {
                        if(!key_exists($itemioid, $keepItem))
                        {
                            $keepItem[$itemioid]                     = $i;
                            $items[$k]['MaSP']      = (int)$itemioid;
                            $items[$k]['DonViTinh'] = (int)$params['uomioid'][$i];
                            $items[$k]['DonGia']    = 0;
                            $items[$k]['KyThuat']   = (int)1;
                            $items[$k]['ThoiGian']  = 1;
                            $items[$k]['SoLuong']   = $params['qty'][$i];
                            $k++;
                        }
                        else
                        {
                            $items[$keepItem[$itemioid]]['SoLuong']  += $params['qty'][$i];
                        }
                    }
                    $i++;
                }

                // echo '<pre>'; print_r($params); die;

                foreach($params['supplier'] as $supplierIOID)
                {
                    if($plan && isset($params['supplier_saved']) && $params['supplier_saved'][$j] == 0)
                    {
                        $insert['OBaoGiaMuaHang'][0]['SoPhieu']     = Qss_Lib_Extra::getDocumentNo($mObject);
                        $insert['OBaoGiaMuaHang'][0]['MaNCC']       = (int)$supplierIOID;
                        $insert['OBaoGiaMuaHang'][0]['SoKeHoach']   = (int)$plan->IOID;
                        $insert['OBaoGiaMuaHang'][0]['NVMuaHang']   = $params['UserName'];
                        $insert['OBaoGiaMuaHang'][0]['NgayYeuCau']  = date('Y-m-d');
                        $insert['OBaoGiaMuaHang'][0]['NgayBaoGia']  = date('Y-m-d');

                        if(count($items))
                        {
                            $insert['ODSBGMuaHang'] = $items;
                        }

//                        echo '<pre>'; print_r($insert); die;

                        $service3 = $this->services->Form->Manual('M406',  0,  $insert, false);

                        if ($service3->isError())
                        {
                            $this->setError();
                            $this->setMessage($service3->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                        }
                    }

                    $j++;
                }
            }


        }

        if($plan)
        {
            $this->setStatus($plan->IOID);
        }

	}
}
?>