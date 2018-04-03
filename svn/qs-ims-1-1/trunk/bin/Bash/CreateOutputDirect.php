<?php
class Qss_Bin_Bash_CreateOutputDirect extends Qss_Lib_Bin
{
    public function __doExecute()
    {
        $user           = Qss_Register::get('userinfo');
        $mCommon        = new Qss_Model_Extra_Extra();
        $mStock         = new Qss_Model_Warehouse_Main();
        $import         = new Qss_Model_Import_Form('M506',false, false);
        $insert         = array();
        $i              = 0;
        $objInputLines  = $mCommon->getTableFetchAll('ODanhSachNhapKho', array('IFID_M402'=>$this->_params->IFID_M402));
        $objInput       = $mCommon->getTableFetchOne('ONhapKho', array('IFID_M402'=>$this->_params->IFID_M402));
        $objXuatThang   = $mCommon->getTableFetchOne('OLoaiXuatKho', array('Loai'=>Qss_Lib_Extra_Const::OUTPUT_TYPE_DIRECT));


        if(!$objXuatThang) {
            $this->setError();
            $this->setMessage('Chưa cài đặt loại xuất thẳng!');
        }

        if($objInput) {
            $objExistOutput = $mCommon->getTableFetchOne('OXuatKho', array('IFNULL(Ref_PhieuNhapKho, 0)'=>$objInput->IOID));

            if($objExistOutput) {
                $this->setError();
                $this->setMessage('Phiếu xuất thẳng đã được tạo trước đó.<a href="'
                    . '/user/form/edit?ifid='
                    . $objExistOutput->IFID_M506
                    . '&deptid='.$objExistOutput->DeptID
                    .'"> Click vào số phiếu '. $objExistOutput->SoChungTu
                    .' để xem chi tiết. </a>
                ');

            }
            else {
                $insert["OXuatKho"][0]["Kho"]                 = @(int)$objInput->Ref_Kho;
                $insert["OXuatKho"][0]["MaKH"]                = @(int)$objInput->Ref_MaKhachHang;
                $insert["OXuatKho"][0]["TenKhachHang"]        = @(int)$objInput->Ref_MaKhachHang;
                $insert["OXuatKho"][0]["LoaiXuatKho"]         = @(int)$objXuatThang->IOID;
                $insert["OXuatKho"][0]["NgayChungTu"]         = date('Y-m-d');//$main->NgayChungTu;
                $insert["OXuatKho"][0]["SoChungTu"]           = $mStock->getOutputNoByConfig();
                $insert["OXuatKho"][0]["NgayChuyenHang"]      = date('Y-m-d');
                $insert["OXuatKho"][0]["PhieuNhapKho"]        = @(int)$objInput->IOID;

                foreach ($objInputLines as $item)
                {
                    $insert["ODanhSachXuatKho"][$i]["MaSP"]                = @(int)$item->Ref_MaSanPham;
                    $insert["ODanhSachXuatKho"][$i]["TenSP"]               = @(int)$item->Ref_TenSanPham;
                    $insert["ODanhSachXuatKho"][$i]["DonViTinh"]           = @(int)$item->Ref_DonViTinh;
                    $insert["ODanhSachXuatKho"][$i]["SoLuong"]             = @$item->SoLuong;
                    $insert["ODanhSachXuatKho"][$i]["DonGia"]              = @$item->DonGia;
                    $insert["ODanhSachXuatKho"][$i]["ThanhTien"]           = @$item->ThanhTien;
                    $i++;
                }

                $import->setData($insert);
                $import->generateSQL();
                $error = $import->countFormError() + $import->countObjectError();

                if($error)
                {
                    $this->setError();
                    $this->setMessage('Không tạo được phiếu xuất kho!');
                }
            }

        }
        else {
            $this->setError();
            $this->setMessage('Không tồn tại đơn nhập kho!');
        }

        if(!$this->isError()) {
            $newIFID     = 0;
            $importedRow = $import->getIFIDs();

            foreach ($importedRow as $item) {
                $newIFID = $item->oldIFID;
            }

            if($newIFID) {
                Qss_Service_Abstract::$_redirect = '/user/form/edit?ifid='.$newIFID.'&deptid='.$user->user_dept_id;
            }
        }
    }
}