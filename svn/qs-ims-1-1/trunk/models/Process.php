<?php

/**
 * Model for instanse form
 *
 */
class Qss_Model_Process extends Qss_Model_Abstract
{
        public static $arrField = array(1 => 'Date', 2 => 'STime', 3 => 'ETime',
            4 => 'UID', 5 => 'Status');
        public static $arrGroup = array(1 => 'Date', 4 => 'UserName', 5 => 'Status');

        /**
         * Working with all design of form that user acess via module management
         * '
         * @access  public
         */
        public function __construct()
        {
                parent::__construct();
        }

        public function getLogs($ifid, $currentpage = 1, $limit = 20,
                $fieldorder, $ordertype = 'ASC', $groupby, $a_Filter)
        {
                $sqllimit = sprintf(' LIMIT %1$d,%2$d',
                        (($currentpage ? $currentpage : 1) - 1) * $limit, $limit);
                $orderfields = array();
                if (isset(self::$arrField[$groupby]))
                {
                        $orderfields[] = self::$arrField[$groupby] . ' ' . $ordertype;
                }
                if (isset(self::$arrField[$fieldorder]))
                {
                        $orderfields[] = self::$arrField[$fieldorder] . ' ' . $ordertype;
                }
                else
                {
                        $orderfields[] = 'Date DESC';
                }
                $sqlorder = sprintf('order by qsfprocesslog.%1$s',
                        implode(',', $orderfields));
                $sqlwhere = '';
                if (is_array($a_Filter))
                {
                        foreach ($a_Filter as $key => $val)
                        {
                                if (isset(self::$arrField[$key]))
                                {
                                        if ($key == 1)
                                        {
                                                $sqlwhere .= sprintf(' and %1$s = str_to_date(%2$s,\'%%d-%%m-%%Y\') ',
                                                        self::$arrField[$key],
                                                        $this->_o_DB->quote($val));
                                        }
                                        else
                                        {
                                                $sqlwhere .= sprintf(' and qsfprocesslog.%1$s = %2$d',
                                                        self::$arrField[$key],
                                                        $val);
                                        }
                                }
                        }
                }
                $sql = sprintf('select *
                                from qsfprocesslog
                                inner join qsusers on qsfprocesslog.UID = qsusers.UID
                                where qsfprocesslog.IFID = %1$d %4$s 
                                %2$s %3$s
                                ', $ifid, $sqlorder, $sqllimit, $sqlwhere);
                //	echo $sql;die;
                return $this->_o_DB->fetchAll($sql);
        }

        public function countLogs($ifid, $currentpage = 1, $limit = 20,
                $fieldorder, $ordertype = 'ASC', $a_Filter)
        {
                $sqlwhere = '';
                if (is_array($a_Filter))
                {
                        foreach ($a_Filter as $key => $val)
                        {
                                if (isset(self::$arrField[$key]))
                                {
                                        if ($key == 1)
                                        {
                                                $sqlwhere .= sprintf(' and %1$s  = str_to_date(%2$s,\'%%d-%%m-%%Y\') ',
                                                        self::$arrField[$key],
                                                        $this->_o_DB->quote($val));
                                        }
                                        else
                                        {
                                                $sqlwhere .= sprintf(' and qsfprocesslog.%1$s = %2$d',
                                                        self::$arrField[$key],
                                                        $val);
                                        }
                                }
                        }
                }

                $sql = sprintf('select count(qsfprocesslog.FPLID) as count
					from qsfprocesslog
					inner join qsusers on qsfprocesslog.UID = qsusers.UID
					where qsfprocesslog.IFID = %1$d %2$s 
					', $ifid, $sqlwhere);
//echo $sql;die;
                $dataSQL = $this->_o_DB->fetchOne($sql);
                return $dataSQL ? $dataSQL->count : 0;
        }

        public function saveLog($data)
        {
                $id = $data['FPLID'];
                if ($id)
                {
                        $sql = sprintf('update qsfprocesslog set %1$s where FPLID = %2$d',
                                $this->arrayToUpdate($data), $id);
                }
                else
                {
                        $sql = sprintf('insert into qsfprocesslog%1$s',
                                $this->arrayToInsert($data));
                }
                return $this->_o_DB->execute($sql);
        }

        public function saveCalendar($params)
        {
                $ifid = $params['ifid'];
                if ($ifid)
                {
                	$sql = sprintf('delete from qsfprocesscalendar where IFID = %1$d'
                                        , $ifid);
                                $this->_o_DB->execute($sql);
                        if (isset($params['times']) && is_array($params['times']))
                        {
                                //echo '<pre>';print_r($params['times']);die;
                                foreach ($params['times'] as $item)
                                {
                                        $arrTimes = explode(',', $item);
                                        if (count($arrTimes) >= 9)
                                        {
                                                $arrTimes[3] = trim($arrTimes[3]);
                                                $arrTimes[4] = trim($arrTimes[4]);
                                                $arrTimes[1] = trim($arrTimes[1]);
                                                
                                                $dataTime = array(
                                                    'IFID' => $ifid,
                                                    'Type' => $arrTimes[0],
                                                    'SDate' => $arrTimes[3]? Qss_Lib_Date::displaytomysql($arrTimes[3]) : '',
                                                    'EDate' => $arrTimes[4]? Qss_Lib_Date::displaytomysql($arrTimes[4]) : '',
                                                    'Date' =>   $arrTimes[1]? Qss_Lib_Date::displaytomysql($arrTimes[1]) : '',
                                                    'Time' => $arrTimes[2],
                                                    'Day' => $arrTimes[6],
                                                    'WDay' => $arrTimes[5],
                                                    'Month' => $arrTimes[7],
                                                    'Interval' => (int) $arrTimes[8]
                                                );
                                                
                                                $sql = sprintf('replace into qsfprocesscalendar%1$s',
                                                        $this->arrayToInsert($dataTime));
                                                $this->_o_DB->execute($sql);
                                        }
                                }
                        }
                        /*else
                        {
                                $dataTime = array(
                                    'IFID' => $ifid,
                                    'Type' => $params['caltype'],
                                    'SDate' => Qss_Lib_Date::displaytomysql(@$params['sdate']),
                                    'EDate' => Qss_Lib_Date::displaytomysql(@$params['edate']),
                                    'Date' => Qss_Lib_Date::displaytomysql(@$params['date']),
                                    'Time' => $params['time'],
                                    'Day' => @$params['day'],
                                    'WDay' => @$params['wday'],
                                    'Month' => @$params['month'],
                                    'Interval' => @(int) $params['interval']
                                );
                                
                                $sql = sprintf('delete from qsfprocesscalendar where IFID = %1$d'
                                        , $ifid);
                                $this->_o_DB->execute($sql);
                                $sql = sprintf('insert into qsfprocesscalendar%1$s'
                                        , $this->arrayToInsert($dataTime));
                                $this->_o_DB->execute($sql);
                        }*/
                }
        }

        public function getCalendars($ifid)
        {
                $sql = sprintf('select * from qsfprocesscalendar where IFID = %1$d',
                        $ifid);
                return $this->_o_DB->fetchAll($sql);
        }

        public function deleteLog($eventid, $elid)
        {
                $sql = sprintf('delete from qsfprocesslog where EventID = %1$d and ELID = %2$d',
                        $eventid, $elid);
                $this->_o_DB->execute($sql);
        }
}

?>