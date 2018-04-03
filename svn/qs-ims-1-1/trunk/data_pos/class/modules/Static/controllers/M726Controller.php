<?php

/**
 *
 * @author: ThinhTuan
 * @todo: bo ca ham thua, /cost/manufacturing, /cost/group/
 * @todo: gop ba bao cao ve dung may theo ky, nhom, khu vuc
 * @todo: Can them dieu kien cho mot so bao cao ve pbt voi tinh trang pbt la hoan thanh
 * @todo: Sua bao cao m750
 */
class Static_M726Controller extends Qss_Lib_Controller
{
	// property
	protected $_model;  /* Remove */

	public function init()
	{
		parent::init();

		/* @todo: Remove headScript */
		$this->headScript($this->params->requests->getBasePath() . '/js/report-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');

		/* @todo: Remove $_model, $_params, $_common */
		$this->_model     = new Qss_Model_Maintenance_Equipment();

		/* @todo: Remove curl */
		//$this->html->curl = $this->params->requests->getRequestUri();
		$this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
	}
	

	public function indexAction()
	{

	}
	public function showAction()
	{
		//$eqModel        = new Qss_Model_Extra_Equip();
		$mEqModel       = new Qss_Model_Maintenance_Equipment();
		// DIEU KIEN LOC THEO THIET BI
        $mCommon        = new Qss_Model_Extra_Extra();
		$eqIOID         = $this->params->requests->getParam('equip', 0);
		$eq             = $this->params->requests->getParam('equipmentStr', 0);
		// DIEU KIEN LOC THEO KHU VUC
		$locationIOID   = $this->params->requests->getParam('location', 0);
		$location       = $this->params->requests->getParam('locationStr', '');
        $oLocation      = $mCommon->getTableFetchOne('OKhuVuc', array('IOID'=>$locationIOID));
		// DIEU KIEN LOC THEO NHOM THIET BI
		$eqGroupIOID    = $this->params->requests->getParam('group', 0);
		$eqGroup        = $this->params->requests->getParam('groupStr', 0);
		// DIEU KIEN LOC THEO LOAI THIET BI
		$eqTypeIOID     = $this->params->requests->getParam('type', 0);
		$eqType         = $this->params->requests->getParam('typeStr', 0);

        $start = $this->params->requests->getParam('start', '');
        $end   = $this->params->requests->getParam('end', '');

        $eqListModel = new Qss_Model_Maintenance_Equip_List();

        $eqs = $eqListModel->getEquipsOrderByGroupAndTypeOfEquip(
			$locationIOID
			, $eqGroupIOID
			, $eqTypeIOID
			, $eqIOID
			, Qss_Lib_Date::displaytomysql($start)
			, Qss_Lib_Date::displaytomysql($end)
		);

		$this->html->eqs             = $eqs;
		// LOC THEO KHU VUC
		$this->html->locationIOID    = $locationIOID;
		$this->html->location        = $location;
		// LOC THEO NHOM THIET BI
		$this->html->eqGroupIOID     = $eqGroupIOID;
		$this->html->eqGroup         = $eqGroup;
		// LOC THEO LOAI THIET BI
		$this->html->eqTypeIOID      = $eqTypeIOID;
		$this->html->eqType          = $eqType;
		// LOC THEO THIET BI
		$this->html->eqIOID          = $eqIOID;
		$this->html->eq              = $eq;
	}

    public function excelAction()
    {
        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"Danh mục thiết bị.xlsx\"");
        $excel = new Qss_Model_Excel(QSS_DATA_DIR.'/template/M726/main.xlsx', true);
        $mCommon        = new Qss_Model_Extra_Extra();
        $main = new stdClass();
        $row = 12;

        $stt = 0;
        $oldEqGroup= '';
        $oldEqType= '';
        $oldEqTypeLevel= '';
        $eqTypeNo = 0;
        $eqGroupNo= 0;
        $eqTypeNoMemory = array();
        $oldHPEqIOID = '';
        $oldLftLoaiTB = '';
        $oldRgtLoaiTB = '';
        $level = 1;


        //$eqModel        = new Qss_Model_Extra_Equip();
        $mEqModel       = new Qss_Model_Maintenance_Equipment();
        // DIEU KIEN LOC THEO THIET BI
        $eqIOID         = $this->params->requests->getParam('equip', 0);
        $eq             = $this->params->requests->getParam('equipmentStr', 0);
        // DIEU KIEN LOC THEO KHU VUC
        $locationIOID   = $this->params->requests->getParam('location', 0);
        $location       = $this->params->requests->getParam('locationStr', '');
        $oLocation      = $mCommon->getTableFetchOne('OKhuVuc', array('IOID'=>$locationIOID));
        // DIEU KIEN LOC THEO NHOM THIET BI
        $eqGroupIOID    = $this->params->requests->getParam('group', 0);
        $eqGroup        = $this->params->requests->getParam('groupStr', 0);
        // DIEU KIEN LOC THEO LOAI THIET BI
        $eqTypeIOID     = $this->params->requests->getParam('type', 0);
        $eqType         = $this->params->requests->getParam('typeStr', 0);

        $start = $this->params->requests->getParam('start', '');
        $end   = $this->params->requests->getParam('end', '');

        $eqListModel = new Qss_Model_Maintenance_Equip_List();

