<?php 
	/**
	* 
	*/
	class Dashboard_M316Controller extends Qss_Lib_Controller
	{
		
		public function init()
		{
			parent::init();
		}
		public function chartbypositionAction()
		{
			$model = new Qss_Model_M316_Main();
			$count = $model->countEmployeeByPosition();
			$this->html->data = $count;
			
			
		}
		public function chartbydepartmentAction()
		{
				$model = new Qss_Model_M316_Main();
			$count = $model->countEmployeeByDepartment();
			 // echo "<pre>";print_r($count);die;
			$this->html->data = $count;
		}
		public function chartbytitleAction()
		{
			$model = new Qss_Model_M316_Main();
			$count = $model->countEmployeeByTitle();
			$this->html->data = $count;
		}
	}

 ?>