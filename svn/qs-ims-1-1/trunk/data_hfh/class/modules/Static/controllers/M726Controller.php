<?php
class Static_M726Controller extends Qss_Lib_Controller
{
	public function init()
	{
		$this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
		parent::init();
        $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
	}
	
	public function indexAction()
	{
        $this->html->Status = Qss_Lib_System::getFieldRegx('ODanhSachThietBi', 'TrangThai');
	}

	public function showAction()
	{
        $mEquip         = new Qss_Model_Maintenance_Equipment();
		$eqIOID         = $this->params->requests->getParam('equip', 0);
		$locationIOID   = $this->params->requests->getParam('location', 0);
		$eqGroupIOID    = $this->params->requests->getParam('group', 0);
		$eqTypeIOID     = $this->params->requests->getParam('type', 0);
		$costcenterIOID = $this->params->requests->getParam('costcenter', 0);
        $partnerIOID    = $this->params->requests->getParam('partner', 0);
        $status         = $this->params->requests->getParam('status', array());
		$sort           = $this->params->requests->getParam('sort', 0);

		$this->html->eqs  = $this->_getReportData(
            $eqIOID,
            $locationIOID,
            $costcenterIOID,
            $eqGroupIOID,
            $eqTypeIOID,
            $sort,
            $status,
            $partnerIOID
        );
		$this->html->sort      = $sort;
        $this->html->arrStatus = Qss_Lib_System::getFieldRegx('ODanhSachThietBi', 'TrangThai');

	}

    public function excelAction()
    {
        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"Danh sách thiết bị.xlsx\"");

        $eqIOID         = $this->params->requests->getParam('equip', 0);
        $locationIOID   = $this->params->requests->getParam('location', 0);
        $eqGroupIOID    = $this->params->requests->getParam('group', 0);
        $eqTypeIOID     = $this->params->requests->getParam('type', 0);
        $costcenterIOID = $this->params->requests->getParam('costcenter', 0);
        $partnerIOID    = $this->params->requests->getParam('partner', 0);
        $status         = $this->params->requests->getParam('status', array());
        $sort           = $this->params->requests->getParam('sort', 0);
        $arrStatus      = Qss_Lib_System::getFieldRegx('ODanhSachThietBi', 'TrangThai');

        $eqs = $this->_getReportData(
            $eqIOID,
            $locationIOID,
            $costcenterIOID,
            $eqGroupIOID,
            $eqTypeIOID,
            $sort,
            $status,
            $partnerIOID
        );
        $row        = 7;
        $file       = new Qss_Model_Excel(Qss_Lib_System::getTemplateFile('M726', 'HFH_DanhSachThietBi.xlsx'), true);
        $main       = new stdClass();
        $old        = '';
        $i          = 1;
        $file->init(array('m'=>$main));


        foreach ($eqs as $item)
        {
            if($old != $item->TitleCode)
            {
                $groupTitle =  '';
                $groupTitle .= $item->TitleName;
                $groupTitle .= ($item->TitleCode && $item->TitleName)?" - {$item->TitleCode}":$item->TitleCode;
                $groupTitle = $groupTitle?$groupTitle:'';

                $data     = new stdClass();
                $data->s1 = $groupTitle;

                $file->newGridRow(array('s'=>$data), $row, 5);
                $row++;
            }

            $old = $item->TitleCode;

            // for($j = 1; $j < 100; $j++)
            {
                $data     = new stdClass();
                $data->a  = $i++;
                $data->b  = $item->TenThietBi;
                $data->c  = $item->MaThietBi;
                $data->d  = $item->MaTaiSan;
                $data->e  = $arrStatus[(int)$item->TrangThai];
                $data->f  = $item->Model;
                $data->g  = $item->XuatXu;
                $data->h  = $item->MaKhuVuc;
                $data->i  = $item->NgayDuaVaoSuDung;
                $data->k  = $item->TenNhanVien;
                $data->j  = '';

                $file->newGridRow(array('s'=>$data), $row, 6);
                $row++;
            }
        }

        $file->removeRow(6);
        $file->removeRow(5);

        $file->save();
        die();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

	private function _getReportData(
        $eqIOID,
        $locIOID,
        $costcenterIOID,
        $eqGroupIOID,
        $eqTypeIOID,
        $sort,
        $status,
        $partnerIOID
    )
    {
        $mEquip        = new Qss_Model_Maintenance_Equipment();
        $sort          = $sort?$sort:1;

        switch ($sort)
        {
            case 1: // nhom theo loai thiet bi
                $equips = $mEquip->getEquipmentsOrderByType(
                    $eqIOID,
                    $locIOID,
                    $costcenterIOID,
                    $eqGroupIOID,
                    $eqTypeIOID,
                    $status,
                    $partnerIOID
                );

                $sortCodeCol = 'MaLoai';
                $sortNameCol = 'TenLoai';
            break;

            case 4: // nhom theo khu vuc
                $equips = $mEquip->getEquipmentsOrderByLocation(
                    $eqIOID,
                    $locIOID,
                    $costcenterIOID,
                    $eqGroupIOID,
                    $eqTypeIOID,
                    $status,
                    $partnerIOID
                );
            break;

            default: // Nhóm theo loại thiết bị
                $equips = $mEquip->getEquipmentsOrderByType(
                    $eqIOID,
                    $locIOID,
                    $costcenterIOID,
                    $eqGroupIOID,
                    $eqTypeIOID,
                    $status,
                    $partnerIOID
                );
            break;
        }

        return $equips;
    }

}

?>