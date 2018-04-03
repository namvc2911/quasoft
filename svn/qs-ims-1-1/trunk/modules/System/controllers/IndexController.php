<?php
/**
b
 */
class System_IndexController extends Qss_Lib_Controller {
	public function init() {
		$this->i_SecurityLevel = 1;
		parent::init();
		$this->headScript($this->params->requests->getBasePath() . '/js/system-list.js');
	}
	public function indexAction() {
		$form = new Qss_Model_Admin_Group();
		$this->html->forms = $form->getRightEle();
	}
	public function deleteTemplateAction() {
		$fid = $this->params->requests->getParam('fid');
		$sz_FileName = QSS_DATA_DIR . '/views/detail/*.phtml';
		@array_map('unlink', glob($sz_FileName));
		$sz_FileName = QSS_DATA_DIR . '/views/edit/*.phtml';
		@array_map('unlink', glob($sz_FileName));
		$sz_FileName = QSS_DATA_DIR . '/views/search/*.phtml';
		@array_map('unlink', glob($sz_FileName));

		$sz_FileName = QSS_DATA_DIR . '/views/edit/object/*.phtml';
		@array_map('unlink', glob($sz_FileName));
		$sz_FileName = QSS_DATA_DIR . '/views/search/import/*.phtml';
		@array_map('unlink', glob($sz_FileName));
		$sz_FileName = QSS_DATA_DIR . '/views/search/object/*.phtml';
		@array_map('unlink', glob($sz_FileName));

		echo Qss_Json::encode(array('error' => 0));
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function deleteFormTemplateAction() {
		$fid = $this->params->requests->getParam('fid');
		$sz_FileName = QSS_DATA_DIR . '/views/detail/' . $fid . '.phtml';
		@unlink($sz_FileName);
		$sz_FileName = QSS_DATA_DIR . '/views/edit/' . $fid . '.phtml';
		@unlink($sz_FileName);
		$sz_FileName = QSS_DATA_DIR . '/views/search/' . $fid . '.phtml';
		@unlink($sz_FileName);
		echo Qss_Json::encode(array('error' => 0));
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function deleteObjectTemplateAction() {
		$objid = $this->params->requests->getParam('objid');
		$mTable = Qss_Model_Db::Table('qsfobjects');
		$mTable->where(sprintf('ObjectCode = "%1$s"', $objid));
		$oForms = $mTable->fetchAll();

		// Xoa form
		foreach ($oForms as $f) {
			$sz_FileName = QSS_DATA_DIR . '/views/edit/' . $f->FormCode . '.phtml';
			@unlink($sz_FileName);
			$sz_FileName = QSS_DATA_DIR . '/views/search/' . $f->FormCode . '.phtml';
			@unlink($sz_FileName);
		}

		$sz_FileName = QSS_DATA_DIR . '/views/edit/object/' . $objid . '.phtml';
		@unlink($sz_FileName);
		$sz_FileName = QSS_DATA_DIR . '/views/search/import' . $objid . '.phtml';
		@unlink($sz_FileName);
		$sz_FileName = QSS_DATA_DIR . '/views/search/object' . $objid . '.phtml';
		@unlink($sz_FileName);
		echo Qss_Json::encode(array('error' => 0));
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function crvAction() {
		$objid = $this->params->requests->getParam('objid');
		$object = new Qss_Model_System_Object();
		if ($objid && $object->v_fInit($objid)) {
			if ($object->createView()) {
				echo 'You have create view!';
			}
		} elseif ($objid == -1) {
			$objects = $object->a_fGetAll();
			foreach ($objects as $data) {
				$object->v_fInit($data->ObjectCode);
				if ($object->createView()) {
					echo 'You have create view!';
				}
			}
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

}