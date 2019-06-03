<?php
  require_once(dirname(dirname(__FILE__)).'/init.php');

  class Grab extends Init{
    private $_headers =  "";
    private $_pid =  "";
    public function __construct(){
      parent::__construct();
    }
    
    // http://www.stats.gov.cn/tjsj/tjbz/tjyqhdmhcxhfdm/2018/index.html
    public function province(){ 
      $url = 'http://www.stats.gov.cn/tjsj/tjbz/tjyqhdmhcxhfdm/2018/index.html';

      $rs = $this->_curl->get($url, true);
      if($rs['http_code'] != '200'){
        echo date('Y-m-d H:i:s')."-> http_code:{$rs['http_code']}, error_no:{$rs['error_no']}, error_msg:{$rs['error_msg']}".PHP_EOL;
        $this->province($url);
        return;
      }
      $htmlsource = mb_convert_encoding($rs['data'], "utf-8", "gb2312");
      echo '<pre>';
      //省份
      preg_match_all('/<tr class=\'provincetr\'>([\s|\S]+?)<\/tr>/i', $htmlsource, $sarr);
      $province = array();
      foreach ($sarr['1'] as $val) {
          $val = str_replace("<td>", "", $val);
          $val = str_replace("</td>", "", $val);
          $val = str_replace("<br/>", "", $val);
          preg_match_all('/<a href=\'([\s|\S]+?)\'>([\s|\S]+?)<\/a>/i', $val, $arr);
          foreach ($arr['1'] as $key => $ckey) {
            $val = str_replace(".html", "", $val);
            $province[] = array(
              'code' => str_replace(".html", "", $ckey),
              'url' => substr($url, 0, strripos($url, "/"))."/".$ckey,
              'name' => $arr['2'][$key],
            );
          }
      }

      // $pid = 0;
      // $argv = isset($_SERVER['argv']) && isset($_SERVER['argv']['1']) ? $_SERVER['argv'] : array();
      // if($argv){
      //   $pid = $argv['1'];
      // }

      // if(!$pid){
        // echo 'no pid';
        // exit;
      // }
      // $this->_pid = $pid;
      // echo $pid;exit;
      // http://www.stats.gov.cn/tjsj/tjbz/tjyqhdmhcxhfdm/2018/
      foreach ($province as $key=>$arr) {
        // if($pid && $arr['code'] == $pid){
        //   $province[$key]['city'] = $this->city($arr['url']);
        // }

        $province[$key]['city'] = $this->city($arr['url']);
        $province_str = $arr['code']."#".$arr['name'].PHP_EOL;
        file_put_contents($this->_dbdir."province.txt", $province_str, FILE_APPEND);
        // sleep(5);
      }
    }

    //市区
    protected function city($url = ""){ 
      $rs = $this->_curl->get($url, true);
      if($rs['http_code'] != '200'){
        echo date('Y-m-d H:i:s')."-> http_code:{$rs['http_code']}, error_no:{$rs['error_no']}, error_msg:{$rs['error_msg']}".PHP_EOL;
        $this->city($url);
        return;
      }
      $htmlsource = mb_convert_encoding($rs['data'], "utf-8", "gb2312");
      //省份
      preg_match_all('/<tr class=\'citytr\'>([\s|\S]+?)<\/tr>/i', $htmlsource, $sarr);
      // print_r($sarr);exit;
      $city = array();
      foreach ($sarr['1'] as $val) {
          $val = str_replace("<br/>", "", $val);
          $val = str_replace("<td>", "", $val);
          $val = str_replace("</td>", "", $val);
          preg_match_all('/<a href=\'([\s|\S]+?)\'>([\s|\S]+?)<\/a>/i', $val, $arr);
          // print_r($arr);exit;
          $city[] = array(
            'code' => $arr['2']['0'],
            'url' => substr($url, 0, strripos($url, "/"))."/".$arr['1']['0'],
            'name' => $arr['2']['1'],
          );
      }
      foreach ($city as $key=>$arr) {
        $city_str = $arr['code']."#".$arr['name'].PHP_EOL;
        file_put_contents($this->_dbdir."city.txt", $city_str, FILE_APPEND);
        $city[$key]['county'] = $this->county($arr['url']);
        // sleep(1);
      }
      return $city;
    }

    //区
    protected function county($url = ""){ 
      $rs = $this->_curl->get($url, true);
      if($rs['http_code'] != '200'){
        echo date('Y-m-d H:i:s')."-> http_code:{$rs['http_code']}, error_no:{$rs['error_no']}, error_msg:{$rs['error_msg']}".PHP_EOL;
        $this->county($url);
        return;
      }
      $htmlsource = mb_convert_encoding($rs['data'], "utf-8", "gb2312");
      //省份
      preg_match_all('/<tr class=\'countytr\'>([\s|\S]+?)<\/tr>/i', $htmlsource, $sarr);
      // print_r($sarr);exit;
      $county = array();
      foreach ($sarr['1'] as $val) {
          if(strpos($val, 'href') !== false){
            $val = str_replace("<br/>", "", $val);
            $val = str_replace("<td>", "", $val);
            $val = str_replace("</td>", "", $val);
            preg_match_all('/<a href=\'([\s|\S]+?)\'>([\s|\S]+?)<\/a>/i', $val, $arr);
            $county[] = array(
              'code' => $arr['2']['0'],
              'url' => substr($url, 0, strripos($url, "/"))."/".$arr['1']['0'],
              'name' => $arr['2']['1'],
            );
          }else{
            preg_match_all('/<td>([\s|\S]+?)<\/td>/i', $val, $arr);
            $county[] = array(
              'code' => $arr['1']['0'],
              'url' => '',
              'name' => $arr['1']['1'],
            );
          }
      }
      // print_r($county);exit;
      foreach ($county as $key=>$arr) {
        $county_str = $arr['code']."#".$arr['name'].PHP_EOL;
        file_put_contents($this->_dbdir."county.txt", $county_str, FILE_APPEND);
        if($arr['url']){
          $county[$key]['town'] = $this->town($arr['url']);
          // sleep(1);
        }
      }
      return $county;
    }

    //街道
    protected function town($url = ""){ 
      $rs = $this->_curl->get($url, true);
      if($rs['http_code'] != '200'){
        echo date('Y-m-d H:i:s')."-> http_code:{$rs['http_code']}, error_no:{$rs['error_no']}, error_msg:{$rs['error_msg']}".PHP_EOL;
        $this->town($url);
        return;
      }
      $htmlsource = mb_convert_encoding($rs['data'], "utf-8", "gb2312");
      //省份
      preg_match_all('/<tr class=\'towntr\'>([\s|\S]+?)<\/tr>/i', $htmlsource, $sarr);
      // print_r($sarr);exit;
      $town = array();
      foreach ($sarr['1'] as $val) {
          if(strpos($val, 'href') !== false){
            $val = str_replace("<br/>", "", $val);
            $val = str_replace("<td>", "", $val);
            $val = str_replace("</td>", "", $val);
            preg_match_all('/<a href=\'([\s|\S]+?)\'>([\s|\S]+?)<\/a>/i', $val, $arr);
            $town[] = array(
              'code' => $arr['2']['0'],
              'url' => substr($url, 0, strripos($url, "/"))."/".$arr['1']['0'],
              'name' => $arr['2']['1'],
            );
          }else{
            preg_match_all('/<td>([\s|\S]+?)<\/td>/i', $val, $arr);
            $town[] = array(
              'code' => $arr['1']['0'],
              'url' => '',
              'name' => $arr['1']['1'],
            );
          }
      }
      foreach ($town as $key=>$arr) {
        $town_str = $arr['code']."#".$arr['name'].PHP_EOL;
        file_put_contents($this->_dbdir."town.txt", $town_str, FILE_APPEND);
        if($arr['url']){
          $town[$key]['village'] = $this->village($arr['url']);
          // sleep(1);
        }
      }
      return $town;
    }

    //社区
    protected function village($url = ""){ 
      $rs = $this->_curl->get($url, true);
      if($rs['http_code'] != '200'){
        echo date('Y-m-d H:i:s')."-> http_code:{$rs['http_code']}, error_no:{$rs['error_no']}, error_msg:{$rs['error_msg']}".PHP_EOL;
        $this->village($url);
        return;
      }
      $htmlsource = mb_convert_encoding($rs['data'], "utf-8", "gb2312");
      //省份
      preg_match_all('/<tr class=\'villagetr\'>([\s|\S]+?)<\/tr>/i', $htmlsource, $sarr);
      $village = array();
      foreach ($sarr['1'] as $val) {
          preg_match_all('/<td>([\s|\S]+?)<\/td>/i', $val, $arr);
          $village[] = array(
            'code' => $arr['1']['0'],
            'url' => $arr['1']['1'],
            'name' => $arr['1']['2'],
          );
          echo date("Y-m-d H:i:s").'-->'.$url.',code=>'.$arr['1']['0'].',url=>'.$arr['1']['1'].',name=>'.$arr['1']['2'].PHP_EOL;
          $village_str = $arr['1']['0']."#".$arr['1']['2'].PHP_EOL;
            file_put_contents($this->_dbdir."village".$this->_pid.".txt", $village_str, FILE_APPEND);
      }
      return $village;
    }
  }

  //业务调度开始
  $grab = new Grab();
  $grab->province();
