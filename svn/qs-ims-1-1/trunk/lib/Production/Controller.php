<?php
/**
 * @author: ThinhTuan
 * @component: Production
 */
class Qss_Lib_Production_Controller extends Qss_Lib_Controller
{
	public $_params;
	public $_common;
	public $_production;
        public $_mrp;
	public $_warehouseCtl;
	
	
	public function init ()
	{
		//$this->i_SecurityLevel = 15;
		parent::init();
		$this->_params = $this->params->requests->getParams();
		$this->_common = new Qss_Model_Extra_Extra;
		$this->_production = new Qss_Model_Extra_Production();
                $this->_mrp = new Qss_Model_Extra_Mrp();
		$this->_warehouseCtl = new Qss_Lib_Warehouse_Controller();
	}
	
	// @Function: getBomConfig(), lay cau hinh bom
	// @Parameter: 
	// 		- $bomIDArr: mang chi so IOID cua BOM.
	// @Return: Chi tiet mot hoac nhieu "Thiet ke san pham".
	public function getBOMConfig($bomIDArr)
	{
		$bomConfig = array();
		$oldLine  = '';
		
		$material  = $this->_production->getBOMByIdArr($bomIDArr,1);
		$output    = $this->_production->getBOMByIdArr($bomIDArr,2);
		$operation = $this->_production->getBOMByIdArr($bomIDArr,3);
		$sparepart = $this->_production->getBOMByIdArr($bomIDArr,4);
		
		foreach ($operation as $item) 
		{
			// Xac dinh level cua cong doan
			if($oldLine != $item->MainIOID)
			{
				$lv = 0;
			}
			$oldLine = $item->MainIOID;
			$lv++;
			
			$bomConfig[$item->MainIOID]['MainKey'] = $item->RefMainItem.'_'.$item->RefMainAttribute;
			$bomConfig[$item->MainIOID]['MainItemCode'] = $item->MainItemCode;
			$bomConfig[$item->MainIOID]['MainItemName'] = $item->MainItemName;
			$bomConfig[$item->MainIOID]['MainAttribute'] = $item->MainAttribute;
			$bomConfig[$item->MainIOID]['MainUOM'] = $item->MainUOM;
			$bomConfig[$item->MainIOID]['MainQty'] = $item->MainQty;
			$bomConfig[$item->MainIOID]['MinQty'] = $item->MinQty;
			$bomConfig[$item->MainIOID]['Assembly'] = $item->Assembly;
			$bomConfig[$item->MainIOID]['Level'][$lv] = $item->RefOperation;
			
			$bomConfig[$item->MainIOID]['Detail'][$item->RefOperation]['ID'] = $item->RefOperation;
			$bomConfig[$item->MainIOID]['Detail'][$item->RefOperation]['Name'] = $item->Operation;
			$bomConfig[$item->MainIOID]['Detail'][$item->RefOperation]['Time'] = $item->OperationTime;
			$bomConfig[$item->MainIOID]['Detail'][$item->RefOperation]['Outsource'] = $item->Outsource;
			$bomConfig[$item->MainIOID]['Detail'][$item->RefOperation]['Cost'] = $item->Cost;
			$bomConfig[$item->MainIOID]['Detail'][$item->RefOperation]['Input'] = array();
			$bomConfig[$item->MainIOID]['Detail'][$item->RefOperation]['Output'] = array();
			$bomConfig[$item->MainIOID]['Detail'][$item->RefOperation]['Sparepart'] = array();
		}
				
		$i = 0;
		foreach ($material as $item) 
		{
			$bomConfig[$item->MainIOID]['Detail'][$item->RefOperation]['Input'][$i]['Key'] = $item->RefItem.'_'.$item->RefAttribute;
			$bomConfig[$item->MainIOID]['Detail'][$item->RefOperation]['Input'][$i]['RefItem'] = $item->RefItem;
			$bomConfig[$item->MainIOID]['Detail'][$item->RefOperation]['Input'][$i]['ItemCode'] = $item->ItemCode;
			$bomConfig[$item->MainIOID]['Detail'][$item->RefOperation]['Input'][$i]['ItemName'] = $item->ItemName;
			$bomConfig[$item->MainIOID]['Detail'][$item->RefOperation]['Input'][$i]['RefAttribute'] = $item->RefAttribute;
			$bomConfig[$item->MainIOID]['Detail'][$item->RefOperation]['Input'][$i]['Attribute'] = $item->Attribute;
			$bomConfig[$item->MainIOID]['Detail'][$item->RefOperation]['Input'][$i]['ItemUOM'] = $item->ItemUOM;
			$bomConfig[$item->MainIOID]['Detail'][$item->RefOperation]['Input'][$i]['ItemQty'] = $item->ItemQty;
			$i++;
		}
		
		$j = 0;
		foreach ($output as $item)
		{
			$bomConfig[$item->MainIOID]['Detail'][$item->RefOperation]['Output'][$j]['Key'] = $item->RefItem.'_'.$item->RefAttribute;
			$bomConfig[$item->MainIOID]['Detail'][$item->RefOperation]['Output'][$j]['RefItem'] = $item->RefItem;
			$bomConfig[$item->MainIOID]['Detail'][$item->RefOperation]['Output'][$j]['ItemCode'] = $item->ItemCode;
			$bomConfig[$item->MainIOID]['Detail'][$item->RefOperation]['Output'][$j]['ItemName'] = $item->ItemName;
			$bomConfig[$item->MainIOID]['Detail'][$item->RefOperation]['Output'][$j]['RefAttribute'] = $item->RefAttribute;
			$bomConfig[$item->MainIOID]['Detail'][$item->RefOperation]['Output'][$j]['Attribute'] = $item->Attribute;
			$bomConfig[$item->MainIOID]['Detail'][$item->RefOperation]['Output'][$j]['ItemUOM'] = $item->ItemUOM;
			$bomConfig[$item->MainIOID]['Detail'][$item->RefOperation]['Output'][$j]['ItemQty'] = $item->ItemQty;
			$bomConfig[$item->MainIOID]['Detail'][$item->RefOperation]['Output'][$j]['NextQty'] = $item->NextQty;
			$j++;
		}
		
		$k = 0;
		foreach ($sparepart as $item)
		{
			$bomConfig[$item->MainIOID]['Detail'][$item->RefOperation]['Sparepart'][$k]['Key'] = $item->RefItem.'_'.$item->RefAttribute;
			$bomConfig[$item->MainIOID]['Detail'][$item->RefOperation]['Sparepart'][$k]['RefItem'] = $item->RefItem;
			$bomConfig[$item->MainIOID]['Detail'][$item->RefOperation]['Sparepart'][$k]['ItemCode'] = $item->ItemCode;
			$bomConfig[$item->MainIOID]['Detail'][$item->RefOperation]['Sparepart'][$k]['ItemName'] = $item->ItemName;
			$bomConfig[$item->MainIOID]['Detail'][$item->RefOperation]['Sparepart'][$k]['RefAttribute'] = $item->RefAttribute;
			$bomConfig[$item->MainIOID]['Detail'][$item->RefOperation]['Sparepart'][$k]['Attribute'] = $item->Attribute;
			$bomConfig[$item->MainIOID]['Detail'][$item->RefOperation]['Sparepart'][$k]['ItemUOM'] = $item->ItemUOM;
			$bomConfig[$item->MainIOID]['Detail'][$item->RefOperation]['Sparepart'][$k]['ItemQty'] = $item->ItemQty;
			$k++;
		}
		return $bomConfig;
	}
	// End getBOMConfig()
	
