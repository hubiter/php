<?php
  require_once(dirname(dirname(__FILE__)).'/init.php');

  //日志格式  name#=#icon#=#author#=#source_url#=#keywords#=#visits#=#ctime#=#content....##=##两个换行
  //系统处理类
  class Sys extends Init{
    private $_black_list =  array();
    public function __construct(){
      parent::__construct();
      $this->_black_list = array(
      );
    }
    
    //处理标签
    public function upRegion(){
      $sql = "select region_id as c_id, code from jia_region where parent_id IS NULL;";
      $rs = $this->_pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
      $tags = array();
      foreach ($rs as $val) {
        // 654003000000
        // $p_code = substr($val['code'], 0, 2);
        $p_code = str_pad(substr($val['code'], 0, 6), 12, '0', STR_PAD_RIGHT);
        $region_id = $val['c_id']; 
        $sql = "select region_id, name, code from jia_region where code = '".$p_code."';";
        echo $sql.PHP_EOL;
        $rs = $this->_pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
        $parent_id = $rs['region_id'];
        $sql = "update jia_region set parent_id = '".$parent_id."' where region_id = '".$region_id."';";
        echo $sql.PHP_EOL.PHP_EOL;
        // $this->_pdo->query($sql);
        exit;
      }
    }

    public function syncFile(){
      $filename = $this->_dbdir."town.txt";
      $fp = fopen($filename, "r");
      $province = array();
      $tmp = array();
      while (!feof($fp)) {
        $line = @fgets($fp);
        $line = trim($line);
        if($line){
          $tmp[] = $line;
        }
      }
      $tmp = array_unique($tmp);
      foreach ($tmp as $val) {
        $arr = explode("#", $val);

        $code = $arr['0'];
        $name = $arr['1'];

        $sql = "INSERT INTO jia_region(`code`, `name`) values('".$code."', '".$name."');";
        echo $sql.PHP_EOL;
        $this->_pdo->query($sql);
      }
      // print_r($province);exit;
    }
  }

  //业务调度开始
  $sys = new Sys();
  $argv = isset($argv) && isset($argv['1']) ? $argv : array();
  if(!$argv){
    echo 'empty method';exit;
  }

  $method = $argv['1'];
  switch ($method) {
    case 'syncFile': $sys->syncFile();break;
    case 'upRegion': $sys->upRegion();break;
  }

