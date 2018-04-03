<?php
/**
 *
 * @author HuyBD
 *
 */
class User_CurrenciesController extends Qss_Lib_Controller
{
	protected $_cookie = 'currencies';

	protected $_model;

	/**
	 *
	 * @return unknown_type
	 */
	public function init ()
	{
		parent::init();
		$this->_model = new Qss_Model_Currency();
		$this->headScript($this->params->requests->getBasePath() . '/js/currencies-list.js');
	}

	/**
	 *
	 * @return void
	 */
	public function indexAction ()
	{
		$this->headScript($this->params->requests->getBasePath() . '/js/popup-select.js');
		$fid = $this->params->requests->getParam('fid', 0);
		$user = $this->params->registers->get('userinfo', null);
		$currentpage = (int) $this->params->cookies->get($this->_cookie . '_currentpage', 1);
		$currentpage = $currentpage ? $currentpage : 1;
		$limit = $this->params->cookies->get($this->_cookie . '_limit', 20);
		$fieldorder = $this->params->cookies->get($this->_cookie . '_fieldorder', '0');
		$ordertype = $this->params->cookies->get($this->_cookie . '_ordertype', '0');
		$filter = $this->params->cookies->get($this->_cookie. '_filter', 'a:0:{}');
		$i_GroupBy = $this->params->cookies->get($this->_cookie . '_groupby', '0');
		//		$filter ? $filter : 'a:0:{}';
		$filter = unserialize($filter);
		//if ( $fid )
		{
			//$o_Form = new Qss_Model_Form();
			//$o_Form->v_fInit($fid, $user->user_dept_id, $user->user_id);
			//if($this->b_fCheckRightsOnForm($o_Form,4))
			{
				$data = $this->_model->getAll($currentpage, $limit, $fieldorder, $ordertype ? 'ASC' : 'DESC', $filter,$i_GroupBy);
				$this->html->listview = $this->views->Common->GridEdit($this->_model,$data,'/user/currencies', $currentpage, $limit, $fieldorder, $ordertype ? 'ASC' : 'DESC',$i_GroupBy);
				$this->html->searchform = $this->views->Common->Search($this->_model, $filter);
				$count = $this->_model->countAll($filter);
				$this->html->pager = $this->views->Common->Pager($this->_model, $data, $count, $currentpage, $limit,$i_GroupBy);
			}
		}
		//else
		//{
		//	$this->setHtmlRender(false);
		//}
	}

	public function gridAction ()
	{
		if ( $this->params->requests->isAjax() )
		{
			$this->params->responses->clearBody();
			//$i_FID = $this->params->requests->getParam('fid', 0);
			//if ( $i_FID )
			{
				//$o_Form = new Qss_Model_Form();
				//$o_Form->v_fInit($i_FID, $user->user_dept_id, $user->user_id);
				//if($this->b_fCheckRightsOnForm($o_Form,4))
				{
					$currentpage = $this->params->requests->getParam('pageno', 1);
					$limit = $this->params->requests->getParam('perpage', 20);
					$fieldorder = $this->params->requests->getParam('fieldorder', 0);
					$ordertype = $this->params->cookies->get($this->_cookie . '_ordertype', 0);
					$i_GroupBy = $this->params->requests->getParam('groupby', 0);
					if ( $fieldorder != 0 )
					{
						$ordertype = !$ordertype;
					}
					else
					{
						$fieldorder = $fieldorder ? $fieldorder : $this->params->cookies->get($this->_cookie . '_fieldorder', 0);
					}
					$filter = $this->a_fGetFilter();
					$this->params->cookies->set($this->_cookie . '_currentpage', $currentpage);
					$this->params->cookies->set($this->_cookie . '_limit', $limit);
					$this->params->cookies->set($this->_cookie . '_filter', serialize($filter));
					$this->params->cookies->set($this->_cookie . '_ordertype', $ordertype);
					$this->params->cookies->set($this->_cookie . '_fieldorder', $fieldorder);
					$this->params->cookies->set($this->_cookie . '_groupby', $i_GroupBy);
						
					$data = $this->_model->getAll($currentpage, $limit, $fieldorder, $ordertype ? 'ASC' : 'DESC', $filter,$i_GroupBy);
					$count = $this->_model->countAll($filter);
					echo $this->views->Common->Pager($this->_model, $data, $count, $currentpage, $limit,$i_GroupBy);
					echo $this->views->Common->GridEdit($this->_model,$data,'/user/currencies', $currentpage, $limit, $fieldorder, $ordertype ? 'ASC' : 'DESC',$i_GroupBy);
				}
			}
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	/**
	 *
	 * @return void
	 */
	public function saveAction ()
	{
		if ( $this->params->requests->isAjax() )
		{
			$params = $this->params->requests->getParams();
			$service = $this->services->Currencies->Save($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function deleteAction ()
	{
		if ( $this->params->requests->isAjax() )
		{
			$id = $this->params->requests->getParam('id',0);
			$retval = array('error'=>false);
			if($this->_model->delete($id))
			{
				$retval = array('error'=>false,'message'=>'Bản ghi không tồn tại.');
			}
			echo Qss_Json::encode($retval);
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
}
?>