	// @Function: getOutputByBOM(), lay san pham dau ra theo bom (Phu pham)
	// @Parameter: 
	//		- $bomConfig: Cau hinh "Thiet ke san pham" duoc lay tu ham getBOMConfig()
	// @Return: Tra ve cac san pham dau ra yeu cau
	// @todo: Gop ba ham getOutputByBOM(), getSparepartByBOM(), getMaterialByBOM() thanh mot ham co gia tri
	// tra ve la mot mang gom ba mang con ouput=>array, sparepart=>array, material=>array
	// @todo: Ham getQtyReality() se tra ve so luong thuc te cua thanh phan, san pham dau ra, cong cu dung cu
	// thay vi tung cai mot nhu bay gio
	public function getOutputByBOM($bomConfig)
	{
		$retval = array();
		
		// Loop cac bom
		foreach ($bomConfig as $bomID=>$bomInfo)
		{
			if($bomInfo['Assembly'])
			{
				foreach ($bomInfo['Detail'] as $operationID=>$operationInfo)
				{
					foreach ($operationInfo['Output'] as $index=>$outputInfo)
					{
						if(!isset($retval[$bomID][$operationInfo['Key']]))
						{
							$retval[$bomID][$outputInfo['Key']] = $outputInfo['ItemQty'];
						}
						else 
						{
							$retval[$bomID][$outputInfo['Key']] -= $outputInfo['ItemQty'];
						}
					}// Endforeach: loop cac san pham dau ra hay phu pham cua mot cong doan
				}// Endforeach: loop cac cong doan trong mot bom
			} // Endif: Neu bom la thao ro thi moi tinh phu pham hay san pham dau ra
		} // Endforeach: loop cac bom
		
		return $retval;
	}
	// End getOutputByBOM()
		
	
	// @Function: getSparepartByBOM(), lay cong cu dung cu theo bom 
	// @Parameter: 
	//		- $bomConfig: Cau hinh "Thiet ke san pham" duoc lay tu ham getBOMConfig()
	// @Return: Tra ve cac cong cu dung cu yeu cau
	public function getSparepartByBOM($bomConfig)
	{
		$retval = array();
		// Loop cac bom
		foreach ($bomConfig as $bomID=>$bomInfo)
		{
			if($bomInfo['Assembly'])
			{
				foreach ($bomInfo['Detail'] as $operationID=>$operationInfo)
				{
					foreach ($operationInfo['Sparepart'] as $index=>$sparepartInfo)
					{
						if(!isset($retval[$bomID][$operationInfo['Key']]))
						{
							$retval[$bomID][$sparepartInfo['Key']] = $sparepartInfo['ItemQty'];
						}
						else 
						{
							$retval[$bomID][$sparepartInfo['Key']] += $sparepartInfo['ItemQty'];
						}
					}// Endforeach: loop cac cong cu dung cu cua mot cong doan
				}// Endforeach: loop cac cong doan trong mot bom
			} // Endif: Neu bom la thao ro thi moi tinh phu pham hay san pham dau ra
		} // Endforeach: loop cac bom
		
		return $retval;
	}
	// End getSparepartByBOM()
	
	
	// @Function: getMaterialByBOM(), lay thanh phan theo bom
	// @Parameter: 
	// 		- $bomConfig: Thong tin cua mot hoac nhieu "Thiet ke san pham",
	// duoc lay tu ham getBOMConfig()
	// @Return: Tra ve cac vat lieu can de san xuat da tru di cac san pham dau ra
	public function getMaterialByBOM($bomConfig)
	{
		$materials = array();
		$output    = array();
		foreach ($bomConfig as $bomID=>$bomInfo)
		{
			$keyArr = array();
			foreach ($bomInfo['Detail'] as $operationID=>$operationInfo) 
			{
				
				// cong don thanh phan giong nhau trong mot bom
				// tru di so luong dau ra su dung nhu thanh phan o cong doan tiep theo
				
				// Lay thanh phan va luu lai key cho tung thanh phan
				if($bomInfo['Assembly'])// Neu la thao ro tinh them thanh phan la san pham dong chinh
				{
					
					$keyArr[] = $bomInfo['MainKey'];
					$materials[$bomID][$bomInfo['MainKey']] = $bomInfo;
					//$materials[$bomID][$bomInfo['MainKey']]['ItemCode']  = $bomInfo['MainItemCode'];
					///$materials[$bomID][$bomInfo['MainKey']]['ItemName']  = $bomInfo['MainItemName'];
					//$materials[$bomID][$bomInfo['MainKey']]['Attribute'] = $bomInfo['MainAttribute']; 
					//$materials[$bomID][$bomInfo['MainKey']]['ItemQty']   = $bomInfo['MainQty']; 
				}
				
				foreach($operationInfo['Input'] as $input)
				{
					if(!in_array($input['Key'], $keyArr))
					{
						$keyArr[] = $input['Key'];
						$materials[$bomID][$input['Key']] = $input;
						//$materials[$bomID][$input['Key']]['ItemCode']  = $input['ItemCode'];
						//$materials[$bomID][$input['Key']]['ItemName']  = $input['ItemName'];
						//$materials[$bomID][$input['Key']]['Attribute'] = $input['Attribute']; 
						//$materials[$bomID][$input['Key']]['ItemQty'] = $input['ItemQty'];
					}
					else 
					{
						$materials[$bomID][$input['Key']]['Attribute'] += $input['ItemQty'];
					} 
				}
				
				// Lay dau ra cua cac cong doan
				foreach ($operationInfo['Output'] as $out)
				{
					$output[$bomID][$out['Key']]  = $out;
					//$output[$bomID][$out['Key']]['ItemCode']  = $out['ItemCode'];
					//$output[$bomID][$out['Key']]['ItemName']  = $out['ItemName'];
					//$output[$bomID][$out['Key']]['Attribute'] = $out['Attribute']; 
					///$output[$bomID][$out['Key']]['ItemQty'] = $out['ItemQty'];
				}
			}
			
			foreach ($keyArr as $key)
			{
				if(isset($output[$bomID][$key]) && $output[$bomID][$key]['ItemQty'])
				{
					$materials[$bomID][$key]['ItemQty'] -= $output[$bomID][$key]['ItemQty'];
					$materials[$bomID][$key]['ItemQty'] = ($materials[$bomID][$key]['ItemQty'] >= 0)?$materials[$bomID][$key]['ItemQty']:0;
				}
			}
		}
		return $materials;
	}
	// End getMaterialByBOM()

	
	// @Remove
	// @Function: getProduction(), lay san pham can san xuat da duoc cong don san pham theo bom
	// @Parameter:
	//		- $params: Thong tin san pham can san xuat, array(array(bom=>,ItemQty=>))
	// @Return: cac san pham can san xuat duoc cong don  theo bom
	public function getProduction($params)
	{
		/*
		$retval = array();
		$i = 0;
		
		foreach ($params['bom'] as $bomID)
		{
			if(!isset($retval[$bomID]))
			{
				
			}
			else
			{
				
			}
			$i++;
		}
		return $retval;
		*/
	}
	// End getProduction()
	
