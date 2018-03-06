<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   SqlSession
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2017/12/14
 * @copyright Copyright (C) 2017 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework\core;

class SqlSession
{
    const FLAG_ASSOC = 1;
    const FLAG_NUM = 2;
    const FLAG_BOTH = 3;

    protected $m_dbconn = NULL;

    public $connect_errno = 0;
    public $connect_error = NULL;
    public $error = "";
    public $errno = 0;

    public function &getNativeConnection()
    {
        return $this->m_dbconn;
    }

    public function connect($hostname, $username, $passwd, $dbname = NULL, $port = 3306, $socket = NULL)
    {
        $this->m_dbconn = new \mysqli($hostname, $username, $passwd, $dbname, $port, $socket);
        $this->connect_errno = $this->m_dbconn->connect_errno;
        $this->connect_error = $this->m_dbconn->connect_error;
	    $this->m_dbconn->set_charset("utf8");

        $this->m_dbconn->query("set tx_isolation = 'READ-COMMITTED'");
        $this->m_dbconn->query("COMMIT");
    }

    public function close()
    {
        $this->m_dbconn->close();
    }

    public function getAutocommit()
    {
        $res = $this->m_dbconn->query("SELECT @@autocommit");
        $row = $res->fetch_row();
        return $row[0];
    }

    public function setAutocommit($value)
    {
        return $this->m_dbconn->autocommit($value);
    }

    public function autocommit($value)
    {
        return $this->m_dbconn->autocommit($value);
    }

    public function begin_transaction()
    {
        return $this->m_dbconn->begin_transaction();
    }

    public function commit()
    {
        return $this->m_dbconn->commit();
    }

    public function rollback()
    {
        return $this->m_dbconn->rollback();
    }

    public function real_escape_string($text)
    {
        return $this->m_dbconn->real_escape_string($text);
    }

    public function nativeQuery($sql)
    {
        return $this->m_dbconn->query($sql);
    }

    // return Result class
    public function queryRaw($sql, $parameters = NULL, $bStoreResult = true)
    {
        $oStmt = $this->prepare($sql);
        if(!$oStmt)
        {
            return $oStmt;
        }

        if($parameters) {
            $bind_param_args = array();
            $param_types = '';
            for ($i = 0; $i < count($parameters); $i++) {
                $strtype = strtolower(gettype($parameters[$i]));
                switch ($strtype) {
                    case 'integer':
                        $param_types .= 'i';
                        break;
                    case 'double':
                        $param_types .= 'd';
                        break;
                    default:
                        $param_types .= 's';
                        break;
                }
            }
            $bind_param_args[] = $param_types;
            for ($i = 0; $i < count($parameters); $i++) {
                $bind_param_args[] = &$parameters[$i];
            }
            call_user_func_array(array($oStmt, 'bind_param'), $bind_param_args);
        }

        if(!$oStmt->execute())
        {
            $this->errno = $oStmt->errno;
            $this->error = $oStmt->error;
            $oStmt->close();
            return false;
        }

        if($bStoreResult)
        {
            $oStmt->store_result();
        }

        return new \JsGreenTeaPHPFramework\SqlSession\Result($oStmt, true);
    }

    public function prepare($sql)
    {
        $nativestmt = $this->m_dbconn->prepare($sql);
        if(!$nativestmt) {
            $this->errno = $this->m_dbconn->errno;
            $this->error = $this->m_dbconn->error;
            return NULL;
        }
        $stmt = new \JsGreenTeaPHPFramework\SqlSession\Statment($this, $nativestmt, $sql);
        return $stmt;
    }

