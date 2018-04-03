<?php
class Static_M856Controller extends Qss_Lib_Controller
{
    public function init ()
    {
        $this->i_SecurityLevel = 15;
        parent::init();
    }

    public function saveuserAction()
    {
        $import = new Qss_Model_Import_Form('M856',false, false);
        $mTable = Qss_Model_Db::Table('ONhanVienNhanThongBao');
        $ids    = $this->params->requests->getParam('ONhanVienNhanThongBao_ID', array());
        $i      = 0;
        $ifid   = $this->params->requests->getParam('ifid', 0);
        $insert = array();
        $remove = array();
        $mTable->where("IFID_M856 = {$ifid}");
        $oldDat = $mTable->fetchAll();

        if ( $this->params->requests->isAjax())
        {
        	$form = new Qss_Model_Form();
        	$form->initData($ifid, 1);
        	if($this->b_fCheckRightsOnForm($form,4) && $form->i_Status == 1)
			{
	            foreach ($oldDat as $item)
	            {
	                $remove['ONhanVienNhanThongBao'][] = $item->IOID;
	            }
	
	            if(count($remove))
	            {
	                $this->services->Form->Remove('M856', $ifid , $remove, false);
	            }
	
	            foreach($ids as $id)
	            {
	                if($id)
	                {
	                    $insert['ONhanVienNhanThongBao'][$i]['MaNV'] = (int)$id;
	                    $insert['ONhanVienNhanThongBao'][$i]['ifid'] = $ifid;
	                    $i++;
	                }
	            }
	
	            $import->setData($insert);
	            $import->generateSQL();
			}
            // echo '<pre>'; print_r($import->getErrorRows()); die;
        }
        echo Qss_Json::encode(array('error'=>0,'message'=>''));

        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function savegroupAction()
    {
        $import = new Qss_Model_Import_Form('M856',false, false);
        $mTable = Qss_Model_Db::Table('ONhanVienNhanThongBao');
        $ids    = $this->params->requests->getParam('ONhanVienNhanThongBao_ID', array());
        $i      = 0;
        $ifid   = $this->params->requests->getParam('ifid', 0);
        $insert = array();
        $remove = array();
        $mTable->where("IFID_M856 = {$ifid}");
        $oldDat = $mTable->fetchAll();

        if ( $this->params->requests->isAjax())
        {
            foreach ($oldDat as $item)
            {
                $remove['ONhanVienNhanThongBao'][] = $item->IOID;
            }

            if(count($remove))
            {
                $this->services->Form->Remove('M856', $ifid , $remove, false);
            }

            foreach($ids as $id)
            {
                if($id)
                {
                    $insert['ONhanVienNhanThongBao'][$i]['ID'] = $id;
                    $insert['ONhanVienNhanThongBao'][$i]['ifid'] = $ifid;
                    $i++;
                }
            }

            $import->setData($insert);
            $import->generateSQL();
        }

        echo Qss_Json::encode(array('error'=>0,'message'=>''));


        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}
?>