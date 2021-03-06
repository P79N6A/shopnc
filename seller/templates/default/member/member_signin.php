<?php defined('Inshopec') or exit('Access Invalid!');?>

<link rel="stylesheet" type="text/css" href="<?php echo MOBILE_TEMPLATES_URL;?>/css/nctouch_member.css">

<style type="text/css">

.s-dialog-wrapper { width: 12rem; height: 14.5rem; top: 50%; left: 50%; margin-top: -7.25rem; margin-left: -6rem; }

.s-dialog-content h4 { font-size: 0.7rem; line-height: 1rem; }

.s-dialog-content ul { margin-top: 0.5rem; }

.s-dialog-content li { font-size: 0.55rem; line-height: 0.8rem; margin-bottom: 0.2rem; text-align: left; }

.s-dialog-btn-wapper a { width: 100%; }

</style>

</head>

<body>

<header id="header">

  <div class="header-wrap">

    <div class="header-l"><a href="javascript:history.go(-1)"><i class="back"></i></a></div>

    <div class="header-title"><h1>签到领积分</h1></div>

    <div class="header-r"> <a id="header-nav" href="javascript:void(0);"><i class="more"></i><sup></sup></a> </div>

    

  </div>

  <?php include template('layout/toptip');?>  



</header>

<div class="member-top">

  <div class="my-pointnum"> 我的积分<span id="pointnum"></span> </div>

  <div class="sign-box" id="signdiv" >

    <div id="signinbtn" class="sign-btn" style="display:none;">

      <h2>签到</h2>

      <h6>+<span id="points_signin">5</span> 积分</h6>

    </div>

    <div id="completedbtn" class="sign-btn" style="display:none;">

      <h2>已签到</h2>

      <h6>坚持哦！</h6>

    </div>

  </div>

  <div id="description_link" class="signin-help">活动说明<i>i</i></div>

  <div id="description_info" style="display: none;">

    <h4>活动说明</h4>

    <ul style="text-align:left">

      <li>1、每人每天最多签到一次，签到后有机会获得积分。</li>

      <li>2、网站可根据活动举办的实际情况，在法律允许的范围内，对本活动规则变动或调整。</li>

      <li>3、对不正当手段（包括但不限于作弊、扰乱系统、实施网络攻击等）参与活动的用户，网站有权禁止其参与活动，取消其获奖资格（如奖励已发放，网站有权追回）。</li>

      <li>4、活动期间，如遭遇自然灾害、网络攻击或系统故障等不可抗拒因素导致活动暂停举办，网站无需承担赔偿责任或进行补偿。</li>

    </ul>

  </div>

</div>

<div class="signin-list">

  <h3>签到日志<a href="<?php echo urlMobile('member_points');?>">查看我的积分</a></h3>

  <ul id="loglist" class="nctouch-default-list">

  </ul>

</div>

<footer id="footer" class="bottom"></footer>

<script type="text/html" id="loglist_tpl">

    <% if(signin_list.length >0){%>

    <% for (var k in signin_list) { var v = signin_list[k]; %>

    <li class="signin-c">

       积分<em>+<%=v.sl_points %></em><span><%=v.sl_addtime_text %>日,<%=v.sl_desc %>获得</span>

    </li>

    <%}%>

    <li class="loading"><div class="spinner"><i></i></div>数据读取中</li>

    <% }else { %>

    <div class="nctouch-norecord signin" style="top: 70%;">

        <div class="norecord-ico"><i></i></div>

        <dl>

            <dt>您还没有任何签到记录</dt>

			<dd>每日签到可获得会员积分奖励</dd>

        </dl>

    </div>

    <% } %>

</script> 

<script type="text/javascript" src="<?php echo MOBILE_TEMPLATES_URL;?>/js/zepto.min.js"></script> 

<script type="text/javascript" src="<?php echo MOBILE_TEMPLATES_URL;?>/js/template.js"></script> 

<script type="text/javascript" src="<?php echo MOBILE_TEMPLATES_URL;?>/js/common.js"></script> 

<script type="text/javascript" src="<?php echo MOBILE_TEMPLATES_URL;?>/js/ncscroll-load.js"></script> 

<script>

    var key = getCookie('key');

    if (!key) {

       window.location.href =  WapUrl + "/tmpl/member/login.html";

    }

    function showSignin(){

        //检验是否能签到

        $.getJSON(ApiUrl + '/index.php?con=member_signin&fun=checksignin', {'key':key}, function(result){

            if(result.code == 200){

                $("#points_signin").html(result.datas.points_signin);

                $("#signinbtn").show();

                $("#completedbtn").hide();

            }else{

                if (result.state == 'isclose') {//如果关闭了签到功能，则不显示签到按钮

                    location.href = WapSiteUrl;

                }else{//如果已经签到完成，则显示已签到

                    $("#signinbtn").hide();

                    $("#completedbtn").show();

                }

            }

        });

    }

    //加载签到日志

    var load_class = new ncScrollLoad();

    function getSigninLog(){

        load_class.loadInit({

            'url':ApiUrl + '/index.php?con=member_signin&fun=signin_list',

            'getparam':{key:key},

            'tmplid':'loglist_tpl',

            'containerobj':$("#loglist"),

            'iIntervalId':true

        });

    }



    $(function(){

        showSignin();

        //获取会员积分

        $.getJSON(ApiUrl + '/index.php?con=member_index&fun=my_asset', {'key':key,'fields':'point'}, function(result){

            $("#pointnum").html(result.datas.point);

        });

        getSigninLog();

        $("#signinbtn").click(function(){

            if ($("#signinbtn").hasClass('loading')) {

                return false;

            }

            $("#signinbtn").addClass('loading');

            //获取详情

            $.getJSON(ApiUrl + '/index.php?con=member_signin&fun=signin_add', {'key':key}, function(result){

                if(result.code == 200){

                    var point =  parseInt(result.datas.point);

                    var pointnum = parseInt($("#pointnum").text());

                    var pointnumshow = point + pointnum;

                    $("#pointnum").html(parseInt(pointnumshow));

                    $("#completedbtn").show();

                    $("#signinbtn").hide();

                    getSigninLog();

                }

                $("#signinbtn").removeClass('loading');

            });

        });

        $("#description_link").click(function(){

            var con = $("#description_info").html();

            layer.open({

                content: con,

                btn: ['OK']

            });

        });

        //加载专题页

        /*$('#special_div').load('../../special.html',function(){

            loadSpecial(1);

        });*/

    });

</script>



