<?php
require_once QSS_ROOT_DIR . '/lib/PHPExcel.php';
require_once QSS_ROOT_DIR . '/lib/PHPExcel/IOFactory.php';
/**
  @author: Thinh Tuan
 */
class Report_MapController extends Qss_Controller
{
        public $common;
        public $workstation;
        public $section;
        private $_params;
        protected $_map;
        public $flow;

        public function init()
        {
                $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/quangngai.php';
                parent::init();
                $this->params->responses->setHeader('', 'Content-Type: text/html; charset=utf-8');
		        if ( $this->params->requests->isAjax())
				{
					$this->setLayoutRender(false);
				}
                $this->headScript($this->params->requests->getBasePath() . '/js/hightchart/highcharts.js');
                //$this->headScript($this->params->requests->getBasePath() . '/js/hightchart/highcharts-more.js');
                //$this->headScript($this->params->requests->getBasePath() . '/js/hightchart/themes/grid.js');
                
                $this->common = new Qss_Model_Extra_Common();
                $this->flow = new Qss_Model_Extra_Flow();
                $this->workstation = new Qss_Model_Extra_Workstation();
                $this->section = new Qss_Model_Extra_Section();
                $this->_params = $this->params->requests->getParams();
                $this->_map = new Qss_Model_Extra_Map();
        }

        public function indexAction()
        {
                // $this->html->workstation = $this->workstation->getWorkstation();
                //$this->html->section     = $this->section->getAllSections();
                $this->html->layers = $this->_map->getMapLayers();
                $this->html->AnhVeTinh = $this->_map->getAnhVeTinh();
        }

        public function searchAction()
        {
                $layers = $this->params->requests->getParam('layers');
                $anhvetinh = $this->params->requests->getParam('anhvetinh');

                //$workstations = $this->params->requests->getParam('workstation');

                $this->html->section = $this->_map->getSection($layers);
                $this->html->workstation = $this->_map->getWorkstation($layers);
                $this->html->basemap = $this->_map->getBaseMap($layers);
                $this->html->anhvetinh = $this->_map->getAnhVeTinhMap($anhvetinh);
        }
        
        public function sectionAction()
        {
            $section = $this->section->getSectionDataByCode($this->_params['section']);
            $this->html->sectionInfo     = $this->section->getSectionByID($this->_params['id']);

            $sectionData = array();
            
            $oldDate = '';
            
            $index = 0;
            $index2 = 0;
            foreach($section as $sec)
            {
                $date = Qss_Lib_Date::mysqltodisplay($sec->SDate);
                if(!isset($sectionData[$date]))
                {
                	$sectionData[$date] = array();
                }
                $sectionData[$date][] = array('ZIndex'=>$sec->ZIndex,'Distance'=>$sec->XDistance);
            }

            $this->html->data    = $sectionData;
            $this->html->section = $this->_params['section'];

            $this->setLayoutRender(false);
        }
        
        public function workstationAction()
        {
                $ioid = $this->params->requests->getParam('id',0);
                $type = $this->params->requests->getParam('type',0);
                $year = $this->params->requests->getParam('year',0);
                $workstation = $this->common->getTable(array('*'), 'Tram', array('IOID'=>$ioid), array(), 'NO_LIMIT', 1);
                $this->html->workstation = $workstation;
                $this->html->type = $type;
                $this->html->ioid = $ioid;
                $loai = '';
                $dvt = '';
                if($workstation->Ref_LopBanDo == 52421)//trạm khí tượng
                {
                	//chỉ có mưa
                	$this->html->NamDuLieu = $this->flow->getLuongMuaByWorksationGroupByYear($ioid);	
                	if(!$year)
                	{
                		$year = $this->html->NamDuLieu[0]->Year;
                	}
                	$this->html->luuluong = $this->flow->getAvgLuongMuaByDay($ioid,0,$year);
                	$loai = 'Lượng mưa';
                	$dvt = 'Lượng mưa (mm)';
                }
                else 
                {
                	//lưu lượng và dòng chảy
                	if(!$type)
                	{
                		$this->html->NamDuLieu = $this->flow->getMucNuocByWorksationGroupByYear($ioid);	
                		if(!$year)
	                	{
	                		$year = $this->html->NamDuLieu[0]->Year;
	                	}
	                	$this->html->luuluong = $this->flow->getAvgMucNuocByDay($ioid,0,$year);
	                	$loai = 'Mực mức';
	                	$dvt = 'Mực nước (mm)';
                	}
                	else
                	{
                		$this->html->NamDuLieu = $this->flow->getLuuLuongByWorksationGroupByYear($ioid);	
	                	if(!$year)
	                	{
	                		$year = $this->html->NamDuLieu[0]->Year;
	                	}
	                	$this->html->luuluong = $this->flow->getAvgLuuLuongByDay($ioid,0,$year);
	                	$loai = 'Lưu lượng';
	                	$dvt = 'Lưu lượng (m3/s)';
                	}
                }
             $this->html->year = $year;
                
			$this->html->loai = $loai;
			$this->html->dvt = $dvt;
        }
        
