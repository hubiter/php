        <div class="side fr">
            <!-- share-entrance S -->
            <div class="share-entrance" style="display: none;">    
                <i class="icon"></i>分享新体验
            </div>
            <!-- share-entrance E -->
            <!-- henzan-app-download S -->
            <div class="hz-app-download-wrapper">
                <div class="panel hz-app-download cl">
                    <p>
                        <strong>微信扫一扫关注</strong>
                        <br>简单轻松进阶
                    </p>
                    <img src="http://img.v2ts.cn/hubphp/img/qrcode.jpg">
                </div>
            </div>
            <!-- henzan-app-download E -->
            <!-- hot-search S -->
            <div class="panel panel-word">
                <div class="hd">
                    <h2>热门标签</h2>
                </div>
                <div class="bd">
                    <ul class="cl list">
                        <?php 
                            if($tags_list){
                                foreach ($tags_list as $val) {
                        ?>
                        <li><a href="/index?cate=<?php echo $cate_id;?>&tag=<?php echo $val['tag_id'];?>"><?php echo $val['tag_val'];?>(<?php echo $val['t_num'];?>)</a></li>
                        <?php 
                            }
                        }
                        ?>
                    </ul>
                </div>
            </div>
            <!-- hot-search E -->
            <!-- hot-article S -->
            <div class="panel panel-figure">
                <div class="hd">
                     <h2>热门排行</h2>
                </div>
                <div class="bd">
                    <ul class="list">
                        <?php 
                        if($right_top_list){
                            foreach ($right_top_list as $val) {
                        ?>
                        <li class="item">
                            <a href="/article/<?php echo $val['article_id'];?>.html" target="_blank">
                                <div class="pic">
                                    <img src="<?php echo $val['icon'];?>" alt="<?php echo $val['name'];?>" />
                                </div>
                                <div class="info">
                                    <p class="title"><?php echo $val['name'];?></p>
                                    <div class="meta cl">
                                        <ul class="count fr">
                                            <li class="lk"><i class="icon"></i><?php echo $val['visits'];?></li>
                                            <!-- <li class="gd"><i class="icon"></i>3</li> -->
                                        </ul>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <?php 
                            }
                        }
                        ?>
                    </ul>
                </div>
            </div>
            <!-- hot-article E -->
            <div class="panel ft">
                <div class="ft-nav cl"> 
                    <a  onclick="javascript:alert('关于我们~');">关于我们</a>
                    <i class="hr"></i>
                    <a  onclick="javascript:alert('加入我们~');">加入我们</a>
                    <i class="hr"></i>
                    <a  onclick="javascript:alert('版权保护~');">版权保护</a>
                    <i class="hr"></i>
                    <a  onclick="javascript:alert('内容合作~');">内容合作</a>
                </div>
                <p class="cp"><a href="http://www.miitbeian.gov.cn" target="_blank">京ICP备13049079号</a>
                </p>
            </div>
            <div id="gotop" class="zh-backtotop">
                <p class="top"></p>
                <p class="feedback"></p>
            </div>
            <!-- 很赞用户反馈 -->
            <div class="panel cl" id="hz-feedback">
                <div class="fork" id="close-feedback"><i class="icon"></i>
                </div>
                <div class="hd">意见反馈</div>
                <div class="bd">
                    <div id="feedback">
                        <form id="feedbackForm">
                            <div class="txt opinion">
                                <textarea class="text" id="message" name="message_fake" maxstrlen="5000" disabled="disabled">  可以在此填写你对很赞的建议反馈哦~</textarea>
                            </div>
                            <input type="button" class="button fr" value="提交" onclick="javascript:alert('建议反馈功能暂时未开放，敬请期待~');">  
                            <span class="tips hide">意见反馈不能为空</span>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type='text/javascript' src='http://img.v2ts.cn/hubphp/js/jquery-1.12.4.min.js?v=<?php echo date('Ymd');?>'></script>
<script type="text/javascript">
    $(document).ready(function(){
        $('.ipt').click(function(){
            $('.hot-search').toggle();
        });
        $(window).on("scroll", function () {
            var a = $(this).scrollTop();
            a > 200 ? $("#gotop").show() : $("#gotop").hide()
        });
        $(document).on("click", ".top", function () {
            $("html,body").animate({
                scrollTop: 0
            }, 800)
        });

        $(document).on("click", ".feedback", function () {
            $('#hz-feedback').toggle();
        });

        $(document).on("click", "#close-feedback", function () {
            $('#hz-feedback').hide();
        });
    });
</script>
<span style="display: none;">
    <script>
        var _hmt = _hmt || [];
        (function() {
          var hm = document.createElement("script");
          hm.src = "https://hm.baidu.com/hm.js?8c3fa25b42fd036ff96674065ecaeb12";
          var s = document.getElementsByTagName("script")[0]; 
          s.parentNode.insertBefore(hm, s);
        })();
    </script>
    <script type="text/javascript">var cnzz_protocol = (("https:" == document.location.protocol) ? " https://" : " http://");document.write(unescape("%3Cspan id='cnzz_stat_icon_1275311642'%3E%3C/span%3E%3Cscript src='" + cnzz_protocol + "s23.cnzz.com/stat.php%3Fid%3D1275311642%26show%3Dpic' type='text/javascript'%3E%3C/script%3E"));</script>
</span>
</body>
</html>