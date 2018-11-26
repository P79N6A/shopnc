<?php defined( 'Inshopec') or exit( 'Access Invalid!');?>

<link rel="stylesheet" type="text/css" href="<?php echo MOBILE_TEMPLATES_URL;?>/css/nctouch_categroy.css">

<link rel="stylesheet" type="text/css" href="<?php echo MOBILE_TEMPLATES_URL;?>/css/nctouch_products_detail.css">

<link rel="stylesheet" type="text/css" href="<?php echo MOBILE_TEMPLATES_URL;?>/css/nctouch_common.css">

</head>

<body>

<header id="header" class="posf">

  <div class="header-wrap">

    <div class="header-l"> <a href="javascript:history.go(-1)"> <i class="back"></i> </a> </div>

    <ul class="header-nav">

      <li><a href="javascript:void(0);" id="goodsDetail">商品</a></li>

      <li><a href="javascript:void(0);" id="goodsBody">详情</a></li>

      <li class="cur"><a href="javascript:void(0);" id="goodsEvaluation">评价</a></li>

    </ul>

  <div class="header-r"> <a id="header-nav" href="javascript:void(0);"><i class="more"></i><sup></sup></a> </div>

  </div>

   <?php include template('layout/toptip');?>



</header>

<div class="nctouch-main-layout">

  <div class="nctouch-tag-nav">

    <ul>

      <li class="selected"><a href="javascript:void(0);" data-state="">全部评价</a></li>

      <li><a href="javascript:void(0);" data-state="1">好评</a></li>

      <li><a href="javascript:void(0);" data-state="2">中评</a></li>

      <li><a href="javascript:void(0);" data-state="3">差评</a></li>

      <li><a href="javascript:void(0);" data-state="4">订单晒图</a></li>

      <li><a href="javascript:void(0);" data-state="5">追加评价</a></li>

    </ul>

  </div>

  <div id="product_evaluation_html" class="product-eval-list"></div>

</div>

</body>

<script type="text/html" id="product_ecaluation_script">

		<% if (goods_eval_list.length > 0) { %>

		<ul>

			<% for (var i=0; i<goods_eval_list.length; i++) { %>

        	<li>

				<div class="eval-user">

					<div class="user-avatar"><img src="<%=goods_eval_list[i].member_avatar%>" /></div>

					<span class="user-name"><%=goods_eval_list[i].geval_frommembername%></span>

					<time><%=goods_eval_list[i].geval_addtime_date%></time>

				</div>

				<div class="goods-raty"><i class="star<%=goods_eval_list[i].geval_scores%>"></i></div>

				<dl class="eval-con">

					<dt><%=goods_eval_list[i].geval_content%></dt>

					<%if(goods_eval_list[i].geval_image_240) {%>

					<dd class="goods_geval">

						<%for (var j=0; j<goods_eval_list[i].geval_image_240.length; j++) {%>

							<a href="javascript:void(0);"><img src="<%=goods_eval_list[i].geval_image_240[j]%>" /></a>

						<%}%>

						<div class="nctouch-bigimg-layout hide">

							<div class="close"></div>

							<div class="pic-box">

								<ul>

								<%for (var j=0; j<goods_eval_list[i].geval_image_240.length; j++) {%>

									<li style="background-image: url(<%=goods_eval_list[i].geval_image_1024[j]%>)"></li>

								<%}%>

								</ul>

							</div>

							<div class="nctouch-bigimg-turn">

								<ul>

								<%for (var j=0; j<goods_eval_list[i].geval_image_240.length; j++) {%>

									<li class="<% if(j == 0) { %>cur<%}%>"></li>

								<%}%>

								</ul>

							</div>

						</div>

					</dd>

					<%}%>

				</dl>

				<%if(goods_eval_list[i].geval_explain && goods_eval_list[i].geval_explain != '') {%>

				<div class="eval-explain">

				解释：<%=goods_eval_list[i].geval_explain%>	

				</div>

				<%}%>

				<% if (goods_eval_list[i].geval_content_again != '') {%>

				<div class="again-eval"><time><%=goods_eval_list[i].geval_addtime_again_date%><time>追加评价</div>

				<dl class="eval-con">

					<dt><%=goods_eval_list[i].geval_content_again%></dt>

					<%if(goods_eval_list[i].geval_image_again_240) {%>

					<dd class="goods_geval">

						<%for (var j=0; j<goods_eval_list[i].geval_image_again_240.length; j++) {%>

							<a href="javascript:void(0);"><img src="<%=goods_eval_list[i].geval_image_again_240[j]%>" /></a>

						<%}%>

						<div class="nctouch-bigimg-layout hide">

							<div class="close"></div>

							<div class="pic-box">

								<ul>

								<%for (var j=0; j<goods_eval_list[i].geval_image_again_240.length; j++) {%>

									<li style="background-image: url(<%=goods_eval_list[i].geval_image_again_1024[j]%>)" ></li>

								<%}%>

								</ul>

							</div>

							<div class="nctouch-bigimg-turn">

								<ul>

								<%for (var j=0; j<goods_eval_list[i].geval_image_again_240.length; j++) {%>

									<li class="<% if(j == 0) { %>cur<%}%>"></li>

								<%}%>

								</ul>

							</div>

						</div>

					</dd>

					<%}%>

				</dl>

				<%if(goods_eval_list[i].geval_explain_again && goods_eval_list[i].geval_explain_again != '') {%>

					<div class="eval-explain">解释：<%=goods_eval_list[i].geval_explain_again%></div>

					<%}%>

				<% } %>

        	</li>

			<% } %>

			<li class="loading"><div class="spinner"><i></i></div>数据读取中</li>

		</ul>

        <%}else {%>

        <div class="nctouch-norecord eval">

            <div class="norecord-ico"><i></i></div>

            <dl>

                <dt>该商品未收到任何评价</dt>

				<dd>期待您的购买与评论！</dd>

            </dl>

        </div>

        <%}%>

	</div>

</div>

</script>

<script type="text/javascript" src="<?php echo MOBILE_TEMPLATES_URL;?>/js/zepto.min.js"></script>

<script type="text/javascript" src="<?php echo MOBILE_TEMPLATES_URL;?>/js/swipe.js"></script>

<script type="text/javascript" src="<?php echo MOBILE_TEMPLATES_URL;?>/js/template.js"></script>

<script type="text/javascript" src="<?php echo MOBILE_TEMPLATES_URL;?>/js/common.js"></script>

<script type="text/javascript" src="<?php echo MOBILE_TEMPLATES_URL;?>/js/ncscroll-load.js"></script>

<script type="text/javascript" src="<?php echo MOBILE_TEMPLATES_URL;?>/js/swipe.js"></script> 

<script type="text/javascript" src="<?php echo MOBILE_TEMPLATES_URL;?>/js/list/product_eval_list.js"></script>
