<?php

class Qss_Bin_Validation_M759_Step4 extends Qss_Lib_Warehouse_WValidation
{
    public function move()
    {
        parent::init();
        $this->revertStructure();
    }

    /**
     * onNext():
     * - Bắt buộc công việc phải được thực hiện toàn bộ mới cho chuyển bước
     * - Bắt buộc ngày trong công việc phải nằm trong thời gian của phiếu bảo trì
     * - Ngày kết thúc yêu cầu bắt buộc và phải nhỏ hơn ngày hiện tại
     * - Kiểm tra xuất kho có giống với vật tư của phiếu bảo trì hay không? Kiểm tra xem phiếu serial có bắt buộc nhập hay không?
     * - Kiểm tra cập nhật cấu trúc
     */
    public function onNext()
    {
        parent::init();

        // Bắt buộc công việc phải được thực hiện toàn bộ mới cho chuyển bước
        $this->checkAllTasksDone();

        // Bắt buộc ngày trong công việc phải nằm trong thời gian của phiếu bảo trì
        $this->checkDateInTimeOfWorkOrder();

        // Ngày kết thúc yêu cầu bắt buộc và phải nhỏ hơn ngày hiện tại
        $this->checkFinishDate();

        // Kiểm tra xuất kho có giống với vật tư của phiếu bảo trì hay không? Kiểm tra xem phiếu serial có bắt buộc nhập hay không?
        $this->compareWithOutput();

        // Kiểm tra cập nhật cấu trúc
        $this->validateUpdateStructure();
    }

    /**
     * next():
     * - Cập nhật lại vật tư vào cấu trúc thiết bị
     * - Cập nhật lại kế hoạch theo ngày phiếu bảo trì
     * - Cập nhật lại chi phí bảo trì
     * - Cập nhật lại yêu cầu bảo trì tương ứng thành hoàn thành
     */
    public function next()
    {
        parent::init();

        // Cập nhật lại vật tư vào cấu trúc thiết bị
        // $this->updateStructure();

        // Cập nhật lại kế hoạch theo ngày phiếu bảo trì
        $this->updatePlans();

        // Cập nhật lại chi phí bảo trì
        $this->updateCost();

        // Cập nhật lại yêu cầu thành hoàn thành
        $this->updateRequest();
    }


    private function updatePlans()
    {
        $common = new Qss_Model_Extra_Extra();
        $model  = new Qss_Model_Maintenance_Plan();
        $sDate  = $this->_params->Ngay;
        $cSDate = date_create($sDate);
        $day    = (int)$cSDate->format('d');
        $wday   = (int)$cSDate->format('w');
        $month  = (int)$cSDate->format('m');

        if (!$this->isError()) {
            $plans = $model->getAllMaintenanceNeedUpdate($this->_params->Ref_MoTa);
            foreach ($plans as $plan) {
                $update = $plan->DieuChinhTheoPBT;
                if ($update)// && !$plan->CanCu
                {
                    $ioid = $plan->IOID;
                    $ifid = $plan->IFID_M724;
                    $period = $plan->Ky;
                    //update plan
                    $insert = array();
                    $insert['OChuKyBaoTri'][0]['ioid'] = $ioid;
                    $insert['OBaoTriDinhKy'][0]['NgayBatDau'] = $cSDate->format('d-m-Y');
                    switch ($period) {
                        // Hang ngay khong xet
                        case Qss_Lib_Extra_Const::PERIOD_TYPE_WEEKLY:
//                            $thu = $common->getTableFetchOne('OThu', "GiaTri = {$wday}");
//                            if ($thu) {
//                                $insert['OChuKyBaoTri'][0]['Thu'] = (string)$thu->Thu;
//                            }
                            $insert['OChuKyBaoTri'][0]['Thu'] = $wday;
                            break;

                        case Qss_Lib_Extra_Const::PERIOD_TYPE_MONTHLY:
                            $insert['OChuKyBaoTri'][0]['Ngay'] = (string)$day;
                            break;

                        case Qss_Lib_Extra_Const::PERIOD_TYPE_YEARLY:
                            $insert['OChuKyBaoTri'][0]['Ngay'] = (string)$day;
                            $insert['OChuKyBaoTri'][0]['Thang'] = (string)$month;
                            break;
                    }
                    $service = $this->services->Form->Manual('M724', $ifid, $insert, false);
                    if ($service->isError()) {
                        $this->setError();
                        $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                    }
                }
            }
        }

        if (!$this->isError() && Qss_Lib_System::objectInForm('M759', 'OThucHienDinhKy')) {//làm cho thằng nào không nhớ nổi
            $plans = $model->getPreventiveByWorkOrder($this->_params->IFID_M759);
            foreach ($plans as $plan) {
                if ($plan->Chon) {
                    $update = $plan->DieuChinhTheoPBT;
                    if ($update)// && !$plan->CanCu
                    {
                        $ioid = $plan->IOID;
                        $ifid = $plan->IFID_M724;
                        $period = $plan->Ky;
                        //update plan
                        $insert = array();
                        $insert['OChuKyBaoTri'][0]['ioid'] = $ioid;
                        $insert['OBaoTriDinhKy'][0]['NgayBatDau'] = $cSDate->format('d-m-Y');
                        switch ($period) {
                            // Hang ngay khong xet
                            case Qss_Lib_Extra_Const::PERIOD_TYPE_WEEKLY:
//                                $thu = $common->getTableFetchOne('OThu', "GiaTri = {$wday}");
//                                if ($thu) {
//                                    $insert['OChuKyBaoTri'][0]['Thu'] = (string)$thu->Thu;
//                                }
                                $insert['OChuKyBaoTri'][0]['Thu'] = $wday;
                                break;

                            case Qss_Lib_Extra_Const::PERIOD_TYPE_MONTHLY:
                                $insert['OChuKyBaoTri'][0]['Ngay'] = (string)$day;
                                break;

                            case Qss_Lib_Extra_Const::PERIOD_TYPE_YEARLY:
                                $insert['OChuKyBaoTri'][0]['Ngay'] = (string)$day;
                                $insert['OChuKyBaoTri'][0]['Thang'] = (string)$month;
                                break;
                        }
                        $service = $this->services->Form->Manual('M724', $ifid, $insert, false);
                        if ($service->isError()) {
                            $this->setError();
                            $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                        }
                    }
                }
            }
            if (!$this->isError()) {
                $service = $this->services->Maintenance->WorkOrder->Cost->Update($this->_form->i_IFID);
                if ($service->isError()) {
                    $this->setError();
                    $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                }
            }
        }
    }

