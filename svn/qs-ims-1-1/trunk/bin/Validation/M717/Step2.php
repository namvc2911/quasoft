<?php
class Qss_Bin_Validation_M717_Step2 extends Qss_Lib_WValidation
{

    /**
     * @Note: Tạm thời chỉ tính cho lắp đặt
     */
	public function onAlert()
	{
		parent::init();
		$common = new Qss_Model_Extra_Extra();
		$msg = '';
		
		// So sanh so luong trong thong ke voi so luong thuc hien trong phieu giao viec
		// TH1: Khong co phieu giao viec, bao loi khong co phieu giao viec
		// TH2: Thong ke co so luong khac voi phieu giao viec
		
		// Lay phieu giao viec tuong ung voi thong ke
//		$filter = array();
//		$filter['module'] = 'OPhieuGiaoViec';
//		$filter['where']['MaLenhSX'] = $this->_params->MaLenhSX;
//		$filter['where']['Ref_CongDoan'] = $this->_params->Ref_CongDoan;
//		$filter['where']['Ref_DonViThucHien'] = $this->_params->Ref_DonViThucHien;
//		$filter['return'] = 1;
        $mGiaoViec = Qss_Model_Db::Table('OPhieuGiaoViec');
        $mGiaoViec->where($mGiaoViec->ifnullString('MaLenhSX', $this->_params->MaLenhSX));
        $mGiaoViec->where($mGiaoViec->ifnullNumber('Ref_CongDoan', $this->_params->Ref_CongDoan));
        $mGiaoViec->where($mGiaoViec->ifnullNumber('Ref_DonViThucHien', $this->_params->Ref_DonViThucHien));

//		$PhieuGiaoViec = $common->getDataset($filter);
        $PhieuGiaoViec = $mGiaoViec->fetchOne();
		$SoLuongPhieuGiaoViec = $PhieuGiaoViec?$PhieuGiaoViec->SoLuong:0;
		
		// Lay so luong trong thong ke
        $MaLenhSX = @(string)$this->_db->MaLenhSX;
        $mSanXuat = Qss_Model_Db::Table('OSanXuat');
        $mSanXuat->where($mSanXuat->ifnullString('MaLenhSX', $MaLenhSX));
//        $SanXuat  = $common->getDataset(array('module'=>'OSanXuat'
//            , 'where'=>array('MaLenhSX'=>$MaLenhSX), 'return'=>1));
        $SanXuat  = $mSanXuat->fetchOne();
		$ThaoDo   = $SanXuat?@(int)$SanXuat->ThaoDo:2; // tra ve 2 ko ton tai, 1 thao do, 0 lap rap
		$SoLuongThongKe = 0;
					
					
		if($ThaoDo == 1)
		{
			// Neu thao do, set so luong ve disabled
			// Cong don so luong phu pham, hoac set ve 0 neu khong co phu pham
//			$PhuPham = $common->getDataset(array('module'=>'OSanLuong'
//						, 'where'=>array('IFID_M717'=>$this->_params->IFID_M717)));
//			foreach($PhuPham as $p)
//			{
//				$SoLuongThongKe += $p->SoLuong;
//			}
		}
		else 
		{
			$SoLuongThongKe = $this->_params->SoLuong;
		}
                
                if($ThaoDo == 0)
                {
                    // Khong co phieu giao viec
                    if($PhieuGiaoViec)
                    {
                            // So luong thong ke khac giao viec
                            if($SoLuongThongKe < $SoLuongPhieuGiaoViec)
                            {
                                    $this->setMessage('Số lượng hoàn thành trong thống kê nhỏ hơn số lượng thực hiện trong phiếu giao việc!');
                            }
                            elseif($SoLuongThongKe > $SoLuongPhieuGiaoViec)
                            {
                                    $this->setMessage('Số lượng hoàn thành trong thống kê lớn hơn số lượng thực hiện trong phiếu giao việc!');
                            }
                    }
                    else 
                    {
                            $this->setMessage('Không tồn tại phiếu giao việc cho thống kê này!');
                    }
                }
		
		// Set error
		if($this->getMessage())
		{
			$this->setError();
		}
	}
	
	public function onNext()
	{
		parent::init();
		$model = new Qss_Model_Extra_Warehouse();
		// @todo: attribute required?
		// @todo: compare stock status with line (sl,pl,nvl,dc)
		// @todo: Check capacity of input, check available of output
	}
	
	// @todo: Cap nhat vao kho khi chuyen buoc
	/**
	 * Insert to warehouse and transaction
	 */
	public function next()
	{
		
		// 
		//@todo: Tạo phiếu nhập kho
		/*
		parent::init();
		$this->updateNVL();	
		if(!$this->isError())
		{
			$this->updateSL();	
			$this->updatePL();
		}
		*/	
	}

}