<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['theme']        = '';   
$config['template_dir'] = APPPATH . 'views/'.$config['theme'];   
$config['compile_dir']  = APPPATH . '/cache/smarty/templates_c';   
$config['cache_dir']    = APPPATH . '/cache/smarty/cache';   
$config['config_dir']   = APPPATH . '/cache/smarty/config';   
// $config['templates_ext'] = '.html';   
$config['caching']      = false;   
$config['lefttime']     = 60;
$config['left_delimiter']   = '{{';
$config['right_delimiter']  = '}}';

