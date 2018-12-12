<!DOCTYPE html>
<html><head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta name="keywords" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>创建比赛</title>
        <link rel="stylesheet" type="text/css" href="/src/css/userinfo/font_1459473269_4751618.css">
        <link href="/src/css/userinfo/style.css" rel="stylesheet">
        <script src="/src/js/jquery.js"></script>
        <!--<script src="/src/js/jquery.snow.js"></script>-->
        <script src="/src/js/area.js"></script>
        <script src="/src/js/bootstrap.js"></script>
        <script src="/src/js/date.js"></script>
        <!--必要样式-->
        <link rel="stylesheet" type="text/css" href="/src/css/userinfo/menu_elastic.css">
        <link rel="stylesheet" type="text/css" href="/src/css/userinfo/info.css">
        <script src="/src/js/snap.js"></script>
        <script src="/src/js/laydate/laydate.js"></script>
        <!--[if IE]>
        <script src="js/html5.js"></script>
        <![endif]-->
        <script src="https://res.wx.qq.com/open/js/jweixin-1.2.0.js"></script>
        <?php include T('Common/share'); ?>
        <style>
            .ddlist .custom p{
                font-size: 14px;
                margin: 5px 10px 5px 10px;
            }
            .button{
                background: #A09E9E;
                border-color: #A09E9E;
                width: 85%;
                height: 35px;
                line-height: 0px;
                font-size: 15px;
                margin-left: 10px;
            }
            .ranking{
                border:1px dashed #8E8A8A;
                border-radius: 10px;
                margin-top:10px;
                background: #F3F0F0;
            }
            .input250{
                border:#A09F9F 1px solid;
                width: 250px;
                height: 35px;
                border-radius: 5px;
                padding-left: 8px;
            }
            .input200{
                border:#A09F9F  1px solid;
                width: 200px;
                height: 35px;
                border-radius: 5px;
                padding-left: 8px;
            }
            .input150{
                border:#A09F9F 1px solid;
                width: 150px;
                height: 35px;
                border-radius: 5px;
                padding-left: 8px;
            }
            .input100{
                border:#A09F9F 1px solid;
                width: 100px;
                height: 35px;
                border-radius: 5px;
                padding-left: 8px;
            }
            .input80{
                border:#A09F9F 1px solid;
                width: 80px;
                height: 35px;
                border-radius: 5px;
                padding-left: 8px;
            }
            .sub{
                margin-top: 20px;
                margin-bottom: 30px;
                text-align: center;
            }
            .cus-but{
                background-color: #fdc158;
                border-radius: 5px;
                height: 40px;
                border:0px;
                width: 40%;
                font-size: 16px;
                letter-spacing: 2px;
            }
            .sysm{
                z-index: 50;
                border: 1px solid #EFECEC;
                border-radius: 5px;
                width: 150px;
                height: 50px;
                line-height: 50px;
                font-size: 16px;
                background: #fff;
                position: absolute;
                right: 8px;
                top: 42px;
            }
            .navbar {
                position: relative;
                min-height: 50px;
                margin-bottom: 2px;
                border: 1px solid transparent;
            }
            .text-center {
                text-align: center;
            }
            .center-block {
                display: block;
                margin-right: auto;
                margin-left: auto;
            }
            button{
                font: inherit;
            }
            /*隐藏掉我们模型的checkbox*/
            .my_protocol .input_agreement_protocol {
                appearance: none;
                -webkit-appearance: none;
                outline: none;
                display: none;
            }
            /*未选中时*/        
           .my_protocol .input_agreement_protocol+span {
                width: 16px;
                height: 16px;
                background-color: red;
                display: inline-block;
                background: url(/src/img/custom/icon_checkbox.png) no-repeat;
                background-position-x: 0px;
                background-position-y: -25px;
                position: relative;
                top: 3px;
            }
           /*选中checkbox时,修改背景图片的位置*/            
           .my_protocol .input_agreement_protocol:checked+span {
                background-position: 0 0px
            }
            .xieyi{
                z-index: 70;
                top: 0px;
                width: 100%;
                background-color:rgba(0,0,0,0.8);
                position:absolute;
                display: none;
            }
            .xieyi div{
                color:white;
                color: white;
                padding: 10px;
                line-height: 25px;
                letter-spacing: 1.5px;
                font-size: 14px;
            }
        </style>
    </head>
    <body class="huibg">
        <nav class="navbar text-center">
            <button class="topleft" onclick="javascript:history.go(-1);" style="top: 10px;font-size: 14px;"><span class="iconfont icon-fanhui"></span></button>
            <a class="navbar-tit center-block">我的比赛</a>
            <button class="topnav" id="open-sysm" style="top: 10px;font-size: 14px;"><span class="iconfont icon-1"></span></button>
            <div class="sysm" id="sysm" style="display: none">
                使用说明
            </div>
        </nav>
        <div id="myTabContent" class="tab-content">
            <div class="tab-pane fade active in" id="sp1">
                <form method="post" id="myform">
                    <input type="hidden" id="custom_id" name="custom_id" value="<?php echo $custom_id; ?>">
                <ul class="ddlist">
                    <li class="custom">
                        <div class="ranking">
                            <p style="margin-top:8px;font-size: 15px;color: red;text-align: center"><?php echo $data["error"]; ?></p>
