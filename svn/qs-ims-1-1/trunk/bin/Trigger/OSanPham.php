<?php

class Qss_Bin_Trigger_OSanPham extends Qss_Lib_Trigger
{

	public $common;

	public function __construct($form)
	{
		parent::__construct($form);
		$this->common = new Qss_Model_Extra_Extra();

	}

	/**
	 * onInserted:
	 * - Insert base uom to uom list of item
	 */
	public function onInserted($object)
	{
		parent::init();
		$uom = $this->_params->DonViTinh;
		$module = 'M113';
		$ifid = $this->_params->IFID_M113;
		$data = array('ODonViTinhSP' => array(array('DonViTinh' => $uom
					, 'HeSoQuyDoi' => 1
					, 'MacDinh' => 1
					, 'SuDungTTSL' => 0)));
		// *****************************************************************
		// === Insert base uom to uom list of item
		// *****************************************************************
		$service = $this->services->Form->Manual($module, $ifid, $data, false);

		if ($service->isError())
		{
			$this->setError();
			$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
		}

	}

	/**
	 * onUpdate:
	 * - 
	 */
	public function onUpdate($object)
	{
		parent::init();
		$module = 'M113';
		$ifid = $this->_params->IFID_M113;
		$newUOM = @(int) $object->getFieldByCode('DonViTinh')->intRefIOID;
		$newUOMSz = @(string) $object->getFieldByCode('DonViTinh')->szValue;
		$oldUOM = @(int) $this->_params->Ref_DonViTinh;
		$inventory = new Qss_Model_Inventory_Inventory();
		$existsItemTransaction = $inventory->checkTransactionExists($this->_params->IOID);
        $countUOM = count($this->_params->ODonViTinhSP);
        

		// *****************************************************************
		// === - Neu da ton tai giao dich kho cua san pham thi khong cho thay 
		// === doi don vi tinh cua san pham
		// === - Nguoc lai neu chua ton tai giao dich kho cua san pham, ta co 
		// === the thay doi don vi tinh mac dinh. Thay doi don vi tinh mac dinh
		// === cu ve don vi tinh chuyen doi neu co. Them don vi tinh mac dinh
		// === theo don vi tinh moi neu chua ton tai don vi tinh nay, nguoc
		// === lai update don vi tinh nay thanh don vi tinh co so (mac dinh)
		// *****************************************************************

		if (0 && isset($existsItemTransaction->transaction) && $existsItemTransaction->transaction && ($oldUOM != $newUOM))
		{
			if ($newUOM && ($newUOM != $oldUOM))
			{
				$this->setMessage($this->_translate(1));
				$this->setError();
			}
		} else
		{
			if ( ($newUOM && ($newUOM != $oldUOM)) || $countUOM == 0 )
			{
				// *****************************************************************
				// === base uom ban dau
				// *****************************************************************
				$oldBaseUOM = $this->common->getTable(array('*'), 'ODonViTinhSP'
					, array('MacDinh' => 1, 'IFID_M113' => $ifid)
					, array(), 'NO_LIMIT',  1);



				// *****************************************************************
				// === Kiem tra xem new uom da co trong uom list chua
				// *****************************************************************
				$newUOMExists = $this->common->getTable(array('*'), 'ODonViTinhSP'
					, array('IFID_M113' => $ifid, 'Ref_DonViTinh' => $newUOM)
					, array(), 'NO_LIMIT', 1);


				// *****************************************************************
				// === Du lieu cap nhat moi
				// *****************************************************************
				$dataInsert = array('ODonViTinhSP' => array(array('DonViTinh' => $newUOMSz
							, 'HeSoQuyDoi' => 1
							, 'MacDinh' => 1
							, 'SuDungTTSL' => 0)));


				// *****************************************************************
				// === Du lieu cap nhat
				// *****************************************************************
				$dataUpdate = array('ODonViTinhSP' => array(array('MacDinh' => 0)));
				$dataUpdate1 = array('ODonViTinhSP' => array(array('MacDinh' => 1, 'HeSoQuyDoi' => 1)));



				// *****************************************************************
				// === Update old base uom to normal
				// *****************************************************************
				if ($oldBaseUOM)
				{
					$dataUpdate['ODonViTinhSP'][0]['ioid'] = $oldBaseUOM->IOID;
					$service1 = $this->services->Form->Manual($module, $ifid, $dataUpdate, false);

					if ($service1->isError())
					{
						$this->setError();
						$this->setMessage($service1->getMessage(Qss_Service_Abstract::TYPE_TEXT));
					}
				}

				// *****************************************************************
				// === Insert or update base uom
				// *****************************************************************
				if ($newUOMExists)
				{
					$dataUpdate1['ODonViTinhSP'][0]['ioid'] = $newUOMExists->IOID;

					$service2 = $this->services->Form->Manual($module, $ifid, $dataUpdate1, false);
					if ($service2->isError())
					{
						$this->setError();
						$this->setMessage($service2->getMessage(Qss_Service_Abstract::TYPE_TEXT));
					}
				} else
				{
					$service3 = $this->services->Form->Manual($module, $ifid, $dataInsert, false);
					if ($service3->isError())
					{
						$this->setError();
						$this->setMessage($service3->getMessage(Qss_Service_Abstract::TYPE_TEXT));
					}
				}
			}
		}

	}

