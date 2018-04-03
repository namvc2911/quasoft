<?php
/**
 *
 * @author HuyBD
 *
 */
class Ws_MailController extends Qss_Controller
{

	public function init ()
	{
		parent::init();
	}

	/**
	 *
	 * @return void
	 */
	public function statusAction ()
	{
		$retval = '';
		$uid = $this->params->requests->getParam('uid'); 
		$ifid = $this->params->requests->getParam('ifid');
		$status = $this->params->requests->getParam('status');
		$sid = $this->params->requests->getParam('sid');
		//check if logined
		$loginret = 0;
		$table = Qss_Model_Db::Table('qsusers');
		$table->where(sprintf('UserID="%1$s"',$uid));
		$data = $table->fetchOne();
		//$user = $this->params->registers->get('userinfo');
		//print_r($user);die;
		if($data)
		{
			//check login
			$data_uid = $data->UserID;
			$data_pass = $data->Password;
			$data_sid = Qss_Util::hmac_md5($data_uid.$data_pass,$ifid);
			if($sid === $data_sid)
			{
				//login
				$loginservice = $this->services->Security->UserLogin($data_uid, $data_pass, 1,15);
				$loginret = $loginservice->getStatus();
				$user = $loginservice->getData();
			} 
		}
		if($loginret)
		{
			//$this->params->sessions->set('userinfo', $user);
			$this->params->registers->set('userinfo', $user);
			$form = new Qss_Model_Form();
			if($form->initData($ifid, 1))
			{
				$service = $this->services->Form->Request($form, $status, $user, 'Mail action');
				if($service->isError())
				{
					$retval = '<span style="color:red">'.$service->getMessage(Qss_Service_Abstract::TYPE_HTML).'</span>';
				}
				else
				{
					$retval = '<span style="color:green">Successfully</span>';
				}
			}
		}
		if($retval)
		{
			echo $retval;
		}
		else
		{
			echo '<span style="color:red">Unsuccessfully</span>';
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	/**
	 *
	 * @return void
	 */
	public function hoursWsdlAction ()
	{
		header("content-type: text/xml");
		$fn = QSS_DATA_DIR . '/hours.wsdl';
		$wsdl = file_get_contents($fn);
		$wsdl = str_replace('_HOST_',QSS_BASE_URL,$wsdl);
		echo $wsdl;
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	/**
	 *
	 * @return void
	 */
	public function productionAction ()
	{
		$soap = new SoapServer(QSS_BASE_URL .'/ws/maintenance/production/wsdl');
		$soap->setClass("Qss_Model_Maintenance_Ws_Production");
		$soap->handle();
		die;
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	/**
	 *
	 * @return void
	 */
	public function electricWsdlAction ()
	{
		header("content-type: text/xml");
		$fn = QSS_DATA_DIR . '/electric.wsdl';
		$wsdl = file_get_contents($fn);
		$wsdl = str_replace('_HOST_',QSS_BASE_URL,$wsdl);
		echo $wsdl;
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
/**
	 *
	 * @return void
	 */
	public function electricAction ()
	{
		$soap = new SoapServer(QSS_BASE_URL .'/ws/maintenance/electric/wsdl');
		$soap->setClass("Qss_Model_Maintenance_Ws_Production");
		$soap->handle();
		die;
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	/**
	 *
	 * @return void
	 */
	public function productionWsdlAction ()
	{
		header("content-type: text/xml");
		$fn = QSS_DATA_DIR . '/production.wsdl';
		$wsdl = file_get_contents($fn);
		$wsdl = str_replace('_HOST_',QSS_BASE_URL,$wsdl);
		echo $wsdl;
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
}
?>