<!--                            <p style="margin-top:8px;">比赛模式：
                                <select id="type" name="type" class="input100">
                                    <option value="1">淘汰模式</option>
                                    <option value="2">轮数模式</option>
                                </select>
                            </p>-->
                            <p style="margin-top:8px;">比赛名称：<input type="text" class="input200" id="name" name="name" value="<?php echo $data["name"]; ?>" placeholder="比赛名称限十个字"></p>
                            <p style="margin-top:8px;">游戏类型：
                                <select id="game_id" name="game_id" class="input200" style="background-color: #fff;">
                                    <option value="1" <?php if($data["game_id"]==1){ ?> selected = "selected"<?php }?> >开心斗地主</option>
                                    <option value="2" <?php if($data["game_id"]==2){ ?> selected = "selected"<?php }?>>炸金花</option>
                                    <option value="3" <?php if($data["game_id"]==3){ ?> selected = "selected"<?php }?>>牛牛</option>
                                    <option value="4" <?php if($data["game_id"]==4){ ?> selected = "selected"<?php }?>>德州扑克</option>
                                    <option value="5" <?php if($data["game_id"]==5){ ?> selected = "selected"<?php }?>>四川麻将</option>
                                    <option value="6" <?php if($data["game_id"]==6){ ?> selected = "selected"<?php }?>>中国象棋</option>
                                </select>
                            </p>
                            <p style="margin-top:8px;">创建者名称：<input type="text" class="input200" id="nickname" name="nickname" value="<?php echo $cu_data["nickname"]; ?>" placeholder="默认为微信昵称"></p>
                            <p><span id="ksrs" style="color:#000">轮&emsp;&emsp;数</span>：<input type="text" class="input200" id="number" name="number" value="<?php echo $data["number"]; ?>" placeholder="建议轮数3-5轮（默认3轮）"></p>
                            <p>场&emsp;&emsp;次：<input type="text" class="input200" id="inning" name="inning" value="<?php echo $data["inning"]; ?>" placeholder="场次必须大于等于1"></p>
                            <p><span id="bssc" style="color:#000">每轮时长</span>：<input type="text" class="input200" id="custom_time" name="custom_time" value="<?php echo $data["custom_time"]; ?>" placeholder="比赛时长大于1小于120分钟"></p>
                            <p style="margin-left: 70px;font-size: 13px;color: red;">该时长决定淘汰分增长速度，并非绝对值。</p>
                            <p>开赛时间：<input type="text" class="input200" id="start_date" name="start_date" value="<?php echo $data["start_date"]; ?>" placeholder="至少提前一日申请" readonly="readonly"></p>
<!--                            <p>门票价格：<input type="text" class="input200" id="tickets" name="tickets" value="<?php echo $data["tickets"]; ?>" placeholder="最大200元">元</p>
                            <p>推广福利：<input type="text" class="input200" id="welfare" name="welfare" value="<?php echo $data["welfare"]; ?>" placeholder="不能大于门票的90%价格">元</p>
                            <p style="height:40px;line-height: 40px;">预估门票收入：<span id="tickets_yj"><?php $mpsr = ($data["tickets"]-$data["welfare"]-$data["charge"]-($data["tickets"]*0.01))*$data["number"];echo $mpsr>0?$mpsr:0; ?></span></p>-->
                        </div>
                        <div class="ranking">
                            <table style="margin-top:8px;font-size: 14px;margin: 10px;width: 98%;">
                                <tr>
                                    <td rowspan="2">联系方式</td> 
                                    <td><input type="text" class="input200" id="mobile" name="mobile" value="<?php echo $cu_data["mobile"]; ?>" >
                                        <p style="margin-top:2px;font-size: 13px;color: red;">上方为手机号码，下方为座机号码</p>
                                    </td> 
                                </tr>
                                <tr> 
                                    <td><input type="text" class="input200" id="phone" name="phone" value="<?php echo $cu_data["phone"]; ?>" >
                                    <p style="margin-top:2px;font-size: 13px;color: red;">手机号码 或 座机号码 任选一项</p>
                                    </td> 
                                </tr>
                            </table>
                        </div>
                        <div class="ranking">
