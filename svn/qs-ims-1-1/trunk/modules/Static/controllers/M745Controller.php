<?php
class Static_M745Controller extends Qss_Lib_Controller
{
	public function init()
	{
		$this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
		parent::init();
        $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
	}
	public function indexAction()
	{

	}

	public function showAction()
	{
        $mBreakdown          = new Qss_Model_Maintenance_Breakdown();;
		$start               = $this->params->requests->getParam('start', '');
		$end                 = $this->params->requests->getParam('end', '');

		$this->html->start   = $start;
		$this->html->end     = $end;
		$this->html->reports = $mBreakdown->getDowntimeByCause(
            Qss_Lib_Date::displaytomysql($start),
            Qss_Lib_Date::displaytomysql($end),
            $this->params->requests->getParam('location', 0),
            $this->params->requests->getParam('equipment', 0),
            $this->params->requests->getParam('eqgroup', 0),
            $this->params->requests->getParam('eqtype', 0),
            $this->params->requests->getParam('reason', 0)
		);

	}

	public function excelAction()
    {
        $formName = Qss_Lib_System::getReportTitle('M745');
        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"{$formName}.xlsx\"");

        $file       = new Qss_Model_Excel(Qss_Lib_System::getTemplateFile('M745', 'BaoCaoSuCoTheoNguyenNhan.xlsx'));
        $mBreakdown = new Qss_Model_Maintenance_Breakdown();;
        $start      = $this->params->requests->getParam('start', '');
        $end        = $this->params->requests->getParam('end', '');
        $fields     = Qss_Lib_System::getFieldsByObject('M759', 'OPhieuBaoTri');
        $row        = 7;

        $reports = $mBreakdown->getDowntimeByCause(
            Qss_Lib_Date::displaytomysql($start),
            Qss_Lib_Date::displaytomysql($end),
            $this->params->requests->getParam('location', 0),
            $this->params->requests->getParam('equipment', 0),
            $this->params->requests->getParam('eqgroup', 0),
            $this->params->requests->getParam('eqtype', 0),
            $this->params->requests->getParam('reason', 0)
        );

        $reportTitle     = new stdClass();
        $reportTitle->m1 = Qss_Lib_System::getUpperCaseReportTitle('M745');
        $reportTitle->m2 = Qss_Lib_Date::displaytomysql($start);
        $reportTitle->m3 = Qss_Lib_Date::displaytomysql($end);
        $reportTitle->m4 = $this->html->_translate(1);
        $reportTitle->m5 = $this->html->_translate(2);

        $tableTitle      = new stdClass();
        $tableTitle->a   = $fields->getFieldByCode('SoPhieu')->szFieldName;
        $tableTitle->b   = $fields->getFieldByCode('NgayBatDau')->szFieldName;
        $tableTitle->c   = $fields->getFieldByCode('NgayYeuCau')->szFieldName;
        $tableTitle->d   = $fields->getFieldByCode('MaThietBi')->szFieldName;
        $tableTitle->e   = $fields->getFieldByCode('TenThietBi')->szFieldName;
        $tableTitle->f   = $fields->getFieldByCode('MaKhuVuc')->szFieldName;
        $tableTitle->g   = $fields->getFieldByCode('TenDVBT')->szFieldName;
        $tableTitle->h   = $fields->getFieldByCode('NgayDungMay')->szFieldName;
        $tableTitle->i   = $fields->getFieldByCode('NgayKetThucDungMay')->szFieldName;
        $tableTitle->j   = $fields->getFieldByCode('ThoiGianDungMay')->szFieldName;
        $tableTitle->k   = $fields->getFieldByCode('MaNguyenNhanSuCo')->szFieldName;

        $file->init(array('m'=>$reportTitle, 't'=>$tableTitle));

        foreach ($reports as $item)
        {
            $sub    = new stdClass();
            $sub->a = $item->SoPhieu;
            $sub->b = Qss_Lib_Date::mysqltodisplay($item->NgayBatDau);
            $sub->c = Qss_Lib_Date::mysqltodisplay($item->NgayYeuCau);
            $sub->d = $item->MaThietBi;
            $sub->e = $item->TenThietBi;
            $sub->f = $item->KhuVucHienTai;
            $sub->g = $item->MaDonViBaoTri;
            $sub->h = Qss_Lib_Date::mysqltodisplay($item->NgayDungMay);
            $sub->i = Qss_Lib_Date::mysqltodisplay($item->NgayKetThucDungMay);
            $sub->j = Qss_Lib_Util::formatNumber($item->ThoiGianDungMay);
            $sub->k = $item->TenNguyenNhanSuCo;

            $file->newGridRow(array('s'=>$sub), $row, 6);
            $row++;
        }

        $file->removeRow(6);
        $file->save();
        die();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);

    }
	
}

?>