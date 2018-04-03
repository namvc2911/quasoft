<?php
class Qss_Service_Button_M317_Calculate extends Qss_Service_Abstract 
{
    public function __doExecute($kycong = 3,$department = 0) 
    {
        /*query trong bảng ngày công là ra, nhóm theo kỳ công, nhân viên
        1. Công chuẩn (count ca làm việc)
        2. Công thực tế (count ca làm việc thực tế)
        3. Số h ca đêm: count số h thuộc ca đêm
        5. Tổng số phút đi muôn về sớm
        */
    	/*query trong bảng ngày công số loại h nghỉ
        */
    	/*query trong bảng làm thêm các loại làm thêm được duyệt
        */
        //query theo phòng ban hoặc tất cả nhân viên cần tỏnghop
        //$array key là ngày, nhân viên
        $arrData = array();
        //lấy lịch làm việc ra cũng tổng vào array
        $kycongmodel = Qss_Model_Db::Table('OKyCong');
        $kycongmodel->where(sprintf('IOID = %1$d',$kycong));
        $kycongdata = $kycongmodel->fetchOne();
        $startdate = $kycongdata->ThoiGianBatDau;
        $enddate = $kycongdata->ThoiGianKetThuc;
        $model = new Qss_Model_M026_Main();
    	$dataCong = $model->sumaryByPeriod($startdate,$enddate,$department);
		
		//lấy ngày nghỉ cũng tổng vào array
    	$dataNghi = $model->sumaryLeaveByPeriod($startdate,$enddate,$department);
    	$arrNghi = array();
		foreach($dataNghi as $item)
		{
			if(!isset($arrNghi[$item->Ref_MaNhanVien]))
			{
				$arrNghi[$item->Ref_MaNhanVien] = array();
			}
			$arrNghi[$item->Ref_MaNhanVien][] = array('LoaiNgayNghi'=>$item->LoaiNgayNghi,
													'SoGioNghi'=>$item->SoGioNghi);
		}
        
    	$dataOT = $model->sumaryOTByPeriod($startdate,$enddate,$department);
    	$arrOT = array();
		foreach($dataOT as $item)
		{
			if(!isset($arrOT[$item->Ref_MaNhanVien]))
			{
				$arrOT[$item->Ref_MaNhanVien] = array();
			}
			$arrOT[$item->Ref_MaNhanVien][] = array('LoaiLamThem'=>$item->LoaiTangCa,
													'SoGioLamThem'=>$item->SoGioTangCa);
		}
		if(count($dataCong))
		{
			$import = new Qss_Model_Import_Form('M317');
			foreach ($dataCong as $item)
			{
				$arrInsert = array();
				$arrInsert['OBangCongTheoKy'] = array(0=>array('Ref_KyCong'=>$kycong
															,'MaNhanVien'=>$item->MaNhanVien
															,'TenNhanVien'=>$item->TenNhanVien
															,'PhongBanHienTai'=>$item->PhongBanHienTai
															,'CongChuan'=>$item->CongChuan
															,'CongThucTe'=>$item->CongThucTe
															,'SoGioCaDem'=>$item->SoGioCaDem
															,'DiMuonVeSom'=>$item->TongPhutMuonSom));
				if(isset($arrOT[$item->Ref_MaNhanVien]))
				{
					$arrInsert['OTongHopLamThem'] = $arrOT[$item->Ref_MaNhanVien];
				}
				if(isset($arrNghi[$item->Ref_MaNhanVien]))
				{
					$arrInsert['OTongHopNgayNghi'] = $arrNghi[$item->Ref_MaNhanVien];
				}
				//print_r($arrInsert);
				$import->setData($arrInsert);
			}
			$import->generateSQL();
			//print_r($import->getErrorRows());die;
		}
        //Tính đi muộn về sớm
        //Tính OT rồi ghi vào bảng bên dưới
        //Lưu lịch sửa vào M046
        $m046 = new Qss_Model_Import_Form('M079');
        $arrM046 = array('OXuLyKyCong'=>array(0=>array('Ref_KyCong'=>(int)$kycong
        													,'Ref_PhongBan'=>(int)$department
        													//,'ThoiGianBatDau'=>date('Y-m-d H:i:s')
        													,'ThoiGianKetThuc'=>date('Y-m-d H:i:s'))));
		$m046->setData($arrM046);
        $m046->generateSQL();
        
    }
}
