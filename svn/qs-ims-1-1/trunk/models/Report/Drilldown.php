<?php

class Qss_Model_Report_Drilldown extends Qss_Model_Abstract
{
    public $i_RecordCount = 0;

    public $i_PageCount = 0;

    public function __construct()
    {
        parent::__construct();
    }

    public function a_fGetIOIDBySQL ($sql, $i_CurrentPage = 1, $i_Limit = 50)
    {
        if($this->i_PageCount && $i_CurrentPage > $this->i_PageCount)
        {
            $i_CurrentPage = 1;
        }
        if($i_Limit)
        {
            $sql .= sprintf(' LIMIT %1$d,%2$d', ($i_CurrentPage - 1) * $i_Limit, $i_Limit);
        }
        //echo $sql;die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function i_fGetPageCount ($sql, $i_CurrentPage = 1, $i_Limit = 50)
    {
        if ( $this->i_PageCount )
        {
            return $this->i_PageCount;
        }
        $order = array("\r\n", "\n", "\r", "\t");
        $sql1 = preg_replace("/(select) .* (from)/s", "$1 count(distinct v.IOID) as recordcounts from ", str_replace($order, ' ', $sql), 1);
        $sql1 = preg_replace("/(order by).*/s", " ", $sql1, 1);
        //echo $sql1;die;
        $o_Count = $this->_o_DB->fetchOne($sql1);
        if ( $o_Count )
        {
            $this->i_RecordCount = $o_Count->recordcounts ? $o_Count->recordcounts : 1;
        }
        $this->i_PageCount = ceil($this->i_RecordCount / $i_Limit);
        return $this->i_PageCount;
    }


    function sz_fGetSQLByUser (
        $fid,
        $ifid,
        $objid,
        Qss_Model_UserInfo $o_User,
        $i_FieldOrder = 0,
        $sz_OrderType = 'ASC',
        $a_Condition = array(),//filter cretical
        $groupbys=array(),
        $status = array(),//filter by status
        $tree = true)//filter by uid
    {
        $mForm        = new Qss_Model_Form();
        $mForm->init($fid);
        $params       = $a_Condition;
        $i_FieldOrder = trim($i_FieldOrder);
        $retval       = array();
        $filter       = array();

        $filterstatus = '';
        $treeselect   = '';
        $where        = '';
        $join         = '';

        if(!$objid) // Lấy main object
        {
            $object = $mForm->o_fGetMainObject();
            $objid  = $object->ObjectCode;
        }

        $tempMainobjects  = $mForm->o_fGetObjectByCode($objid);
        $mainobjects      = array();
        $mainobjects[]    = $tempMainobjects;

        if ( count($a_Condition) )
        {
            foreach($mForm->a_Objects as $item)
            {
                $hasSearch = false;
                $alias = ($item->ObjectCode == $objid)?'v':$item->ObjectCode;
                foreach ($item->loadFields() as $key => $f)
                {
                    $fieldalias = ($mForm->o_fGetObjectByCode($objid)->ObjectCode === $f->ObjectCode)?('v.'.$f->FieldCode):($f->ObjectCode.'.'.$f->FieldCode);

                        if(!isset($a_Condition[$f->ObjectCode . '_' . $f->FieldCode])
                            && !isset($a_Condition[$f->ObjectCode . '_' . $f->FieldCode.'_S'])
                            && !isset($a_Condition[$f->ObjectCode . '_' . $f->FieldCode.'_E']))
                        {
                            continue;
                        }
                        if(($f->intInputType == 5 || $f->intInputType == 3) && $f->getJsonRegx())
                        {
                            $val = $a_Condition[$f->ObjectCode . '_' . $f->FieldCode];
                            if ( $val )
                            {
                                $where .= sprintf(' and %1$s like %2$s ',
                                    $alias . '.Ref_' . $f->FieldCode,
                                    $this->_o_DB->quote( '%'.$val.'%' ));
                                $hasSearch = true;
                            }
                            continue;
                        }
                        switch ( $f->intFieldType )
                        {
                            case 1:
                            case 2:
                            case 3:
                            case 4:
                            case 12:
                            case 13:
                            case 14:
                            case 15:
                            case 16:
                                $val = $a_Condition[$f->ObjectCode . '_' . $f->FieldCode];
                                if ( $val )
                                {
                                    $where .= sprintf(' and replace(%1$s, "\n"," ") like trim(%2$s) ',
                                        $alias . '.' . $f->FieldCode,
                                        $this->_o_DB->quote( '%'.$val.'%' ));
                                    $hasSearch = true;
                                }
                                break;
                            case 5:
                            case 6:
                                $val = $a_Condition[$f->ObjectCode . '_' . $f->FieldCode];
                                if ( $val )
                                {
                                    if(preg_match_all("/(>|<|=|<=|>=|<>)([0-9(.)]+)/", $val, $match))
                                    {
                                        $regcon = '';
                                        $operator = 'and';
                                        if(in_array('=',$match[1]))
                                        {
                                            $operator = 'or';
                                        }
                                        elseif(preg_match_all("/(>|>=)([0-9(.)]+)/", $val, $match1) && preg_match_all("/(<|<=)([0-9(.)]+)/", $val, $match2))
                                        {
                                            if(max($match1[2]) > min($match2[2]))
                                            {
                                                $operator = 'or';
                                            }
                                        }
                                        foreach($match[0] as $value)
                                        {
                                            if($regcon != '')
                                            {
                                                $regcon .= ' ' . $operator . ' ';
                                            }
                                            $regcon .= sprintf('%1$s %2$s ', $alias . '.' . $f->FieldCode, $value);
                                        }
                                        $where .= sprintf(' and (%1$s) ', $regcon);
                                    }
                                    else
                                    {
                                        $where .= sprintf(' and %1$s = %2$d ',
                                            $alias . '.' . $f->FieldCode,
                                            $val);
                                    }
                                    $hasSearch = true;
                                }
                                break;
                            case 11:
                                $val = $a_Condition[$f->ObjectCode . '_' . $f->FieldCode];
                                if ( $val )
                                {
                                    if(preg_match_all("/(>|<|=|<=|>=|<>)([0-9(.)]+)/", $val, $match))
                                    {
                                        $regcon = '';
                                        $operator = 'and';
                                        if(in_array('=',$match[1]))
                                        {
                                            $operator = 'or';
                                        }
                                        elseif(preg_match_all("/(>|>=)([0-9(.)]+)/", $val, $match1) && preg_match_all("/(<|<=)([0-9(.)]+)/", $val, $match2))
                                        {
                                            if(max($match1[2]) > min($match2[2]))
                                            {
                                                $operator = 'or';
                                            }
                                        }
                                        foreach($match[0] as $value)
                                        {
                                            if($regcon != '')
                                            {
                                                $regcon .= ' ' . $operator . ' ';
                                            }
                                            $regcon .= sprintf('%1$s/1000 %2$s ', $alias . '.' . $f->FieldCode, $value);
                                        }
                                        $where .= sprintf(' and (%3$s) ', $regcon);
                                    }
                                    else
                                    {
                                        $where .= sprintf(' and %1$s/1000 = %2$d ',
                                            $alias . '.' . $f->FieldCode,
                                            $val);
                                    }
                                    $hasSearch = true;
                                }
                                break;
                            case 7:
                                $val = (int) $a_Condition[$f->ObjectCode . '_' . $f->FieldCode];
                                if ( $val != -1 )
                                {
                                    if($val == 1)
                                    {
                                        $where .= sprintf(' and %1$s = %2$d ',
                                            $alias . '.' . $f->FieldCode,
                                            $val);
                                    }
                                    else
                                    {
                                        $where .= sprintf(' and (%1$s is null or %1$s = 0) ',
                                            $alias . '.' . $f->FieldCode,
                                            $val);
                                    }
                                    $hasSearch = true;
                                }
                                break;
                            case 10:
                                $val1 = isset($a_Condition[$f->ObjectCode . '_' . $f->FieldCode . '_S'])?$a_Condition[$f->ObjectCode . '_' . $f->FieldCode . '_S']:'';
                                $val2 = isset($a_Condition[$f->ObjectCode . '_' . $f->FieldCode . '_E'])?$a_Condition[$f->ObjectCode . '_' . $f->FieldCode . '_E']:'';
                                if ( $val1 && $val2 )
                                {
                                    $where .= sprintf(' and %1$s between str_to_date(%2$s ,\'%%d-%%m-%%Y\') and str_to_date(%3$s ,\'%%d-%%m-%%Y\')',
                                        $alias . '.' . $f->FieldCode, $this->_o_DB->quote($val1), $this->_o_DB->quote($val2));
                                    $hasSearch = true;
                                }
                                elseif ( $val1 && !$val2 )
                                {
                                    $where .= sprintf(' and %1$s >= str_to_date(%2$s ,\'%%d-%%m-%%Y\')',
                                        $alias . '.' . $f->FieldCode, $this->_o_DB->quote($val1));
                                    $hasSearch = true;
                                }
                                elseif ( !$val1 && $val2 )
                                {
                                    $where .= sprintf(' %1$s <= str_to_date(%2$s ,\'%%d-%%m-%%Y\')',
                                        $alias . '.' . $f->FieldCode, $this->_o_DB->quote($val2));
                                    $hasSearch = true;
                                }
                                break;
                        }

                }
                if($hasSearch && $mForm->o_fGetObjectByCode($objid)->ObjectCode !== $f->ObjectCode)
                {
                    $join .= sprintf(' left join %1$s on v.IFID_%2$s = %1$s.IFID_%2$s ',$item->ObjectCode,$mForm->FormCode);
                }
            }
        }

        $order = array();
        $sort = false;
        foreach($mainobjects as $object)
        {
            if($object->b_Tree && (!count($groupbys) || !is_array($groupbys)))//đã group by thì không cần tree nữa
            {
                $treeselect = sprintf(' ,(SELECT count(*) FROM %1$s as u WHERE u.lft<=v.lft and u.rgt >= v.rgt) as level ',
                    $object->ObjectCode);
                $treecookie = Qss_Cookie::get('form_'.$mForm->FormCode.'_tree', 'a:0:{}');
                $treecookie = unserialize($treecookie);
                $order[] = 'v.lft';
                foreach ($treecookie as $closefid)
                {
                    $where .= sprintf(' and not (lft > ifnull((SELECT lft FROM %1$s WHERE IFID_%2$s = %3$d),0) and v.rgt < ifnull((SELECT rgt FROM %1$s WHERE IFID_%2$s = %3$d),0)) ',
                        $object->ObjectCode,
                        $mForm->FormCode,
                        $closefid);

                }
                $treeselect .= sprintf(' , case when v.IFID in (%1$s) then 1 else 0 end as close ',implode(',', count($treecookie)?$treecookie:array(-1)));
                //echo $where;die;
            }
            if(count($groupbys) && is_array($groupbys))
            {
                foreach ($groupbys as $groupby)
                {
                    if($groupby == -2)
                    {
                        $order[] = sprintf('qsiforms.UID %1$s', $sz_OrderType);
                    }
                    elseif($groupby == -1 && $mForm->i_Type == Qss_Lib_Const::FORM_TYPE_MODULE)
                    {
                        $order[] = sprintf('qsiforms.Status %1$s', $sz_OrderType);
                    }
                    else
                    {
                        $fgroupby = $mForm->getFieldByCode($object->ObjectCode,$groupby);
                        if($fgroupby->ObjectCode == $object->ObjectCode
                            && $fgroupby->intFieldType != 4
                            && $fgroupby->intFieldType != 9
                            && $fgroupby->intFieldType != 8
                            && $fgroupby->intFieldType != 17 )
                        {
                            $order[] = sprintf('%1$s DESC',$fgroupby->FieldCode);
                            $sort = true;
                        }
                    }
                }
            }
            elseif ( $i_FieldOrder == -1)
            {
                $order[] = sprintf('qsiforms.Status %1$s', $sz_OrderType);

            }
            elseif ($i_FieldOrder  )
            {
                $foder = $mForm->getFieldByCode($object->ObjectCode, $i_FieldOrder);
                if ( $foder->ObjectCode == $object->ObjectCode
                    && $foder->intFieldType != 4
                    && $foder->intFieldType != 9
                    && $foder->intFieldType != 8
                    && $foder->intFieldType != 17 )
                {
                    $order[] = sprintf('v.%2$s %1$s', $sz_OrderType,$foder->FieldCode);
                    $sort = true;
                }
            }
            elseif(!$i_FieldOrder && $object->orderField)
            {
                $foder = $mForm->getFieldByCode($object->ObjectCode,$object->orderField);
                if ( $foder->ObjectCode == $object->ObjectCode
                    && $foder->intFieldType != 4
                    && $foder->intFieldType != 9
                    && $foder->intFieldType != 8
                    && $foder->intFieldType != 17 )
                {
                    $jsonData = $object->getJsonData();
                    if(is_array($jsonData))
                    {
                        $order[] = sprintf('FIELD(%2$s,%1$s) DESC', "'".implode(',', array_keys($jsonData))."'",$foder->FieldCode);
                        //$order[] = sprintf('qsiforms.SDate DESC');
                        //@TODO$order[] = sprintf('TIME(FROM_UNIXTIME(qsiforms.SDate)) DESC');
                    }
                    else
                    {
                        $order[] = sprintf('%2$s %1$s', $object->orderType,$foder->FieldCode);
                    }
                    $sort = true;
                }
            }
            if($mForm->i_WorkFlowID)
            {
                $order[] = sprintf('qsiforms.IFID DESC');
                //$order[] = sprintf('DATE(FROM_UNIXTIME(qsiforms.SDate)) DESC');@TODO
            }
            else
            {
                $order[] = sprintf('qsiforms.IFID');
            }

            $classname = 'Qss_Bin_Filter_'.$object->ObjectCode;
            if(class_exists($classname))
            {
                //echo $classname;die;
                $exfilter = new $classname($o_User,$params);
                //$join .= $exfilter->getJoin();
                $where .= $exfilter->getWhere();
            }
            //gộp với tab
            if(count($status) && $mForm->i_Type == Qss_Lib_Const::FORM_TYPE_MODULE)
            {
                $where .= sprintf(' and qsiforms.Status in (%1$s)',implode(',',$status));
            }

            // Loc theo ifid
            if($ifid)
            {
                $where .= sprintf(' and v.IFID_%1$s = %2$d ', $mForm->FormCode, $ifid);
            }

            if(count($order))
            {
                $where .= sprintf(' order by %1$s',implode(',', $order));
            }

            if ( $mForm->i_Type == 1 || $mForm->i_Type == Qss_Lib_Const::FORM_TYPE_PROCESS)
            {
                $sql = sprintf('select distinct v.*, qsiforms.*,qsusers.UserName
									/*%1$s*/
									from %2$s as v
									inner join qsiforms on qsiforms.IFID = v.IFID_%3$s
									inner join qsusers on qsiforms.UID=qsusers.UID
									%7$s
									where qsiforms.Deleted<>1 
									%5$s
									%6$s', /* */
                    $treeselect,
                    $mForm->o_fGetObjectByCode($objid)->ObjectCode,
                    $mForm->FormCode,
                    $this->_o_DB->quote($mForm->FormCode),
                    $filterstatus,
                    $where,
                    $join
                );
            }
            elseif ( $mForm->i_Type == 2 )
            {
                $sql = sprintf('select distinct v.*, qsiforms.*,qsusers.UserName
									/*%1$s*/
									from %2$s as v
									inner join qsiforms on qsiforms.IFID = v.IFID_%3$s
									inner join qsusers on qsiforms.UID=qsusers.UID
									%8$s
									where qsiforms.Deleted<>1 
									and qsiforms.DepartmentID in(%7$s)
									%5$s
									%6$s', /* */
                    $treeselect,
                    $mForm->o_fGetObjectByCode($objid)->ObjectCode,
                    $mForm->FormCode,
                    $this->_o_DB->quote($mForm->FormCode),
                    $filterstatus,
                    $where,
                    $o_User->user_dept_id . ',' . $o_User->user_dept_list,
                    $join);
            }
            else
            {
                $statusRights = '';
                foreach ($mForm->a_Rights as $k=>$v)
                {
                    if($statusRights)
                    {
                        $statusRights .= ' or ';
                    }
                    $statusRights .= sprintf('(case when Status = %1$d then %2$d end)'
                        ,$k,$v);
                }
                $sql = sprintf('select distinct v.*, qsiforms.*,qsusers.UserName
									/*%1$s*/
									from %2$s as v
									inner join qsiforms on qsiforms.IFID = v.IFID_%3$s
									inner join qsusers on qsiforms.UID=qsusers.UID
									%9$s
									where (%10$s)
									and qsiforms.Deleted<>1 
									and qsiforms.DepartmentID in(%7$s) 
									%5$s
									%6$s', /* */
                    $treeselect,
                    $mForm->o_fGetObjectByCode($objid)->ObjectCode,
                    $mForm->FormCode,
                    $this->_o_DB->quote($mForm->FormCode),
                    $filterstatus,
                    $where,
                    $o_User->user_dept_id . ',' . $o_User->user_dept_list,
                    $mForm->i_Rights,
                    $join,
                    $statusRights);
            }
            if($treeselect && $tree)
            {
                //$sql = str_ireplace(array('select','from','where'),array('SELECT','FROM','WHERE'),$sql);
                $sql = sprintf('select v.* %1$s from (%2$s) as v', $treeselect,$sql);
            }
            $retval[$object->ObjectCode] = $sql;
            //for sort
            if($sort)
            {
                $retval[0] = $sql;
            }
        }
        //echo $sql;die;
        if(!isset($retval[0]))
        {
            $retval[0] = $retval[$mForm->o_fGetObjectByCode($objid)->ObjectCode];
        }

        return $retval;
    }
}