<!--                            <p style="margin-top:8px;">银行卡号：<input type="text" class="input200" id="bank_card" name="bank_card" value="<?php echo $cu_data["bank_card"]; ?>" placeholder="用于接收报名费"></p>-->
                            <p style="margin-top:8px;">姓 名：<input type="text" class="input200" id="card_name" name="card_name" value="<?php echo $cu_data["card_name"]; ?>" placeholder="请确认为身份证持有人"></p>
                            <p>身份证：<input type="text" class="input200" id="card_id" name="card_id" value="<?php echo $cu_data["card_id"]; ?>" placeholder="请输入18位二代身份证"></p>
                        </div>
                        <div class="ranking">
                            <p>冠军奖品：<input type="text" class="input200" id="prizes1_name" name="prizes1_name" value="<?php echo $data["prizes1_name"]; ?>" placeholder="奖品名称限十六个字"></p>
                            <p>奖品价值：<input type="text" class="input200" id="prizes1_value" name="prizes1_value" value="<?php echo $data["prizes1_value"]; ?>" placeholder="奖品预估"></p>
                        </div>
                        <div class="ranking">
                            <p>亚军奖品：<input type="text" class="input200" id="prizes2_name" name="prizes2_name" value="<?php echo $data["prizes2_name"]; ?>" placeholder="奖品名称限十六个字"></p>
                            <p>奖品价值：<input type="text" class="input200" id="prizes2_value" name="prizes2_value" value="<?php echo $data["prizes2_value"]; ?>" placeholder="奖品预估"></p>
                        </div>
                        <div class="ranking">
                            <p>季军奖品：<input type="text" class="input200" id="prizes3_name" name="prizes3_name" value="<?php echo $data["prizes3_name"]; ?>" placeholder="奖品名称限十六个字"></p>
                            <p>奖品价值：<input type="text" class="input200" id="prizes3_value" name="prizes3_value" value="<?php echo $data["prizes3_value"]; ?>" placeholder="奖品预估"></p>
                        </div>
                        <div class="ranking">
                            <p>第四名奖品：<input type="text" class="input200" id="prizes4_name" name="prizes4_name" value="<?php echo $data["prizes4_name"]; ?>" placeholder="奖品名称限十六个字"></p>
                            <p>奖品价值：<input type="text" class="input200" id="prizes4_value" name="prizes4_value" value="<?php echo $data["prizes4_value"]; ?>" placeholder="奖品预估"></p>
                        </div>
                        <div class="ranking">
                            <p>第五名奖品：<input type="text" class="input200" id="prizes5_name" name="prizes5_name" value="<?php echo $data["prizes5_name"]; ?>" placeholder="奖品名称限十六个字"></p>
                            <p>奖品价值：<input type="text" class="input200" id="prizes5_value" name="prizes5_value" value="<?php echo $data["prizes5_value"]; ?>" placeholder="奖品预估"></p>
                        </div>
                        <div class="ranking">
                            <p>参与奖奖品：<input type="text" class="input200" id="join_prizes_name" name="join_prizes_name" value="<?php echo $data["join_prizes_name"]; ?>" placeholder="奖品名称限十六个字"></p>
                            <p>参与奖价值：<input type="text" class="input200" id="join_prizes_value" name="join_prizes_value" value="<?php echo $data["join_prizes_value"]; ?>" placeholder="奖品预估"></p>
                        </div>
                        <div class="ranking">
                            <p style="margin-top:2px;font-size: 14px;color: red;">注：奖品发放期限最多为3天 (开赛时间后，单位天)<br>
                                若奖品没有及时发放，系统将会自动删除比赛
                            </p>
                        </div>
                        <div style="text-align: center;margin-top: 15px;">
                            <label class="my_protocol">
                                <input class="input_agreement_protocol" type="checkbox" checked="true"/>
                                  <span></span>
                            </label>

                            已阅读并同意<a style="text-decoration: underline;color: red;" id="ckxy">《斗地主比赛创建协议》</a>
                        </div>
                        <div class="sub">
                            <?php if($custom_id){ ?>
                            <input type="button" value="重新提交" id="butSubmit" class="cus-but">
                            <?php }else{ ?>
                            <input type="button" value="创建比赛" id="butSubmit" class="cus-but">
                            <?php } ?>
                        </div>
                    </li>
                </ul>
                </form>
            </div>
        </div>
        <div class="xieyi">
            <div> 开心逗棋牌游戏许可及服务协议</br>
《开心逗棋牌游戏许可及服务协议》（以下简称“本协议”）由您与开心逗棋牌游戏服务提供方共同缔结，本协议具有合同效力。请您务必审慎阅读、充分理解各条款内容，特别是免除或者限制开心逗棋牌责任的条款（以下称“免责条款”）、对用户权利进行限制的条款（以下称“限制条款”）、约定争议解决方式和司法管辖的条款，以及开通或使用某项服务的单独协议。前述免责、限制及争议解决方式和管辖条款可能以黑体加粗、颜色标记或其他合理方式提示您注意，包括但不限于本协议第二条、第三条、第四条、第六条、第九条等相关条款，您对该等条款的确认将可能导致您在特定情况下的被动、不便、损失，请您在确认同意本协议之前或在使用开心逗棋牌游戏服务之前再次阅读前述条款。双方确认前述条款并非属于《合同法》第40条规定的“免除其责任、加重对方责任、排除对方主要权利的”的条款，并同意该条款的合法性及有效性。</br>

除非您已阅读并接受本协议所有条款，否则您无权使用开心逗棋牌游戏服务。如果您对本协议或开心逗棋牌游戏服务有意见或建议，可与平台客户服务部门联系，我们会给予您必要的帮助。您点击同意、接受或下一步，或您注册、使用该斗地主比赛游戏服务均视为您已阅读并同意签署本协议。</br>

如果您未满18周岁，请在法定监护人的陪同下阅读本协议，并特别注意未成年人使用条款。</br>

一、【定义】</br>
1.1 本协议：指本协议正文、游戏规则及其修订版本。上述内容一经正式发布，即为本协议不可分割的组成部分。本协议同时还包括文化部根据《网络游戏管理暂行办法》（文化部令第49号）制定的《网络游戏服务格式化协议必备条款》。</br>

1.2 游戏规则：指开心逗棋牌游戏服务提供方不时发布并修订的关于开心逗棋牌游戏的用户守则、玩家条例、游戏公告、提示及通知等内容。</br>

1.3 开心逗棋牌游戏服务提供方：指向您提供开心逗棋牌游戏及其服务的深圳市开心娱乐网络有限公司，在本协议中简称为“开心娱乐”。</br>

1.4 开心逗棋牌游戏：指由开心娱乐负责运营的斗地主比赛游戏的名称；开心逗棋牌游戏可能以软件、网页形式提供，这种情况下，开心逗棋牌游戏还包括该相关软件及相关文档。</br>

1.5 开心逗棋牌游戏服务：指开心娱乐向您提供的与游戏相关的各项在线运营服务。</br>

1.6 您：又称“玩家”或“用户”，指被授权使用开心逗棋牌游戏及其服务的自然人。</br>

1.7 游戏数据：指您在使用开心逗棋牌游戏过程中产生的被服务器记录的各种数据，包括但不限于角色数据、虚拟物品数据、行为日志、购买日志等等数据。</br>