        $eqs = $eqListModel->getEquipsOrderByGroupAndTypeOfEquip(
            $locationIOID
            , $eqGroupIOID
            , $eqTypeIOID
            , $eqIOID
            , Qss_Lib_Date::displaytomysql($start)
            , Qss_Lib_Date::displaytomysql($end)
        );

        $main->bpql = $oLocation?"{$oLocation->MaKhuVuc} - {$oLocation->Ten}":"";
        $main->nam  = date('Y');
        $data = array('main'=>$main);
        $excel->init($data);
        $tempS      = array();
        $tempSIndex = array();

        foreach ($eqs as $eq)
        {
            // IN NHOM THIET BI
            if($oldEqGroup != $eq->RefNhomTB)
            {
                $data = new stdClass();
                $data->t1 = ++$eqGroupNo . '. ' . $eq->NhomThietBiChinh;

                $tempS[]      = array('s'=>$data);
                $tempSIndex[] = 9;

//                $excel->newGridRow(array('s'=>$data), $row, 9);
//                $row++;

                $oldEqType                  = '';
                $eqTypeNo                   = 0;// reset;
                $eqTypeNoMemory[$eqGroupNo] = array();
            }
            $oldEqGroup = $eq->RefNhomTB;

            // IN LOAI THIET BI
            if($oldEqType != $eq->RefLoaiTBJoin)
            {
                $displayEqType = $eqGroupNo;

                // neu chua ton tai bien nho so thu tu cua level hien tai thi khoi tao
                if(!isset($eqTypeNoMemory[$eqGroupNo][$eq->LevelLoaiTBJoin]))
                {
                    $eqTypeNoMemory[$eqGroupNo][$eq->LevelLoaiTBJoin] = 0;
                }

                $eqTypeNoMemory[$eqGroupNo][$eq->LevelLoaiTBJoin]++;

                if($oldEqTypeLevel != '' && $eq->LevelLoaiTBJoin < $oldEqTypeLevel)
                {
                    foreach($eqTypeNoMemory[$eqGroupNo] as $etlv=>$etnm)
                    {
                        if($etlv > $eq->LevelLoaiTBJoin)
                        {
                            $eqTypeNoMemory[$eqGroupNo][$etlv] = 0;
                        }
                    }
                }


                $i = 0;
                while($i != $eq->LevelLoaiTBJoin)
                {
                    $j = $i + 1;
                    $displayEqType .= '.'.$eqTypeNoMemory[$eqGroupNo][$j];
                    $i++;
                }

                $displayEqType .= ' '.$eq->LoaiThietBiJoin;

                $oldEqTypeLevel = $eq->LevelLoaiTBJoin;

                $data = new stdClass();
                $data->t2 = $displayEqType;

                $tempS[]      = array('s'=>$data);
                $tempSIndex[] = 10;

//                $excel->newGridRow(array('s'=>$data), $row, 10);
//                $row++;
            }
            $oldEqType = $eq->RefLoaiTBJoin;

            $duAn = $eq->DuAn?" ({$eq->DuAn})":'';

            // IN DANH SACH THIET BI
            if($eq->RefLoaiTBJoin == $eq->RefLoaiTB)
            {
                $temp3 = new stdClass();
                $temp3->c1  = ++$stt;//!$eq->Ref_TrucThuoc?(++$stt):'';
                $temp3->c2  = $eq->TenThietBi;
                $temp3->c3  = $eq->DacTinhKT;
                $temp3->c4  = $eq->MaThietBi;
                $temp3->c5  = $eq->Serial;
                $temp3->c6  = $eq->XuatXu;
                $temp3->c7  = $eq->NamSanXuat;
                $temp3->c8  = Qss_Lib_Date::mysqltodisplay($eq->NgayDuaVaoSuDung);
                $temp3->c9  = $eq->MaKhuVuc.$duAn;
                $temp3->c10 = '';
                $temp3->c11 = $eq->GhiChu;
                $temp3->c12 = $eq->MaTaiSan;

                $tempS[]      = array('s'=>$temp3);
                $tempSIndex[] = 11;

//                $excel->newGridRow(array('s'=>$temp3), $row, 11);
//                $row++;
            }

        }

        if(count($tempS))
        {
            $jkl = 0;

            foreach ($tempS as $sArray)
            {
                $excel->newGridRow($sArray, $row, $tempSIndex[$jkl]);
                $row++;
                $jkl++;
            }
        }

        $row++;
        $excel->setCellValue('K'.$row, '               Ngày…...….tháng…...….năm…...….');
        $excel->setStyles('K'.$row, '', '', true);
        $row++;
        $row++;

        $excel->setCellValue('A'.$row, '              Người lập  ');
        $excel->setStyles('A'.$row, '', '', true);
        $excel->setCellValue('D'.$row, '                                         Đơn vị quản lý');
        $excel->setStyles('D'.$row, '', '', true);
        $excel->setCellValue('H'.$row, '                    Phòng Kỹ thuật');
        $excel->setStyles('H'.$row, '', '', true);
        $excel->setCellValue('L'.$row, '             Ban Giám Đốc');
        $excel->setStyles('L'.$row, '', '', true);

        $excel->removeRow(11);
        $excel->removeRow(10);
        $excel->removeRow(9);

        $excel->save();
        die();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

}

?>