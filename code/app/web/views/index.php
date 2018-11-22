<!-- header E -->
<div id="bd">
    <div class="main panel fl">
        <div class="category">
            <ul class="cl main-category">
                <li class="cate-item <?php if($cate_id == '0'){echo 'cur';}?>"><a href="/" class="no-cate"><?php echo $f_name;?></a></li>
                <li class="cate-item <?php if($cate_id == '1'){echo 'cur';}?>"><a href="/system">系统</a></li>
                <li class="cate-item <?php if($cate_id == '2'){echo 'cur';}?>"><a href="/database">数据库</a></li>
                <li class="cate-item <?php if($cate_id == '3'){echo 'cur';}?>"><a href="/develop">开发</a></li>
                <li class="cate-item <?php if($cate_id == '4'){echo 'cur';}?>"><a href="/safe">安全</a></li>
                <li class="cate-item <?php if($cate_id == '5'){echo 'cur';}?>"><a href="/bigdata">大数据</a></li>
                <li class="cate-item <?php if($cate_id == '6'){echo 'cur';}?>"><a href="/other">综合</a></li>
            </ul>
            <div class="more-catagory">
                <div class="more-catagory-list"></div>
                <div class="popup-box">
                    <!-- hover出现弹层 -->
                    <ul class="list">
                        <li class="c139"><a href="/category/139">居家</a></li>
                    </ul>   
                    <span class="arrow-up"><span class="inner"></span></span>
                </div>
            </div>
        </div>
        <ul class="article-list" id="hot-article-list">
            <?php 
                if($article_list){
                    foreach ($article_list as $item) {
            ?>
            <li class="item cl">
                <?php if($item['icon']){?>
                <div class="pic">
                    <a href="/article/<?php echo $item['article_id'];?>.html" target='_blank'>
                        <img src="<?php echo $item['icon'];?>" alt="<?php echo $item['name'];?>" />
                    </a>
                </div>
                <?php }?>
                <div class="info  no-goods-info ">
                    <p class="title cl">    
                        <!-- <span class="art_type_tag fl">净水</span> -->
                        <a href="/article/<?php echo $item['article_id'];?>.html" target="_blank"><?php echo $item['name'];?></a>
                    </p>
                    <p class="desc"><?php echo $item['description'];?>...</p>
                    <div class="meta cl">
                        <div class="authorinfo fl">   
                            <a href="/index?cate=<?php echo $item['cate_id'];?>" target="_blank" class="author"><?php echo $item['cate_name'];?></a>
                            <div class="cetification author-cetification">  
                            <i class="icon cetification-icon"></i>
                                <div class="popup-box">设计师、摄影师、数码爱好者    
                                    <span class="arrow-up"><span class="inner"></span></span>
                                </div>
                            </div>
                        </div>
                        <ul class="count fr">
                            <li class="lk">
                                <!--<a href="javascrip:void(0)">-->
                                <i class="icon"></i><?php echo $item['visits'];?>
                                <!--</a>-->
                            </li>
                            <!-- <li class="gd"><a href="/post/198811" target="_blank"><i class="icon"></i>3</a>
                            </li>
                            <li class="cmt"><a href="/post/198811#comment" target="_blank"><i class="icon"></i>2</a>
                            </li> -->
                        </ul>
                    </div>
                </div>
            </li>
             <?php            
                    }
                }
            ?>
        </ul>
        <link rel="stylesheet" href="http://img.v2ts.cn/hubphp/css/misc.css?v=<?php echo date('Ymd');?>" type="text/css">
        <div class="u-textAlignCenter posts-nav">
            <nav class="navigation pagination" role="navigation">
                <h2 class="screen-reader-text">文章导航</h2>
                <?php echo $page_string;?>
            </nav>
        </div>
        <!-- article-list E -->
    </div>
