<!DOCTYPE html>
<html>
    <head>
        <title>收银台</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="/static/jquery-1.8.1.min.js" type="text/javascript"></script>
        <link href="/static/style.css" rel="stylesheet" type="text/css"/>
    </head>
    <body class="maxwidth">
        <div class="_head"> 收银台</div>
        <div class="w100 hg20"></div>
        <div class="_user_invite_area1">
            <div class="w100 hg20">&nbsp;</div>
            <div class="w100 hg20">&nbsp;</div>
            <p class="f16">支付金额：&yen;<?php echo $param['money']; ?></p>
            <p>编号：<?php echo $qrcode_id; ?></p>
            <!--<img id="paycodes_img" src="/home/paycode_qr" />-->
            <img id="paycodes_img" src="/static/bg.jpg" />
            <p>长按识别二维码付款</p>
            <p id="paycodes_p">付款码在 <b><font id="paycodes_font"><?php echo $expire; ?></font></b> 秒后失效</p>
        </div>
        
        <script>
            (function() {
                var wait = <?php echo $expire; ?>;
                var get = 0;
                var interval = setInterval(function() {
                    var time = --wait;
                    if(get==0 || get==1){
                        $.get("/pay/getqrcode",{id:<?php echo $qrcode_id; ?>},function(d){
                            if( d.code == 1 && get==0 ){
                                if(d.val!=""){
                                    $("#paycodes_img").attr("src","/home/paycode_qr?val="+d.val);
                                    get = 1;
                                }
                            }
                            if(d.code==2){
                                document.getElementById("paycodes_img").setAttribute("src","/static/bg3.jpg");
                                document.getElementById("paycodes_p").innerHTML = "已完成支付。";
                                clearInterval(interval);
                                get = 2;
                            }
                        },'json');
                    }
                    document.getElementById("paycodes_font").innerHTML = time;
                    if (time <= 0) {
                        document.getElementById("paycodes_img").setAttribute("src","/static/bg2.jpg");
                        document.getElementById("paycodes_p").innerHTML = "<font style='color:#f00;'>二维码已失效 ，请重新生成</font><br /><br /><a href='' class='alink'>重新生成</a>";
                        clearInterval(interval);
                    }
                }, 1000);
            })();
        </script>
    </body>
</html>
