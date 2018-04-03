<?php 
	class Static_M088Controller extends Qss_Lib_Controller 
	{
		public function init()
		{
			$this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
			parent::init();
	
		}
		public function indexAction()
		{
			
		}
		public function showAction()
		{
			$model = new Qss_Model_M316_Main();

			$department = $this->params->requests->getParam('department',0);
			$moiVaoLam = $this->params->requests->getParam('moiVaoLam',0);
			$nghiViec = $this->params->requests->getParam('nghiViec',0);
			$dangLamViec = $this->params->requests->getParam('dangLamViec',0);
			
			$this->html->data = $model->getEmployeeByStatus($department,$moiVaoLam,$nghiViec,$dangLamViec);

		}

	}


 ?>