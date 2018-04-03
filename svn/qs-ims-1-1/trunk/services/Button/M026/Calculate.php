<?php
class Qss_Service_Button_M026_Calculate extends Qss_Service_Abstract 
{
    public function __doExecute($kycong = 3,$department = 0) 
    {
    	$arrWeek = array(0=>'ChuNhat',1=>'ThuHai',2=>'ThuBa',3=>'ThuTu',4=>'ThuNam',5=>'ThuSau',6=>'ThuBay');
        //insert
        //query theo phòng ban hoặc tất cả nhân viên cần tỏnghop
        //$array key là ngày, nhân viên
        $arrData = array();
        //lấy lịch làm việc ra cũng tổng vào array
        $kycongmodel = Qss_Model_Db::Table('OKyCong');
        $kycongmodel->where(sprintf('IOID = %1$d',$kycong));
        $kycongdata = $kycongmodel->fetchOne();
        $startdate = $kycongdata->ThoiGianBatDau;
        $enddate = $kycongdata->ThoiGianKetThuc;
        $model = new Qss_Model_M308_Main();
    	$datalich = $model->getDataByPeriod($startdate,$enddate,$department);
		//cho vào mảng từ ngày đến ngày
		foreach($datalich as $item)
		{
			$start = date_create($item->TuNgay);
			$end = date_create($item->DenNgay);
			while ($start <= $end)
			{
				if(!isset($arrData[$item->Ref_MaNhanVien][$start->format('Y-m-d')]))
				{
					$arrData[$item->Ref_MaNhanVien][$start->format('Y-m-d')] = array();
					$arrData[$item->Ref_MaNhanVien][$start->format('Y-m-d')]['MaNhanVien'] = $item->MaNhanVien;
					$arrData[$item->Ref_MaNhanVien][$start->format('Y-m-d')]['TenNhanVien'] = $item->TenNhanVien;
					$arrData[$item->Ref_MaNhanVien][$start->format('Y-m-d')]['PhongBanHienTai'] = $item->PhongBan;
					$arrData[$item->Ref_MaNhanVien][$start->format('Y-m-d')]['NgayCong'] = $start->format('Y-m-d');
				}
				//xem là thứ mấy để lấy đúng giá trị
				$arrData[$item->Ref_MaNhanVien][$start->format('Y-m-d')]['CaLamViec'] = $item->{$arrWeek[$start->format('w')]};	
				$arrData[$item->Ref_MaNhanVien][$start->format('Y-m-d')]['CaDiLamThucTe'] = $item->{$arrWeek[$start->format('w')]};
				$start = Qss_Lib_Date::add_date($start, 1);
			}
		}
		//lấy ngày nghỉ của phòng ban vào array
    	$modelnghile = new Qss_Model_M056_Main();
        $dataNghiLe = $modelnghile->getDataByPeriod($startdate, $enddate,$department);
		foreach($dataNghiLe as $item)
		{
			$start = date_create($item->NgayBatDau);
			$end = date_create($item->NgayKetThuc);
			while ($start <= $end)
			{
				if(!isset($arrData[$item->Ref_MaNhanVien][$start->format('Y-m-d')]))
				{
					$arrData[$item->Ref_MaNhanVien][$start->format('Y-m-d')] = array();
					$arrData[$item->Ref_MaNhanVien][$start->format('Y-m-d')]['MaNhanVien'] = $item->MaNhanVien;
					$arrData[$item->Ref_MaNhanVien][$start->format('Y-m-d')]['TenNhanVien'] = $item->TenNhanVien;
					$arrData[$item->Ref_MaNhanVien][$start->format('Y-m-d')]['PhongBanHienTai'] = $item->PhongBanHienTai;
					$arrData[$item->Ref_MaNhanVien][$start->format('Y-m-d')]['NgayCong'] = $start->format('Y-m-d');
				}
				//xem là thứ mấy để lấy đúng giá trị
				$arrData[$item->Ref_MaNhanVien][$start->format('Y-m-d')]['LoaiNgayNghi'] = $item->LoaiNghi;	
				$start = Qss_Lib_Date::add_date($start, 1);
			}
		}
		
    	//lấy ngày nghỉ toàn công ty vào array
        $dataAllNghiLe = $modelnghile->getAllDataByPeriod($startdate, $enddate);
        $arrNghiLeCongTy = array();
		foreach($dataAllNghiLe as $item)
		{
			$start = date_create($item->NgayBatDau);
			$end = date_create($item->NgayKetThuc);
			while ($start <= $end)
			{
				$arrNghiLeCongTy[$start->format('Y-m-d')] = $item->LoaiNghi;	
				$start = Qss_Lib_Date::add_date($start, 1);
			}
		}
		
    	//lấy ngày ưu tiên
    	$modeluutien = new Qss_Model_M076_Main();
        $dataUuTien = $modeluutien->getDataByPeriod($startdate, $enddate,$department);
		foreach($dataUuTien as $item)
		{
			$start = date_create($item->ThoiGianBatDau);
			$end = date_create($item->ThoiGianKetThuc);
			while ($start <= $end)
			{
				if(!isset($arrData[$item->Ref_MaNhanVien][$start->format('Y-m-d')]))
				{
					$arrData[$item->Ref_MaNhanVien][$start->format('Y-m-d')] = array();
					$arrData[$item->Ref_MaNhanVien][$start->format('Y-m-d')]['MaNhanVien'] = $item->MaNhanVien;
					$arrData[$item->Ref_MaNhanVien][$start->format('Y-m-d')]['TenNhanVien'] = $item->TenNhanVien;
					$arrData[$item->Ref_MaNhanVien][$start->format('Y-m-d')]['PhongBanHienTai'] = $item->MaPhongBan;
					$arrData[$item->Ref_MaNhanVien][$start->format('Y-m-d')]['NgayCong'] = $start->format('Y-m-d');
				}
				//xem là thứ mấy để lấy đúng giá trị
				$arrData[$item->Ref_MaNhanVien][$start->format('Y-m-d')]['LoaiUuTien'] = $item->LoaiUuTien;	
				$start = Qss_Lib_Date::add_date($start, 1);
			}
		}
		
    	//lấy ngày nghỉ cũng tổng vào array
    	$modelnghi = new Qss_Model_M077_Main();
        $dataNghi = $modelnghi->getDataByPeriod($startdate, $enddate,$department);
		foreach($dataNghi as $item)
		{
			$start = date_create($item->NgayBatDau);
			$end = date_create($item->NgayKetThuc);
			while ($start <= $end)
			{
				if(!isset($arrData[$item->Ref_MaNhanVien][$start->format('Y-m-d')]))
				{
					$arrData[$item->Ref_MaNhanVien][$start->format('Y-m-d')] = array();
					$arrData[$item->Ref_MaNhanVien][$start->format('Y-m-d')]['MaNhanVien'] = $item->MaNhanVien;
					$arrData[$item->Ref_MaNhanVien][$start->format('Y-m-d')]['TenNhanVien'] = $item->TenNhanVien;
					$arrData[$item->Ref_MaNhanVien][$start->format('Y-m-d')]['PhongBanHienTai'] = $item->PhongBanHienTai;
					$arrData[$item->Ref_MaNhanVien][$start->format('Y-m-d')]['NgayCong'] = $start->format('Y-m-d');
				}
				//xem là thứ mấy để lấy đúng giá trị
				$arrData[$item->Ref_MaNhanVien][$start->format('Y-m-d')]['LoaiNgayNghi'] = $item->LoaiNgayNghi;	
				$arrData[$item->Ref_MaNhanVien][$start->format('Y-m-d')]['SoGioNghi'] = $item->SoGioNghi;
				$start = Qss_Lib_Date::add_date($start, 1);
			}
		}
		
		//lấy ca làm việc
		$camodel = Qss_Model_Db::Table('OCa');
		$ca = $camodel->fetchAll();
		$arrCa = array();
		foreach($ca as $item)
		{
			$arrCa[$item->MaCa] = $item;
		}
		//tính toán đăng ký làm thêm
        $modelOT = new Qss_Model_M078_Main();
        $dataOT = $modelOT->getDataByPeriod($startdate, $enddate,$department);
		$arrOT = array();
    	foreach($dataOT as $item)
		{
			if(!isset($arrOT[$item->Ref_MaNhanVien][$item->NgayDangKy]))
			{
				$arrOT[$item->Ref_MaNhanVien][$item->NgayDangKy] = array();
			}
			$arrOT[$item->Ref_MaNhanVien][$item->NgayDangKy][] = array('LoaiTangCa'=>$item->LoaiTangCa,'SoGioDangKy'=>$item->GioDangKy);
		}
		//lấy các loại nghỉ mà có Loại tăng ca
		$arrOTNgayLe = array();
        $table = Qss_Model_Db::Table('OPhanLoaiNghi');
        $table->where('ifnull(Ref_LoaiTangCa,0)');
        $arrTmp = $table->fetchAll();
        foreach ($arrTmp as $item)
        {
        	$arrOTNgayLe[$item->MaLoaiNghi] = $item->LoaiTangCa;
        }
	    //lấy các loại ưu tiên mà có Loại tăng ca
		$arrOTUuTien = array();
        $table = Qss_Model_Db::Table('OLoaiUuTien');
        $table->where('ifnull(Ref_LoaiTangCa,0)');
        $arrTmp = $table->fetchAll();
        foreach ($arrTmp as $item)
        {
        	$arrOTUuTien[$item->MaLoaiNghi] = $item->LoaiTangCa;
        }		
        //lấy dữ liệu chấm công group theo mã chấm công, lấy min và max time rồi tổng vào array
		$modelchamcong = new Qss_Model_M321_Main();
        $dataChamCong = $modelchamcong->getInOut($startdate, $enddate, $department);
        $arrLamThem = array();
		foreach($dataChamCong as $item)
		{
			if(!isset($arrData[$item->Ref_MaNhanVien][$item->NgayVao]))
			{
				$arrData[$item->Ref_MaNhanVien][$item->NgayVao] = array();
				$arrData[$item->Ref_MaNhanVien][$item->NgayVao]['MaNhanVien'] = $item->MaNhanVien;
				$arrData[$item->Ref_MaNhanVien][$item->NgayVao]['TenNhanVien'] = $item->TenNhanVien;
				$arrData[$item->Ref_MaNhanVien][$item->NgayVao]['PhongBanHienTai'] = $item->MaPhongBan;
				$arrData[$item->Ref_MaNhanVien][$item->NgayVao]['NgayCong'] = $item->NgayVao;
			}
			if(isset($arrNghiLeCongTy[$item->NgayVao]))
			{
				$arrData[$item->Ref_MaNhanVien][$item->NgayVao]['LoaiNgayNghi'] = $arrNghiLeCongTy[$item->NgayVao];
			}			
			$arrData[$item->Ref_MaNhanVien][$item->NgayVao]['NgayVao'] = $item->NgayVao;	
			$arrData[$item->Ref_MaNhanVien][$item->NgayVao]['NgayRa'] = $item->NgayRa;
			$arrData[$item->Ref_MaNhanVien][$item->NgayVao]['GioVao'] = $item->GioVao;
			if($item->GioVao != $item->GioRa)
			{
				$arrData[$item->Ref_MaNhanVien][$item->NgayVao]['GioRa'] = $item->GioRa;
			}
			//tinhs lamf them
			$calv = @$arrData[$item->Ref_MaNhanVien][$item->NgayVao]['CaLamViec'];
			if($calv)
			{
				$loaitc = '';
				$loainghi = @$arrData[$item->Ref_MaNhanVien][$item->NgayVao]['LoaiNgayNghi'];
				$loaiuutien = @$arrData[$item->Ref_MaNhanVien][$item->NgayVao]['LoaiUuTien'];
				$lftOT = (strtotime($item->GioVao)<strtotime($arrCa[$calv]->GioBatDau))?Qss_Lib_Date::diffTime($item->GioVao, $arrCa[$calv]->GioBatDau):0;
				$rgtOT = (strtotime($item->GioRa)>strtotime($arrCa[$calv]->GioBatDau))?Qss_Lib_Date::diffTime($item->GioRa, $arrCa[$calv]->GioKetThuc):0;
				$OT = $lftOT + $rgtOT;
				$OT = round((($OT*100)/25))*25/100;
				if($loainghi && isset($arrOTNgayLe[$loainghi]))
				{
					$OT += $arrCa[$calv]->SoGio;
					$loaitc = $arrOTNgayLe[$loainghi];
				}
				elseif($loaiuutien && isset($arrOTUuTien[$loainghi]))
				{
					$loaitc = $arrUuTien[$loainghi];
				}
				if($OT)
				{
					if(!isset($arrLamThem[$item->Ref_MaNhanVien][$item->NgayVao]))
					{
						$arrLamThem[$item->Ref_MaNhanVien][$item->NgayVao] = array();
					}
					if(!$loaitc)
					{
						if(date_create($item->NgayVao)->format('w') == 6)
						{
							$loaitc = 'OT200';
						}
						else 
						{
							$loaitc = 'OT150';
						}
					}
					$arrSubOT = array('LoaiTangCa'=>$loaitc,'SoGioTangCa'=>$OT);
					$arrLamThem[$item->Ref_MaNhanVien][$item->NgayVao][] = $arrSubOT;
																				
				}
			}
		}
		if(count($arrData))
		{
			$import = new Qss_Model_Import_Form('M026');
			foreach ($arrData as $key=>$value)
			{
				foreach($value as $key2=>$item)
				{
					$arrImport = array('OBangCongTheoNgay'=>array($item));
					$arrOTInsert = array();
					if(isset($arrLamThem[$key][$key2]))
					{
						$arrOTInsert = $arrLamThem[$key][$key2];
					}
					if(isset($arrOT[$key][$key2]))
					{
						$arrOTInsert = array_merge_recursive($arrOTInsert,$arrOT[$key][$key2]); 
					}
					//print_r($arrOTInsert);
					if(count($arrOTInsert))
					{
						$arrOTInsert = $this->_mergeArray($arrOTInsert);
						$arrImport['OTangCaHangNgay'] = $arrOTInsert;
					}
					//print_r($arrImport);
					$import->setData($arrImport);
				}
			}
			$import->generateSQL();
			//print_r($import->getErrorRows());die;
		}
		//Tính đi muộn về sớm
        
        
        //Lưu lịch sửa vào M046
        $m046 = new Qss_Model_Import_Form('M046');
        $arrM046 = array('OTongHopNgayCong'=>array(0=>array('Ref_KyCong'=>(int)$kycong
        													,'Ref_PhongBan'=>(int)$department
        													,'ThoiGianBatDau'=>date('Y-m-d H:i:s')
        													,'ThoiGianKetThuc'=>date('Y-m-d H:i:s'))));
		$m046->setData($arrM046);
        $m046->generateSQL();
        
    }
    protected function _mergeArray($arr)
    {
    	$ret = array();
    	foreach($arr as $key=>$item)
    	{
    		if(isset($ret[$item['LoaiTangCa']]))
    		{
    			$ret[$item['LoaiTangCa']] = array_merge($ret[$item['LoaiTangCa']],$item);
    			$duyet = min($ret[$item['LoaiTangCa']]['SoGioDangKy'],$ret[$item['LoaiTangCa']]['SoGioTangCa']);
    			$ret[$item['LoaiTangCa']]['SoGioDuyet'] = $duyet;
    		}
    		else 
    		{
    			$ret[$item['LoaiTangCa']] = $item;
    		}
    	}
    	$ret = (array_values($ret));
    	return $ret;
    }
}