    private function updateCost()
    {
        if (!$this->isError()) {
            $service = $this->services->Maintenance->WorkOrder->Cost->Update($this->_form->i_IFID);
            if ($service->isError()) {
                $this->setError();
                $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
            }
        }
    }

    private function updateRequest()
    {
        $common = new Qss_Model_Extra_Extra();

        if (!$this->isError()) {
            if($this->_params->Ref_PhieuYeuCau)
            {
                $ifidYeuCau = $common->getTableFetchOne('OYeuCauBaoTri',array('IOID'=>$this->_params->Ref_PhieuYeuCau),array('IFID_M747','DeptID'));
                if($ifidYeuCau)
                {
                    $ycform = new Qss_Model_Form();
                    $ycform->initData($ifidYeuCau->IFID_M747, $ifidYeuCau->DeptID);
                    $service = $this->services->Form->Request($ycform, 3, Qss_Register::get('userinfo'), '');
                    if ($service->isError())
                    {
                        $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                    }
                }
            }
        }
    }

    private function validateUpdateStructure()
    {
        if(is_array($this->_params->OVatTuPBT) && count($this->_params->OVatTuPBT))
        {

            foreach ($this->_params->OVatTuPBT as $item)
            {
                $add      = $item->SoLuong;
                $hinhThuc = (int)$item->HinhThuc;

                if($hinhThuc)//thay bt thi ko can check
                {
                    $sql = sprintf('
                        select OCauTrucThietBi.*
                        from ODanhSachThietBi
                        inner join OCauTrucThietBi on ODanhSachThietBi.IFID_M705 = OCauTrucThietBi.IFID_M705
                        where OCauTrucThietBi.IOID = %1$d',
                        $item->Ref_ViTri);

                    //echo '<pre>'; print_r($sql); die;

                    $dataSQL   = $this->_db->fetchOne($sql);
                    // Bao loi voi thay the
                    if($dataSQL)
                    {
                        //@todo: check cả trường hợp đổi khác mã phải khớp số lượng
                        if($hinhThuc != 4 && $hinhThuc != 0 && $hinhThuc != 6)//Thay thế PT và tháo PT (trừ lắp mới)
                        {
                            if((int)$dataSQL->Ref_MaSP == 0)
                            {
                                $this->setError();
                                $this->setMessage('Vị trí '.$item->ViTri.' chưa được cài đặt mặt hàng trước đó!');
                            }
                            else
                            {
                                if((int)$dataSQL->Ref_MaSP == (int)$item->Ref_MaVatTu)
                                {
                                    if($item->SoLuong > $dataSQL->SoLuongHC)
                                    {
                                        $this->setError();
                                        $this->setMessage('Số lượng thay thế tại vị trí '.$item->ViTri.' khi thay cùng mặt hàng phải nhỏ hơn hoặc bằng số lượng hiện có!');
                                    }
                                }
                                else
                                {
                                    if($item->SoLuong != $dataSQL->SoLuongHC)
                                    {
                                        $this->setError();
                                        $this->setMessage('Số lượng thay thế tại vị trí '.$item->ViTri.' khi thay khác mặt hàng phải bằng với số lượng hiện có!');
                                    }
                                }
                            }
                        }
                        elseif($hinhThuc == 4) // Bao loi voi lap moi
                        {
                            if((int)$dataSQL->Ref_MaSP && (int)$dataSQL->Ref_MaSP != (int)$item->Ref_MaVatTu)
                            {
                                $this->setError();
                                $this->setMessage('Không hỗ trợ nhiều mã vật tư cùng một vị trí, xem vị trí '.$item->ViTri.'!');
                            }
                        }
                    }
                    else
                    {
                        $this->setError();
                        $this->setMessage('Vị trí ' . $item->ViTri . ' không tồn tại!');
                    }
                }
            }
        }
    }

