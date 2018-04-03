<?php
/**
 *
 * @author HuyBD
 *
 */
class Ws_IndexController extends Qss_Lib_Controller
{

	public function init ()
	{
		parent::init();
		$this->params->responses->setHeader('', 'Content-Type: text/html; charset=utf-8');
	}

	/**
	 *
	 * @return void
	 */
	public function indexAction ()
	{

	}

	/**
	 *
	 * @return void
	 */
	public function testAction ()
	{
		$ok = $this->params->requests->getParam('OK',0);
		if($ok)
		{
			//manual
			$model = new Qss_Model_Import_Form('M402');
			$params = array();
			$i=0;
			//while($i <= 100)
			{
				$params['ONhapKho'][0]['SoChungTu'] = '1234567';
				$params['ONhapKho'][0]['NgayChungTu'] = '2017-01-01';
				$params['ONhapKho'][0]['Kho'] = 'CC01';
				$params['ONhapKho'][0]['status'] = 2;
				//$params['ONhapKho'][0]['NhomThietBi'] = 16329;
				//$params['ONhapKho'][0]['TrangThai'] = 2;
				
				//$params['OCauTrucThietBi'][0]['ViTri'] = "M2222";
				//$params['OCauTrucThietBi'][0]['BoPhan'] = "Lót tấm s";
				//$params['OCauTrucThietBi'][0]['TrucThuoc'] = "";
				//$params['OCauTrucThietBi'][0]['MoTa'] = "Cai fid y";
				$model->setData($params);
				$i++;
			}
			$model->generateSQL();
			return;
			$code = $this->params->requests->getParam('code','');
			$content = $this->params->requests->getParam('content',0);
			$phoneno = $this->params->requests->getParam('phoneno',0);
			$sc = new SOAPClient( QSS_BASE_URL ."/ws/maintenance/hours/wsdl");
			$this->html->retval = $sc->sendHours("huybd","Admin1","1",$code,$phoneno,$phoneno,$phoneno,$content);
		}
		$this->html->content = $content;
		$this->html->phoneno = $phoneno;
		$this->html->wscode = Qss_Config::get('secure')->wscode;
		$this->html->defaultno = Qss_Config::get('secure')->phoneno;
	}
/**
	 *
	 * @return void
	 */
	public function test1Action ()
	{
		$ok = $this->params->requests->getParam('OK',0);
		if($ok)
		{
			$sc = new SOAPClient( QSS_BASE_URL ."/ws/maintenance/production/wsdl");
			$this->html->retval = $sc->sendProduction("admin","Admin1","TT1",
						date('Y-m-d'),"123","1234","2342","2342","222","OK");
		}
		$this->html->content = $content;
		$this->html->phoneno = $phoneno;
		$this->html->wscode = Qss_Config::get('secure')->wscode;
		$this->html->defaultno = Qss_Config::get('secure')->phoneno;
	}
	public function test2Action ()
	{
		$ok = $this->params->requests->getParam('OK',0);
		if($ok)
		{
			$arr = array();
			$arr[] = 'ABC002';
			$arr[] = 'CC01';
			$arr[] = '2016-10-02';
			$arr[] = 'AOCN001';
			$arr[] = 'áo sơ mi cho công ty';
			$arr[] = 'cái';
			$arr[] = '2';
			$arr[] = '200000';
			$arr[] = '2000';
			$arr[] = 'ABC001';
			$arrT = array($arr);
			$sc = new SOAPClient( QSS_BASE_URL ."/ws/inventory/sync/wsdl");
			$this->html->retval = $sc->receipt("admin","Admin1",Qss_Json::encode($arrT));
		}
		$this->html->content = $content;
		$this->html->phoneno = $phoneno;
		$this->html->wscode = Qss_Config::get('secure')->wscode;
		$this->html->defaultno = Qss_Config::get('secure')->phoneno;
	}
	public function test3Action ()
	{
		$ok = $this->params->requests->getParam('OK',0);
		if($ok)
		{
			$arr = array();
			$arr[] = 'CC01';
			$arr[] = 'ATTTT';
			$arr[] = '3';
			$arr[] = 'APPP 1';
			$arr[] = 'kg';
			$arr[] = '1110';
			$arr[] = 'ABC';
			$arrT = array($arr);
			$sc = new SOAPClient( QSS_BASE_URL ."/ws/inventory/sync/wsdl");
			$this->html->retval = $sc->update("admin","Admin1",Qss_Json::encode($arrT));
		}
		$this->html->content = $content;
		$this->html->phoneno = $phoneno;
		$this->html->wscode = Qss_Config::get('secure')->wscode;
		$this->html->defaultno = Qss_Config::get('secure')->phoneno;
	}
}
?>