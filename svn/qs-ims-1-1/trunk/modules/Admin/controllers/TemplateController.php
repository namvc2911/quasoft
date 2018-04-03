<?php
/**
 *
 * @author HuyBD
 *
 */
class Admin_TemplateController extends Qss_Lib_Controller
{

	/**
	 *
	 * @return unknown_type
	 */
	public function init ()
	{
		$this->i_SecurityLevel = 4;
		parent::init();
		$this->headScript($this->params->requests->getBasePath() . '/js/admin-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/jquery.bgiframe.min.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/ajaxfileupload.js');
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
	 * @author HuyBD
	 * @return void
	 */
	public function webAction ()
	{
		$userinfo = $this->params->registers->get('userinfo');
		$cms = new Qss_Model_Admin_CMS();
		$this->html->cms = $cms->getCMSList($userinfo);
	}
	/**
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function webLoadAction ()
	{
		$userinfo = $this->params->registers->get('userinfo');
		$szSearch = $this->params->requests->getParam('search', 0);
		$cms = new Qss_Model_Admin_CMS();
		echo $this->views->Common->List($cms->getCMSList($userinfo, $szSearch), 'id', 'name');
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	/**
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function webEditAction ()
	{
		$userinfo = $this->params->registers->get('userinfo');
		$cms_id = $this->params->requests->getParam('cms_id', 0);
		if(!$cms_id)
		$cms_id = -1;
		$cms = new Qss_Model_Admin_CMS();
		$cms->init($cms_id);
		$this->html->cms_id = $cms->intCMSID;
		$this->html->name = $cms->szName;
		$dirArray = $cms->getJSCSS($userinfo->user_dept_id);
		$arrFile= array();
		$indexCount	= count($dirArray);
		$currentdir = QSS_DATA_DIR . '/jscss/'. $userinfo->user_dept_id;
		sort($dirArray);
		for($index=0; $index < $indexCount; $index++)
		{
			$filename = $currentdir.'/' .$dirArray[$index];
			$filetype = filetype($filename);
			if (substr("$dirArray[$index]", 0, 1) != "." && substr("$dirArray[$index]", 0, 1) != ".db" )
			{
				if($filetype != "dir")
				{
					$arrFile[] =$dirArray[$index];
				}
			}
		}
		$this->html->files= $arrFile;
		$this->html->jscss = $cms->arrJSCSS;
		$this->html->content = $cms->getCMS($userinfo);
	}
	/**
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function webSaveAction()
	{
		$user = $this->params->registers->get('userinfo');
		$params = $this->params->requests->getParams();
		$service = $this->services->Admin->Web->Save($params,$user);
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function webDeleteAction()
	{
		$user = $this->params->registers->get('userinfo');
		$params = $this->params->requests->getParams();
		$service = $this->services->Admin->Web->Save($params,$user);
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	/**
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function webJscssAction ()
	{
		$user = $this->params->registers->get('userinfo');
		$action = $this->params->requests->getParam('action');
		$currentdir = QSS_DATA_DIR . '/jscss/'. $user->user_dept_id;

		if(!is_dir($currentdir))
		{
			mkdir($currentdir,'0777');
		}
		elseif(sizeof($_POST))
		{
			if(isset($_FILES['epFile']))
			{
				if($_FILES['epFile']['error']==UPLOAD_ERR_OK)
				{
					$ext = strtolower(pathinfo($_FILES['epFile']['name'],PATHINFO_EXTENSION));
					if(in_array($ext,array("css","js")))
					{
						$scan = Qss_Lib_Util::scanFile($_FILES['epFile']["tmp_name"], $virusname);
						if ($scan != 0)
						{
							$this->html->message =  ($scan > 0)?'File bị nhiễm ' . $virusname:'Server chưa hỗ trợ chương trình diệt virus';
						}
						else
						{
							$fn = $currentdir . '/'.$_FILES['epFile']['name'];
							if(is_file($fn))
							unlink($fn);
							$ret = move_uploaded_file($_FILES['epFile']["tmp_name"],$fn);
						}
					}
					else
					{
						$this->html->message =  "<p color=red>Không hỗ trợ upload dạng ".strtolower($_FILES['epFile']['type']);
					}
				}
			}
		}
		$cms = new Qss_Model_Admin_CMS();
		$dirArray = $cms->getJSCSS($user->user_dept_id);
		$arrFile= array();
		$indexCount	= count($dirArray);
		sort($dirArray);
		for($index=0; $index < $indexCount; $index++)
		{
			$filename = $currentdir.'/' .$dirArray[$index];
			$filetype = filetype($filename);
			if (substr("$dirArray[$index]", 0, 1) != "." && substr("$dirArray[$index]", 0, 1) != ".db" )
			{
				if($filetype != "dir")
				{
					$arrFile[] =$dirArray[$index];
				}
			}
		}
		$this->html->arrFile = $arrFile;
	}
	/**
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function webJscssEditAction ()
	{
		$user = $this->params->registers->get('userinfo');
		$file = $this->params->requests->getParam('file');

		$filename = QSS_DATA_DIR . '/jscss/'. $user->user_dept_id.'/'.$file;
		$content = '';
		if(file_exists($filename))
		{
			$content = file_get_contents($filename);
		}
		$this->html->file= $file;
		$this->html->content = $content;
	}
	/**
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function webJscssDeleteAction ()
	{
		$user = $this->params->registers->get('userinfo');
		$file = $this->params->requests->getParam('file');

		$filename = QSS_DATA_DIR . '/jscss/'. $user->user_dept_id.'/'.$file;
		if(file_exists($filename))
		{
			unlink($filename);
		}
		echo Qss_Json::encode(array('error'=>0));
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	/**
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function webJscssSaveAction ()
	{
		$user = $this->params->registers->get('userinfo');
		$params = $this->params->requests->getParams();
		$service = $this->services->Admin->Web->Jscss->Save($params,$user);
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	/**
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function formWebAction ()
	{
		$this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/design.php';
		$userinfo = $this->params->registers->get('userinfo');
		$fid = $this->params->requests->getParam('fid',0);
		$this->html->fid = $fid;
		$design = new Qss_Model_Admin_Design($this->_user->user_dept_id,Qss_Lib_Const::FORM_DESIGN_FORM);
		$this->html->forms = $design->a_fGetFormByUser($this->_user);
	}
	/**
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function formWebReloadAction ()
	{
		$userinfo = $this->params->registers->get('userinfo');
		$fid = $this->params->requests->getParam('fid',0);
		$design = new Qss_Model_Admin_Design($userinfo->user_dept_id,Qss_Lib_Const::FORM_DESIGN_FORM);
		$designs = $design->a_fGetDesignByForm($fid);
		echo $this->views->Common->List($designs, 'ID', 'Description');
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	/**
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function formWebEditAction ()
	{
		$userinfo = $this->params->registers->get('userinfo');
		$fid = $this->params->requests->getParam('fid',0);
		$designid = $this->params->requests->getParam('designid',0);
		$design = new Qss_Model_Admin_Design($userinfo->user_dept_id,Qss_Lib_Const::FORM_DESIGN_FORM);
		$design->v_fInit($designid);
		$this->html->fid = (int) $fid;
		$this->html->design = $design;
		$this->html->content = $design->getDesignByID($fid,$designid);
	}
	/**
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function formWebSaveAction ()
	{
		$userinfo = $this->params->registers->get('userinfo');
		$params = $this->params->requests->getParams();
		$fid = $this->params->requests->getParam('fid',0);
		if ( $this->params->requests->isAjax() )
		{
			$service = $this->services->Admin->Template->Save($params,Qss_Lib_Const::FORM_DESIGN_FORM);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);

	}
	/**
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function formWebDeleteAction ()
	{
		$userinfo = $this->params->registers->get('userinfo');
		$designid = $this->params->requests->getParam('designid',0);
		if ( $this->params->requests->isAjax() )
		{
			$service = $this->services->Admin->Template->Delete($designid,Qss_Lib_Const::FORM_DESIGN_FORM);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);

	}
	//BEGIN

	/**
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function objectWebAction ()
	{
		$this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/design.php';
		$userinfo = $this->params->registers->get('userinfo');
		$fid = $this->params->requests->getParam('fid',0);
		$this->html->fid = $fid;
		$design = new Qss_Model_Admin_Design($this->_user->user_dept_id,Qss_Lib_Const::FORM_DESIGN_OBJECT);
		$this->html->forms = $design->a_fGetObjectByUser($this->_user);
	}
	/**
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function objectWebReloadAction ()
	{
		$userinfo = $this->params->registers->get('userinfo');
		$fid = $this->params->requests->getParam('fid',0);
		$design = new Qss_Model_Admin_Design($userinfo->user_dept_id,Qss_Lib_Const::FORM_DESIGN_OBJECT);
		$designs = $design->a_fGetDesignByForm($fid);
		echo $this->views->Common->List($designs, 'ID', 'Description');
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	/**
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function objectWebEditAction ()
	{
		$userinfo = $this->params->registers->get('userinfo');
		$fid = $this->params->requests->getParam('fid',0);
		$designid = $this->params->requests->getParam('designid',0);
		$design = new Qss_Model_Admin_Design($userinfo->user_dept_id,Qss_Lib_Const::FORM_DESIGN_OBJECT);
		$design->v_fInit($designid);
		$this->html->fid = (int) $fid;
		$this->html->design = $design;
		$this->html->content = $design->getDesignByID($fid,$designid);
	}
	/**
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function objectWebSaveAction ()
	{
		$userinfo = $this->params->registers->get('userinfo');
		$params = $this->params->requests->getParams();
		$fid = $this->params->requests->getParam('fid',0);
		if ( $this->params->requests->isAjax() )
		{
			$service = $this->services->Admin->Template->Save($params,Qss_Lib_Const::FORM_DESIGN_OBJECT);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);

	}
	/**
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function objectWebDeleteAction ()
	{
		$userinfo = $this->params->registers->get('userinfo');
		$designid = $this->params->requests->getParam('designid',0);
		if ( $this->params->requests->isAjax() )
		{
			$service = $this->services->Admin->Template->Delete($designid,Qss_Lib_Const::FORM_DESIGN_OBJECT);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);

	}

	public function browserModuleAction()
	{
		$user = $this->params->registers->get('userinfo');
		$folder = $this->params->requests->getParam('folder');
		$fid = $this->params->requests->getParam('intRefFID');
		$name =  $this->params->requests->getParam('name');
		$record_id =  $this->params->requests->getParam('record_id');
		$content_id =  $this->params->requests->getParam('content_id');
		if(!$fid)
		{
			if(in_array($name,array('dat','lb','ogrid','otitle')))
			$fid = (int)$record_id;
			else
			$fid = (int)$content_id;
		}
		if(in_array($name,array('dat','lb','ogrid','otitle')))
		$fieldid = (int)$content_id;
		else
		$fieldid = (int)$record_id;
		$design = new Qss_Model_Admin_Design($user->user_dept_id,Qss_Lib_Const::FORM_DESIGN_FORM);
		$br = new Qss_Model_Admin_Browser(Qss_Lib_Const::TINY_PLUGIN_MODULE, $folder);
		$this->html->forms = $br->getRefFIDEle($fid, $user);
		$this->html->data = $br->getRefDataIDEle($fid, $user);
		$this->html->designs = $design->a_fGetDesignByForm($fid);
		$this->html->fid = $fid;
		$this->html->limit = $this->params->requests->getParam('limit',0);
		$this->html->page  = $this->params->requests->getParam('name','');
		$this->html->design_id = $this->params->requests->getParam('design_id',0);
		$this->html->fieldid = $fieldid;
		$this->setLayoutRender(false);
	}

	public function browserObjectAction()
	{

		$user = $this->params->registers->get('userinfo');
		$folder = $this->params->requests->getParam('folder');
		$fid = $this->params->requests->getParam('intRefFID');
		$name =  $this->params->requests->getParam('name');
		$record_id =  $this->params->requests->getParam('record_id');
		$content_id =  $this->params->requests->getParam('content_id');
		if(!$fid)
		{
			if(in_array($name,array('dat','lb','ogrid','otitle')))
			$fid = (int)$record_id;
			else
			$fid = (int)$content_id;
		}
		$objid = $this->params->requests->getParam('intRefObjID');
		if(!$objid)
		{
			if(in_array($name,array('dat','lb','ogrid','otitle')))
			$objid = (int)$content_id;
			else
			$objid = (int)$record_id;
		}
		$design = new Qss_Model_Admin_Design($user->user_dept_id,Qss_Lib_Const::FORM_DESIGN_OBJECT);
		$br = new Qss_Model_Admin_Browser(Qss_Lib_Const::TINY_PLUGIN_OBJECT, $folder);
		$this->html->forms = $br->getRefFIDEle($fid, $user);
		$this->html->objects = $br->getRefObjIDEle($fid);
		$this->html->designs = $design->a_fGetDesignByForm($objid);
		$this->html->fid = $fid;
		$this->html->fieldid = $fieldid;
		$this->html->objectid = $objid;
		$this->html->design_id = $this->params->requests->getParam('design_id');
		$this->setLayoutRender(false);

	}
	/**
	 * Add element of object to template
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function browserElementAction()
	{
		$user = $this->params->registers->get('userinfo');
		$folder = $this->params->requests->getParam('folder');
		$fid = $this->params->requests->getParam('intRefFID');
		$name =  $this->params->requests->getParam('name');
		$record_id =  $this->params->requests->getParam('record_id');
		$content_id =  $this->params->requests->getParam('content_id');
		if(!$fid)
		{
			if(in_array($name,array('dat','lb','ogrid','otitle')))
			$fid = (int)$record_id;
			else
			$fid = (int)$content_id;
		}
		if(in_array($name,array('dat','lb','ogrid','otitle')))
		$fieldid = (int)$content_id;
		else
		$fieldid = (int)$record_id;
		$br = new Qss_Model_Admin_Browser(Qss_Lib_Const::TINY_PLUGIN_ELEMENT, $folder);
		$this->html->forms = $br->getRefFIDEle($fid, $user);
		$this->html->fields = $br->getRefFieldIDEle($fid);
		$this->html->fid = $fid;
		$this->html->fieldid = $fieldid;
		$this->setLayoutRender(false);
	}
	/**
	 * Get to link in template
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function browserFileAction()
	{
		$user = $this->params->registers->get('userinfo');
		$folder = $this->params->requests->getParam('folder');
		$fid = $this->params->requests->getParam('intRefFID');
		$name =  $this->params->requests->getParam('name');
		$record_id =  $this->params->requests->getParam('record_id');
		$content_id =  $this->params->requests->getParam('content_id');
		if(!$fid)
		{
			if(in_array($name,array('dat','lb','ogrid','otitle')))
			$fid = (int)$record_id;
			else
			$fid = (int)$content_id;
		}
		if(in_array($name,array('dat','lb','ogrid','otitle')))
		$fieldid = (int)$content_id;
		else
		$fieldid = (int)$record_id;
		$design = new Qss_Model_Admin_Design($user->user_dept_id,Qss_Lib_Const::FORM_DESIGN_FORM);
		$br = new Qss_Model_Admin_Browser(Qss_Lib_Const::TINY_PLUGIN_FILE, $folder);
		$cms = new Qss_Model_Admin_CMS();
		$this->html->forms = $br->getRefFIDEle($fid, $user);
		$this->html->data = $br->getRefDataIDEle($fid, $user);
		$this->html->designs = $design->a_fGetDesignByForm($fid);
		$this->html->cms = $cms->getCMSList($user);
		$this->html->fid = $fid;
		$this->html->fieldid = $fieldid;
		$this->setLayoutRender(false);
	}
	/**
	 * Insert image to template
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function browserImageAction()
	{
		$user = $this->params->registers->get('userinfo');
		$folder = $this->params->requests->getParam('folder');
		$action = $this->params->requests->getParam('action');
		if($action == 'delete')
		{
			$this->deleteImage();
		}
		elseif(sizeof($_POST))
		{
			if(isset($_FILES['epFile']))
			{
				if($_FILES['epFile']['error']==UPLOAD_ERR_OK)
				{
					$limitedext=array("image/png","image/gif","image/jpg","image/jpeg","image/pjpeg","image/bmp","audio/x-ms-wma");
					if(in_array(strtolower($_FILES['epFile']["type"]),$limitedext))
					{
						$scan = Qss_Lib_Util::scanFile($_FILES['epFile']["tmp_name"], $virusname);
						if ($scan != 0)
						{
							$this->html->message =  ($scan > 0)?'File bị nhiễm ' . $virusname:'Server chưa hỗ trợ chương trình diệt virus';
						}
						else
						{
							$fn = QSS_DATA_DIR . '/' . Qss_Lib_Const::TINY_PLUGIN_IMAGE . '/' .$user->user_dept_id . '/'.$folder.'/' .$_FILES['epFile']['name'];
							if(is_file($fn))
							unlink($fn);
							$ret = move_uploaded_file($_FILES['epFile']["tmp_name"],$fn);
						}
					}
					else
					{
						$this->html->message =  "<p color=red>Không hỗ trợ upload dạng ".strtolower($_FILES['epFile']['type']);
					}
				}
			}

			if(isset($_POST['epFolder']))
			{
				@mkdir(QSS_DATA_DIR . '/' . Qss_Lib_Const::TINY_PLUGIN_IMAGE . '/' .$user->user_dept_id . '/' .$_POST['epFolder']);
			}
		}
		$this->html->css = $this->headLink($this->params->requests->getBasePath() . '/css/form.css');

		$br = new Qss_Model_Admin_Browser(Qss_Lib_Const::TINY_PLUGIN_IMAGE, $folder);
		$currentdir = QSS_DATA_DIR .'/'. Qss_Lib_Const::TINY_PLUGIN_IMAGE . '/' . $user->user_dept_id . '/' . $folder;
		if(!is_dir($currentdir))
		{
			$currentdir = QSS_DATA_DIR .'/'. Qss_Lib_Const::TINY_PLUGIN_IMAGE . '/' . $user->user_dept_id ;
			$folder = '';
		}
		$dirArray = $br->getFileView($user,$currentdir);
		$arrFolder = array();
		$arrFile= array();
		$indexCount	= count($dirArray);
		sort($dirArray);
		for($index=0; $index < $indexCount; $index++)
		{
			$filename = $currentdir.'/' . $folder.'/'.$dirArray[$index];
			$filetype = filetype($filename);
			if (substr("$dirArray[$index]", 0, 1) != "." && substr("$dirArray[$index]", 0, 1) != ".db" )
			{
				if($filetype == "dir")
				{
					$arrFolder[] =$dirArray[$index];
				}
				else
				{
					$arrFile[] =$dirArray[$index];
				}
			}
		}
		$this->html->arrFolder = $arrFolder;
		$this->html->arrFile = $arrFile;
		$this->html->folder = $folder;
		$this->html->dataURL = QSS_DATA_DIR;
		$this->setLayoutRender(false);
	}
	/**
	 *
	 * @return void
	 */
	public function imageAction ()
	{
		$user = $this->params->registers->get('userinfo');
		$folder = $this->params->requests->getParam('folder');
		$file = QSS_DATA_DIR . '/' . Qss_Lib_Const::TINY_PLUGIN_IMAGE . '/' .$user->user_dept_id .'/'. $folder.'/'. $this->params->requests->getParam('file');

		if ( file_exists($file) )
		{
			header("Content-type: application/force-download");
			header("Content-Transfer-Encoding: Binary");
			header("Content-length: " . filesize($file));
			header("Content-disposition: attachment; filename=\"" . basename($file) . "\"");
			readfile("$file");
			die();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	/**
	 *
	 * @return void
	 */
	public function deleteImage ()
	{
		$user = $this->params->registers->get('userinfo');
		$folder = $this->params->requests->getParam('folder');
		$file = $this->params->requests->getParam('file');
		if($file)
		{
			$fn = QSS_DATA_DIR .'/' . Qss_Lib_Const::TINY_PLUGIN_IMAGE . '/' .$user->user_dept_id .'/'. $folder.'/'. $file;

		}
		else
		{
			$fn = QSS_DATA_DIR . '/' . Qss_Lib_Const::TINY_PLUGIN_IMAGE . '/' .$user->user_dept_id .'/'. $folder;
		}
		if (!$file)
		{
			rmdir($fn);
		}
		else
		{
			unlink($fn);
		}
	}
	/**
	 * Insert image to template
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function browserMediaAction()
	{
		$user = $this->params->registers->get('userinfo');
		$folder = $this->params->requests->getParam('folder');
		$action = $this->params->requests->getParam('action');

		if($action == 'delete')
		{
			$this->deleteImage();
		}
		elseif(sizeof($_POST))
		{
			if(isset($_FILES['epFile']))
			{
				if($_FILES['epFile']['error']==UPLOAD_ERR_OK)
				{
					$limitedext=array("image/png","image/gif","image/jpg","image/jpeg","image/pjpeg","image/bmp","audio/x-ms-wma");
					if(in_array(strtolower($_FILES['epFile']["type"]),$limitedext))
					{
						$scan = Qss_Lib_Util::scanFile($_FILES['epFile']["tmp_name"], $virusname);
						if ($scan != 0)
						{
							$this->html->message =  ($scan > 0)?'File bị nhiễm ' . $virusname:'Server chưa hỗ trợ chương trình diệt virus';
						}
						else
						{
							$fn = QSS_DATA_DIR . '/media/' .$user->user_dept_id . '/'.$folder.'/' .$_FILES['epFile']['name'];
							if(is_file($fn))
							unlink($fn);
							$ret = move_uploaded_file($_FILES['epFile']["tmp_name"],$fn);
						}
					}
					else
					{
						$this->html->message =  "<p color=red>Không hỗ trợ upload dạng ".strtolower($_FILES['epFile']['type']);
					}
				}
			}

			if(isset($_POST['epFolder']))
			{
				@mkdir(QSS_DATA_DIR . '/media/' .$user->user_dept_id . '/' .$_POST['epFolder']);
			}
		}
		$this->html->css = $this->headLink($this->params->requests->getBasePath() . '/css/form.css');

		$br = new Qss_Model_Admin_Browser(Qss_Lib_Const::TINY_PLUGIN_MEDIA, $folder);
		$currentdir = QSS_DATA_DIR .'/'. Qss_Lib_Const::TINY_PLUGIN_MEDIA . '/' . $user->user_dept_id . '/' . $folder;
		if(!is_dir($currentdir))
		{
			$currentdir = QSS_DATA_DIR .'/'. Qss_Lib_Const::TINY_PLUGIN_MEDIA . '/' . $user->user_dept_id ;
			$folder = '';
		}
		$dirArray = $br->getFileView($user,$currentdir);
		$arrFolder = array();
		$arrFile= array();
		$indexCount	= count($dirArray);
		sort($dirArray);
		for($index=0; $index < $indexCount; $index++)
		{
			$filename = $currentdir.'/' . $folder.'/'.$dirArray[$index];
			$filetype = filetype($filename);
			if (substr("$dirArray[$index]", 0, 1) != "." && substr("$dirArray[$index]", 0, 1) != ".db" )
			{
				if($filetype == "dir")
				{
					$arrFolder[] =$dirArray[$index];
				}
				else
				{
					$arrFile[] =$dirArray[$index];
				}
			}
		}
		$this->html->arrFolder = $arrFolder;
		$this->html->arrFile = $arrFile;
		$this->html->folder = $folder;
		$this->html->dataURL = QSS_DATA_DIR;
		$this->setLayoutRender(false);
	}
}
?>