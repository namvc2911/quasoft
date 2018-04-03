<?php
class Qss_Bin_Notify_Validate_M079_Close extends Qss_Lib_Notify_Validate
{
	const TITLE ='Đặt lịch tự động đóng kỳ công.';

	const TYPE ='SUBSCRIBE';

	public function __doExecute()
	{
		try
		{
			$model = new Qss_Model_M339_Main();
			$kycong = $model->getLastPeriod();
			if($kycong)
			{
				//duyệt tất cả bản công ngày
				$sql = sprintf('update qsiforms
								inner join OBangCongTheoNgay on OBangCongTheoNgay.IFID_M026 =  qsiforms.IFID
								 set Status = 2
								 where FormCode="M026"
								 and OBangCongTheoNgay.NgayCong between %1$s and %2$s'
					, $this->_db->quote($kycong->ThoiGianBatDau)
					, $this->_db->quote($kycong->ThoiGianKetThuc));
				$this->_db->execute($sql);
				//duyệt tất cả bảng công tháng
				$sql = sprintf('update qsiforms
								inner join OBangCongTheoKy on OBangCongTheoKy.IFID_M317 =  qsiforms.IFID
								 set Status = 2
								 where FormCode="M317"
								 and OBangCongTheoKy.Ref_KyCong = %1$s'
					, $kycong->IOID);
				$this->_db->execute($sql);
				$form = new Qss_Model_Form();
				$form->initData($kycong->IFID_M339,$kycong->DeptID);
				$form->changeStatus(2);
				//Đóng kỳ công
			}
		}
		catch(Exception $e)
		{
			$this->setError();
			$this->setMessage($e->getMessage());
		}

		//echo $count;
	}
}
?>