二、【游戏账号】</br>
2.1 您如果需要使用和享受开心逗棋牌游戏，则您需要将您享有使用权的微信账号作为游戏账号，并按照《网络游戏管理暂行规定》及文化部《网络游戏服务格式化协议必备条款》的要求，登录实名注册系统并进行实名注册。

您进行实名注册时，应提供有关您本人真实、合法、准确、有效的身份信息及其他相关信息，且不得以他人身份资料进行实名注册。否则，开心娱乐有权终止为您提供开心逗棋牌游戏服务，并有权对您的游戏账号采取包括但不限于警告、限制或禁止使用游戏帐号全部或部分功能、删除游戏账号及游戏数据、删除相关信息、游戏账号封禁（以下有时简称“封号”）直至注销的处理措施（为描述方便，以下有时也将该等处理措施称为“处罚”），因此造成的一切后果由您自行承担。

若您不进行实名注册的，或您提供的注册信息不完整的，则您可能无法使用开心逗棋牌游戏或在使用开心逗棋牌游戏过程中会受到相应限制。</br>

2.2 您进一步知悉并同意，您在游客模式下可能无法进行游戏充值或消费。且一旦您卸载或重装开心逗棋牌游戏，或您更换手机、电脑等终端设备或该等终端设备损坏的，您在该游客模式下所有游戏相关数据可能都将会被清空，且无法查询和恢复。如因此造成您任何损失的，均由您自行承担。

如您使用开心娱乐认可的第三方帐号作为游戏账号使用和享受开心逗棋牌游戏的，您还应遵守有关该第三方帐号的协议、规则，且因该第三方帐号产生的相关问题包括但不限于被盗等，您应自行联系该第三方进行解决，开心娱乐可视情况提供相应的协助。</br>

2.3 您充分理解并同意：为判断或核实您提供的相关实名注册信息是否真实或有效，开心娱乐有权将您提供的实名注册信息提供给第三方进行整理、保存及比对等处理。且开心娱乐会按照国家相关要求将您的实名注册信息运用于防沉迷系统之中，即开心娱乐可能会根据您的实名注册信息判断您是否年满18周岁、您提交的实名身份信息是否规范或实名验证是否通过等，从而决定是否对您的游戏账号予以防沉迷限制。</br>

2.4 您充分理解并同意，开心娱乐有权审查用户注册所提供的身份信息是否真实、有效，并应积极地采取技术与管理等合理措施保障用户账号的安全、有效；用户有义务妥善保管其账号及密码，并正确、安全地使用其账号及密码。任何一方未尽上述义务导致账号密码遗失、账号被盗等情形而给用户和他人的民事权利造成损害的，应当承担由此产生的法律责任。

若您发现有他人冒用或盗用您的游戏账号及密码、或任何其他未经您合法授权使用的情形时，应立即以开心娱乐要求的有效方式通知开心娱乐并告知开心娱乐需采取的措施。您通知开心娱乐时，应提供与您注册身份信息相一致的个人有效身份信息，开心娱乐收到您的有效请求并核实身份后，会根据您的要求或结合具体情况采取相应措施（包括但不限于暂停该账号的登录和使用等），开心娱乐因根据您的请求采取相应措施而造成您及其他用户损失的，由您自行承担。若您没有提供有效身份信息或您提供的个人有效身份信息与所注册的身份信息不一致的，开心娱乐有权拒绝您的请求，因此造成您损失的，由您自行承担。</br>

2.5 您充分理解并同意，为高效利用服务器资源，如果您3年内未使用游戏账号登录开心逗棋牌游戏，开心娱乐有权在提前通知的情况下，对该账号及其账号下的游戏数据及相关信息采取删除等处置措施。</br>

2.6 您理解并同意，您不得将游戏账号以任何方式提供给他人使用，包括但不限于不得以转让、出租、借用等方式提供给他人作包括但不限于直播、录制、代打代练等商业性使用。否则，因此产生任何法律后果及责任均由您自行承担，且开心娱乐有权对您的游戏账号采取包括但不限于警告、限制或禁止使用游戏帐号全部或部分功能、删除游戏账号及游戏数据及其他相关信息、封号直至注销的处理措施，因此造成的一切后果由您自行承担。</br>

三、【用户信息收集、使用及保护】</br>
3.1 您同意并授权开心娱乐为履行本协议之目的收集您的用户信息，这些信息包括您在实名注册系统中注册的信息、您游戏账号下的游戏数据以及其他您在使用开心逗棋牌游戏服务的过程中向开心娱乐提供或开心娱乐基于安全、用户体验优化等考虑而需收集的信息，开心娱乐对您的用户信息的收集将遵循本协议及相关法律的规定。</br>

3.2 您充分理解并同意：开心娱乐或其合作的第三方可以根据您的用户信息，通过短信、电话、邮件等各种方式向您提供关于开心逗棋牌游戏的活动信息、推广信息等各类信息。</br>

3.3 您理解并同意：为了更好地向您提供游戏服务，改善游戏体验，开心娱乐可对您微信账号或游戏账号中的昵称、头像以及在开心逗棋牌游戏中的相关操作信息、游戏信息等信息（以下称“该等信息”。该等信息具体包括但不限于您的登录状态、对战信息/状态、成就信息等）进行使用，并可向您本人或其他用户或好友展示该等信息。</br>

3.4 您应对通过开心逗棋牌游戏及相关服务了解、接收或可接触到的包括但不限于其他用户在内的任何人的个人信息予以充分尊重，您不应以搜集、复制、存储、传播或以其他任何方式使用其他用户的个人信息，否则，由此产生的后果由您自行承担。</br>

