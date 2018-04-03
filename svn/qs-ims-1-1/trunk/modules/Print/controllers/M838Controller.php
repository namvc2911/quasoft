<?php

class Print_M838Controller extends Qss_Lib_PrintController
{
    public function init()
    {
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/print.php';
        parent::init();
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/print.php';
    }

    /**
     * Mẫu in quasoft
     */
    public function template1Action()
    {
        $this->html->data = $this->_params;
        $mTable = Qss_Model_Db::Table('OKeHoachBaoTri');
        $mTable->where($mTable->ifnullNumber('Ref_KeHoachTongThe', $this->_params->IOID));
        $this->html->detail = $mTable->fetchAll();
    }

    public function template1excelAction()
    {
        $ifid   = $this->params->requests->getParam('ifid', 0);
        $file   = new Qss_Model_Excel(Qss_Lib_System::getTemplateFile('M838', 'template1.xlsx'));
        $mTable = Qss_Model_Db::Table('OKeHoachTongThe');
        $mTable->where($mTable->ifnullNumber('IFID_M838', $ifid));
        $main   = $mTable->fetchOne();
        $mTable = Qss_Model_Db::Table('OKeHoachBaoTri');
        $mTable->where($mTable->ifnullNumber('Ref_KeHoachTongThe', @(int)$main->IOID));
        $detail = $mTable->fetchAll();
        $eMain  = new stdClass();
        $row    = 12; // Dong bat dau in du lieu mat hang

        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"Kế hoạch tổng thể ".@$main->Ma.".xlsx\"");

        $eMain->a3 = date('d-m-Y');
        $eMain->d2 = @$main->Ma; //Mã KH:
        $eMain->d3 = Qss_Lib_Date::mysqltodisplay(@$main->NgayBatDau); //Từ ngày:
        $eMain->d4 = @$main->NguoiTao; //Người tạo:
        $eMain->d5 = @$main->NguoiPheDuyet; //Người phê duyệt:
        $eMain->h2 = @$main->Ten; //Tên KH:
        $eMain->h3 = Qss_Lib_Date::mysqltodisplay(@$main->NgayKetThuc); //Đến ngày:
        $eMain->h4 = Qss_Lib_Date::mysqltodisplay(@$main->NgayTao); //Ngày tạo:
        $eMain->h5 = Qss_Lib_Date::mysqltodisplay(@$main->NgayPheDuyet); //Ngày phê duyệt:

        $file->init(array('m'=>$eMain));

        foreach($detail as $item)
        {
            $aliasLoaiBaoTri = '';
            if($aliasLoaiBaoTri != '' && is_numeric($item->LoaiBaoTri)) {
                $aliasLoaiBaoTri = 'Ref_LoaiBaoTri';
            }
            else {
                $aliasLoaiBaoTri = 'LoaiBaoTri';
            }

            $data    = new stdClass();
            $data->a = Qss_Lib_Date::mysqltodisplay($item->NgayBatDau);// Ngày bắt đầu
            $data->b = Qss_Lib_Date::mysqltodisplay($item->NgayKetThuc);//Ngày kết thúc
            $data->c = @$item->MaKhuVuc;//Khu vực
            $data->d = $item->MaThietBi;//Mã thiết bị
            $data->e = $item->TenThietBi;//Tên thiết bị
            $data->f = $item->BoPhan;//Bộ phận
            $data->g = $item->{$aliasLoaiBaoTri};//Loại bảo trì
            $data->h = $item->ChuKy;//Chu kỳ
            $data->i = $item->MoTa;//Tên công việc
            $data->j = @(int)$item->Ngoai?'':'v';//Nội bộ
            $file->newGridRow(array('s'=>$data), $row, 11);
            $row++;
        }

