<?php
class Qss_Bin_Onload_ONhapKho extends Qss_Lib_Onload
{
	/**
	 * onInsert
	 */
	var $loai;
	public function __doExecute()
	{
		parent::__doExecute();
		$PhanLoai = $this->_object->getFieldByCode('LoaiNhapKho');
		$sql = sprintf('select * from OLoaiNhapKho where IOID = %1$d',$PhanLoai->intRefIOID);
		$dataSQL = $this->_db->fetchOne($sql);
		$loai = '';
		if($dataSQL)
		{
			$loai = $dataSQL->Loai;
		}
		$this->loai = $loai;
		$this->_object->getFieldByCode('MaNCC')->bReadOnly = true;
		$this->_object->getFieldByCode('PhieuXuatKho')->bReadOnly = true;
		//$this->_object->getFieldByCode('DonViThucHien')->bReadOnly = true;
        
        if($loai != 'MUAHANG')
        {
            $this->_object->getFieldByCode('MaNCC')->setRefIOID(0);
            $this->_object->getFieldByCode('TenNCC')->setRefIOID(0);
        }        
        
        if($this->_object->intStatus == 1)
        {
            switch ($loai)
            {
                case Qss_Lib_Extra_Const::INPUT_TYPE_PURCHASE://'NHAPMUA';
                    $this->_object->getFieldByCode('MaNCC')->bReadOnly = false;
                    $this->_object->getFieldByCode('MaNCC')->bRequired = true;
                break;
                case Qss_Lib_Extra_Const::INPUT_TYPE_RETURN://'TRALAI';
				case Qss_Lib_Extra_Const::INPUT_TYPE_MOVEMENT:
                	 $this->_object->getFieldByCode('PhieuXuatKho')->bReadOnly = false;
					 $this->_object->getFieldByCode('PhieuXuatKho')->bRequired = true;
                    //$this->_object->getFieldByCode('DonViThucHien')->bReadOnly = false;
                    //$this->_object->getFieldByCode('DonViThucHien')->bRequired = true;
                break;
                default:
                break;
            }
        }
	}

	/**
	 * PhieuXuatKho()
	 * Chi lay phieu da xuat kho va neu co phieu bao tri thi phai la phieu bao tri da ban hanh hoac da hoan thanh
	 */
	public function PhieuXuatKho()
	{
		if(Qss_Lib_System::fieldActive('ONhapKho', 'PhieuXuatKho'))
		{
			/*$PhanLoai = $this->_object->getFieldByCode('LoaiNhapKho')->intRefIOID;
			$dataSQL  = $this->_db->fetchOne(sprintf('select * from OLoaiNhapKho where IOID = %1$d',$PhanLoai));
			$loai     = ($dataSQL)?$dataSQL->Loai:'';*/

			$sql      = ' v.IOID in ( ';
			$sql     .= sprintf('
                        SELECT OXuatKho.IOID
                        FROM OXuatKho
                        INNER JOIN qsiforms ON OXuatKho.IFID_M506 = qsiforms.IFID
                        LEFT JOIN OPhieuBaoTri ON ifnull(OXuatKho.Ref_PhieuBaoTri, 0) = ifnull(OPhieuBaoTri.IOID, 0)
                        LEFT JOIN qsiforms AS qsiforms2 ON ifnull(OPhieuBaoTri.IFID_M759, 0) = ifnull(qsiforms2.IFID, 0)
                        WHERE qsiforms.Status = 2 ');
			if(Qss_Lib_System::fieldActive('OXuatKho', 'PhieuBaoTri')
				&& (int)$this->_object->intStatus == 1
				&& $this->loai == Qss_Lib_Extra_Const::INPUT_TYPE_RETURN)
			{
				$sql .= ' AND ( ifnull(OXuatKho.Ref_PhieuBaoTri, 0) = 0 OR ifnull(qsiforms2.Status, 0) in (2,3)) ';
			}
			$sql     .= ' )';

			$this->_object->getFieldByCode('PhieuXuatKho')->arrFilters[] = $sql;
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
			if($this->loai == Qss_Lib_Extra_Const::INPUT_TYPE_SCRAP)
			{
	        	$this->_object->getFieldByCode('Kho')->arrFilters[] =
	            	sprintf('
	                        ifnull(v.LoaiKho,"") = %1$s'
	                        , $this->_db->quote(Qss_Lib_Extra_Const::WAREHOUSE_TYPE_DRAFT)
	                    );
			}
			else
			{
					$this->_object->getFieldByCode('Kho')->arrFilters[] =
	            	sprintf('
	                        ifnull(v.LoaiKho,"") != %1$s'
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
            && Qss_Lib_System::fieldActive('ONhapKho', 'SoYeuCau')) {

            $refKho = $this->_object->getFieldByCode('Kho')->getRefIOID();
            $where  = '';

            if(Qss_Lib_System::fieldActive('ODuAn', 'KhoVatTu')) {
                $where .= sprintf(' and IFNULL(Ref_KhoVatTu, 0) = %1$d ', $refKho);
            }

            if(Qss_Lib_System::fieldActive('ODuAn', 'KhoCongCu')) {
                $where .= sprintf(' and IFNULL(Ref_KhoCongCu, 0) = %1$d  ', $refKho);
            }

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