3.5 保护用户信息及隐私是开心娱乐的一项基本原则。除本协议另有规定外，开心逗棋牌游戏服务对用户信息收集、使用及保护等将遵循开心娱乐的相关隐私政策。</br>

四、【开心逗棋牌游戏服务】</br>
4.1 在您遵守本协议及相关法律法规的前提下，开心娱乐给予您一项个人的、不可转让及非排他性的许可，以使用开心逗棋牌游戏服务。您仅可为非商业目的使用开心逗棋牌游戏服务，包括：</br>

（1）接收、下载、安装、启动、升级、登录、显示、运行和/或截屏开心逗棋牌游戏；</br>

（2）创建游戏角色，设置网名，查阅游戏规则、用户个人资料、游戏对局结果，开设游戏房间、设置游戏参数，在游戏中购买、使用游戏道具、游戏装备、游戏币等，使用聊天功能、社交分享功能；</br>

（3）使用开心逗棋牌游戏支持并允许的其他某一项或几项功能。</br>

4.2 您在使用开心逗棋牌游戏服务过程中不得未经开心娱乐许可以任何方式录制、直播或向他人传播开心逗棋牌游戏内容，包括但不限于不得利用任何第三方软件进行网络直播、传播等。</br>

4.3 在开心逗棋牌游戏以软件形式提供的情况下，您在使用开心逗棋牌游戏及开心逗棋牌游戏服务时还应符合本协议第五条关于软件许可的规定。</br>

4.4 本条及本协议其他条款未明示授权的其他一切权利仍由开心娱乐保留，您在行使这些权利时须另外取得开心娱乐的书面许可。</br>

4.5 如果您违反本协议约定的，开心娱乐有权采取相应的措施进行处理，该措施包括但不限于：不经通知随时对相关内容进行删除，并视行为情节对违规游戏账号处以包括但不限于警告、限制或禁止使用全部或部分功能、游戏账号封禁直至注销的处罚，并公告处理结果，要求您赔偿因您从事违约行为而给开心娱乐造成的损失等。</br>

4.6 您充分理解并同意，开心娱乐有权依合理判断对违反有关法律法规或本协议规定的行为进行处理，对违法违规的任何用户采取适当的法律行动，并依据法律法规保存有关信息向有关部门报告等，用户应独自承担由此而产生的一切法律责任。</br>

4.7 您充分理解并同意，因您违反本协议或相关服务条款的规定，导致或产生第三方主张的任何索赔、要求或损失，您应当独立承担责任；开心娱乐因此遭受损失的，您也应当一并赔偿。</br>

4.8 您充分理解并同意：游戏道具、游戏装备、游戏币等是开心逗棋牌游戏服务的一部分，开心娱乐在此许可您依本协议而获得其使用权。您购买、使用游戏道具、游戏装备、游戏币等应遵循本协议、游戏具体规则的要求；同时，游戏道具、游戏装备、游戏币等可能受到一定有效期限的限制，即使您在规定的有效期内未使用，除不可抗力或可归责于开心娱乐的原因外，一旦有效期届满，将会自动失效。

您充分理解并同意：为更好地向用户提供开心逗棋牌游戏服务，开心娱乐有权对游戏中的任何内容或构成元素等作出调整、更新或优化（包括但不限于您购买或正在使用的角色、游戏装备及其他游戏道具的美术设计、性能及相关数值设置等作出调整、更新或优化等）。且如开心娱乐做出相应调整、更新或优化的，您同意不会因此追究开心娱乐的任何法律责任。</br>

4.9 您充分理解并同意：为保障您游戏账号安全，为营造公平、健康及安全的游戏环境，在您使用开心逗棋牌游戏服务的过程中，在不违反相关法律规定情况下，开心娱乐可以通过技术手段了解您终端设备的随机存储内存以及与开心逗棋牌游戏同时运行的相关程序。一经发现有任何未经授权的、危害开心逗棋牌游戏服务正常运营的相关程序，开心娱乐可以收集所有与此有关的信息并采取合理措施予以打击。</br>

4.10 您充分理解并同意：为了保证您及其他用户的游戏体验，开心娱乐有权转移或者清除开心逗棋牌游戏服务器上存储的一些过往的游戏数据。</br>

4.11 开心娱乐将按照相关法律法规和本协议的规定，采取切实有效的措施保护未成年人在使用开心逗棋牌游戏服务过程中的合法权益，包括可能采取技术措施、禁止未成年人接触不适宜的游戏或者游戏功能、限制未成年人的游戏时间、预防未成年人沉迷网络。作为游戏规则的一部分，开心娱乐还将在适当位置发布开心逗棋牌游戏用户指引和警示说明，包括游戏内容介绍、正确使用游戏的方法以及防止危害发生的方法。所有未成年人用户都应在法定监护人的指导下仔细阅读并遵照执行这些指引和说明；其他玩家在使用开心逗棋牌游戏服务的过程中应避免发布、产生任何有损未成年人身心健康的内容，共同营造健康游戏环境。</br>

4.12 如果您未满18周岁的，为保障您的合法权益，开心娱乐有权依据国家有关法律法规及政策规定、本协议其他条款规定、开心逗棋牌游戏运营策略或根据您法定监护人的合理要求采取以下一种或多种措施：</br>

（1）将与您游戏相关的信息（包括但不限于您游戏帐号的登录信息、充值流水信息等）提供给您的法定监护人，使得您法定监护人可及时或同步了解您游戏情况；</br>

