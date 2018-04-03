<?php 
	class Static_M356Controller extends Qss_Lib_Controller 
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
			$model = new Qss_Model_M317_Main();
			$kycong = $this->params->requests->getParam('marvel',0);
			$nhanvien = $this->params->requests->getParam('emloyee',0);
			$phongban = $this->params->requests->getParam('department',0);
			
			$this->html->bangKyCong = $model->getBangKyCong($kycong,$nhanvien,$phongban);
			

			$this->html->loaiNghi = $model->getLoaiNgayNghi();

			$this->html->loaiTangCa	= $model->getLoaiTangCa();
			$tangca[] = array();
			foreach($model->getLoaiTangCa() as $loaiTangCa){
				foreach ($model->getBangKyCong() as $bangKyCong) {
					$tangca[$bangKyCong->Ref_MaNhanVien][$loaiTangCa->Ref_LoaiLamThem] = $loaiTangCa->TongGioLamThem;
				}
				
			}

			// echo "<pre>";
			// print_r($this->html->loaiTangCa);die;


		

		}

	}


 ?>