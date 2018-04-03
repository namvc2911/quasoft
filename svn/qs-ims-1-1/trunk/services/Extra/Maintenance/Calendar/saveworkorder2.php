<?php
Class Qss_Service_Extra_Maintenance_Calendar_Saveworkorder extends  Qss_Service_Abstract
{
    public function __doExecute($params)
    {
        
		$insert     = array();
		$ifid       = @(int)$params['ifid'];
		$module     = @(string)$params['module'];
		$startDate  = @(string)$params['start_date'];
		$endDate    = @(string)$params['end_date'];	
		$startTime  = @(string)$params['start_time'];
		$endTime    = @(string)$params['end_time'];
		$emp        = @(int)$params['employee'];
		$wc         = @(int)$params['workcenter'];
		
		if(!$this->_validate($ifid))
		{
			$this->setError();
			return;
		}
		
		$insert['OPhieuBaoTri'][0]['NgayBatDau']    = $startDate;
		$insert['OPhieuBaoTri'][0]['Ngay']          = $endDate;
		$insert['OPhieuBaoTri'][0]['GioBatDau']     = $startTime;
		$insert['OPhieuBaoTri'][0]['GioKetThuc']    = $endTime;
		$insert['OPhieuBaoTri'][0]['NguoiThucHien'] = $emp;
		$insert['OPhieuBaoTri'][0]['MaDVBT']        = $wc;
		
		$service = $this->services->Form->Manual($module, $ifid, $insert, true);

		if ($service->isError())
		{
			$this->setError();
			$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
		}				
	}	
	
	public function _validate($ifid)
	{
		$ret = true;
		
		$common = new Qss_Model_Extra_Extra();
		$iForm  = $common->getTable(array('Status'), 'qsiforms', array('IFID'=>$ifid), array(), 'NO_LIMIT', 1);

		if($iForm &&  in_array(@(int)$iForm->Status, array(3, 4, 5)))
		{
			$ret= false;
			$this->setMessage('Bạn không thể sửa phiếu bảo trì!');
		}
		return $ret;
	}
}

