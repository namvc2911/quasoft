<?php
class Button_M765Controller extends Qss_Lib_Controller
{
    public function paramIndexAction()
    {

    }

    public function paramExportAction()
    {

    }

    public function paramParamsAction()
    {
        $equipments = $this->params->requests->getParam('equipment',array(0));

        $mTable = Qss_Model_Db::Table('ODanhSachDiemDo');
        $mTable->select('ODanhSachDiemDo.*');
        $mTable->join('INNER JOIN ODanhSachThietBi ON ODanhSachDiemDo.IFID_M705 = ODanhSachThietBi.IFID_M705');
        $mTable->where(sprintf(' ODanhSachThietBi.IFID_M705 IN (%1$s) ', implode(', ', $equipments)));
        $mTable->groupby('ODanhSachDiemDo.Ma');
        if(Qss_Lib_System::fieldActive('ODanhSachDiemDo', 'ThuTu')) {
            $mTable->orderby('ODanhSachDiemDo.ThuTu');
        }

        $mTable->orderby('ODanhSachDiemDo.Ma');
        $this->html->params = $mTable->fetchAll();

        if(Qss_Lib_System::fieldActive('ODanhSachDiemDo', 'BoPhanNhap')) {
            $this->html->boPhanNhap = Qss_Lib_System::getFieldRegx('ODanhSachDiemDo', 'BoPhanNhap');
        }
    }

    public function paramImportAction()
    {
        $params = $this->params->requests->getParams();
        $service = $this->services->Button->M765->Param->Import($params);
        echo $service->getMessage(Qss_Service_Abstract::TYPE_HTML);
        echo $service->getData();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function paramExcelAction()
    {

        $mCommon       = new Qss_Model_Extra_Extra();
        $arrParams     = $this->params->requests->getParam('monitors',array(0));
        $start         = $this->params->requests->getParam('start', '');
        $end           = $this->params->requests->getParam('end', '');
        $deleteOld     = $this->params->requests->getParam('deleteOld', '');
        $mStart        = date_create($start);
        $mEnd          = date_create($end);
        $tmpStart      = $mStart;
        $equipment     = $this->params->requests->getParam('equipment', array(0));
        $excel_col     = array_flip(Qss_Lib_Const::$EXCEL_COLUMN);
        $oEquips       = $mCommon->getTableFetchAll('ODanhSachThietBi', sprintf('IFID_M705 in (%1$s) ', implode(',', $equipment)));
        $mChiSoTheoCa  = Qss_Model_Db::Table('ODanhSachDiemDo');
        $mChiSoTheoCa->where('IFNULL(TheoCa, 0) = 1');
        $mChiSoTheoCa->where(sprintf('IFID_M705 IN  (%1$s)', implode(',', $equipment)));
        $mChiSoTheoCa->where(sprintf('IOID IN  (%1$s)', implode(',', $arrParams)));
        $mChiSoTheoCa->groupby('Ma');
        if(Qss_Lib_System::fieldActive('ODanhSachDiemDo', 'ThuTu')) {
            $mChiSoTheoCa->orderby('ODanhSachDiemDo.ThuTu');
        }
        $arrParams1    = $mChiSoTheoCa->fetchAll();
        $soCotChiSoTheoCa = count($arrParams1) +2;
        $mChiSoTheoGio = Qss_Model_Db::Table('ODanhSachDiemDo');
        $mChiSoTheoGio->where('IFNULL(TheoCa, 0) = 0');
        $mChiSoTheoGio->where(sprintf('IFID_M705 IN  (%1$s)', implode(',', $equipment)));
        $mChiSoTheoGio->where(sprintf('IOID IN  (%1$s)', implode(',', $arrParams)));
        $mChiSoTheoGio->groupby('Ma');
        if(Qss_Lib_System::fieldActive('ODanhSachDiemDo', 'ThuTu')) {
            $mChiSoTheoGio->orderby('ODanhSachDiemDo.ThuTu');
        }
        $arrParams2    = $mChiSoTheoGio->fetchAll();
        $soCotChiSoTheoGio = count($arrParams2) +2;
        $name          = 'Nhập thông số '.$start.' - '.$end.'.xlsx';
        $iCol1         = 4;
        $iCol2         = 3;
        $iRow1         = 2;
        $iRow2         = 2;
        $backgroundOddLine = 'EDEDED';

        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"".$name."\"");

        $file = new Qss_Model_Excel(Qss_Lib_System::getTemplateFile('M765', 'Mau_Import_Chi_So.xlsx'));
        $file->init();

        $file->setActiveSheetIndex(0);

        foreach($arrParams1 as $item) {
            $file->setCellValue($excel_col[$iCol1].'1', $item->Ma);
            $iCol1++;
        }

        foreach ($oEquips as $equip)
        {
            $tmpStart = $mStart;
            while ($tmpStart <= $mEnd) {
                for($i = 0; $i <= 23; $i++) {
                    $file->setCellValue('A'.$iRow1, @$equip->MaThietBi);
                    $file->setCellValue('B'.$iRow1, $tmpStart->format('d-m-Y'));
                    $file->setCellValue('C'.$iRow1, $i);

                    if($iRow1%2!=0) {
                        $file->setStyles('A'.$iRow1.':'.$file->numberToColumn($soCotChiSoTheoCa).$iRow1, '', $backgroundOddLine);
                    }

                    $iRow1++;
                }
                $tmpStart = Qss_Lib_Date::add_date($tmpStart, 1);
            }
        }


        $file->setActiveSheetIndex(1);

        foreach($arrParams2 as $item) {
            $file->setCellValue($excel_col[$iCol2].'1', $item->Ma);
            $iCol2++;
        }


        foreach ($oEquips as $equip)
        {
            $tmpStart = $mStart;
            while ($tmpStart <= $mEnd) {
                $file->setCellValue('A'.$iRow2, @$equip->MaThietBi);
                $file->setCellValue('B'.$iRow2, $tmpStart->format('d-m-Y'));

                if($iRow1%2!=0) {
                    $file->setStyles('A'.$iRow2.':'.$file->numberToColumn($soCotChiSoTheoGio).$iRow2, '', $backgroundOddLine);
                }

                $iRow2++;
                $tmpStart = Qss_Lib_Date::add_date($tmpStart, 1);
            }


        }

        $file->setActiveSheetIndex(0);

        $file->save();
        die();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}
?>