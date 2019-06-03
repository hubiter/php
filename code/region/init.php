<?php
  header("Content-type: text/html; charset=utf-8");
  date_default_timezone_set("Asia/Shanghai");
  ini_set('memory_limit', '4096M');
  set_time_limit(0);

  error_reporting(E_ALL);
  ini_set('display_errors', 1);
 
  /**
    *@desc 1、抓取：抓取代理IP信息，存入入库队列；包含：ip:port#地址；
    *      2、验证队列中IP是否有效，有效则存入集合，集合结构为ip:port#地址#响应均耗/检测时间
  */
  class Init{
    //数据库+redis+ssdb等配置
    private $_config = array(
      'mysql' => array(
        'host' => '127.0.0.1',
        'port' => '3306',
        'username' => 'root',
        'passwd' => 'root123',
        'db' => 'test',
      ),

      'redis' => array(
        'host' => '127.0.0.1',
        'port' => 6379,
        'password' => '0fe8f44d2871aad026a5619260e05cc0',
        'timeout_ms' => 5000,
      ),
    );

    protected $_logdir = "";
    protected $_dbdir = "";
    protected $_pdo = "";
    // protected $_ssdb = "";
    protected $_redis = "";
    protected $_curl = "";

    public function __construct(){
      $sapi = strtolower(PHP_SAPI);
      if($sapi != 'cli'){
        echo 'error access';
        // exit;
      }
     $this->_logdir = dirname(__FILE__).'/logs/';
     $this->_dbdir = dirname(__FILE__).'/logs/';
      //mysql
      try {
          $this->_pdo = new PDO("mysql:host={$this->_config['mysql']['host']};port={$this->_config['mysql']['port']};dbname={$this->_config['mysql']['db']}", $this->_config['mysql']['username'], $this->_config['mysql']['passwd']);
      } catch (PDOException $e) {
          print "DB Error!: " . $e->getMessage() . "<br/>";
          die();
      }

      //ssdb
      // try{ 
      //     include_once('com/SSDB.php');
      //     $this->_ssdb = new SimpleSSDB($this->_config['ssdb']['host'], $this->_config['ssdb']['port'], $this->_config['ssdb']['timeout_ms']);
      //     $this->_ssdb->auth($this->_config['ssdb']['password']);
      // }catch(Exception $e){
      //     die(__FILE__.' '. $e->getMessage());
      // }

      // redis
      try{ 
          // $this->_redis = new Redis();
          // $this->_redis->connect($this->_config['redis']['host'], $this->_config['redis']['port']);
          // $this->_redis->auth($this->_config['redis']['password']);
      }catch(Exception $e){
          die(__FILE__.' '. $e->getMessage());
      }
      require_once 'com/Curl.php';
      $this->_curl = new Curl();
    }

    //生成微秒时间
    protected function microtime_float(){
      list($usec, $sec) = explode(" ", microtime());
      return ((float)$usec + (float)$sec);
    }
  }