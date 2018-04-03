<?php
class Qss_Service_Button_M765_Param_Import extends Qss_Service_Abstract
{
    protected $_insert     = array();
    protected $_remove     = array();
    protected $_removeDuplicate = array(); // Xóa những dòng delete giống nhau
    protected $countRemove = 0;
    protected $_arrParams;

    public function __doExecute($params)
    {
        $tempFile   = @(string)$params['excel_import']; // $this->params->requests->getParam('excel_import', '');
        $ignore     = @(boolean)$params['ignore'];      // $this->params->requests->getParam('ignore', false);
        $checkall   = @(boolean)$params['checkall'];    // $this->params->requests->getParam('checkall', false);
        $deleteOld  = @(boolean)$params['deleteOld'];   // Xóa giá trị cũ

        if($tempFile == '')
        {
            $this->setMessage($this->_translate(177));
            $this->setError();
            return;
        }

        $file       = new Qss_Model_Excel(QSS_DATA_DIR.'/tmp/'.$tempFile);
        $file->init();

        $sheetTheoCa   = $file->getSheetIndexByName('Theo ca');
        $sheetTheoNgay = $file->getSheetIndexByName('Theo ngày');

        // Lấy dữ liệu import
        if($sheetTheoCa == -1 && $sheetTheoNgay == -1) {
            // Lỗi định dạng
            $this->setMessage($this->_translate(177));
            $this->setError();
            return;
        }

        $this->getDanhSachDiemDo($file);

        if($sheetTheoCa != -1)
        {
            $this->buildDataByTime($file);
        }

        if($sheetTheoNgay != -1) {
            $this->buildDataByDate($file);
        }

        // echo '<pre>'; print_r(count($this->_insert)); die;


        if($deleteOld) {
            $this->removeM765OldData();
        }
        $this->importM765Data($ignore, $checkall);
    }

    public function removeM765OldData() {
        $delete          = array();
        $mM765           = new Qss_Model_M765_Statistic();

        // echo '<Pre>'; print_r($this->_remove); die;

        // Xóa phiếu cũ theo nội dung insert
        if(count($this->_remove)) {
            foreach($this->_remove as $item) {
                $delete[] = sprintf(' (Ngay = "%1$s" AND Ref_DiemDo = "%2$s" AND Ref_MaTB = "%3$s") ', $item['Ngay'], $item['DiemDo'], $item['MaTB']);
            }

            $countDelete = count($delete);

            if(count($delete)) {
                $arrDel = array();
                $i      = 0;
                $step   = 100;

                // SELECT ra 10 dòng 1, chọn hết một lượt có thể dính lỗi max_allow_packet
                while(true) {
                    $next    = $i;
                    $endNext = $next + $step;
                    $tempArr = array();

                    for($i = $next; $i < $endNext; $i++) {
                        if(isset($delete[$i])) { // Nếu nhỏ hơn bước nhảy $step có thể không có dữ liệu
                            $tempArr[] = $delete[$i];
                        }
                    }

                    if(count($tempArr)) {
                        $mTable = Qss_Model_Db::Table('ONhatTrinhThietBi');
                        $mTable->where(implode(' or ', $tempArr));
                        $data   = $mTable->fetchAll();

                        // echo '<pre>'; print_r($data);

                        foreach($data as $dat) {
                            $arrDel[] = $dat->IFID_M765;
                        }
                    }

                    if($i > $countDelete) {
                        break;
                    }
                }
                // die;

                // echo '<pre>'; print_r($arrDel); die;

                // Xóa theo 1000 dòng 1, xóa hết một lượt có thể dính lỗi max_allow_packet
                $countArrDel = count($arrDel);
                $j           = 0;
                $step        = 1000;

                if($countArrDel) {
                    while(true) {
                        $next    = $j;
                        $endNext = $next + $step;
                        $tempArr = array();

                        for($j = $next; $j < $endNext; $j++) {
                            if(isset($arrDel[$j])) { // Nếu nhỏ hơn bước nhảy $step có thể không có dữ liệu
                                $tempArr[] = $arrDel[$j];
                            }
                        }

                        if(count($tempArr)) {
                            $mM765->deleteByIFIDs($tempArr);
                        }

                        if($j > $countArrDel) {
                            break;
                        }
                    }

                    $this->countRemove = $countArrDel;
                }
            }
        }
    }