    /**
     * OnUpdated: Cập nhật lại tên mặt hàng (Với trường hợp sử dụng mã tạm)
     * - Cập nhật lại tên sản phẩm trong kế hoạch bảo trì M724
     * - Cập nhật lại tên sản phẩm trong kế hoạch tổng thể M838
     * - Cập nhật lại tên sản phẩm trong phiếu bảo trì M759
     */
	public function onUpdated(Qss_Model_Object $object) {
        parent::init();
        // Chỉ cập nhật lại tên mặt hàng khi sủ dụng mã tạm
        if(Qss_Lib_System::fieldActive('OSanPham', 'MaTam')) {
            $mCommon     = new Qss_Model_Extra_Extra();
            $ioidSanPham = $object->i_IOID; // #note: cái này có bằng IOID của sản phẩm

            // Cập nhật lại tên sản phẩm trong kế hoạch bảo trì M724
            if(Qss_Lib_System::objectInForm('M724', 'OVatTu')) {
                $fieldTenSanPhamM724 = $mCommon->getTableFetchOne('qsfields', array('ObjectCode'=>'OVatTu', 'FieldCode'=>'TenVatTu'));

                // Chỉ cập nhật lại khi có field TenSanPham và field này dạng TextBox
                if($fieldTenSanPhamM724 && $fieldTenSanPhamM724->InputType = 1) {
                    $sql = sprintf('
                        UPDATE OVatTu
                        INNER JOIN OSanPham ON OVatTu.Ref_MaVatTu = OSanPham.IOID
                        SET OVatTu.TenVatTu = OSanPham.TenSanPham
                        WHERE IFNULL(OVatTu.Ref_MaVatTu, 0) = %1$d AND IFNULL(OSanPham.MaTam, 0) = 0 
                    ', $ioidSanPham);

                    $this->_db->execute($sql);
                }
            }

            // Cập nhật lại tên sản phẩm trong kế hoạch tổng thể M838
            if(Qss_Lib_System::objectInForm('M837', 'OVatTuKeHoach')) {
                $fieldTenSanPhamM838 = $mCommon->getTableFetchOne('qsfields', array('ObjectCode'=>'OVatTuKeHoach', 'FieldCode'=>'TenVatTu'));

                // Chỉ cập nhật lại khi có field TenSanPham và field này dạng TextBox
                if($fieldTenSanPhamM838 && $fieldTenSanPhamM838->InputType = 1) {
                    $sql = sprintf('
                        UPDATE OVatTuKeHoach
                        INNER JOIN OSanPham ON OVatTuKeHoach.Ref_MaVatTu = OSanPham.IOID
                        SET OVatTuKeHoach.TenVatTu = OSanPham.TenSanPham
                        WHERE IFNULL(OVatTuKeHoach.Ref_MaVatTu, 0) = %1$d AND IFNULL(OSanPham.MaTam, 0) = 0 
                    ', $ioidSanPham);

                    $this->_db->execute($sql);
                }
            }

            // Cập nhật lại tên sản phẩm trong phiếu bảo trì M759

            // M759 A: Vật tư thực thế
            if(Qss_Lib_System::objectInForm('M759', 'OVatTuPBT')) {
                $fieldTenSanPhamM759 = $mCommon->getTableFetchOne('qsfields', array('ObjectCode'=>'OVatTuPBT', 'FieldCode'=>'TenVatTu'));

                // Chỉ cập nhật lại khi có field TenSanPham và field này dạng TextBox
                if($fieldTenSanPhamM759 && $fieldTenSanPhamM759->InputType = 1) {
                    $sql = sprintf('
                        UPDATE OVatTuPBT
                        INNER JOIN OSanPham ON OVatTuPBT.Ref_MaVatTu = OSanPham.IOID
                        SET OVatTuPBT.TenVatTu = OSanPham.TenSanPham
                        WHERE IFNULL(OVatTuPBT.Ref_MaVatTu, 0) = %1$d AND IFNULL(OSanPham.MaTam, 0) = 0 
                    ', $ioidSanPham);

                    $this->_db->execute($sql);
                }
            }

            // M759 B: Vật tư dự kiến
            if(Qss_Lib_System::objectInForm('M759', 'OVatTuPBTDK')) {
                $fieldTenSanPhamDuKienM759 = $mCommon->getTableFetchOne('qsfields', array('ObjectCode'=>'OVatTuPBTDK', 'FieldCode'=>'TenVatTu'));

                // Chỉ cập nhật lại khi có field TenSanPham và field này dạng TextBox
                if($fieldTenSanPhamDuKienM759 && $fieldTenSanPhamDuKienM759->InputType = 1) {
                    $sql = sprintf('
                        UPDATE OVatTuPBTDK
                        INNER JOIN OSanPham ON OVatTuPBTDK.Ref_MaVatTu = OSanPham.IOID
                        SET OVatTuPBTDK.TenVatTu = OSanPham.TenSanPham
                        WHERE IFNULL(OVatTuPBTDK.Ref_MaVatTu, 0) = %1$d AND IFNULL(OSanPham.MaTam, 0) = 0 
                    ', $ioidSanPham);

                    $this->_db->execute($sql);
                }
            }
        }
    }

}
