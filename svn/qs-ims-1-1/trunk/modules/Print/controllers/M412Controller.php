<?php

function imagecreatefromfile( $filename ) {
    if (!file_exists($filename)) {
        throw new InvalidArgumentException('File "'.$filename.'" not found.');
    }
    switch ( strtolower( pathinfo( $filename, PATHINFO_EXTENSION ))) {
        case 'jpeg':
        case 'jpg':
            return imagecreatefromjpeg($filename);
            break;

        case 'png':
            return imagecreatefrompng($filename);
            break;

        case 'gif':
            return imagecreatefromgif($filename);
            break;

        default:
            throw new InvalidArgumentException('File "'.$filename.'" is not valid jpg, png or gif image.');
            break;
    }
}

class Print_M412Controller extends Qss_Lib_PrintController
{
    public function init()
    {
        parent::init();
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/print.php';
    }

    /**
     * Yeu cau mua hang (Theo mau cua anphat plastic mau 1)
     */
    public function normalAction()
    {
        $mForm    = new Qss_Model_Form();
        $appr     = $mForm->getApproverByStep($this->_form->i_IFID, 2);
        $mRequest = new Qss_Model_Purchase_Request();
        $main     = $mRequest->getRequestByIFID($this->_form->i_IFID);
        $sub      = $mRequest->getRequestLineByIFID($this->_form->i_IFID);

        $this->html->main      = $main;
        $this->html->sub       = $sub;
        $this->html->ifid      = $this->_form->i_IFID;
        $this->html->step2Appr = @$appr[0];
        $this->html->step3Appr = @$appr[1];
    }

    public function normalexcelAction()
    {
        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"Yêu cầu vật tư.xlsx\"");

        $mForm    = new Qss_Model_Form();
        $appr     = $mForm->getApproverByStep($this->_form->i_IFID, 2);
        $file     = new Qss_Model_Excel(QSS_DATA_DIR.'/template/M412/YeuCauVatTu.xlsx');
        $mainDat  = new stdClass();
        $ifid     = $this->params->requests->getParam('ifid', 0);
        $stt      = 0;
        $startAt  = 11;
        $row      = $startAt;
        $mRequest = new Qss_Model_Purchase_Request();
        $main     = $mRequest->getRequestByIFID($ifid);
        $sub      = $mRequest->getRequestLineByIFID($ifid);

        $mainDat->nguoiDeNghi = $main->NguoiDeNghi;
        $mainDat->MucDich     = $main->MucDich;
        $mainDat->ngayDeNghi  = Qss_Lib_Date::mysqltodisplay($main->Ngay);
        $mainDat->DonVi       = $main->DonViYeuCau;
        $mainDat->SoDeNghi    = $main->SoPhieu;
        $mainDat->g4          = Qss_Lib_Date::mysqltodisplay($main->NgayCanCo);

        $file->init(array('m'=>$mainDat));

        // Dien thong tin thong so ky thuat
        foreach($sub as $item)
        {
            $data       = new stdClass();
            $data->a    = ++$stt;
            $data->b    = $item->TenSP;
            $data->c    = $item->Model;
            $data->d    = $item->XuatXu;
            $data->e    = $item->DonViTinh;
            $data->f    = $item->NhaMay;
            $data->g    = Qss_Lib_Util::formatNumber($item->SoLuong);

            $file->newGridRow(array('s'=>$data), $row, 10);
            $row++;

            if($item->Anh) {
                $image = QSS_DATA_DIR . '/documents/' . $item->Anh;
                $imgSize = getimagesize($image);
                $imgSize[0] = $imgSize[0]/4;
                $imgSize[1] = $imgSize[1]/4;

                $file->setImage( ($row - 1) , 'H', $imgSize[0], $imgSize[1], $image);
            }
        }

        $footer = new stdClass();
        $footer->NguoiDeNghi = $main->NguoiDeNghi;
        $footer->KyThuat     = @(string)$appr[0]->TitleName;
        $footer->LanhDao     = @(string)$appr[1]->TitleName;
        $file->init(array('title'=>$footer));

        $file->removeRow(10);

        $file->save();
        die();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    /**
     * Yeu cau mua hang (Theo mau cua anphat plastic mau 2)
     */
    public function minAction()
    {
        $mForm    = new Qss_Model_Form();
        $appr     = $mForm->getApproverByStep($this->_form->i_IFID, 2);
        $mRequest = new Qss_Model_Purchase_Request();
        $main     = $mRequest->getRequestByIFID($this->_form->i_IFID);
        $sub      = $mRequest->getRequestLineByIFID($this->_form->i_IFID);

        $this->html->main = $main;
        $this->html->sub  = $sub;
        $this->html->ifid = $this->_form->i_IFID;
        $this->html->step2Appr = @$appr[0];
        $this->html->step3Appr = @$appr[1];
    }

    public function minexcelAction()
    {
        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"Yêu cầu vật tư.xlsx\"");

        $mForm    = new Qss_Model_Form();
        $appr     = $mForm->getApproverByStep($this->_form->i_IFID, 2);
        $file     = new Qss_Model_Excel(QSS_DATA_DIR.'/template/M412/YeuCauVatTuToiThieu.xlsx');
        $mainDat  = new stdClass();
        $ifid     = $this->params->requests->getParam('ifid', 0);
        $stt      = 0;
        $row      = 11;