	// @Function: getQtyReality(), tra ve phu pham hay san pham dau ra theo thuc te sx
	// @Parameter:
	//		- $config: San pham dau ra, thanh phan, cong cu dung cu se co theo bom
	//		- $bomConfig: Cau hinh "Thiet ke san pham"
	//		- $params: Thong tin san pham can san xuat, array(array(bom=>,ItemQty=>))
	// @Return: Tra ve phu pham, thanh phan, cong cu dung cu theo thuc te
	// @return: array if true or false if false
	// @error: empty array if empty member
	// @error: false if bom qty is null or equal zero
	public function getQtyReality($config, $bomConfig, $params)
	{
		$retval = array(); // tra ve khong xet theo bom cu the
		$retval2 = array(); // tra ve theo tung bom
		$lastRetval = array();
		
		$i = 0;
		foreach ($params['bom'] as $bomID)
		{
			// Neu ton tai phu pham cua bom
			if(isset($config[$bomID]))
			{
				
				if($retval === false)
				{
					break;
				}
				
				foreach ($config[$bomID] as $key=>$item)
				{
					if(!$bomConfig[$bomID]['MainQty'])
					{
						$retval = false;
						break;
					}
					
					if($params['ItemQty'][$i] && $item['ItemQty'])
					{
						if(isset($retval[$key]))
						{
							$retval[$key]['ItemQty'] += ($item['ItemQty'] * $params['ItemQty'][$i]) / $bomConfig[$bomID]['MainQty'];
						}
						else 
						{
							$retval[$key] = $item;
							$retval[$key]['ItemQty'] = ($item['ItemQty'] * $params['ItemQty'][$i]) / $bomConfig[$bomID]['MainQty'];
						}
						
						
						//$retval2
						if(isset($retval2[$bomID][$key]))
						{
							$retval2[$bomID][$key]['ItemQty'] += ($item['ItemQty'] * $params['ItemQty'][$i]) / $bomConfig[$bomID]['MainQty'];
						}
						else 
						{
							$retval2[$bomID][$key] = $item;
							$retval2[$bomID][$key]['ItemQty'] = ($item['ItemQty'] * $params['ItemQty'][$i]) / $bomConfig[$bomID]['MainQty'];
						}
					} // Endif: Neu co so luong sx va so luong phu pham
				}// Endforeach: loop san pham dau ra hay phu pham
			}// Endif: Neu co san pham dau ra	
			$i++;		
		}// Endforeach: loop qua ca bom 
		
		$lastRetval = array('byBOM'=>$retval2,'notByBOM'=>$retval);
		return $lastRetval;	
	}
	// End getQtyReality()
	
