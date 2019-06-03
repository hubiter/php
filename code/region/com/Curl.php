<?php
	//CURL类
	class Curl{
		private $_timeout = 3;	//超时时间
		private $_ctimeout = 3;	//超时时间
		private $_refer = 'http://www.89ip.cn';	//来源refer
		private $_proxy = array(
			'proxy_host' => '', 
			'proxy_port' => '',
			'proxy_type' => 'http',
		); 
		private $_headers = array(
			'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.131 Safari/537.36',
			'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
			'Referer' => 'http://www.89ip.cn/index_21.html',
			'Cookie' => 'UM_distinctid=1699bbab969b03-0b722aef6ca5c2-9333061-1fa400-1699bbab96a8a7; CNZZDATA1254651946=525935306-1553095890-%7C1553095890; Hm_lvt_8ccd0ef22095c2eebfe4cd6187dea829=1553095900; yd_cookie=aa6127d2-663f-44cd8f0fb8a3778aedb5180a60a807788cf0; Hm_lvt_f9e56acddd5155c92b9b5499ff966848=1558366661; Hm_lpvt_f9e56acddd5155c92b9b5499ff966848=1558368976',
		); 

		public function __construct(){

		}

		public function get($url = "", $full_data = false, $headers = array(), $proxy = array()){
			$ch = curl_init();
			curl_setopt ($ch, CURLOPT_URL, $url);

			//伪造来源refer
			// curl_setopt ($ch, CURLOPT_REFERER, $this->_refer);
			$user_agent = "";
			
			if($headers){
				$this->_headers = $headers;
			}
			curl_setopt ($ch, CURLOPT_HEADER, 1);
			curl_setopt ($ch, CURLOPT_HTTPHEADER , $this->_headers);
			// curl_setopt ($ch, CURLOPT_USERAGENT, $this->_headers['user_agent']); 	// 模拟用户使用的浏览器
			// curl_setopt ($ch, CURLOPT_AUTOREFERER, 1);
			// curl_setopt ($ch, CURLOPT_COOKIEJAR, $this->_headers['cookie']);		//连接结束后保存cookie信息的文件
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt ($ch, CURLOPT_TIMEOUT, $this->_timeout);			//允许 cURL 函数执行的最长秒数
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $this->_ctimeout);	//在尝试连接时等待的秒数

			if($proxy){
				$proxy_host = isset($proxy['proxy_host']) ? $proxy['proxy_host'] : $this->_proxy['proxy_host'];
				$proxy_port = isset($proxy['proxy_port']) ? $proxy['proxy_port'] : $this->_proxy['proxy_port'];
				$proxy_type = isset($proxy['proxy_type']) ? $proxy['proxy_type'] : $this->_proxy['proxy_type'];
				curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC); 	//代理认证模式
				curl_setopt($ch, CURLOPT_PROXY, $proxy_host); 			//代理服务器地址
				curl_setopt($ch, CURLOPT_PROXYPORT, $proxy_port); 		//代理服务器端口
				curl_setopt($ch, CURLOPT_PROXYUSERPWD, ":"); 			//http代理认证帐号，名称:pwd的格式
				switch ($proxy_type) {
					case 'http':
						$proxy_type = CURLPROXY_HTTP;
						break;
					case 'socks5':
						$proxy_type = CURLPROXY_SOCKS5;
						break;
					default:
						$proxy_type = CURLPROXY_HTTP;
						break;
				}
				
				curl_setopt($ch, CURLOPT_PROXYTYPE, $proxy_type); 	//使用http代理模式
			}
			
			$data = curl_exec($ch);
			$einfo = curl_getinfo($ch);
			// $result['einfo'] = $einfo;	//CURL详细信息
			$result['http_code'] = $einfo['http_code'];
			$result['used_time'] = isset($einfo['total_time']) ? floatval($einfo['total_time']) : 0;					//最后一次传输所消耗的时间

			if($result['http_code'] != 200 || curl_errno($ch)){
				$result['error_no'] = curl_errno($ch);
				$result['error_msg'] = curl_error($ch);
			}
			if($full_data){
				$result['data'] = $data;
			}
			curl_close($ch);
       		return $result;
		}

		public function post(){

		}
	}