    public function importM765Data($ignore, $checkall)
    {
        $startTime  = date('Y-m-d H:i:s');
        $import     = new Qss_Model_Import_Form('M765', !$ignore, (boolean)$checkall);

        if(count($this->_insert)) {
            foreach ($this->_insert as $item) {
                $import->setData($item);
            }

            $import->generateSQL();
            $arrErrorRows = $import->getErrorRows();
            $htmlError  = '';
            $endTime    = date('Y-m-d H:i:s');

            $this->setMessage("{$this->countRemove} dòng được xóa đi, {$import->countFormImported()} dòng được import, {$import->countFormError()} dòng bị lỗi. Thời gian: từ {$startTime} đến {$endTime} (di chuyển con trỏ đến dòng lỗi để biết thêm chi tiết lỗi).");

            if($import->countFormError() > 0)
            {
                if(isset($arrErrorRows['ONhatTrinhThietBi'])) {
                    foreach ($arrErrorRows['ONhatTrinhThietBi'] as $item) {

                        $htmlError .= sprintf('<tr title="%1$s (%2$s)">',$this->_translate($item->Error),$item->ErrorMessage);
                        $htmlError .= '<td>'.$item->DiemDo.'</td>';
                        $htmlError .= '<td>'.$item->MaTB.'</td>';
                        $htmlError .= '<td class="center">'.Qss_Lib_Date::mysqltodisplay($item->Ngay).'</td>';
                        $htmlError .= '<td class="center">'.$item->ThoiGian.'</td>';
                        $htmlError .= '<td class="right">'.Qss_Lib_Util::formatNumber($item->SoHoatDong).'</td>';
                        $htmlError .= '</tr>';
                    }

                    if($htmlError) {
                        $tempHead = '<table cellspacing="0" cellpadding="0" border="0" class="grid">';
                        $tempHead .= '<tr>';
                        $tempHead .= '<th>Điểm đo</th>';
                        $tempHead .= '<th>Thiết bị</th>';
                        $tempHead .= '<th>Ngày</th>';
                        $tempHead .= '<th>Thời gian</th>';
                        $tempHead .= '<th>Số hoạt động</th>';
                        $tempHead .= '</tr>';
                        $tempFoot = '</table>';
                        $htmlError = $tempHead.$htmlError.$tempFoot;
                    }
                }
                else {
                    $htmlError = $this->_translate(177);
                }

                if($htmlError) {
                    $this->setError();
                    $this->setMessage($htmlError);
                }
            }
            else {
                if(class_exists('Qss_Bin_Import_M765')) {
                    $m765 = new Qss_Bin_Import_M765($import);
                    $m765->__doExecute();
                }
            }
        }
        else {
            $this->setError();
            $this->setMessage($this->_translate(177));
        }
    }

