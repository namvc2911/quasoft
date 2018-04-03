<?php
class Qss_Bin_Notify_Validate_M077_Approve extends Qss_Lib_Notify_Validate
{
	const TITLE ='Đặt lịch tự động duyệt nghỉ phép.';

	const TYPE ='SUBSCRIBE';

	public function __doExecute()
	{
		try
		{
			$retval = '';
			//lấy tất cả đăng ký nghỉ phép cho phép duyệt tự động
			$sql= sprintf('select * from ODangKyNghi
							inner join qsiforms on qsiforms.IFID = ODangKyNghi.IFID_M077  
							inner join OPhanLoaiNghi on OPhanLoaiNghi.IOID = ODangKyNghi.Ref_LoaiNgayNghi
							where qsiforms.Status = 1 and ifnull(OPhanLoaiNghi.TuDongDuyet,0) = 1');
			$dataSQL = $this->_db->fetchAll();
			foreach ($dataSQL as $item)
			{
				$service = new Qss_Service();
				$form = new Qss_Model_Form();
				$form->initData($dataSQL->IFID, $dataSQL->DepartmentID);
				$service = $this->services->Form->Request($form, 1, $this->_user, 'Auto approve');
				if($service->isError())
				{
					$this->setError();
					$this->setMessage($service->getMessage());
				}
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