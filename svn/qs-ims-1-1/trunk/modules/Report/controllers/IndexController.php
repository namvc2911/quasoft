<?php
/**
 b
 */
class Report_IndexController extends Qss_Lib_Controller
{
	public function init ()
	{
		$this->i_SecurityLevel = 15;
		parent::init();
		//$this->headScript($this->params->requests->getBasePath() . '/js/extra/calendar.js');
	}
	public function indexAction()
	{

	}
	public function excelAction()
	{
		$content =  $this->params->requests->getParam('content');
		$name =  $this->params->requests->getParam('name','');
		$name = $name?$name:'report';
		$content = Qss_Lib_Util::htmlToText($content);
		$content = Qss_Json::decode($content); 
		header("Content-type: application/vnd.ms-excel");
		header("Content-disposition: attachment; filename=\"{$name}.xls\"");
		$html2excel = new Qss_Model_Html2Excel();
		$html2excel->init();
		$html2excel->render($content);
		die();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function pdfAction()
	{
		$content =  $this->params->requests->getParam('content');
		$content = $content;
		$content = Qss_Json::decode($content); 
		header('Content-type: application/pdf');
		header('Content-Disposition: attachment; filename="report.pdf"');
		$html2pdf = new Qss_Model_Html2PDF();
		$html2pdf->init();
		$html2pdf->render($content);
		die();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function saveExcelAction()
	{
		$document = new Qss_Model_Document();
		$name =  $this->params->requests->getParam('name');
		if(!$name)
		{
			$name =  $this->_title . ': ' . date('Y-m-d h:i:s');
		}
		$data = array('id'=>0,
					'docno'=>uniqid(),
					'name'=>$name,
					'ext'=>'xls',
					'size'=>0,
					'uid'=>$this->_user->user_id,
					'folder'=>'Excel reports');				
		$id = $document->save($data);
		if(!is_dir(QSS_DATA_DIR . "/documents/"))
		{
			mkdir(QSS_DATA_DIR . "/documents/");
		}
		$destfile = QSS_DATA_DIR . "/documents/" . $id . ".xls";
		$content =  $this->params->requests->getParam('content');
		$content = Qss_Lib_Util::htmlToText($content);
		$content = Qss_Json::decode($content); 
		$html2excel = new Qss_Model_Html2Excel();
		$html2excel->init();
		$html2excel->render($content,$destfile);
		$data = array('id'=>$id,
					'docno'=>uniqid(),
					'name'=>$name,
					'ext'=>'xls',
					'size'=>(int) @filesize($destfile),
					'uid'=>$this->_user->user_id,
					'folder'=>'Excel reports');		
		$document->save($data);
		echo (Qss_Json::encode(array('error'=>false)));
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function savePdfAction()
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
					'ext'=>'pdf',
					'size'=>0,
					'uid'=>$this->_user->user_id,
					'folder'=>'PDF reports');				
		$id = $document->save($data);
		if(!is_dir(QSS_DATA_DIR . "/documents/"))
		{
			mkdir(QSS_DATA_DIR . "/documents/");
		}
		$destfile = QSS_DATA_DIR . "/documents/" . $id . ".pdf";
		$content =  $this->params->requests->getParam('content');
		$html2pdf = new Qss_Model_Html2PDF();
		$doc = new DOMDocument('1.0', 'UTF-8');
		@$doc->loadHTML($content);
		$html2pdf->render($doc,$destfile);
		$data = array('id'=>$id,
					'docno'=>uniqid(),
					'name'=>$name,
					'ext'=>'pdf',
					'size'=>(int) @filesize($destfile),
					'uid'=>$this->_user->user_id,
					'folder'=>'PDF reports');
		$document->save($data);
		echo (Qss_Json::encode(array('error'=>false)));
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
}