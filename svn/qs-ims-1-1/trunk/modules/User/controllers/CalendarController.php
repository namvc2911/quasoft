<?php
/**
 *
 * @author HuyBD
 *
 */
class User_CalendarController extends Qss_Lib_Controller
{
	protected $_model;
	/**
	 *
	 * @return unknown_type
	 */
	public function init ()
	{
		$this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/wide.php';
		parent::init();
		$this->_model = new Qss_Model_Event();
		$this->headScript($this->params->requests->getBasePath() . '/js/calendar.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/tabs.js');
		$this->headLink($this->params->requests->getBasePath() . '/css/calendar.css');
	}

	/**
	 *
	 * @return void
	 */
	public function indexAction ()
	{
		$fid = $this->params->requests->getParam('fid',0);
		$type = $this->params->requests->getParam('type','');
		if(!$type)
		{
			$type = $this->params->cookies->get('cal_' . $fid . '_type', 'day');
		}
		$day = $this->params->requests->getParam('day',date('d'));
		$week = $this->params->requests->getParam('week',date('W'));
		$month = $this->params->requests->getParam('month',date('m'));
		$year = $this->params->requests->getParam('year',date('Y'));
		$owner = $this->params->requests->getParam('user',array());
		$filter = $this->params->requests->getParam('filter',array());
		if ( $fid )
		{
			$o_Form = new Qss_Model_Calendar_Form();
			$o_Form->init($fid, $this->_user->user_dept_id, $this->_user->user_id);
			//$this->_title = $o_Form->sz_Name;
			if($this->b_fCheckRightsOnForm($o_Form,4))
			{
				switch($type)
				{
					case 'day':
						$this->html->select = $this->views->Calendar->Select->Day($o_Form,$day,$month,$year);
						$this->html->content = $this->views->Calendar->Content->Day($o_Form,$day,$month,$year,$filter,$owner);
						break;
					case 'week':
						$this->html->select = $this->views->Calendar->Select->Week($o_Form,$week,$month,$year);
						$this->html->content = $this->views->Calendar->Content->Week($o_Form,$week,$month,$year,$filter,$owner);
						break;
					case 'month':
						$this->html->select = $this->views->Calendar->Select->Month($o_Form,$month,$year);
						$this->html->content = $this->views->Calendar->Content->Month($o_Form,$month,$year,$filter,$owner);
						break;
					case 'year':
						$this->html->select = $this->views->Calendar->Select->Year($o_Form,$year);
						$this->html->content = $this->views->Calendar->Content->Year($o_Form,$year,$filter,$owner);
						break;
				}
			}
		}
		$this->html->form = $o_Form;
		$this->html->fid = $fid;
		$this->html->type = $type;
		$this->html->day = $day;
		$this->html->week = $week;
		$this->html->month = $month;
		$this->html->year = $year;
		$this->html->owner = $owner;
		$this->html->filter = $filter;
		$this->html->users = $o_Form->getUsers();
		$this->html->filters = $o_Form->getFilters();

	}
	public function reloadAction ()
	{
		$fid = $this->params->requests->getParam('fid',0);
		$type = $this->params->requests->getParam('type','day');
		$day = $this->params->requests->getParam('day',date('d'));
		$week = $this->params->requests->getParam('week',date('W'));
		//echo $week;die;
		$month = $this->params->requests->getParam('month',date('m'));
		$year = $this->params->requests->getParam('year',date('Y'));
		$owner = $this->params->requests->getParam('user',array());
		$filter = $this->params->requests->getParam('filter',array());
		if ( $fid )
		{
			$o_Form = new Qss_Model_Calendar_Form();
			$o_Form->init($fid, $this->_user->user_dept_id, $this->_user->user_id);
			if($this->b_fCheckRightsOnForm($o_Form,4))
			{
				switch($type)
				{
					case 'day':
						$this->html->select = $this->views->Calendar->Select->Day($o_Form,$day,$month,$year);
						$this->html->content = $this->views->Calendar->Content->Day($o_Form,$day,$month,$year,$filter,$owner);
						break;
					case 'week':
						$this->html->select = $this->views->Calendar->Select->Week($o_Form,$week,$month,$year);
						$this->html->content = $this->views->Calendar->Content->Week($o_Form,$week,$month,$year,$filter,$owner);
						break;
					case 'month':
						$this->html->select = $this->views->Calendar->Select->Month($o_Form,$month,$year);
						$this->html->content = $this->views->Calendar->Content->Month($o_Form,$month,$year,$filter,$owner);
						break;
					case 'year':
						$this->html->select = $this->views->Calendar->Select->Year($o_Form,$year);
						$this->html->content = $this->views->Calendar->Content->Year($o_Form,$year,$filter,$owner);
						break;
				}
			}
		}
		$this->html->fid = $fid;
		$this->html->type = $type;
		$this->html->day = $day;
		$this->html->week = $week;
		$this->html->month = $month;
		$this->html->year = $year;
		$this->html->owner = $owner;
		$this->html->filter = $filter;
		$this->html->users = $o_Form->getUsers();
		$this->html->filters = $o_Form->getFilters();
		$this->params->cookies->set('cal_' . $fid . '_type', $type);
	}
}
?>