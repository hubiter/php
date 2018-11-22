<?php
  require_once('../init.php');
  //日志格式  name#=#icon#=#author#=#source_url#=#keywords#=#visits#=#ctime#=#content....##=##两个换行

  //www.php.cn的作者
  class Grab extends Init{
    private $_max_page = 1000000;

    public function __construct(){
      parent::__construct();
    }
    
    //作者列表
    public function authorQuene(){ 
      $care_cate = array(
        '/article.html',
        '/toutiao.html', 
        '/php-weizijiaocheng.html',
        '/mysql-tutorials.html',
        '/div-tutorial.html',
        '/css-tutorial.html',
        '/js-tutorial.html',
        '/html5-tutorial.html',
        '/ps-tutorial.html',
        '/weixin-kaifa.html',
        '/xiaochengxu.html',
        '/jishu/php/',
        '/jishu/html/',
        '/jishu/css/',
        '/jishu/mysql/',
        '/phpstudy.html',
        '/phpkj/laravel/',
        '/windows.html',
        '/linux.html',
        '/tool/sublime/',
        '/tool/phpstorm/',
        '/tool/notepad/',
        '/tool/atom/',
        '/tool/webstorm/',
        '/tool/dreamweaver/',
        '/tool/navicat/',
        '/blog.html',
        '/java-article.html',
        '/csharp-article.html',
        '/python-tutorials.html',
        '/xml_rss.html'
      );

      $max_page = 0;
      foreach ($care_cate as $cate) {
        $htmlsource = file_get_contents("http://www.php.cn".$cate);
        if(strpos($htmlsource, 'layui-row diy-page') != false){
          preg_match_all('/<div class=\"layui-row diy-page\">([\s|\S]*?)<\/div>/i', $htmlsource, $arr);
          if(isset($arr['1']['0'])){
            preg_match_all('/<a (?:[\s|\S]+?)>([\s|\S]*?)<\/a>/i', $arr['1']['0'], $arr);
          }
          if(isset($arr['1'])){
            $num = count($arr['1'])-2;
            $max_page = intval($arr['1'][$num]);
          }
        } 
        
        $page = 0;
        while ($page < $max_page) {
          $a_url = "http://www.php.cn".$cate."?p=".$page;
          $htmlsource = $this->_curlGet($a_url);
          if(strpos($htmlsource, 'article-list') != false){
            preg_match_all('/<div class=\"layui-col-md8 article-list-left\">([\s|\S]*?)<div class=\"layui-row diy-page\">/i', $htmlsource, $arr);
            if(isset($arr['1']['0'])){
              preg_match_all('/<li class=\'ar-img\'><a href="([\s|\S]*?)" target="_blank">(?:[\s|\S]+?)<\/a><\/li>/i', $arr['1']['0'], $arr);
              foreach ($arr['1'] as $cate_url) {
                $url = "http://www.php.cn".$cate_url;
                if(!$this->_ssdb->zget('hubphp_article_php_article_list', $url)){
                  $this->_ssdb->qpush_front('hubphp_article_php_article_quene', $url);
                  $this->_ssdb->zset('hubphp_article_php_article_list', $url, 1);
                  echo date('Y-m-d H:i:s')."->".$url.' succ， size：'.$this->_ssdb->zsize('hubphp_article_php_article_list').PHP_EOL;
                }else{
                  echo date('Y-m-d H:i:s')."->".$url.' exist!'.PHP_EOL;
                }
              }
            }
          }
          $page++;
        }
      }
    }

    //作者列表及详情，判断文章是否存在；
    public function authorList(){
      while (1) {
        $url = $this->_ssdb->qpop_back('hubphp_article_php_article_quene');
        if(!$url){
          echo date('Y-m-d H:i:s')."->url waiting..".PHP_EOL;
          sleep(5);
        }else{
          $this->_authorList($url);
        }
      }
    }

    public function _authorList($source_url = "") { 
      if(!$source_url){
        return "";
      }
      if(!$this->_ssdb->zget('hubphp_article_url_list', md5($source_url))){
        $info = $this->_authorInfo($source_url);
        $info['source_url'] = $source_url;
        if($info  && $info['code'] == '200'){
          $str = $info['name']."#=#".$info['icon']."#=#".$info['author']."#=#".$info['source_url']."#=#".$info['keywords']."#=#".$info['visits']."#=#".$info['ctime']."#=#".$info['content']."##=##".PHP_EOL.PHP_EOL;
          echo date('Y-m-d H:i:s')."-->".$info['source_url'].", length：".strlen($str).PHP_EOL;
          $this->_ssdb->qpush_front('hubphp_article_quene_log', $str);
          $this->_ssdb->zset('hubphp_article_url_list', md5($source_url), 1);
        }
      }
    }

    //文章详情
    private function _authorInfo($source_url = ""){
      $info = array(
        'code' => '200',
        'source_url' => $source_url,
        'name' => '',
        'icon' => '',
        'author' => '',
        'keywords' => '',
        'visits' => 0,
        'ctime' => '',
        'content' => '',
      );
      if(!$source_url){
        $info['code'] = '100';
        return $info;
      }
      $htmlsource = file_get_contents($source_url);
      if(!$htmlsource || strpos($htmlsource, 'layui-row php-article') === false){
        $info['code'] = '101';
        return $info;
      }

      $pattern = '/<h1>([\s|\S]+?)<\/h1>/i';
      preg_match($pattern, $htmlsource, $arr);     
      if(!isset($arr['1']) || empty($arr['1'])){
        $info['code'] = '102';
        return $info;
      } 
      $info['name'] = trim($arr['1']);
    
      if(strpos($htmlsource, "class='content'") != false){
        $pattern = '/<div class=\'content\'>([\s|\S]*)<div class=\'share layui-clear bdsharebuttonbox\'>/i';
        preg_match($pattern, $htmlsource, $arr);
        $info['content'] = addslashes(trim($arr['1']));
      }

      if(strpos($htmlsource, 'author-name') != false){
        $pattern = '/<span class="author-name">([\s|\S]*?)<\/span>/i';
        preg_match($pattern, $htmlsource, $arr);     
        $info['author'] = trim($arr['1']);
      }

      if(strpos($htmlsource, 'layui-icon') != false){
        $pattern = '/[\d]{4}-[\d]{1,2}-[\d]{1,2}\s[\d]{1,2}:[\d]{1,2}:[\d]{1,2}/i';
        preg_match($pattern, $htmlsource, $arr); 
        $info['ctime'] = date('Y-m-d H:i:s', strtotime(trim($arr['0'])));
      }

      // https://www.cnblogs.com/mvc/blog/ViewCountCommentCout.aspx?postId=9717903
      // <span class="hot"><i class="layui-icon" title="浏览"></i>225</span>
      if(strpos($htmlsource, "class='hot'") != false){
        preg_match('/<span class=\'hot\'>([\s|\S]*?)<\/span>/i', $htmlsource, $arr);
        if(isset($arr['1'])){
          preg_match('/[\d]+/i', $arr['1'], $arr);
        }
        $info['visits'] = intval(trim($arr['0']));
      } 
      if(strpos($htmlsource, 'tags layui-clear') != false){
        preg_match('/<div class=\'tags layui-clear\'>([\s|\S]+?)<\/div>([\s]*)<div class=\'page layui-clear\'>/i', $htmlsource, $arr);
        if(isset($arr['1'])){
          preg_match_all('/<a href="(?:[\s|\S]*?)" target="_blank">([\s|\S]*?)<\/a>/i', trim($arr['1']), $arr);
          if(isset($arr['1'])){
            $info['keywords'] = implode(",", $arr['1']);
          }
        }
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
    case 'authorQuene': $grab->authorQuene();break;
    case 'authorList': $grab->authorList();break;
  }

