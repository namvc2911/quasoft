<?php
class Static_M858Controller extends Qss_Lib_Controller
{
    public $existsInvestor;
    public $existsQC;
    public $investor;
    public $representativeInvestor;
    public $representativeTech;
    public $allPass;

    public function init()
    {
        $this->i_SecurityLevel = 15;
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';

        $this->existsInvestor = Qss_Lib_System::fieldActive('OPhieuBaoTri', 'BenKhachHang')?true:false;
        $this->existsQC = Qss_Lib_System::fieldActive('OPhieuBaoTri', 'Dat')?true:false;
        $this->investor       = '';
        $this->representativeInvestor = array();
        $this->representativeTech = array();
        $this->allPass = 1; // 0 tat ca khong dat, 1 tat ca dat
    }

    public function indexAction()
    {

    }

    public function showAction()
    {
        $orders    = $this->params->requests->getParam('orders', array());
        $other     = $this->params->requests->getParam('other', 0);
        $repOther1 = $this->params->requests->getParam('repOther1', 0);
        $repOther2 = $this->params->requests->getParam('repOther1', 0);
        $start     = $this->params->requests->getParam('start', '');
        $end       = $this->params->requests->getParam('end', '');
        $oOrders   = $this->getOrdersByIOID($orders);
        $mStart    = date_create($start);
        $mEnd      = date_create($end);

        $mTable = Qss_Model_Db::Table('ODoiTac');
        $mTable->where(sprintf('IFID_M118 = %1$d', $other));
        $oOther = $mTable->fetchOne();

        $mTable = Qss_Model_Db::Table('OLienHeCaNhan');
        $mTable->where(sprintf('IOID = %1$d', $repOther1));
        $oRepOther1 = $mTable->fetchOne();

        $mTable = Qss_Model_Db::Table('OLienHeCaNhan');
        $mTable->where(sprintf('IOID = %1$d', $repOther2));
        $oRepOther2 = $mTable->fetchOne();

        $this->parseOrders($oOrders);

        $this->html->orders   = $oOrders;
        $this->html->investor = $this->investor;
        $this->html->representativeInvestor = $this->representativeInvestor;
        $this->html->representativeTech = $this->representativeTech;
        $this->html->otherCompany = $oOther?$oOther->TenDoiTac:'';
        $this->html->representativeOther1 = $oRepOther1;
        $this->html->representativeOther2 = $oRepOther2;
        $this->html->existsQC = $this->existsQC;
        $this->html->allPass  = $this->allPass;
        $this->html->mStart   = $start?$mStart:'';
        $this->html->mEnd     = $end?$mEnd:'';
    }

    public function excelAction()
    {
        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"Biên bản nghiệm thu.xlsx\"");

        $file      = new Qss_Model_Excel(Qss_Lib_System::getTemplateFile('M858', 'SDM_BienBanNghiemThu.xlsx'));
        $orders    = $this->params->requests->getParam('orders', array());
        $other     = $this->params->requests->getParam('other', 0);
        $repOther1 = $this->params->requests->getParam('repOther1', 0);
        $repOther2 = $this->params->requests->getParam('repOther1', 0);
        $start     = $this->params->requests->getParam('start', '');
        $end       = $this->params->requests->getParam('end', '');
        $oOrders   = $this->getOrdersByIOID($orders);
        $mStart    = date_create($start);
        $mEnd      = date_create($end);

        $mTable = Qss_Model_Db::Table('ODoiTac');
        $mTable->where(sprintf('IFID_M118 = %1$d', $other));
        $oOther = $mTable->fetchOne();

        $mTable = Qss_Model_Db::Table('OLienHeCaNhan');
        $mTable->where(sprintf('IOID = %1$d', $repOther1));
        $oRepOther1 = $mTable->fetchOne();

        $mTable = Qss_Model_Db::Table('OLienHeCaNhan');
        $mTable->where(sprintf('IOID = %1$d', $repOther2));
        $oRepOther2 = $mTable->fetchOne();

        $this->parseOrders($oOrders);

        $stt    = 0;
        $row    = 7;
        $eMain  = new stdClass();

        $HoTen1    = @$oRepOther1->HoTen.str_repeat(' ', 50 - strlen(@$oRepOther1->HoTen));
        $ChucDanh1 = @$oRepOther1->ChucDanh.str_repeat(' ', 50 - strlen(@$oRepOther1->ChucDanh));
        $HoTen2    = @$oRepOther2->HoTen.str_repeat(' ', 50 - strlen(@$oRepOther2->HoTen));
        $ChucDanh2 = @$oRepOther2->ChucDanh.str_repeat(' ', 50 - strlen(@$oRepOther2->ChucDanh));