    public function insert($tblname, $data, $duplicateUpdateKey = NULL)
    {
        $sql = "INSERT INTO `$tblname` (";
        $pidx = 1;
        $params = array("");
        $tmp = false;
        foreach($data as $key => $value)
        {
            if($tmp)
                $sql .= ", ";
            else
                $tmp = true;
            $sql .= "`$key`";
            $params[$pidx++] = &$data[$key];

            if(is_integer($value) || is_long($value))
                $params[0] .= 'i';
            else if(is_float($value) || is_double($value))
                $params[0] .= 'd';
            else
                $params[0] .= 's';
        }
        $sql .= ") VALUES (";
        $tmp = false;
        foreach($data as $key => $value)
        {
            if($tmp)
                $sql .= ", ";
            else
                $tmp = true;
            $sql .= "?";
        }
        $sql .= ")";
        if($duplicateUpdateKey)
        {
            $sql .= " ON DUPLICATE KEY UPDATE ";
            $tmp = false;
            foreach($data as $key => $value)
            {
                if($key != $duplicateUpdateKey) {
                    if ($tmp)
                        $sql .= ", ";
                    else
                        $tmp = true;
                    $sql .= "`$key`=?";
                    $params[$pidx++] = &$data[$key];

                    if(is_integer($value) || is_long($value))
                        $params[0] .= 'i';
                    else if(is_float($value) || is_double($value))
                        $params[0] .= 'd';
                    else
                        $params[0] .= 's';
                }
            }
        }

        $oStmt = $this->prepare($sql);
        if(!$oStmt) {
            return false;
        }

        $result = false;
        do {
            if (!call_user_func_array(array($oStmt, "bind_param"), $params)) {
                $this->errno = $oStmt->errno;
                $this->error = $oStmt->error;
                break;
            }

            if (!$oStmt->execute()) {
                $this->errno = $oStmt->errno;
                $this->error = $oStmt->error;
                break;
            }

            $result = new \JsGreenTeaPHPFramework\SqlSession\Result($oStmt, true);
            $oStmt->reset();
        }while(false);
        if(!$result)
            $oStmt->close();

        return $result;
    }

    public function update($tblname, $data, $where)
    {

        $sql = "UPDATE `$tblname` SET ";
        $pidx = 1;
        $params = array("");
        $tmp = false;
        foreach($data as $key => $value)
        {
            if($tmp)
                $sql .= ", ";
            else
                $tmp = true;
            $sql .= "`$key`=?";
            $params[$pidx++] = &$data[$key];

            if(is_integer($value) || is_long($value))
                $params[0] .= 'i';
            else if(is_float($value) || is_double($value))
                $params[0] .= 'd';
            else
                $params[0] .= 's';
        }
        if($where)
        {
            $sql .= " WHERE ";
            $tmp = false;
            foreach($where as $key => $value)
            {
                if ($tmp)
                    $sql .= " AND ";
                else
                    $tmp = true;
                $sql .= "'$key'=?";
                $params[$pidx++] = &$where[$key];

                if(is_integer($value) || is_long($value))
                    $params[0] .= 'i';
                else if(is_float($value) || is_double($value))
                    $params[0] .= 'd';
                else
                    $params[0] .= 's';
            }
        }

        $oStmt = $this->prepare($sql);
        if(!$oStmt) {
            $this->errno = $this->m_dbconn->errno;
            $this->error = $this->m_dbconn->error;
            return false;
        }

        $result = false;
        do {
            if (!call_user_func_array(array($oStmt, "bind_param"), $params)) {
                $this->errno = $oStmt->errno;
                $this->error = $oStmt->error;
                break;
            }

            if (!$oStmt->execute()) {
                $this->errno = $oStmt->errno;
                $this->error = $oStmt->error;
                break;
            }

            $result = new \JsGreenTeaPHPFramework\SqlSession\Result($oStmt, true);
            $oStmt->reset();
        }while(false);
        if(!$result)
            $oStmt->close();

        return $result;
    }
};

namespace JsGreenTeaPHPFramework\SqlSession;

class Statment
{
    private $m_session;
    public $m_stmt;
    private $m_sql;

    private $m_fieldList = NULL;

    private $m_autofreeresult = false;

    public $affected_rows = -1;
    public $errno = 0;
    public $error_list = NULL;
    public $error = NULL;
    public $field_count = 0;
    public $insert_id = -1;
    public $num_rows = 0;
    public $param_count = 0;
    public $sqlstate = NULL;

    public function __construct(&$session, $nativestmt, $sql)
    {
        $this->m_session = $session;
        $this->m_stmt = $nativestmt;
        $this->m_sql = $sql;
    }

    function __destruct()
    {
        $this->close();
    }

    public function getNativeStmt()
    {
        return $this->m_stmt;
    }

    public function close()
    {
        if($this->m_stmt) {
            if($this->m_autofreeresult)
            {
                $this->m_stmt->free_result();
                $this->m_autofreeresult = false;
            }
            $this->m_stmt->close();
            $this->m_stmt = NULL;
        }
    }

    private function _copyStates()
    {
        $this->affected_rows = $this->m_stmt->affected_rows;
        $this->errno = $this->m_stmt->errno;
        $this->error_list = $this->m_stmt->error_list;
        $this->error = $this->m_stmt->error;
        $this->field_count = $this->m_stmt->field_count;
        $this->insert_id = $this->m_stmt->insert_id;
        $this->num_rows = $this->m_stmt->num_rows;
        $this->param_count = $this->m_stmt->param_count;
        $this->sqlstate = $this->m_stmt->sqlstate;
    }

