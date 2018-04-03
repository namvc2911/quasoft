<?php 
class Print_M190Controller extends  Qss_Lib_PrintController
	{
		public function init()
		{
		
	        parent::init();
	        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/print.php';
		}
		
		public function indexAction()
		{
			header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        	header("Content-disposition: attachment; filename=\"Biên bản giao nhận.xlsx\"");
        	$file = new Qss_Model_Excel(Qss_Lib_System::getTemplateFile('M190', 'BienBanGiaoNhan.xlsx'));
        	$ifid   = $this->params->requests->getParam('ifid', 0);
	        $mCommon = new Qss_Model_Extra_Extra();
	        $oGiaoNhanThietBi = $mCommon->getTableFetchOne('OGiaoNhanThietBi',array('IFID_M190'=>$ifid));
	        
	       // 	echo "<pre>";
	       // print_r($oGiaoNhanThietBi);die;
	       // 
	         $stt  = 0;
        	
        	$data = new stdClass();
        		
        	$row = 12;
	          	$data->date =	'';	
	        	// $data->SoThietBi = $oGiaoNhanThietBi->MIVNo;
	        	$data->SoThietBi2 = $oGiaoNhanThietBi->MIVNo;
	        	$data->nguoiNhan = $oGiaoNhanThietBi->NguoiNhan;
	        	$data->phongBan = $oGiaoNhanThietBi->PhongBanBoPhan;
	        	$data->soThe = $oGiaoNhanThietBi->SoTheLamViec;
	        	$data->mieuTa = $oGiaoNhanThietBi->MieuTa;
	       		$data->kho = $oGiaoNhanThietBi->Kho;
	       		
	          $file->init(array('m'=>$data));
	     		$thietbi = sprintf('SELECT *
	     		 FROM OGiaoThietBi 
	     		 INNER JOIN ODanhSachThietBi ON OGiaoThietBi.Ref_MaThietBi = ODanhSachThietBi.IOID
	     		 INNER JOIN OLoaiThietBi ON ODanhSachThietBi.Ref_LoaiThietBi = OLoaiThietBi.IOID
	     	
	     		WHERE OGiaoThietBi.IFID_M190 = %1$d
	     			 ',$ifid);
	     		$oThietBi = $this->_db->fetchAll($thietbi);
	     		// echo "<pre>";
	     		// print_r($oThietBi);die;
	     		foreach($oThietBi as $item)
	     		{
		        	$s = new stdClass();
		        	$s->stt = ++$stt;
		        	$s->thietBi = $item->TenThietBi;
		        	$s->ma = $item->MaThietBi;
		        	$s->donVi = $item->DonViTinh;
		        	$s->soLuong = 1;
		        	$s->mieuTa = '';
		        	$s->note = $item->GhiChu;

		        	
		            $file->newGridRow(array('s'=>$s), $row, 11);
            		$row++;
            		
	        	}
	    $header = $file->wsMain->getHeaderFooter()->getOddHeader();
        $header = str_replace(
		array('{m:SoThietBi2}', '{m:date}'),array($data->SoThietBi2, $data->date), $header);
		$file->wsMain->getHeaderFooter()->setOddHeader($header);
        $file->removeRow(11);
        
        $file->save();
        die();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
		}

	}

 ?>