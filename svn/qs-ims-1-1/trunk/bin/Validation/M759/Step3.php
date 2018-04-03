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

        if($this->_params->Ngay)
        {
            $compareDate  = Qss_Lib_Date::compareTwoDate($this->_params->Ngay, date('Y-m-d') );

            if($compareDate == 1)
            {
                $this->setError();
                $this->setMessage("Ngày hoàn thành thực tế lớn hơn hiện tại!");
            }
        }
	}
}