    public function getDanhSachDiemDo($file)
    {
        $retval      = array();
        $arrThietBi  = array();
        $arrDiemDo   = array();
        $file->setActiveSheetByName('Theo ca');

        $highestDataColumn = $file->columnToNumber($file->wsMain->getHighestDataColumn());
        $highestDataRow    = $file->wsMain->getHighestDataRow();

        if($highestDataColumn >= 4) { // Co du lieu chi so
            for($row = 2; $row <= $highestDataRow; $row++) {
                $equipCell   = $file->getCellValue('A'.$row);

                for($col = 4; $col <= $highestDataColumn; $col++) {
                    $strCol      = $file->numberToColumn($col-1);
                    $monitorCell = trim($file->getCellValue($strCol.'1'));

                    $arrThietBi[] = $equipCell;
                    $arrDiemDo[]  = $monitorCell;
                }
            }
        }

        $file->setActiveSheetByName('Theo ngày');
        $highestDataColumn = $file->columnToNumber($file->wsMain->getHighestDataColumn());
        $highestDataRow    = $file->wsMain->getHighestDataRow();


        if($highestDataColumn >= 3) { // Co du lieu chi so
            for($row = 2; $row <= $highestDataRow; $row++) {
                $equipCell   = $file->getCellValue('A'.$row);

                for($col = 3; $col <= $highestDataColumn; $col++) {
                    $strCol  = $file->numberToColumn($col-1);
                    $monitorCell = $file->getCellValue($strCol.'1');

                    $arrThietBi[] = $equipCell;
                    $arrDiemDo[]  = $monitorCell;
                }
            }
        }



        if($arrThietBi && $arrDiemDo) {
            // Chỉ select 10 dòng 1 tránh lỗi MAX_ALLOW_PACKET
            $tempWhere = '';
            $tempIndex = 0;
            $step      = 100;
            $countBreak = count($arrDiemDo);

            while (true) {
                $next    = $tempIndex;
                $endNext = $next + $step;
                $tempWhere = '';

                for($tempIndex = $next; $tempIndex < $endNext; $tempIndex++) {
                    if(isset($arrThietBi[$tempIndex]) && isset($arrDiemDo[$tempIndex])) {
                        $tempWhere .= $tempWhere?' OR ':'';
                        $tempWhere .= sprintf('(ODanhSachThietBi.MaThietBi = "%1$s" AND ODanhSachDiemDo.Ma = "%2$s")', trim(@$arrThietBi[$tempIndex]), trim(@$arrDiemDo[$tempIndex]));
                    }
                }

                if($tempWhere) {
                    $mTable = Qss_Model_Db::Table('ODanhSachDiemDo');
                    $mTable->select('ODanhSachDiemDo.*, ODanhSachThietBi.MaThietBi, ODanhSachThietBi.IOID AS Ref_MaThietBi, OChiSoMayMoc.IOID AS Ref_DonViTinh');
                    $mTable->join('INNER JOIN ODanhSachThietBi ON ODanhSachDiemDo.IFID_M705 = ODanhSachThietBi.IFID_M705');
                    $mTable->join('INNER JOIN OChiSoMayMoc ON OChiSoMayMoc.IOID = ODanhSachDiemDo.Ref_ChiSo');
                    $mTable->where($tempWhere);
                    $oDiemDo = $mTable->fetchAll();

                    foreach ($oDiemDo as $item) {
                        $retval[$item->MaThietBi][$item->Ma] = $item;
                    }
                }

                if($tempIndex > $countBreak) {
                    break;
                }
            }
        }

        $this->_arrParams = $retval;
    }