    private function updateStructure()
    {
        if(is_array($this->_params->OVatTuPBT) && count($this->_params->OVatTuPBT))
        {
            foreach ($this->_params->OVatTuPBT as $item)
            {
                $add      = $item->SoLuong;
                $hinhThuc = (int)$item->HinhThuc;
                $mathietbikhac = $item->Ref_MaThietBiKhac;
                $vitrikhac = $item->Ref_ViTriKhac;
                $vitri = $item->Ref_ViTri;
                //update số lượng
                switch ($hinhThuc)
                {
                    case 4://Trừ đi vì lắp mới
                    case 6://Trừ đi vì lắp mới từ tb khác
                        $sql = sprintf('update OCauTrucThietBi
            							set SoLuongHC = ifnull(SoLuongHC,0) + %1$s
            							where IOID = %2$d'
                            ,$add
                            ,$vitri);
                        $this->_db->execute($sql);
                        break;
                    case 5://Cộng vào vì tháo ra
                        $sql = sprintf('update OCauTrucThietBi
            							set SoLuongHC = ifnull(SoLuongHC,0) - %1$s
            							where IOID = %2$d'
                            ,$add
                            ,$vitri);
                        $this->_db->execute($sql);
                        break;
                }
                //cập nhật thiết bị khác
                if($mathietbikhac && $vitrikhac)
                {
                    switch ($hinhThuc)
                    {
                        case 2://Lấy, Trả lại số lượng, ko update mã sp vì lấy thay vào
                        case 6: //Lấy và lắp mới thì trả
                            $sql = sprintf('update OCauTrucThietBi
            							set SoLuongHC = ifnull(SoLuongHC,0) - %1$s
            							where IOID = %2$d'
                                ,$add
                                ,$vitrikhac);
                            $this->_db->execute($sql);
                            //Update thằng hiện tại
                            //update thằng hiện tại theo thằng khác t2
                            $sql = sprintf('update OCauTrucThietBi as t
            							inner join OVatTuPBT as t1 on t1.Ref_ViTri = t.IOID
            							inner join OCauTrucThietBi as t2 on t1.Ref_ViTriKhac = t2.IOID
            							set t.MaSP = t2.MaSP	
            							,t.Ref_MaSP = t2.Ref_MaSP
            							,t.TenSP = t2.TenSP
            							,t.Ref_TenSP = t2.Ref_TenSP
            							,t.DonViTinh = t2.DonViTinh
            							,t.Ref_DonViTinh = t2.Ref_DonViTinh
            							where t1.IOID = %1$d'
                                ,$item->IOID);
                            $this->_db->execute($sql);
                            break;
                        case 3://Chỉ đổi mã sp
                            //update thằng hiện tại theo thằng khác t2
                            $sql = sprintf('update OCauTrucThietBi as t
            							inner join OVatTuPBT as t1 on t1.Ref_ViTri = t.IOID
            							inner join OCauTrucThietBi as t2 on t1.Ref_ViTriKhac = t2.IOID
            							set t.MaSP = t2.MaSP	
            							,t.Ref_MaSP = t2.Ref_MaSP
            							,t.TenSP = t2.TenSP
            							,t.Ref_TenSP = t2.Ref_TenSP
            							,t.DonViTinh = t2.DonViTinh
            							,t.Ref_DonViTinh = t2.Ref_DonViTinh
            							where t1.IOID = %1$d'
                                ,$item->IOID);
                            $this->_db->execute($sql);
                            //update thằng khác
                            $sql = sprintf('update OCauTrucThietBi as t
            							set t.MaSP = %2$s	
            							,t.Ref_MaSP = %3$d
            							,t.TenSP = %4$s
            							,t.Ref_TenSP = %5$d
            							,t.DonViTinh = %6$s
            							,t.Ref_DonViTinh = %7$d
            							where t.IOID = %1$d'
                                ,$vitrikhac
                                ,$this->_db->quote($item->MaVatTu)
                                ,$item->Ref_MaVatTu
                                ,$this->_db->quote($item->TenVatTu)
                                ,$item->Ref_TenVatTu
                                ,$this->_db->quote($item->DonViTinh)
                                ,$item->Ref_DonViTinh
                            );
                            $this->_db->execute($sql);
                            break;
                    }
                }
            }
        }
    }