        $file->removeRow(11);
        $file->save();
        die();

        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }


    public function saveExcelTemplate1Action()
    {
        $document = new Qss_Model_Document();
        $name =  $this->params->requests->getParam('name');
        if(!$name)
        {
            $name =  'Report: ' . date('Y-m-d h:i:s');
        }
        $data = array('id'=>0,
            'docno'=>uniqid(),
            'name'=>$name,
            'ext'=>'xlsx',
            'size'=>0,
            'uid'=>$this->_user->user_id,
            'folder'=>'Excel reports');
        $id = $document->save($data);
        if(!is_dir(QSS_DATA_DIR . "/documents/"))
        {
            mkdir(QSS_DATA_DIR . "/documents/");
        }
        $destfile = QSS_DATA_DIR . "/documents/" . $id . ".xlsx";

        $ifid   = $this->params->requests->getParam('ifid', 0);


        $file   = new Qss_Model_Excel(Qss_Lib_System::getTemplateFile('M838', 'template1.xlsx'));
        $mTable = Qss_Model_Db::Table('OKeHoachTongThe');
        $mTable->where($mTable->ifnullNumber('IFID_M838', $ifid));
        $main   = $mTable->fetchOne();
        $mTable = Qss_Model_Db::Table('OKeHoachBaoTri');
        $mTable->where($mTable->ifnullNumber('Ref_KeHoachTongThe', @(int)$main->IOID));
        $detail = $mTable->fetchAll();
        $eMain  = new stdClass();
        $row    = 12; // Dong bat dau in du lieu mat hang

        $eMain->a3 = date('d-m-Y');
        $eMain->d2 = @$main->Ma; //Mã KH:
        $eMain->d3 = Qss_Lib_Date::mysqltodisplay(@$main->NgayBatDau); //Từ ngày:
        $eMain->d4 = @$main->NguoiTao; //Người tạo:
        $eMain->d5 = @$main->NguoiPheDuyet; //Người phê duyệt:
        $eMain->h2 = @$main->Ten; //Tên KH:
        $eMain->h3 = Qss_Lib_Date::mysqltodisplay(@$main->NgayKetThuc); //Đến ngày:
        $eMain->h4 = Qss_Lib_Date::mysqltodisplay(@$main->NgayTao); //Ngày tạo:
        $eMain->h5 = Qss_Lib_Date::mysqltodisplay(@$main->NgayPheDuyet); //Ngày phê duyệt:

        $file->init(array('m'=>$eMain));

        foreach($detail as $item)
        {
            $aliasLoaiBaoTri = '';
            if($aliasLoaiBaoTri != '' && is_numeric($item->LoaiBaoTri)) {
                $aliasLoaiBaoTri = 'Ref_LoaiBaoTri';
            }
            else {
                $aliasLoaiBaoTri = 'LoaiBaoTri';
            }

            $data    = new stdClass();
            $data->a = Qss_Lib_Date::mysqltodisplay($item->NgayBatDau);// Ngày bắt đầu
            $data->b = Qss_Lib_Date::mysqltodisplay($item->NgayKetThuc);//Ngày kết thúc
            $data->c = @$item->MaKhuVuc;//Khu vực
            $data->d = $item->MaThietBi;//Mã thiết bị
            $data->e = $item->TenThietBi;//Tên thiết bị
            $data->f = $item->BoPhan;//Bộ phận
            $data->g = $item->{$aliasLoaiBaoTri};//Loại bảo trì
            $data->h = $item->ChuKy;//Chu kỳ
            $data->i = $item->MoTa;//Tên công việc
            $data->j = @(int)$item->Ngoai?'':'v';//Nội bộ
            $file->newGridRow(array('s'=>$data), $row, 11);
            $row++;
        }

        $file->removeRow(11);
        $file->save($destfile);

        $data = array('id'=>$id,
            'docno'=>uniqid(),
            'name'=>$name,
            'ext'=>'xlsx',
            'size'=>(int) @filesize($destfile),
            'uid'=>$this->_user->user_id,
            'folder'=>'Excel reports');
        $document->save($data);
        echo (Qss_Json::encode(array('error'=>false)));
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}

?>