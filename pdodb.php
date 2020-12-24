<?php
ini_set('date.timezone','Asia/Taipei') ;

/*使用方式
$db = new ProductDB;

$db->beginTransaction();

$sql = "select `id` from `student` where `user_id`=:user_id";
$rs = $db->all($sql, ['user_id' => $user_id]);

if (!$rs) {
	$sql = "insert into `student`(`user_id`) values (:user_id);";
	$db->exeSql($sql, ['user_id' => $user_id]);
}

$db->endTransaction();

//讀取資料
foreach ($rs as $index => $value) {
	$data[$i]['prizes_id'] = $value['prizes_id'];
	$data[$i]['pic_labar'] = $value['pic_labar'];
	$i++;
}


*/

class ProductDB extends Database {
    protected $host     = 'host' ;
    protected $user     = 'user' ;
    protected $pass     = 'pass' ;
    protected $port     = 'port' ;

    protected $dbname   = 'accunix_v2' ;
}

/*******類別定義*****/
class Database{
    private $dbh ;
    private $error ;

    private $stmt ;

    public function __construct($str='set names utf8mb4') {
        // 設定 DSN
        $dsn = 'mysql:host='.$this->host.';port='.$this->port.';dbname='.$this->dbname ;
        ##

        // 設定 options 遇到big5時 初始化物件加上big5字串
        if($str=='big5') $options = array() ;
        else {
            $options = array(
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_INIT_COMMAND => $str,
                //PDO::MYSQL_ATTR_INIT_COMMAND=>'SET CHARACTER SET utf8',
                PDO::ATTR_EMULATE_PREPARES=>true
            ) ;
        }
        ##

        // 新增 PDO instanace
        try
        {
            //$a=$dsn.",". $this->user.",". $this->pass.",". $options;
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options) ;

        }
        catch(PDOException $e)
        {
            $this->error = $e->getMessage() ;
        }
        ##
    }

    //bindValue
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT ;
                    break ;

                case is_bool($value):
                    $type = PDO::PARAM_BOOL ;
                    break ;

                case is_null($value):
                    $type = PDO::PARAM_NULL ;
                    break ;

                default:
                    $type = PDO::PARAM_STR ;
                    break ;
            }
        }

        $this->stmt->bindValue($param, $value, $type) ;
    }

    //prepare
    public function query($query) {
        $this->stmt = $this->dbh->prepare($query) ;
    }
    ##

    //execute
    public function go($data=array()) {
        if (empty($data)) return $this->stmt->execute() ;
        else {
            foreach ($data as $param => $value) {
                // $this->bind($param, $value) ;
                $this->bind($param, $value, PDO::PARAM_STR) ;
            }
            return $this->stmt->execute($data) ;
        }
    }
    ##

    //prepare + execute
    public function getPrepare($query, $data=array()) {
        $this->query($query) ;
        return $this->go($data) ;
    }
    ##

    //prepare + execute
    public function exeSql($query, $data=array()) {
        return $this->getPrepare($query, $data) ;
    }
    ##

    //使用fetchALL時
    public function all($query, $data=array()) {
        $this->getPrepare($query, $data) ;
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC) ;
    }
    ##

    //使用fetch時
    public function one($query, $data=array()) {
        $this->getPrepare($query, $data) ;
        return $this->stmt->fetch(PDO::FETCH_ASSOC) ;
    }
    ##

    //傳回被影響的行數
    public function rowCount() {
        return $this->stmt->rowCount() ;
    }
    ##

    //返回最後插入資料的id
    public function lastInsertId() {
        return $this->dbh->lastInsertId() ;
    }
    ##

    //To begin a transaction:
    public function beginTransaction() {
        return $this->dbh->beginTransaction() ;
    }
    ##

    //To end a transaction and commit your changes:
    public function endTransaction() {
        return $this->dbh->commit() ;
    }
    ##

    //To cancel a transaction and roll back your changes:
    public function cancelTransaction()	{
        return $this->dbh->rollBack() ;
    }
    ##

    //印出執行的 sql 語法
    public function debugDump() {
        return $this->stmt->debugDumpParams() ;
    }
    ##

    public function pdo_debugStrParams() {
        ob_start();
        $this->stmt->debugDumpParams();
        $r = str_replace(array("\r", "\n", "\r\n", "\n\r"), '', ob_get_contents());
        ob_end_clean();
        return $r;
    }

}
##
?>