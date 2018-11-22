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

    //消费队列
    public function consumeQuene(){
      $cur_num = $this->_ssdb->get('hubphp_article_quene_max_log_id');
      $cur_num = isset($cur_num) && $cur_num ? $cur_num : 0;
      // $this->_ssdb->set('hubphp_article_quene_max_log_id', $cur_num);exit;
      while (1) {
        $arr = $this->_ssdb->qpop_back('hubphp_article_quene_log', 100);
        if(!$arr){
          echo date('Y-m-d H:i:s')."->id:".$cur_num.", zsize:".$this->_ssdb->zsize('hubphp_article_url_list').", waiting...".PHP_EOL;
          sleep(5);
        }else{
          $filename = 2000000 + intval($cur_num/20000);
          $str = "";
          foreach ($arr as $val) {
            $str .= $val;
          }
          file_put_contents($this->_dbdir.$filename.".log", $str, FILE_APPEND);
          echo date('Y-m-d H:i:s')."->id:".$cur_num.", str length:".strlen($str).", zsize:".$this->_ssdb->zsize('hubphp_article_url_list').PHP_EOL;
          $cur_num++;
          $this->_ssdb->set('hubphp_article_quene_max_log_id', $cur_num);
          unset($str);
        }
      }
    }

    //处理数据库记录到队列
    public function syncContentToQuene(){
      exit;
      $cur_id = $this->_ssdb->get('hubphp_article_max_id');
      $cur_id = isset($cur_id) && $cur_id ? $cur_id : 0;
      // print_r($cur_id);exit;
      $sql = "select a.article_id, a.name, a.cate_name, a.icon, a.author, a.source_url, a.keywords, a.visits, a.ctime, c.content from hubphp_article a INNER JOIN hubphp_article_content c ON a.article_id = c.article_id where a.article_id > ".$cur_id." limit 1000";
      $rs = $this->_pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
      if($rs){
        $last_id = 0;
        foreach ($rs as $val) {
          $last_id = $val['article_id'];
          $val['keywords'] = $val['keywords'] ? $val['keywords'] : $val['cate_name'];
          $str = $val['name']."#=#".$val['icon']."#=#".$val['author']."#=#".$val['source_url']."#=#".$val['keywords']."#=#".$val['visits']."#=#".$val['ctime']."#=#".$val['content']."##=##".PHP_EOL.PHP_EOL;
          echo date('Y-m-d H:i:s')."->".$val['article_id'].", length:".strlen($str).PHP_EOL;
          $this->_ssdb->qpush_front('hubphp_article_quene_log', $str);
          $this->_ssdb->zset('hubphp_article_url_list', md5($val['source_url']), 1);
          unset($str);
          unset($rs);
          usleep(10000);
        }
        $this->_ssdb->set('hubphp_article_max_id', $last_id);
        $this->syncContentToQuene();
      }else{
        echo 'finnish!'.PHP_EOL.PHP_EOL;
        exit;
      }
    }

    public function getUrlListInfo(){
      echo $this->_ssdb->qsize('hubphp_article_quene_log');exit;
      while(1){
        // $this->_ssdb->set('hubphp_article_max_id', 0);
        // $this->_ssdb->zclear('hubphp_article_url_list');
        // $str = $this->_ssdb->qpop_back('hubphp_article_quene_log');
        // if(!$str){
        //   break;
        // }
        $zsize = $this->_ssdb->zsize('hubphp_article_url_list');
        // $info = $this->_ssdb->zrrange('hubphp_article_url_list', $zsize-10, 10);
        // echo '<pre>';
        echo date('Y-m-d H:i:s')."->".$zsize.PHP_EOL;
        // print_r($info);
        sleep(1);
      }
    }

    //处理数据库记录到队列
    public function syncLogToDb(){
      $cur_id = $this->_ssdb->get('hubphp_log_file_id');
      $cur_id = isset($cur_id) && $cur_id ? $cur_id : 2000074;
      // print_r($cur_id);exit;
      for (; $cur_id <= 2000115; $cur_id++) { 
        $str = file_get_contents($this->_dbdir.$cur_id.".log");
        $line = explode("##=##", $str);
        if($line){
            foreach ($line as $item) {
              $val = array();
              $arr = explode("#=#", $item);
              $val['name'] = $arr['0'];
              $val['icon'] = $arr['1'];
              $val['author'] = $arr['2'];
              $val['source_url'] = $arr['3'];
              $val['source_url_md5'] = md5($arr['3']);
              $val['keywords'] = $arr['4'];
              $val['visits'] = $arr['5'];
              $val['ctime'] = $arr['6'];
              $val['content'] = $arr['7'];
              
              $tmp = strip_tags($val['content']);
              $tmp = str_replace(PHP_EOL, "", str_replace(" ", "", $tmp));
              $tmp = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/","",$tmp);
              $tmp = mb_substr($tmp, 0, 300);
              $val['description'] = addslashes($tmp);
              
              $val['utime'] = date('Y-m-d H:i:s');
              // $str = $val['name']."#=#".$val['icon']."#=#".$val['author']."#=#".$val['source_url']."#=#".$val['keywords']."#=#".$val['visits']."#=#".$val['ctime']."#=#".$val['content']."##=##".PHP_EOL.PHP_EOL;
              if($article_id = $this->_insertData($val)){
                echo date('Y-m-d H:i:s')."-> insert succ, id:".$article_id.", ".$val['name'].", source_url:".$val['source_url'].PHP_EOL;
              }else{
                echo date('Y-m-d H:i:s')."-> insert fail, id:".$article_id.", ".$val['name'].", source_url:".$val['source_url'].PHP_EOL;
              }
            }
        }
        $cur_id++;
        $this->_ssdb->set('hubphp_log_file_id', $cur_id);
      }
    }

        public function syncFile(){
      $cnum = 74;
      $num = $cnum;
      $file_path = dirname(__FILE__)."/content/";
      $file_path = "/mnt/xvdb1/www/v2ts.cn/log/hubphp/data/";
      $line = 0;
      while($num<=115){
        $filename = 2000000+$num;
        // $filename = str_pad($num, 6, '0', STR_PAD_LEFT)."_0";
        $file = $file_path.$filename.".log";
        if(!file_exists($file)){
          continue;
        }
        $str = file_get_contents($file);
        $arrs = explode("##=##", $str);
        // $line += count($arrs);
        if($arrs){
          $c_line = 0;
          foreach ($arrs as $cstr) {
            ++$c_line;
            if($filename == str_pad($cnum, 6, '0', STR_PAD_LEFT)."_0" && $c_line < 2386){
              echo $c_line.'->jump'.PHP_EOL;
              continue;
            }
            $line += 1;
            // echo date('Y-m-d H:i:s')."->".$filename.'->'.$line.PHP_EOL;
            // continue;
            $arr = explode("#=#", $cstr);
            $row = array();
            //$info['name']."#=#".$info['icon']."#=#".$info['author']."#=#".$info['source_url']."#=#".$info['keywords']."#=#".$info['visits']."#=#".$info['ctime']."#=#".$info['content']."##=##".PHP_EOL.PHP_EOL;
            $row['cate_id'] = 0;        
            $row['visits'] = isset($arr['5']) ? intval($arr['5']) : "";        
            $row['is_show'] = 1;        
            $row['is_top'] = 2;        
            $row['source'] = '';        
            $row['source_url'] = isset($arr['3']) ? trim($arr['3']) : ""; 
            // echo $row['source_url'].PHP_EOL;
            if(!$row['source_url'] || in_array($row['source_url'], $this->_black_list)){
               continue;
            }

            $row['cate_name'] = isset($arr['4']) ? trim($arr['4']) : "";
            $arr['name'] = ""; 
            if(isset($arr['0'])){
                $arr['0'] = strip_tags($arr['0']);
                $arr['0'] = str_replace(PHP_EOL, "", $arr['0']);
                $arr['0'] = str_replace("\N", "", $arr['0']);
                $arr['0'] = str_replace("\R", "", $arr['0']);
                $arr['0'] = str_replace("\\r", "", $arr['0']);
                $arr['0'] = str_replace("\\n", "", $arr['0']);
                $row['name'] =  trim($arr['0']);      
            }    
            $row['icon'] = isset($arr['1']) ? trim($arr['1']) : "";        
            $row['author'] = isset($arr['2']) ? trim($arr['2']) : "";        
            $row['icon'] = "";        
            $row['source_url_md5'] = md5($row['source_url']);
            $row['content'] = isset($arr['7']) ? trim($arr['7']) : ""; 
            $row['keywords'] = isset($arr['4']) ? trim($arr['4']) : "";
            $row['content'] = str_replace(PHP_EOL, "", $row['content']);
            $row['content'] = str_replace("\N", "", $row['content']);
            $row['content'] = str_replace("\R", "", $row['content']);
            $row['content'] = str_replace("\\r", "", $row['content']);
            $row['content'] = str_replace("\\n", "", $row['content']);
            $row['content'] = str_replace("LF", "", $row['content']);
            $row['content'] = str_replace("CR", "", $row['content']);

            $tmp = strip_tags($row['content']);
            $tmp = str_replace(PHP_EOL, "", str_replace(" ", "", $tmp));
            $tmp = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/","",$tmp);
            $tmp = mb_substr($tmp, 0, 300);
            $row['description'] = addslashes($tmp);
            $row['ctime'] = isset($arr['6']) ? date('Y-m-d H:i:s', strtotime($arr['6'])) : "";
            $row['utime'] = date('Y-m-d H:i:s');
            $row['content'] = mb_convert_encoding(addslashes(trim($row['content'])), 'utf-8');
            $c_row['content'] = $row['content'];
            unset($row['content']);
            $c_row['source_url_md5'] = $row['source_url_md5'];

           if($row['source_url'] && $row['name']){
              try{
                $article_id = $this->_addData($row);
                // var_dump($row['utime']);
                // var_dump($article_id);
                // $this->_pdo->last_query();
                // exit;
                if(strpos($article_id, "repeat") === false){
                  $c_row['article_id'] = $article_id;
                  $this->_addData($c_row, 'hubphp_article_content');
                  $log_str = date('Y-m-d H:i:s')."-->{$row['source_url']} - {$row['name']}成功。ID:{$article_id}"."=>".$line."=>".$filename."=>".$c_line.PHP_EOL;
                  echo $log_str;
                }else{
                  $log_str = date('Y-m-d H:i:s')."-->".$article_id."=>".$row['source_url'].'->md5:'.$row['source_url_md5']."=>".$line."=>".$filename."=>".$c_line.PHP_EOL;
                  echo $log_str;
                  // $this->_pdo->last_query();
                }
              }catch(Exception $e){
                var_dump($e->getmessage());
                // exit(0);
              }
            }
            exit;
            echo PHP_EOL.'-------'.PHP_EOL;
            if($line%100==0){
              usleep(500);
            }
            // exit;
          }
        }
        $num++;
      }
    }

    public function syncContent($id = 0){
      // $this->_pdo->
      $sql = "select article_id, content from hubphp_article where  article_id > ".$id."  and description = '' and length(content) > 10 order by article_id asc limit 1";
      $info = $this->_pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
      if($info){
          echo date('Y-m-d H:i:s').'->do '.$info['article_id'].PHP_EOL;

          $tmp = strip_tags($info['content']);
          $tmp = str_replace(PHP_EOL, "", str_replace(" ", "", $tmp));
          $tmp = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/","",$tmp);
          $tmp = mb_substr($tmp, 0, 300);
          $tmp = addslashes($tmp);

          $sql = "update hubphp_article set description = '".$tmp."' where article_id = '".$info['article_id']."'";
          $rs = $this->_pdo->query($sql);
          // var_dump($rs);

          $sql = "select article_id, content from hubphp_article_content where article_id = '".$info['article_id']."' limit 1";
          $rs = $this->_pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
          if(!$rs){
            echo date('Y-m-d H:i:s').'->insert '.$info['article_id'].PHP_EOL;
            $sql = "insert into hubphp_article_content(article_id, content) values('".$info['article_id']."', '".$info['content']."')";
            $rs = $this->_pdo->query($sql);
            var_dump($this->_pdo->lastInsertId());
          }else{
            echo date('Y-m-d H:i:s').'->repeat '.$info['article_id'].PHP_EOL.PHP_EOL;
            // exit;
          }
          // usleep(100000);
          $this->syncContent($info['article_id']);
      }else{
        echo date('Y-m-d H:i:s').'->finish!';
      }
    }

    public function syncContentMid(){
      $sql = "select article_id from hubphp_article where article_id not in(select article_id from hubphp_article_content);";
      $rs = $this->_pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
      if($rs){
          foreach ($rs as $val) {
            // echo date('Y-m-d H:i:s').'->do '.$val['article_id'].PHP_EOL;
            $sql = "select article_id, content from hubphp_article where article_id = '".$val['article_id']."' limit 1";
            $info = $this->_pdo->query($sql)->fetch(PDO::FETCH_ASSOC);

            $sql = "select article_id, content from hubphp_article_content where article_id = '".$info['article_id']."' limit 1";
            $rs = $this->_pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
            if(!$rs){
              echo date('Y-m-d H:i:s').'->insert '.$info['article_id'].PHP_EOL;
              $sql = "insert into hubphp_article_content(article_id, content) values('".$info['article_id']."', '".addslashes($info['content'])."')";
              // echo $sql;exit;
              $rs = $this->_pdo->query($sql);
              $id = $this->_pdo->lastInsertId();
              //   var_dump($id);exit;
              // if($id){
              //   echo $id.PHP_EOL;
              // }else{
              //   var_dump($id);
              //   exit;
              // }
            }else{
              echo date('Y-m-d H:i:s').'->repeat '.$info['article_id'].PHP_EOL.PHP_EOL;
              usleep(1000000);
              // exit;
            }
          }
          // $this->syncContentMid();
      }else{
        echo date('Y-m-d H:i:s').'->finish!';
      }
    }

    public function syncTagRel($aid = 83774){
      $sql = "select article_id, name, keywords from hubphp_article where article_id > '".$aid."' order by article_id asc limit 1";
      $article = $this->_pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
      if($article){
        // $sql = "select content from hubphp_article_content where article_id = '".$article['article_id']."' limit 1";
        // $content = $this->_pdo->query($sql)->fetch(PDO::FETCH_ASSOC);

        $sql = "select tag_id, tag_val from hubphp_tags order by tag_id asc;";
        $tags = $this->_pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        if($tags){
            foreach ($tags as $val) {
              $article_id = $article['article_id'];
              $tag_id = $val['tag_id'];
              $tag = $val['tag_val'];
              
              echo date('Y-m-d H:i:s').'->do '.$tag_id."=>".$article_id.PHP_EOL;
          
              $is_tag = 0;
              $partten = '/'.strtolower($tag).'/i';
              preg_match($partten, strtolower($article['name']), $rs);
              // var_dump($rs);
              if(isset($rs['0']) && !empty($rs['0'])){
                 $is_tag = 1;
              }

              if($is_tag == 0){
                preg_match($partten, strtolower($article['keywords']), $rs);
                // var_dump($rs);
                if(isset($rs['0']) && !empty($rs['0'])){
                  $is_tag = 1;
                }
              }

              // if($is_tag == 0){
              //   preg_match($partten, strtolower($content['content']), $rs);
              //   var_dump($rs);
              //   if(isset($rs['0']) && !empty($rs['0'])){
              //     $is_tag = 1;
              //   }
              // }
              
              echo date('Y-m-d H:i:s').'->is_tag '.$is_tag.PHP_EOL;
              if($is_tag){
                  $sql = "select rel_id from hubphp_article_tag_rel where tag_id = '".$tag_id."' and article_id = '".$article_id."' limit 1";
                  $rs = $this->_pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
                  if(!$rs){
                    $sql = "insert into hubphp_article_tag_rel(tag_id, article_id) values('".$tag_id."', '".$article_id."')";
                     // echo $sql;exit;
                    $this->_pdo->query($sql);
                    echo date('Y-m-d H:i:s').'->insert'.PHP_EOL;
                  }else{
                    echo date('Y-m-d H:i:s').'->repeat'.PHP_EOL;
                    usleep(1000000);
                    // exit;
                  }
              }
            }
            echo PHP_EOL.PHP_EOL;
            usleep(100000);
            $this->syncTagRel($article_id);
        }
      }else{
        echo date('Y-m-d H:i:s').'->finish!';
      }
    }
    
    //入库
    private function _insertData($data = array()){
      if(!$data){
        return 'empty';
      }
    
      $content = $data['content'];
      unset($data['content']);

      try{
        $sql = "select article_id from hubphp_article where source_url_md5 = '{$data['source_url_md5']}' limit 1";
        $rs = 0;
        // $rs = $this->_pdo->query($sql)->fetch();
        if(!$rs && !$rs['article_id']){
          $sql = "INSERT INTO hubphp_article(";
          $val_str = "";
          foreach ($data as $col => $val) {
            $sql .= "`".$col."`, ";
            $val_str .= "'".$val."', ";
            // $val_str .= "?, ";
          }
          $val_str = rtrim($val_str, ", ");
          $sql = rtrim($sql, ', ');
          // $sql = $sql.") values(".$val_str.") RETURNING article_id;"; 
          $sql = $sql.") values(".$val_str.")"; 
          // echo $sql;exit;
          $this->_pdo->beginTransaction();
          $this->_pdo->query($sql);
          $article_id = $this->_pdo->lastInsertId();
          if(intval($article_id) > 100000){
            //内容
            $sql = "INSERT INTO hubphp_article_content(`article_id`, `content`) values('".$article_id."', '".$content."');";
            $this->_pdo->query($sql);
          }
          $this->_pdo->commit();
          return $article_id;
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

  //业务调度开始
  $sys = new Sys();
  $argv = isset($argv) && isset($argv['1']) ? $argv : array();
  if(!$argv){
    echo 'empty method';exit;
  }

  $method = $argv['1'];
  switch ($method) {
    case 'syncTagRel': $sys->syncTagRel();break;

    case 'consumeQuene': $sys->consumeQuene();break;

    case 'syncContentToQuene': $sys->syncContentToQuene();break;

    case 'getUrlListInfo': $sys->getUrlListInfo();break;

    case 'syncLogToDb': $sys->syncLogToDb();break;
  }