    /**
     * Khôi phục lại số lượng vật tư trong cấu trúc thiết bị khi chuyển từ bước đóng sang bước khác.
     */
    private function revertStructure()
    {
        if(is_array($this->_params->OVatTuPBT) && count($this->_params->OVatTuPBT))
        {
            foreach ($this->_params->OVatTuPBT as $item)
            {
                $add      = $item->SoLuong;
                $hinhThuc = (int)$item->HinhThuc;
                $mathietbikhac = $item->Ref_MaThietBiKhac;
                $vitrikhac = $item->Ref_ViTriKhac;
                $vitri = $item->Ref_ViTri;
                //update số lượng
                switch ($hinhThuc)
                {
                    case 4://Trừ đi vì lắp mới
                    case 6://Trừ đi vì lắp mới từ tb khác
                        $sql = sprintf('update OCauTrucThietBi
            							set SoLuongHC = ifnull(SoLuongHC,0) - %1$s
            							where IOID = %2$d'
                            ,$add
                            ,$vitri);
                        break;
                        $this->_db->execute($sql);
                    case 5://Cộng vào vì tháo ra
                        $sql = sprintf('update OCauTrucThietBi
            							set SoLuongHC = ifnull(SoLuongHC,0) + %1$s
            							where IOID = %2$d'
                            ,$add
                            ,$vitri);
                        $this->_db->execute($sql);
                        break;
                }
                //cập nhật thiết bị khác
                if($mathietbikhac && $vitrikhac)
                {
                    switch ($hinhThuc)
                    {
                        case 2://Lấy, Trả lại số lượng, ko update mã sp vì lấy thay vào
                        case 6: //Lấy và lắp mới thì trả
                            $sql = sprintf('update OCauTrucThietBi
            							set SoLuongHC = ifnull(SoLuongHC,0) + %1$s
            							where IOID = %2$d'
                                ,$add
                                ,$vitrikhac);
                            $this->_db->execute($sql);
                            //Đổi mã mới
                            //update thằng hiện tại
                            $sql = sprintf('update OCauTrucThietBi as t
            							set t.MaSP = %2$s	
            							,t.Ref_MaSP = %3$d
            							,t.TenSP = %4$s
            							,t.Ref_TenSP = %5$d
            							,t.DonViTinh = %6$s
            							,t.Ref_DonViTinh = %7$d
            							where t.IOID = %1$d'
                                ,$vitri
                                ,$this->_db->quote($item->MaVatTu)
                                ,$item->Ref_MaVatTu
                                ,$this->_db->quote($item->TenVatTu)
                                ,$item->Ref_TenVatTu
                                ,$this->_db->quote($item->DonViTinh)
                                ,$item->Ref_DonViTinh
                            );
                            $this->_db->execute($sql);
                        case 3://Chỉ đổi mã sp
                            //update thằng khác theo thằng hiện tại
                            $sql = sprintf('update OCauTrucThietBi as t
            							inner join OVatTuPBT as t1 on t1.Ref_ViTri = t.IOID
            							inner join OCauTrucThietBi as t2 on t1.Ref_ViTriKhac = t2.IOID
            							set t.MaSP = t2.MaSP	
            							,t.Ref_MaSP = t2.Ref_MaSP
            							,t.TenSP = t2.TenSP
            							,t.Ref_TenSP = t2.Ref_TenSP
            							,t.DonViTinh = t2.DonViTinh
            							,t.Ref_DonViTinh = t2.Ref_DonViTinh
            							where t1.IOID = %1$d'
                                ,$item->IOID);
                            $this->_db->execute($sql);
                            //update thằng hiện tại
                            $sql = sprintf('update OCauTrucThietBi as t
            							set t.MaSP = %2$s	
            							,t.Ref_MaSP = %3$d
            							,t.TenSP = %4$s
            							,t.Ref_TenSP = %5$d
            							,t.DonViTinh = %6$s
            							,t.Ref_DonViTinh = %7$d
            							where t.IOID = %1$d'
                                ,$vitri
                                ,$this->_db->quote($item->MaVatTu)
                                ,$item->Ref_MaVatTu
                                ,$this->_db->quote($item->TenVatTu)
                                ,$item->Ref_TenVatTu
                                ,$this->_db->quote($item->DonViTinh)
                                ,$item->Ref_DonViTinh
                            );
                            $this->_db->execute($sql);
                            break;
                    }
                }
            }
        }
    }