        $mRequest = new Qss_Model_Purchase_Request();
        $main     = $mRequest->getRequestByIFID($ifid);
        $sub      = $mRequest->getRequestLineByIFID($ifid);

        $mainDat->nguoiDeNghi = $main->NguoiDeNghi;
        $mainDat->MucDich     = $main->MucDich;
        $mainDat->ngayDeNghi  = Qss_Lib_Date::mysqltodisplay($main->Ngay);
        $mainDat->DonVi       = $main->DonViYeuCau;
        $mainDat->SoDeNghi    = $main->SoPhieu;
        $mainDat->ngayCanCo   = Qss_Lib_Date::mysqltodisplay($main->NgayCanCo);

        $file->init(array('main'=>$mainDat));



        // Dien thong tin thong so ky thuat
        foreach($sub as $item)
        {
            $data            = new stdClass();
            $data->a   = ++$stt;
            $data->b   = "{$item->MaSP} - {$item->TenSP}";
            $data->c   = $item->XuatXu;
            $data->d = $item->DonViTinh;
            $data->e  = Qss_Lib_Util::formatNumber($item->SLToiThieu);
            $data->f   = Qss_Lib_Util::formatNumber($item->SoLuong);
            $data->g    = $item->NhaMay;
            $data->h = Qss_Lib_Date::mysqltodisplay($item->NgayCanCo);
            $data->i   = '';
            $data->j   = '';
            $data->k    = @$item->MucDich;

            $file->newGridRow(array('s'=>$data), $row, 10);
            $row++;
        }

        $footer = new stdClass();
        $footer->NguoiDeNghi  = $main->NguoiDeNghi;
        $footer->KyThuat      = @(string)$appr[0]->TitleName;
        $footer->LanhDao      = @(string)$appr[1]->TitleName;
        $file->init(array('title'=>$footer));

        $file->removeRow(10);

        $file->save();
        die();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
        $this->html->step2Appr = $mForm->getApproverByStep($this->_form->i_IFID, 2);
        $this->html->step3Appr = $mForm->getApproverByStep($this->_form->i_IFID, 3);
    }


    /**
     * Yeu cau mua hang (Theo mau cua anphat plastic mau 2)
     */
    public function fixAction()
    {
        $mMainReq = Qss_Model_Db::Table('OYeuCauMuaSam');
        $mMainReq->where(sprintf('IFID_M412 = %1$d', $this->_form->i_IFID));
        $oMainReq = $mMainReq->fetchOne();
        $mForm    = new Qss_Model_Form();
        $appr     = $mForm->getApproverByStep($this->_form->i_IFID, 2);
        $mRequest = new Qss_Model_Purchase_Request();

        $this->html->main      = $oMainReq;
        $this->html->sub       = $mRequest->getRequestLineByIFID($this->_form->i_IFID);
        $this->html->ifid      = $this->_form->i_IFID;
        $this->html->step2Appr = @$appr[0];
        $this->html->step3Appr = @$appr[1];
    }

    public function fixexcelAction()
    {
        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"Yêu cầu vật tư.xlsx\"");


        $mMainReq = Qss_Model_Db::Table('OYeuCauMuaSam');
        $mMainReq->where(sprintf('IFID_M412 = %1$d', $this->_form->i_IFID));
        $mForm    = new Qss_Model_Form();
        $appr     = $mForm->getApproverByStep($this->_form->i_IFID, 2);
        $file     = new Qss_Model_Excel(QSS_DATA_DIR.'/template/M412/DeNghiSuaVatTu.xlsx');
        $mainDat  = new stdClass();
        $ifid     = $this->params->requests->getParam('ifid', 0);
        $row      = 10;

        $mMainReq = Qss_Model_Db::Table('OYeuCauMuaSam');
        $mMainReq->where(sprintf('IFID_M412 = %1$d', $ifid));
        $mRequest = new Qss_Model_Purchase_Request();
        $main     = $mMainReq->fetchOne();
        $sub      = $mRequest->getRequestLineByIFID($ifid);

        $mainDat->NgayGui  = Qss_Lib_Date::mysqltodisplay($main->Ngay);
        $mainDat->NhaMay   = $main->DonViYeuCau;
        $mainDat->SoDeNghi   = $main->SoPhieu;

        $file->init(array('main'=>$mainDat));

        // Dien thong tin thong so ky thuat
        foreach($sub as $item)
        {
            $data                  = new stdClass();
            $data->a        = $item->TenSP;
            $data->b  = $item->DacTinhKyThuat;
            $data->c          = $item->XuatXu;
            $data->d       = $item->DonViTinh;
            $data->e         = Qss_Lib_Util::formatNumber($item->SoLuong);
            $data->f       = $item->NhaMay;
            $data->g         = 'Bên ngoài';
            $data->h         = $item->MucDich;
            $data->i   = $item->NgayCanCo?Qss_Lib_Date::mysqltodisplay($item->NgayCanCo):'';

            $file->newGridRow(array('sub'=>$data), $row, 9);
            $row++;
        }

        $footer = new stdClass();
        $footer->NguoiDeNghi  = $main->NguoiDeNghi;
        $footer->KyThuat      = @(string)$appr[0]->TitleName;
        $footer->LanhDao      = @(string)$appr[1]->TitleName;
        $file->init(array('title'=>$footer));

        $file->removeRow(9);

        $file->save();
        die();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}

?>