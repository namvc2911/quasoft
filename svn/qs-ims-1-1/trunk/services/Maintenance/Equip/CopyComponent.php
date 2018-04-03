<?php
class Qss_Service_Maintenance_Equip_CopyComponent extends Qss_Service_Abstract
{
    public function __doExecute($params)
    {
        $mCommon   = new Qss_Model_Extra_Extra();
        $model     = new Qss_Model_Import_Form('M705',false, false);
        $oequip    = $mCommon->getTableFetchOne('ODanhSachThietBi', sprintf('IFID_M705 = %1$d', @(int)$params['ifid']));

        if(!isset($params['ViTri']) || !count($params['ViTri']))
        {
            $this->setError();
            $this->setMessage('Cần chọn ít nhất một bộ phận để cập nhật!');
        }

        if(!isset($params['from_equip']) || !$params['from_equip'])
        {
            $this->setError();
            $this->setMessage('Thiết bị yêu cầu bắt buộc!');
        }

        if(!$this->isError())
        {
            $mList           = new Qss_Model_Maintenance_Equip_List();
            $updateSparepart = (isset($params['update_sparepart']) && $params['update_sparepart'])?true:false;
            $i               = 0;
            $j               = 0;
            $insert          = array();
            $component       = array();

            $insert['ODanhSachThietBi'][0]['MaThietBi'] = $oequip->MaThietBi;

            foreach($params['ViTri'] as $position)
            {
                $component[] = $position;
                $comLinkTemp = '';
                $copyImage   = '';

                if($params['Anh'][$i])
                {
                    // Link anh goc
                    $image  = $params['Anh'][$i]?"{$params['Anh'][$i]}":'';
                    $link   = $image?QSS_DATA_DIR.'/documents/'.$image:'';

                    if(file_exists($link))
                    {
                        // id cua anh cuoi cung
                        $comCopyImageID = uniqid();
                        $copyImage      = $comCopyImageID.'.'.$params['Anh'][$i];
                        $copyLink       = QSS_DATA_DIR.'/tmp/'.$copyImage;

                        // copy anh
                        $comLinkTemp = copy($link, $copyLink);
                    }
                }

                $insert['OCauTrucThietBi'][$i]['ViTri']         = "{$params['ViTri'][$i]}";
                $insert['OCauTrucThietBi'][$i]['BoPhan']        = "{$params['BoPhan'][$i]}";
                $insert['OCauTrucThietBi'][$i]['PhuTung']       = $params['PhuTung'][$i];
                $insert['OCauTrucThietBi'][$i]['TrucThuoc']     = "{$params['TrucThuoc'][$i]}";
                $insert['OCauTrucThietBi'][$i]['Serial']   	    = (int)$params['Ref_Serial'][$i];
                $insert['OCauTrucThietBi'][$i]['ClassHuHong']   = (int)$params['Ref_ClassHuHong'][$i];
                $insert['OCauTrucThietBi'][$i]['Anh']           = $copyImage;
                $insert['OCauTrucThietBi'][$i]['MoTa']          = $params['MoTa'][$i];
                $insert['OCauTrucThietBi'][$i]['MaSP']          = (int)$params['Ref_MaSP'][$i];
                $insert['OCauTrucThietBi'][$i]['TenSP']         = (int)$params['Ref_MaSP'][$i];
                $insert['OCauTrucThietBi'][$i]['DonViTinh']     = (int)$params['Ref_DonViTinh'][$i];
                $insert['OCauTrucThietBi'][$i]['SoLuongChuan']  = $params['SoLuongChuan'][$i];
                $insert['OCauTrucThietBi'][$i]['SoLuongHC']     = $params['SoLuongHC'][$i];
                $insert['OCauTrucThietBi'][$i]['SoNgayCanhBao'] = $params['SoNgayCanhBao'][$i];
                $insert['OCauTrucThietBi'][$i]['DacTinhKyThuat'] = (int)$params['Ref_MaSP'][$i];
                $i++;
            }

            if($updateSparepart)
            {
                $spareparts = $mList->getReplaceSparepartsByIFID($params['from_equip']);

                foreach($spareparts as $item)
                {
                    if($item->Ref_ViTri == 0 || in_array($item->ViTri, $component))
                    {
                        $insert['ODanhSachPhuTung'][$j]['ViTri']     = (int)$item->Ref_ViTri;
                        $insert['ODanhSachPhuTung'][$j]['BoPhan']    = (int)$item->Ref_ViTri;
                        $insert['ODanhSachPhuTung'][$j]['MaSP']      = (int)$item->Ref_MaSP;
                        $insert['ODanhSachPhuTung'][$j]['TenSP']     = (int)$item->Ref_MaSP;
                        $insert['ODanhSachPhuTung'][$j]['DonViTinh'] = (int)$item->Ref_DonViTinh;
                        $j++;
                    }
                }
            }

            $model->setData($insert);
            $model->generateSQL();
            $error = $model->countFormError() + $model->countObjectError();

            if($error)
            {
                // echo '<pre>'; print_r($model->getImportRows()); die;
                $this->setError();
                $this->setMessage($model->getErrorRows());
            }

//            if(count($insert))
//            {
//                $service = $this->services->Form->Manual('M705' , $params['ifid'], $insert, false);
//                if($service->isError())
//                {
//                    $this->setError();
//                    $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
//                }
//            }

            if(!$this->isError())
            {
                $this->setMessage('Cập nhật thành công!');

            }

            if(!$this->isError())
            {
                Qss_Service_Abstract::$_redirect = '/user/form/edit?ifid='.$params['ifid'].'&deptid='.$params['deptid'];
            }
        }
    }
}