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

    public function &getConnection()
    {
        return $this->m_dbconn;
    }

    public function connect($hostname, $username, $passwd, $dbname = NULL, $port = 3306, $socket = NULL)
    {
        $this->m_dbconn = new \mysqli($hostname, $username, $passwd, $dbname, $port, $socket);
        $this->connect_errno = $this->m_dbconn->connect_errno;
        $this->connect_error = $this->m_dbconn->connect_error;
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

    // return Result class
    public function queryRaw($sql, $parameters = NULL, $isWriter = false)
    {
        if($parameters)
        {
            $stmt = $this->m_dbconn->prepare($sql);
            if(!$stmt)
            {
                $this->errno = $this->m_dbconn->errno;
                $this->error = $this->m_dbconn->error;
                return false;
            }

            $bind_param_args = array();
            $param_types = '';
            for($i=0; $i<count($parameters); $i++) {
                $strtype = strtolower(gettype($parameters[$i]));
                switch($strtype)
                {
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
            for($i=0; $i<count($parameters); $i++) {
                $bind_param_args[] = &$parameters[$i];
            }
            call_user_func_array(array($stmt, 'bind_param'), $bind_param_args);

            if(!$stmt->execute())
            {
                $this->errno = $this->m_dbconn->errno;
                $this->error = $this->m_dbconn->error;
                return false;
            }

            return new \JsGreenTeaPHPFramework\SqlSession\Result($stmt);
        }else{
            $res = $this->m_dbconn->query($sql);
            return $res;
        }
    }
    // $dbres = queryRaw(sql);
    // $dbres->fetch_array();

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
};

namespace JsGreenTeaPHPFramework\SqlSession;

class Statment
{
    private $m_session;
    private $m_stmt;
    private $m_sql;

    public $affected_rows = -1;
    public $errno = 0;
    public $error_list = NULL;
    public $error = NULL;
    public $field_count = 0;
    public $insert_id = -1;
    public $num_rows = 0;
    public $param_count = 0;
    public $sqlstate = NULL;

    public function __construct(&$session, &$nativestmt, $sql)
    {
        $this->m_session = $session;
        $this->m_stmt = $nativestmt;
        $this->m_sql = $sql;
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

    public function bind_param($types, ...$parameters)
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

    public function get_result()
    {
        return new \JsGreenTeaPHPFramework\SqlSession\Result($this->m_stmt);
    }
};

class Result
{
    private $m_stmt;
    private $m_numOfCols;

    public $affected_rows = -1;
    public $errno = 0;
    public $error_list = NULL;
    public $error = NULL;
    public $insert_id = -1;

    public function __construct($stmt)
    {
        $this->m_stmt = $stmt;

        $this->affected_rows = $stmt->affected_rows;
        $this->errno = $stmt->errno;
        $this->error_list = $stmt->error_list;
        $this->error = $stmt->error;
        $this->insert_id = $stmt->insert_id;
        $this->m_numOfCols = $stmt->field_count;
    }

    public function close()
    {
        $this->m_stmt->reset();
        $this->m_stmt->close();
    }

    public function fetch_array($flags = \JsGreenTeaPHPFramework\core\SqlSession::FLAG_ASSOC)
    {
        $row = array();
        $params = array();
        $variables = array();

        $meta = $this->m_stmt->result_metadata();
        while($field = $meta->fetch_field())
        {
            $variables[] = $field;
        }

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

        $this->m_stmt->bind_result(...$params);

        // This should advance the "$stmt" cursor.
        if (!$this->m_stmt->fetch()) { return NULL; };

        if(($flags != \JsGreenTeaPHPFramework\core\SqlSession::FLAG_ASSOC) && ($flags & \JsGreenTeaPHPFramework\core\SqlSession::FLAG_ASSOC)) {
            for ($i = 0; $i < $this->m_numOfCols; $i++) {
                $row[$variables[$i]->name] = &$row[$i];
            }
        }

        // Return the array we built.
        return $row;
    }

};

