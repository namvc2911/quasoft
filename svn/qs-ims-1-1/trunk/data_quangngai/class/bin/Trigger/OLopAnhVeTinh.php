<?php
/**
 * @author: Thinh Tuan <thinh.tuan@quasoft.vn>
 * @website: quasoft.vn
 */
 
class Qss_Bin_Trigger_OLopAnhVeTinh extends Qss_Lib_Trigger
{

        public function onInserted($object)
        {
                parent::init();
                $this->updateBaseMap($object);
        }

        public function onUpdated($object)
        {
        	  
                parent::init();
                $this->updateBaseMap($object);
        }
        
        public function updateBaseMap($object)
        {
        	    $model     = new Qss_Model_Extra_Map();
                $protocol  = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';
                $domain   = $_SERVER['HTTP_HOST'];
                $base       = $protocol.$domain.'/data_quangngai/documents/';
                $root       =  QSS_ROOT_DIR . '/data_quangngai/documents/';
                $filebando = $object->getFieldByCode('FileBanDo')->getValue(); 
                $fileanh = $object->getFieldByCode('Anh')->getValue();
                $kmlfile    = ($filebando != '')?($root.$filebando):'';
                $anhfile    = ($fileanh != '')?($root.$fileanh):'';
                $anhthay    = '/data_quangngai/documents/'.$fileanh;
                // neu co style va ton tai file kml
            	
                if($kmlfile && $anhfile && file_exists($kmlfile))
                {
                	    // them style moi vao
                        if($filebando)
                        {
                                $fType = strtolower($filebando); 
                                $fType = pathinfo($fType, PATHINFO_EXTENSION);
                                        
                                if(($fType == 'kml')) // chi ho tro file kml
                                {
                                        $doc = new DOMDocument();
                                        $doc->load( $kmlfile );
                                        $doc->getElementsByTagName("href")->item(0)->nodeValue = $anhthay;
                                        $doc->save($kmlfile);
                                }
                        }
                }
        } 
}
