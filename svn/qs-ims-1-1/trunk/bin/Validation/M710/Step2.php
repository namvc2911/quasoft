<?php

class Qss_Bin_Validation_M710_Step2 extends Qss_Lib_Warehouse_WValidation
{
	public function onNext()
	{
			
		$EnoughStages = Qss_Lib_Production_Common::checkEnoughStages($this->_params->Ref_DayChuyen, $this->_params->Ref_ThietKe);
		if(count($EnoughStages))
		{
			$this->setError();
			$this->setMessage($EnoughStages['error']);
		}
	}

	/**
	 * create outgoing shipments
	 */
	public function next()
	{
		parent::init();
		$field = array('MaSP', 'ThuocTinh', 'SoLuong', 'NgayYeuCau', '', '', '', '', 'MaLenhSX', 'DonViTinh');
		$insertFieldLabel = array('Partner' => 'MaDoiTac', 'Date' => 'Ngay',
                    'Item' => 'MaSP', 'Attr' => 'ThuocTinh',
                    'Qty' => 'SoLuong', 'Price' => 'DonGia',
                    'Module' => 'Module', 'Warehouse' => 'Kho', 'Des' => 'MoTa'
                    , 'UOM' => 'DonViTinh');
                    $this->insertIncomingOutgoing($field, $insertFieldLabel, 'OHangDoiXuat', 'ONVLDauVao', 'OSanXuat', 'M710', 'M611', '', false);


                    // Dai y: Ham nay dung de tao phieu giao viec tu dong khi chuyen sang buoc 2; Phieu giao viec duoc tao theo thiet ke
                    // 1. Lay thiet ke cua san pham
                    // 2. Lay thiet ke de tao phieu giao viec

                    $common = new Qss_Model_Extra_Extra(); // Cac ham model thuong xu dung
                    $this->themPhieuGiaoViec();
	}

	private function themPhieuGiaoViec()
	{

		$common = new Qss_Model_Extra_Extra(); // Cac ham model thuong xu dung
        $mCa = Qss_Model_Db::Table('OCa');
        $mCa->orderby('IOID');
        $mCa->display('1');
//		$DefaultShift = $common->getDataset(array('module'=>'OCa', 'return'=>1, 'order'=>'IOID', 'limit'=>1)); // Lay ca mac dinh
        $DefaultShift = $mCa->fetchOne();

		// Thanh phan nvl
//		$filter['module']           = 'OThanhPhanSanPham';
//		$filter['join'][0]['table']  = 'OCauThanhSanPham';
//		$filter['join'][0]['alias']  = 'tksp';
//		$filter['join'][0]['type']  = 0;
//		$filter['join'][0]['condition'][0]['col1']  = 'IFID_M114';
//		$filter['join'][0]['condition'][0]['alias1']  = 'cm';
//		$filter['join'][0]['condition'][0]['col2']  = 'IFID_M114';
//		$filter['join'][0]['condition'][0]['alias2']  = 'tksp';
//		$filter['where'] = array('tksp.IOID'=>$this->_params->Ref_ThietKe);
//		$thanhPhanSanPham =  $common->getDataset($filter);

        $mThanhPhan = Qss_Model_Db::Table('OThanhPhanSanPham');
        $mThanhPhan->select('OThanhPhanSanPham.*');
        $mThanhPhan->join('INNER JOIN OCauThanhSanPham ON OThanhPhanSanPham.IFID_M114 = OCauThanhSanPham.IFID_M114');
        $mThanhPhan->where(sprintf('OCauThanhSanPham.IOID = %1$d', $this->_params->Ref_ThietKe));
        $thanhPhanSanPham =  $mThanhPhan->fetchAll();

		$MaterialArr = array();

		foreach ($thanhPhanSanPham as $sl)
		{
			if($sl->Ref_CongDoan )
			$MaterialArr[$sl->Ref_CongDoan][] = $sl;
		}


		// Lay cong doan theo day chuyen
//		$filter['select']             = 'cd.*, cm.MaDayChuyen';
//		$filter['module']           = 'ODayChuyen';
//		$filter['join'][0]['table']  = 'OCongDoanDayChuyen';
//		$filter['join'][0]['alias']  = 'cd';
//		$filter['join'][0]['type']  = 1;
//		$filter['join'][0]['condition'][0]['col1']  = 'IFID_M702';
//		$filter['join'][0]['condition'][0]['alias1']  = 'cm';
//		$filter['join'][0]['condition'][0]['col2']  = 'IFID_M702';
//		$filter['join'][0]['condition'][0]['alias2']  = 'cd';
//		$filter['where'] = array('cm.IOID'=>$this->_params->Ref_DayChuyen);
//		$StagesByLine =  $common->getDataset($filter);

        $mDayChuyen = Qss_Model_Db::Table('ODayChuyen');
        $mDayChuyen->select('OCongDoanDayChuyen.*, ODayChuyen.MaDayChuyen');
        $mDayChuyen->join('LEFT JOIN OCongDoanDayChuyen ON ODayChuyen.IFID_M702 = OCongDoanDayChuyen.IFID_M702');
        $mDayChuyen->where(sprintf('ODayChuyen.IOID = %1$d', $this->_params->Ref_DayChuyen));
        $StagesByLine =  $mDayChuyen->fetchAll();

		$StagesByLineArr = array();

		foreach ($StagesByLine as $sl)
		{
			if($sl->Ref_CongDoan && !isset($StagesByLineArr[$sl->Ref_CongDoan]))
			$StagesByLineArr[$sl->Ref_CongDoan] = $sl->MaDonViThucHien;
		}


		// Lay cong doan theo BOM cua san pham
//		$filter['select']             = 'cd.*, cm.TenCauThanhSanPham';
//		$filter['module']           = 'OCauThanhSanPham';
//		$filter['join'][0]['table']  = 'OCongDoanBOM';
//		$filter['join'][0]['alias']  = 'cd';
//		$filter['join'][0]['type']  = 1;
//		$filter['join'][0]['condition'][0]['col1']  = 'IFID_M114';
//		$filter['join'][0]['condition'][0]['alias1']  = 'cm';
//		$filter['join'][0]['condition'][0]['col2']  = 'IFID_M114';
//		$filter['join'][0]['condition'][0]['alias2']  = 'cd';
//		$filter['where'] = array('cm.IOID'=>$this->_params->Ref_ThietKe);
//		$StagesByBOM =  $common->getDataset($filter);

        $mCauThanh = Qss_Model_Db::Table('OCauThanhSanPham');
        $mCauThanh->select('OCongDoanBOM.*, OCauThanhSanPham.TenCauThanhSanPham');
        $mCauThanh->join('LEFT JOIN OCongDoanBOM ON OCauThanhSanPham.IFID_M114 = OCongDoanBOM.IFID_M114');
        $mCauThanh->where(sprintf('OCauThanhSanPham.IOID = %1$d', $this->_params->Ref_ThietKe));
        $StagesByBOM =  $mCauThanh->fetchAll();

			
		foreach ($StagesByBOM as $sp)
		{
			$insert = array();
			$insert['OPhieuGiaoViec'][0]['MaLenhSX'] = $this->_params->MaLenhSX;
			$insert['OPhieuGiaoViec'][0]['Ngay'] = $this->_params->TuNgay;
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
}
