<?php
/**
 *
 * @author HuyBD
 *
 */
class Report_FinanceController extends Qss_Lib_Controller
{
	/**
	 *
	 * @return unknown_type
	 */
	public function init ()
	{
		//$this->i_SecurityLevel = 15;
		parent::init();
		$this->headScript($this->params->requests->getBasePath() . '/js/report-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');
		$this->html->curl = $this->params->requests->getRequestUri();
	}
	public function balanceAction()
	{
		//	$this->v_fCheckRightsOnForm(155);
	}
	public function balanceTrialAction()
	{
		//	$this->v_fCheckRightsOnForm(155);
	}
	public function paymentAction()
	{
		//	$this->v_fCheckRightsOnForm(155);
	}
	public function glAction()
	{
		//	$this->v_fCheckRightsOnForm(155);
	}
}
?>