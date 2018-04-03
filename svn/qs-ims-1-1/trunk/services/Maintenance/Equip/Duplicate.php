<?php
class Qss_Service_Maintenance_Equip_Duplicate extends Qss_Service_Abstract
{
    public function __doExecute($params)
    {
        $mImport     = new Qss_Model_Import_Form('M705', false, false);
        $common      = new Qss_Model_Extra_Extra();
        $linkTemp    = '';
        $comLinkTemp = '';
        $insert      = array();

        $line      = $common->getTable(array('*'), 'ODanhSachThietBi', array('IFID_M705'=>$params['ifid']), array(), 1, 1);
        $comArr    = array();
        $copyImage = '';
        $equipCode = $line->MaThietBi.'_'.uniqid().'_copy';

        if($line && $line->Anh)
        {
            // Link anh goc
            $image  = $line?"{$line->Anh}":'';
            $link   = $image?QSS_DATA_DIR.'/documents/'.$image:'';

            if(file_exists($link))
            {
                // id cua anh cuoi cung
                $copyImageID = uniqid();
                $copyImage = $copyImageID.'.'.$line->Anh;
                $copyLink  = QSS_DATA_DIR.'/tmp/'.$copyImage;

                // copy anh
                $linkTemp = copy($link, $copyLink);
            }
        }

        $insert['ODanhSachThietBi'][0]['MaThietBi']        = $equipCode;
        $insert['ODanhSachThietBi'][0]['LoaiThietBi']      = (int)$line->Ref_LoaiThietBi;
        $insert['ODanhSachThietBi'][0]['TenThietBi']       = $line->TenThietBi;
        $insert['ODanhSachThietBi'][0]['NhomThietBi']      = (int)$line->Ref_NhomThietBi;
        $insert['ODanhSachThietBi'][0]['NamSanXuat']       = $line->NamSanXuat;
        $insert['ODanhSachThietBi'][0]['Serial']           = $line->Serial;
        $insert['ODanhSachThietBi'][0]['Model']            = $line->Model;
        $insert['ODanhSachThietBi'][0]['XuatXu']           = $line->XuatXu;
        $insert['ODanhSachThietBi'][0]['TrucThuoc']        = (int)$line->Ref_TrucThuoc;
        $insert['ODanhSachThietBi'][0]['DacTinhKT']        = $line->DacTinhKT;
        $insert['ODanhSachThietBi'][0]['NgayNgung']        = $line->NgayNgung;
        $insert['ODanhSachThietBi'][0]['TrangThai']        = $line->TrangThai;
        $insert['ODanhSachThietBi'][0]['ClassHuHong']      = $line->ClassHuHong;

        $insert['ODanhSachThietBi'][0]['MaTaiSan']         = $line->MaTaiSan?$line->MaTaiSan.'_'.uniqid().'_copy':'';
        $insert['ODanhSachThietBi'][0]['NgayMua']          = $line->NgayMua;
        $insert['ODanhSachThietBi'][0]['HangBaoHanh']      = $line->HangBaoHanh;
        $insert['ODanhSachThietBi'][0]['HanBaoHanh']       = $line->HanBaoHanh;
        $insert['ODanhSachThietBi'][0]['NgayDuaVaoSuDung'] = $line->NgayDuaVaoSuDung;
        $insert['ODanhSachThietBi'][0]['NguyenGia']        = Qss_Lib_Util::formatMoney($line->NguyenGia);
        $insert['ODanhSachThietBi'][0]['GiaTriThanhLy']    = $line->GiaTriThanhLy;
        $insert['ODanhSachThietBi'][0]['TinhKhauHao']      = $line->TinhKhauHao;
        $insert['ODanhSachThietBi'][0]['ChiSo']            = $line->ChiSo;
        $insert['ODanhSachThietBi'][0]['GiaTri']           = $line->GiaTri;
        $insert['ODanhSachThietBi'][0]['TrungTamChiPhi']   = $line->TrungTamChiPhi;

        $insert['ODanhSachThietBi'][0]['DayChuyen']        = $line->DayChuyen;
        $insert['ODanhSachThietBi'][0]['LichLamViec']      = $line->LichLamViec;
        $insert['ODanhSachThietBi'][0]['MaKhuVuc']         = $line->MaKhuVuc;
        $insert['ODanhSachThietBi'][0]['QuanLy']           = $line->QuanLy;
        $insert['ODanhSachThietBi'][0]['DuAn']             = $line->DuAn;
        $insert['ODanhSachThietBi'][0]['Anh']              = $copyImage;


        // Cau truc thiet bi
        if(isset($params['CauTruc']) && Qss_Lib_System::objectInForm('M705', 'OCauTrucThietBi'))
        {
            $component = $common->getTable(array('*'), 'OCauTrucThietBi', array('IFID_M705'=>$params['ifid']), array('lft'));
            $comIndex  = 0;

            foreach($component as $com)
            {

                $comArr[]    = $com->IOID;
                $comLinkTemp = '';
                $copyImage   = '';

                if($com->Anh)
                {
                    // Link anh goc
                    $image  = $com?"{$com->Anh}":'';
                    $link   = $image?QSS_DATA_DIR.'/documents/'.$image:'';

                    if(file_exists($link))
                    {
                        // id cua anh cuoi cung
                        $comCopyImageID = uniqid();
                        $copyImage      = $comCopyImageID.'.'.$com->Anh;
                        $copyLink       = QSS_DATA_DIR.'/tmp/'.$copyImage;

                        // copy anh
                        $comLinkTemp = copy($link, $copyLink);
                    }
                }

                $insert['OCauTrucThietBi'][$comIndex]['ViTri']        = $com->ViTri;
                $insert['OCauTrucThietBi'][$comIndex]['BoPhan']       = $com->BoPhan;
                $insert['OCauTrucThietBi'][$comIndex]['PhuTung']      = @(int)$com->PhuTung;
                $insert['OCauTrucThietBi'][$comIndex]['MaSP']         = @(int)$com->Ref_MaSP;
                $insert['OCauTrucThietBi'][$comIndex]['TenSP']        = @(int)$com->Ref_MaSP;
                $insert['OCauTrucThietBi'][$comIndex]['Serial']       = @(int)$com->Ref_Serial;
                $insert['OCauTrucThietBi'][$comIndex]['DonViTinh']    = @(int)$com->Ref_DonViTinh;
                $insert['OCauTrucThietBi'][$comIndex]['TrucThuoc']    = (string)$com->TrucThuoc;
                $insert['OCauTrucThietBi'][$comIndex]['Anh']          = $copyImage;
                $insert['OCauTrucThietBi'][$comIndex]['ClassHuHong']  = (int)$com->Ref_ClassHuHong;
                $insert['OCauTrucThietBi'][$comIndex]['SoLuongChuan'] = @$com->SoLuongChuan;
                $insert['OCauTrucThietBi'][$comIndex]['SoLuongHC']    = @$com->SoLuongHC;
                $insert['OCauTrucThietBi'][$comIndex]['SoNgayCanhBao']= @$com->SoNgayCanhBao;
                $insert['OCauTrucThietBi'][$comIndex]['MoTa']         = @$com->MoTa;
                $insert['OCauTrucThietBi'][$comIndex]['DacTinhKyThuat'] = @(int)$com->Ref_MaSP;
                $comIndex++;
            }
        }

        // Phu tung
        if(isset($params['PhuTung']) && Qss_Lib_System::objectInForm('M705', 'ODanhSachPhuTung'))
        {
            $sparepart      = $common->getTable(array('*'), 'ODanhSachPhuTung', array('IFID_M705'=>$params['ifid']));
            $sparepartIndex = 0;

            foreach($sparepart as $spare)
            {
                if((int)$spare->Ref_ViTri == 0 ||  in_array($spare->Ref_ViTri, $comArr))
                {
                    $insert['ODanhSachPhuTung'][$sparepartIndex]['ViTri']         = @$spare->ViTri;
                    $insert['ODanhSachPhuTung'][$sparepartIndex]['BoPhan']        = @$spare->BoPhan;
                    $insert['ODanhSachPhuTung'][$sparepartIndex]['MaSP']          = @(int)$spare->Ref_MaSP;
                    $insert['ODanhSachPhuTung'][$sparepartIndex]['TenSP']         = @(int)$spare->Ref_MaSP;
                    $insert['ODanhSachPhuTung'][$sparepartIndex]['DonViTinh']     = @(int)$spare->Ref_DonViTinh;
                    $sparepartIndex++;
                }
            }
        }

        // DiemDo
        if(isset($params['DiemDo']) && Qss_Lib_System::objectInForm('M705', 'ODanhSachDiemDo'))
        {
            $techIndex = 0;
            $technote  = $common->getTable(array('*'), 'ODanhSachDiemDo', array('IFID_M705'=>$params['ifid']));

            foreach ($technote as $te) {
                $insert['ODanhSachDiemDo'][$techIndex]['Ma']          = @$te->Ma;
                $insert['ODanhSachDiemDo'][$techIndex]['MaThietBi']   = @(int)$te->Ref_MaThietBi;
                $insert['ODanhSachDiemDo'][$techIndex]['BoPhan']      = @$te->BoPhan;
                $insert['ODanhSachDiemDo'][$techIndex]['ChiSo']       = @(int)$te->Ref_ChiSo;
                $insert['ODanhSachDiemDo'][$techIndex]['GioiHanDuoi'] = @$te->GioiHanDuoi;
                $insert['ODanhSachDiemDo'][$techIndex]['GioiHanTren'] = @$te->GioiHanTren;
                $insert['ODanhSachDiemDo'][$techIndex]['ThuCong']     = @$te->ThuCong;
                $insert['ODanhSachDiemDo'][$techIndex]['Ky']          = @(int)$te->Ref_Ky;
                $insert['ODanhSachDiemDo'][$techIndex]['Thu']         = @$te->Thu;
                $insert['ODanhSachDiemDo'][$techIndex]['Ngay']        = @$te->Ngay;
                $insert['ODanhSachDiemDo'][$techIndex]['Thang']       = @$te->Thang;
                $insert['ODanhSachDiemDo'][$techIndex]['LapLai']      = @$te->LapLai?$te->LapLai:1;
                $insert['ODanhSachDiemDo'][$techIndex]['GhiChu']      = @$te->GhiChu;
                $techIndex++;
            }
        }

        // Dac tinh thiet bi
        if(isset($params['DacTinh']) && Qss_Lib_System::objectInForm('M705', 'ODacTinhThietBi'))
        {
            $techIndex = 0;
            $dacTinh   = $common->getTable(array('*'), 'ODacTinhThietBi', array('IFID_M705'=>$params['ifid']));

            foreach($dacTinh as $te)
            {
                $insert['ODacTinhThietBi'][$techIndex]['DonViTinh'] = @(int)$te->Ref_DonViTinh;
                $insert['ODacTinhThietBi'][$techIndex]['GiaTri']    = @$te->GiaTri;
                $insert['ODacTinhThietBi'][$techIndex]['Ten']       = @(int)$te->Ref_Ten;
                $techIndex++;
            }
        }
        
        // bao hiem thiet bi
        if(isset($params['BaoHiem']) && Qss_Lib_System::objectInForm('M705', 'BaoHiem'))
        {
            $techIndex = 0;
            $baoHiem   = $common->getTable(array('*'), 'OBaoHiemThietBi', array('IFID_M705'=>$params['ifid']));

            foreach($baoHiem as $te)
            {
                $insert['OBaoHiemThietBi'][$techIndex]['NgayDongBaoHiem'] = @$te->NgayDongBaoHiem;
                $insert['OBaoHiemThietBi'][$techIndex]['NgayHetHan']      = @$te->NgayHetHan;
                $insert['OBaoHiemThietBi'][$techIndex]['DonViBaoHiem']    = @(int)$te->Ref_DonViBaoHiem;
                $insert['OBaoHiemThietBi'][$techIndex]['SoTienDong']      = @$te->SoTienDong;
                $insert['OBaoHiemThietBi'][$techIndex]['NoiDung']         = @$te->NoiDung;
                $techIndex++;
            }
        }

        // echo '<pre>'; print_r($insert); die;

        $mImport->setData($insert);
        $mImport->generateSQL();

        $errorForm   = $mImport->countFormError();
        $errorObject = $mImport->countObjectError();
        $error       = $errorForm + $errorObject;

        // echo '<pre>'; print_r($mImport->getErrorRows()); die;

//        if($error)
//        {
//            $this->setError();
//            $this->setMessage('Có '.$error.' dòng lỗi. Nhân đôi thiết bị không thành công!');
//        }

        if(file_exists($linkTemp)) unlink($linkTemp);
        if(file_exists($comLinkTemp)) unlink($comLinkTemp);

        if(!$this->isError())
        {
            $new = $common->getTableFetchOne('ODanhSachThietBi', array('MaThietBi'=>$equipCode));

            if($new)
            {
                if( !isset($params['redirect']) || $params['redirect']  ) // mặc định redirect
                {
                    Qss_Service_Abstract::$_redirect = '/user/form/edit?ifid='.$new->IFID_M705.'&deptid=1';
                }
            }
            else
            {
                $this->setMessage('Nhân đôi thiết bị không thành công!');
                $this->setError();
            }
        }




    }
}