        $eMain->DaiDienChuDauTu      = $this->investor;
        $eMain->DaiDienDonViLienQuan = $oOther?$oOther->TenDoiTac:'';
        $eMain->DaiDienLienQuan1     = "    - Ông (Bà) : ".$HoTen1." Chức vụ : ".$ChucDanh1;
        $eMain->DaiDienLienQuan2     = "    - Ông (Bà) : ".$HoTen2." Chức vụ : ".$ChucDanh2;
        $eMain->DanhGiaSauBaoTri     = ($this->existsQC && $this->allPass)?'Đạt':'';
        $eMain->BatDau               = $start?"      h       ngày  ".$mStart->format('d')."   tháng  ".$mStart->format('m')."   năm ".$mStart->format('Y'):"      h       ngày       tháng       năm      ";
        $eMain->KetThuc              = $end?"      h       ngày  ".$mEnd->format('d')."   tháng  ".$mEnd->format('m')."   năm ".$mEnd->format('Y'):"      h       ngày       tháng       năm      ";"      h       ngày       tháng       năm      ";

        $file->init(array('m'=>$eMain));

        $header = $file->wsMain->getHeaderFooter()->getOddHeader();
        $header = str_replace('{m:NgayBanHanh}', date('d/m/Y'), $header);
        $file->wsMain->getHeaderFooter()->setOddHeader($header);

        $copyFrom0 = 6;
        foreach($this->representativeInvestor as $item)
        {
            $item[0] = $item[0].str_repeat(' ', 50 - strlen($item[0]));
            $item[1] = $item[1].str_repeat(' ', 50 - strlen($item[1]));

            $data = new stdClass();
            $data->DaiDienDauTu = "    - Ông (Bà) : {$item[0]} Chức vụ : {$item[1]}";
            $file->newGridRow(array('s'=>$data), $row, $copyFrom0);
            $row++;
        }

        $copyFrom1 = $row + 1;
        $row       = $row + 2;
        foreach ($this->representativeTech as $item)
        {
            $item[0] = $item[0].str_repeat(' ', 50 - strlen($item[0]));
            $item[1] = $item[1].str_repeat(' ', 50 - strlen($item[1]));

            $data = new stdClass();
            $data->DaiDienBaoDuong = "    - Ông (Bà) : {$item[0]} Chức vụ : {$item[1]}";
            $file->newGridRow(array('s'=>$data), $row, $copyFrom1);
            $row++;
        }

        $copyFrom2 = $row + 15;
        $row       = $row + 16;
        foreach($oOrders as $item)
        {
            $data    = new stdClass();
            $data->a = ++$stt;
            $data->b = $item->TenThietBi;
            $data->c = $item->LoaiThietBi;
            $data->d = $item->MaThietBi;
            $data->e = $item->MaKhuVuc;
            $data->f = '';//$item->GhiChu;
            $file->newGridRow(array('s'=>$data), $row, $copyFrom2);
            $row++;
        }

        $file->removeRow($copyFrom2);
        $file->removeRow($copyFrom1);
        $file->removeRow($copyFrom0);
        $file->save();
        die();

        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function saveexcelAction()
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


        $file      = new Qss_Model_Excel(Qss_Lib_System::getTemplateFile('M858', 'SDM_BienBanNghiemThu.xlsx'));
        $orders    = $this->params->requests->getParam('orders', array());
        $other     = $this->params->requests->getParam('other', 0);
        $repOther1 = $this->params->requests->getParam('repOther1', 0);
        $repOther2 = $this->params->requests->getParam('repOther1', 0);
        $start     = $this->params->requests->getParam('start', '');
        $end       = $this->params->requests->getParam('end', '');
        $oOrders   = $this->getOrdersByIOID($orders);
        $mStart    = date_create($start);
        $mEnd      = date_create($end);

        $mTable = Qss_Model_Db::Table('ODoiTac');
        $mTable->where(sprintf('IFID_M118 = %1$d', $other));
        $oOther = $mTable->fetchOne();

        $mTable = Qss_Model_Db::Table('OLienHeCaNhan');
        $mTable->where(sprintf('IOID = %1$d', $repOther1));
        $oRepOther1 = $mTable->fetchOne();

        $mTable = Qss_Model_Db::Table('OLienHeCaNhan');
        $mTable->where(sprintf('IOID = %1$d', $repOther2));
        $oRepOther2 = $mTable->fetchOne();

        $this->parseOrders($oOrders);

        $stt    = 0;
        $row    = 7;
        $eMain  = new stdClass();

