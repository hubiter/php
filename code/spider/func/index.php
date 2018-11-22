<?php
  require_once('init.php');

  /**
  *
  */
  //数据抓取类
  class Grab extends Init{
    public function __construct(){
      parent::__construct();
    }
    
    public function syncTagRel(){
      $sql = "select cate_name from hubphp_article where cate_name <> '' group by cate_name order by cate_name desc;";
      $rs = $this->_pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
      $tags = array();
      foreach ($rs as $val) {
        $cate_name = str_replace("/", ",", $val['cate_name']);
        // echo $cate_name.PHP_EOL;
        $cate_name = explode(",", $cate_name);
        foreach ($cate_name as $cate) {
          if($cate){
            $tags[] = strtolower($cate);
          }
        }
      }
      // echo count($tags).PHP_EOL;
      $tags = array_unique($tags);
      // echo count($tags).PHP_EOL;
      // print_r($tags);
      // exit;
      if($tags){
        foreach ($tags as $tag) {
          $sql = "select tag_id from hubphp_tags where tag_val = '{$tag}';";
          $rs = $this->_pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
          if(!$rs){
              $sql = "insert into hubphp_tags(tag_val) values('{$tag}');";
              $rs = $this->_pdo->query($sql);
              var_dump($rs);
          }else{
            echo "exists ".$tag."=>".$rs['tag_id'].PHP_EOL;
          }
        }
      }

      //处理对应关系
      $sql = "select tag_id, tag_val from hubphp_tags;";
      $rs = $this->_pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
      foreach ($rs as $val) {
        $tag_id = $val['tag_id'];
        $tag_val = $val['tag_val'];

        $sql = "select article_id from hubphp_article where cate_name LIKE '%{$tag_val}%';";
        $lists = $this->_pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        if($lists){
          foreach ($lists as $a_val) {
            $sql = "select rel_id from hubphp_article_tag_rel where tag_id = '{$tag_id}' AND article_id = '".$a_val['article_id']."';";
            $rel = $this->_pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
            if(!$rel){
              $sql = "insert into hubphp_article_tag_rel(article_id, tag_id) values('".$a_val['article_id']."', '".$tag_id."');";
              $res = $this->_pdo->query($sql);
              var_dump($res);
            }else{
              echo "exists ".$tag_id."=>".$rel['rel_id'].PHP_EOL;
            }
          }
        }
      }

      //情况cate_name关键字
      $sql = "update hubphp_article set cate_name = ''";
      $res = $this->_pdo->query($sql);
      var_dump($res);
    }

    //https://www.2cto.com
    public function get_2ctocom()
    { 
      // $this->_ssdb->zclear(__FUNCTION__."-list");
      $log_file = $this->_logdir.__CLASS__."-".__FUNCTION__."-".date('Ymd').".log";
      $log_str = date('Y-m-d H:i:s')."-->开始抓取，".PHP_EOL;
      file_put_contents($log_file, $log_str, FILE_APPEND);

      $url_arr = array(
        //linux
        '7' => array(
          'https://www.2cto.com/os/linux/',
        ),
        //系统综合
        '9' => array(
          'https://www.2cto.com/os/windows/',
          'https://www.2cto.com/os/dos/',
          'https://www.2cto.com/os/liulanqi/',
          'https://www.2cto.com/os/xuniji/',
          'https://www.2cto.com/os/qita/',
        ),
      );
      foreach($url_arr as $cate_id=>$arr){
        foreach ($arr as $url) {
          $min_page = 1;
          $max_page = 0;

          if(!$max_page){
            $link = $url;
            $this->_snoopy->fetch($link); 
            $links_arr = @$this->_snoopy->results;
            $htmlsource = mb_convert_encoding($links_arr, 'UTF-8', 'gb2312');
            // var_dump($link);
            // var_dump($htmlsource);exit;
            if(!$htmlsource || strpos($htmlsource, "box_ListBody") === false){
              $log_str = date('Y-m-d H:i:s')."-->".$link."首页数据为空，".PHP_EOL;
              echo $log_str;
              file_put_contents($log_file, $log_str, FILE_APPEND);
              break;
            }

            $this->_htmldom->load($htmlsource);
            if(strpos($htmlsource, "text-c") !== false){
              $max_page = $this->_htmldom->find('.text-c a', -2)->innertext();
            }else{
              $max_page = 10000;
            }

            if(!$max_page){
              $log_str = date('Y-m-d H:i:s')."-->".$link."最大页面数为空，".PHP_EOL;
              echo $log_str;
              file_put_contents($log_file, $log_str, FILE_APPEND);
              break;
            }
          }

          // echo '<pre>';
          // echo $max_page;exit;
          while($min_page <= $max_page){
            $link = $url.$min_page.".html";
            $this->_snoopy->fetch($link); 
            $links_arr = @$this->_snoopy->results;
            $htmlsource = mb_convert_encoding($links_arr, 'UTF-8', 'gb2312');
            if(!$htmlsource){
              $log_str = date('Y-m-d H:i:s')."-->".$link." 列表数据为空，".PHP_EOL;
              echo $log_str;
              file_put_contents($log_file, $log_str, FILE_APPEND);
              break;
            }
            // echo $htmlsource;exit;
            $this->_htmldom->load($htmlsource);
            if(strpos($htmlsource, "art_list") === false){
              $log_str = date('Y-m-d H:i:s')."-->".$link." 列表DOM数据为空，".PHP_EOL;
              echo $log_str;
              file_put_contents($log_file, $log_str, FILE_APPEND);
              var_dump($htmlsource);exit;
              break;
            }

            foreach($this->_htmldom->find('.art_list li') as $li) {
              $row = array();
              $row['cate_id'] = $cate_id;        
              $row['source'] = '红黑联盟';        
              $row['visits'] = 0;        
              $row['is_show'] = 1;        
              $row['is_top'] = 2;        
              $row['source_url'] = trim(@($li->find('a',0)->href));
              $row['source_url_md5'] = md5($row['source_url']);
              $row['name'] = "";
              $row['keywords'] = "";
              $row['description'] = "";
              $row['content'] = "";
              $row['ctime'] = "";

              if($this->_ssdb->zget(__FUNCTION__."-list", $row['source_url_md5'])){
                $log_str = date('Y-m-d H:i:s')."-->".$row['source_url_md5']."网址已存在，".PHP_EOL;
                echo $log_str;
                file_put_contents($log_file, $log_str, FILE_APPEND);
                continue;
              }
              if(strpos($htmlsource, "intro") !== false){
                $row['description'] = trim(@($li->find('.intro',0)->innertext()));
              }
              if(!$row['source_url']){
                $log_str = date('Y-m-d H:i:s')."-->".$link."，文章URL为空，".PHP_EOL;
                echo $log_str;
                file_put_contents($log_file, $log_str, FILE_APPEND);
                break;
              }
              // $this->_htmldom->clear();
              
              //获取文章内容
              $this->_snoopy->fetch($row['source_url']); 
              $links_arr = @$this->_snoopy->results;
              $htmlsource = mb_convert_encoding($links_arr, 'UTF-8', 'gb2312');
              if(!$htmlsource || strpos($htmlsource, "box_left") === false){
                $log_str = date('Y-m-d H:i:s')."-->".$row['source_url']."文章内容数据为空，".PHP_EOL;
                echo $log_str;
                file_put_contents($log_file, $log_str, FILE_APPEND);
                var_dump($htmlsource);
                var_dump($this->_curlGet($row['source_url']));

                exit;
                break;
              }

              $htmldom = new simple_html_dom();
              $htmldom->load($htmlsource);
              if(strpos($htmlsource, "box_t") !== false){
                $row['name'] = addslashes(trim(@($htmldom->find('.box_t', 0)->innertext())));
              }

              if(strpos($htmlsource, "more_tags") !== false){
                $row['keywords'] = trim(@($htmldom->find('.more_tags', 0)->innertext()));
                $row['keywords'] = str_replace("<span>相关TAG标签</span>", "", $row['keywords']);
                $row['keywords'] = strip_tags($row['keywords']);
                $row['keywords'] = str_replace(PHP_EOL, "", trim($row['keywords']));
                $row['keywords'] = str_replace(" ", ",", trim($row['keywords']));
                $tmp_arr = explode(",", $row['keywords']);
                $row['keywords'] = "";
                foreach ($tmp_arr as $str) {
                  if(trim($str)){
                    $row['keywords'] .= $str.",";
                  }
                }
                $row['keywords'] = rtrim($row['keywords'], ",");
                $row['keywords'] = $row['keywords'] ? $row['keywords'] : $row['name'];
              }

              if(strpos($htmlsource, "Article") !== false){
                $row['content'] = addslashes(trim(@($htmldom->find('#Article', 0)->innertext())));
              }
              
              if(strpos($htmlsource, "frinfo") !== false){
                $tmp_info = trim(@($htmldom->find('.frinfo', 0)->innertext()));
                preg_match("/([0-9]{4})-([0-9]{2})-([0-9]{2})(\s)+([0-9]{2}):([0-9]{2}):([0-9]{2})/", $tmp_info, $tmp_info);
                $row['ctime'] = $tmp_info['0'] ? $tmp_info['0'] : date('Y-m-d H:i:s');
              } 
              $htmldom->clear();

              // echo '<pre>';print_r($row);exit;
              if($row['source_url'] && $row['name'] && $row['content']){
                try{
                  $id = $this->_addData($row);
                  if(strpos($id, "repeat") === false){
                    $log_str = date('Y-m-d H:i:s')."-->抓取{$link} - {$row['source_url']} - {$row['name']}成功。ID:{$id}".PHP_EOL;
                    echo $log_str;
                    file_put_contents($log_file, $log_str, FILE_APPEND);
                    $this->_ssdb->zset(__FUNCTION__."-list", $row['source_url_md5'], $id);
                  }else{
                    $log_str = date('Y-m-d H:i:s')."-->".$link.'->'.$id.$row['source_url'].'->md5:'.$row['source_url_md5'].PHP_EOL;
                    echo $log_str;
                    file_put_contents($log_file, $log_str, FILE_APPEND);
                    // $this->_pdo->last_query();
                  }
                }catch(Exception $e){
                  var_dump($e->getmessage());exit(0);
                }

              }
            }
            $this->_htmldom->clear();
            $min_page++;
          }
        }
      }
    }

    //www.cnblogs.com
    public function get_cnblogs()
    { 
      // $this->_ssdb->zclear(__FUNCTION__."-list");
      $log_file = $this->_logdir.__CLASS__."-".__FUNCTION__."-".date('Ymd').".log";
      $log_str = date('Y-m-d H:i:s')."-->开始抓取，".PHP_EOL;
      file_put_contents($log_file, $log_str, FILE_APPEND);

      $url_arr = array(
        '7' => array(
          // 'https://www.cnblogs.com/cate/linux/',
          array(
            'ParentCategoryId' => '108724',
            'CategoryId' => '108726'
          ),
        ),

        '18' => array(
          // 'https://www.cnblogs.com/cate/php/',
          array(
            'ParentCategoryId' => '2',
            'CategoryId' => '106882'
          ),
        ),
       
        '24' => array(
          // 'https://www.cnblogs.com/cate/web/',
          // 'https://www.cnblogs.com/cate/html5/',
          array(
            'ParentCategoryId' => '108703',
            'CategoryId' => '108737'
          ),
          array(
            'ParentCategoryId' => '108703',
            'CategoryId' => '106883'
          ),
        ),

        '25' => array(
          // 'https://www.cnblogs.com/cate/javascript/',
          // 'https://www.cnblogs.com/cate/jquery/',
          array(
            'ParentCategoryId' => '108703',
            'CategoryId' => '106893'
          ),
          array(
            'ParentCategoryId' => '108703',
            'CategoryId' => '108731'
          ),
        ),

        '6' => array(
          // 'https://www.cnblogs.com/cate/infosec/',
          array(
            'ParentCategoryId' => '108704',
            'CategoryId' => '108749'
          ),
        ),

        '12' => array(
          // 'https://www.cnblogs.com/cate/mysql/',
          array(
            'ParentCategoryId' => '108712',
            'CategoryId' => '108715'
          ),
        ),
        '3' => array(
          // 'https://www.cnblogs.com/cate/nosql/',
          array(
            'ParentCategoryId' => '108712',
            'CategoryId' => '108743'
          ),
        ),
        '9' => array(
          // 'https://www.cnblogs.com/cate/design/',
          array(
            'ParentCategoryId' => '108712',
            'CategoryId' => '108743'
          ),
        ),
      );

      foreach($url_arr as $cate_id=>$arr){
        foreach ($arr as $hlink) {
          $min_page = 1;
          $max_page = 10;

          if(!$max_page){
            $htmlsource = @file_get_contents($hlink);
            if(!$htmlsource || strpos($htmlsource, "post_list") === false){
              $log_str = date('Y-m-d H:i:s')."-->".$hlink."首页数据为空，".PHP_EOL;
              echo $log_str;
              file_put_contents($log_file, $log_str, FILE_APPEND);
              break;
            }

            $this->_htmldom->load($htmlsource);
            $max_page = $this->_htmldom->find('.pager a', -2)->innertext();
            if(!$max_page){
              $log_str = date('Y-m-d H:i:s')."-->".$hlink."最大页面数为空，".PHP_EOL;
              echo $log_str;
              file_put_contents($log_file, $log_str, FILE_APPEND);
              break;
            }
          }

          while($min_page <= $max_page){
            $post_url = "https://www.cnblogs.com/mvc/AggSite/PostList.aspx";
            $param = array(
              'CategoryId' => $hlink['CategoryId'],
              'ParentCategoryId' => $hlink['CategoryId'],
              'CategoryType' => 'SiteCategory',
              'ItemListActionName' => 'PostList',
              'PageIndex' => $min_page,
              'TotalPostCount' => '40000',
            );
            // $link = $hlink."#p".$min_page;
            $htmlsource = $this->_curlPost($post_url, $param);
            // var_dump($htmlsource);exit;
            // $htmlsource = @file_get_contents($link);
            $link =  json_encode($hlink);
            if(!$htmlsource){
              $log_str = date('Y-m-d H:i:s')."-->".$link." 列表数据为空，".PHP_EOL;
              echo $log_str;
              file_put_contents($log_file, $log_str, FILE_APPEND);
              break;
            }
            // echo $htmlsource;exit;
            $this->_htmldom->load($htmlsource);
            foreach($this->_htmldom->find('.post_item') as $li) {
              $row = array();
              $row['cate_id'] = $cate_id;        
              $row['source'] = '博客园';        
              $row['visits'] = 0;        
              $row['is_show'] = 1;        
              $row['is_top'] = 2;        
              $row['source_url'] = trim(@($li->find('.post_item_body h3 a',0)->href));
              $row['source_url_md5'] = md5($row['source_url']);
              $row['keywords'] = "";
              $row['description'] = "";
              $row['content'] = "";
              $row['ctime'] = "";

              if($this->_ssdb->zget(__FUNCTION__."-list", $row['source_url_md5'])){
                $log_str = date('Y-m-d H:i:s')."-->".$link."->".$row['source_url_md5']."网址已存在，".PHP_EOL;
                echo $log_str;
                file_put_contents($log_file, $log_str, FILE_APPEND);
                continue;
              }
              
              if(strpos($htmlsource, "post_item_summary") !== false){
                $row['description'] = trim(strip_tags(@($li->find('.post_item_summary',0)->innertext())));
              }

              if(!$row['source_url']){
                $log_str = date('Y-m-d H:i:s')."-->".$link."，文章URL为空，".PHP_EOL;
                echo $log_str;
                file_put_contents($log_file, $log_str, FILE_APPEND);
                break;
              }
              // $this->_htmldom->clear();
              //获取文章内容
              $htmlsource = @file_get_contents($row['source_url']);
              if(!$htmlsource || strpos($htmlsource, "post_detail") === false){
                $log_str = date('Y-m-d H:i:s')."-->".$row['source_url']."文章内容数据为空，".PHP_EOL;
                echo $log_str;
                file_put_contents($log_file, $log_str, FILE_APPEND);
                break;
              }
              // print_r($htmlsource);exit;

              $htmldom = new simple_html_dom();
              $htmldom->load($htmlsource);
              if(strpos($htmlsource, "cb_post_title_url") !== false){
                $row['name'] = addslashes(trim(@($htmldom->find('#cb_post_title_url', 0)->innertext())));
              }

              if(strpos($htmlsource, "EntryTag") !== false){
                $row['keywords'] = trim(@($htmldom->find('#EntryTag', 0)->innertext()));
                $row['keywords'] = strip_tags($row['keywords']);
                $row['keywords'] = str_replace("标签: ", "", $row['keywords']);
                $row['keywords'] = str_replace(PHP_EOL, "", trim($row['keywords']));
                $row['keywords'] = str_replace(" ", ",", trim($row['keywords']));
                $tmp_arr = explode(",", $row['keywords']);
                $row['keywords'] = "";
                foreach ($tmp_arr as $str) {
                  if(trim($str)){
                    $row['keywords'] .= $str.",";
                  }
                }
                $row['keywords'] = rtrim($row['keywords'], ",");
                $row['keywords'] = $row['keywords'] ? $row['keywords'] : $row['name'];
              }

              if(strpos($htmlsource, "cnblogs_post_body") !== false){
                $row['content'] = addslashes(trim(@($htmldom->find('#cnblogs_post_body', 0)->innertext())));
              }
              // $tmp_info = trim(@($htmldom->find('.frinfo', 0)->innertext()));
              // preg_match("/([0-9]{4})-([0-9]{2})-([0-9]{2})(\s)+([0-9]{2}):([0-9]{2}):([0-9]{2})/", $tmp_info, $tmp_info);
              // $row['ctime'] = $tmp_info['0']?$tmp_info['0'] : date('Y-m-d H:i：s'); 
              if(strpos($htmlsource, "post-date") !== false){
                $row['ctime'] = date('Y-m-d H:i:s', strtotime(trim(@($htmldom->find('#post-date', 0)->innertext())))); 
              }

              // echo '<pre>';
              // print_r($row);exit;
              $htmldom->clear();
              if($row['source_url'] && $row['name'] && $row['content']){
                try{
                  $id = $this->_addData($row);
                  if(strpos($id, "repeat") === false){
                    $log_str = date('Y-m-d H:i:s')."-->抓取{$link} - {$row['source_url']} - {$row['name']}成功。ID:{$id}".PHP_EOL;
                    echo $log_str;
                    file_put_contents($log_file, $log_str, FILE_APPEND);
                    $this->_ssdb->zset(__FUNCTION__."-list", $row['source_url_md5'], $id);
                  }else{
                    $log_str = date('Y-m-d H:i:s')."-->".$link.'->'.$id.$row['source_url'].'->md5:'.$row['source_url_md5'].PHP_EOL;
                    echo $log_str;
                    file_put_contents($log_file, $log_str, FILE_APPEND);
                    // $this->_pdo->last_query();
                  }
                }catch(Exception $e){
                  var_dump($e->getmessage());exit(0);
                }
              }
            }
            $this->_htmldom->clear();
            // exit;
            $min_page++;
          }
        }
      }
    }

    //www.itdaan.com/
    public function get_itdaan()
    { 
      // $this->_ssdb->zclear(__FUNCTION__."-list");
      $log_file = $this->_logdir.__CLASS__."-".__FUNCTION__."-".date('Ymd').".log";
      $log_str = date('Y-m-d H:i:s')."-->开始抓取，".PHP_EOL;
      file_put_contents($log_file, $log_str, FILE_APPEND);

      // $sql = "select cate_id, cate_name from hubit_article_cate where cate_name <> '' and cate_name IS NOT NULL AND cate_id NOT IN('1','3','4','9')";
      $sql = "select cate_id, cate_name from hubit_article_cate where cate_name <> '' and cate_name IS NOT NULL AND cate_id >=17";
      $url_arr = $this->_pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

      foreach($url_arr as $cate_id => $arr){
        $cate_id = $arr['cate_id'];
        $cate_name = $arr['cate_name'];

        $min_page = 1;
        $max_page = 10;

        while($min_page <= $max_page){
          $link = "http://www.itdaan.com/so?q=".$cate_name."&page=".$min_page;
          $this->_snoopy->fetch($link); 
          $htmlsource = @$this->_snoopy->results;
          if(!$htmlsource){
            $log_str = date('Y-m-d H:i:s')."-->".$link." 列表数据为空，".PHP_EOL;
            echo $log_str;
            file_put_contents($log_file, $log_str, FILE_APPEND);
            break;
          }
          $this->_htmldom->load($htmlsource);
          if(strpos($htmlsource, "content_div") === false || strpos($htmlsource, "media") === false){
            $log_str = date('Y-m-d H:i:s')."-->".$link." 列表DOM数据为空，".PHP_EOL;
            echo $log_str;
            file_put_contents($log_file, $log_str, FILE_APPEND);
            break;
          }

          foreach($this->_htmldom->find('.content_div .media') as $li) {
            $row = array();
            $row['cate_id'] = $cate_id;        
            $row['source'] = '开发者知识库';        
            $row['visits'] = 0;        
            $row['is_show'] = 1;        
            $row['is_top'] = 2;        
            $row['source_url'] = trim(@($li->find('dt a',0)->href));
            $row['source_url_md5'] = md5($row['source_url']);
            $row['keywords'] = "";
            $row['description'] = "";
            $row['content'] = "";
            $row['ctime'] = "";

            if($this->_ssdb->zget(__FUNCTION__."-list", $row['source_url_md5'])){
              $log_str = date('Y-m-d H:i:s')."-->".$link."->".$row['source_url_md5']."网址已存在，".PHP_EOL;
              echo $log_str;
              file_put_contents($log_file, $log_str, FILE_APPEND);
              continue;
            }
            
            if(strpos($htmlsource, "<dd>") !== false){
              $row['description'] = trim(strip_tags(@($li->find('dd',0)->innertext())));
            }

            if(!$row['source_url']){
              $log_str = date('Y-m-d H:i:s')."-->".$link."，文章URL为空，".PHP_EOL;
              echo $log_str;
              file_put_contents($log_file, $log_str, FILE_APPEND);
              continue;
            }
           
            $this->_snoopy->fetch($row['source_url']); 
            $htmlsource = @$this->_snoopy->results;
            if(!$htmlsource || strpos($htmlsource, "content_div") === false){
              $log_str = date('Y-m-d H:i:s')."-->".$row['source_url']."文章内容数据为空，".PHP_EOL;
              echo $log_str;
              file_put_contents($log_file, $log_str, FILE_APPEND);
              continue;
            }
            // print_r($htmlsource);exit;
            $htmldom = new simple_html_dom();
            $htmldom->load(trim($htmlsource));
            if(strpos($htmlsource, '<center><h3>') !== false){
              preg_match("/<center><h3>(.*)<\/h3>/", $htmlsource, $name);
              $row['name'] = isset($name) && isset($name['1']) ? addslashes(trim($name['1'])) : "";
            }
            $row['keywords'] = $row['name'];
            
            if(strpos($htmlsource, "media-body") !== false){
              echo $row['source_url'].PHP_EOL;
              preg_match('/<div class="media-body" id="content_div">([.|\s|\S]*)<\/div>([.|\s|\S]*)<\/div>([.|\s|\S]*)<div class="ad_content_down">/i', $htmlsource, $tmp);
              $str = isset($tmp) && isset($tmp['1']) ? trim($tmp['1']) : "";
              $str = preg_replace('/<div class="left_fixed">([.|\s|\S]*)<\/font><\/a>/i', "", $str);
              $str = preg_replace('/^<br>/i', "", trim($str));
              $str = preg_replace('/^<br\/>/i', "", trim($str));
              $str = trim($str);
              $row['content'] = str_replace("<p>转载请标明出处，谢谢！</p>", "", $str);
              $row['content'] = addslashes($row['content']);
            }

            preg_match('/<div class="postinfo">([.|\S|\s]*)<div class="ad_title_down">/i', $htmlsource, $tmp);
            $str = isset($tmp) && isset($tmp['1']) ? trim($tmp['1']) : "";
            preg_match("/([0-9]{4})\/([0-9]{2})\/([0-9]{2})/i", $str, $tmp);
            $row['ctime'] = isset($tmp) && isset($tmp['0']) ? date('Y-m-d H:i:s', strtotime(trim($tmp['0']))) : date('Y-m-d H:i:s');

            // echo '<pre>';
            // print_r($row);exit;
            $htmldom->clear();
            if($row['source_url'] && $row['name'] && $row['content']){
              try{
                $id = $this->_addData($row);
                if(strpos($id, "repeat") === false){
                  $log_str = date('Y-m-d H:i:s')."-->抓取{$link} - {$row['source_url']} - {$row['name']}成功。ID:{$id}".PHP_EOL;
                  echo $log_str;
                  file_put_contents($log_file, $log_str, FILE_APPEND);
                  $this->_ssdb->zset(__FUNCTION__."-list", $row['source_url_md5'], $id);
                }else{
                  $log_str = date('Y-m-d H:i:s')."-->".$link.'->'.$id.$row['source_url'].'->md5:'.$row['source_url_md5'].PHP_EOL;
                  echo $log_str;
                  file_put_contents($log_file, $log_str, FILE_APPEND);
                  // $this->_pdo->last_query();
                }
              }catch(Exception $e){
                var_dump($e->getmessage());exit(0);
              }
            }
          }
          $this->_htmldom->clear();
          // exit;
          $min_page++;
        }
      }
    }

    //http://www.infoq.com
    public function get_infoq_news()
    { 
      // $this->_ssdb->zclear(__FUNCTION__."-list");
      $log_file = $this->_logdir.__CLASS__."-".__FUNCTION__."-".date('Ymd').".log";
      $log_str = date('Y-m-d H:i:s')."-->开始抓取，".PHP_EOL;
      file_put_contents($log_file, $log_str, FILE_APPEND);

      $url_arr = array(
        '24' => array(
          'html-5',
        ),

        '25' => array(
          'javascript',
        ),

        '5' => array(
          'Front-end',
        ),

        '9' => array(
          'architecture',
          'performance-scalability',
          'design',
          'Case_Study',
        ),

        '6' => array(
          'Security',
        ),

        '29' => array(
          'bigdata',
        ),

        '3' => array(
          'nosql',
          'database',
        ),
      );

      foreach($url_arr as $cate_id => $arr){
        foreach($arr as $val){
          $min_page = 0;
          $max_page = 10;

          while($min_page <= $max_page){
            $news_num = $min_page*15;
            $link = "http://www.infoq.com/cn/".$val."/news/".$news_num;
            $this->_snoopy->fetch($link); 
            $htmlsource = @$this->_snoopy->results;
            // echo $htmlsource;exit;
            if(!$htmlsource || strpos($htmlsource, 'id="content"') === false || strpos($htmlsource, 'class="news_type_block"') === false){
              $log_str = date('Y-m-d H:i:s')."-->".$link." 新闻列表/DOM数据为空，".PHP_EOL;
              echo $log_str;
              file_put_contents($log_file, $log_str, FILE_APPEND);
              break;
            }
              
            $this->_htmldom->load($htmlsource);
            foreach($this->_htmldom->find('.news_type_block') as $li) {
              $row = array();
              $row['cate_id'] = $cate_id;        
              $row['source'] = 'InfoQ';        
              $row['visits'] = 0;        
              $row['is_show'] = 1;        
              $row['is_top'] = 2;        
              $row['source_url'] = "http://www.infoq.com".trim(@($li->find('h2 a',0)->href));
              $row['source_url_md5'] = md5($row['source_url']);
              $row['keywords'] = "";
              $row['description'] = "";
              $row['content'] = "";
              $row['ctime'] = "";
              if($row['source_url']){
                if($this->_ssdb->zget(__FUNCTION__."-list", $row['source_url_md5'])){
                  $log_str = date('Y-m-d H:i:s')."-->".$link."->".$row['source_url_md5']."网址已存在，".PHP_EOL;
                  echo $log_str;
                  file_put_contents($log_file, $log_str, FILE_APPEND);
                  continue;
                }

                if(strpos($htmlsource, "<p>") !== false){
                  $row['description'] = trim(strip_tags(@($li->find('p',0)->innertext())));
                }
                //获取文章内容
                $this->_snoopy->fetch($row['source_url']); 
                $htmlsource = @$this->_snoopy->results;
                if(!$htmlsource || strpos($htmlsource, 'id="content"') === false){
                  $log_str = date('Y-m-d H:i:s')."-->".$row['source_url']."文章内容数据为空，".PHP_EOL;
                  echo $log_str;
                  file_put_contents($log_file, $log_str, FILE_APPEND);
                  continue;
                }
                // print_r($htmlsource);exit;
                $htmldom = new simple_html_dom();
                $htmldom->load(trim($htmlsource));
                if(strpos($htmlsource, 'class="general"') !== false){
                  $row['name'] = addslashes(trim(@($htmldom->find('.general', 0)->innertext())));
                }
                $row['keywords'] = $row['name'];

                if(strpos($htmlsource, "text_info") !== false){
                  $row['content'] = trim(@($htmldom->find('.text_info', 0)->innertext()));
                  $row['content'] = "<span id='content'>".$row['content']."</span>";
                  $chtmldom = new simple_html_dom();
                  $chtmldom->load(trim($row['content']));
                  if(strpos($row['content'], "div") !== false){
                    foreach($chtmldom->find('div') as $div) {
                      $div->outertext = "";
                    }
                  }
                  if(strpos($row['content'], "script") !== false){
                    foreach($chtmldom->find('script') as $div) {
                      $div->outertext = "";
                    }
                  }
                  $chtmldom->find('#cont_item_primary_topic', 0)->outertext = "";
                  $chtmldom->find('.comments_like', 0)->outertext = "";
                  $chtmldom->save();

                  $row['content'] = $chtmldom->find('span', 0)->innertext();
                  $row['content'] = str_replace("<!-- overlay -->", "", $row['content']);
                  $row['content'] = str_replace("<!-- reply box -->", "", $row['content']);
                  $row['content'] = str_replace("<!-- edit comment box -->", "", $row['content']);
                  $row['content'] = str_replace("<!-- notification popup -->", "", $row['content']);
                  $row['content'] = trim($row['content']);
                  $row['content'] = addslashes($row['content']);
                }
                $tmp_info = trim(@($htmldom->find('.author_general', 0)->innertext()));
                // 2018年5月31日
                // print_r($tmp_info);
                $tmp_info = str_replace("年", "-",  $tmp_info);
                $tmp_info = str_replace("月", "-",  $tmp_info);
                $tmp_info = str_replace("日", "-",  $tmp_info);
                preg_match("/(20[0-9]{2})-([0-9]{1,2})-([0-9]{1,2})/i", $tmp_info, $tmp_info);
                $row['ctime'] = isset($tmp_info['0']) ? date('Y-m-d H:i:s', strtotime($tmp_info['0'])) : date('Y-m-d H:i:s'); 

                // echo '<pre>';
                // print_r($row);exit;
                $htmldom->clear();
                if($row['source_url'] && $row['name'] && $row['content']){
                  try{
                    $id = $this->_addData($row);
                    if(strpos($id, "repeat") === false){
                      $log_str = date('Y-m-d H:i:s')."-->抓取{$link} - {$row['source_url']} - {$row['name']}成功。ID:{$id}".PHP_EOL;
                      echo $log_str;
                      file_put_contents($log_file, $log_str, FILE_APPEND);
                      $this->_ssdb->zset(__FUNCTION__."-list", $row['source_url_md5'], $id);
                    }else{
                      $log_str = date('Y-m-d H:i:s')."-->".$link.'->'.$id.$row['source_url'].'->md5:'.$row['source_url_md5'].PHP_EOL;
                      echo $log_str;
                      file_put_contents($log_file, $log_str, FILE_APPEND);
                      // $this->_pdo->last_query();
                    }
                  }catch(Exception $e){
                    var_dump($e->getmessage());exit(0);
                  }
                }
              }
            }

            $this->_htmldom->clear();
            // exit;
            $min_page++;
          }
        }
      }
    }

     //http://www.infoq.com
    public function get_infoq_article()
    { 
      // $this->_ssdb->zclear(__FUNCTION__."-list");
      $log_file = $this->_logdir.__CLASS__."-".__FUNCTION__."-".date('Ymd').".log";
      $log_str = date('Y-m-d H:i:s')."-->开始抓取，".PHP_EOL;
      file_put_contents($log_file, $log_str, FILE_APPEND);

      $url_arr = array(
        '24' => array(
          'html-5',
        ),

        '25' => array(
          'javascript',
        ),

        '5' => array(
          'Front-end',
        ),

        '9' => array(
          'architecture',
          'performance-scalability',
          'design',
          'Case_Study',
        ),

        '6' => array(
          'Security',
        ),

        '29' => array(
          'bigdata',
        ),

        '3' => array(
          'nosql',
          'database',
        ),
      );

      foreach($url_arr as $cate_id => $arr){
        foreach($arr as $key_word){
          $min_page = 0;
          $max_page = 10;

          while($min_page <= $max_page){
            $news_num = $min_page*12;
            $link = "http://www.infoq.com/cn/".$key_word."/articles/".$news_num;
            $this->_snoopy->fetch($link); 
            $htmlsource = @$this->_snoopy->results;
            // echo $htmlsource;exit;
            if(!$htmlsource || strpos($htmlsource, 'id="content"') === false || strpos($htmlsource, 'news_type2 full_screen') === false || strpos($htmlsource, "加载更多") === false){
              $log_str = date('Y-m-d H:i:s')."-->".$link." 新闻列表/DOM数据为空，".PHP_EOL;
              echo $log_str;
              file_put_contents($log_file, $log_str, FILE_APPEND);
              break;
            }

            $this->_htmldom->load($htmlsource);
            echo $link.PHP_EOL;
            $list_arr = array();
            
            foreach($this->_htmldom->find('.news_type1') as $li) {
              $url = "http://www.infoq.com".trim(@($this->_htmldom->find('.news_type1 h2 a', 0)->href));
              $description = trim(strip_tags(@($this->_htmldom->find('.news_type1 p', 0)->innertext())));
              $arr = array(
                'source_url' => $url,
                'description' => $description,
              );
              array_push($list_arr, $arr);
            }

            foreach($this->_htmldom->find('.news_type2') as $li) {
              $url = "http://www.infoq.com".trim(@($li->find('h2 a',0)->href));
              $description = trim(strip_tags(@($li->find('p', 0)->innertext())));
              $arr = array(
                'source_url' => $url,
                'description' => $description,
              );
              array_push($list_arr, $arr);
            }
            // print_r($list_arr);exit;
            foreach($list_arr as $val) {
              if(!$val){
                continue;
              }

              $row = array();
              $row['source_url'] = $val['source_url'];
              $row['cate_id'] = $cate_id;        
              $row['source'] = 'InfoQ';        
              $row['visits'] = 0;        
              $row['is_show'] = 1;        
              $row['is_top'] = 2;        
              $row['source_url_md5'] = md5($row['source_url']);
              $row['keywords'] = "";
              $row['description'] = $val['description'];
              $row['content'] = "";
              $row['ctime'] = "";
              
              if($this->_ssdb->zget(__FUNCTION__."-list", $row['source_url_md5'])){
                $log_str = date('Y-m-d H:i:s')."-->".$link."->".$row['source_url_md5']."网址已存在，".PHP_EOL;
                echo $log_str;
                file_put_contents($log_file, $log_str, FILE_APPEND);
                continue;
              }
              
              //获取文章内容
              $this->_snoopy->fetch($row['source_url']); 
              $htmlsource = @$this->_snoopy->results;
              if(!$htmlsource || strpos($htmlsource, 'id="content"') === false){
                $log_str = date('Y-m-d H:i:s')."-->".$row['source_url']."文章内容数据为空，".PHP_EOL;
                echo $log_str;
                file_put_contents($log_file, $log_str, FILE_APPEND);
                continue;
              }
              // print_r($htmlsource);exit;
              $htmldom = new simple_html_dom();
              $htmldom->load(trim($htmlsource));
              if(strpos($htmlsource, 'class="title_canvas"') !== false){
                $row['name'] = addslashes(trim(@($htmldom->find('.title_canvas h1', 0)->innertext())));
              }
              $row['keywords'] = $row['name'];

              if(strpos($htmlsource, "text_info") !== false){
                $row['content'] = trim(@($htmldom->find('.text_info', 0)->innertext()));
                $row['content'] = "<span id='content'>".$row['content']."</span>";
                $chtmldom = new simple_html_dom();
                $chtmldom->load(trim($row['content']));
                if(strpos($row['content'], "div") !== false){
                  foreach($chtmldom->find('div') as $div) {
                    $div->outertext = "";
                  }
                }
                if(strpos($row['content'], "script") !== false){
                  foreach($chtmldom->find('script') as $div) {
                    $div->outertext = "";
                  }
                }
                $chtmldom->find('#cont_item_primary_topic', 0)->outertext = "";
                $chtmldom->find('.comments_like', 0)->outertext = "";
                $chtmldom->save();

                $row['content'] = $chtmldom->find('span', 0)->innertext();
                $row['content'] = str_replace("<!-- overlay -->", "", $row['content']);
                $row['content'] = str_replace("<!-- reply box -->", "", $row['content']);
                $row['content'] = str_replace("<!-- edit comment box -->", "", $row['content']);
                $row['content'] = str_replace("<!-- notification popup -->", "", $row['content']);
                $row['content'] = trim($row['content']);
                $row['content'] = addslashes($row['content']);
              }

              $tmp_info = trim(@($htmldom->find('.author_general', 0)->innertext()));
              $tmp_info = str_replace("年", "-",  $tmp_info);
              $tmp_info = str_replace("月", "-",  $tmp_info);
              $tmp_info = str_replace("日", "-",  $tmp_info);
              preg_match("/(20[0-9]{2})-([0-9]{1,2})-([0-9]{1,2})/i", $tmp_info, $tmp_info);
              $row['ctime'] = isset($tmp_info['0']) ? date('Y-m-d H:i:s', strtotime($tmp_info['0'])) : date('Y-m-d H:i:s'); 

              // echo '<pre>';
              // print_r($row);exit;
              $htmldom->clear();
              if($row['source_url'] && $row['name'] && $row['content']){
                try{
                  $id = $this->_addData($row);
                  if(strpos($id, "repeat") === false){
                    $log_str = date('Y-m-d H:i:s')."-->抓取{$link} - {$row['source_url']} - {$row['name']}成功。ID:{$id}".PHP_EOL;
                    echo $log_str;
                    file_put_contents($log_file, $log_str, FILE_APPEND);
                    $this->_ssdb->zset(__FUNCTION__."-list", $row['source_url_md5'], $id);
                  }else{
                    $log_str = date('Y-m-d H:i:s')."-->".$link.'->'.$id.$row['source_url'].'->md5:'.$row['source_url_md5'].PHP_EOL;
                    echo $log_str;
                    file_put_contents($log_file, $log_str, FILE_APPEND);
                    // $this->_pdo->last_query();
                  }
                }catch(Exception $e){
                  var_dump($e->getmessage());exit(0);
                }
              }
            }

            $this->_htmldom->clear();
            // exit;
            $min_page++;
          }
        }
      }
    }

    public function get_jb51_list(){ 
      // $this->_ssdb->zclear(__FUNCTION__."-list");exit;
      $log_file = $this->_logdir.__CLASS__."-".__FUNCTION__."-".date('Ymd').".log";
      $log_str = date('Y-m-d H:i:s')."-->开始抓取，".PHP_EOL;
      file_put_contents($log_file, $log_str, FILE_APPEND);

      $url_arr = array(
        '25' => array(
          'list/list_3',
        ),
        '18' => array(
          'list/list_15',
        ),
        '30' => array(
          'list/list_6',
        ),
        '28' => array(
          'list/list_5',
        ),
        '24' => array(
          'list/list_4',
        ),
        '6' => array(
          'list/list_60',
          'list/list_72',
        ),
        '9' => array(
          'list/list_177',
          'list/list_185',
          'list/list_82',
        ),
        '12' => array(
          'list/list_112',
        ),
        '17' => array(
          'list/list_215',
        ),
        '13' => array(
          'list/list_224',
        ),
        '14' => array(
          'list/list_242',
        ),
        '3' => array(
          'list/list_133',
        ),
        '7' => array(
          'list/list_203',
        ),
      );
      
      foreach($url_arr as $cate_id=>$arr){
        foreach ($arr as $url) {
          $min_page = 1;
          $max_page = 10;

          if(!$max_page){
            $link = "https://www.jb51.net/".$url."_".$min_page.".htm";
            $this->_snoopy->fetch($link); 
            $links_arr = @$this->_snoopy->results;
            $htmlsource = mb_convert_encoding($links_arr, 'UTF-8', 'gb2312');
            // var_dump($link);
            // var_dump($htmlsource);exit;
            if(!$htmlsource || strpos($htmlsource, "artlist") === false){
              $log_str = date('Y-m-d H:i:s')."-->".$link."首页数据为空，".PHP_EOL;
              echo $log_str;
              file_put_contents($log_file, $log_str, FILE_APPEND);
              break;
            }

            $this->_htmldom->load($htmlsource);
            if(strpos($htmlsource, "dxypage") !== false){
              $max_page = $this->_htmldom->find('.dxypage a', -1)->href;
              // /list/list_3_1054.htm
              $max_page = str_replace(".htm", "", $max_page);
              $max_page = str_replace("/".$url."_", "", $max_page);
            }else{
              $max_page = 10000;
            }
            if(!$max_page){
              $log_str = date('Y-m-d H:i:s')."-->".$link."最大页面数为空，".PHP_EOL;
              echo $log_str;
              file_put_contents($log_file, $log_str, FILE_APPEND);
              break;
            }
          }

          // echo '<pre>';
          // echo $max_page;exit;
          while($min_page <= $max_page){
            $link = "https://www.jb51.net/".$url."_".$min_page.".htm";
            $this->_snoopy->fetch($link); 
            $links_arr = @$this->_snoopy->results;
            $htmlsource = mb_convert_encoding($links_arr, 'UTF-8', 'gb2312');
            if(!$htmlsource){
              $log_str = date('Y-m-d H:i:s')."-->".$link." 列表数据为空，".PHP_EOL;
              echo $log_str;
              file_put_contents($log_file, $log_str, FILE_APPEND);
              break;
            }
            // echo $htmlsource;exit;
            $this->_htmldom->load($htmlsource);
            if(strpos($htmlsource, "artlist") === false){
              $log_str = date('Y-m-d H:i:s')."-->".$link." 列表DOM数据为空，".PHP_EOL;
              echo $log_str;
              file_put_contents($log_file, $log_str, FILE_APPEND);
              // var_dump($htmlsource);exit;
              break;
            }

            foreach($this->_htmldom->find('.artlist dl dt') as $li) {
              $row = array();
              $row['cate_id'] = $cate_id;        
              $row['source'] = '脚本之家';        
              $row['visits'] = 0;        
              $row['is_show'] = 1;        
              $row['is_top'] = 2;        
              $row['source_url'] = "https://www.jb51.net".trim(@($li->find('a',0)->href));
              $row['source_url_md5'] = md5($row['source_url']);
              $row['name'] = "";
              $row['keywords'] = "";
              $row['description'] = "";
              $row['content'] = "";
              $row['ctime'] = "";
              
              if(!$row['source_url']){
                $log_str = date('Y-m-d H:i:s')."-->".$link."，文章URL为空，".PHP_EOL;
                echo $log_str;
                file_put_contents($log_file, $log_str, FILE_APPEND);
                break;
              }

              if($this->_ssdb->zget(__FUNCTION__."-list", $row['source_url_md5'])){
                $log_str = date('Y-m-d H:i:s')."-->".$row['source_url_md5']."网址已存在，".PHP_EOL;
                echo $log_str;
                file_put_contents($log_file, $log_str, FILE_APPEND);
                continue;
              }
              
              // $this->_htmldom->clear();
              //获取文章内容
              $this->_snoopy->fetch($row['source_url']); 
              $links_arr = @$this->_snoopy->results;
              $htmlsource = mb_convert_encoding($links_arr, 'UTF-8', 'gb2312');
              if(!$htmlsource || strpos($htmlsource, "article-content") === false){
                $log_str = date('Y-m-d H:i:s')."-->".$row['source_url']."文章内容数据为空，".PHP_EOL;
                echo $log_str;
                file_put_contents($log_file, $log_str, FILE_APPEND);
                var_dump($htmlsource);
                var_dump($this->_curlGet($row['source_url']));
                break;
              }

              $htmldom = new simple_html_dom();
              $htmldom->load($htmlsource);
              if(strpos(@($htmldom->find('.article-content',0)->innertext()), '<h1 class="YaHei">') !== false){
                $row['name'] = addslashes(trim(@($htmldom->find('.article-content .title .YaHei', 0)->innertext())));
              }

              if(strpos(@($htmldom->find('.article-content',0)->innertext()), "meta-tags") !== false){
                $row['keywords'] = trim(@($htmldom->find('.meta-tags', 0)->innertext()));
                // $row['keywords'] = str_replace("<span>相关TAG标签</span>", "", $row['keywords']);
                $row['keywords'] = strip_tags($row['keywords']);
                $row['keywords'] = str_replace(PHP_EOL, "", trim($row['keywords']));
                $row['keywords'] = str_replace(" ", ",", trim($row['keywords']));
                $tmp_arr = explode(",", $row['keywords']);
                $row['keywords'] = "";
                foreach ($tmp_arr as $str) {
                  if(trim($str)){
                    $row['keywords'] .= $str.",";
                  }
                }
                $row['keywords'] = rtrim($row['keywords'], ",");
                $row['keywords'] = $row['keywords'] ? $row['keywords'] : $row['name'];
              }

              if(strpos(@($htmldom->find('.article-content',0)->innertext()), "art_demo") !== false){
                $row['description'] = trim(@($htmldom->find('#art_demo',0)->innertext()));
              }

              if(strpos(@($htmldom->find('.article-content',0)->innertext()), "title") !== false){
                $tmp_info = trim(@($htmldom->find('.article-content .title p', 0)->innertext()));
                $tmp_info = strip_tags($tmp_info);
                $tmp_info = str_replace("年", "-", $tmp_info);
                $tmp_info = str_replace("月", "-", $tmp_info);
                $tmp_info = str_replace("日", "", $tmp_info);
                preg_match("/([0-9]{4})-([0-9]{2})-([0-9]{2})(\s)+([0-9]{2}):([0-9]{2}):([0-9]{2})/", $tmp_info, $tmp_info);
                $row['ctime'] = $tmp_info['0'] ? $tmp_info['0'] : date('Y-m-d H:i:s');
              } 

              if(strpos($htmlsource, 'id="content"') !== false){
                if(strpos($htmldom->find('#content', 0)->innertext(), 'art_xg') !== false){
                  $htmldom->find('#content .art_xg', 0)->outertext = "";
                }
                if(strpos($htmldom->find('#content', 0)->innertext(), 'jb51ewm') !== false){
                  $htmldom->find('#content .jb51ewm', 0)->outertext = "";
                }
                if(strpos($htmldom->find('#content', 0)->innertext(), 'jbTestPos') !== false){
                  $htmldom->find('#content .jbTestPos', 0)->outertext = "";
                }

                $row['content'] = addslashes(trim(@($htmldom->find('#content', 0)->innertext())));
              }
              // print_r($row);exit;
              
              $htmldom->clear();

              // echo '<pre>';print_r($row);exit;
              if($row['source_url'] && $row['name'] && $row['content']){
                try{
                  $id = $this->_addData($row);
                  if(strpos($id, "repeat") === false){
                    $log_str = date('Y-m-d H:i:s')."-->抓取{$link} - {$row['source_url']} - {$row['name']}成功。ID:{$id}".PHP_EOL;
                    echo $log_str;
                    file_put_contents($log_file, $log_str, FILE_APPEND);
                    $this->_ssdb->zset(__FUNCTION__."-list", $row['source_url_md5'], $id);
                  }else{
                    $log_str = date('Y-m-d H:i:s')."-->".$link.'->'.$id.$row['source_url'].'->md5:'.$row['source_url_md5'].PHP_EOL;
                    echo $log_str;
                    file_put_contents($log_file, $log_str, FILE_APPEND);
                    // $this->_pdo->last_query();
                  }
                }catch(Exception $e){
                  var_dump($e->getmessage());exit(0);
                }

              }
            }
            $this->_htmldom->clear();
            $min_page++;
          }
        }
      }
    }

    public function get_jb51_list_icon()
    { 
      // $this->_ssdb->zclear(__FUNCTION__."-list");
      $log_file = $this->_logdir.__CLASS__."-".__FUNCTION__."-".date('Ymd').".log";
      $log_str = date('Y-m-d H:i:s')."-->开始抓取，".PHP_EOL;
      file_put_contents($log_file, $log_str, FILE_APPEND);

      $url_arr = array(
        //HTML5/CSS
        '24' => array(
          'web/list220',
          'html5/list551',
          'css/list221',
          'web/list225',
        ),
        
        '9' => array(
          'os/list682',
          'os/list490',
          'os/list685',
          'os/list381',
          'os/other/list98',
        ),

        '7' => array(
          'LINUXjishu/list314',
          'os/RedHat/list92',
          'os/Ubuntu/list295',
          'os/hongqi/list95',
        ),

        '6' => array(
          'hack/list171',
          'hack/list305',
          'hack/list461',
          'hack/list544',
          'hack/list173',
          'hack/list172',
          'hack/list301',
          'hack/list176',
          'hack/list175',
          'hack/list174',
          'hack/list453',
          'hack/list460',
        ),
      );

      foreach($url_arr as $cate_id=>$arr){
        foreach ($arr as $url) {
          $min_page = 1;
          $max_page = 0;

          if(!$max_page){
            $link = "https://www.jb51.net/".$url."_".$min_page.".html";
            $this->_snoopy->fetch($link); 
            $links_arr = @$this->_snoopy->results;
            $htmlsource = mb_convert_encoding($links_arr, 'UTF-8', 'gb2312');
            // var_dump($link);
            // var_dump($htmlsource);exit;
            if(!$htmlsource || strpos($htmlsource, "listsub") === false){
              $log_str = date('Y-m-d H:i:s')."-->".$link."首页数据为空，".PHP_EOL;
              echo $log_str;
              file_put_contents($log_file, $log_str, FILE_APPEND);
              break;
            }

            $this->_htmldom->load($htmlsource);
            if(strpos($htmlsource, "dxypage") !== false){
              $max_page = trim(@($this->_htmldom->find('.dxypage a', -1)->href));
              // list220_59.html
              $max_page = str_replace(".html", "", $max_page);
              $str = substr($url, (strpos($url, "/")+1));
              // var_dump($str);exit();
              $max_page = str_replace($str."_", "", $max_page);
            }else{
              $max_page = 10000;
            }
            if(!$max_page){
              $log_str = date('Y-m-d H:i:s')."-->".$link."最大页面数为空，".PHP_EOL;
              echo $log_str;
              file_put_contents($log_file, $log_str, FILE_APPEND);
              break;
            }
          }

          // echo '<pre>';
          // echo $max_page;exit;
          while($min_page <= $max_page){
            $link = "https://www.jb51.net/".$url."_".$min_page.".html";
            $this->_snoopy->fetch($link); 
            $links_arr = @$this->_snoopy->results;
            $htmlsource = mb_convert_encoding($links_arr, 'UTF-8', 'gb2312');
            if(!$htmlsource){
              $log_str = date('Y-m-d H:i:s')."-->".$link." 列表数据为空，".PHP_EOL;
              echo $log_str;
              file_put_contents($log_file, $log_str, FILE_APPEND);
              break;
            }
            // echo $htmlsource;exit;
            $this->_htmldom->load($htmlsource);
            if(strpos($htmlsource, 'id="lists"') === false || strpos($htmlsource, 'item-inner') === false){
              $log_str = date('Y-m-d H:i:s')."-->".$link." 列表DOM数据为空，".PHP_EOL;
              echo $log_str;
              file_put_contents($log_file, $log_str, FILE_APPEND);
              // var_dump($htmlsource);exit;
              break;
            }

            foreach($this->_htmldom->find('#lists .item-inner') as $li) {
              $row = array();
              $row['cate_id'] = $cate_id;        
              $row['source'] = '脚本之家';        
              $row['visits'] = 0;        
              $row['is_show'] = 1;        
              $row['is_top'] = 2;     
              if(strpos($li->innertext(), "rbox-inner") !== false){
                $row['source_url'] = "https://www.jb51.net".trim(@($li->find('.rbox-inner p a',0)->href));
                $row['source_url_md5'] = md5($row['source_url']);
              }
              $row['name'] = "";
              $row['keywords'] = "";
              $row['description'] = "";
              $row['content'] = "";
              $row['ctime'] = "";
              $row['icon'] = "";
              
              if(!isset($row['source_url']) || !$row['source_url']){
                $log_str = date('Y-m-d H:i:s')."-->".$link."，文章URL为空，".PHP_EOL;
                echo $log_str;
                file_put_contents($log_file, $log_str, FILE_APPEND);
                break;
              }

              if($this->_ssdb->zget(__FUNCTION__."-list", $row['source_url_md5'])){
                $log_str = date('Y-m-d H:i:s')."-->".$row['source_url_md5']."网址已存在，".PHP_EOL;
                echo $log_str;
                file_put_contents($log_file, $log_str, FILE_APPEND);
                continue;
              }

              if(strpos($li->innertext(), "img-wrap") !== false){
                $row['icon'] = trim(@($li->find('.img-wrap img',0)->src));
              }
              
              //获取文章内容
              $b_arr = array(
                'https://www.jb51.net/web/6533.html',
                'https://www.jb51.net/hack/5851.html',
                'https://www.jb51.net/hack/5832.html',
              );
              if(in_array($row['source_url'], $b_arr)){
                continue;
              }
              $this->_snoopy->fetch($row['source_url']); 
              $links_arr = @$this->_snoopy->results;
              $htmlsource = mb_convert_encoding($links_arr, 'UTF-8', 'gb2312');
              echo $row['source_url'].PHP_EOL;
              if(!$htmlsource || strpos($htmlsource, "article-content") === false || strpos($htmlsource, '<h1 class="YaHei">') === false){
                $log_str = date('Y-m-d H:i:s')."-->".$row['source_url']."文章内容数据为空，".PHP_EOL;
                file_put_contents($log_file, $log_str, FILE_APPEND);
                break;
              }

              $htmldom = new simple_html_dom();
              $htmldom->load($htmlsource);
              if(strpos(@($htmldom->find('.article-content',0)->innertext()), '<h1 class="YaHei">') !== false){
                $row['name'] = addslashes(trim(@($htmldom->find('.article-content .title .YaHei', 0)->innertext())));
              }
              if(strpos(@($htmldom->find('.article-content',0)->innertext()), "art_demo") !== false){
                $row['description'] = trim(@($htmldom->find('#art_demo',0)->innertext()));
              }
              echo $row['source_url'].PHP_EOL;
              if(strpos(@($htmldom->find('.article-content',0)->innertext()), "meta-tags") !== false){
                $row['keywords'] = trim(@($htmldom->find('.meta-tags', 0)->innertext()));
                $row['keywords'] = strip_tags($row['keywords']);
                $row['keywords'] = str_replace(PHP_EOL, "", trim($row['keywords']));
                $row['keywords'] = str_replace("&nbsp;", "", trim($row['keywords']));
                $row['keywords'] = str_replace(" ", ",", trim($row['keywords']));
                $tmp_arr = explode(",", $row['keywords']);
                $row['keywords'] = "";
                foreach ($tmp_arr as $str) {
                  if(trim($str)){
                    $row['keywords'] .= $str.",";
                  }
                }
                $row['keywords'] = rtrim($row['keywords'], ",");
                $row['keywords'] = $row['keywords'] ? $row['keywords'] : $row['name'];
              }
             
              if(strpos(@($htmldom->find('.article-content',0)->innertext()), "title") !== false && strpos(@($htmldom->find('.article-content .title',0)->innertext()), "p") !== false){
                $tmp_info = trim(@($htmldom->find('.article-content .title p', 0)->innertext()));
                $tmp_info = strip_tags($tmp_info);
                $tmp_info = str_replace("年", "-", $tmp_info);
                $tmp_info = str_replace("月", "-", $tmp_info);
                $tmp_info = str_replace("日", "", $tmp_info);
                preg_match("/([0-9]{4})-([0-9]{2})-([0-9]{2})(\s)+([0-9]{2}):([0-9]{2}):([0-9]{2})/", $tmp_info, $tmp_info);
                $row['ctime'] = $tmp_info['0'] ? $tmp_info['0'] : date('Y-m-d H:i:s');
              } 

              if(strpos($htmlsource, 'id="content"') !== false){
                if(strpos($htmldom->find('#content', 0)->innertext(), 'art_xg') !== false){
                  $htmldom->find('#content .art_xg', 0)->outertext = "";
                }
                if(strpos($htmldom->find('#content', 0)->innertext(), 'jb51ewm') !== false){
                  $htmldom->find('#content .jb51ewm', 0)->outertext = "";
                }
                if(strpos($htmldom->find('#content', 0)->innertext(), 'jbTestPos') !== false){
                  $htmldom->find('#content .jbTestPos', 0)->outertext = "";
                }

                $row['content'] = addslashes(trim(@($htmldom->find('#content', 0)->innertext())));
              }
              $htmldom->clear();

              // echo '<pre>';print_r($row);exit;
              if($row['source_url'] && $row['name'] && $row['content']){
                try{
                  $id = $this->_addData($row);
                  if(strpos($id, "repeat") === false){
                    $log_str = date('Y-m-d H:i:s')."-->抓取{$link} - {$row['source_url']} - {$row['name']}成功。ID:{$id}".PHP_EOL;
                    echo $log_str;
                    file_put_contents($log_file, $log_str, FILE_APPEND);
                    $this->_ssdb->zset(__FUNCTION__."-list", $row['source_url_md5'], $id);
                  }else{
                    $log_str = date('Y-m-d H:i:s')."-->".$link.'->'.$id.$row['source_url'].'->md5:'.$row['source_url_md5'].PHP_EOL;
                    echo $log_str;
                    file_put_contents($log_file, $log_str, FILE_APPEND);
                    // $this->_pdo->last_query();
                  }
                }catch(Exception $e){
                  var_dump($e->getmessage());exit(0);
                }

              }
            }
            $this->_htmldom->clear();
            $min_page++;
          }
        }
      }
    }

    //phpchina.com
    public function get_phpchina(){
      // $this->_ssdb->zclear(__FUNCTION__."-list");
      $log_file = $this->_logdir.__CLASS__."-".__FUNCTION__."-".date('Ymd').".log";
      $log_str = date('Y-m-d H:i:s')."-->开始抓取，".PHP_EOL;
      file_put_contents($log_file, $log_str, FILE_APPEND);
      $cur = 1;
      $max = 20;
      while($cur<=$max){
        $link = "http://www.phpchina.com/portal.php?mod=listall&start={$cur}";
        $json = file_get_contents($link);
        if($json){
            echo date('Y-m-d H:i:s')."=>".$link.PHP_EOL;
            $arr = json_decode($json, true);
            foreach ($arr as $val) {
              $row = array();
              $d_url = "http://www.phpchina.com/portal.php?mod=view&aid=".$val['aid'];
              $row['cate_id'] = 0;        
              $row['cate_name'] = isset($val['catname']) ? trim($val['catname']) : "";       
              $row['source'] = 'PHPChina开发者社区';        
              $row['name'] = isset($val['title']) ? trim($val['title']) : "";        
              $row['icon'] = isset($val['pic']) ? "http://www.phpchina.com/data/attachment/".trim($val['pic']) : "";        
              $row['visits'] = 0;        
              $row['is_show'] = 1;        
              $row['is_top'] = 2;        
              $row['source_url'] = $d_url;
              $row['source_url_md5'] = md5($row['source_url']);
              $row['keywords'] = $row['name'];
              $row['description'] = isset($val['summary']) ? trim($val['summary']) : "";
              $row['ctime'] = isset($val['dateline']) ? trim($val['dateline']) : "";   
              // var_dump($this->_ssdb->zget(__FUNCTION__."-list", $row['source_url_md5']));
              //   echo PHP_EOL;
              //   continue;
              // var_dump($this->_ssdb->zrange(__FUNCTION__."-list", 0, 100));exit;
              if($this->_ssdb->zget(__FUNCTION__."-list", $row['source_url_md5'])){
                $log_str = date('Y-m-d H:i:s')."-->".$d_url."->".$row['source_url_md5']."网址已存在，".PHP_EOL;
                echo $log_str;
                file_put_contents($log_file, $log_str, FILE_APPEND);
                continue;
              }

              $row['content'] = "";
              $content = file_get_contents($d_url);上
              preg_match_all('/<span class="number">(\d+)<\/span>/i', $content, $text);
              if(!isset($text['1']['0'])){
                echo date('Y-m-d H:i:s')."=>".$d_url." 'number' is not regex!".PHP_EOL;
                continue;
              }
              $row['visits'] = $text['1']['0'];

              $pattern = '/<div class="main_first">([\s|\S]*)<\/div>(?:(\s)*)<div class="author">/i';
              preg_match_all($pattern, $content, $text);
              if(!isset($text['1']['0'])){
                echo date('Y-m-d H:i:s')."=>".$d_url." 'main_first' is not regex!".PHP_EOL;
                continue;
              }
              preg_match_all('/<\/div>([\s|\S]*)/i', $text['1']['0'], $tmp);

              if(!isset($tmp['1']['0'])){
                echo date('Y-m-d H:i:s')."=>".$d_url." 'clear content' is not regex!".PHP_EOL;
                continue;
              }
              $row['content'] = addslashes(trim(str_replace("<!--文章内容-->", "", $tmp['1']['0'])));
              
              if($row['source_url'] && $row['name'] && $row['content']){
                try{
                  $id = $this->_addData($row);
                  // var_dump($id);
                  if(strpos($id, "repeat") === false){
                    $log_str = date('Y-m-d H:i:s')."-->抓取{$link} - {$row['source_url']} - {$row['name']}成功。ID:{$id}".PHP_EOL;
                    echo $log_str;
                    file_put_contents($log_file, $log_str, FILE_APPEND);
                    $this->_ssdb->zset(__FUNCTION__."-list", $row['source_url_md5'], time());
                  }else{
                    $log_str = date('Y-m-d H:i:s')."-->".$link.'->'.$id.'->'.$row['source_url'].'->md5:'.$row['source_url_md5'].PHP_EOL;
                    echo $log_str;
                    file_put_contents($log_file, $log_str, FILE_APPEND);
                    // $this->_pdo->last_query();
                  }
                }catch(Exception $e){
                  var_dump($e->getmessage());exit(0);
                }
              }
              // exit;
            }
        }else{
          echo date('Y-m-d H:i:s')."=> finished!".PHP_EOL;
          break;
        }
        $cur++;
      }
    }

    //meituan.com
    public function get_meituan(){
      // $this->_ssdb->zclear(__FUNCTION__."-list");
      $log_file = $this->_logdir.__CLASS__."-".__FUNCTION__."-".date('Ymd').".log";
      $log_str = date('Y-m-d H:i:s')."-->开始抓取，".PHP_EOL;
      file_put_contents($log_file, $log_str, FILE_APPEND);
      $link = "https://tech.meituan.com/?l=1600";
      $html = file_get_contents($link);
      // echo $html;
      $pattern = '/<article class="post post-with-tags">([\s\S]*?)<\/article>/i';
      preg_match_all($pattern, $html, $articles);
      if($articles && isset($articles['1'])){
         foreach ($articles['1'] as $item) {
            $row = array();
            $row['cate_id'] = 0;        
            $row['visits'] = 0;        
            $row['is_show'] = 1;        
            $row['is_top'] = 2;        
            $row['source'] = '美团点评技术团队';        

            $row['cate_name'] = "";       
            $row['name'] = "";        
            $row['icon'] = "";        
            $row['source_url'] = "";
            $row['source_url_md5'] = "";
            $row['content'] = "";
            $row['description'] = "";
            $row['ctime'] = "";   

            preg_match('/<header class="post-title">(?:[\s]*)<a href=\"([\s\S]*)\">([\s\S]*)<\/a>([\s]*)<\/header>/i', $item, $tmp);
            $row['source_url'] = "https://tech.meituan.com".(isset($tmp['1']) ? trim($tmp['1']) : "");
            $row['source_url_md5'] = md5($row['source_url']);
            $row['name'] = isset($tmp['2']) ? trim($tmp['2']) : "";

            if($this->_ssdb->zget(__FUNCTION__."-list", $row['source_url_md5'])){
              $log_str = date('Y-m-d H:i:s')."-->".$d_url."->".$row['source_url_md5']."网址已存在，".PHP_EOL;
              echo $log_str;
              file_put_contents($log_file, $log_str, FILE_APPEND);
              continue;
            }

            preg_match('/<p class="post-abstract">([\s|\S]*)<\/p>/i', $item, $tmp);
            $row['description'] = isset($tmp['1']) ? str_replace("...", "", trim($tmp['1'])) : "";

            preg_match_all('/<span class="tag_name">([\s\S]*?)<\/span>/i', $item, $tmp);
            if($tmp && isset($tmp['1'])){
              foreach ($tmp['1'] as $tag) {
                $row['cate_name'] .= $tag.",";
              }
              $row['cate_name'] = trim($row['cate_name'], ",");
            }

            $html = file_get_contents($row['source_url']);
            // $html = file_get_contents(dirname(__FILE__)."/content1.log");
            preg_match('/<span class="date">([\s\S]*?)<\/span>/i', $html, $tmp);
            $row['ctime'] = isset($tmp['1']) ? date('Y-m-d H:i:s', strtotime(trim($tmp['1']))) : "";
            
            preg_match('/<div class=\"article__content\">([\s\S]*?)<div class=\"hidden-mobile\">/i', $html, $tmp);
            $row['content'] = isset($tmp['1']) ? addslashes(trim($tmp['1'])) : "";
            // print_r($row['content']);

            // print_r($row);
            // exit;
            if($row['source_url'] && $row['name'] && $row['content']){
              try{
                $id = $this->_addData($row);
                // var_dump($id);
                if(strpos($id, "repeat") === false){
                  $log_str = date('Y-m-d H:i:s')."-->抓取{$row['source_url']} - {$row['name']}成功。ID:{$id}".PHP_EOL;
                  echo $log_str;
                  file_put_contents($log_file, $log_str, FILE_APPEND);
                  $this->_ssdb->zset(__FUNCTION__."-list", $row['source_url_md5'], time());
                }else{
                  $log_str = date('Y-m-d H:i:s')."-->".$id.'->'.$row['source_url'].'->md5:'.$row['source_url_md5'].PHP_EOL;
                  echo $log_str;
                  file_put_contents($log_file, $log_str, FILE_APPEND);
                  // $this->_pdo->last_query();
                }
              }catch(Exception $e){
                var_dump($e->getmessage());exit(0);
              }
            }
         }
      }
    }

    // itpub.net
    public function get_itpub(){
      // $this->_ssdb->zclear(__FUNCTION__."-list");
      $log_file = $this->_logdir.__CLASS__."-".__FUNCTION__."-".date('Ymd').".log";
      $log_str = date('Y-m-d H:i:s')."-->开始抓取，".PHP_EOL;
      file_put_contents($log_file, $log_str, FILE_APPEND);

      $arr = array(
        '1',
        '2',
        '7',
        '24',
        '25',
        '48',
        '57',
        '62',
        '77',
        '86',
        '87',
        '94',
      );
      // http://blog.itpub.net/blog/getmore/11/?page=1
      foreach ($arr as $cateid) {
        $cur = 1;
        $max = 0;
        do{
            $link = "http://blog.itpub.net/blog/getmore/".$cateid."/?page=".$cur;
            $json = file_get_contents($link);
            var_dump($json);exit;
            if($json){
                echo date('Y-m-d H:i:s')."=>".$link.PHP_EOL;
                $arr = json_decode($json, true);
                print_r($arr);exit;
                if(!isset($arr['data']['items']) || !$arr['data']['items']){
                  echo date('Y-m-d H:i:s')."=>".$link." cur finished".PHP_EOL;
                  break;
                }
                if(!$max){
                  $max = ceil($arr['data']['total']/$arr['data']['pagesize']);
                }
                foreach ($arr as $val) {
                  $row = array();
                  $d_url = "http://www.phpchina.com/portal.php?mod=view&aid=".$val['aid'];
                  $row['cate_id'] = 0;        
                  $row['cate_name'] = isset($val['catname']) ? trim($val['catname']) : "";       
                  $row['source'] = 'PHPChina开发者社区';        
                  $row['name'] = isset($val['title']) ? trim($val['title']) : "";        
                  $row['icon'] = isset($val['pic']) ? "http://www.phpchina.com/data/attachment/".trim($val['pic']) : "";        
                  $row['visits'] = 0;        
                  $row['is_show'] = 1;        
                  $row['is_top'] = 2;        
                  $row['source_url'] = $d_url;
                  $row['source_url_md5'] = md5($row['source_url']);
                  $row['keywords'] = $row['name'];
                  $row['description'] = isset($val['summary']) ? trim($val['summary']) : "";
                  $row['ctime'] = isset($val['dateline']) ? trim($val['dateline']) : "";   
                  // var_dump($this->_ssdb->zget(__FUNCTION__."-list", $row['source_url_md5']));
                  //   echo PHP_EOL;
                  //   continue;
                  // var_dump($this->_ssdb->zrange(__FUNCTION__."-list", 0, 100));exit;
                  if($this->_ssdb->zget(__FUNCTION__."-list", $row['source_url_md5'])){
                    $log_str = date('Y-m-d H:i:s')."-->".$d_url."->".$row['source_url_md5']."网址已存在，".PHP_EOL;
                    echo $log_str;
                    file_put_contents($log_file, $log_str, FILE_APPEND);
                    continue;
                  }

                  $row['content'] = "";
                  $content = file_get_contents($d_url);
                  preg_match_all('/<span class="number">(\d+)<\/span>/i', $content, $text);
                  if(!isset($text['1']['0'])){
                    echo date('Y-m-d H:i:s')."=>".$d_url." 'number' is not regex!".PHP_EOL;
                    continue;
                  }
                  $row['visits'] = $text['1']['0'];

                  $pattern = '/<div class="main_first">([\s|\S]*)<\/div>(?:(\s)*)<div class="author">/i';
                  preg_match_all($pattern, $content, $text);
                  if(!isset($text['1']['0'])){
                    echo date('Y-m-d H:i:s')."=>".$d_url." 'main_first' is not regex!".PHP_EOL;
                    continue;
                  }
                  preg_match_all('/<\/div>([\s|\S]*)/i', $text['1']['0'], $tmp);

                  if(!isset($tmp['1']['0'])){
                    echo date('Y-m-d H:i:s')."=>".$d_url." 'clear content' is not regex!".PHP_EOL;
                    continue;
                  }
                  $row['content'] = addslashes(trim(str_replace("<!--文章内容-->", "", $tmp['1']['0'])));
                  
                  if($row['source_url'] && $row['name'] && $row['content']){
                    try{
                      $id = $this->_addData($row);
                      // var_dump($id);
                      if(strpos($id, "repeat") === false){
                        $log_str = date('Y-m-d H:i:s')."-->抓取{$link} - {$row['source_url']} - {$row['name']}成功。ID:{$id}".PHP_EOL;
                        echo $log_str;
                        file_put_contents($log_file, $log_str, FILE_APPEND);
                        $this->_ssdb->zset(__FUNCTION__."-list", $row['source_url_md5'], time());
                      }else{
                        $log_str = date('Y-m-d H:i:s')."-->".$link.'->'.$id.'->'.$row['source_url'].'->md5:'.$row['source_url_md5'].PHP_EOL;
                        echo $log_str;
                        file_put_contents($log_file, $log_str, FILE_APPEND);
                        // $this->_pdo->last_query();
                      }
                    }catch(Exception $e){
                      var_dump($e->getmessage());exit(0);
                    }
                  }
                  // exit;
                }
            }
            $cur++;
        }while ($cur <= $max);
      }
      
    }

    // iteye.com
    public function get_iteye(){
      // $this->_ssdb->zclear(__FUNCTION__."-list");
      $log_file = $this->_logdir.__CLASS__."-".__FUNCTION__."-".date('Ymd').".log";
      $log_str = date('Y-m-d H:i:s')."-->开始抓取，".PHP_EOL;
      file_put_contents($log_file, $log_str, FILE_APPEND);

      $arr = array(
        'http://www.iteye.com/news/category/web',
        'http://www.iteye.com/news/tag/%E5%AE%89%E5%85%A8',
        'http://www.iteye.com/news/tag/PHP',
        'http://www.iteye.com/news/category/internet',
        'http://www.iteye.com/news/category/opensource',
        'http://www.iteye.com/news/tag/Linux',
        'http://www.iteye.com/news/tag/Ubuntu',
        'http://www.iteye.com/news/category/database',
      );
      
      foreach ($arr as $cate_url) {
        $cur = 1;
        $max = 0;
        do{
            sleep(5);
            $link = $cate_url."?page=".$cur;
            // $html = file_get_contents($link);
            $html = $this->_curlGet($link);
            echo $link;
            echo $html;exit;
            $pattern = '/<div class="news clearfix">([\s\S]*?)<div class="news clearfix">/i';
            preg_match_all($pattern, $html, $articles);
            if($articles && isset($articles['1'])){
               foreach ($articles['1'] as $item) {
                  $row = array();
                  $row['cate_id'] = 0;        
                  $row['visits'] = 0;        
                  $row['is_show'] = 1;        
                  $row['is_top'] = 2;        
                  $row['source'] = '美团点评技术团队';        

                  $row['cate_name'] = "";       
                  $row['name'] = "";        
                  $row['icon'] = "";        
                  $row['source_url'] = "";
                  $row['source_url_md5'] = "";
                  $row['content'] = "";
                  $row['description'] = "";
                  $row['ctime'] = "";   

                  preg_match('/<h3>(?:[\s\S]*)<a href=\'([\s\S]*)\' title="([\s\S]*)">(?:[\s\S]*)<\/a>(?:[\s]*)<\/h3>/i', $item, $tmp);
                  $row['source_url'] = "http://www.iteye.com".(isset($tmp['1']) ? trim($tmp['1']) : "");
                  $row['source_url_md5'] = md5($row['source_url']);
                  $row['name'] = isset($tmp['2']) ? trim($tmp['2']) : "";

                  if($this->_ssdb->zget(__FUNCTION__."-list", $row['source_url_md5'])){
                    $log_str = date('Y-m-d H:i:s')."-->".$link."->".$row['source_url_md5']."网址已存在，".PHP_EOL;
                    echo $log_str;
                    file_put_contents($log_file, $log_str, FILE_APPEND);
                    continue;
                  }

                  preg_match('/<div>([\s\S]*?)<\/div>/i', $item, $tmp);
                  $row['description'] = isset($tmp['1']) ? str_replace("...", "", trim($tmp['1'])) : "";

                  preg_match('/<div class="news_tag">([\s\S]*?)<\/div>/i', $item, $tmp);
                  if($tmp && isset($tmp['1'])){
                    $tmp = strip_tags($tmp['1']);
                    $tmp = str_replace(" ", "", trim($tmp));
                    $tmp = str_replace(PHP_EOL, ",", trim($tmp));
                    $row['cate_name'] = $tmp;
                  }

                  preg_match('/<span class=\'view\'>([\s\S]*?)<\/span>/i', $item, $tmp);
                  $row['visits'] = isset($tmp['1']) ? str_replace("人浏览", "", str_replace("有", "", trim($tmp['1']))) : "";
                  $html = file_get_contents($row['source_url']);
                  preg_match('/<span class=\'date\'>([\s\S]*?)<\/span>/i', $html, $tmp);
                  $row['ctime'] = isset($tmp['1']) ? date('Y-m-d H:i:s', strtotime(trim($tmp['1']))) : "";

                  preg_match('/<div class="iteye-blog-content-contain" style="font-size: 14px;">([\s\S]*?)<\/div>([\s]*?)<\/div>([\s]*?)<div id="news_recommended_n2">/i', $html, $tmp);
                  $row['content'] = isset($tmp['1']) ? addslashes(trim($tmp['1'])) : "";
                  
                  if($row['source_url'] && $row['name'] && $row['content']){
                    try{
                      $id = $this->_addData($row);
                      // var_dump($id);
                      if(strpos($id, "repeat") === false){
                        $log_str = date('Y-m-d H:i:s')."-->抓取{$row['source_url']} - {$row['name']}成功。ID:{$id}".PHP_EOL;
                        echo $log_str;
                        file_put_contents($log_file, $log_str, FILE_APPEND);
                        $this->_ssdb->zset(__FUNCTION__."-list", $row['source_url_md5'], time());
                      }else{
                        $log_str = date('Y-m-d H:i:s')."-->".$id.'->'.$row['source_url'].'->md5:'.$row['source_url_md5'].PHP_EOL;
                        echo $log_str;
                        file_put_contents($log_file, $log_str, FILE_APPEND);
                        // $this->_pdo->last_query();
                      }
                    }catch(Exception $e){
                      var_dump($e->getmessage());exit(0);
                    }
                  }
               }
            }else{
              echo 'empty '.$link.PHP_EOL;
              break;
            }
            
            $cur++;
        }while ($cur <= $max);
      }
      
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
    case 'syncTagRel': $grab->syncTagRel();break;

    case 'get_2ctocom': $grab->get_2ctocom();break;

    case 'get_cnblogs': $grab->get_cnblogs();break;

    case 'get_itdaan': $grab->get_itdaan();break;

    case 'get_infoq_news': $grab->get_infoq_news();break;

    case 'get_infoq_article': $grab->get_infoq_article();break;

    case 'get_jb51_list': $grab->get_jb51_list();break;

    case 'get_jb51_list_icon': $grab->get_jb51_list_icon();break;

    case 'get_phpchina': $grab->get_phpchina();break;

    case 'get_meituan': $grab->get_meituan();break;
    
    case 'get_itpub': $grab->get_itpub();break;

    case 'get_iteye': $grab->get_iteye();break;
  }