（2）限制您游戏账号的消费额度；</br>

（3）采取技术措施屏蔽某些游戏或游戏的某些功能，或限定您游戏时间或游戏时长；</br>

（4）注销或删除您游戏账号及游戏数据等相关信息；</br>

（5）您法定监护人要求采取的，或开心娱乐认为可采取的其他合理措施，以限制或禁止您使用开心逗棋牌游戏。</br>

4.13 开心娱乐向用户提供游戏服务本身属于商业行为，用户有权自主决定是否根据开心娱乐自行确定的收费项目（包括但不限于购买游戏内的虚拟道具的使用权以及接受其他增值服务等各类收费项目）及收费标准支付相应的费用，以获得相应的游戏服务。如您不按相应标准支付相应费用的，您将无法获得相应的游戏服务。

您知悉并同意：收费项目或收费标准的改变、调整是一种正常的商业行为，您不得因为收费项目或收费标准的改变、调整而要求开心娱乐进行赔偿或补偿。</br>

4.14 在任何情况下，开心娱乐不对因不可抗力导致的您在使用开心逗棋牌游戏服务过程中遭受的损失承担责任。该等不可抗力事件包括但不限于国家法律、法规、政策及国家机关的命令及其他政府行为或者其它的诸如地震、水灾、雪灾、火灾、海啸、台风、罢工、战争等不可预测、不可避免且不可克服的事件。</br>

4.15 开心逗棋牌游戏可能因游戏软件BUG、版本更新缺陷、第三方病毒攻击或其他任何因素导致您的游戏角色、游戏道具、游戏装备及游戏币等账号数据发生异常。在数据异常的原因未得到查明前，开心娱乐有权暂时冻结该游戏账号；若查明数据异常为非正常游戏行为所致，开心娱乐有权恢复游戏账号数据至异常发生前的原始状态（包括向第三方追回被转移数据），且开心娱乐无须向您承担任何责任。</br>

4.16 开心娱乐未授权您从任何第三方通过购买、接受赠与或者其他的方式获得游戏账号、游戏道具、游戏装备、游戏币等，开心娱乐不对第三方交易的行为负责，并且不受理因任何第三方交易发生纠纷而带来的申诉。</br>

4.17 您充分理解到：不同操作系统之间存在不互通的客观情况，该客观情况并非开心娱乐造成，由此可能导致您在某一操作系统中的充值和游戏数据不能顺利转移到另一操作系统中。由于您在不同系统进行切换造成的充值损失和游戏数据丢失风险应由您自行承担，开心娱乐对此不承担任何责任。</br>

4.18 您充分理解到：开心逗棋牌游戏中可能会设置强制对战区域或玩法，如果您不同意强制对战，请您不要进入该游戏或游戏区域；您的进入，将被视为同意该玩法并接受相应后果。</br>

4.19 开心娱乐自行决定终止运营开心逗棋牌游戏时或开心逗棋牌游戏因其他任何原因终止运营时，开心娱乐会按照文化部有关网络游戏终止运营的相关规定处理游戏终止运营相关事宜，以保障用户合法权益。</br>

五、【软件许可】</br>
5.1 使用开心逗棋牌游戏服务可能需要下载并安装相关软件，您可以直接从开心娱乐的相关网站上获取该软件，也可以从得到开心娱乐授权的第三方获取。如果您从未经开心娱乐授权的第三方获取开心逗棋牌游戏或与开心逗棋牌游戏名称相同的游戏，将视为您未获得开心娱乐授权，开心娱乐无法保证该游戏能够正常使用，并对因此给您造成的损失不予负责。</br>

5.2 开心娱乐可能为不同的终端设备或操作系统开发了不同的软件版本，包括但不限于windows、ios、android、windows phone、symbian、blackberry等多个应用版本，您应当根据实际情况选择下载合适的版本进行安装，下载安装程序后，您需要按照该程序提示的步骤正确安装。</br>

5.3 若开心逗棋牌游戏以软件形式提供，开心娱乐给予您一项个人的、不可转让及非排他性的许可。您仅可为非商业目的在单一台终端设备上下载、安装、登录、使用该开心逗棋牌游戏。</br>

5.4 为了保证开心逗棋牌游戏服务的安全性和功能的一致性，开心娱乐有权对软件进行更新，或者对软件的部分功能效果进行改变或限制。</br>

5.5 软件新版本发布后，旧版本的软件可能无法使用。开心娱乐不保证旧版本软件继续可用及相应的客户服务，请您随时核对并下载最新版本。</br>

六、【用户行为规范】</br>
6.1 您充分了解并同意，您必须为自己游戏账号下的一切行为负责，包括您所发表的任何内容以及由此产生的任何后果。</br>

6.2 您除了可以按照本协议的约定使用开心逗棋牌游戏服务之外，不得进行任何侵犯开心逗棋牌游戏的知识产权的行为，或者进行其他的有损于开心娱乐或其他第三方合法权益的行为。</br>

6.3 您在使用开心逗棋牌游戏或开心逗棋牌游戏服务时须遵守法律法规，不得利用开心逗棋牌游戏或开心逗棋牌游戏服务从事违法违规行为，包括但不限于以下行为：</br>

（一）违反宪法确定的基本原则的；</br>

（二）危害国家统一、主权和领土完整的；</br>

（三）泄露国家秘密、危害国家安全或者损害国家荣誉和利益的；</br>

（四）煽动民族仇恨、民族歧视，破坏民族团结，或者侵害民族风俗、习惯的；</br>

（五）宣扬邪教、迷信的；</br>

