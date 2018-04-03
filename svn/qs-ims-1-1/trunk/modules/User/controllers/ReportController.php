<?php
/**
 *
 * @author HuyBD
 *
 */
class User_ReportController extends Qss_Lib_Controller
{

	/**
	 *
	 * @return unknown_type
	 */
	public function init ()
	{
		parent::init();
		$this->headScript($this->params->requests->getBasePath() . '/js/report-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/jquery.bgiframe.min.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/ajaxfileupload.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/tabs.js');

	}

	/**
	 *
	 * @return void
	 */
	public function indexAction ()
	{
		$fid = $this->params->requests->getParam('fid', 0);
		$urid = $this->params->requests->getParam('urid', 0);
		$this->html->dashboard = $this->params->requests->getParam('dashboard', 0);
		$o_Form = new Qss_Model_Form();
		if ( $fid )
		{
			$o_Form->init($fid, $this->_user->user_dept_id, $this->_user->user_id);
		}
		if($this->b_fCheckRightsOnForm($o_Form,4))
		{
			$report = new Qss_Model_Report();
			$this->html->form = $o_Form;
			$this->html->urid = $urid;
			$this->html->data = $report->getById($urid);
			/*$params = $report->getParams($this->_user->user_id,$urid);
			 $this->html->params = array();
			 if($params)
			 {
				$this->html->params = unserialize($params->Params);
				}
				if($o_Form->i_Type == Qss_Lib_Const::FORM_TYPE_MODULE)
				{
				$stepmodel = new Qss_Model_System_Step($o_Form->i_WorkFlowID);
				$this->html->steps = $stepmodel->getAll();
				}*/
			$lang 						= new Qss_Model_System_Language();
			$this->html->languages 		= $lang->getAll(1);
		}
		$this->setLayoutRender(false);
	}
	public function optionAction ()
	{
		$fid = $this->params->requests->getParam('fid', 0);
		$form = new Qss_Model_Form();
		if ( $fid )
		{
			$form->v_fInit($fid, $this->_user->user_dept_id, $this->_user->user_id);
		}
		if($this->b_fCheckRightsOnForm($form,4))
		{
			$this->html->form = $form;
			$this->html->groupbybox = $this->params->requests->getParam('groupbybox');
			$this->html->calculatebox = $this->params->requests->getParam('calculatebox');
		}
		$this->setLayoutRender(false);
	}
	/**
	 * Edit action
	 *
	 * @return void
	 */
	public function showAction ()
	{
		$fid = $this->params->requests->getParam('fid', 0);
		$form = new Qss_Model_Form();
		if ( $fid )
		{
			$form->v_fInit($fid, $this->_user->user_dept_id, $this->_user->user_id);
		}
		if($this->b_fCheckRightsOnForm($form,4))
		{
			$this->html->form = $form;
				
			$table_display =  $this->params->requests->getParam('table_display', array());
			$chart_display =  $this->params->requests->getParam('chart_display', array());
			$chart =  $this->params->requests->getParam('chart', '');
			$tructung =  $this->params->requests->getParam('tructung', '');
			//$tructung = $tructung[0];
			$status =  $this->params->requests->getParam('status', '');
			$limit =  $this->params->requests->getParam('limit', 0);
			$params = $this->params->requests->getParams();
			$filter = array();
			$agr = array();
			$order = array();
			foreach($params as $key=>$value)
			{
				if(substr($key,0,7) == 'filter_' && $value != '')
				{
					$filter[substr($key,7,strlen($key))] = $value;
				}
				if(substr($key,0,4) == 'agr_' && $value != '')
				{
					$agr[substr($key,4,strlen($key))] = $value;
				}
				if(substr($key,0,6) == 'order_' && $value != '')
				{
					$order[substr($key,6,strlen($key))] = $value;
				}
			}
			$tructung = $this->params->requests->getParam('tructung', 0);
			$dataSQL = $form->getReport($table_display,$chart_display,$filter,$agr,$order,$limit,$status);
			$this->html->list = $dataSQL;
			$this->html->table_display = $table_display;
			$this->html->chart_display = $chart_display;
			$this->html->chart = $chart;
			$this->html->agr = $agr;
			$this->html->tructung = $tructung;
			$this->html->params = $params;
			$this->html->fid = $fid;
			$this->html->reportname = $this->params->requests->getParam('reportname', '');
			$this->html->showtable = $this->params->requests->getParam('showtable', 1);

		}
		$this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/quickreport.php';
		//$this->setLayoutRender(false);
	}
	public function saveAction ()
	{
		$params = $this->params->requests->getParams();
		$service = $this->services->Report->Save($this->_user->user_id,$params);
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);

	}
	public function deleteAction ()
	{
		$params = $this->params->requests->getParams();
		$service = $this->services->Report->Delete($this->_user->user_id,$params);
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);

	}

	public function listboxAction()
	{
		$params = $this->params->requests->getParams();

		$retval = array();
		$model  = new Qss_Model_Extra_Extra();
		$params['listboxVal']['val'] = $params['tag'];
		$filterByLookupArr = isset($params['filterByLookupArr'])?$params['filterByLookupArr']:array();
		$excludeObject = isset($params['excludeObject'])?$params['excludeObject']:array();
		$dataSQL = $model->getReportListBoxData( $params['listboxVal']
		, $params['getFields']
		, $filterByLookupArr
		, $excludeObject );

		foreach ($dataSQL as $item)
		{
			$tempArr = array();
			for($i = 1; $i <= $params['getFields']['num']; $i++)
			{
				$name = 'display'.$i;
				if(isset($item->$name) && $item->$name)
				{
					$tempArr[] = $item->$name;
				}
			}
			$level = (int)@$item->level;
			$level = ($level > 0)?($level -1):0;

			$retval[] = array('id'=>$item->id,'value'=>str_repeat('&nbsp;&nbsp;',$level) . implode(' - ', $tempArr));
		}
		echo Qss_Json::encode($retval);
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	public function comboboxAction()
	{
		$params = $this->params->requests->getParams();

		$retval = array();
		$model  = new Qss_Model_Extra_Extra();
		$filterByLookupArr = isset($params['filterByLookupArr'])?$params['filterByLookupArr']:array();
		$excludeObject = isset($params['excludeObject'])?$params['excludeObject']:array();
		$dataSQL = $model->getReportComboxboxData(    $params['getFields']
		, $filterByLookupArr
		, $excludeObject );
		 
		foreach ($dataSQL as $item)
		{
			$tempArr = array();
			for($i = 1; $i <= $params['getFields']['num']; $i++)
			{
				$name = 'display'.$i;

				if(isset($item->$name) && $item->$name)
				{
					$tempArr[] = $item->$name;
				}
			}
			$level = (int)@$item->level;
			$level = ($level > 0)?($level -1):0;
			$retval[] = array('id'=>$item->id,'value'=>str_repeat('&nbsp;&nbsp;',$level) . implode(' - ', $tempArr));
		}

		echo Qss_Json::encode($retval);
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

    public function dialboxAction()
    {
        $params  = $this->params->requests->getParams();
        $retval  = array();
        $model   = new Qss_Model_Extra_Extra();
        $dataSQL = $model->getReportDialBoxData(
            $params['ObjectCode']
            , $params['KeyField']
            , $params['DisplayFields']
            , $params['CompareFields']
            , $params['SearchText']
            , @$params['ExtendCondition']
            , @$params['OrderFields']
            , @$params['Join']);

        foreach ($dataSQL as $item) {
            $display = '';
            foreach ($params['DisplayFields'] as $field) {
                $display .= ($display?' - ':'').$item->{$field};
            }
            $retval[] = array('id'=>$item->{$params['KeyField']},'value'=>$display);
        }
        echo Qss_Json::encode($retval);
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}
?>