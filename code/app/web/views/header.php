<html>
<head>
    <meta charset="utf-8">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-COMPATIBLE" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=yes,maximum-scale=1.0, minimum-scale=0.5,user-scalable=yes">
    <title><?php echo $site_name;?></title>
    <link rel="shortcut icon" href="http://img.v2ts.cn/hubphp/img/favicon.ico">
    <meta name="keywords" content="<?php echo $site_keyword;?>">
    <meta name="description" content="<?php echo $site_description;?>">
    <link rel="stylesheet" href="http://img.v2ts.cn/hubphp/css/reset.css?v=<?php echo date('Ymd');?>">
    <link rel="stylesheet" href="http://img.v2ts.cn/hubphp/css/henzan.css?v=<?php echo date('Ymd');?>">
    </head>
    <body>
        <div id="doc">
            <!-- header S -->
            <div id="hd">
                <div class="header">
                    <h1 class="logo">
                      <a href="/" title="简单轻松进阶"><img src="http://img.v2ts.cn/hubphp/img/logo.png" alt="焦点PHP - PHP开发者进阶资料库"></a>
                    </h1>
                    <div class="search">
                        <form action="/index" method="GET">
                            <input type="hidden" value="<?php echo $cate_id;?>" name="cate">
                            <input type="hidden" value="<?php echo $tag;?>" name="tag">
                            <input type="text" value="<?php echo $skey;?>" placeholder="搜索感兴趣的文章" class="ipt" name="skey" autocomplete="off">
                            <input type="submit" value="搜索" class="sub icon">
                            <div class="autocomplete-suggestions hot-search" style="position: absolute; z-index: 9999; width: 328px;display: none;">
                                <div class="autocomplete-suggestion">
                                    <div class="suggestion-wrapper">
                                        <a href="/index?cate=<?php echo $cate_id;?>&tag=<?php echo $tag;?>&skey=Linux">Linux</a>
                                    </div>
                                </div>
                                <div class="autocomplete-suggestion">
                                    <div class="suggestion-wrapper">
                                        <a href="/index?cate=<?php echo $cate_id;?>&tag=<?php echo $tag;?>&skey=SVN">SVN</a>
                                    </div>
                                </div>
                                <div class="autocomplete-suggestion">
                                    <div class="suggestion-wrapper">
                                        <a href="/index?cate=<?php echo $cate_id;?>&tag=<?php echo $tag;?>&skey=Mysql">Mysql</a>
                                    </div>
                                </div>
                                <div class="autocomplete-suggestion">
                                    <div class="suggestion-wrapper">
                                        <a href="/index?cate=<?php echo $cate_id;?>&tag=<?php echo $tag;?>&skey=postgresql">PG</a>
                                    </div>
                                </div>
                                <div class="autocomplete-suggestion">
                                    <div class="suggestion-wrapper">
                                        <a href="/index?cate=<?php echo $cate_id;?>&tag=<?php echo $tag;?>&skey=Nginx">Nginx</a>
                                    </div>
                                </div>
                                <div class="autocomplete-suggestion">
                                    <div class="suggestion-wrapper">
                                        <a href="/index?cate=<?php echo $cate_id;?>&tag=<?php echo $tag;?>&skey=Redis">Redis</a>
                                    </div>
                                </div>
                                <div class="autocomplete-suggestion">
                                    <div class="suggestion-wrapper">
                                        <a href="/index?cate=<?php echo $cate_id;?>&tag=<?php echo $tag;?>&skey=SSDB">SSDB</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <!--i class="icon"></i-->
                    </div>
                </div>
            </div>