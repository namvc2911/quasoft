<?php

/**
 * M843Controller.php
 *
 * Báo cáo hiệu chuẩn kiểm định
 *
 * @category   Static
 * @author     Thinh Tuan
 */

class Static_M850Controller extends Qss_Lib_Controller
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
        $mPartner = new Qss_Model_Master_Partner();
        $partner  = $this->params->requests->getParam('partner', 0);
        $this->html->report = $mPartner->getContactsOfPartners($partner, false, true);
        $this->html->count  = $mPartner->countContactsOfPartners($partner, false, true);
    }

    public function excelAction()
    {
        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"Danh sách nhà cung cấp thiết bị.xlsx\"");

        $mPartner = new Qss_Model_Master_Partner();
        $partner = $this->params->requests->getParam('partner', 0);

        $report = $mPartner->getContactsOfPartners($partner, false, true);
        $count = $mPartner->countContactsOfPartners($partner, false, true);
        $stt = 0;
        $row = 8;
        $merge = array();
        $file = new Qss_Model_Excel(Qss_Lib_System::getTemplateFile('M845', 'HFH_DanhSachDoiTac.xlsx'));
        $main = new stdClass();
        $oldDoiTac = '';

        $main->m1 = date('d/m/Y');

        $file->init(array('m' => $main));

        foreach($report as $item)
        {
            $data      = new stdClass();

            if($oldDoiTac != TRIM($item->TenDoiTac))
            {
                $data->a   = $item->TenDoiTac;
                $data->b   = $item->DiaChiDoiTac;
                $data->c   = $item->DienThoaiDoiTac;

                $tmpCount = isset($count[$item->IFID_M118])?$count[$item->IFID_M118]:1;
                $tmpCount = $tmpCount - 1;

                if($tmpCount > 0)
                {
                    $rowspan = $row + $tmpCount;
                    $merge[] = "A{$row}:A{$rowspan}";
                    $merge[] = "B{$row}:B{$rowspan}";
                    $merge[] = "C{$row}:C{$rowspan}";
                }
            }
            else
            {
                $data->a   = '';
                $data->b   = '';
                $data->c   = '';
            }

            $data->d   = $item->HoTen;
            $data->e   = $item->ChucDanh;
            $data->f   = $item->DienThoaiDiDongLienHe;
            $data->g   = $item->EmailLienHe;
            $data->h   = $item->GhiChu;

            $file->newGridRow(array('s'=>$data), $row, 7);
            $row++;
            $oldDoiTac = TRIM($item->TenDoiTac);
        }

        foreach ($merge as $item)
        {
            $file->mergeCells($item);
        }

        $file->removeRow(7);

        $file->save();
        die();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    private function getData($partner = 0)
    {
        $mPartner = new Qss_Model_Master_Partner();
        $report   = $mPartner->getContactsOfPartners($partner);

        foreach ($report as $item)
        {

        }
    }
}

?>