	// @Remove
	// @Function: getMaterialRequire(), lay nguyen vat lieu yeu cau thuc te
	// @Parameter: 
	// 		- $materials: Nguyen vat lieu can de san xuat lay tu ham getMaterialByBOM().
	//		- $bomConfig: Cau hinh cac "Thiet ke san pham"
	//		- $params: Thong tin bom id va so luong san pham can sx, array(array(bom=>id,ItemQty=>))
	// @Return: 
	public function getMaterialRequire($materials, $bomConfig, $params)
	{
		/*
		$retval = array();
		$i = 0;
		foreach ($params['bom'] as $bomID)
		{
			// Neu ton tai thanh phan cua bom
			if(isset($materials[$bomID]))
			{
				
				if($retval === false)
				{
					break;
				}
				
				// Lay thanh phan
				foreach ($materials[$bomID] as $key=>$item)
				{
					if(!$bomConfig[$bomID]['MainQty'])
					{
						$retval = false;
						break;
					}
					
					if($params['ItemQty'][$i] && $item['ItemQty'])
					{
						if(isset($retval[$key]))
						{
							$retval[$key]['ItemQty'] += ($item['ItemQty'] * $params['ItemQty'][$i]) / $bomConfig[$bomID]['MainQty'];
						}
						else 
						{
							$retval[$key] = $item;
							$retval[$key]['ItemQty'] = ($item['ItemQty'] * $params['ItemQty'][$i]) / $bomConfig[$bomID]['MainQty'];
						}
					}
				}
			}	
			$i++;		
		}
		return $retval;
		*/
	}
	// End getMaterialRequire()
	