（六）散布谣言，扰乱社会秩序，破坏社会稳定的；</br>

（七）宣扬淫秽、色情、赌博、暴力，或者教唆犯罪的；</br>

（八）侮辱、诽谤他人，侵害他人合法权益的；</br>

（九）违背社会公德的；</br>

（十）有法律、行政法规和国家规定禁止的其他内容的。</br>

6.4 除非法律允许或开心娱乐书面许可，您不得从事下列行为：</br>

（1）删除游戏软件及其副本上关于著作权的信息；</br>

（2）对游戏软件进行反向工程、反向汇编、反向编译或者以其他方式尝试发现软件的源代码；</br>

（3）对游戏软件进行扫描、探查、测试，以检测、发现、查找其中可能存在的BUG或弱点；</br>

（4）对游戏软件或者软件运行过程中释放到任何终端内存中的数据、软件运行过程中客户端与服务器端的交互数据，以及软件运行所必需的系统数据，进行复制、修改、增加、删除、挂接运行或创作任何衍生作品，形式包括但不限于使用插件、外挂或非经合法授权的第三方工具/服务接入软件和相关系统；</br>

（5）修改或伪造软件运行中的指令、数据，增加、删减、变动软件的功能或运行效果，或者将用于上述用途的软件、方法进行运营或向公众传播，无论上述行为是否为商业目的；</br>

（6）通过非开心娱乐开发、授权的第三方软件、插件、外挂、系统，使用开心逗棋牌游戏及开心逗棋牌游戏服务，或制作、发布、传播非开心娱乐开发、授权的第三方软件、插件、外挂、系统；</br>

（7）对游戏中开心娱乐拥有知识产权的内容进行使用、出租、出借、复制、修改、链接、转载、汇编、发表、出版、建立镜像站点等；</br>

（8）建立有关开心逗棋牌游戏的镜像站点，或者进行网页（络）快照，或者利用架设服务器等方式，为他人提供与开心逗棋牌游戏服务完全相同或者类似的服务；</br>

（9）将开心逗棋牌游戏的任意部分分离出来单独使用，或者进行其他的不符合本协议的使用；</br>

（10）使用、修改或遮盖开心逗棋牌游戏的名称、商标或其它知识产权；</br>

（11）其他未经开心娱乐明示授权的行为。</br>

6.5 您在使用开心逗棋牌游戏服务过程中有如下任何行为的，开心娱乐有权视情节严重程度，依据本协议及相关游戏规则的规定，对您做出暂时或永久性地禁止登录（即封号）、删除游戏账号及游戏数据、删除相关信息等处理措施，情节严重的将移交有关行政管理机关给予行政处罚，或者追究您的刑事责任：</br>

（1）以某种方式暗示或伪称开心娱乐内部员工或某种特殊身份，企图得到不正当利益或影响其他用户权益的行为；</br>

（2）在开心逗棋牌游戏中使用非法或不当词语、字符等，包括用于角色命名；</br>

（3）以任何方式破坏开心逗棋牌游戏或影响开心逗棋牌游戏服务的正常进行；</br>

（4）各种非法外挂行为；</br>

（5）传播非法言论或不当信息；</br>

（6）盗取他人游戏账号、游戏物品；</br>

（7）私自进行游戏账号交易；</br>

（8）私自进行游戏虚拟货币、游戏装备、游戏币及其他游戏道具等交易；</br>

（9）违反本协议任何约定的；</br>

（10）其他在行业内被广泛认可的不当行为，无论是否已经被本协议或游戏规则明确列明。

您知悉并同意，由于外挂具有隐蔽性或用完后即消失等特点，开心娱乐有权根据您的游戏数据和表现异常判断您有无使用非法外挂等行为。</br>

6.6 您知悉并同意，如开心娱乐依据本协议对您的游戏账号采取封号处理措施的，具体封号期间由开心娱乐根据您违规行为情节而定。

您知悉并同意：</br>
（1）在封号期间，您游戏账号中的游戏虚拟货币、游戏装备、游戏币及其他游戏道具可能都将无法使用；</br>
（2）如前述游戏虚拟货币、游戏装备、游戏币及其他游戏道具存在一定有效期，该有效期可能会在封号期间过期，您游戏账号解封后，您将无法使用该等已过期的游戏虚拟货币、游戏装备、游戏币及其他游戏道具。据此，您也同意不会因发生前述第（1）和（或）第（2）点规定的情形而追究开心娱乐任何法律责任。</br>

七、【知识产权】</br>
7.1 开心娱乐是开心逗棋牌游戏的知识产权权利人。开心逗棋牌游戏（包括开心逗棋牌游戏整体及开心逗棋牌游戏涉及的所有内容、组成部分或构成要素 ）的一切著作权、商标权、专利权、商业秘密等知识产权及其他合法权益，以及与开心逗棋牌游戏相关的所有信息内容（包括文字、图片、音频、视频、图表、界面设计、版面框架、有关数据或电子文档等）均受中华人民共和国法律法规和相应的国际条约保护，开心娱乐享有上述知识产权和合法权益，但相关权利人依照法律规定应享有的权利除外。未经开心娱乐事先书面同意，您不得以任何方式将开心逗棋牌游戏（包括开心逗棋牌游戏整体及开心逗棋牌游戏涉及的所有内容、组成部分或构成要素 ）进行商业性使用。</br>

