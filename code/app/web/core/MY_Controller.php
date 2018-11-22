<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {
	protected $_data = array();
	protected $_is_phone = 0;

	public function __construct(){
		parent::__construct();
		$this->_data['site_name'] = "焦点PHP - PHP开发者进阶资料库";
		$this->_data['site_keyword'] = "焦点PHP - PHP开发者进阶资料库";
		$this->_data['site_description'] = "焦点PHP - PHP开发者进阶资料库";
		
		$this->_data['cate_id'] = 0;
		$this->_data['tag'] = "";
		$this->_data['skey'] = "";
		$this->load->helper('url');

		$is_phone = 0;
		$agent = $_SERVER['HTTP_USER_AGENT'];  
		if(strpos($agent,"NetFront") || strpos($agent,"iPhone") || strpos($agent,"MIDP-2.0") || strpos($agent,"Opera Mini") || strpos($agent,"UCWEB") || strpos($agent,"Android") || strpos($agent,"Windows CE") || strpos($agent,"SymbianOS")){
			$is_phone = 1;
		}
		$this->_data['is_phone'] = $is_phone;
	}
	
	protected function _getCache($key = ""){
		return $this->cache->redis->get($key);
	}

	protected function _setCache($key = "", $val = "", $expire = 86400){
		return $this->cache->redis->save($key, $val, $expire);
	}

	protected function _getZsetCache($key = "", $start = "", $stop = ""){
		return $this->cache->redis->zrange($key, $start, $stop);
	}

	protected function _setZsetCache($key = "", $score = "", $value = ""){
		return $this->cache->redis->zadd($key, $score, $value);
	}

	protected function _delZsetCache($key = "", $member = ""){
		return $this->cache->redis->zrem($key, $member);
	}

	public function assign($key, $val) {   
        $this->cismarty->assign($key, $val);   
    }   
  
    public function display($html) {   
        $this->cismarty->display($html);   
    }  

	public function pagination($config = array()){
	    $config['base_url'] = isset($config['base_url'])  && !empty($config['base_url']) ? $config['base_url'] : $base_url;
	    $config['total_rows'] = isset($config['total_rows'])  && !empty($config['total_rows']) ? $config['total_rows'] : 0;
	    $config['per_page'] = isset($config['per_page'])  && !empty($config['per_page']) ? $config['per_page'] : 1;
	    $config['cur_page'] = isset($config['cur_page'])  && !empty($config['cur_page']) ? $config['cur_page'] : 1;

	    $page_size = $config['per_page'];
	    $total_rows = $config['total_rows'];
	    $cur_page = $config['cur_page'];
	    
	    $total_page = $total_rows % $page_size == 0 ? $total_rows / $page_size : ceil($total_rows / $page_size);
	    $total_page = $total_page < 1 ? 1 : $total_page;
	    $cur_page = $cur_page > ($total_page) ? ($total_page) : $cur_page;
	    $start = ($cur_page - 1) * $page_size;
	    $this->load->library('pagination');
	    $this->pagination->initialize($config);
	    return array('offset'=>$start, 'page_string' => $this->pagination->create_links());
	}

	public function sortMult(&$arr = array(), $order_column = "", $order_direction = SORT_DESC){
		if(!$arr || !$order_column){
			return $arr;
		}
		if(is_array($arr)){
			$tmpKey = array();
			foreach ($arr as $val) {
				if(is_array($val)){
					$tmpKey[] = $val[$order_column];
				}
			}
			array_multisort($tmpKey, $order_direction, $arr);
		}
		return $arr;
	}

	public function success($data = array()){
		echo json_encode(array('status'=>'200','data'=>$data));
		exit;
	}

	public function error($msg = ""){
		echo json_encode(array('status'=>'0','msg'=>$msg));
		exit;
	} 
}