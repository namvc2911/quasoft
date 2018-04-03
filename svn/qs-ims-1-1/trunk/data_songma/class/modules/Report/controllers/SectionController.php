<?php
/**
 b
 */
class Report_SectionController extends Qss_Lib_Controller
{
    
        public $common;
        public $section;
        private $_params;
        
	public function init ()
	{
		$this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
		parent::init();
		//$this->headScript($this->params->requests->getBasePath() . '/js/extra/calendar.js');
		$this->common = new Qss_Model_Extra_Common();
		$this->section = new Qss_Model_Extra_Section();
		$this->_params = $this->params->requests->getParams();
	}
        
	public function indexAction()
	{

	}
        
        // *****************************************************************
        // === Bieu do tung mat cat theo ngay
        // *****************************************************************
        public function  chartAction()
        {
            $this->html->section = $this->section->getAllSections();
        }
        
        public function chart1Action()
        {
            
            // *****************************************************************
            // === $section, lay ra thong tin section theo section truyen vao
            // === order by date 
            // *****************************************************************
            $section = $this->section->getSectionDataByCode($this->_params['section']);
            
            
            // *****************************************************************
            // === $sectionData, luu du lieu section
            // === St: array( [0] => 
            // ===          array( Date => Date,
            // ===                 Val => array(
            // ===                        [0] => array( ZIndex => , Distance =>
            // ===                              )  
            // ===                )
            // ===          )
            // *****************************************************************
            $sectionData = array();
            
            
            // *****************************************************************
            // === $oldDate, giup lay cac mat cat duoc do cung ngay vao mot mang
            // === Rl: $sectionData
            // *****************************************************************
            $oldDate = '';
            
            
            // *****************************************************************
            // $index, Chi so mang $sectionData
            // *****************************************************************
            $index = 0;
            
            
            // *****************************************************************
            // $index2, Chi so mang "Val" cua mang $sectionData
            // *****************************************************************            
            $index2 = 0;
            
            
            // *****************************************************************
            // === Gan mat cat vao mang chia theo ngay
            // *****************************************************************
            foreach($section as $sec)
            {
                $date = Qss_Lib_Date::mysqltodisplay($sec->SDate);
                if(!isset($sectionData[$date]))
                {
                	$sectionData[$date] = array();
                }
                // Val
                $sectionData[$date][] = array('ZIndex'=>$sec->ZIndex,'Distance'=>$sec->XDistance);
            }
            
            // *****************************************************************
            // === Truyen tham so sang phtml
            // *****************************************************************
            $this->html->data    = $sectionData;
            $this->html->section = $this->_params['section'];
            
            // *****************************************************************
            // === Disable layout
            // *****************************************************************
            $this->setLayoutRender(false);
        }
}