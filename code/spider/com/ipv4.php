<?php
	header("Content-type: text/html; charset=utf-8");
	require_once 'init.php';
	$arr = array('name'=>'ip 代理','list'=>array());
	echo json_encode($arr);

	// 0.0.0.0-255.255.255.255
	// A类	0.0.0.0-127.255.255.255		其中10.X.X.X，127.X.X.X保留
	// B类 	128.0.0.0-191.255.255.255	其中172.16.0.0---172.31.255.255，169.254.X.X，
	// C类	192.0.0.0-223.255.255.255	其中192.168.0.0---192.168.255.255
	// D类	224.0.0.0-239.255.255.255
	// E类	240.0.0.0-255.255.255.255

	$num = 0;
	$stime = "Start ".date('Y-m-d H:i:s').PHP_EOL;
	for ($a=0; $a <= 255; $a++) { 
		if($a==10 || $a==127){
			continue;
		}

		for ($b=0; $b <= 255; $b++) { 
			if($a==169 && $b==254){
				continue;
			}

			if($a==172 && $b>=16 && $b<=31){
				continue;
			}

			if($a==192 && $b==168){
				continue;
			}

			$ip = "";
			for ($c=0; $c <= 255; $c++) { 
				for ($d=0; $d <= 255; $d++) { 
					$ip .= $a.".".$b.".".$c.".".$d.PHP_EOL;
					$num++;
				}
			}

			file_put_contents(dirname(__FILE__)."/".$a.".log", $ip, FILE_APPEND);
		}
	}

	$etime = "End ".date('Y-m-d H:i:s').PHP_EOL;
	$num = 'total num :'.$num.PHP_EOL;
	file_put_contents(dirname(__FILE__)."/num.log", $stime.$etime.$num);