    public function buildDataByTime($file)
    {
        $dataByTime = array();

        $file->setActiveSheetByName('Theo ca');
        $highestDataColumn = $file->columnToNumber($file->wsMain->getHighestDataColumn());
        $highestDataRow    = $file->wsMain->getHighestDataRow();

        if($highestDataColumn >= 4) { // Co du lieu chi so
            for($row = 2; $row <= $highestDataRow; $row++) {
                $equipCell   = $file->getCellValue('A'.$row);
                $dateCell    = $file->getCellValue('B'.$row);
                $timeCell    = $file->getCellValue('C'.$row);

                if(is_numeric($timeCell)) {
                    $timeCell    = (int)$timeCell;
                    $timeCell    = $timeCell<10?'0'.$timeCell.':00:00':$timeCell.':00:00';
                }

                // if(preg_match("/(2[0-4]|[01][1-9]|10):([0-5][0-9])/", $timeCell))
                {
                    for($col = 4; $col <= $highestDataColumn; $col++) {
                        $strCol       = $file->numberToColumn($col-1);
                        $getCell      = $file->getCellValue($strCol.$row);
                        $monitorCell  = trim($file->getCellValue($strCol.'1'));
                        $keyDuplicate = @(int)$this->_arrParams[$equipCell][$monitorCell]->IOID.'-'
                            .@(int)$this->_arrParams[$equipCell][$monitorCell]->Ref_MaThietBi.'-'
                            .Qss_Lib_Date::displaytomysql($dateCell);

                        if(@$this->_arrParams[$equipCell][$monitorCell] && TRIM($getCell) != '') {
                            $dataByTime['ONhatTrinhThietBi'][0]['DiemDo']     = @(int)$this->_arrParams[$equipCell][$monitorCell]->IOID;
                            $dataByTime['ONhatTrinhThietBi'][0]['MaTB']       = @(int)$this->_arrParams[$equipCell][$monitorCell]->Ref_MaThietBi;
                            $dataByTime['ONhatTrinhThietBi'][0]['BoPhan']     = @(int)$this->_arrParams[$equipCell][$monitorCell]->Ref_BoPhan;
                            $dataByTime['ONhatTrinhThietBi'][0]['ChiSo']      = @(int)$this->_arrParams[$equipCell][$monitorCell]->Ref_ChiSo;
                            $dataByTime['ONhatTrinhThietBi'][0]['DonViTinh']  = @(int)$this->_arrParams[$equipCell][$monitorCell]->Ref_DonViTinh;
                            $dataByTime['ONhatTrinhThietBi'][0]['Ngay']       = Qss_Lib_Date::displaytomysql($dateCell);
                            $dataByTime['ONhatTrinhThietBi'][0]['Ca']         = '';
                            $dataByTime['ONhatTrinhThietBi'][0]['ThoiGian']   = TRIM($timeCell);
                            $dataByTime['ONhatTrinhThietBi'][0]['SoHoatDong'] = TRIM($getCell);
                            $this->_insert[] = $dataByTime;
                        }

                        if(!in_array($keyDuplicate, $this->_removeDuplicate)) {
                            $this->_removeDuplicate[] = $keyDuplicate;
                            $this->_remove[] = array(
                                'DiemDo'=>@(int)$this->_arrParams[$equipCell][$monitorCell]->IOID
                                , 'MaTB'=>@(int)$this->_arrParams[$equipCell][$monitorCell]->Ref_MaThietBi
                                , 'Ngay'=>Qss_Lib_Date::displaytomysql($dateCell));
                        }
                    }
                }
            }
        }
    }

