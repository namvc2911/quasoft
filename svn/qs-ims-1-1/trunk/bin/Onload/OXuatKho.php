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
		$PhanLoai  = $this->_object->getFieldByCode('LoaiXuatKho');
		$sql       = sprintf('select * from OLoaiXuatKho where IOID = %1$d',$PhanLoai->intRefIOID);
		$dataSQL   = $this->_db->fetchOne($sql);
		$loai      = ($dataSQL)?$dataSQL->Loai:'';
		$this->loai = $loai; 
		
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

    /**
     * Lọc số phiếu yêu cầu theo tình trạng đã duyệt và phân quyền theo dự án  (POS)
     */
	public function SoYeuCau() {
        $user = Qss_Register::get('userinfo');

	    if(Qss_Lib_System::formSecure('M803')
            && Qss_Lib_System::fieldActive('OXuatKho', 'SoYeuCau')) {

	        $refKho = $this->_object->getFieldByCode('KhoChuyenDen')->getRefIOID();
	        $where  = '';

	        $temp   = '';

	        if(Qss_Lib_System::fieldActive('ODuAn', 'KhoVatTu')) {
                $temp .= sprintf(' IFNULL(Ref_KhoVatTu, 0) = %1$d ', $refKho);
            }

            $temp .= $temp?' or ':'';

            if(Qss_Lib_System::fieldActive('ODuAn', 'KhoCongCu')) {
                $temp .= sprintf('  IFNULL(Ref_KhoCongCu, 0) = %1$d  ', $refKho);
            }

            $where = $temp?' and ('.$temp.')':'';

            $this->_object->getFieldByCode('SoYeuCau')->arrFilters[] = sprintf('
                 (
                    v.IOID in (
                        SELECT OYeuCauTrangThietBiVatTu.IOID 
                        FROM ODuAn
                        inner join qsrecordrights on ODuAn.IFID_M803 = qsrecordrights.IFID
                        inner join OYeuCauTrangThietBiVatTu ON OYeuCauTrangThietBiVatTu.Ref_DuAn = ODuAn.IOID                        
                        inner join qsiforms ON OYeuCauTrangThietBiVatTu.IFID_M751 = qsiforms.IFID
                        WHERE qsrecordrights.UID = %1$d AND qsiforms.Status = 3 %2$s
                    ) 
                )
            ',$user->user_id, $where);
        }
    }
	
}