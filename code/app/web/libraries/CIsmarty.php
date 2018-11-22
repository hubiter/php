<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH . 'libraries/smarty/Smarty.class.php');

class CIsmarty extends Smarty {
	protected $ci;

	public function __construct(){
		parent::__construct();
		$this->ci = &get_instance();
		$this->ci->load->config('smarty');
		$this->template_dir   = $this->ci->config->item('template_dir');   
        $this->compile_dir    = $this->ci->config->item('compile_dir');   
        $this->cache_dir      = $this->ci->config->item('cache_dir');   
        $this->config_dir     = $this->ci->config->item('config_dir');   
        // $this->template_ext   = $this->ci->config->item('template_ext');   
        $this->caching        = $this->ci->config->item('caching');   
        $this->cache_lifetime = $this->ci->config->item('lefttime');
	}
}