        $HoTen1    = @$oRepOther1->HoTen.str_repeat(' ', 50 - strlen(@$oRepOther1->HoTen));
        $ChucDanh1 = @$oRepOther1->ChucDanh.str_repeat(' ', 50 - strlen(@$oRepOther1->ChucDanh));
        $HoTen2    = @$oRepOther2->HoTen.str_repeat(' ', 50 - strlen(@$oRepOther2->HoTen));
        $ChucDanh2 = @$oRepOther2->ChucDanh.str_repeat(' ', 50 - strlen(@$oRepOther2->ChucDanh));

        $eMain->DaiDienChuDauTu      = $this->investor;
        $eMain->DaiDienDonViLienQuan = $oOther?$oOther->TenDoiTac:'';
        $eMain->DaiDienLienQuan1     = "    - Ông (Bà) : ".$HoTen1." Chức vụ : ".$ChucDanh1;
        $eMain->DaiDienLienQuan2     = "    - Ông (Bà) : ".$HoTen2." Chức vụ : ".$ChucDanh2;
        $eMain->DanhGiaSauBaoTri     = ($this->existsQC && $this->allPass)?'Đạt':'';
        $eMain->BatDau               = $start?"      h       ngày  ".$mStart->format('d')."   tháng  ".$mStart->format('m')."   năm ".$mStart->format('Y'):"      h       ngày       tháng       năm      ";
        $eMain->KetThuc              = $end?"      h       ngày  ".$mEnd->format('d')."   tháng  ".$mEnd->format('m')."   năm ".$mEnd->format('Y'):"      h       ngày       tháng       năm      ";"      h       ngày       tháng       năm      ";

        $file->init(array('m'=>$eMain));

        $header = $file->wsMain->getHeaderFooter()->getOddHeader();
        $header = str_replace('{m:NgayBanHanh}', date('d/m/Y'), $header);
        $file->wsMain->getHeaderFooter()->setOddHeader($header);

        $copyFrom0 = 6;
        foreach($this->representativeInvestor as $item)
        {
            $item[0] = $item[0].str_repeat(' ', 50 - strlen($item[0]));
            $item[1] = $item[1].str_repeat(' ', 50 - strlen($item[1]));

            $data = new stdClass();
            $data->DaiDienDauTu = "    - Ông (Bà) : {$item[0]} Chức vụ : {$item[1]}";
            $file->newGridRow(array('s'=>$data), $row, $copyFrom0);
            $row++;
        }

        $copyFrom1 = $row + 1;
        $row       = $row + 2;
        foreach ($this->representativeTech as $item)
        {
            $item[0] = $item[0].str_repeat(' ', 50 - strlen($item[0]));
            $item[1] = $item[1].str_repeat(' ', 50 - strlen($item[1]));

            $data = new stdClass();
            $data->DaiDienBaoDuong = "    - Ông (Bà) : {$item[0]} Chức vụ : {$item[1]}";
            $file->newGridRow(array('s'=>$data), $row, $copyFrom1);
            $row++;
        }

        $copyFrom2 = $row + 15;
        $row       = $row + 16;
        foreach($oOrders as $item)
        {
            $data    = new stdClass();
            $data->a = ++$stt;
            $data->b = $item->TenThietBi;
            $data->c = $item->LoaiThietBi;
            $data->d = $item->MaThietBi;
            $data->e = $item->MaKhuVuc;
            $data->f = '';//$item->GhiChu;
            $file->newGridRow(array('s'=>$data), $row, $copyFrom2);
            $row++;
        }

        $file->removeRow($copyFrom2);
        $file->removeRow($copyFrom1);
        $file->removeRow($copyFrom0);
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

