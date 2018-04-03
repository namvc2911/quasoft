<?php

/**
  @author: Thinh Tuan
 */
class Report_MapController extends Qss_Lib_Controller
{
        public $common;
        public $workstation;
        public $section;
        private $_params;
        protected $_map;

        public function init()
        {
                $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
                parent::init();
                //$this->headScript($this->params->requests->getBasePath() . '/js/extra/calendar.js');
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
        }

        public function searchAction()
        {
                $layers = $this->params->requests->getParam('layers');

                //$workstations = $this->params->requests->getParam('workstation');

                $this->html->section = $this->_map->getSection($layers);
                $this->html->workstation = $this->_map->getWorkstation($layers);
                $this->html->HoKhoan = $this->_map->getHoKhoan($layers);
                $this->html->HoChua = $this->_map->getHoChua($layers);
                $this->html->XoiLoBoiTu = $this->_map->getXoiLoBoiTu($layers);
                $this->html->basemap = $this->_map->getBaseMap($layers);
        }
        
        public function sectionAction()
        {
			$sectionID   = $this->params->requests->getParam('id');
			$sectionCode = $this->params->requests->getParam('section');
			
            $this->html->section     = $this->section->getSectionByID($sectionID);
			$this->html->sectionData = $this->section->getSectionDataByCodeGroupByYear($sectionCode);
        }
        
        public function workstationAction()
        {
                $ioid = $this->params->requests->getParam('id',0);
                $this->html->workstation = $this->common->getTable(array('*'), 'Tram', array('IOID'=>$ioid), array(), 'NO_LIMIT', 1);
                $this->html->LuuLuong = $this->flow->getLuuLuongByWorksationGroupByYear($ioid);
                $this->html->LuongMua = $this->flow->getLuongMuaByWorksationGroupByYear($ioid);
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
}
