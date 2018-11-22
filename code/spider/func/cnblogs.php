<?php
  require_once('../init.php');
  //日志格式  name#=#icon#=#author#=#source_url#=#keywords#=#visits#=#ctime#=#content....##=##两个换行

  //www.cnblogs.com的作者
  class Grab extends Init{
    private $_quene = "hubphp_article_cnblogs_quene";
    private $_max_page = 1000000;

    public function __construct(){
      parent::__construct();
    }
    
    //作者列表
    public function authorQuene(){ 
      // $this->_addAuthorQuene("tiger-xc");
      $htmlsource = file_get_contents(dirname(__FILE__)."/content.txt");
      preg_match_all('/<a href="([\s|\S]+?)"/i', $htmlsource, $arr);
      
      if(isset($arr['1'])){
        foreach ($arr['1'] as $cate_url) {
          $cate = str_replace("https://www.cnblogs.com/", "", $cate_url);
          $cate = str_replace("/", "", $cate);
          $cate = str_replace("rss.aspx", "", $cate);
          echo $cate.PHP_EOL;
          $this->_addAuthorQuene($cate);
        }
      }

      // exit;
      $care_cate = array(
        'all',
        'aspnet',
        'csharp',
        'aspnet',

        'java',
        'cpp',
        'php',
        'python',
        'ruby',
        'c',
        'go',
        'swift',
        'scala',
        'r',
        'otherlang',

        '108701',
        'design',
        '108702',
        'dp',
        'ddd',

        '108703',
        'web',
        'javascript',
        'jquery',
        'html5',

        '108704',
        '108705',
        'android',
        'ios',
        'wm',
        'mobile',
        '108709',
        'agile',
        'pm',
        'Engineering',
        '108712',
        'sqlserver',
        'oracle',
        'mysql',
        'nosql',
        'bigdata',
        'database',
        '108724',
        'win7',
        'winserver',
        'linux',
        'osx',
        '4',
      );

      foreach ($care_cate as $cate) {
        $htmlsource = file_get_contents("https://www.cnblogs.com/cate/".$cate."/");
        if(strpos($htmlsource, '"ParentCategoryId":') != false){
          preg_match('/"ParentCategoryId":([\S]+),"CategoryId"/i', $htmlsource, $arr);
          $ParentCategoryId = intval(trim($arr['1']));
        } 
        if(strpos($htmlsource, '"CategoryId":') != false){
          preg_match('/"CategoryId":([\S]+),"PageIndex"/i', $htmlsource, $arr);
          $CategoryId = intval(trim($arr['1']));
        }
        if(strpos($htmlsource, '"TotalPostCount":') != false){
          preg_match('/"TotalPostCount":([\S]+),"ItemListActionName"/i', $htmlsource, $arr);
          $TotalPostCount = intval(trim($arr['1']));
        }
        $max_page = ceil($TotalPostCount/20);
        $page = 0;
        while ($page < $max_page) {
          $post_url = "https://www.cnblogs.com/mvc/AggSite/PostList.aspx";
          $param = array(
            'CategoryId' => $CategoryId,
            'ParentCategoryId' => $ParentCategoryId,
            'CategoryType' => 'SiteCategory',
            'ItemListActionName' => 'PostList',
            'PageIndex' => $page,
            'TotalPostCount' => $TotalPostCount,
          );
          $htmldiv = $this->_curlPost($post_url, $param);
          // <a href="https://www.cnblogs.com/huhu1020387597/" class="lightblue">糯米糊糊</a>
          if(strpos($htmldiv, 'lightblue') != false){
            preg_match_all('/<div class="post_item_foot">(?:[\s]*)<a href="([\s|\S]+?)" class="lightblue">(?:[\s|\S]+?)<\/a>/i', $htmldiv, $arr);
            if(isset($arr['1'])){
              foreach ($arr['1'] as $cate_url) {
                $cate = str_replace("https://www.cnblogs.com/", "", $cate_url);
                $cate = str_replace("/", "", $cate);
                $this->_addAuthorQuene($cate);
              }
            }
          }
          $page++;
        }
      }
    }

    private function _addAuthorQuene($author = ""){
      if(!$this->_ssdb->zget('hubphp_article_cnblogs_author_list', $author)){
        $this->_ssdb->qpush_front('hubphp_article_cnblogs_author_quene', $author);
        $this->_ssdb->zset('hubphp_article_cnblogs_author_list', $author, 1);
        echo $author.' succ， size：'.$this->_ssdb->zsize('hubphp_article_cnblogs_author_list').PHP_EOL;
      }else{
        echo $author.' exist!'.PHP_EOL;
      }
    }

    //作者列表及详情，判断文章是否存在；
    public function authorList(){
      while (1) {
        $author = $this->_ssdb->qpop_back('hubphp_article_cnblogs_author_quene');
        if(!$author){
          echo date('Y-m-d H:i:s')."->author waiting..".PHP_EOL;
          sleep(5);
        }else{
          $this->_authorList($author);
        }
      }
    }

    public function _authorList($author = "") { 
      if(!$author){
        return "";
      }
      
      $author_url = "https://www.cnblogs.com/".$author."/default.html?page=";
      $cur_page = 0;
      while($cur_page < $this->_max_page){
        $author_url = $author_url.$cur_page;
        $htmlsource = $this->_curlGet($author_url);
        if(!$htmlsource || strpos($htmlsource, 'class="day"') === false){
          echo date('Y-m-d H:i:s')."-->cnblogs->".$author.", 数据抓取已完成，".PHP_EOL;
          break;
        }
        //抓取主体；
        $pattern = '/<div class="postTitle">([\s\S]*?)<\/div>/i';
        preg_match_all($pattern, $htmlsource, $links);
        if($links && isset($links['1'])){
          foreach ($links['1'] as $link) {
            $pattern = '/<a id="(?:[\s|\S]*)" href="([\s\S]*?)">(?:[\s|\S]*)/i';
            preg_match($pattern, $link, $arr);
            if(isset($arr['1'])){ 
                $source_url = $arr['1'];
                //判断时候已经抓取
                if(!$this->_ssdb->zget('hubphp_article_url_list', md5($source_url))){
                  $info = $this->_authorInfo($source_url);
                  $info['author'] = $author;
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
            usleep(10000);
          }
        }
        $cur_page++;
      }
    }

    //文章详情
    private function _authorInfo($source_url = ""){
      $info = array(
        'code' => '200',
        'name' => '',
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
      $htmlsource = file_get_contents($source_url);
      if(!$htmlsource || strpos($htmlsource, 'class="postTitle2"') === false){
        $info['code'] = '101';
        return $info;
      }

      $pattern = '/<a id="(?:[\s|\S]*?)" class="postTitle2" href="(?:[\s\S]*?)">([\s|\S]+?)<\/a>/i';
      preg_match($pattern, $htmlsource, $arr);     
      if(!isset($arr['1']) || empty($arr['1'])){
        $info['code'] = '102';
        return $info;
      } 
      $info['name'] = trim($arr['1']);
    
      if(strpos($htmlsource, 'id="cnblogs_post_body"') != false){
        $pattern = '/<div id="cnblogs_post_body" class="blogpost-body(?:[\s|\S]*?)">([\s|\S]*)<\/div><div id="MySignature">/i';
        preg_match($pattern, $htmlsource, $arr);
        $info['content'] = addslashes(trim($arr['1']));
      }

      if(strpos($htmlsource, 'id="post-date"') != false){
        $pattern = '/<span id="post-date">([\s|\S]+?)<\/span>/i';
        preg_match($pattern, $htmlsource, $arr);     
        $info['ctime'] = date('Y-m-d H:i:s', strtotime(trim($arr['1'])));
      }

      // https://www.cnblogs.com/mvc/blog/ViewCountCommentCout.aspx?postId=9717903
      // cb_blogId=217117,cb_entryId=9717903

      if(strpos($htmlsource, 'cb_entryId=') != false){
        preg_match('/,cb_entryId=([\d]+),/i', $htmlsource, $arr);
        $posId = intval(trim($arr['1']));
        $info['visits'] = intval(trim(file_get_contents("https://www.cnblogs.com/mvc/blog/ViewCountCommentCout.aspx?postId=".$posId)));
      } 

      if(isset($posId) && strpos($htmlsource, 'cb_blogId=') != false && strpos($htmlsource, 'currentBlogApp = ') != false){
        preg_match('/,cb_blogId=([\d]+),/i', $htmlsource, $arr);
        $blogId = intval(trim($arr['1']));

        preg_match('/currentBlogApp(?:[\s]+?)=([\s|\S]+?),/i', $htmlsource, $arr);
        $author = trim($arr['1']);
        
        $key_str = "";
        $json = file_get_contents("https://www.cnblogs.com/mvc/blog/CategoriesTags.aspx?blogApp=".$author."&blogId=".$blogId."&postId=".$posId);
        $arr = json_decode($json, true);
        if(isset($arr['Tags'])){
          $arr['Tags'] = str_replace("标签: ", "", $arr['Tags']);
          $key_str .= trim(strip_tags($arr['Tags']));
        }
        if(isset($arr['Categories'])){
          $arr['Categories'] = str_replace("分类: ", "", $arr['Categories']);
          $key_str .= ",".trim(strip_tags($arr['Categories']));
        }

        $arr = explode(",", $key_str);
        foreach ($arr as $val) {
          $info['keywords'] .= trim($val).",";
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
    case 'authorQuene': $grab->authorQuene();break;
    case 'authorList': $grab->authorList();break;
  }