	// @Function: getTimeRequire(), lay thoi gian yeu cau thuc te
	// @Parameter: 
	//		- $bomConfig: Cau hinh cac "Thiet ke san pham"
	//		- $params: Thong tin bom id va so luong san pham can sx, array('bom'=>id, 'ItemQty'=>)
	// @Return: 
	// @return: array if true or false if false
	// @error: empty array if empty member
	// @error: false if bom qty is null or equal zero
	public function getTimeRequire($bomConfig, $params)
	{
		$retval = array(); // Khong theo bom (khong phu thuoc don vi thuc hien)
		$retval2 = array(); // theo bom (khong phu thuoc don vi thuc hien)
		$lastRetval = array();
		$i = 0;
		
		foreach ($params['bom'] as $bomID)
		{
			if($retval === false)
			{
				break;
			}
			foreach ($bomConfig[$bomID]['Detail'] as $operationID=>$operationInfo)
			{
				if(!$bomConfig[$bomID]['MainQty'])
				{
					$retval = false;
				}
				if(isset($retval[$operationID]))
				{
					$retval[$operationID]['Time'] +=  ($params['ItemQty'][$i] * $operationInfo['Time'])/$bomConfig[$bomID]['MainQty'];
				}
				else 
				{
					$retval[$operationID] = $operationInfo;
					$retval[$operationID]['Time'] =  ($params['ItemQty'][$i] * $operationInfo['Time'])/$bomConfig[$bomID]['MainQty'];
				}
				
				if(isset($retval2[$bomID][$operationID]))
				{
					$retval2[$bomID][$operationID]['Time'] +=  ($params['ItemQty'][$i] * $operationInfo['Time'])/$bomConfig[$bomID]['MainQty'];
				}
				else 
				{
					$retval2[$bomID][$operationID] = $operationInfo;
					$retval2[$bomID][$operationID]['Time'] =  ($params['ItemQty'][$i] * $operationInfo['Time'])/$bomConfig[$bomID]['MainQty'];
				}
			}
			$i++;
		}
		
		$lastRetval = array('byBOM'=>$retval2,'notByBOM'=>$retval);
		return $lastRetval;
	}
	// End getTimeRequire()
	
