<?php
/**
 * Class Static_M405Controller
 * Xử lý kế hoạch mua sắm
 * Purchase request process
 */
class Static_M778Controller extends Qss_Lib_Controller {

	public function init() {
		$this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
		parent::init();
		$this->_common = new Qss_Model_Extra_Extra();

		$this->headScript($this->params->requests->getBasePath() . '/js/report-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');

		$this->_model = new Qss_Model_Maintenance_Workorder();
	}

	/**
	 * Lý lịch thiết bị
	 */
	public function indexAction() {

	}
	public function showAction() {
		$locModel = new Qss_Model_Maintenance_Location();
		$eqModel = new Qss_Model_Maintenance_Equipment();
		$common = new Qss_Model_Extra_Extra();

		$refEq = $this->params->requests->getParam('eq', 0);
		$eq = $this->params->requests->getParam('equipmentStr', '');
		$eqArr = $refEq ? array($refEq) : array();

		$this->html->eq = $common->getTableFetchOne('ODanhSachThietBi', array('IOID' => $refEq));
		$this->html->workcenter = $locModel->getManageDepOfEquip($refEq);
		$this->html->eqParams = array();
		$this->html->sparepart = $eqModel->getSparepartOfEquip($refEq);
		$this->html->history = $this->getWorkOrderHistoryDetailOfEquipment($refEq);
		$this->html->document = $eqModel->getDocumentsOfEquip($refEq);
		$this->html->eqTechnicalParams = (Qss_Lib_System::objectInForm('ODacTinhThietBi', 'M705'))
		? $eqModel->getTechnicalParameters($eqArr) : array();
	}

