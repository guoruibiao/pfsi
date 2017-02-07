<?php

/**
 * Created by PhpStorm.
 * User: ${郭璞}
 * Date: 2017/1/21
 * Time: 22:24
 * Description: DB相关
 *              <br>在DbUtils中配置所需数据库连接信息。
 *              <br>然后只需要实例化QueryRunner即可完成自定义的CRUD语句了。
 */

/**
 * Class DbUtils
 * 数据库配置信息必备。只需要修改db_config 数组中的数据即可。
 */
class DbUtils
{
    private $dbConfig;
    private $conn;

    public function getConn()
    {
        return $this->conn;
    }

    public function __construct($encoding = "utf8")
    {
        /**
         * 数据库配置信息
         */
        $db_config = array(
            "host" => "localhost",
            "user" => "root",
            "password" => "mysql",
            "dbname" => "test",
        );

        $this->dbConfig = $db_config;
        $this->conn = new PDO("mysql:host={$this->dbConfig['host']};dbname={$this->dbConfig['dbname']}", $this->dbConfig['user'], $this->dbConfig['password']);
        if ($encoding !== "utf8") {
            $this->conn->query("set names {$encoding}");
        } else {
            $this->conn->query("set names utf8");
        }
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        // 关闭数据库连接对象
        $this->conn = null;
    }
}

/**
 * Class QueryRunner
 * 模拟Apache db-utils实现的数据库常用操作支持类。最简易版本。
 */
class QueryRunner
{
    private $conn;

    public function __construct($encoding = 'utf8')
    {
        $dbutils = new DbUtils($encoding);
        $this->conn = $dbutils->getConn();
    }

    /**
     * 根据SQL语句获取到查询结果，防止SQL注入并处理附加参数。
     * @param $sql
     * @param $params
     * @return array|bool
     */
    public function select($sql = "", $params = array())
    {
        $sqlRunner = $this->prepareAndExecute($sql, $params);
        $resultSet = $sqlRunner->fetchAll(PDO::FETCH_ASSOC);

        if (count($resultSet) == 0 || $resultSet == false) {
            return false;
        } else {
            return $resultSet;
        }

    }

    /**
     * 数据插入，返回值表示是否成功执行。
     * @param $sql
     * @param $params
     * @return bool
     */
    public function insert($sql = "", $params = array())
    {
        $sqlRunner = $this->prepareAndExecute($sql, $params);
        if ($sqlRunner)
            return true;
        else
            return false;
    }

    /**
     * 支持单条记录更新以及批量更新操作。
     * @param $sql
     * @param $params
     * @return bool
     */
    public function update($sql = "", $params = array())
    {
        $sqlRunner = $this->prepareAndExecute($sql, $params);
        if ($sqlRunner)
            return true;
        else
            return false;
    }

    /**
     * 支持单条记录删除以及批量删除操作。
     * @param $sql
     * @param $params
     * @return bool
     */
    public function delete($sql = "", $params = array())
    {
        $sqlRunner = $this->prepareAndExecute($sql, $params);
        if ($sqlRunner)
            return true;
        else
            return false;
    }

    /**
     * 补全SQL语句，并处理附加参数。
     * @param $sql
     * @param $params
     * @return PDOStatement
     */
    public function prepareAndExecute($sql = "", $params = array())
    {
        try{
            $sqlRunner = $this->conn->prepare($sql);
            for ($index = 0; $index < count($params); $index++) {
                $sqlRunner->bindParam($index + 1, $params[$index]);
            }
            $sqlRunner->execute();
            return $sqlRunner;
        }catch (Exception $e){
            throw new RuntimeException($e->getMessage());
        }
    }

    /**
     * 全方位实现所有SQL语句的执行。<br />
     * 自动区分SQL语句CRUD类型，并实现$SQL语句的附加参数处理。
     * @param $sql
     * @param $params
     */
    public function exec($sql = "", $params = array())
    {
        $sqlType = substr($sql, 0, 6);
        $sqlRunner = $this->prepareAndExecute($sql, $params);
        if ($sqlType == "select") {
            $resultSet = $sqlRunner->fetchAll(PDO::FETCH_ASSOC);
            if ($resultSet != false && count($resultSet) != 0) {
                return $resultSet;
            } else {
                return false;
            }
        } else if ($sqlType == 'delete' or $sqlType == 'update' or $sqlType == 'insert') {
            if ($sqlRunner) {
                return true;
            } else {
                return false;
            }
        } else {
            // 实际上，即使是非CURD语句，这里也是能够成功得到执行的，prepareAndExecute方法内部完成了对此的处理。但是为了业务逻辑更加清晰，此处故意以抛出异常的形式处理。
            throw new RuntimeException('您输入的SQL语句不是业务语句！如必须执行，请使用prepareAndExecute()方法代替！');
        }


    }

}
