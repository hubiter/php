<?php
  header("Content-type: text/html; charset=utf-8");
  date_default_timezone_set("Asia/Shanghai");
  ini_set('memory_limit', '4096M');
  set_time_limit(0);

  error_reporting(E_ALL);
  ini_set('display_errors', 1);
 
  class Init{
    protected $_snoopy = "";
    protected $_htmldom = "";
    protected $_logdir = '../log/hubphp/logs/';
    protected $_dbdir = '../log/hubphp/data/';
    protected $_pdo = "";
    protected $_ssdb = "";
    protected $_multData = array();
    private $_config = array(
      'mysql' => array(
        'host' => '127.0.0.1',
        'port' => '3306',
        'username' => '',
        'passwd' => '',
        'db' => '',
      ),

      'ssdb' => array(
        'host' => '127.0.0.1',
        'port' => '',
        'password' => '',
        'timeout_ms' => 5000,
      ),

      'redis' => array(
        'host' => '127.0.0.1',
        'port' => '',
        'password' => '',
        'timeout_ms' => 5000,
      ),
    );

    public function __construct(){
      $sapi = strtolower(PHP_SAPI);
      if($sapi != 'cli'){
        echo 'error access';
        exit;
      }
    

      if(!isset($this->_snoopy) || !$this->_snoopy){
        require_once('com/Snoopy.php');
        $this->_snoopy = new Snoopy();
      }

      if(!isset($this->_htmldom) || !$this->_htmldom){
        require_once('com/simple_html_dom.php');
        $this->_htmldom = new simple_html_dom();
      }
      
      //mysql
      try {
          $this->_pdo = new PDO("mysql:host={$this->_config['mysql']['host']};port={$this->_config['mysql']['port']};dbname={$this->_config['mysql']['db']}", $this->_config['mysql']['username'], $this->_config['mysql']['passwd']);
      } catch (PDOException $e) {
          print "DB Error!: " . $e->getMessage() . "<br/>";
          die();
      }

      //ssdb
      try{ 
          include_once('com/SSDB.php');
          $this->_ssdb = new SimpleSSDB($this->_config['ssdb']['host'], $this->_config['ssdb']['port'], $this->_config['ssdb']['timeout_ms']);
          $this->_ssdb->auth($this->_config['ssdb']['password']);
      }catch(Exception $e){
          die(__FILE__.' '. $e->getMessage());
      }

      //redis
      // try{ 
      //     $this->_redis = new Redis();
      //     $this->_redis->connect($this->_config['redis']['host'], $this->_config['redis']['port']);
      //     $this->_redis->auth($this->_config['redis']['password']);
      // }catch(Exception $e){
      //     die(__FILE__.' '. $e->getMessage());
      // }
      // require_once 'com/Curl.php';
      // $this->_curl = new Curl();
    }

    //检查远程文件是否有效
    protected function _checkRemoteFileExists($url = "") {
      if(!$url){
        return "";
      }
      $curl = curl_init($url); // 不取回数据
      curl_setopt($curl, CURLOPT_NOBODY, true);
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET'); // 发送请求
      $result = curl_exec($curl);
      $found = false; // 如果请求没有发送失败
      if ($result !== false) {
          /** 再检查http响应码是否为200 */
          $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
          if ($statusCode == 200) {
              $found = true;
          }
      }
      curl_close($curl);
   
      return $found;
    }

    protected function _curlPost($url = "", $param = ""){
      if (empty($url) || empty($param)) {
          return false;
      }
      
      $postUrl = $url;
      $curlPost = $param;
      $ch = curl_init();//初始化curl
      curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
      curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
      curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
      curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
      $data = curl_exec($ch);//运行curl
      curl_close($ch);
      
      return $data;
    }

    protected function _curlGet($url = ""){
      if(!$url){
        return "";
      }
      $ch = curl_init($url); // 不取回数据
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HEADER, true);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);

      // curl_setopt($curl, CURLOPT_NOBODY, true);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET'); // 发送请求
      $result = curl_exec($ch);
      $httpcode = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
      if ($httpcode != '200') {
          curl_close($ch);
          return $httpcode;
      }else{
          curl_close($ch);
          return ($result);
      }
    }

    protected function microtime_float(){
      list($usec, $sec) = explode(" ", microtime());
      return ((float)$usec + (float)$sec);
    }

    protected function _addData($data = array(), $tablename = "hubphp_article"){
      if(!$data){
        return 'empty';
      }
    
      try{
        $this->_pdo->beginTransaction();
        $sql = "select article_id from hubphp_article where source_url_md5 = '{$data['source_url_md5']}' limit 1";
        $rs = 0;
        // $rs = $this->_pdo->query($sql)->fetch();
        if(!$rs && !$rs['article_id']){
          $sql = "INSERT INTO ".$tablename."(";
          $val_str = "";
          foreach ($data as $col => $val) {
            $sql .= "`".$col."`, ";
            $val_str .= "'".$val."', ";
          }
          $val_str = rtrim($val_str, ", ");
          $sql = rtrim($sql, ', ');
          $sql = $sql.") values(".$val_str.");"; 
          $stmt = $this->_pdo->prepare($sql);
          $stmt->execute();
          $this->_pdo->commit();
          if($tablename == 'hubphp_article'){
            $sql = "select article_id from hubphp_article where source_url_md5 = '{$data['source_url_md5']}' limit 1";
            $rs = $this->_pdo->query($sql)->fetch();
            return $rs['article_id'];
          }else{
            echo $sql;exit;
          }

          return $this->_pdo->lastInsertId();
        }else{
          $this->_pdo->rollBack();
          return "repeat->".$rs['article_id'];
        }
      }catch(Exeption $e){
        var_dump($e->getMessage());
        exit;
      }
    }
}