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
		// $this->checkAllTasksDone();

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
		$this->updateStructure();

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
					$period = $plan->KyBaoDuong;
					//update plan
					$insert = array();
					$insert['OChuKyBaoTri'][0]['ioid'] = $ioid;
					$insert['OBaoTriDinhKy'][0]['NgayBatDau'] = $cSDate->format('d-m-Y');
					switch ($period) {
						// Hang ngay khong xet
						case Qss_Lib_Extra_Const::PERIOD_TYPE_WEEKLY:
//							$thu = $common->getTableFetchOne('OThu', "GiaTri = {$wday}");
//							if ($thu) {
//								$insert['OChuKyBaoTri'][0]['Thu'] = (string)$thu->Thu;
//							}

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
//								$thu = $common->getTableFetchOne('OThu', "GiaTri = {$wday}");
//								if ($thu) {
//									$insert['OChuKyBaoTri'][0]['Thu'] = (string)$thu->Thu;
//								}
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
			$phutung = array();

			$sql     = sprintf('select * from ODanhSachThietBi WHERE IOID = %1$d', $this->_params->Ref_MaThietBi);
			$dataSQL = $this->_db->fetchOne($sql);
			$equipIFID = $dataSQL?$dataSQL->IFID_M705:0;

			foreach ($this->_params->OVatTuPBT as $item)
			{
				$add      = $item->SoLuong;
				$hinhThuc = (int)$item->HinhThuc;

				if($add && $hinhThuc != 0)
				{
					$subphutung = array();

					$sql = sprintf('
                        select OCauTrucThietBi.*
                        from ODanhSachThietBi
                        inner join OCauTrucThietBi on ODanhSachThietBi.IFID_M705 = OCauTrucThietBi.IFID_M705
                        where OCauTrucThietBi.IOID = %1$d',
					$item->Ref_ViTri);

					$dataSQL   = $this->_db->fetchOne($sql);

					if($dataSQL)
					{
						$soLuongHC = $dataSQL->SoLuongHC?$dataSQL->SoLuongHC:0;
						if($item->MaVatTu == $dataSQL->MaSP)
						{
							if($hinhThuc == 4 || $hinhThuc == 6) // Lắp mới
							{
								$add += $soLuongHC;
							}
							elseif($hinhThuc == 5)//tháo ra
							{
								$add = $soLuongHC - $add;
							}
						}

					}

					$subphutung['ioid']      = $dataSQL?$dataSQL->IOID:'';
					$subphutung['ViTri']     = $item->ViTri;
					$subphutung['BoPhan']    = $item->BoPhan;
					$subphutung['PhuTung']   = 1;
					$subphutung['SoLuongHC'] = $add;
					$subphutung['TrucThuoc'] = $dataSQL?$dataSQL->Ref_TrucThuoc:'';
					$subphutung['MaSP']      = $item->MaVatTu;
					$subphutung['DonViTinh'] = $item->DonViTinh;
					$subphutung['Serial']    = $item->SerialKhac;
					$phutung[]               = $subphutung;
				}
			}

			if(count($phutung))
			{
				$insert = array();
				$insert['OCauTrucThietBi'] = $phutung;

				$service = $this->services->Form->Manual('M705', $equipIFID, $insert, false);
				if ($service->isError())
				{
					$this->setError();
					$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
				}
			}

            foreach ($this->_params->OVatTuPBT as $item)
            {
                if($hinhThuc == 4 || $hinhThuc == 3 || $hinhThuc == 6 || $hinhThuc == 5)//Cập nhật cấu trúc thiết bị khác
                {
                    $mathietbikhac = $item->Ref_MaThietBiKhac;
                    $vitrikhac = $item->ViTriKhac;
                    if($mathietbikhac && $vitrikhac)
                    {
                        //lấy danh sách thiết bị
                        $sql = sprintf('
                        select tb.IFID_M705,ct.SoLuongHC,ct.IOID
                        from ODanhSachThietBi as tb
                        inner join OCauTrucThietBi as ct on tb.IFID_M705 = ct.IFID_M705
                        where tb.IOID = %1$d and ct.ViTri=%2$s'
                            , $mathietbikhac
                            , $this->_db->quote($vitrikhac));
                        $dataSQL   = $this->_db->fetchOne($sql);
                        if($dataSQL)
                        {
                            if($hinhThuc == 4 || $hinhThuc == 6)
                            {
                                $soluong = $dataSQL->SoLuongHC - $add;
                                $serial = '';
                            }
                            elseif($hinhThuc == 5)//tháo ra
                            {
                                $soluong = $dataSQL->SoLuongHC + $add;
                            }
                            else
                            {
                                $soluong = $dataSQL->SoLuongHC;
                                $serial = $item->Serial;
                            }
                            $update = array();
                            $update['OCauTrucThietBi'] = array(0=>array('ioid'=>$dataSQL->IOID,'SoLuongHC'=>$soluong,'Serial'=>$serial));

                            $service = $this->services->Form->Manual('M705', $dataSQL->IFID_M705, $update, false);
                            if ($service->isError())
                            {
                                $this->setError();
                                $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                            }
                        }
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
            $phutung = array();

            $sql     = sprintf('select * from ODanhSachThietBi WHERE IOID = %1$d', $this->_params->Ref_MaThietBi);
            $dataSQL = $this->_db->fetchOne($sql);
            $equipIFID = $dataSQL?$dataSQL->IFID_M705:0;

            foreach ($this->_params->OVatTuPBT as $item)
            {
                $add      = $item->SoLuong;
                $hinhThuc = (int)$item->HinhThuc;

                if($add && $hinhThuc != 0)
                {
                    $subphutung = array();

                    $sql = sprintf('
                        select OCauTrucThietBi.*
                        from ODanhSachThietBi
                        inner join OCauTrucThietBi on ODanhSachThietBi.IFID_M705 = OCauTrucThietBi.IFID_M705
                        where OCauTrucThietBi.IOID = %1$d',
                        $item->Ref_ViTri);

                    $dataSQL   = $this->_db->fetchOne($sql);

                    if($dataSQL)
                    {
                        $soLuongHC = $dataSQL->SoLuongHC?$dataSQL->SoLuongHC:0;
                        if($item->MaVatTu == $dataSQL->MaSP)
                        {
                            if($hinhThuc == 4 || $hinhThuc == 6) // Lắp mới
                            {
                                $add = $soLuongHC - $add;
                            }
                            elseif($hinhThuc == 5)//tháo ra
                            {
                                $add = $soLuongHC + $add;
                            }
                        }

                    }

                    $subphutung['ioid']      = $dataSQL?$dataSQL->IOID:'';
                    $subphutung['ViTri']     = $item->ViTri;
                    $subphutung['BoPhan']    = $item->BoPhan;
                    $subphutung['PhuTung']   = 1;
                    $subphutung['SoLuongHC'] = $add;
                    $subphutung['TrucThuoc'] = $dataSQL?$dataSQL->Ref_TrucThuoc:'';
                    $subphutung['MaSP']      = $item->MaVatTu;
                    $subphutung['DonViTinh'] = $item->DonViTinh;
                    $subphutung['Serial']    = $item->SerialKhac;
                    $phutung[]               = $subphutung;
                }
            }

            if(count($phutung))
            {
                $insert = array();
                $insert['OCauTrucThietBi'] = $phutung;

                $service = $this->services->Form->Manual('M705', $equipIFID, $insert, false);
                if ($service->isError())
                {
                    $this->setError();
                    $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                }
            }

            foreach ($this->_params->OVatTuPBT as $item)
            {
                if($hinhThuc == 4 || $hinhThuc == 3 || $hinhThuc == 6 || $hinhThuc == 5)//Cập nhật cấu trúc thiết bị khác
                {
                    $mathietbikhac = $item->Ref_MaThietBiKhac;
                    $vitrikhac = $item->ViTriKhac;
                    if($mathietbikhac && $vitrikhac)
                    {
                        //lấy danh sách thiết bị
                        $sql = sprintf('
                        select tb.IFID_M705,ct.SoLuongHC,ct.IOID
                        from ODanhSachThietBi as tb
                        inner join OCauTrucThietBi as ct on tb.IFID_M705 = ct.IFID_M705
                        where tb.IOID = %1$d and ct.ViTri=%2$s'
                            , $mathietbikhac
                            , $this->_db->quote($vitrikhac));
                        $dataSQL   = $this->_db->fetchOne($sql);
                        if($dataSQL)
                        {
                            if($hinhThuc == 4 || $hinhThuc == 6)
                            {
                                $soluong = $dataSQL->SoLuongHC + $add;
                            }
                            elseif($hinhThuc == 5)//tháo ra
                            {
                                $soluong = $dataSQL->SoLuongHC - $add;
                            }
                            else
                            {
                                $soluong = $dataSQL->SoLuongHC;
                            }
                            $update = array();
                            $update['OCauTrucThietBi'] = array(0=>array('ioid'=>$dataSQL->IOID,'SoLuongHC'=>$soluong));

                            $service = $this->services->Form->Manual('M705', $dataSQL->IFID_M705, $update, false);
                            if ($service->isError())
                            {
                                $this->setError();
                                $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                            }
                        }
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
