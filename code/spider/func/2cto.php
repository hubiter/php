<?php
  require_once('../init.php');
  //日志格式  name#=#icon#=#author#=#source_url#=#keywords#=#visits#=#ctime#=#content....##=##两个换行

  //www.2cto.com的作者
  class Grab extends Init{
    private $_max_page = 100;

    public function __construct(){
      parent::__construct();
    }
    
    //文章列表
    public function articleQuene(){ 
      $care_cate = array(
        'https://zz.2cto.com/seo/',
        'https://zz.2cto.com/jingyan/',
        'https://www.2cto.com/zz/wangzhuan/',

        'https://www.2cto.com/news/it/',
        'https://www.2cto.com/news/safe/',
        'https://www.2cto.com/news/gonggao/',
        'https://www.2cto.com/news/yujing/',
        'https://www.2cto.com/news/renwu/',
        'https://www.2cto.com/news/qita/',
        
        'https://www.2cto.com/article/xitong/',
        'https://www.2cto.com/article/web/',
        'https://www.2cto.com/article/qiye/',
        'https://www.2cto.com/article/net/',
        'https://www.2cto.com/article/tool/',
        'https://www.2cto.com/article/shadu/',
        'https://www.2cto.com/article/jiami/',

        'https://www.2cto.com/database/mysql/news/',
        'https://www.2cto.com/database/mssql/',
        'https://www.2cto.com/database/Oracle/',
        'https://www.2cto.com/database/DB2/',
        'https://www.2cto.com/database/Sybase/',
        'https://www.2cto.com/database/qita/',

        'https://www.2cto.com/os/windows/',
        'https://www.2cto.com/os/linux/',
        'https://www.2cto.com/os/dos/',
        'https://www.2cto.com/os/liulanqi/',
        'https://www.2cto.com/os/xuniji/',
        'https://www.2cto.com/os/qita/',

        'https://www.2cto.com/net/Router/',
        'https://www.2cto.com/net/Switch/',
        'https://www.2cto.com/net/net/',
        'https://www.2cto.com/net/yinan/',
        'https://www.2cto.com/net/qita/',
        'https://www.2cto.com/net/cloud/',

        'https://www.2cto.com/kf/qianduan/css/',
        'https://www.2cto.com/kf/qianduan/JS/news/',
        'https://www.2cto.com/kf/qianduan/html5/news/',
        'https://www.2cto.com/kf/ware/c/',
        'https://www.2cto.com/kf/ware/cpp/',
        'https://www.2cto.com/kf/ware/cs/',
        'https://www.2cto.com/kf/ware/cs/',
        'https://www.2cto.com/kf/web/asp/',
        'https://www.2cto.com/kf/web/jsp/',
        'https://www.2cto.com/kf/web/Python/news/',
        'https://www.2cto.com/kf/web/qita/',
        'https://www.2cto.com/kf/web/php/news/',
        'https://www.2cto.com/kf/yidong/iphone/',
        'https://www.2cto.com/kf/yidong/Android/news/',
        'https://www.2cto.com/kf/yidong/wp/',
        'https://www.2cto.com/kf/all/safe/',
        'https://www.2cto.com/kf/all/qita/',
      );

      foreach ($care_cate as $cate_url) {
        $page = 1;
        if(!$this->_max_page){
          $url = rtrim($cate_url, "/")."/".$page.".html";
          $htmlsource = $this->_curlGet($url);
          $htmlsource = mb_convert_encoding($htmlsource, 'UTF-8', 'gb2312');
          if(strpos($htmlsource, 'class="a1"') != false){
            preg_match('/<a class="a1">([\s|\S]+?)<\/a>/i', $htmlsource, $arr);
            if(isset($arr['1'])){
              $this->_max_page = str_replace("条", "", $arr['1']);
            }else{
              $this->_max_page = 1;
            }
          }
        }

        while ($page < $this->_max_page) {
          $url = rtrim($cate_url, "/")."/".$page.".html";
          echo date('Y-m-d H:i:s')."->{$url}".PHP_EOL;
          $htmlsource = $this->_curlGet($url);
          $htmlsource = mb_convert_encoding($htmlsource, 'UTF-8', 'gb2312');
          if(strpos($htmlsource, 'class="title"') != false){
            preg_match_all('/<a class=\"title\" target=\"_blank\" href=\"([\s|\S]+?)\">/i', $htmlsource, $arr);
            if(isset($arr['1'])){
              foreach ($arr['1'] as $source_url) {
                $source_url = trim($source_url);
                if(!$this->_ssdb->zget('hubphp_article_url_list', md5($source_url))){
                  $this->_ssdb->qpush_front('hubphp_article_2cto_article_quene', $source_url);
                }
              }
            }
          }
          $page++;
          usleep(100000);
        }
      }
    }

    //作者列表及详情，判断文章是否存在；
    public function articleInfo(){
      while (1) {
        $source_url = $this->_ssdb->qpop_back('hubphp_article_2cto_article_quene');
        if(!$source_url){
          echo date('Y-m-d H:i:s')."->article waiting..".PHP_EOL;
          sleep(5);
        }else{
          //判断时候已经抓取
          if(!$this->_ssdb->zget('hubphp_article_url_list', md5($source_url))){
            $info = $this->_articleInfo($source_url);
            $info['source_url'] = $source_url;
            // print_r($info);
            // exit;
            if($info  && $info['code'] == '200'){
              $str = $info['name']."#=#".$info['icon']."#=#".$info['author']."#=#".$info['source_url']."#=#".$info['keywords']."#=#".$info['visits']."#=#".$info['ctime']."#=#".$info['content']."##=##".PHP_EOL.PHP_EOL;
              echo date('Y-m-d H:i:s')."-->".$info['source_url'].", length：".strlen($str).PHP_EOL;
              $this->_ssdb->qpush_front('hubphp_article_quene_log', $str);
              $this->_ssdb->zset('hubphp_article_url_list', md5($source_url), 1);
              // exit;
            }
          }
        }
      }
    }

    //文章详情
    private function _articleInfo($source_url = ""){
      $info = array(
        'code' => '200',
        'name' => '',
        'author' => '',
        'icon' => '',
        'keywords' => '',
        'visits' => 0,
        'ctime' => '',
        'content' => '',
      );
      if(!$source_url){
        $info['code'] = '100';
        return $info;
      }
      $htmlsource = $this->_curlGet($source_url);
      $htmlsource = mb_convert_encoding($htmlsource, 'UTF-8', 'gb2312');
      if(!$htmlsource || strpos($htmlsource, 'class="box_t"') === false){
        $info['code'] = '101';
        return $info;
      }

      $pattern = '/<h1 class="box_t">([\s|\S]+?)<\/h1>/i';
      preg_match($pattern, $htmlsource, $arr);     
      if(!isset($arr['1']) || empty($arr['1'])){
        $info['code'] = '102';
        return $info;
      } 
      $info['name'] = trim($arr['1']);

      if(strpos($htmlsource, 'id="Article"') != false){
        $pattern = '/<div id="Article">([\s|\S]*)<\/div>(?:[\s]*)<\/div>(?:[\s]*)<div id="pages" class="box_body">/i';
        preg_match($pattern, $htmlsource, $arr);
        $info['content'] = addslashes(trim($arr['1']));
      }

      preg_match("/([0-9]{4})-([0-9]{2})-([0-9]{2})(\s)+([0-9]{2}):([0-9]{2}):([0-9]{2})/i", $htmlsource, $arr); 
      $info['ctime'] = trim($arr['0']);


      if(strpos($htmlsource, 'more_tags') != false){
        preg_match('/<div class="more_tags">([\s|\S]+?)<dl class="box_NPre">/i', $htmlsource, $arr);
        $tags = trim($arr['1']);
        preg_match_all('/<a href="(?:[\s|\S]*?)" target="_blank">([\s|\S]+?)<\/a>/i', $tags, $arr);
       
        if(isset($arr['1'])){
          foreach ($arr['1'] as $val) {
            $info['keywords'] .= trim($val).",";
          }
        }

        unset($key_str);
      }

      return $info;
    }
  }

  //业务调度开始
  $grab = new Grab();
  $argv = isset($argv) && isset($argv['1']) ? $argv : array();
  if(!$argv){
    echo 'empty method';exit;
  }

  $method = $argv['1'];
  switch ($method) {
    case 'articleQuene': $grab->articleQuene();break;
    case 'articleInfo': $grab->articleInfo();break;
  }
