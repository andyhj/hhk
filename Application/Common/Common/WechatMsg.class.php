<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Common\Common;

/**
 * Description of WechatMsg
 *
 * @author Administrator
 */
use Common\Model\UserModel;
use Common\WxApi\class_weixin_adv;
use Common\Common\Redis;
class WechatMsg {
    public function valid()  //用于申请 成为开发者 时向微信发送验证信息。
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }


    //开发者通过检验signature对请求进行校验（下面有校验方式）。若确认此次GET请求来自微信服务器，请求原样返回echostr参数内容，则接入生效，否则接入失败。
    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];    
                
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

    //响应消息
    public function responseMsg()
    {
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if (!empty($postStr)){
            $this->logger("R ".$postStr);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);
             add_log("wx_msg.log", "wxmsg", "消息类型：". var_export($RX_TYPE,true));
             add_log("wx_msg.log", "wxmsg", "消息内容：". var_export($postObj,true));
            //消息类型分离
            switch ($RX_TYPE)
            {
                case "event":
                    $result = $this->receiveEvent($postObj);
                    break;
                case "text":
                    $result = $this->receiveText($postObj);
                    break;
                case "image":
                    $result = $this->receiveImage($postObj);
                    break;
                case "location":
                    $result = $this->receiveLocation($postObj);
                    break;
                case "voice":
                    $result = $this->receiveVoice($postObj);
                    break;
                case "video":
                    $result = $this->receiveVideo($postObj);
                    break;
                case "link":
                    $result = $this->receiveLink($postObj);
                    break;
                default:
                    $result = "unknown msg type: ".$RX_TYPE;
                    break;
            }
            $this->logger("T ".$result);
            echo $result;
        }else {
            echo "";
            exit;
        }
    }

    //接收事件消息
    private function receiveEvent($object)
    {
        add_log("wx_msg.log", "wxmsg", "操作内容：". var_export($object,true));
        $this->logger("receiveEvent: ".$object);
        $content = "";
        switch ($object->Event)
        {
            case "subscribe":
                $user_model = new UserModel();
                $custom = D("Custom");
                $other_id = trim($object->FromUserName);
                $user_info = $user_model->getUserOneByOtherId($other_id);
                $m_redis = new Redis();
                $code_id = $m_redis->get("custom_codeid".$user_info["other_id"]);
                if(!$user_info["is_msg"]){
                    $user_model->updIsMsg($user_info["id"]);
                    // require_once $_SERVER['DOCUMENT_ROOT'] . "/Application/Common/Concrete/wxapi/example/weixin.api.php";
                    // $weixin = new class_weixin_adv();
                    // $msg_data = $user_model->subscribeMsg($user_info["id"]);
                    // $weixin->send_user_message($msg_data);
                    $content = "欢迎关注开心娱乐微信公众号";
                }else{
                    $content = "欢迎关注开心娱乐微信公众号";
                }
                if($code_id){
                    $m_redis->rm("custom_codeid".$user_info["other_id"]);
                    $custom_id = $code_id;
                    $code_where["id"] = $custom_id;
                    $code_info = $custom->getCodeOne($code_where);
                    $c_where["id"] = $code_info["c_id"];
                    $custom_info = $custom->getOne($c_where);
                    $content = "点击进入比赛";
                    $news["Title"] = $custom_info["name"];
                    $news["Description"] = $content;
                    $news["PicUrl"] = CDN_HOST."/images/match/ddzmatchlogo2.png";
                    $news["Url"] = 'http://'.$_SERVER['HTTP_HOST'].'/index/index/custom/?code_id='.$custom_id;
                    $newsArray[] = $news;
                    $result = $this->transmitNews($object, $newsArray);
                    return $result;
                }
                $custom_id = 0;
                if($object->EventKey){
                    $custom_arr = explode("_", $object->EventKey);
                    if(isset($custom_arr[1])){
                        $custom_id = $custom_arr[1];
                        $code_where["id"] = $custom_id;
                        $code_info = $custom->getCodeOne($code_where);
                        $c_where["id"] = $code_info["c_id"];
                        $custom_info = $custom->getOne($c_where);
                        $content = "点击进入比赛";
                        $news["Title"] = $custom_info["name"];
                        $news["Description"] = $content;
                        $news["PicUrl"] = CDN_HOST."/images/match/ddzmatchlogo2.png";
                        $news["Url"] = 'http://'.$_SERVER['HTTP_HOST'].'/index/index/custom/?code_id='.$custom_id;
                        $newsArray[] = $news;
                        $result = $this->transmitNews($object, $newsArray);
                        return $result;
                    }
                }
                break;
            case "unsubscribe":
                $content = "取消关注";
                break;
            case "SCAN":
                $content = "点击进入游戏";
                $news["Title"] = "欢迎关注开心娱乐微信公众号";
                $news["Description"] = $content;
                $news["PicUrl"] = CDN_HOST."/images/match/ddzmatchlogo2.png";
                $news["Url"] = 'http://'.$_SERVER['HTTP_HOST'].'/index/index/custom/?code_id='.$object->EventKey;
                $newsArray[] = $news;
                $result = $this->transmitNews($object, $newsArray);
                return $result;
                break;
            case "CLICK":
                switch ($object->EventKey)
                {
                    case "rgkf":
                        $content = "您好，很高兴为您服务，点击左下角进行会话，会话结束再点击左下角切换到菜单状态";
                        break;
                    case "syzn":
                        $content = "敬请期待";
                        break;
                    case "fwzx":
                        $content = "敬请期待";
                        break;
                    default:
                        $content = "欢迎关注开心娱乐微信公众号";
                        break;
                }
                break;
            case "LOCATION":
                $content = "上传位置：纬度 ".$object->Latitude.";经度 ".$object->Longitude;
                break;
            case "VIEW":
                $content = "跳转链接 ".$object->EventKey."?ToUserName=". $object->ToUserName;
                break;
            case "MASSSENDJOBFINISH":
                $content = "消息ID：".$object->MsgID."，结果：".$object->Status."，粉丝数：".$object->TotalCount."，过滤：".$object->FilterCount."，发送成功：".$object->SentCount."，发送失败：".$object->ErrorCount;
                break;
            default:
                $content = "receive a new event: ".$object->Event;
                break;
        }
        if(is_array($content)){
            if (isset($content[0])){
                $result = $this->transmitNews($object, $content);
            }else if (isset($content['MusicUrl'])){
                $result = $this->transmitMusic($object, $content);
            }
        }else{
            $result = $this->transmitText($object, $content);
        }

        return $result;
    }

    private function receiveText($object)
    {
        $keyword = trim($object->Content);
        if (strpos($keyword, "@")){
            $music = explode("@",$keyword);
            $url = "http://apix.sinaapp.com/music/?appkey=".$object->ToUserName."&singer=".urlencode($music[0])."&song=".urlencode($music[1]);
            //$url = "http://api100.duapp.com/song/?appkey=trialuser&name=%E6%96%B0%E5%B9%B4%E5%A5%BD";
            $output = file_get_contents($url);
            $content = json_decode($output, true);
            $result = $this->transmitMusic($object, $content);
        }else{

            if (strpos($keyword, "天气")){
                $tianqi = explode("天",$keyword);
                $cityName = "http://apix.sinaapp.com/weather/?appkey=".$object->ToUserName."&city=".urlencode($tianqi[0]);
                $output = file_get_contents($cityName);
                $content = json_decode($output, true);
                $result = $this->transmitNews($object, $content);

            }elseif(strpos($keyword, "翻译")){
                $tianqi = explode("译",$keyword);
                $url_youdao  = "http://fanyi.youdao.com/openapi.do?keyfrom=ig2play&key=1697028104&type=data&doctype=json&version=1.1&q=".$tianqi[1];
                $output = file_get_contents($url_youdao);
                $content = json_decode($output, true);
                $trans = $content['web'][0];
                $result = $this->transmitText($object,$trans);
                
                
            }else{
                if($keyword=="好"||strpos($keyword,"好")){
                    $content = "您好，有什么可以帮到您的吗？";
                }elseif($keyword=="游戏"||strpos($keyword,"游戏")){
                    $content = "开心逗棋牌一共有六款游戏，分别是斗地主、牛牛、诈金花、德州扑克、四川麻将和象棋";
                }elseif(($keyword=="兑换礼品"||strpos($keyword,"兑换礼品"))||($keyword=="券"||strpos($keyword,"券"))||($keyword=="商城"||strpos($keyword,"商城"))){
                    $content = "如何兑换礼品：\n用开心豆报名参加任意一款游戏的即可获得兑换券（无论输赢以及放弃比赛）；\n然后在“礼品商城”用兑换券兑换手机、平板、话费等礼品；\n回复“赚开心豆”了解如何赚取更多开心豆";
                }elseif(($keyword=="台费"||strpos($keyword,"台费"))||($keyword=="提成"||strpos($keyword,"提成"))||($keyword=="返点"||strpos($keyword,"返点"))||($keyword=="返现"||strpos($keyword,"返现"))||($keyword=="福利"||strpos($keyword,"福利"))){
                    $content = "贵宾：下线充值返点最高3%、充值优惠80%；\n金尊：下线充值返点最高6%、充值优惠75%；\n至尊：下线充值返点最高9%、充值优惠70%；\n白金：下线充值返点最高9%、充值优惠70%、台费提成最高30%、发展收费代理提成最高40%；\n黑金：下线充值返点最高9%、充值优惠70%、台费提成最高45%、发展收费代理提成最高50%；\n钻石：下线充值返点最高9%、充值优惠70%、台费提成最高50%、发展收费代理提成最高60%；";
                }elseif($keyword=="等级"){
                    $content = "免费代理有：注册、贵宾、金尊、至尊四个等级；\n免费代理可享受充值优惠和下级用户的充值提成；\n付费代理有：白金、黑金、钻石三个等级；\n可享受更高的以上福利外，还可享受发展用户在游戏中消耗台费的提成；\n免费代理成功推荐5个同级别用户即可提升等级；\n付费代理还额外需要缴纳一定费用（也可用开心豆代缴）；回复“福利”查看更多详情！";
                }elseif($keyword=="赚开心豆"||strpos($keyword,"赚")){
                    $content = "如何赚取开心豆：\n您可以在游戏中赢得更多的开心豆；\n当然您也可以将“个人中心”-“我的推广”中的文章发到朋友圈或在游戏中分享，通过发展下线来获得推荐奖励；\n或者鼓励您发展的用户充值或发展更多下线；\n回复“佣金”了解如何赚取提成！";
                }elseif($keyword=="佣金"||strpos($keyword,"佣金")){
                    $content = "如何赚取佣金：\n如果您是免费代理，可以通过鼓励下级用户（或代理）充值，或者将他们培养成您的同级代理来提升您的等级从而提升您提成比例；\n如果您是付费代理，鼓励下级用户参与游戏或推荐他们当代理都是很好的方案；\n回复“目录”查看所有帮助";
                }elseif($keyword=="目录"||strpos($keyword,"目录")){
                    $content = "回复“兑换礼品”，了解如何兑换话费、手机等礼品；\n回复“赚开心豆”，了解如何赚取更多开心豆；\n回复“佣金”，了解如何赚取提成；\n回复“等级”，了解代理等级及福利；\n回复“福利”，了解详细的代理提成；\n";
                }else{
                    $content = "有其它问题可以通过电话联系我们，我们电话是0755-22671514，欢迎您的来电";
                }
                //$date = date("Ymd");
//                if($date=="20180302"){
//                    $user_model = new UserModel();
//                    $other_id = trim($object->FromUserName);
//                    $user_info = $user_model->getUserOneByOtherId($other_id);
//                    $le_where["uid"] = $user_info["id"];
//                    $le_where["logtype"] = 92;
//                    $le_info = $user_model->getGameLogEconomy($le_where);
//                    if($keyword=="答题"||strpos($keyword,"答题")){
//                        $content = "开心逗棋牌答题活动共五题，答完即送20000开心豆！\n准备好了请回复“开始”";
//                    }elseif($keyword=="开始"){
//                        $content = "第一题：开心逗棋牌一共有几款游戏？";
//                    }elseif(($keyword=="6"||strpos($keyword,"6"))||($keyword=="六"||strpos($keyword,"六"))){
//                        $content = "答题正确！\n开心逗棋牌一共有六款游戏，分别是斗地主、牛牛、诈金花、德州扑克、四川麻将和象棋；\n回复“好玩”进入下一题";
//                    }elseif($keyword=="好玩"){
//                        $content = "第二题：玩开心逗棋牌如何赚手机、平板、话费？";
//                    }elseif(($keyword=="兑换"||strpos($keyword,"兑换"))||($keyword=="券"||strpos($keyword,"券"))||($keyword=="商城"||strpos($keyword,"商城"))){
//                        $content = "答题正确！\n用开心豆报名参加任意一款游戏的即可获得兑换券（无论输赢以及放弃比赛）；\n然后在“礼品商城”用兑换券兑换手机、平板、话费等礼品；\n回复“福利”进入下一题";
//                    }elseif($keyword=="福利"){
//                        $content = "第三题：代理用户有什么福利？";
//                    }elseif(($keyword=="台费"||strpos($keyword,"台费"))||($keyword=="提成"||strpos($keyword,"提成"))||($keyword=="返点"||strpos($keyword,"返点"))||($keyword=="返现"||strpos($keyword,"返现"))){
//                        $content = "答题正确！\n免费代理可享受发展用户充值的返点，并且自己充值拥有优惠；\n收费代理可享受更高的以上福利外，还可享受发展用户在游戏中消耗台费的提成；\n回复“等级”进入下一题";
//                    }elseif($keyword=="等级"){
//                        $content = "第四题：用户如何提升等级？";
//                    }elseif(($keyword=="发展"||strpos($keyword,"发展"))||($keyword=="分享"||strpos($keyword,"分享"))||($keyword=="推荐"||strpos($keyword,"推荐"))||($keyword=="拉"||strpos($keyword,"拉"))||($keyword=="缴费"||strpos($keyword,"缴费"))||($keyword=="花钱"||strpos($keyword,"花钱"))||($keyword=="邀请"||strpos($keyword,"邀请"))){
//                        $content = "答题正确！\n免费代理有：注册用户、贵宾用户、金尊用户、至尊用户四个等级；\n付费代理有：白金代理、黑金代理、钻石代理三个等级；\n免费代理成功推荐5个同级别用户即可提升等级；\n付费代理还额外需要缴纳一定费用（也可用开心豆代缴）；\n回复“灯谜”进入最后一题";
//                    }elseif($keyword=="灯谜"){
//                        $content = "第五题：粽子脸，梅花脚。前面喊叫，后面舞刀。（打一动物名）？";
//                    }elseif($keyword=="狗"||strpos($keyword,"狗")){
//                        if(!$le_info){
//                            $user_model->addGameLogEconomy($user_info["id"]);
//                            $content = "恭喜您完成答题活动，活动赠送的20000开心豆已经到帐，继续开心“逗”棋牌吧！";
//                        }else{
//                            $content = "恭喜您完成答题活动，您已经领取过答题奖励了，不好意思，该奖励每人限领一次；";
//                        }
//                    }else{
//                        $content = "回复“开始”可以重新开始答题；\n有其它问题可以通过电话联系我们，我们电话是0755-22671514，欢迎您的来电~";
//                    }
//                    
//                }else{
//                    $content = "有其它问题可以通过电话联系我们，我们电话是0755-22671514，欢迎您的来电";
//                }
                
                
                $result = $this->transmitText($object, $content);
            }
            
        }
        return $result;
    }



    //接收图片消息
    private function receiveImage($object)
    {
        $content = array("MediaId"=>$object->MediaId);
        $result = $this->transmitImage($object, $content);
        return $result;
    }

    //接收位置消息
    private function receiveLocation($object)
    {
        $content = "你发送的是位置，纬度为：".$object->Location_X."；经度为：".$object->Location_Y."；缩放级别为：".$object->Scale."；位置为：".$object->Label;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    //接收语音消息
    private function receiveVoice($object)
    {
        if (isset($object->Recognition) && !empty($object->Recognition)){
            $content = "你刚才说的是：".$object->Recognition;
            $result = $this->transmitText($object, $content);
        }else{
            $content = array("MediaId"=>$object->MediaId);
            $result = $this->transmitVoice($object, $content);
        }

        return $result;
    }

    //接收视频消息
    private function receiveVideo($object)
    {
        $content = array("MediaId"=>$object->MediaId, "ThumbMediaId"=>$object->ThumbMediaId, "Title"=>"", "Description"=>"");
        $result = $this->transmitVideo($object, $content);
        return $result;
    }

    //接收链接消息
    private function receiveLink($object)
    {
        $content = "你发送的是链接，标题为：".$object->Title."；内容为：".$object->Description."；链接地址为：".$object->Url;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    //回复文本消息
    private function transmitText($object, $content)
    {
        $xmlTpl = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[text]]></MsgType>
            <Content><![CDATA[%s]]></Content>
            </xml>";
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), $content);
        return $result;
    }

    //回复图片消息
    private function transmitImage($object, $imageArray)
    {
        $itemTpl = "<Image>
            <MediaId><![CDATA[%s]]></MediaId>
        </Image>";

        $item_str = sprintf($itemTpl, $imageArray['MediaId']);

        $xmlTpl = "<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[image]]></MsgType>
                $item_str
                </xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //回复语音消息
    private function transmitVoice($object, $voiceArray)
    {
        $itemTpl = "<Voice>
                <MediaId><![CDATA[%s]]></MediaId>
            </Voice>";

        $item_str = sprintf($itemTpl, $voiceArray['MediaId']);

        $xmlTpl = "<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[voice]]></MsgType>
                $item_str
                </xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //回复视频消息
    private function transmitVideo($object, $videoArray)
    {
        $itemTpl = "<Video>
            <MediaId><![CDATA[%s]]></MediaId>
            <ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
            <Title><![CDATA[%s]]></Title>
            <Description><![CDATA[%s]]></Description>
        </Video>";

        $item_str = sprintf($itemTpl, $videoArray['MediaId'], $videoArray['ThumbMediaId'], $videoArray['Title'], $videoArray['Description']);

        $xmlTpl = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[video]]></MsgType>
            $item_str
            </xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //回复图文消息
    private function transmitNews($object, $newsArray)
    {
        if(!is_array($newsArray)){
            return;
        }
        $itemTpl = "    <item>
                <Title><![CDATA[%s]]></Title>
                <Description><![CDATA[%s]]></Description>
                <PicUrl><![CDATA[%s]]></PicUrl>
                <Url><![CDATA[%s]]></Url>
            </item>
        ";
        $item_str = "";
        foreach ($newsArray as $item){
            $item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);
        }
        $xmlTpl = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[news]]></MsgType>
            <ArticleCount>%s</ArticleCount>
            <Articles>
            $item_str</Articles>
            </xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), count($newsArray));
        return $result;
    }

    //回复音乐消息
    private function transmitMusic($object, $musicArray)
    {
        $itemTpl = "<Music>
            <Title><![CDATA[%s]]></Title>
            <Description><![CDATA[%s]]></Description>
            <MusicUrl><![CDATA[%s]]></MusicUrl>
            <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
        </Music>";

        $item_str = sprintf($itemTpl, $musicArray['Title'], $musicArray['Description'], $musicArray['MusicUrl'], $musicArray['HQMusicUrl']);

        $xmlTpl = "<xml>
        <ToUserName><![CDATA[%s]]></ToUserName>
        <FromUserName><![CDATA[%s]]></FromUserName>
        <CreateTime>%s</CreateTime>
        <MsgType><![CDATA[music]]></MsgType>
        $item_str
        </xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //回复多客服消息
    private function transmitService($object)
    {
        $xmlTpl = "<xml>
        <ToUserName><![CDATA[%s]]></ToUserName>
        <FromUserName><![CDATA[%s]]></FromUserName>
        <CreateTime>%s</CreateTime>
        <MsgType><![CDATA[transfer_customer_service]]></MsgType>
        </xml>";
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //日志记录
    private function logger($log_content)
    {
        if(isset($_SERVER['HTTP_APPNAME'])){   //SAE
            sae_set_display_errors(false);
            sae_debug($log_content);
            sae_set_display_errors(true);
        }else if($_SERVER['REMOTE_ADDR'] != "127.0.0.1"){ //LOCAL
            $max_size = 10000;
            $log_filename = 'log/weixin'.date('Ymd',time()).'.txt';
            if(file_exists($log_filename) and (abs(filesize($log_filename)) > $max_size)){unlink($log_filename);}
            file_put_contents($log_filename, date('H:i:s')." ".$log_content."\r\n", FILE_APPEND);
        }
    }
}