    /**
     * Bắt buộc số lượng vật tư xuất kho phải bằng số lượng sử dụng trong phiếu bảo trì
     */
    private function compareWithOutput()
    {
        if(Qss_Lib_System::formActive('M506'))
        {
            $mInventory = new Qss_Model_Inventory_Inventory();
            $inv        = $mInventory->getInOutOfWorkOrders('', '', 0, 0, 0, 0, $this->_params->IOID);

            foreach($inv as $item)
            {
                $hinhThuc   = (int)$item->HinhThuc;

                if($hinhThuc == 1 && $item->SoLuongThuaThieu != 0)
                {
                    $this->setError();
                    $this->setMessage('Mặt hàng '. $item->MaVatTu . ' ('. $item->DonViTinh.')'. ' có số lượng xuất nhập kho chênh lệch với số lượng sử dụng.');
                }

                if( ($item->QuanLyTheoMa || $item->SerialKhac) && !$item->TrueSerial )
                {
                    $this->setError();
                    $this->setMessage('Mặt hàng '. $item->MaVatTu . ' ('. $item->DonViTinh.')'. ' có serial '.$item->SerialKhac.' không hợp lệ.');
                }
            }
        }
    }

    /**
     * Bắt buộc công việc phải được thực hiện toàn bộ mới cho chuyển bước
     */
    private function checkAllTasksDone()
    {
        if(Qss_Lib_System::fieldActive('OCongViecBTPBT', 'ThucHien'))
        {
            $done = true;

            if(is_array($this->_params->OCongViecBTPBT))
            {
                foreach ($this->_params->OCongViecBTPBT as $item)
                {
                    if(!$item->ThucHien)
                    {
                        $this->setMessage($item->Ten . ' ' .$item->MoTa . ' ' . $this->_translate(6));
                        $done = false;
                    }
                }
            }

            if(!$done)
            {
                $this->setError();
            }
        }
    }

    /**
     * Bắt buộc ngày trong công việc phải nằm trong thời gian của phiếu bảo trì
     */
    private function checkDateInTimeOfWorkOrder()
    {
        $workOrderModel  = new Qss_Model_Maintenance_Workorder();
        $tasksNotInRange = $workOrderModel->getTasksOfWokrOrderNotInOrderRangeTime(
            $this->_form->i_IFID
            , $this->_params->NgayBatDau
            , $this->_params->Ngay);

        if(count($tasksNotInRange))
        {
            $this->setError();
            $this->setMessage('Ít nhất một công việc bảo trì có ngày thực hiện không hợp lệ (Không nằm trong khoảng thời gian bắt đầu kết thúc của phiếu bảo trì).');
        }
    }

    /**
     * Ngày kết thúc yêu cầu bắt buộc và phải nhỏ hơn ngày hiện tại
     */
    private function checkFinishDate()
    {
        if(!$this->_params->Ngay)
        {
            $this->setError();
            $this->setMessage('Ngày hoàn thành thực tế yêu cầu bắt buộc');
        }

        $compareDate  = Qss_Lib_Date::compareTwoDate($this->_params->Ngay, date('Y-m-d') );

        if($compareDate == 1)
        {
            $this->setError();
            $this->setMessage("Ngày hoàn thành thực tế lớn hơn hiện tại!");
        }
    }
}
