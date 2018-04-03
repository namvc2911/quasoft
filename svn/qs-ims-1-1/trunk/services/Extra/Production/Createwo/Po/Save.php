<?php

class Qss_Service_Extra_Production_Createwo_Po_Save extends Qss_Service_Abstract
{
	//@todo: Can tinh thoi gian hoan thanh san pham de chia cho tung cong doan, don vi thuc hien
	public function __doExecute($params)
	{
		$error = $this->validate_save($params);
		if($error)
		{
			$this->setError();
			$this->setMessage($error);
		}
		else
		{
			$this->themPhieuGiaoViec($params);
		}
	}
	
	private function themPhieuGiaoViec($params)
	{

		$common       = new Qss_Model_Extra_Extra(); // Cac ham model thuong xu dung
		$DefaultShift = $common->getDataset(array('module'=>'OCa', 'return'=>1, 'order'=>'IOID', 'limit'=>1)); // Lay ca mac dinh
		$j =0;
		//$params['RefLine'] = isset($params['RefLine'])?$params['RefLine']:array();
		//$params['RefBOM'] = isset($params['RefBOM'])?$params['RefBOM']:array();
		
		foreach($params['RefLine'] as $rl)
		{
			// Thanh phan nvl
			$filter['module']            = 'OThanhPhanSanPham';
			$filter['join'][0]['table']  = 'OCauThanhSanPham';
			$filter['join'][0]['alias']  = 'tksp';
			$filter['join'][0]['type']  = 0;
			$filter['join'][0]['condition'][0]['col1']  = 'IFID_M114';
			$filter['join'][0]['condition'][0]['alias1']  = 'cm';
			$filter['join'][0]['condition'][0]['col2']  = 'IFID_M114';
			$filter['join'][0]['condition'][0]['alias2']  = 'tksp';
			$filter['where'] = array('tksp.IOID'=>$params['RefBOM'][$j]);
			$thanhPhanSanPham =  $common->getDataset($filter);
			$MaterialArr = array();
	
			foreach ($thanhPhanSanPham as $sl)
			{
				if($sl->Ref_CongDoan )
				$MaterialArr[$sl->Ref_CongDoan][] = $sl;
			}
	
	
			// Lay cong doan theo day chuyen
			$filter['select']             = 'cd.*, cm.MaDayChuyen';
			$filter['module']           = 'ODayChuyen';
			$filter['join'][0]['table']  = 'OCongDoanDayChuyen';
			$filter['join'][0]['alias']  = 'cd';
			$filter['join'][0]['type']  = 1;
			$filter['join'][0]['condition'][0]['col1']  = 'IFID_M702';
			$filter['join'][0]['condition'][0]['alias1']  = 'cm';
			$filter['join'][0]['condition'][0]['col2']  = 'IFID_M702';
			$filter['join'][0]['condition'][0]['alias2']  = 'cd';
			$filter['where'] = array('cm.IOID'=>$rl);
			$StagesByLine =  $common->getDataset($filter);
			$StagesByLineArr = array();
			$LineName = count((array)$StagesByLine)?$StagesByLine[0]->MaDayChuyen:'';
	
			foreach ($StagesByLine as $sl)
			{
				if($sl->Ref_CongDoan && !isset($StagesByLineArr[$sl->Ref_CongDoan]))
				$StagesByLineArr[$sl->Ref_CongDoan] = $sl->MaDonViThucHien;
			}
	
	
			// Lay cong doan theo BOM cua san pham
			$filter['select']             = 'cd.*, cm.TenCauThanhSanPham';
			$filter['module']           = 'OCauThanhSanPham';
			$filter['join'][0]['table']  = 'OCongDoanBOM';
			$filter['join'][0]['alias']  = 'cd';
			$filter['join'][0]['type']  = 1;
			$filter['join'][0]['condition'][0]['col1']  = 'IFID_M114';
			$filter['join'][0]['condition'][0]['alias1']  = 'cm';
			$filter['join'][0]['condition'][0]['col2']  = 'IFID_M114';
			$filter['join'][0]['condition'][0]['alias2']  = 'cd';
			$filter['where'] = array('cm.IOID'=>$params['RefBOM'][$j]);
			$StagesByBOM =  $common->getDataset($filter);
			 
			
			foreach ($StagesByBOM as $sp)
			{
				if(isset($params['DocNo'][$j]))
				{
					$insert = array();
					$insert['OPhieuGiaoViec'][0]['MaLenhSX'] = $params['DocNo'][$j];
					$insert['OPhieuGiaoViec'][0]['Ngay'] = @$params['Date'][$j];
					$insert['OPhieuGiaoViec'][0]['Ca'] = $DefaultShift?$DefaultShift->MaCa:'';
					$insert['OPhieuGiaoViec'][0]['GioBD'] = $DefaultShift?$DefaultShift->GioBatDau:'';
					$insert['OPhieuGiaoViec'][0]['GioKT'] = $DefaultShift?$DefaultShift->GioKetThuc:'';
					$insert['OPhieuGiaoViec'][0]['DonViThucHien'] = $StagesByLineArr[$sp->Ref_Ten];
					$insert['OPhieuGiaoViec'][0]['CongDoan'] = $sp->Ten;
					$i = 0;
		
					if(isset($MaterialArr[$sp->Ref_Ten]))
					{
						foreach($MaterialArr[$sp->Ref_Ten] as $m)
						{
							$insert['ONVLDauVao'][$i]['MaSP'] = $m->MaThanhPhan;
							$insert['ONVLDauVao'][$i]['DonViTinh'] = $m->DonViTinh;
							$insert['ONVLDauVao'][$i]['ThuocTinh'] = $m->ThuocTinh;
							$insert['ONVLDauVao'][$i]['SoLuong'] = $m->SoLuong;
							$i++;
						}
					}
					
					$service = $this->services->Form->Manual('M712' , 0, $insert, false);
					if($service->isError())
					{
						$this->setError();
						$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
					}
				}
			}
			$j++;
		}
	}
	
	private function validate_save($params)
	{
		$i = 0;
		$retval = '';
		//$params['RefLine'] = isset($params['RefLine'])?$params['RefLine']:array();
		//$params['RefBOM'] = isset($params['RefBOM'])?$params['RefBOM']:array();
		
		foreach($params['RefLine'] as $rl)
		{
			$EnoughStages = Qss_Lib_Production_Common::checkEnoughStages($rl, $params['RefBOM'][$i]);
			if(count($EnoughStages))
			{
				$retval .= $EnoughStages['error'];
			}
			$i++;
		}
		return $retval;
	}
}

?>