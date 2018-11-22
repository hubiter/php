<link href="http://img.v2ts.cn/hubphp/css/showtrend.css?v=<?php echo date('Ymd');?>" rel="stylesheet">
<!-- header E -->
<div id="bd" class="cl">
    <div class="main fl">
        <!-- article-detail S -->
        <div class="article-detail panel-shadow" data-id="198788">
            <h2 class="title"><?php echo $info['name'];?></h2>
            <div class="tag cl">
                <ul class="count fr">
                    <li class="lk"><i class="icon"></i><?php echo $info['visits'];?></li>
                    <!-- <li class="gd"><a href="javascript:void(0);"><i class="icon"></i>1</a></li> -->
                    <!-- <li class="cmt"><a href="/post/198788#comment"><i class="icon"></i></a></li> -->
                </ul>
                <span class="art-type-tag fl">标签</span>
                <span class="rel-cat-tag">
                    <?php 
                        if($article_tags_list){
                            foreach ($article_tags_list as $val) {
                    ?>
                    <a href="/index?cate=<?php echo $cate_id;?>&tag=<?php echo $val['tag_id'];?>"><?php echo $val['tag_val'];?></a>
                    <?php 
                        }
                    }
                    ?>
                </span>
            </div>
            <style type="text/css">
                .content pre{
                    white-space: pre-wrap !important;
                }
            </style>
            <div class="content">
                <?php echo $info['content'];?>
                <script type="text/javascript">
                    var r_url = "<?php echo $info['source_url'];?>";
                </script>
                <span>说明：本文转自<a href="javascript:void(0);" onclick="javascript:if(confirm('确定跳转至<?php echo $info['source'];?>吗？')){window.open(r_url, '_blank');}"><?php echo $info['source'];?></a>，用于技术交流分享，仅代表原文作者观点，如有疑问，请联系我们删除~</span>
            </div>
            <div class="handle cl" style="display: none;">
                <div class="cl"> <a class="button gd "><i class="icon"></i>赞一下<span class="zan-animation">+1</span></a>
                    <span>有<em>1</em>位用户觉得不错</span>
                    <div class="share-wrap fr"> <span class="fl">文章分享到</span>

                        <ul class="fl share-module">
                            <li>
                                <a href="javascript:void(0);" class="wx"></a>
                                <div class="popup-box ewm-wx">
                                    <p>分享到微信</p>
                                    <div class="ewm-cont">
                                        <canvas width="140" height="140"></canvas>
                                    </div>
                                    <p class="tips">打开微信，点击“发现”,使用“扫一扫”,即可将网页分享到微信</p> <span class="arrow-up"><span class="inner"></span></span>
                                </div>
                            </li>
                            <li>
                                <a href="http://service.weibo.com/share/share.php?title=跑动魔都——New Balance 880V8上海城市跑步限量版&url=http://www.henzan.com/post/198788&searchPic＝false&source=&appkey=2887791756&pic=" target="_blank" class="xl"></a>
                            </li>
                            <li>
                                <a href="http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url=http://www.henzan.com/post/198788&title=跑动魔都——New Balance 880V8上海城市跑步限量版&pics=&summary=&pic=" target="_blank" class="qq"></a>
                            </li>
                        </ul>
                        <!--/div-->
                    </div>
                </div>
                <ul class="user-list cl">
                    <li class="" data-uid="103052080">
                        <a href="/u/103052080">
                            <img src="//img1.miaomiaoz.com/image/ff1bcf71c7552320c225d295974c084b.jpeg!/rotate/auto/both/64x64" alt="雄关漫道">
                        </a>
                    </li>
                    <a class="more hide" href="javascript:void(0);">
                        <img src="//img1.miaomiaoz.com/image/721a505e38b38760478df7d157b03eca.png" alt="更多">
                    </a>
                </ul>
            </div>
        </div>
        <!-- article-detail E -->
        <!-- comment S -->
        <div class="panel comment" name="comment" id="comment">
            <div class="hd">
                <h2>讨论<em>(0)</em></h2>
            </div>
            <div class="bd">
                <p class="tips"><span>参与讨论,说说你的看法吧</span>
                </p>
                <ul class="list cmt-list"></ul>
                <a class="status more more-cmt hide" href="javascript:void(0);">
                    查看更多评论
                </a>
            <div class="release" id="default-release">
                <div class="rel-area">
                    <div class="textarea">
                        <textarea class="empty" disabled="disabled">  敬请回复~</textarea>
                    </div>
                    <p class="word-num hide"> <span>500</span>字以内</p>
                    <div class="rel-ft cl"> 
                        <input type="button" value="发表" class="sub" onclick="javascript:alert('评论功能暂时未开放，敬请期待~');">
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- comment E -->
</div>
