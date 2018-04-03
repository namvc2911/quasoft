<?php
/**
 *
 * @author HuyBD
 *
 */
class Static_M016Controller extends Qss_Lib_Controller {
	protected $_model;
	/**
	 *
	 * @return unknown_type
	 */
	public function init() {
		parent::init();
		$this->_model = new Qss_Model_Document();
		$this->headScript($this->params->requests->getBasePath() . '/js/document-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/jquery.bgiframe.min.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/ajaxfileupload.js');
	}

	/**
	 *
	 * @return void
	 */
	public function indexAction() {
		$currentpage = (int) $this->params->cookies->get('form_doc_currentpage', 1);
		$currentpage = $currentpage ? $currentpage : 1;
		$limit = $this->params->cookies->get('form_doc_limit', 20);
		$fieldorder = $this->params->cookies->get('form_doc_fieldorder', '0');
		$ordertype = $this->params->cookies->get('form_doc_ordertype', '0');
		$filter = $this->params->cookies->get('form_doc_filter', 'a:0:{}');
		$i_GroupBy = $this->params->cookies->get('form_doc_groupby', '0');
		//		$filter ? $filter : 'a:0:{}';
		$filter = unserialize($filter);
		$this->html->filters = $filter;
		$user = new Qss_Model_Admin_User();
		$this->html->users = $user->a_fGetAllNormal();
		$this->html->folder = $this->_model->getFolders($this->_user->user_id);
		$this->html->listview = $this->views->Document->Grid($this->_user, $currentpage, $limit, $fieldorder, $ordertype ? 'ASC' : 'DESC', $i_GroupBy, $filter);
		// print_r($this->html->listview);die;
	}
	public function gridAction() {
		$currentpage = (int) $this->params->requests->getParam('pageno', 1);
		$limit = $this->params->requests->getParam('perpage', 20);
		$fieldorder = $this->params->requests->getParam('fieldorder', '0');
		$ordertype = $this->params->cookies->get('form_doc_ordertype', '0');
		$filter = $this->a_fGetFilter();
		$i_GroupBy = $this->params->requests->getParam('groupby', '0');
		if ($fieldorder != 0) {
			$ordertype = !$ordertype;
		} else {
			$fieldorder = $fieldorder ? $fieldorder : $this->params->cookies->get('form_doc_fieldorder', 0);
		}
		//		$filter ? $filter : 'a:0:{}';
		$this->params->cookies->set('form_doc_currentpage', $currentpage);
		$this->params->cookies->set('form_doc_limit', $limit);
		$this->params->cookies->set('form_doc_filter', serialize($filter));
		$this->params->cookies->set('form_doc_ordertype', $ordertype);
		$this->params->cookies->set('form_doc_fieldorder', $fieldorder);
		$this->params->cookies->set('form_doc_groupby', $i_GroupBy);
		echo $this->views->Document->Grid($this->_user, $currentpage, $limit, $fieldorder, $ordertype ? 'ASC' : 'DESC', $i_GroupBy, $filter);
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	/**
	 *
	 * @return void
	 */
	public function uploadAction() {
		$retval = new stdClass();
		$retval->error = 0;
		$retval->message = '';
		$destfile = '';
		$filename = '';
		$id = 0;
		if (sizeof($_FILES)) {
			$file = array_shift($_FILES);
			if ($file['error'] == UPLOAD_ERR_OK) {
				$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
				//$limitedext = array("application/vnd.ms-excel","application/msexcel","application/vnd.ms-word","application/msword","application/pdf","image/png", "image/gif", "image/jpg", "image/jpeg", "image/pjpeg", "image/bmp");
				$scan = Qss_Lib_Util::scanFile($file["tmp_name"], $virusname);
				if ($scan != 0) {
					$retval->error = 1;
					$retval->message = ($scan > 0) ? 'File bị nhiễm ' . $virusname : 'Server không thể quét virus trên file này';
				} else {
					$filename = $file['name'];
					$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
					$data = array('id' => 0,
						'docno' => uniqid(),
						'name' => $filename,
						'ext' => $ext,
						'size' => $file['size'],
						'uid' => $this->_user->user_id,
						'folder' => $this->params->requests->getParam('folder'));
					$id = $this->_model->save($data);
					if (!is_dir(QSS_DATA_DIR . "/documents/")) {
						mkdir(QSS_DATA_DIR . "/documents/");
					}
					$destfile = QSS_DATA_DIR . "/documents/" . $id . "." . $ext;
					$ret = @move_uploaded_file($file["tmp_name"], $destfile);
				}
			}
		}
		$retval->id = $id;
		$retval->name = $filename;
		/* Endcode result to Json & push to js */
		echo (Qss_Json::encode($retval));
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function attachAction() {
		if ($this->params->requests->isAjax()) {
			$i_CurrentPage = $this->params->requests->getParam('pageno', 1);
			$i_PerPage = $this->params->requests->getParam('perpage', 20);
			$i_FieldOrder = $this->params->requests->getParam('fieldorder', 0);
			$i_OrderType = $this->params->cookies->get('attach_doc_ordertype', 0);
			$i_GroupBy = $this->params->requests->getParam('groupby', 0);
			if ($i_FieldOrder != 0) {
				$i_OrderType = !$i_OrderType;
			}
			$this->params->cookies->set('attach_doc_ordertype', $i_OrderType);
			echo $this->views->Document->Attach($this->_user, $i_CurrentPage, $i_PerPage, $i_FieldOrder, $i_OrderType ? 'ASC' : 'DESC', $i_GroupBy);
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function editAction() {
		$id = $this->params->requests->getParam('id', 0);
		$this->html->document = $this->_model->getByID($id, $this->_user->user_id);
		$this->html->refers = $this->_model->getFormRefer($id);
		$this->html->members = $this->_model->getMembers($id);
	}
	public function uploadfileAction() {
		$retval = new stdClass();
		$retval->error = 0;
		$retval->message = '';
		$destfile = '';
		$name = '';
		if (sizeof($_FILES)) {
			$file = array_shift($_FILES);
			if ($file['error'] == UPLOAD_ERR_OK) {
				$filename = uniqid();
				$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
				//$limitedext = array("application/vnd.ms-excel","application/msexcel","application/vnd.ms-word","application/msword","application/pdf","image/png", "image/gif", "image/jpg", "image/jpeg", "image/pjpeg", "image/bmp");
				$scan = Qss_Lib_Util::scanFile($file["tmp_name"], $virusname);
				if ($scan != 0) {
					$retval->error = 1;
					$retval->message = ($scan > 0) ? 'File bị nhiễm ' . $virusname : 'Server không thể quét virus trên file này';
				} else {
					$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
					$destfile = QSS_DATA_DIR . "/tmp/" . $filename . "." . $ext;
					$ret = @move_uploaded_file($file["tmp_name"], $destfile);
					$name = $file['name'];
				}
			}
		}
		$retval->name = $name;
		$retval->value = $filename . "." . $ext;
		/* Endcode result to Json & push to js */
		echo (Qss_Json::encode($retval));
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function saveAction() {
		$params = $this->params->requests->getParams();
		$params['uid'] = $this->_user->user_id;
		$params['docno'] = uniqid();
		$id = $this->params->requests->getParam('id', 0);
		$service = $this->services->Document->Check($this->_user->user_id, $id, 1);
		if ($service->getData()) {
			$service = $this->services->Document->Save($params);
		}
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function downloadAction() {
		$id = $this->params->requests->getParam('id', 0);
		$service = $this->services->Document->Check($this->_user->user_id, $id, 2);
		if (1 || $service->getData()) {
			$doc = $this->_model->getById($id, $this->_user->user_id);
			if ($doc) {
				$file = QSS_DATA_DIR . '/documents/' . $doc->DID . '.' . $doc->Ext;
				if (file_exists($file)) {
					header("Content-type: application/force-download");
					header("Content-Transfer-Encoding: Binary");
					header("Content-length: " . filesize($file));
					header("Content-disposition: attachment; filename=\"{$doc->Name}.{$doc->Ext}\"");
					readfile("$file");
					die();
				} else {
					$service->setMessage('Tài liệu đã bị xóa');
				}
			}
		}
		echo $service->getMessage(Qss_Service_Abstract::TYPE_HTML);
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function deleteAction() {
		$id = $this->params->requests->getParam('id', 0);
		$service = $this->services->Document->Check($this->_user->user_id, $id, 1);
		if ($id && $service->getData()) {
			$doc = $this->_model->getById($id, $this->_user->user_id);
			$this->_model->emptyMember($id);
			if ($doc) {
				$fn = QSS_DATA_DIR . '/documents/' . $doc->DID . '.' . $doc->Ext;
				if (file_exists($fn)) {
					unlink($fn);
				}
			}
			$this->_model->delete($id);
		}
		if (!$id) {
			$service->setMessage('Bản ghi không tồn tại.');
		}
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function selectAction() {
		if ($this->params->requests->isAjax()) {
			$ifid = $this->params->requests->getParam('ifid', 0);
			$deptid = $this->params->requests->getParam('deptid', 0);
			$form = new Qss_Model_Form();
			if ($ifid && $deptid) {
				$form->initData($ifid, $deptid);
				$i_CurrentPage = $this->params->requests->getParam('pageno', 1);
				$i_PerPage = $this->params->requests->getParam('perpage', 20);
				$i_FieldOrder = $this->params->requests->getParam('fieldorder', 0);
				$i_OrderType = $this->params->cookies->get('attach_doc_ordertype', 0);
				$i_GroupBy = $this->params->requests->getParam('groupby', 0);
				if ($i_FieldOrder != 0) {
					$i_OrderType = !$i_OrderType;
				}
				$this->params->cookies->set('attach_doc_ordertype', $i_OrderType);
				echo $this->views->Document->Select($form, $i_CurrentPage, $i_PerPage, $i_FieldOrder, $i_OrderType ? 'ASC' : 'DESC', $i_GroupBy);
			}
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function tagAction() {
		$retval = array();
		$tag = $this->params->requests->getParam('tag');
		$ifid = $this->params->requests->getParam('ifid', 0);
		if ($tag) {
			$model = new Qss_Model_Document();
			$dataSQL = $model->searchTag($this->_user->user_id, $tag, $ifid);
			foreach ($dataSQL as $item) {
				$retval[] = array('id' => $item->DID, 'value' => $item->Name . (isset($item->Folder) ? " ({$item->Folder})" : ''));
			}
		}
		echo Qss_Json::encode($retval);
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function attachFormAction() {
		$id = $this->params->requests->getParam('id', 0);
		$ifid = $this->params->requests->getParam('ifid', 0);
		$form = new Qss_Model_Form();
		$form->initData($ifid, $this->_user->user_dept_id);
		if ($this->b_fCheckRightsOnForm($form, 2)) {
			$service = $this->services->Document->Check($this->_user->user_id, $id, 2);
			if ($id && $service->getData()) {
				//$this->_model->attachForm($id,$ifid);
				$this->services->Document->Check($this->_user->user_id, $id, 2);
			}
			if (!$id) {
				$service->setError();
				$service->setMessage('Bản ghi không tồn tại.');
			}
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function attachDeleteAction() {
		$id = $this->params->requests->getParam('id', 0);
		$ifid = $this->params->requests->getParam('ifid', 0);
		$form = new Qss_Model_Form();
		$form->initData($ifid, $this->_user->user_dept_id);
		if ($this->b_fCheckRightsOnForm($form, 2)) {
			$service = $this->services->Document->Check($this->_user->user_id, $id, 2);
			if ($id && $service->getData()) {
				$this->_model->deleteAttach($id, $ifid);
			}
			if (!$id) {
				$service->setError();
				$service->setMessage('Bản ghi không tồn tại.');
			}
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function pictureAction() {
		$file = QSS_DATA_DIR . '/documents/' . $this->params->requests->getParam('file');
		if (!file_exists($file) || getimagesize($file) === false) {
			$file = QSS_PUBLIC_DIR . '/images/extension/download.png';
		}
		header("Content-type: application/force-download");
		header("Content-Transfer-Encoding: Binary");
		header("Content-length: " . filesize($file));
		header("Content-disposition: attachment; filename=\"" . basename($file) . "\"");
		readfile("$file");
		die();

		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
}
?>