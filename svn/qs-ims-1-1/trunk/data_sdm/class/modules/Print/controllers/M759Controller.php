<?php
/**
 * @author: Huy.Bui
 * @component: Cac mau in su co thiet bi
 */
class Print_M759Controller extends Qss_Lib_PrintController
{
	
	public function init()
	{
		parent::init();
		$this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/print.php';
	}

    /**
     * In bien ban su co
     */
    public function documentAction()
    {
        $this->html->data = $this->_params;
        $this->html->status = $this->_form->i_Status;
    }

    /**
     * Phiếu đề xuất
     */
    public function proposeAction()
    {
       		header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    		header("Content-disposition: attachment; filename=\"Phiếu đề xuất công việc.xlsx\"");

		$mCommon = new Qss_Model_Extra_Extra();
		$mBreak = new Qss_Model_Sdmbreakdown();
		$mMaintenance = new Qss_Model_Maintenance_Breakdown();
		$eqs = array();
		$startRow = 17;
		$row = $startRow + 1;
		$str = 'Căn cứ vào thực tế sử dụng và dự phòng cho công tác vận hành';
		$txtSoBaoCao = 'Báo Cáo Sự Cố';
		$txtSoBienBan = 'Báo Cáo Biên Bản';
		$file = new Qss_Model_Excel(Qss_Lib_System::getTemplateFile('M759', 'HFH_PhieuDeXuatCongViec.xlsx'));
		$main = new stdClass();

		$model = new Qss_Model_M708_Main();
		// echo "<pre>";
		// print_r($model->getSuCo($this->_params->IFID_M759));die;
		$phieuYeuCau = $mCommon->getTableFetchOne('OYeuCauBaoTri', array('IOID' => (int) $this->_params->Ref_PhieuYeuCau));
		
		$thietBi = $mCommon->getTableFetchOne('ODanhSachThietBi', array('IOID' => @(int) $this->_params->Ref_MaThietBi));
        // echo "<pre>";
        // print_r($thietBi);die;

		$doDaiKhoangTrangSoPhieu = abs(19 - strlen(trim($this->_params->SoPhieu)));
		$doDaiKhoangTrangSoPhieu = $doDaiKhoangTrangSoPhieu ? $doDaiKhoangTrangSoPhieu : 1;

		$main->SoPhieu = str_repeat(' ', $doDaiKhoangTrangSoPhieu) . trim($this->_params->SoPhieu);
		

		$main->NgayBanHanh = str_repeat(' ', 2) . Qss_Lib_Date::mysqltodisplay($this->_params->NgayBatDau);

		$main->Trang = '                                ';
		// echo "<pre>";
		// print_r($model->checkSuCo($this->_params->IFID_M759));die;
		if ($model->checkSuCo($this->_params->IFID_M759))
		{
			$main->txtSoBaoCao = $txtSoBaoCao;
			$main->txtSoBienBan = $txtSoBienBan;
			$day = @$phieuYeuCau->NgayBaoCao ? date('d', strtotime(@$phieuYeuCau->NgayBaoCao)) : '  ';
			$month = @$phieuYeuCau->NgayBaoCao ? date('m', strtotime(@$phieuYeuCau->NgayBaoCao)) : '  ';
			$year = @$phieuYeuCau->NgayBaoCao ? date('Y', strtotime(@$phieuYeuCau->NgayBaoCao)) : '    ';

			$main->SoBaoCao = @$phieuYeuCau->SoBaoCao;
			$main->NgayThangNamBaoCao = 'Ngày ' . $day . ' ';
			$main->NgayThangNamBaoCao .= 'tháng ' . $month . ' ';
			$main->NgayThangNamBaoCao .= 'năm ' . $year;
			$main->SoBienBan = @$phieuYeuCau->SoPhieu;
			$main->NgayThangNamBienBan = 'Ngày ' . $day . ' ';
			$main->NgayThangNamBienBan .= 'tháng ' . $month . ' ';
			$main->NgayThangNamBienBan .= 'năm ' . $year;

		}
		else
		{
			$main->NgayThangNamBienBan = '';
			$main->NgayThangNamBaoCao = '';
			$main->SoBienBan = '';
			$main->SoBaoCao = $str;
			$main->txtSoBaoCao = '';
			$main->txtSoBienBan = '';

		}

		$main->MaThietBi = @$thietBi->MaThietBi;
		$main->TenThietBi = @$thietBi->TenThietBi;
		$main->LoaiThietBi = @$thietBi->LoaiThietBi;
		$main->HeThong = @$thietBi->NhomThietBi;
		$main->KhuVucLapDat = @$thietBi->MaKhuVuc;
		$main->DeXuatKhac = $this->_params->DeXuatKhac;

		//$huHong                      = $mCommon->getTableFetchAll('ODanhMucBoPhanHuHong', array('IFID_M759'=>$this->_params->IFID_M759), array('*'), array('MaSuCo'));
		$huHong = $mMaintenance->getFailureByWorkorder($this->_params->IFID_M759);
		$arrMucDoUuTien = Qss_Lib_System::getFieldRegx('ODanhMucBoPhanHuHong', 'MucDoUuTien');
		$vatTu = $mBreak->getVatTuTheoHangMuc($this->_params->IFID_M759);

		$file->init(array('m' => $main));

		foreach ($huHong as $item) 
		{
			$data = new stdClass();
			$data->a = @$item->MaSuCo;
			$data->b = @$item->MoTaSuCo;
			$data->c = @$item->Tenhuhong;
			$data->d = $arrMucDoUuTien[@$item->MucDoUuTien];

			$file->newGridRow(array('s1' => $data), $row, $startRow);
			$row++;
		}

		$tableDeXuat = $row + 4;
		$row = $row + 5;

		foreach ($huHong as $item) 
		{
			$data = new stdClass();
			$data->a = $item->MaSuCo;
			$data->b = $item->BienPhapKhacPhuc;
			$data->c = $item->ThoiHanCanGiaiQuyet;

			$file->newGridRow(array('s2' => $data), $row, $tableDeXuat);
			$row++;
		}

		$tableUocLuong = $row + 4;
		$row = $row + 5;

		foreach ($vatTu as $item) 
		{
			$data = new stdClass();
			$data->a = @$item->MaHangMuc;
			$data->b = @$item->MoTaHangMuc;
			$data->c = @$item->GiaiPhapTrucThuoc;
			$data->d = @$item->MaVatTu;
			$data->e = @$item->SoLuongDuKien;
			$data->f = @$item->DonViTinh;

			$file->newGridRow(array('s3' => $data), $row, $tableUocLuong);
			$row++;
		}

		$file->removeRow($tableUocLuong);
		$file->removeRow($tableDeXuat);
		$file->removeRow($startRow);

//        echo '<pre>'; print_r($file->wsMain->getHeaderFooter()->getOddHeader()); die;

//        echo '<pre>'; print_r($file->wsMain->getHeaderFooter()->getOddHeader());

		$header = $file->wsMain->getHeaderFooter()->getOddHeader();

		$header = str_replace(
			array('{m:SoPhieu}', '{m:NgayBanHanh}', '{m:Trang}')
			, array($main->SoPhieu, $main->NgayBanHanh, $main->Trang), $header);
		$file->wsMain->getHeaderFooter()->setOddHeader($header);

//        echo '<pre>'; print_r($file->wsMain->getHeaderFooter()->getOddHeader()); die;

		$file->save();
		die();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
    /**
     * Phiếu yêu cầu
     */
    public function requestAction()
    {
        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"Phiếu yêu cầu công việc.xlsx\"");

        $mCommon    = new Qss_Model_Extra_Extra();
        $mBreak     = new Qss_Model_Sdmbreakdown();
        $mMaintenance = new Qss_Model_Maintenance_Breakdown();
        $eqs        = array();
        $startRow   = 24;
        $row        = $startRow + 1;
        $file       = new Qss_Model_Excel(Qss_Lib_System::getTemplateFile('M759', 'HFH_PhieuYeuCauCongViec.xlsx'), false);
        $main       = new stdClass();
        $str = 'Căn cứ vào thực tế sử dụng và dự phòng cho công tác vận hành';
		$txtSoBaoCao = '-Báo Cáo Sự Cố:';
		$txtSoBienBan = '-Báo Cáo Biên Bản:';
        $phieuYeuCau                  = $mCommon->getTableFetchOne('OYeuCauBaoTri', array('IOID'=>(int)$this->_params->Ref_PhieuYeuCau));
        $thietBi                      = $mCommon->getTableFetchOne('ODanhSachThietBi', array('IOID'=>(int)$this->_params->Ref_MaThietBi));

        $DaiDienKhachHang1 = $mCommon->getTableFetchOne('OLienHeCaNhan', array('IOID'=>(int)$this->_params->Ref_DaiDienKhachHang1));
        $DaiDienKhachHang2 = $mCommon->getTableFetchOne('OLienHeCaNhan', array('IOID'=>(int)$this->_params->Ref_DaiDienKhachHang2));

        $DaiDienKyThuat1 = $mCommon->getTableFetchOne('OLienHeCaNhan', array('IOID'=>(int)$this->_params->Ref_DaiDienThucHien1));
        $DaiDienKyThuat2 = $mCommon->getTableFetchOne('OLienHeCaNhan', array('IOID'=>(int)$this->_params->Ref_DaiDienThucHien2));

        $main->BenKhachHang          = $this->_params->BenKhachHang;
        $main->DaiDienKhachHang1     = $this->_params->DaiDienKhachHang1;;
        $main->DaiDienKhachHang2     = $this->_params->DaiDienKhachHang2;;
        $main->ChucDanhDaiDienKH1    = @$DaiDienKhachHang1->ChucDanh;
        $main->ChucDanhDaiDienKH2    = @$DaiDienKhachHang2->ChucDanh;
        $main->DaiDienThucHien1      = $this->_params->DaiDienThucHien1;
        $main->ChucDanhDaiDienTH1    = @$DaiDienKyThuat1->ChucDanh;
        $main->DaiDienThucHien2      = $this->_params->DaiDienThucHien2;
        $main->ChucDanhDaiDienTH2    = @$DaiDienKyThuat2->ChucDanh;

        $doDaiKhoangTrangSoPhieu = abs(19 - strlen(trim($this->_params->SoPhieu)));
        $doDaiKhoangTrangSoPhieu = $doDaiKhoangTrangSoPhieu?$doDaiKhoangTrangSoPhieu:1;

        $main->SoPhieu               = str_repeat(' ', $doDaiKhoangTrangSoPhieu).trim($this->_params->SoPhieu);
        $main->NgayBanHanh           = str_repeat(' ', 2).Qss_Lib_Date::mysqltodisplay($this->_params->NgayBatDau);
        $main->Trang                 = '                                ';
        $day   = @$phieuYeuCau->NgayBaoCao?date('d', strtotime(@$phieuYeuCau->NgayBaoCao)):'  ';
        $month = @$phieuYeuCau->NgayBaoCao?date('m', strtotime(@$phieuYeuCau->NgayBaoCao)):'  ';
        $year  = @$phieuYeuCau->NgayBaoCao?date('Y', strtotime(@$phieuYeuCau->NgayBaoCao)):'    ';
        $model = new Qss_Model_M708_Main;
      if ($model->checkSuCo($this->_params->IFID_M759)){
      	  $main->SoBaoCao              = @$phieuYeuCau->SoBaoCao;
        $main->NgayThangNamBaoCao    = 'Ngày '.$day.' ';
        $main->NgayThangNamBaoCao   .= 'tháng '.$month.' ';
        $main->NgayThangNamBaoCao   .= 'năm '.$year;
        $main->SoBienBan             = @$phieuYeuCau->SoPhieu;
        $main->NgayThangNamBienBan   = 'Ngày '.$day.' ';
        $main->NgayThangNamBienBan  .= 'tháng '.$month.' ';
        $main->NgayThangNamBienBan  .= 'năm '.$year;
        $main->txtSoBaoCao = $txtSoBaoCao;
        $main->txtSoBienBan = $txtSoBienBan;

      }
      else{
      	$main->NgayThangNamBaoCao = '';
      	$main->NgayThangNamBienBan='';
      	$main->txtSoBaoCao = $str;
      	$main->txtSoBienBan = '';
      	$main->SoBaoCao = '';
      	$main->SoBienBan = '';
      }
      	
        $main->MaThietBi             = $this->_params->MaThietBi;
        $main->TenThietBi            = $this->_params->TenThietBi;
        $main->LoaiThietBi           = @$thietBi->LoaiThietBi;
        $main->HeThong               = @$thietBi->NhomThietBi;
        $main->KhuVucLapDat          = @$thietBi->MaKhuVuc;
        $main->DeXuatKhac            = @$this->_params->DeXuatKhac;
        //$huHong                      = $mCommon->getTableFetchAll('ODanhMucBoPhanHuHong', array('IFID_M759'=>$this->_params->IFID_M759), array('*'), array('MaSuCo'));
        $huHong                      = $mMaintenance->getFailureByWorkorder($this->_params->IFID_M759);
        $arrMucDoUuTien              = Qss_Lib_System::getFieldRegx('ODanhMucBoPhanHuHong', 'MucDoUuTien');
        $vatTu                       = $mBreak->getVatTuTheoHangMuc($this->_params->IFID_M759);

        $file->init(array('m'=>$main));

        foreach ($huHong as $item)
        {
            $data     = new stdClass();
            $data->a  = @$item->MaSuCo;
            $data->b  = @$item->MoTaSuCo;
            $data->c  = @$item->Tenhuhong;
            $data->d  = @$arrMucDoUuTien[@$item->MucDoUuTien];

            $file->newGridRow(array('s1'=>$data), $row, $startRow);
            $row++;
        }

        $tableDeXuat = $row + 4;
        $row         = $row + 5;

        foreach ($huHong as $item)
        {
            $data     = new stdClass();
            $data->a  = $item->MaSuCo;
            $data->b  = $item->BienPhapKhacPhuc;
            $data->c  = $item->ThoiHanCanGiaiQuyet;

            $file->newGridRow(array('s2'=>$data), $row, $tableDeXuat);
            $row++;
        }
        $tableUocLuong = $row + 4;
        $row           = $row + 5;

        foreach ($vatTu as $item)
        {
            $data     = new stdClass();
            $data->a  = $item->MaHangMuc;
            $data->b  = $item->MoTaHangMuc;
            $data->c  = $item->GiaiPhapTrucThuoc;
            $data->d  = $item->MaVatTu;
            $data->e  = $item->SoLuongDuKien;
            $data->f  = $item->DonViTinh;

            $file->newGridRow(array('s3'=>$data), $row, $tableUocLuong);
            $row++;
        }

        $file->removeRow($tableUocLuong);
        $file->removeRow($tableDeXuat);
        $file->removeRow($startRow);

        $header = $file->wsMain->getHeaderFooter()->getOddHeader();
        $header = str_replace(
            array('{m:SoPhieu}', '{m:NgayBanHanh}', '{m:Trang}')
            , array($main->SoPhieu, $main->NgayBanHanh, $main->Trang), $header);
        $file->wsMain->getHeaderFooter()->setOddHeader($header);

        $file->save();
        die();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    /**
     * Phiếu theo dõi bảo dưỡng
     */
    public function cycleAction()
    {
        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"Phiếu theo dõi bảo dưỡng.xlsx\"");

        $eqs = array();
        $startRow   = 11;
        $row        = $startRow + 1;
        $file       = new Qss_Model_Excel(Qss_Lib_System::getTemplateFile('M759', 'HFH_PhieuTheoDoiBaoDuong.xlsx'), true);
        $main       = new stdClass();

        $main->NgayBaoDuong           = '';
        $main->CaBaoDuong             = '';
        $main->TruongCaTrucVHBD       = '';
        $main->TruongCaTrucVanHanhCDT = '';
        $main->SoPhieu                = '';
        $main->NgayBanHanh            = '';
        $main->Trang                  = '';
        $main->KetLuanKienNghi        = '';

        $file->init(array('m'=>$main));

        foreach ($eqs as $item)
        {
            $data     = new stdClass();
            $data->a  = $item->TenThietBi;
            $data->b  = $item->KhuVucLapDat;
            $data->c  = $item->NoiDungCongViec;
            $data->d  = $item->TrangThaiCongViec;
            $data->e  = $item->ThoiGianDuKien;
            $data->f  = $item->ThoiGianHoanTat;
            $data->g  = $item->NguoiThucHien;
            $data->h  = $item->NguoiDungThietBi;
            $data->i  = $item->GhiChu;

            $file->newGridRow(array('s'=>$data), $row, $startRow);
            $row++;
        }

        $file->removeRow($startRow);

        $file->save();
        die();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    /**
     * Biên bản xác nhận bảo trì
     */
    public function reportAction() {
        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"Phiếu theo dõi bảo dưỡng.xlsx\"");

        $mCommon  = new Qss_Model_Extra_Extra();
        $file     = new Qss_Model_Excel(Qss_Lib_System::getTemplateFile('M759', 'SDM_BienBanXacNhanBaoTri.xlsx'), true);
        $main     = new stdClass();
        $DaiDienChuDauTu1    = $mCommon->getTableFetchOne('OLienHeCaNhan', array('IOID'=>(int)$this->_params->Ref_DaiDienKhachHang1));
        $DaiDienBenLienQuan1 = $mCommon->getTableFetchOne('OLienHeCaNhan', array('IOID'=>(int)$this->_params->Ref_DaiDienThucHien1));
        $DaiDienBenKyThuat   = $mCommon->getTableFetchOne('OLienHeCaNhan', array('IOID'=>(int)$this->_params->Ref_DaiDienBenKyThuat));
        $equip               = $mCommon->getTableFetchOne('ODanhSachThietBi', array('IOID'=>(int)$this->_params->Ref_MaThietBi));
        $location            = $mCommon->getTableFetchOne('OKhuVuc', array('IOID'=>@(int)$equip->Ref_MaKhuVuc));

        $gioBatDau   = $this->_params->GioBatDau?date('H', strtotime($this->_params->GioBatDau)):'  ';
        $phutBatDau  = $this->_params->GioBatDau?date('i', strtotime($this->_params->GioBatDau)):'  ';
        $ngayBatDau  = $this->_params->NgayBatDau?date('d', strtotime($this->_params->NgayBatDau)):'  ';
        $thangBatDau = $this->_params->NgayBatDau?date('m', strtotime($this->_params->NgayBatDau)):'  ';
        $namBatDau   = $this->_params->NgayBatDau?date('Y', strtotime($this->_params->NgayBatDau)):date('Y');

        $gioKetThuc   = $this->_params->GioKetThuc?date('H', strtotime($this->_params->GioKetThuc)):'  ';
        $phutKetThuc  = $this->_params->GioKetThuc?date('i', strtotime($this->_params->GioKetThuc)):'  ';
        $ngayKetThuc  = $this->_params->Ngay?date('d', strtotime($this->_params->Ngay)):'  ';
        $thangKetThuc = $this->_params->Ngay?date('m', strtotime($this->_params->Ngay)):'  ';
        $namKetThuc   = $this->_params->Ngay?date('Y', strtotime($this->_params->Ngay)):date('Y');

        $data                           = new stdClass();
        $data->SoPhieu                  = 'BM –SDM-01-2016';
        $data->NgayBanHanh              = '15/05 /2017';
        $data->ChuDauTu                 = @$this->_params->BenKhachHang;
        $data->DaiDienChuDauTu1         = @$this->_params->DaiDienKhachHang1;
        $data->DaiDienChuDauTu2         = '';
        $data->ChucDanhDaiDienCDT1      = @$DaiDienChuDauTu1?$DaiDienChuDauTu1->ChucDanh:'';
        $data->ChucDanhDaiDienCDT2      = '';
        $data->BenKyThuat               = @$this->_params->DonViBaoTri;
        $data->DaiDienKyThuat1          = @$this->_params->DaiDienBenKyThuat;
        $data->DaiDienKyThuat2          = '';
        $data->ChucDanhDaiDienKT1       = @$DaiDienBenKyThuat?$DaiDienBenKyThuat->ChucDanh:'';;
        $data->ChucDanhDaiDienKT2       = '';
        $data->BenLienQuan              = @$this->_params->BenThucHien;
        $data->DaiDienBenLienQuan1      = @$this->_params->DaiDienThucHien1;
        $data->DaiDienBenLienQuan2      = '';
        $data->ChucDanhDaiDienLienQuan1 = @$DaiDienBenLienQuan1?$DaiDienBenLienQuan1->ChucDanh:'';
        $data->ChucDanhDaiDienLienQuan2 = '';
        $data->ThoiGianBatDau           = "{$gioBatDau} h {$phutBatDau} ngày {$ngayBatDau} tháng {$thangBatDau} năm {$namBatDau}";
        $data->ThoiGianKetThuc          = "{$gioKetThuc} h {$phutKetThuc} ngày {$ngayKetThuc} tháng {$thangKetThuc} năm {$namKetThuc}";

        $data->a = @$equip->TenThietBi; // Ten thiet bi
        $data->b = @$equip->LoaiThietBi; // Loai thiet bi
        $data->c = @$equip->MaThietBi; // Ma thiet bi
        $data->d = @$location->Ten; // Khu vuc
        $data->e = ''; // Ghi chu

        $data->a1        = @$this->_params->ChuKy;; // Co phai la dinh ky (nút tick) (Định kỳ)
        $data->b1  = ''; // Co phai la dinh ky (Số ngày định kỳ)
        $data->c1 = ''; // Co phai la dinh ky (Số tháng định kỳ)
        $data->KhongDinhKy   = ''; // Không định kỳ (nút tick)
        $data->Khac          = ''; // Khac neu ro

        $data->DanhGia       = @$this->_params->Ref_Dat; // Danh gia sau bao tri
        $data->YKienKhac     = @$this->_params->DeXuatKhac;
        $data->KetLuan       = @$this->_params->KetLuan;

        $file->init(array('m'=>$data));


        $header = $file->wsMain->getHeaderFooter()->getOddHeader();
        $header = str_replace(
            array('{m:SoPhieu}', '{m:NgayBanHanh}')
            , array($data->SoPhieu, $data->NgayBanHanh), $header);
        $file->wsMain->getHeaderFooter()->setOddHeader($header);

        $file->setRowHeight(26, -1);

        $file->save();
        die();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}

?>