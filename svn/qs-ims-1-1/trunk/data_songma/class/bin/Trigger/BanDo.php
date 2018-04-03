<?php
/**
 * @author: Thinh Tuan <thinh.tuan@quasoft.vn>
 * @website: quasoft.vn
 */
 
class Qss_Bin_Trigger_BanDo extends Qss_Lib_Trigger
{

        public function onInserted($object)
        {
                parent::init();
                $this->updateBaseMap($object);
        }

        public function onUpdate($object)
        {
                parent::init();
                $this->updateBaseMap($object);
        }
        
        public function updateBaseMap($object)
        {
                $model     = new Qss_Model_Extra_Map();
                $protocol  = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';
                $domain   = $_SERVER['HTTP_HOST'];
                $base       = $protocol.$domain.'/data_songma/documents/';
                $root       =  QSS_ROOT_DIR . '\data_songma\documents\\';
                $maplayer = $model->getMapLayers($this->_params->Ref_LopBanDo);
                $kmlstyle       = isset($maplayer[0]->Style)?$maplayer[0]->Style:'';
                $kmlfile    = ($this->_params->BanDo)?$root.$this->_params->BanDo:'';
                // neu co style va ton tai file kml
                if($kmlstyle && $kmlfile && file_exists($kmlfile))
                {
                        // them style moi vao
                        if($this->_params->BanDo)
                        {
                                $fType = strtolower($this->_params->BanDo); 
                                $fType = trim($this->_params->BanDo);
                                
                                if(($fType == 'kml')) // chi ho tro file kml
                                {
                                        $doc = new DOMDocument();
                                        $docSub = new DOMDocument();
                                        
                                        
                                        $doc->load( $kmlfile );
                                        $docSub->loadXML($kmlstyle);
                                       // print_r($docSub->saveXML());
                                        
                                        $bar = $docSub->documentElement;
                                        
                                        $style  = $doc->getElementsByTagName( "Style");
                                        if($style->length)
                                        {
                                                for($i = 0; $i < $style->length; $i++)
                                                {
                                                        // Neu co the Style
                                                        $style->item($i)->setAttribute("id","quasoft");
                                                        
                                                        while ($style->item($i)->hasChildNodes()) 
                                                        {
                                                                $style->item($i)->removeChild($style->item($i)->firstChild);
                                                        }
                                                        
                                                        $style->item($i)->appendChild($doc->importNode($bar, TRUE));
                                                }
                                        }
                                        else 
                                        {
                                                // Neu khong co the Style
                                                $placemark = $doc->getElementsByTagName("Placemark"); //->item(0)->appendChild($style);
                                                
                                                for($j = 0; $j < $placemark->length; $j++)
                                                {
                                                        $style = $doc->createElement("Style");
                                                        $style->setAttribute("id","quasoft");
                                                        $style->appendChild($doc->importNode($bar, TRUE));
                                                        $placemark->item($j)->appendChild($style);
                                                        
                                                }
                                                
                                        }
                                        $doc->save($kmlfile);
                                }
                        }
                }
        } 
}
