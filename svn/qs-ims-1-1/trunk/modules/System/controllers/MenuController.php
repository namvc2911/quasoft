<?php
/**
 *
 * The system form management
 *
 * @author HuyBD
 *
 */
class System_MenuController extends Qss_Lib_Controller
{
	/**
	 *
	 * @return unknown_type
	 */
	public function init ()
	{
		$this->i_SecurityLevel = 1;
		parent::init();
		$this->headScript($this->params->requests->getBasePath() . '/js/system-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/jquery.bgiframe.min.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/ajaxfileupload.js');
	}
	public function indexAction()
	{
		$id = $this->params->requests->getParam('id',0);
		$mid = $this->params->requests->getParam('mid',0);
		$menu = new Qss_Model_Menu();
		$this->html->id = $id;
		$this->html->mid = $mid;
		$this->html->menu = $menu->getAll($id,$mid);
		$this->html->all = $menu->getMainMenu();
	}
	public function editAction()
	{
		$id = $this->params->requests->getParam('id',0);
		$mid = $this->params->requests->getParam('mid',0);
		$parentid = $this->params->requests->getParam('parentid',0);
		$menu = new Qss_Model_Menu();
		$this->html->mainmenu = $menu->getAll(0,$mid);
		$this->html->mains = $menu->getMainMenu();
		/*$arrTmp = array();
		 $arrTmp[0] = '--- '.(38,'Toàn bộ').' ---';
		 foreach ($all as $item)
		 {
			$arrTmp[$item->MenuID] = $item->MenuName;
			}
			*/
		$lang = new Qss_Model_System_Language();
		$this->html->languages = $lang->getAll(1);
		$menuitem = $menu->getById($id);
		//$this->html->mainmenu = $arrTmp;
		$this->html->mid = $mid;
		$this->html->menu = $menuitem;
		$this->html->configs = $menu->getMenuConfig($id);

		$this->html->id = $id;
		$this->html->parentid = $menuitem->ParentID?$menuitem->ParentID:$parentid;
	}
	public function saveAction()
	{
		/* Use same name in databse */
		$a_Params = $this->params->requests->getParams();
		if ( $this->params->requests->isAjax())
		{
			$service = $this->services->Menu->Save($a_Params);
			echo $service->getMessage();
		}

		/* We process in ajax, no need to render view and layout */
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function deleteAction()
	{
		/* Use same name in databse */
		$id = $this->params->requests->getParam('id',0);
		if ( $this->params->requests->isAjax())
		{
			$menu = new Qss_Model_Menu();
			$menu->delete($id);
			echo Qss_Json::encode(array('error'=>0));
		}

		/* We process in ajax, no need to render view and layout */
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
}
?>