    /**
     * @param $orders
     * - Khi hiển thị nhà đầu tư chỉ chọn 1 nhà đầu tư duy nhất, voi danh sách đại diện nếu không có thì in ra hai dòng trống
     */
    public function parseOrders($orders)
    {
        $i = 0;
        $j = 0;

        if($this->existsInvestor)
        {
            foreach ($orders as $item)
            {
                $this->representativeInvestor[$i][0]   = $item->DaiDienKhachHang1;
                $this->representativeInvestor[$i++][1] = $item->ChucDanhDDKH1;
                $this->representativeInvestor[$i][0]   = $item->DaiDienKhachHang2;
                $this->representativeInvestor[$i][1]   = $item->ChucDanhDDKH2;

                $this->representativeTech[$j][0]   = $item->DaiDienThucHien1;
                $this->representativeTech[$j++][1] = $item->ChucDanhDDKThuat1;
                $this->representativeTech[$j][0]   = $item->DaiDienThucHien2;
                $this->representativeTech[$j][1]   = $item->ChucDanhDDKThuat2;

                if($this->investor == '') // Chi lay mot nha thau thoi
                {
                    $this->investor = $item->BenKhachHang;
                }

                if(@(int)$item->Dat == 0)
                {
                    $this->allPass = @(int)$item->Dat;
                }
            }
        }

        // Neu khong co dai dien chu dau tu chen hai dong trong
        if(!count($this->representativeInvestor))
        {
            $this->representativeInvestor[0][0] = '';
            $this->representativeInvestor[0][1] = '';
            $this->representativeInvestor[1][0] = '';
            $this->representativeInvestor[1][1] = '';
        }

        // Neu khong co dai dien ky thuat chen hai dong trong
        if(!count($this->representativeTech))
        {
            $this->representativeTech[0][0] = '';
            $this->representativeTech[0][1] = '';
            $this->representativeTech[1][0] = '';
            $this->representativeTech[1][1] = '';
        }

    }

    // @todo: nen viet vao model
    public function getOrdersByIOID($arrIOID)
    {
        $arrIOID[] = 0;
        $mTable  = Qss_Model_Db::Table('OPhieuBaoTri');
        $mTable->select('OPhieuBaoTri.*, ODanhSachThietBi.LoaiThietBi, ODanhSachThietBi.MaKhuVuc');
        $mTable->join('LEFT JOIN ODanhSachThietBi ON IFNULL(OPhieuBaoTri.Ref_MaThietBi, 0) = ODanhSachThietBi.IOID');

        if($this->existsInvestor)
        {
            $mTable->select('LienHe1.ChucDanh AS ChucDanhDDKH1');
            $mTable->select('LienHe2.ChucDanh AS ChucDanhDDKH2');
            $mTable->join('LEFT JOIN OLienHeCaNhan AS LienHe1 ON IFNULL(OPhieuBaoTri.Ref_DaiDienKhachHang1, 0) = LienHe1.IOID');
            $mTable->join('LEFT JOIN OLienHeCaNhan AS LienHe2 ON IFNULL(OPhieuBaoTri.Ref_DaiDienKhachHang2, 0) = LienHe2.IOID');

            $mTable->select('NhanVien1.ChucDanh AS ChucDanhDDKThuat1');
            $mTable->select('NhanVien2.ChucDanh AS ChucDanhDDKThuat2');
            $mTable->join('LEFT JOIN ODanhSachNhanVien AS NhanVien1 ON IFNULL(OPhieuBaoTri.Ref_DaiDienThucHien1, 0) = NhanVien1.IOID');
            $mTable->join('LEFT JOIN ODanhSachNhanVien AS NhanVien2 ON IFNULL(OPhieuBaoTri.Ref_DaiDienThucHien2, 0) = NhanVien2.IOID');


        }

        $mTable->where(sprintf('OPhieuBaoTri.IOID IN (%1$s)', implode(', ', $arrIOID)));
        return $mTable->fetchAll();
    }

    // @todo: nen viet vao model
    public function ordersAction()
    {
        $notpass  = $this->params->requests->getParam('notpass', 0);
        $pass     = $this->params->requests->getParam('pass', 0);
        $investor = $this->params->requests->getParam('investor', 0);
        $start    = $this->params->requests->getParam('start', '');
        $end      = $this->params->requests->getParam('end', '');
        $orders   = array();

        if($start && $end && (!Qss_Lib_System::fieldActive('OPhieuBaoTri', 'BenKhachHang') || $investor))
        {
            $mStart  = Qss_Lib_Date::displaytomysql($start);
            $mEnd    = Qss_Lib_Date::displaytomysql($end);
            $mTable  = Qss_Model_Db::Table('OPhieuBaoTri');

            if($investor)
            {
                $mTable->where(sprintf('ifnull(Ref_BenKhachHang, 0) != %1$d', $investor));
            }

            $mTable->where('ifnull(NgayYeuCau, "") != ""');
            $mTable->where(sprintf('(NgayYeuCau Between "%1$s" AND "%2$s")', $mStart, $mEnd));

            if($notpass && !$pass)
            {
                $mTable->where($mTable->ifnullNumber('Dat', 0));
            }
            elseif($pass && !$notpass)
            {
                $mTable->where($mTable->ifnullNumber('Dat', 1));
            }

            $mTable->orderby('NgayYeuCau');
            $mTable->orderby('SoPhieu');
            $orders = $mTable->fetchAll();
        }

        $this->html->orders = $orders;
    }
}