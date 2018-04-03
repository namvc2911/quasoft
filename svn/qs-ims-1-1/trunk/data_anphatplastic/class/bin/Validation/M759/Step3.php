<?php

class Qss_Bin_Validation_M759_Step3 extends Qss_Lib_Warehouse_WValidation
{
    /**
     * onNext()
     * - Bắt buộc công việc phải được thực hiện toàn bộ mới cho chuyển bước
     * - Bắt buộc ngày trong công việc phải nằm trong thời gian của phiếu bảo trì
     * - Ngày kết thúc yêu cầu bắt buộc và phải nhỏ hơn ngày hiện tại
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
    }

    /**
     * next():
     * - Cập nhật chi phí bảo trì
     * - Tạo phiếu xuất kho trực ca cho riêng An Phát
     */
    public function next()
    {
        parent::init();

        if(!$this->isError())
        {
            $service = $this->services->Maintenance->WorkOrder->Cost->Update($this->_form->i_IFID);
            if ($service->isError())
            {
                $this->setError();
                $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
            }
        }

        if(!$this->isError())
        {
            $this->createOutput();
        }
    }

    /**
     * Bắt buộc ngày trong công việc phải nằm trong thời gian của phiếu bảo trì
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

    /**
     * Tạo phiếu xuất kho cho trực ca
     * @todo: Thiếu kho trong chi tiết vật tư của An Phát
     */
    private function createOutput()
    {
        if(!$this->isError())
        {
            $user    = Qss_Register::get('userinfo');
            $ioid    = $this->_params->IOID;
            $newIFID = 0;
            $mStock  = new Qss_Model_Warehouse_Main();
            $mInout  = new Qss_Model_Warehouse_Inout();
            $import  = new Qss_Model_Import_Form('M506',false, false);

            $dataKho         = $mStock->getStockByUser($user->user_id);
            $dataLoaiXuatKho = $mStock->getInputTypeByCode(Qss_Lib_Extra_Const::OUTPUT_TYPE_MAINTAIN);
            $dataPhieuBaoTri = $mInout->getOutputByWorkorder($this->_params->IOID);

            if($dataKho && $dataLoaiXuatKho && !$dataPhieuBaoTri)
            {
                // Main Obj
                $insert['OXuatKho'][0]['SoChungTu']      = $mStock->getOutputNoByConfig();
                $insert['OXuatKho'][0]['Kho']            = (int) $dataKho->Ref_KhoVatTu;
                $insert['OXuatKho'][0]['LoaiXuatKho']    = (int) $dataLoaiXuatKho->IOID;
                $insert['OXuatKho'][0]['NgayChungTu']    = $this->_params->NgayBatDau;
                $insert['OXuatKho'][0]['NgayChuyenHang'] = $this->_params->NgayBatDau;
                $insert['OXuatKho'][0]['PhieuBaoTri']    = (int)$this->_params->IOID;
                $insert['OXuatKho'][0]['DonViThucHien']  = (int)$this->_params->Ref_MaDVBT;

                $i = 0;
                foreach ($this->_params->OVatTuPBT as $item)
                {
                    $insert['ODanhSachXuatKho'][$i]['MaSP']      = (int) $item->Ref_MaVatTu;
                    $insert['ODanhSachXuatKho'][$i]['DonViTinh'] = (int) $item->Ref_DonViTinh;
                    $insert['ODanhSachXuatKho'][$i]['SoLuong']   = $item->SoLuongDuKien;
                    $i++;
                }

                $import->setData($insert);
                $import->generateSQL();
                $error = $import->countFormError() + $import->countObjectError();

                if($error)
                {
                    $this->setError();
                    $this->setMessage('Không thể tạo phiếu xuất kho, có '.$error.' dòng lỗi!');
                }

                /*if(count(@(array)$insert['ODanhSachXuatKho']) && !$this->isError())
                {
                    $importedRow = $import->getIFIDs();

                    foreach ($importedRow as $item) {
                        $newIFID = $item->oldIFID;
                    }

                    if($newIFID)//@todo Tính toán lại bước này, sẽ chỉ để phiếu tình trạng soạn thảo và người có trách nhiệm đóng phiếu, vậy thì người duyệt dược đóng phiếu mà không cần buowcs chờ duyệt=> Phải phân quyền được nhóm nào được chuyển đến bước nào .
                    {
                        // Chuyển đến yêu cầu duyệt
                        $form = new Qss_Model_Form();
                        $form->initData($newIFID, $user->user_dept_id);
                        $service2 = $this->services->Form->Request($form, 4, $user , '');

                        if ($service2->isError())
                        {
                            //$this->setError();
                            $this->setMessage($service2->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                        }

                        // Duyệt toàn bộ các duyệt
                        $form = new Qss_Model_Form();
                        $form->initData($newIFID, $user->user_dept_id);

                        $approves = $form->getApproveByStep('M506', 4);

                        foreach ($approves as $item)
                        {
                            $form->approve($item->SAID, $user->user_id);
                        }

                        // Chuyển đến bước đã duyệt
                        $form = new Qss_Model_Form();
                        $form->initData($newIFID, $user->user_dept_id);

                        $service3 = $this->services->Form->Request($form, 5, $user , '');

                        if ($service3->isError())
                        {
                            //$this->setError();
                            $this->setMessage($service3->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                        }
						
                        // Chuyển đến bước xuất kho
                        $form = new Qss_Model_Form();
                        $form->initData($newIFID, $user->user_dept_id);

                        $service4 = $this->services->Form->Request($form, 2, $user , '');

                        if ($service4->isError())
                        {
                            //$this->setError();
                            $this->setMessage($service4->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                        }
                    }
                }*/
            }
        }

    }
}

