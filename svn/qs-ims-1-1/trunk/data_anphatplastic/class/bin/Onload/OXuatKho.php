<?php
class Qss_Bin_Onload_OXuatKho extends Qss_Lib_Onload
{

	var $loai;
	/**
	 * onInsert
	 */
	public function __doExecute()
	{
		parent::__doExecute();
        $user    = Qss_Register::get('userinfo');
        $mStock  = new Qss_Model_Warehouse_Main();

        $stock   = $mStock->getStockByUser($user->user_id);
        $sql     = sprintf('select * from OLoaiXuatKho where Loai = "%1$s" LIMIT 1', Qss_Lib_Extra_Const::OUTPUT_TYPE_MOVEMENT);
        $dataSQL = $this->_db->fetchOne($sql);

        $PhanLoai  = $this->_object->getFieldByCode('LoaiXuatKho');

        if((int)$PhanLoai->getRefIOID() == 0 && $dataSQL)
        {
            $this->_object->getFieldByCode('LoaiXuatKho')->setValue($dataSQL->Ten);
            $this->_object->getFieldByCode('LoaiXuatKho')->setRefIOID($dataSQL->IOID);
        }

		$sql       = sprintf('select * from OLoaiXuatKho where IOID = %1$d',$PhanLoai->intRefIOID);
		$dataSQL   = $this->_db->fetchOne($sql);
		$loai      = ($dataSQL)?$dataSQL->Loai:'';
		$this->loai = $loai;


        if($stock
            && $loai == Qss_Lib_Extra_Const::OUTPUT_TYPE_MOVEMENT
            && (int)$this->_object->getFieldByCode('KhoChuyenDen')->getRefIOID() == 0)
        {
            $this->_object->getFieldByCode('KhoChuyenDen')->setValue($stock->KhoVatTu);
            $this->_object->getFieldByCode('KhoChuyenDen')->setRefIOID($stock->Ref_KhoVatTu);
        }

		
        $existPhieuBaoTri  = Qss_Lib_System::fieldExists('OXuatKho', 'PhieuBaoTri');
        $existKhoChuyenDen = Qss_Lib_System::fieldExists('OXuatKho', 'KhoChuyenDen');

        // Dat readonly voi cac truong thay doi theo loai xuat kho
		$this->_object->getFieldByCode('MaKH')->bReadOnly         = true;
		$this->_object->getFieldByCode('PhieuBaoTri')->bReadOnly  = true;
		$this->_object->getFieldByCode('KhoChuyenDen')->bReadOnly = true;

        // Xoa khach hang khi loai khong phai la xuat ban
        if($loai != Qss_Lib_Extra_Const::OUTPUT_TYPE_SALE)
        {
            $this->_object->getFieldByCode('MaKH')->setRefIOID(0);
            $this->_object->getFieldByCode('MaKH')->setValue('');
            $this->_object->getFieldByCode('TenKhachHang')->setRefIOID(0);
            $this->_object->getFieldByCode('TenKhachHang')->setValue('');
        }

        // Xoa kho chuyen den khi loai khong phai la chuyen kho
        if($loai != Qss_Lib_Extra_Const::OUTPUT_TYPE_MOVEMENT && $existKhoChuyenDen)
        {
            $this->_object->getFieldByCode('KhoChuyenDen')->setRefIOID(0);
            $this->_object->getFieldByCode('KhoChuyenDen')->setValue('');
        }

        // Xoa phieu bao tri khi loai khong phai la bao tri
        if($loai != Qss_Lib_Extra_Const::OUTPUT_TYPE_MAINTAIN && $existPhieuBaoTri)
        {
            $this->_object->getFieldByCode('PhieuBaoTri')->setRefIOID(0);
            $this->_object->getFieldByCode('PhieuBaoTri')->setValue('');
        }

        // Hien thi va yeu cau nhap doi voi cac truong load lai theo loai
        if( (int)$this->_object->intStatus == 1)
        {
            switch ($loai)
            {
                case Qss_Lib_Extra_Const::OUTPUT_TYPE_SALE:
                    $this->_object->getFieldByCode('MaKH')->bReadOnly = false;
                    $this->_object->getFieldByCode('MaKH')->bRequired = true;
                break;
                case Qss_Lib_Extra_Const::OUTPUT_TYPE_MAINTAIN:
                    if($existPhieuBaoTri)
                    {
                        $this->_object->getFieldByCode('PhieuBaoTri')->bReadOnly = false;
                        //$this->_object->getFieldByCode('PhieuBaoTri')->bRequired = true;
                    }
                break;
				case Qss_Lib_Extra_Const::OUTPUT_TYPE_MOVEMENT:
                    if($existKhoChuyenDen)
                    {
                        $this->_object->getFieldByCode('KhoChuyenDen')->bReadOnly = false;
                        $this->_object->getFieldByCode('KhoChuyenDen')->bRequired = true;
                    }
                break;
            }
        }
	}

    public function PhieuBaoTri()
    {
        if(Qss_Lib_System::fieldActive('OXuatKho', 'PhieuBaoTri'))
        {
            /*$PhanLoai = $this->_object->getFieldByCode('LoaiXuatKho')->intRefIOID;
            $dataSQL  = $this->_db->fetchOne(sprintf('select * from OLoaiXuatKho where IOID = %1$d',$PhanLoai));
            $loai     = ($dataSQL)?$dataSQL->Loai:'';*/

            if((int)$this->_object->intStatus == 1 && $this->loai == Qss_Lib_Extra_Const::OUTPUT_TYPE_MAINTAIN)
            {
                $this->_object->getFieldByCode('PhieuBaoTri')->arrFilters[] =
                    sprintf('
                        v.IOID in (SELECT OPhieuBaoTri.IOID
                        FROM OPhieuBaoTri
                        INNER JOIN qsiforms ON OPhieuBaoTri.IFID_M759 = qsiforms.IFID
                        WHERE qsiforms.Status = 2 or qsiforms.Status = 3 )'
                    );
            }
        }
    }

	public function Kho()
	{
		$user = Qss_Register::get('userinfo');

        if(Qss_Lib_System::formSecure('M601'))
        {
            $this->_object->getFieldByCode('Kho')->arrFilters[] = sprintf(' v.IFID_M601 in (SELECT IFID FROM qsrecordrights
						WHERE UID = %1$d and FormCode="M601")'
                ,$user->user_id);
        }

		if((int)$this->_object->intStatus == 1)
		{
			if($this->loai == Qss_Lib_Extra_Const::OUTPUT_TYPE_SCRAP)
			{
	        	$this->_object->getFieldByCode('Kho')->arrFilters[] =
	            	sprintf('
	                        v.LoaiKho = %1$s'
	                        , $this->_db->quote(Qss_Lib_Extra_Const::WAREHOUSE_TYPE_DRAFT)
	                    );
			}
			else
			{
					$this->_object->getFieldByCode('Kho')->arrFilters[] =
	            	sprintf('
	                        v.LoaiKho != %1$s'
	                        , $this->_db->quote(Qss_Lib_Extra_Const::WAREHOUSE_TYPE_DRAFT)
	                    );
			}
		}
	}
	
}