7.2 尽管本协议有其他规定，您在使用开心逗棋牌游戏服务中产生的游戏数据的所有权和知识产权归开心娱乐所有，开心娱乐有权保存、处置该游戏数据。其中，开心娱乐对用户购买游戏虚拟货币的购买记录的保存期限将遵守文化部《网络游戏管理暂行办法》有关规定。对其他游戏数据的保存期限由开心娱乐自行决定，但国家法律法规另有规定的从其规定。</br>

7.3 开心逗棋牌游戏可能涉及第三方知识产权，而该等第三方对您基于本协议在开心逗棋牌游戏中使用该等知识产权有要求的，开心娱乐将以适当方式向您告知该要求，您应当一并遵守。</br>

八、【遵守当地法律监管】</br>
8.1 您在使用开心逗棋牌游戏服务过程中应当遵守当地相关的法律法规，并尊重当地的道德和风俗习惯。如果您的行为违反了当地法律法规或道德风俗，您应当为此独立承担责任。</br>

8.2 您应避免因使用开心逗棋牌游戏服务而使开心娱乐卷入政治和公共事件，否则开心娱乐有权暂停或终止对您的服务。</br>

九、【管辖与法律适用】</br>
9.1 本协议签订地为中华人民共和国广东省深圳市南山区。</br>

9.2 本协议的成立、生效、履行、解释及纠纷解决，适用中华人民共和国大陆地区法律（不包括冲突法）。</br>

9.3 若您和开心娱乐之间因本协议发生任何纠纷或争议，首先应友好协商解决；协商不成的，您同意将纠纷或争议提交至本协议签订地有管辖权的人民法院管辖。</br>

9.4 本协议所有条款的标题仅为阅读方便，本身并无实际涵义，不能作为本协议涵义解释的依据。</br>

9.5 本协议条款无论因何种原因部分无效，其余条款仍有效，对各方具有约束力。</br>

十、【其他】</br>
10.1 开心娱乐有权在必要时变更本协议条款，您可以在开心逗棋牌游戏的相关页面查阅最新版本的协议条款。本协议条款变更后，如果您继续使用开心逗棋牌游戏服务，即视为您已接受变更后的协议。</br>

10.2 根据国家新闻出版总署关于健康游戏的忠告，开心娱乐提醒您：抵制不良游戏，拒绝盗版游戏；注意自我保护，谨防受骗上当；适度游戏益脑，沉迷游戏伤身。</br>
<div style="text-align: center;"><input type="button"  value="同 意" id="tytk" style="margin: 15px;width: 30%;height: 35px;font-size: 18px;border: 0px;background-color: red;color: #fff;"></div>
            </div>
        </div>


        <script src="/src/js/classie.js"></script>
        <script src="/src/js/main3.js"></script>

        <script  type="text/javascript" charset="utf-8" async defer>
            $("#del").click(function (event) {
                if (confirm("是否要删除？")) {
                    location = "delcustom.html?custom_id=" + $("#id").val();
                }
            });
            $("#butSubmit").click(function (event) {
                if (confirm("是否要提交比赛？")) {
                    if($("input[type='checkbox']").is(':checked')){
                        $("#myform").submit();
                    }else{
                        alert("请阅读并同意斗地主比赛创建协议");
                    }
                }
            });
            $("#type").change(function(){
                var t=$(this).val();
                if(t==1){
                    $("#number").attr('placeholder',"必须大于等于6人");
                    $("#ksrs").html("开赛人数");
                    $("#bssc").html("比赛时长");
                }else{
                    $("#ksrs").html("轮&emsp;&emsp;数");
                    $("#bssc").html("每轮时长");
                    $("#number").attr('placeholder',"建议轮数3-5轮（默认3轮）");
                }
                
            });
            $("#tytk").click(function (event) {
                $(".xieyi").hide();
                $("input[type='checkbox']").attr("checked", true);
            });
            $("#ckxy").click(function (event) {
                $(".xieyi").show();
                window.scrollTo(0,0);
            });
            $("#code").click(function (event) {
                location = '<?php echo $code; ?>';
            });
            $("#stop").click(function (event) {
                var is_stop = $("#is_stop").val();
                var title = "是否暂停？";
                if (is_stop == 1) {
                    title = "是否继续？";
                }
                if (confirm(title)) {
                    location = "stopcustom.html?custom_id=" + $("#id").val() + "&is_stop=" + $("#is_stop").val();
                }
            });
//            $("#number").change(function(event) {
//               var num = parseInt($(this).val());
//               if(num){
//                    var n=Math.ceil(num/3);
//                    $(this).val(n*3);
//               }
//            });
            $("#radio1").click(function(event) {
                $("#prizes1").hide();
                $("#prizes2").hide();
                $("#prizes3").hide();
                $("#prizes1_url").val("");
                $("#prizes2_url").val("");
                $("#prizes3_url").val("");
            });
            $("#radio2").click(function(event) {
                $("#prizes1").show();
                $("#prizes2").show();
                $("#prizes3").show();
            });
            $("#open-sysm").click(function(event) {
                var show = $('#sysm').css('display');
                if (show=='none') {
                    $("#sysm").css('display','block'); 
                }else{
                    $("#sysm").css('display','none'); 
                }

            });
            $("#sysm").click(function(event) {
                location="instruction.html";

            });
//            $("#welfare").blur(function(){
//                var tickets = $("#tickets").val();
//                var welfare = $("#welfare").val();
//                var number = $("#number").val();
//                $("#tickets_yj").html(((tickets-welfare-(tickets*0.01))*number).toFixed(2)+" 元");
//            });
        </script>
        
        <script>
        //时间选择器
            laydate.render({
              elem: '#start_date'
              ,type: 'datetime',
              fixed: true
            });
        </script>
    </body>
</html>