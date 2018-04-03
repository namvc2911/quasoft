<?php

class Qss_Bin_Onload_OVatTuPBTDK extends Qss_Lib_Onload
{
	/**
	 *  - Thay vật tư
		- Thay PT từ kho
		- Thay PT từ thiết bị khác
		- Đổi PT từ thiết bị khác
		- Lắp mới PT
		- Tháo PT
	 */

	public function __doExecute()
	{
		parent::__doExecute();

		$mCommon  = new Qss_Model_Extra_Extra();
		$task     = $mCommon->getTableFetchAll('OCongViecBTPBT', array('IFID_M759'=>$this->_object->i_IFID));
        $hinhThuc = (int)$this->_object->getFieldByCode('HinhThuc')->getValue();


        // Onload lại tên mặt hàng có thể sửa khi sử dụng mã tạm
        if(Qss_Lib_System::fieldActive('OSanPham', 'MaTam'))
        {
            $item = (int)$this->_object->getFieldByCode('MaVatTu')->getRefIOID();

            if(!$item)
            {
                $mTable = Qss_Model_Db::Table('OVatTuPBT');
                $mTable->where(sprintf('IOID = %1$d', $this->_object->i_IOID));
                $data = $mTable->fetchOne();

                if($data)
                {
                    $item = (int)$data->Ref_MaSP;
                }
            }

            $this->_object->getFieldByCode('TenVatTu')->bReadOnly = true;

            $mItem = Qss_Model_Db::Table('OSanPham');
            $mItem->where(sprintf('IOID = %1$d', $item));
            $oItem = $mItem->fetchOne();

            if($oItem)
            {
                if($oItem->MaTam)
                {
                    $this->_object->getFieldByCode('TenVatTu')->bReadOnly = false;
                }
                else
                {
                    $this->_object->getFieldByCode('TenVatTu')->setValue($oItem->TenSanPham);
                }
            }
        }

        // Dùng để reset lại khi không phải là gia công
        $this->_object->getFieldByCode('MaVatTu')->bReadOnly = false;
        $this->_object->getFieldByCode('MaVatTu')->bRequired = true;
        $this->_object->getFieldByCode('DonViTinh')->bReadOnly = false;
        $this->_object->getFieldByCode('DonViTinh')->bRequired = true;
        $this->_object->getFieldByCode('DonGia')->bReadOnly = true;


        // Tự động load giá mặt hàng cho trường hợp không phải là Gia công
        $refItem  = (int)$this->_object->getFieldByCode('MaVatTu')->getRefIOID();
        $soluong  = (int)$this->_object->getFieldByCode('SoLuong')->getValue();
        $hinhThuc = (int)$this->_object->getFieldByCode('HinhThuc')->getValue();
        $donGia   = '';

        if($hinhThuc != 10) {
            if($refItem && $soluong)
            {
                $sql = sprintf('
                SELECT GiaMua
                FROM OSanPham
                WHERE IOID = %1$d ', $refItem);
                $output = $this->_db->fetchOne($sql);
                $donGia = $output?($output->GiaMua/1000):'';
            }

            $this->_object->getFieldByCode('DonGia')->setValue($donGia);
        }

		if(count($task) == 1)//chỉ tự động khi có 1 công việc
		{
			$task = $task[0];
			if(!$this->_object->getFieldByCode('CongViec')->getValue())
			{
				$this->_object->getFieldByCode('CongViec')->setRefIOID($task->IOID);
				$this->_object->getFieldByCode('CongViec')->setValue($task->MoTa);
			}

			if(!$this->_object->getFieldByCode('ViTri')->getValue())
			{
				$this->_object->getFieldByCode('ViTri')->setRefIOID($task->Ref_ViTri);
				$this->_object->getFieldByCode('ViTri')->setValue($task->ViTri);
			}

			if(!$this->_object->getFieldByCode('ViTri')->getValue())
			{
				$this->_object->getFieldByCode('BoPhan')->setRefIOID($task->Ref_ViTri);
				$this->_object->getFieldByCode('BoPhan')->setValue($task->BoPhan);
			}
		}
		$ioidSanPham = $this->_object->getFieldByCode('MaVatTu')->getRefIOID();
		if($ioidSanPham)
		{
			$model = new Qss_Model_Master_Item();
			$dvt     = $model->getUOMByIOID($ioidSanPham);
			if(count($dvt) == 1)//chỉ tự động khi có 1 công việc
			{
				$dvt = $dvt[0];
				if(!$this->_object->getFieldByCode('DonViTinh')->getValue())
				{
					$this->_object->getFieldByCode('DonViTinh')->setRefIOID($dvt->IOID);
					$this->_object->getFieldByCode('DonViTinh')->setValue($dvt->DonViTinh);
				}

			}
		}

		if(Qss_Lib_System::fieldActive('OVatTuPBT', 'HinhThuc'))
		{
			$this->_object->getFieldByCode('ViTri')->bRequired = false;
			$hinhThuc = (int)$this->_object->getFieldByCode('HinhThuc')->getValue();

			if($hinhThuc != 0) // nhiều khi chưa có trong cấu trúc
			{
				$this->_object->getFieldByCode('ViTri')->bRequired = true;
			}
		}

		if(Qss_Lib_System::formActive('M506'))
		{
			$mItem    = new Qss_Model_Master_Item();
			$refItem  = $this->_object->getFieldByCode('MaVatTu')->intRefIOID;
			$refItem  = $refItem?$refItem:0;
			$item     = $mItem->getItemByIOID($refItem);
			$hinhThuc = (int)$this->_object->getFieldByCode('HinhThuc')->getValue();

			if($item && (int)$item->QuanLyTheoMa)
			{
				$this->_object->getFieldByCode('SerialKhac')->bRequired = true;

				if($hinhThuc != 0) // Thay the
				{
					$this->_object->getFieldByCode('Serial')->bRequired = true;
				}
			}
		}
		 // Onload lại tên mặt hàng có thể sửa khi sử dụng mã tạm
        if(Qss_Lib_System::fieldActive('OSanPham', 'MaTam'))
        {
            $item = (int)$this->_object->getFieldByCode('MaVatTu')->getRefIOID();

            if(!$item)
            {
                $mTable = Qss_Model_Db::Table('OVatTuPBT');
                $mTable->where(sprintf('IOID = %1$d', $this->_object->i_IOID));
                $data = $mTable->fetchOne();

                if($data)
                {
                    $item = (int)$data->Ref_MaVatTu;
                }
            }

            $this->_object->getFieldByCode('TenVatTu')->bReadOnly = true;

            $mItem = Qss_Model_Db::Table('OSanPham');
            $mItem->where(sprintf('IOID = %1$d', $item));
            $oItem = $mItem->fetchOne();

            if($oItem)
            {
                if($oItem->MaTam)
                {
                    $this->_object->getFieldByCode('TenVatTu')->bReadOnly = false;
                }
                else
                {
                    $this->_object->getFieldByCode('TenVatTu')->setValue($oItem->TenSanPham);
                }
            }
        }

        if($hinhThuc == 10) // Gia công
        {
            $this->_object->getFieldByCode('MaVatTu')->bReadOnly = true;
            $this->_object->getFieldByCode('MaVatTu')->bRequired = false;

            $this->_object->getFieldByCode('DonViTinh')->bReadOnly = true;
            $this->_object->getFieldByCode('DonViTinh')->bRequired = false;

            $this->_object->getFieldByCode('DonGia')->bReadOnly = false;
        }
	}

	public function ViTri()
	{
		$this->_doFilter($this->_object->getFieldByCode('ViTri'));
		//Filter theo bộ phận đối tượng chính bỏ vì đã filter theo công việc
		/*$sql     = sprintf('
		 select OCauTrucThietBi.lft, OCauTrucThietBi.rgt, ODanhSachThietBi.IFID_M705 AS IFID
		 from OPhieuBaoTri
		 inner join ODanhSachThietBi ON OPhieuBaoTri.Ref_MaThietBi = ODanhSachThietBi.IOID
		 inner join OCauTrucThietBi ON ODanhSachThietBi.IFID_M705 = OCauTrucThietBi.IFID_M705
		 and ifnull(OPhieuBaoTri.Ref_BoPhan, 0) = OCauTrucThietBi.IOID
		 WHERE OPhieuBaoTri.IFID_M759 = %1$d
		 ', @(int)$this->_object->i_IFID);

		 $dataSQL = $this->_db->fetchOne($sql);

		 if($dataSQL)
		 {
		 $this->_object->getFieldByCode('ViTri')->arrFilters[] = sprintf('
		 v.IOID in (select IOID from OCauTrucThietBi where IFID_M705 = %1$d and lft >= %2$d and rgt <= %3$d )'
		 , $dataSQL->IFID, $dataSQL->lft, $dataSQL->rgt);
		 }*/
		$congviec = @(int)$this->_object->getFieldByCode('CongViec')->getRefIOID();
		$sql = sprintf('select ct.lft,ct.rgt
                    FROM OCongViecBTPBT as t
                    inner join OCauTrucThietBi as ct on ct.IOID = t.Ref_ViTri
                    WHERE t.IFID_M759 = %1$d
                    AND t.IOID = %2$d
            ', @(int)$this->_object->i_IFID
		, $congviec);
		$dataSQL = $this->_db->fetchOne($sql);
		if($congviec)
		{
			if($dataSQL)
			{
				$this->_object->getFieldByCode('ViTri')->arrFilters[] =
				sprintf('(v.lft >= %1$d and v.rgt <=  %2$d )
			            ', $dataSQL->lft
				, $dataSQL->rgt);

				$this->_object->getFieldByCode('ViTri')->bRequired = true;
			}
		}
		else
		{
			$this->_object->getFieldByCode('ViTri')->arrFilters[] = '1=0';
		}
	}
	public function MaVatTu()
	{
		//Ngoài thay thế vật tư còn lại phai chọn trong cấu trúc và phụ tùng thay thế
		$hinhThuc = (int)$this->_object->getFieldByCode('HinhThuc')->getValue();
		$viTri    = (int)$this->_object->getFieldByCode('ViTri')->intRefIOID;

		if($hinhThuc != 0 )
		{
			//Lay phu tung tu cau truc va phu tung thay the
			$refItemSql = sprintf('
                    SELECT CauTruc.Ref_MaSP
                    FROM OPhieuBaoTri AS Phieu
                    INNER JOIN ODanhSachThietBi AS ThietBi ON Phieu.Ref_MaThietBi = ThietBi.IOID
                    INNER JOIN OCauTrucThietBi AS CauTruc ON ThietBi.IFID_M705 = CauTruc.IFID_M705
                    WHERE Phieu.IFID_M759 = %1$d
                        AND IFNULL(CauTruc.Ref_MaSP, 0) != 0
                        AND IFNULL(CauTruc.PhuTung, 0) = 1
                        AND IFNULL(CauTruc.IOID, 0) = %2$d

                    UNION

                    SELECT CauTruc.Ref_MaSP
                    FROM OPhieuBaoTri AS Phieu
                    INNER JOIN ODanhSachThietBi AS ThietBi ON Phieu.Ref_MaThietBi = ThietBi.IOID
                    INNER JOIN ODanhSachPhuTung AS CauTruc ON ThietBi.IFID_M705 = CauTruc.IFID_M705
                    WHERE Phieu.IFID_M759 = %1$d
                        AND IFNULL(CauTruc.Ref_MaSP, 0) != 0
                        AND IFNULL(CauTruc.Ref_ViTri, 0) = %2$d
            ', (int)$this->_object->i_IFID, $viTri);
			$refItem = $this->_db->fetchAll($refItemSql);

			//Lay vi tri hien tai dang chon
			$comSql = sprintf('
                SELECT IFNULL(CauTruc.PhuTung, 0) AS PhuTung, Ref_MaSP
                FROM OPhieuBaoTri AS Phieu
                INNER JOIN ODanhSachThietBi AS ThietBi ON Phieu.Ref_MaThietBi = ThietBi.IOID
                INNER JOIN OCauTrucThietBi AS CauTruc ON ThietBi.IFID_M705 = CauTruc.IFID_M705
                WHERE Phieu.IFID_M759 = %1$d AND IFNULL(CauTruc.IOID, 0) = %2$d
            ', (int)$this->_object->i_IFID, $viTri);
			$com = $this->_db->fetchOne($comSql);

			//Neu không phai phu tung thi khong hien ra gi
			//Neu la phu tung tung thi phai xet xem vi tri da co mat hang nao chua
			//Neu chua co ca trong cau truc lan phu tung thay the thi cho chon tat
			//Con mat hang co o mot trong hai thi chon theo cau truc va phu tung thay the
			if($com && $com->PhuTung == 1)
			{
				if($com->Ref_MaSP && $refItem)
				{
					$this->_object->getFieldByCode('MaVatTu')->arrFilters[] =
					sprintf('
                (
                    v.IOID in (
                        SELECT CauTruc.Ref_MaSP
                        FROM OPhieuBaoTri AS Phieu
                        INNER JOIN ODanhSachThietBi AS ThietBi ON Phieu.Ref_MaThietBi = ThietBi.IOID
                        INNER JOIN OCauTrucThietBi AS CauTruc ON ThietBi.IFID_M705 = CauTruc.IFID_M705
                        WHERE Phieu.IFID_M759 = %1$d
                            AND IFNULL(CauTruc.Ref_MaSP, 0) != 0
                            AND IFNULL(CauTruc.PhuTung, 0) = 1
                            AND IFNULL(CauTruc.IOID, 0) = %2$d

                        UNION

                        SELECT CauTruc.Ref_MaSP
                        FROM OPhieuBaoTri AS Phieu
                        INNER JOIN ODanhSachThietBi AS ThietBi ON Phieu.Ref_MaThietBi = ThietBi.IOID
                        INNER JOIN ODanhSachPhuTung AS CauTruc ON ThietBi.IFID_M705 = CauTruc.IFID_M705
                        WHERE Phieu.IFID_M759 = %1$d
                            AND IFNULL(CauTruc.Ref_MaSP, 0) != 0
                            AND IFNULL(CauTruc.Ref_ViTri, 0) = %2$d
                    )
                )
            ', (int)$this->_object->i_IFID, $viTri);
				}
			}
			else
			{
				$this->_object->getFieldByCode('MaVatTu')->arrFilters[] = sprintf(' 1=0 ');
			}
			//echo '<pre>'; print_r($this->_object->getFieldByCode('MaVatTu')->arrFilters); die;
		}
	}

	public function SerialKhac()
	{
		//lọc trạng thái lưu trữ của kho theo vật tư và phải có số lượng = 1
		$vatTu = (int)$this->_object->getFieldByCode('MaVatTu')->getRefIOID();
		$thietbikhac = (int)$this->_object->getFieldByCode('MaThietBiKhac')->getRefIOID();
		$vitrikhac = (int)$this->_object->getFieldByCode('ViTriKhac')->getRefIOID();
		if($thietbikhac || $vitrikhac)
		{
			$this->_object->getFieldByCode('SerialKhac')->arrFilters[] =
			sprintf('
                v.IOID in (
                    SELECT Ref_Serial
                    FROM OCauTrucThietBi AS ct
                    inner join ODanhSachThietBi as tb on tb.IFID_M705 = ct.IFID_M705
                    WHERE tb.IOID = %1$d and ct.IOID = %2$d)'
                    , $thietbikhac
                    , $vitrikhac);
		}
		else
		{
			$this->_object->getFieldByCode('SerialKhac')->arrFilters[] =
			sprintf('
                v.IOID in (
                    SELECT TrangThai.IOID
                    FROM OThuocTinhChiTiet AS TrangThai
                    WHERE IFNULL(TrangThai.IFID_M602, 0) != 0
                        AND IFNULL(TrangThai.SoSerial, \'\') != \'\'
                        AND IFNULL(TrangThai.SoLuong, 0) = 1
                        AND IFNULL(TrangThai.Ref_MaSanPham, 0) = %1$d
                )
            ', $vatTu);
		}
	}
	/* Chuyển vào calculate, load mặc định ra
	 public function Serial()
	 {
	  
	 //lọc trạng thái lưu trữ của kho có trong cấu trúc thiết bị (kể cả vị trí )
	 $vatTu = (int)$this->_object->getFieldByCode('MaVatTu')->intRefIOID;
	 $viTri = (int)$this->_object->getFieldByCode('ViTri')->intRefIOID;

	 $this->_object->getFieldByCode('SerialKhac')->arrFilters[] =
	 sprintf('
	 v.IOID in (
	 SELECT CauTruc.IOID
	 FROM OCauTrucThietBi AS CauTruc
	 WHERE IFNULL(CauTruc.Ref_MaSP, 0) = %1$d
	 AND IFNULL(CauTruc.IOID, 0) = %2$d
	 )
	 ', $vatTu, $viTri);
	 }
	 */
	public function ViTriKhac()
	{
		$this->_doFilter($this->_object->getFieldByCode('ViTriKhac'));
		$vatTu = (int)$this->_object->getFieldByCode('MaVatTu')->getRefIOID();
		if($vatTu)
		{
			$this->_object->getFieldByCode('ViTriKhac')->arrFilters[] =
			sprintf('
                	v.Ref_MaSP = %1$d'
                	, $vatTu);
		}
	}
}
