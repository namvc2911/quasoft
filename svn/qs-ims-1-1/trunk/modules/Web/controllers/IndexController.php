<?php
/**
 *
 * @author HuyBD
 *
 */
class Web_IndexController extends Qss_Lib_Controller {

	public function init() {
		parent::init();
		$this->params->responses->setHeader('', 'Content-Type: text/html; charset=utf-8');
	}

	/**
	 *
	 * @return void
	 */
	public function indexAction() {
		$cms_id = $this->params->requests->getParam('cms_id', 0);
		$content_id = (int) $this->params->requests->getParam('content_id');
		$record_id = (int) $this->params->requests->getParam('record_id');
		$design_id = (int) $this->params->requests->getParam('design_id');
		$limit = (int) $this->params->requests->getParam('limit');
		$pageno = (int) $this->params->requests->getParam('pageno');
		$page = $this->params->requests->getParam('page');

		if (!$cms_id) {
			echo 'Không tìm thấy cms id!';
			exit();
		}
		$dp = new Qss_Model_Web_CMS();
		$dp->getData($cms_id);
		echo $dp->getDisplay($content_id, $record_id, $design_id, $limit, $pageno, $page, $this->params->requests->isAjax());
		$this->setLayoutRender(false);
		$this->setHtmlRender(false);
	}

	public function cssAction() {
		$deptid = $this->params->requests->getParam('deptid');
		$filename = $this->params->requests->getParam('file');
		$file = QSS_DATA_DIR . '/jscss/' . $deptid . '/' . $filename;
		if (file_exists($file)) {
			header("Content-type: text/css; charset=UTF-8");
			header("Cache-Control: no-cache, must-revalidate");
			header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
			echo file_get_contents($file);
			die();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function jsAction() {
		$deptid = $this->params->requests->getParam('deptid');
		$filename = $this->params->requests->getParam('file');
		$file = QSS_DATA_DIR . '/jscss/' . $deptid . '/' . $filename;
		if (file_exists($file)) {
			if (strstr($_SERVER["HTTP_USER_AGENT"], "MSIE") == false) {
				header("Content-type: text/javascript");
				header("Content-Disposition: inline; filename=\"download.js\"");
				header("Content-Length: " . filesize($file));
			} else {
				header("Content-type: application/force-download");
				header("Content-Disposition: attachment; filename=\"download.js\"");
				header("Content-Length: " . filesize($file));
			}
			header("Expires: Fri, 01 Jan 2010 05:00:00 GMT");
			if (strstr($_SERVER["HTTP_USER_AGENT"], "MSIE") == false) {
				header("Cache-Control: no-cache");
				header("Pragma: no-cache");
			}
			include $file;
			die();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	/**
	 *
	 * @return void
	 */
	public function saveCommentAction() {
		$params = $this->params->requests->getParams();
		$service = $this->services->Web->Comment->Save($params);
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	/**
	 *
	 * @return void
	 */
	public function saveFeedbackAction() {
		$params = $this->params->requests->getParams();
		$service = $this->services->Web->Feedback->Save($params);
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	/**
	 *
	 * @return void
	 */
	public function surveyAction() {

	}

	public function forumAction() {

	}
	public function captchaAction() {
		$data = array(0 => array('0.png', '1.png', '2.png', '3.png', '4.png', '5.png', '6.png', '7.png', '8.png', '9.png'), /* */
			1 => array('10.png', '11.png', '12.png', '13.png', '14.png', '15.png', '16.png', '17.png', '18.png', '19.png'), /* */
			2 => array('20.png', '21.png', '22.png', '23.png', '24.png', '25.png', '26.png', '27.png', '28.png', '29.png'), /* */
			3 => array('30.png', '31.png', '32.png', '33.png', '34.png', '35.png', '36.png', '37.png', '38.png', '39.png'), /* */
			4 => array('40.png', '41.png', '42.png', '43.png', '44.png', '45.png', '46.png', '47.png', '48.png', '49.png'), /* */
			5 => array('50.png', '51.png', '52.png', '53.png', '54.png', '55.png', '56.png', '57.png', '58.png', '59.png'), /* */
			6 => array('60.png', '61.png', '62.png', '63.png', '64.png', '65.png', '66.png', '67.png', '68.png', '69.png'), /* */
			7 => array('70.png', '71.png', '72.png', '73.png', '74.png', '75.png', '76.png', '77.png', '78.png', '79.png'), /* */
			8 => array('80.png', '81.png', '82.png', '83.png', '84.png', '85.png', '86.png', '87.png', '88.png', '89.png'), /* */
			9 => array('90.png', '91.png', '92.png', '93.png', '94.png', '95.png', '96.png', '97.png', '98.png', '99.png'), /* */
		);
		$num1 = rand(0, 9);
		$num2 = rand(0, 9);
		$num3 = rand(0, 9);
		$num4 = rand(0, 9);
		$imagenum1 = rand(0, 9);
		$imagenum2 = rand(0, 9);
		$imagenum3 = rand(0, 9);
		$imagenum4 = rand(0, 9);
		$string = $num1 . $num2 . $num3 . $num4;
		$this->params->sessions->set('captcha', $string);
		$folder = QSS_DATA_DIR . '/captcha/';
		if (!is_dir($folder)) {
			mkdir($folder);
		}
		$imagefile1 = $folder . $data[$num1][$imagenum1];
		$imagefile2 = $folder . $data[$num2][$imagenum2];
		$imagefile3 = $folder . $data[$num3][$imagenum3];
		$imagefile4 = $folder . $data[$num4][$imagenum4];
		$dest = imagecreatetruecolor(80, 20);

		$src = imagecreatefrompng($imagefile1);
		imagecopy($dest, $src, 0, 0, 0, 0, 20, 20);
		$src = imagecreatefrompng($imagefile2);
		imagecopy($dest, $src, 20, 0, 0, 0, 20, 20);
		$src = imagecreatefrompng($imagefile3);
		imagecopy($dest, $src, 40, 0, 0, 0, 20, 20);
		$src = imagecreatefrompng($imagefile4);
		imagecopy($dest, $src, 60, 0, 0, 0, 20, 20);
		$black = imagecolorallocate($dest, 0, 0, 0);
		$start = rand(2, 18);
		$end = rand(2, 18);
		imageline($dest, 0, $start, 80, $end, $black);
		$this->params->responses->setHeader('', 'Content-Type: image/png; charset=utf-8');
		imagepng($dest);

		imagedestroy($dest);
		imagedestroy($src);
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function downloadAction() {
		$filename = $this->params->requests->getParam('file');
		$file = QSS_DATA_DIR . '/files/' . $filename;
		if (file_exists($file)) {
			header("Content-Type: application/force-download");
			header('Content-Description: File Transfer');
			readfile($file);
			die();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
}
?>