    public function buildDataByDate($file)
    {
        $dataByDate = array();

        $file->setActiveSheetByName('Theo ngày');
        $highestDataColumn = $file->columnToNumber($file->wsMain->getHighestDataColumn());
        $highestDataRow    = $file->wsMain->getHighestDataRow();


        if($highestDataColumn >= 3) { // Co du lieu chi so
            for($row = 2; $row <= $highestDataRow; $row++) {
                $equipCell   = $file->getCellValue('A'.$row);
                $dateCell    = $file->getCellValue('B'.$row);

                for($col = 3; $col <= $highestDataColumn; $col++) {
                    $strCol       = $file->numberToColumn($col-1);
                    $getCell      = $file->getCellValue($strCol.$row);
                    $monitorCell  = $file->getCellValue($strCol.'1');
                    $keyDuplicate = @(int)$this->_arrParams[$equipCell][$monitorCell]->IOID.'-'
                        .@(int)$this->_arrParams[$equipCell][$monitorCell]->Ref_MaThietBi.'-'
                        .Qss_Lib_Date::displaytomysql($dateCell);

                    if(@$this->_arrParams[$equipCell][$monitorCell] && TRIM($getCell) != '') {
                        $dataByDate['ONhatTrinhThietBi'][0]['DiemDo']     = @(int)$this->_arrParams[$equipCell][$monitorCell]->IOID;
                        $dataByDate['ONhatTrinhThietBi'][0]['MaTB']       = @(int)$this->_arrParams[$equipCell][$monitorCell]->Ref_MaThietBi;
                        $dataByDate['ONhatTrinhThietBi'][0]['BoPhan']     = @(int)$this->_arrParams[$equipCell][$monitorCell]->Ref_BoPhan;
                        $dataByDate['ONhatTrinhThietBi'][0]['ChiSo']      = @(int)$this->_arrParams[$equipCell][$monitorCell]->Ref_ChiSo;
                        $dataByDate['ONhatTrinhThietBi'][0]['DonViTinh']  = @(int)$this->_arrParams[$equipCell][$monitorCell]->Ref_DonViTinh;
                        $dataByDate['ONhatTrinhThietBi'][0]['Ngay']       = Qss_Lib_Date::displaytomysql($dateCell);
                        $dataByDate['ONhatTrinhThietBi'][0]['Ca']         = '';
                        $dataByDate['ONhatTrinhThietBi'][0]['ThoiGian']   = '';
                        $dataByDate['ONhatTrinhThietBi'][0]['SoHoatDong'] = TRIM($getCell);
                        $this->_insert[] = $dataByDate;
                    }

                    if(!in_array($keyDuplicate, $this->_removeDuplicate)) {
                        $this->_removeDuplicate[] = $keyDuplicate;
                        $this->_remove[] = array(
                            'DiemDo'=>@(int)$this->_arrParams[$equipCell][$monitorCell]->IOID
                            , 'MaTB'=>@(int)$this->_arrParams[$equipCell][$monitorCell]->Ref_MaThietBi
                            , 'Ngay'=>Qss_Lib_Date::displaytomysql($dateCell)
                        );
                    }
                }
            }
        }
    }

//    function getImportedGrid ($import)
//    {
//        $all    = $import->getErrorRows();
//        $fields = Qss_Lib_System::getFieldsByObject('M765', 'ONhatTrinhThietBi');
//        $ret    = '';
//
//        foreach ($all as $key=>$rows)
//        {
//            $ret .= sprintf('<div class="line-hr"><span>Nhật trình thiết bị</span></div>');
//            $ret .= '<table class="grid">';
//            $ret .= '<tr>';
//            $ret .= sprintf('<th>%1$s</th>','STT');
//            foreach ($fields->loadFields() as $f)
//            {
//                if($f->bEditStatus)
//                {
//                    $ret .= sprintf('<th>%1$s</th>',$f->szFieldName);
//                }
//            }
//            $ret .= '</tr>';
//
//            $i = 1;
//            foreach ($rows as $row)
//            {
//                $error = explode(',',$row->ErrorMessage);
//                if($row->Error)
//                {
//                    $ret .= sprintf('<tr title="%1$s (%2$s)">'
//                        ,$this->_translate($row->Error)
//                        ,$row->ErrorMessage);
//                }
//                else
//                {
//                    $ret .= '<tr class="imported">';
//                }
//                $ret .= sprintf('<td class="center %2$s">%1$d</td>',$i,($row->Error == 4)?'bgred':'');
//                foreach ($row as $key=>$value)
//                {
//                    if($key != 'IOID'
//                        && $key != 'DeptID'
//                        && $key != 'Error'
//                        && $key != 'ErrorMessage'
//                        && strpos($key, 'IFID_') === false
//                        && $fields->getFieldByCode($key)->bEditStatus)
//                    {
//                        $class = '';
//                        if(in_array($key,$error))
//                        {
//                            $class = 'bgred';
//                        }
//                        $ret .= sprintf('<td class="%2$s">%1$s</td>',$value,$class);
//                    }
//                }
//                $ret .= '</tr>';
//                $i++;
//            }
//            $ret .= '</table>';
//        }
//        return $ret;
//    }
}