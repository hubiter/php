<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Index extends MY_Controller {
	private $_max_page = 20;
	
	public function __construct(){
		parent::__construct();
		$this->load->model('Article_model', 'article');
	}

	public function index($cate_id = "0")
	{	
		$cate = intval(trim($this->input->get('cate')));
		$cate_id = $cate ? $cate : $cate_id;
		$this->_data['cate_id'] = $cate_id;
		
		$tag_id = intval(trim($this->input->get('tag')));
		$this->_data['tag'] = $tag_id;
		
		$skey = trim($this->input->get('skey'));
		$this->_data['skey'] = $skey;
		
		$cur_page = intval(trim($this->input->get('page')));
		$cur_page = $cur_page ? $cur_page : 1;
		$this->_data['page'] = $cur_page;

		if($cur_page > $this->_max_page){
			$cur_page = $this->_max_page;
		}

		$f_name = "推荐";
		if($cate_id == 0 && $skey != ""){
			$f_name = "搜索";
		}
		$this->_data['f_name'] = $f_name;

		$this->_data['tags_list'] = $this->_getTagList($cate_id);
		$this->_data['right_top_list'] = $this->_getTopArticle($cate_id);

		$field = "article_id, name, cate_id, cate_name, icon, ctime, visits, description";
		$where = " is_show = 1 ";
		if($cate_id == 0){
			// $where .= " AND icon <> '' ";
			// $where .= " AND is_top = 1";
		}else{
			$where .= " AND cate_id = '".$cate_id."'";
		}

		if($tag_id){
			$a_id_str = "";
			$article_id_arr = $this->article->getArticleIdByTags($tag_id);
			// echo '<pre>';
			// print_r($article_id_arr);exit;
			if($article_id_arr){
				$article_id_arr = array_slice($article_id_arr, 0, 100);
				foreach ($article_id_arr as $val) {
					$a_id_str .= "'".$val['article_id']."',";
				}

				$a_id_str = trim($a_id_str, ",");
				$where .= " AND article_id in({$a_id_str})";
			}
		}

		if($skey){
			$where .= " AND (name LIKE '%".$skey."%')";
			// $where .= " AND (name LIKE '%".$skey."%' OR content LIKE '%".$skey."%')";
		}

		$order_by = "article_id";
		$order_by_dire = "DESC";
		$limit = 12;
		$offset = ($cur_page-1) * $limit;

		$article_count = $this->article->count($where);
		$total_num = isset($article_count['num']) ? (($article_count['num'] > $this->_max_page*$limit) ? $this->_max_page*$limit : $article_count['num']) : 0;
		$article_list = $this->article->lists($field, $where, $order_by, $order_by_dire, $offset, $limit);
		// echo $this->article->db->last_query();
		foreach ($article_list as $key => $val) {
			$article_list[$key]['name'] = str_replace($skey, "<font color='red'>".$skey."</font>", $val['name']);
			$article_list[$key]['name'] = str_replace(strtolower($skey), "<font color='red'>".strtolower($skey)."</font>", $article_list[$key]['name']);
			$article_list[$key]['name'] = str_replace(strtoupper($skey), "<font color='red'>".strtoupper($skey)."</font>", $article_list[$key]['name']);
			
			$article_list[$key]['description'] = str_replace($skey, "<font color='red'>".$skey."</font>", $val['description']);
			$article_list[$key]['description'] = str_replace(strtolower($skey), "<font color='red'>".strtolower($skey)."</font>", $article_list[$key]['description']);
			$article_list[$key]['description'] = str_replace(strtoupper($skey), "<font color='red'>".strtoupper($skey)."</font>", $article_list[$key]['description']);

			$article_list[$key]['description'] = mb_substr(strip_tags($article_list[$key]['description']), 0, 120);
		}
		
		$this->_data['article_list'] = $article_list;
		
		$url = "";
		if($cate_id){
			$url .= "cate=".$cate_id."&";
		}
		if($tag_id){
			$url .= "tag=".$tag_id."&";
		}
		if($skey){
			$url .= "skey=".$skey."&";
		}
		$url = trim($url, "&");
		if(!$cate_id && !$tag_id && !$skey){
			$url = "/index";
		}else{
			$url = "/index?".$url;
		}
		$config['base_url'] = $url;
		$config['total_rows'] = $total_num;
		$config['cur_page'] = $cur_page;
		$config['per_page'] = $limit;
		$page = $this->pagination($config);
		$this->_data['page_string'] = $page['page_string'];

		$this->load->view('header', $this->_data);
		$this->load->view('index', $this->_data);
		$this->load->view('footer', $this->_data);
	}

	public function detail($article_id = "")
	{	
		if(!$article_id){
			redirect('/');
		}

		$info = $this->article->detail($article_id);
		$content = $this->article->content($info['article_id']);
		$info['content'] = isset($content['content']) ? $content['content'] : "";
		if(!$info['article_id']){
			redirect('http://www.hubphp.com/');
		}
		$this->_data['cate_id'] = $info['cate_id'];

		$data = array('article_id' => $article_id, 'visits'=>$info['visits']+1);
		$this->article->edit($data); 
		
		$this->_data['tags_list'] = $this->_getTagList($info['cate_id']);
		$this->_data['right_top_list'] = $this->_getTopArticle($info['cate_id']);
		$this->_data['article_tags_list'] = $this->article->getTagsByArticleId($article_id);
		
		if(!$info['keywords']){
			$info['keywords'] = $info['name'];
		}

		$source = str_replace("https://", "", $info['source_url']);
		$source = str_replace("http://", "", $source);
		$t_arr = explode('/', $source);
		$info['source'] = isset($t_arr['0']) ? trim($t_arr['0']) : "互联网";
		$this->_data['info'] = $info; 

		$this->_data['site_name'] = $info['name']." | 焦点PHP - PHP开发者进阶资料库";
		$this->_data['site_keyword'] = $info['keywords']."焦点PHP - PHP开发者进阶资料库";
		$this->_data['site_description'] = $info['description']."焦点PHP - PHP开发者进阶资料库";

		$this->load->view('header', $this->_data);
		$this->load->view('detail', $this->_data);
		$this->load->view('footer', $this->_data);
	}

	public function press()
	{	
		$article_id = rand(0, 68686);
		echo $article_id;
		// exit;
		$info = $this->article->detail($article_id);
		$this->_data['info'] = $info;

		if(!$info['description']){
			$tmp = strip_tags($info['content']);
			$tmp = str_replace(PHP_EOL, "", str_replace(" ", "", $tmp));
			$tmp = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/","",$tmp);
			$tmp = mb_substr($tmp, 0, 110);
			$info['description'] = $tmp;
		}

		if(!$info['keywords']){
			$info['keywords'] = $info['name'];
		}
		
		$this->_data['site_name'] = $info['name']." | 焦点PHP - PHP开发者进阶资料库";
		$this->_data['site_keyword'] = $info['keywords']."焦点PHP - PHP开发者进阶资料库";
		$this->_data['site_description'] = $info['description']."焦点PHP - PHP开发者进阶资料库";

		$this->load->view('header', $this->_data);
		$this->load->view('detail', $this->_data);
		$this->load->view('footer', $this->_data);
	}

	public function about($about_id = "")
	{	
		if(!$about_id){
			redirect('/');
		}

		$info = $this->article->detail($about_id);
		$this->_data['info'] = $info;

		if(!$info['description']){
			$tmp = strip_tags($info['content']);
			$tmp = str_replace(PHP_EOL, "", str_replace(" ", "", $tmp));
			$tmp = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/","",$tmp);
			$tmp = mb_substr($tmp, 0, 110);
			$info['description'] = $tmp;
		}

		if(!$info['keywords']){
			$info['keywords'] = $info['name'];
		}
		
		$this->_data['site_name'] = $info['name']." | 焦点PHP - PHP开发者进阶资料库";
		$this->_data['site_keyword'] = $info['keywords']."焦点PHP - PHP开发者进阶资料库";
		$this->_data['site_description'] = $info['description']."焦点PHP - PHP开发者进阶资料库";

		$this->load->view('header', $this->_data);
		$this->load->view('about', $this->_data);
	}

	private function _getTagList($cate_id = ""){
		$rs = $this->article->getArticleTagsList($cate_id);
		if(!$cate_id){
			$rs = array_slice($rs, 0, 15);
		}else{
			$rs = array_slice($rs, 0, 30);
		}
		return $rs;
	}

	private function _getTopArticle($cate_id = ""){
		$field = "article_id, name, cate_name, icon, ctime, visits";
		$where = " is_show = 1 AND icon <> ''";
		if($cate_id == 0){
			// $where .= " AND is_top = 1";
		}else{
			$where .= " AND cate_id = '".$cate_id."'";
		}
		$order_by = "visits";
		$order_by_dire = "DESC";
		$limit = 6;
		$offset = 0;
		$rs = $this->article->lists($field, $where, $order_by, $order_by_dire, $offset, $limit);
		return $rs;
	}
}
