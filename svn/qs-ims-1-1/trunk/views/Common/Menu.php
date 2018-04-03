<?php
class Qss_View_Common_Menu extends Qss_View_Abstract
{
	public function __doExecute ($o_User)
	{
		if ( $o_User instanceof Qss_Model_UserInfo )
		{
			$lastActive = Qss_Cookie::get('lastActiveModule');
			$lastActive = $lastActive?$lastActive:'M000';
			$parent = $o_User->getRootByFormCode($lastActive);
			$this->html->selected = $lastActive;
			Qss_Cookie::set('lastMenuID',@$parent->MenuID);
			$rootmenu = 0;
			if($parent)
			{
				$rootmenu = $parent->MenuID;
			}
			$document = new DOMDocument();
			$header = Qss_Session::get('memuxml_'.$rootmenu);
			
			if ( $header )
			{
				$document->loadXML($header);
			}
			else
			{
				if( $o_User->user_id)
				{
					if ( $o_User->user_id == -1 )
					{
						$document->load(QSS_DATA_BASE . '/sa_menu.xml');
						$elements = $document->getElementsByTagName('item');
						foreach ($elements as $element)
						{
							$text = $element->getAttribute('text');
							if($text && is_numeric($text))
							{
								$element->setAttribute('title',$this->html->_translate($text));
							}
						}
						$elements = $document->getElementsByTagName('block');
						foreach ($elements as $element)
						{
							$text = $element->getAttribute('text');
							if($text && is_numeric($text))
							{
								$element->setAttribute('title',$this->html->_translate($text));
							}
						}
					}
					else
					{
						$document = new DOMDocument('1.0', 'UTF-8');
						$menu = $document->CreateElement("menu");
						$document->appendChild($menu);
						$menulist = array();
						if($rootmenu)
						{
							$menulist = $o_User->a_fGetListMenu($rootmenu);
						}
						$menuitem = $o_User->a_fGetListByMenu($rootmenu);
						foreach ($menuitem as $o_Form)
						{
							if(!$o_Form->class && $o_Form->classMobile)
							{
								continue;
							}
							$newsubitem = $document->CreateElement("item");
							$newsubitem->setAttribute("title",  $o_Form->Name);
							if($o_Form->class && $o_Form->Type != Qss_Lib_Const::FORM_TYPE_PROCESS)
							{
								$newsubitem->setAttribute("href", Qss_Params::getInstance()->requests->getBaseUrl() .$o_Form->class);
								$newsubitem->setAttribute("code", $o_Form->FormCode);
							}
							else
							{
								$newsubitem->setAttribute("href", Qss_Params::getInstance()->requests->getBaseUrl() . "/user/form?fid=" . $o_Form->FormCode);
								$newsubitem->setAttribute("code", $o_Form->FormCode);
							}
							$menu->appendChild($newsubitem);
						}
						foreach ($menulist as $item)
						{
							if($o_User->hasChild($item->MenuID))
							{
								$submenu = $o_User->a_fGetListMenu($item->MenuID);
								$newblock = $document->CreateElement("block");
								$newblock->setAttribute("title", $item->MenuName);
								
								$menuitem = $o_User->a_fGetListByMenu($item->MenuID);
								foreach ($menuitem as $o_Form)
								{
									$newsubitem = $document->CreateElement("item");
									$newsubitem->setAttribute("title",$o_Form->Name);
									if($o_Form->class && $o_Form->Type != Qss_Lib_Const::FORM_TYPE_PROCESS)
									{
										$newsubitem->setAttribute("href", Qss_Params::getInstance()->requests->getBaseUrl() .$o_Form->class);
										$newsubitem->setAttribute("code", $o_Form->FormCode);
									}
									else
									{
										$newsubitem->setAttribute("href", Qss_Params::getInstance()->requests->getBaseUrl() . "/user/form?fid=" . $o_Form->FormCode);
										$newsubitem->setAttribute("code", $o_Form->FormCode);
									}
									$newblock->appendChild($newsubitem);
								}
								foreach ($submenu as $o_List)
								{
									if($o_User->hasChild($o_List->MenuID))
									{
										$this->addMenu($o_List,$o_User,$document,$newblock);
									}
								}
								//$this->addMenu($item,$o_User,$document,$newblock);
								$document->documentElement->appendChild($newblock);
							}
						}
					}
					Qss_Session::set('memuxml_'.$rootmenu, $document->saveXML());
				}
			}
			$stylesheet = new DOMDocument();

			@$stylesheet->load(QSS_DATA_BASE . '/menu.xsl');
			$processor = new XSLTProcessor(); // <- T_VARIABLE error
			@$processor->importStylesheet($stylesheet);
			$ret = @$processor->transformToXML($document);
			$ret = str_ireplace('{baseURL}', Qss_Params::getInstance()->requests->getBaseUrl(), $ret);
			$this->html->banner = $ret;
			$o_Form = new Qss_Model_Form();
		}
	}
	private function addMenu($item,$o_User,&$document,&$pnewblock)
	{
		$submenu = $o_User->a_fGetListMenu($item->MenuID);
		$newblock = $document->CreateElement("item");
		$newblock->setAttribute("title", $item->MenuName);
		foreach ($submenu as $o_List)
		{
			if($o_User->hasChild($o_List->MenuID))
			{
				//$newitem = $document->CreateElement("item");
				//$newitem->setAttribute("title", $o_List->MenuName);
				/*$menuitem1 = $o_User->a_fGetListByMenu($o_List->MenuID);
				foreach ($menuitem1 as $o_Form)
				{
					$newsubitem = $document->CreateElement("item");
					$newsubitem->setAttribute("title", $o_Form->Name);
					if($o_Form->class)
					{
						$newsubitem->setAttribute("href", Qss_Params::getInstance()->requests->getBaseUrl() .$o_Form->class);
					}
					else
					{
						$newsubitem->setAttribute("href", Qss_Params::getInstance()->requests->getBaseUrl() . "/user/form?fid=" . $o_Form->FID);
					}
					$newitem->appendChild($newsubitem);
				}*/
				//$newblock->appendChild($newitem);
				$this->addMenu($o_List,$o_User,$document,$newblock);
			}
		}
		$menuitem = $o_User->a_fGetListByMenu($item->MenuID);
		foreach ($menuitem as $o_Form)
		{
			$newsubitem = $document->CreateElement("item");
			$newsubitem->setAttribute("title", $o_Form->FormCode . ' - ' . $o_Form->Name);
			if($o_Form->class && $o_Form->Type != Qss_Lib_Const::FORM_TYPE_PROCESS)
			{
				$newsubitem->setAttribute("href", Qss_Params::getInstance()->requests->getBaseUrl() .$o_Form->class);
				$newsubitem->setAttribute("code", $o_Form->FormCode);
				$newsubitem->setAttribute("fid", $o_Form->FormCode);
			}
			else
			{
				$newsubitem->setAttribute("href", Qss_Params::getInstance()->requests->getBaseUrl() . "/user/form?fid=" . $o_Form->FormCode);
				$newsubitem->setAttribute("code", $o_Form->FormCode);
				$newsubitem->setAttribute("fid", $o_Form->FormCode);
			}
			$newblock->appendChild($newsubitem);
		}
		$pnewblock->appendChild($newblock);
	//	$document->documentElement->appendChild($newblock);
	}
}
?>