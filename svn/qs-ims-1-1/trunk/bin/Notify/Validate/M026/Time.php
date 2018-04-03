<?php
class Qss_Bin_Notify_Validate_M026_Time extends Qss_Lib_Notify_Validate
{
	const TITLE ='Tổng hợp ngày công.';

	const TYPE ='SUBSCRIBE';

	public function __doExecute()
	{
		try
		{
			/*$model = new Qss_Model_M321_Main();
			//tải dữ liệu các máy chấm công
			$maychamcong = Qss_Model_Db::Table('OMayChamCong');
			$maychamcong->where(sprintf('TinhTrang = 1'));
			$dataSQl = $maychamcong->fetchAll();
			
			foreach ($dataSQl as $item)
			{
				$model->updateTimesheet($item);
			}*/
			//query từ chấm công, min và max để insert vào bảng công ngày/ phần này nếu có tính ra luôn OT
			//gọi serrvice
			$service= new Qss_Service();
			$service->Button->M026->Calculate();
		}
		catch(Exception $e)
		{
			return $e->getMessage();
		}

		//echo $count;
	}
}
?>