	// @Function: getWCal()
	// @Parameter: 
	//		- $params: day chuyen id, ngay bat dau, ngay ket thuc, array(lineFilter=>id,fromDateFilter,toDateFilter)
	// @Return: Lich lam viec theo khoang thoi gian
	public function getWCal($params)
	{
		// Lay lich lam viec
		$lichLamViec = $this->_common->getTable(array('Ref_LichLamViec'), 'ODayChuyen', array('IOID'=>$params['lineFilter']), array(), 1, 1);
		$lichLamViec = $lichLamViec?$lichLamViec->Ref_LichLamViec:0;
		
		$wCalEnd = array();
		$wCal = Qss_Lib_Extra::getWorkingHoursPerShiftByCal($lichLamViec);
		$sCal = Qss_Lib_Extra::getLichDacBiet($lichLamViec, $params['fromDateFilter']);
		$start = date_create($params['fromDateFilter']);
		$end   = date_create($params['toDateFilter']);

		while($start <= $end)
		{
			$weekday = $start->format('w');
			$startToDate = $start->format('Y-m-d');
			$wCalEnd[$startToDate] = isset($sCal[$lichLamViec][$startToDate])?$sCal[$lichLamViec][$startToDate]:@$wCal[$lichLamViec][$weekday];
			$start = Qss_Lib_Date::add_date($start, 1);
		}
		return $wCalEnd;
	}
	// End getWCal()
	
	
	// @Function: getAvailableTime()
	// @Parameter: 
	//		- $params: day chuyen id, ngay bat dau, ngay ket thuc, array(lineFilter=>id,fromDateFilter,toDateFilter)
	// @Return: Lich lam viec theo khoang thoi gian tinh them hieu suat don vi thuc hien
	public function getAvailableTime($params)
	{
		$retval = array();
		$wCalEnd = $this->getWCal($params);
		
		// Lay don vi theo day chuyen
		$wCenter = $this->_common->getObjectByIDArr($params['lineFilter'], array('code'=>'M702', 'table'=>'OCongDoanDayChuyen', 'main'=>'ODayChuyen'));
		
		foreach ($wCalEnd as $refCal=>$date)
		{
			foreach ($date as $shift=>$hours)
			{
				foreach ($wCenter as $item)
				{
					if(isset($retval[$item->Ref_CongDoan]))
					{
						$retval[$item->Ref_CongDoan] += ($hours * $item->HieuSuat)/100;
					}
					else
					{
						$retval[$item->Ref_CongDoan] = ($hours * $item->HieuSuat)/100;
					}
				}
			}
		}
		
		return $retval;
	}
	// End getAvailableTime()

	// @Function: getCost()
	// @Parameter: 
	//		- $bomConfig: cau hinh bom
	//		- $params: so luong san pham, array(ItemQty=>)
	// @Return: Lich lam viec the
	public function getCost($bomConfig, $params)
	{
		$retval = array(); // kho theo bom
		$retval2 = array(); // theo bom
		$lastRetval = array();
		$i = 0;
		
		foreach ($params['bom'] as $bomID)
		{
			if($retval === false)
			{
				break;
			}
			foreach ($bomConfig[$bomID]['Detail'] as $operationID=>$operationInfo)
			{
				if(!$bomConfig[$bomID]['MainQty'])
				{
					$retval = false;
				}
				if(isset($retval[$operationID]))
				{
					$retval[$operationID] +=  ($params['ItemQty'][$i] * $operationInfo['Cost'])/$bomConfig[$bomID]['MainQty'];
				}
				else 
				{
					$retval[$operationID] =  ($params['ItemQty'][$i] * $operationInfo['Cost'])/$bomConfig[$bomID]['MainQty'];
				}
				
				
				if(isset($retval[$bomID][$operationID]))
				{
					$retval[$bomID][$operationID] +=  ($params['ItemQty'][$i] * $operationInfo['Cost'])/$bomConfig[$bomID]['MainQty'];
				}
				else 
				{
					$retval[$bomID][$operationID] =  ($params['ItemQty'][$i] * $operationInfo['Cost'])/$bomConfig[$bomID]['MainQty'];
				}
			}
			$i++;
		}
		
		$lastRetval = array('byBOM'=>$retval2,'notByBOM'=>$retval);
		return $lastRetval;
	}
	// End getCost()

	// @Function: getProductionPlaned()
	// @Parameter: 
	//		- $startDate: Ngay bat dau
	// @Return: Thoi gian da dat san xuat
	// @todo: phai tru di thoi gian ko thuoc khoang
	// @todo: Phai them dieu kien enddate
	public function getProductionPlaned($startDate)
	{
		$daLay = $this->_production->getDaLay(Qss_Lib_Date::displaytomysql($startDate)); 
		$daDatSanXuat = array();
		foreach ($daLay as $datTruoc)
		{			
			
			if(isset($daDatSanXuat[$datTruoc->RefOperation]))
			{
				$daDatSanXuat[$datTruoc->RefOperation] +=  ($datTruoc->ThoiGian * $datTruoc->Performance)/100;
			}
			else 
			{
				$daDatSanXuat[$datTruoc->RefOperation]  =  ($datTruoc->ThoiGian * $datTruoc->Performance)/100;
			}
			
			
		} // End loop da lay
		return $daDatSanXuat;
	}
	// End getProductionPlaned()

	// @Function: getRequirementOrdered(), lay cac yeu cau da dat lenh
	// @Parameter: 
	//		- $startDate: Ngay bat dau
	// @Return: Thoi gian da dat san xuat
	public function getRequirementOrdered()
	{
		$retval = array();
		
		return $retval;
	}
	// End getRequirementOrdered()
}
?>