    public function bind_param($types, &...$parameters)
    {
        $result = $this->m_stmt->bind_param($types, ...$parameters);
        $this->_copyStates();
        return $result;
    }

    public function bind_result(&...$parameters)
    {
        $result = $this->m_stmt->bind_result(...$parameters);
        $this->_copyStates();
        return $result;
    }

    public function reset()
    {
        $result = $this->m_stmt->reset();
        $this->_copyStates();
        return $result;
    }

    public function execute()
    {
        $result = $this->m_stmt->execute();
        $this->_copyStates();
        return $result;
    }

    public function fetch()
    {
        $result = $this->m_stmt->fetch();
        $this->_copyStates();
        return $result;
    }

    public function store_result()
    {
        $result = $this->m_stmt->store_result();
        $this->_copyStates();
        $this->m_autofreeresult = true;
        return $result;
    }

    public function free_result()
    {
        $result = $this->m_stmt->free_result();
        $this->m_autofreeresult = false;
        return $result;
    }

    public function get_result()
    {
        $oRes = new \JsGreenTeaPHPFramework\SqlSession\Result($this, false);
        $oRes->_setFieldList($this->m_fieldList);
        return $oRes;
    }
};

class Result
{
    private $m_oStmt;
    private $m_nativestmt;
    private $m_autodestructstmt;
    private $m_numOfCols;
    private $m_fieldlist = NULL;
    private $m_bindresults = NULL;

    public $affected_rows = -1;
    public $errno = 0;
    public $error_list = NULL;
    public $error = NULL;
    public $insert_id = -1;

    public $num_rows = 0;

    function __construct($oStmt, $autodestructstmt = true)
    {
        $this->m_oStmt = $oStmt;
        $this->m_nativestmt = $oStmt->getNativeStmt();
        $this->m_autodestructstmt = $autodestructstmt;

        $this->affected_rows = $this->m_oStmt->affected_rows;
        $this->errno = $this->m_oStmt->errno;
        $this->error_list = $this->m_oStmt->error_list;
        $this->error = $this->m_oStmt->error;
        $this->insert_id = $this->m_oStmt->insert_id;
        $this->m_numOfCols = $this->m_oStmt->field_count;
        $this->num_rows = $this->m_oStmt->num_rows;
    }

    function __destruct()
    {
        $this->close();
    }

    public function _setFieldList(&$fieldlist)
    {
        $this->m_fieldlist = &$fieldlist;
    }

    public function close()
    {
        if($this->m_oStmt && $this->m_autodestructstmt)
        {
            $this->m_oStmt->close();
            $this->m_oStmt = NULL;
            $this->m_nativestmt = NULL;
        }
    }

    public function fetch_array($flags = \JsGreenTeaPHPFramework\core\SqlSession::FLAG_ASSOC)
    {
        $row = array();
        $variables = array();

        $this->errno = 0;

        if(!$this->m_fieldlist) {
            $this->m_fieldlist = array();
            $meta = $this->m_nativestmt->result_metadata();
            if (!$meta) {
                $this->errno = $this->m_nativestmt->errno;
                $this->error = $this->m_nativestmt->error;
                return false;
            }
            while ($field = $meta->fetch_field()) {
                $this->m_fieldlist[] = $field;
            }
            $meta->close();
        }

        $row = array();
        foreach($this->m_fieldlist as $field)
        {
            $variables[] = $field;
        }

        $params = array();
        for($i=0; $i<$this->m_numOfCols; $i++)
        {
            if($flags == \JsGreenTeaPHPFramework\core\SqlSession::FLAG_ASSOC) {
                $row[$variables[$i]->name] = NULL;
                $params[] = &$row[$variables[$i]->name];
            }else{
                $row[$i] = NULL;
                $params[] = &$row[$i];
            }
        }

        if(!$this->m_nativestmt->bind_result(...$params))
        {
            $this->errno = $this->m_nativestmt->errno;
            $this->error = $this->m_nativestmt->error;
            return false;
        }

        // This should advance the "$stmt" cursor.
        if (!$this->m_nativestmt->fetch()) {
            $this->errno = $this->m_nativestmt->errno;
            $this->error = $this->m_nativestmt->error;
            return NULL;
        };

        if(($flags != \JsGreenTeaPHPFramework\core\SqlSession::FLAG_ASSOC) && ($flags & \JsGreenTeaPHPFramework\core\SqlSession::FLAG_ASSOC)) {
            for ($i = 0; $i < $this->m_numOfCols; $i++) {
                $row[$variables[$i]->name] = &$row[$i];
            }
        }

        // Return the array we built.
        return $row;
    }

};

