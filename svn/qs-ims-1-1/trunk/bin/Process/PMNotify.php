<?php

class Qss_Bin_Process_PMNotify extends Qss_Lib_Bin
{

	public function __doExecute()
	{
		$common     = new Qss_Model_Extra_Extra();
		$dateCreate = (isset($this->_params->Ngay) && $this->_params->Ngay && $this->_params->Ngay != '0000-00-00')
            ?Qss_Lib_Date::displaytomysql($this->_params->Ngay):date('Y-m-d');
		$date       = date_create($dateCreate);

        // Lay thoi gian bat dau ket thuc tao phieu bao tri
		if(isset($this->_params->Ngay) && $this->_params->Ngay && $this->_params->Ngay != '0000-00-00')
		{
			$enddate = date_create($dateCreate);
		} 
		else // Lay lich cho mot tuan, bat dau tu thu 2 va ket thuc vao chu nhat
		{
			//tính ngày bắt đầu là t2 tuần sau và kết thúc là cn tuần sau
			$enddate    = date_create($dateCreate);
			$startfound = false;
			$i          = 0;

			while($i<14)
			{
				if($enddate->format('N') == 1)
				{
					$date       = date_create($enddate->format('Y-m-d'));
					$startfound = true;
				}
				elseif($enddate->format('N') == 7 && $startfound)
				{
					//$enddate = $sdate;
					break;
				}
				$enddate = Qss_Lib_Date::add_date($enddate, 1);
				$i++; 
			}
		}

        $model = new Qss_Model_Maintenance_Workorder();
        $error = $model->createWorkOrdersFromPlans(
            $date->format('Y-m-d')
            , $enddate->format('Y-m-d')
            , (int)$this->_params->Ref_KhuVuc
            , (int)$this->_params->Ref_DVBT);


        if($error > 0)
        {
            $this->setError();
            $this->setMessage('Có '.$error .' dòng lỗi!');
        }
	}
}
?>