        public function xoiloboituAction()
        {
                $ioid = $this->params->requests->getParam('id',0);
                $this->html->xoiloboitu = $this->common->getTable(array('*'), 'XoiLoBoiTu', array('IOID'=>$ioid), array(), 'NO_LIMIT',  1);
        }
        
        public function hochuaAction()
        {
                $ioid = $this->params->requests->getParam('id',0);
                $this->html->hochua = $this->common->getTable(array('*'), 'HoChua', array('IOID'=>$ioid), array(), 'NO_LIMIT', 1);
        }
        
        public function hokhoanAction()
        {
                $ioid = $this->params->requests->getParam('id',0);
                $this->html->hokhoan = $this->common->getTable(array('*'), 'HoKhoan', array('IOID'=>$ioid), array(), 'NO_LIMIT', 1);
        }
	public function downloadAction ()
	{
		header("Content-type: application/force-download");
		header("Content-Transfer-Encoding: Binary");
		$objPHPExcel = new PHPExcel();
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->setPreCalculateFormulas(false);
		
		$type = $this->params->requests->getParam('type',0);
		$start = $this->params->requests->getParam('start','');
		$end = $this->params->requests->getParam('end','');
		$start = Qss_Lib_Date::displaytomysql($start);
        $end = Qss_Lib_Date::displaytomysql($end);
		$data = array();
		$name = '';
		switch ($type)
		{
			case 0:
				$data = $this->flow->getLuongMuaByRange($start,$end);
				$name = 'LuongMua';
				break;
			case 1:
				$data = $this->flow->getLuuLuongByRange($start,$end);
				$name = 'LuuLuong';
				break;
			case 2:
				$data = $this->flow->getMucNuocByRange($start,$end);
				$name = 'MucNuoc';
				break;
		}
		$tram = 0;
		$i = 2;
		foreach ($data as $item)
		{
			if($tram != $item->Ref_Tram)
			{
				if($tram == 0)
				{
					$ws = $objPHPExcel->setActiveSheetIndex(0);
				}
				else
				{
					$ws->getStyle('B1:B'.($i-1))
						->getNumberFormat()
						->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_DATETIME);
					//$ws->removeRow($ws->getHighestRow(),1);
					$ws = $objPHPExcel->createSheet();
				}
				$ws->getColumnDimension('B')->setWidth("15");
				$ws->getColumnDimension('C')->setWidth("15");
				$ws->setTitle($item->Tram);
				$ws->setCellValue('B1','ThoiGian');
				$ws->setCellValue('C1',$name);
				$i = 2;
			}
			$ws->setCellValueByColumnAndRow(1,$i,PHPExcel_Shared_Date::PHPToExcel( $item->Ngay));
			$ws->setCellValue('C'.$i,$item->Data);
			$tram = $item->Ref_Tram;
			$i++;
		}
		if($i>2)
		{
			$ws->getStyle('B1:B'.($i-1))
						->getNumberFormat()
						->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_DATETIME);
			//$ws->removeRow($ws->getHighestRow()+1,1);
		}
		header("Content-disposition: attachment; filename=\"".$name.".xls\"");
		$objWriter->save('php://output');
		
		die();
		
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
}