	public function excelAction() {
		header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
		header("Content-disposition: attachment; filename=\"Lý lịch thiết bị.xlsx\"");

		$locModel = new Qss_Model_Maintenance_Location();
		$eqModel = new Qss_Model_Maintenance_Equipment();
		$common = new Qss_Model_Extra_Extra();

		$refEq = $this->params->requests->getParam('eq', 0);
		$eq = $this->params->requests->getParam('equipmentStr', '');
		$eqArr = $refEq ? array($refEq) : array();

		if (!$refEq) {return;}

		$file = new Qss_Model_Excel(QSS_DATA_DIR . '/template/M778/LyLichThietBi.xlsx');
		$main = new stdClass();
		$workcenters = array();
		$row = 12; // Dong bat dau in du lieu mat hang
		$paramNo = 0;
		$sparepartNo = 0;
		$docNo = 0;
		$breakdownNo = 0;
		$woHistoryNo = 0;

		$equip = $common->getTableFetchOne('ODanhSachThietBi', array('IOID' => $refEq));
		$manage = $locModel->getManageDepOfEquip($refEq);
		$equipStatus = Qss_Lib_System::getFieldRegx('ODanhSachThietBi', 'TrangThai');
		$equpParams = $eqModel->getTechnicalParameterValues($eqArr);
		$sparepart = $eqModel->getSparepartOfEquip($refEq);
		$document = $eqModel->getDocumentsOfEquip($refEq);
		$history = $this->getWorkOrderHistoryDetailOfEquipment($refEq);

		foreach ($manage as $wc) {
			if ($wc->IOID && !in_array($wc->Ma, $workcenters)) {$workcenters[] = $wc->Ma;}
		}

		// Dien thong tin chinh
		$main->tenThietBi = $equip->TenThietBi;
		$main->maThietBi = $equip->MaThietBi;
		$main->model = $equip->Model;
		$main->serial = $equip->Serial;
		$main->nhaSanXuat = $equip->XuatXu;
		$main->DacTinhKyThuat = $equip->DacTinhKT;
		$main->MaTaiSan = $equip->MaTaiSan;

		if (Qss_Lib_System::fieldActive('ODanhSachThietBi', 'ChucNang')) {
			$main->ChucNangNhiemVu = 'Chức năng nhiệm vụ: ' . $equip->ChucNang;
		}

		$main->namSanXuat = $equip->NamSanXuat;
		$main->ngay = Qss_Lib_Date::mysqltodisplay($equip->NgayDuaVaoSuDung);
		$main->donVi = implode(' , ', $workcenters);
		$main->tinhTrang = $equipStatus[(int) $equip->TrangThai];
		$file->init(array('main' => $main));

		// Dien thong tin thong so ky thuat
		foreach ($equpParams as $item) {
			$data = new stdClass();
			$data->stt = ++$paramNo;
			$data->thongSo = $item->ChiSo;
			$data->donVi = $item->DonViTinh;
			$data->giaTri = $item->GiaTri;
			$data->ghiChu = '';

			$file->newGridRow(array('sub1' => $data), $row, 9);
			$row++;
		}

		// Set lai row voi table phụ tung tai lieu
		$table2StartRow = $row + 5;
		$row = $table2StartRow;

		// Danh sach phu tung
		if (count($sparepart)) {
			$data = new stdClass();
			$data->tieuDe = 'Phụ tùng';
			$cellMerge1 = 'A' . $row . ':O' . $row;

			$file->newGridRow(array('sub2' => $data), $row, ($table2StartRow - 2));
			$row++;
		}

		foreach ($sparepart as $item) {
			$data = new stdClass();
			$data->stt = ++$sparepartNo;
			$data->boPhan = $item->BoPhan;
			$data->ten = $item->TenSP;
			$data->quyCach = $item->DacTinhKyThuat;
			$data->soLuong = $item->SoLuongHC;
			$data->maSo = $item->MaSP;
			$data->ghiChu = '';

			$file->newGridRow(array('sub2' => $data), $row, ($table2StartRow - 1));
			$row++;
		}

		// Danh sach tai lieu
		if (count($document)) {
			$data = new stdClass();
			$data->tieuDe = 'Tài liệu';

			$file->newGridRow(array('sub2' => $data), $row, ($table2StartRow - 2));
			$row++;
		}

		foreach ($document as $docNo) {
			$data = new stdClass();
			$data->stt = ++$sparepartNo;
			$data->boPhan = '';
			$data->ten = $item->Type;
			$data->quyCach = '';
			$data->soLuong = '';
			$data->maSo = '';
			$data->ghiChu = '';

			$file->newGridRow(array('sub2' => $data), $row, ($table2StartRow - 1));
			$row++;
		}

		// Set lai row voi table su co
		$table3StartRow = $row + 7;
		$row = $table3StartRow;

		// Lich su bao tri su co
		foreach ($history as $item) {
			if ($item['Info']['TypeCode'] != Qss_Lib_Extra_Const::MAINT_TYPE_BREAKDOWN) {continue;}

			$data = new stdClass();
			$data->stt = ++$breakdownNo;
			$data->boPhan = $item['Info']['BoPhan'];
			$data->moTa = $item['Info']['Des'];
			$data->ngaySuCo = Qss_Lib_Date::mysqltodisplay($item['Info']['BreakDate']);
			$data->nguyeNhan = $item['Info']['BreakCode'];
			$data->thoiGianDungMay = $item['Info']['Downtime'];
			$data->xuLy = $item['Info']['Intervention'];

			$file->newGridRow(array('sub3' => $data), $row, ($table3StartRow - 1));
			$row++;
		}

		// Set lai row voi table nhat ky bao duong
		$table4StartRow = $row + 4;
		$row = $table4StartRow;

		// Nhat ky bao duong
		foreach ($history as $item) {
			if ($item['Info']['TypeCode'] == Qss_Lib_Extra_Const::MAINT_TYPE_BREAKDOWN) {continue;}

			$trongKH = ($item['Info']['TypeCode'] == Qss_Lib_Extra_Const::MAINT_TYPE_PREVENTIVE) ? 'x' : '';
			$ngoaiKH = ($item['Info']['TypeCode'] == Qss_Lib_Extra_Const::MAINT_TYPE_PREVENTIVE) ? '' : 'x';

			$ghiChu = '';

			if ($item['Info']['StatusNo'] == 5) // Tinh trang huy
			{
				$ghiChu .= "Hủy phiếu bảo trì " . $item['Info']['DocNo'] . ": " . $item['Info']['Des'];
			}

			if (isset($item['Component']) && count($item['Component'])) {
				foreach ($item['Component'] as $com1) {
					if (isset($com1['Work']) && count($com1['Work'])) {
						foreach ($com1['Work'] as $work1) {
							$data = new stdClass();
							$data->stt = ++$woHistoryNo;
							$data->boPhan = $work1['BoPhan'];
							$data->noiDung = $work1['MoTa'];
							$data->ngayBDSC = Qss_Lib_Date::mysqltodisplay(@$data['Info']['SDate']);
							$data->ngoaiKeHoach = $ngoaiKH;
							$data->nguoiThucHien = $work1['NguoiThucHien'];
							$data->ghiChu = $ghiChu;

							$file->newGridRow(array('sub4' => $data), $row, ($table4StartRow - 1));
							$row++;
						}
					}
				}
			}
		}

//        echo $table4StartRow-1; echo '<br/>';
		//        echo $table3StartRow-1; echo '<br/>';
		//        echo $table2StartRow-1; echo '<br/>';
		//        echo $table2StartRow-2; echo '<br/>';
		//        die;

		$file->removeRow($table4StartRow - 1);
		$file->removeRow($table3StartRow - 1);
		$file->removeRow($table2StartRow - 1);
		$file->removeRow($table2StartRow - 2);
		$file->removeRow(11);

		if (!Qss_Lib_System::fieldActive('ODanhSachThietBi', 'ChucNang')) {
			$file->removeRow(6);
		}

		$file->save();
		die();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	protected function getWorkOrderHistoryDetailOfEquipment($refEq) {
		$orderModel = new Qss_Model_Maintenance_Workorder();
		$orders = $orderModel->getClosedWorkOrderByEquipment($refEq);

		$retval = array();
		$ordersIFIDArr = array();
		$oldIFID = '';
		$oldPosition = '';
		$mOldIFID = ''; // Material
		$mOldPosition = ''; // Material
		$oOldIFID = '';
		$oOldPosition = '';
		$tIndex = 0;
		$oIndex = 0;
		$mIndex = 0;

		// ===== INIT MAINT ORDER ARRAY =====
		foreach ($orders as $item) {
			$ordersIFIDArr[] = $item->IFID_M759;
			$tempInfo = array();
			$tempInfo['IFID'] = $item->IFID_M759;
			$tempInfo['DocNo'] = @$item->SoPhieu;
			$tempInfo['Code'] = $item->MaThietBi;
			$tempInfo['Name'] = $item->TenThietBi;
			$tempInfo['Type'] = $item->LoaiBaoTri;
			$tempInfo['BoPhan'] = $item->BoPhan;
			$tempInfo['TypeCode'] = $item->Loai;
			$tempInfo['Shift'] = $item->Ca;
			$tempInfo['WorkCenter'] = $item->TenDVBT;
			$tempInfo['Employee'] = $item->NguoiThucHien;
			$tempInfo['Line'] = 0;
			//$tempInfo['Status']          = $item->Name;
			$tempInfo['StatusNo'] = ($item->StepNo > 0) ? $item->StepNo : 1;
			//$tempInfo['Review']          = $item->DanhGia;
			$tempInfo['ReqDate'] = $item->NgayYeuCau;
			$tempInfo['SDate'] = $item->NgayBatDau;
			$tempInfo['EDate'] = $item->Ngay;
			$tempInfo['Des'] = $item->MoTa;
			$tempInfo['Intervention'] = $item->XuLy;

			// Su co
			$tempInfo['BreakDate'] = $item->NgayDungMay;
			$tempInfo['Downtime'] = $item->ThoiGianDungMay;
			$tempInfo['BreakCode'] = $item->MaNguyenNhanSuCo;

			$tempInfo['MIndex'] = 0;
			$tempInfo['TIndex'] = 0;
			$tempInfo['NotMat'] = 0;
			$retval[$item->IFID_M759]['Info'] = $tempInfo;
			$retval[$item->IFID_M759]['Component'] = array();
		}

		// ===== GET TASKS AND MATERIALS =====
		$tasks = $orderModel->getTasksByIFID($ordersIFIDArr);
		$materials = $orderModel->getMaterialsByIFID($ordersIFIDArr);
		// 		$outsources   = $orderModel->getOutsourcesByIFID($ordersIFIDArr);

		// ===== ADD TASKS TO MAINT ARRAY  =====
		foreach ($tasks as $item) {
			if ($oldIFID != $item->IFID || $oldPosition != $item->Ref_ViTri) {
				if (isset($tIndex) && $tIndex && $oldIFID) {
					if (!isset($retval[$oldIFID]['Info']['TIndex'])) {
						$retval[$oldIFID]['Info']['TIndex'] = $tIndex;
					} else {
						$retval[$oldIFID]['Info']['TIndex'] += $tIndex;
					}
				}
				$tIndex = 0;
			}

			$oldIFID = $item->IFID;
			$oldPosition = $item->Ref_ViTri;

			if (!isset($retval[$item->IFID]['Component'][@(int) $item->Ref_ViTri])) {
				$tempCom = array();
				$tempCom['BoPhan'] = $item->BoPhan;
				$tempCom['ViTri'] = $item->ViTri;
				$tempCom['RowSpan'] = 0;
				$retval[$item->IFID]['Component'][@(int) $item->Ref_ViTri] = $tempCom;
			}

			$temp = array();
			$temp['BoPhan'] = $item->BoPhan . ($item->ViTri ? (' - ' . $item->ViTri) : '');
			$temp['MoTa'] = $item->MoTaCongViec;
			$temp['Ten'] = $item->Ten;
			$temp['NguoiThucHien'] = @$item->NguoiThucHien;
			$temp['GhiChu'] = $item->GhiChuCongViec;
			$temp['DanhGia'] = $item->DanhGia;
			$temp['Dat'] = $item->Dat;

			$retval[$item->IFID]['Component'][@(int) $item->Ref_ViTri]['Work'][$tIndex] = $temp;
			$tIndex++;
			$retval[$item->IFID]['Component'][@(int) $item->Ref_ViTri]['TIndex'] = $tIndex;
			$retval[$item->IFID]['Component'][@(int) $item->Ref_ViTri]['RowSpan'] = $tIndex;
		}

		if (isset($tIndex) && $tIndex && $oldIFID) {
			if (!isset($retval[$oldIFID]['Info']['TIndex'])) {
				$retval[$oldIFID]['Info']['TIndex'] = $tIndex;
			} else {
				$retval[$oldIFID]['Info']['TIndex'] += $tIndex;
			}
		}

		// ===== ADD MATERIALS TO MAINT ORDER ARRAY  =====
		foreach ($materials as $item) {
			if ($mOldIFID != $item->IFID || $mOldPosition != $item->Ref_ViTri) {
				if (isset($mIndex) && $mIndex && $mOldIFID) {
					if (!isset($retval[$mOldIFID]['Info']['MIndex'])) {
						$retval[$mOldIFID]['Info']['MIndex'] = $mIndex;
					} else {
						$retval[$mOldIFID]['Info']['MIndex'] += $mIndex;
					}
				}
				$mIndex = 0;
			}
			$mOldIFID = $item->IFID;
			$mOldPosition = $item->Ref_ViTri;

			if (!isset($retval[$item->IFID]['Component'][@(int) $item->Ref_ViTri])) {
				$tempCom = array();
				$tempCom['BoPhan'] = $item->BoPhan;
				$tempCom['ViTri'] = $item->ViTri;
				$tempCom['RowSpan'] = 0;
				$retval[$item->IFID]['Component'][@(int) $item->Ref_ViTri] = $tempCom;
			}

			$temp = array();
			$temp['MaVatTu'] = $item->MaVatTu;
			$temp['TenVatTu'] = $item->TenVatTu;
			$temp['DonViTinh'] = $item->DonViTinh;
			$temp['SoLuong'] = $item->SoLuong;
			$temp['GhiChu'] = $item->GhiChu;
			$temp['DacTinhKyThuat'] = $item->DacTinhKyThuat;
			$temp['GhiChu'] = $item->GhiChu;

			$retval[$item->IFID]['Component'][@(int) $item->Ref_ViTri]['Material'][$mIndex] = $temp;
			$mIndex++;
			$retval[$item->IFID]['Component'][@(int) $item->Ref_ViTri]['MIndex'] = $mIndex;
		}

		if (isset($mIndex) && $mIndex && $mOldIFID) {
			if (!isset($retval[$mOldIFID]['Info']['MIndex'])) {
				$retval[$mOldIFID]['Info']['MIndex'] = $mIndex;
			} else {
				$retval[$mOldIFID]['Info']['MIndex'] += $mIndex;
			}
		}
		return $retval;
	}

}