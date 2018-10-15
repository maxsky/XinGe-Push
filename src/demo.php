<?php
require_once 'XingeApp.php';

// var_dump(DemoPushSingleDeviceNotification());
// var_dump(DemoPushSingleDeviceMessage());
// var_dump(DemoPushSingleDeviceIOS());
// var_dump(DemoPushSingleAccount());
// var_dump(DemoPushAccountList());
// var_dump(DemoPushSingleAccountIOS());
// var_dump(DemoPushAllDevices());
// var_dump(DemoPushTags());
// var_dump(DemoQueryPushStatus());
// var_dump(DemoQueryDeviceCount());
// var_dump(DemoQueryTags());
// var_dump(DemoQueryTagTokenNum());
// var_dump(DemoQueryTokenTags());
// var_dump(DemoCancelTimingPush());
// var_dump(DemoBatchDelTag());
// var_dump(DemoBatchSetTag());
// var_dump(DemoPushAccountListMultipleNotification());
// var_dump(DemoPushDeviceListMultipleNotification());
// var_dump(DemoQueryInfoOfToken());
// var_dump(DemoQueryTokensOfAccount());
// var_dump(DemoDeleteTokenOfAccount());
// var_dump(DemoDeleteAllTokensOfAccount());

$appId = 'appId';
$secretKey = 'secretKey';
$accessId = 'accessId';

//单个设备下发通知消息
function DemoPushSingleDeviceNotification() {
    $push = new XingeApp($appId, $secretKey);
    $mess = new Message();
    $mess->setType(Message::TYPE_NOTIFICATION);
    $mess->setTitle("title");
    $mess->setContent("中午");
    $mess->setExpireTime(86400);
    $mess->setSendTime(date('Y-m-d H:i:s'));
    #含义：样式编号0，响铃，震动，不可从通知栏清除，不影响先前通知
    $style = new Style(0, 1, 1, 0, 0);
    $action = new ClickAction();
    $action->setActionType(ClickAction::TYPE_URL);
    $action->setUrl("http://xg.qq.com");
    #打开url需要用户确认
    $action->setComfirmOnUrl(1);
    $custom = array('key1' => 'value1', 'key2' => 'value2');
    $mess->setStyle($style);
    $mess->setAction($action);
    $mess->setCustom($custom);
    $acceptTime1 = new TimeInterval(0, 0, 23, 59);
    $mess->addAcceptTime($acceptTime1);
    $ret = $push->PushSingleDevice('token', $mess);
    return ($ret);
}

//单个设备下发透传消息       注：透传消息默认不展示
function DemoPushSingleDeviceMessage() {
    $push = new XingeApp($appId, $secretKey);
    $mess = new Message();
    $mess->setTitle('title');
    $mess->setContent('content');
    $mess->setExpireTime(86400);
    $mess->setSendTime(date('Y-m-d H:i:s'));
    $mess->setType(Message::TYPE_MESSAGE);
    $ret = $push->PushSingleDevice('token', $mess);
    return $ret;
}

//下发IOS设备消息
function DemoPushSingleDeviceIOS() {
    $push = new XingeApp('88311062e1e79', '66ddd9c5913269c2d4c9659328db29b7');
    $mess = new MessageIOS();
    $mess->setType(MessageIOS::TYPE_APNS_NOTIFICATION);
    $mess->setTitle('title');
    $mess->setContent('content');
    $mess->setExpireTime(86400);
    $mess->setSendTime(date('Y-m-d H:i:s'));
    //$mess->setAlert(array('key1'=>'value1'));
    $mess->setBadge(1);
    $mess->setSound("beep.wav");
    $custom = array('key1' => 'value1', 'key2' => 'value2');
    $mess->setCustom($custom);
    $acceptTime = new TimeInterval(0, 0, 23, 59);
    $mess->addAcceptTime($acceptTime);
    // $raw = '{"xg_max_payload":1,"accept_time":[{"start":{"hour":"20","min":"0"},"end":{"hour":"23","min":"59"}}],"aps":{"alert":"="}}';
    // $mess->setRaw($raw);
    $ret = $push->PushSingleDevice('token', $mess, XingeApp::IOSENV_DEV);
    return $ret;
}

//下发单个账号
function DemoPushSingleAccount() {
    $push = new XingeApp($appId, $secretKey);
    $mess = new Message();
    $mess->setType(Message::TYPE_NOTIFICATION);
    $mess->setTitle("title");
    $mess->setContent("中午");
    $mess->setExpireTime(86400);
    $mess->setSendTime(date('Y-m-d H:i:s'));
    $ret = $push->PushSingleAccount('joelliu', $mess);
    return ($ret);
}

//下发多个账号， IOS下发多个账号参考DemoPushSingleAccountIOS进行相应修改
function DemoPushAccountList() {
    $push = new XingeApp($appId, $secretKey);
    $mess = new Message();
    $mess->setType(Message::TYPE_NOTIFICATION);
    $mess->setTitle("title");
    $mess->setContent("中午");
    $mess->setExpireTime(86400);
    $mess->setSendTime(date('Y-m-d H:i:s'));
    $accountList = array('joelliu', 'hoepeng');
    $ret = $push->PushAccountList($accountList, $mess);
    return ($ret);
}

//下发IOS账号消息
function DemoPushSingleAccountIOS() {
    $push = new XingeApp('88311062e1e79', '66ddd9c5913269c2d4c9659328db29b7');
    $mess = new MessageIOS();
    $mess->setType(MessageIOS::TYPE_APNS_NOTIFICATION);
    $mess->setTitle('title');
    $mess->setContent('content');
    $mess->setExpireTime(86400);
    $mess->setSendTime(date('Y-m-d H:i:s'));
    //$mess->setAlert(array('key1'=>'value1'));
    $mess->setBadge(1);
    $mess->setSound("beep.wav");
    $custom = array('key1' => 'value1', 'key2' => 'value2');
    $mess->setCustom($custom);
    $acceptTime1 = new TimeInterval(0, 0, 23, 59);
    $mess->addAcceptTime($acceptTime1);
    $ret = $push->PushSingleAccount('joelliu', $mess, XingeApp::IOSENV_DEV);
    return $ret;
}

//下发所有设备
function DemoPushAllDevices() {
    $push = new XingeApp($appId, $secretKey);
    $mess = new Message();
    $mess->setType(Message::TYPE_NOTIFICATION);
    $mess->setTitle("title");
    $mess->setContent("中午");
    $mess->setExpireTime(86400);
    $mess->setSendTime(date('Y-m-d H:i:s'));
    #含义：样式编号0，响铃，震动，不可从通知栏清除，不影响先前通知
    $style = new Style(0, 1, 1, 0, 0);
    $action = new ClickAction();
    $action->setActionType(ClickAction::TYPE_URL);
    $action->setUrl("http://xg.qq.com");
    #打开url需要用户确认
    $action->setComfirmOnUrl(1);
    $mess->setStyle($style);
    $mess->setAction($action);
    $acceptTime1 = new TimeInterval(0, 0, 23, 59);
    $mess->addAcceptTime($acceptTime1);
    $ret = $push->PushAllDevices($mess);
    return ($ret);
}

//下发标签选中设备
function DemoPushTags() {
    $push = new XingeApp($appId, $secretKey);
    $mess = new Message();
    $mess->setType(Message::TYPE_NOTIFICATION);
    $mess->setTitle("title");
    $mess->setContent("中午");
    $mess->setExpireTime(86400);
    $mess->setSendTime(date('Y-m-d H:i:s'));
    $tagList = array('Demo3', 'Demo2');
    $ret = $push->PushTags($tagList, 'OR', $mess);
    return ($ret);
}

//查询消息推送状态
function DemoQueryPushStatus() {
    $push = new XingeApp($appId, $secretKey, $accessId);
    $pushIdList = array('31', '32');
    $ret = $push->QueryPushStatus($pushIdList);
    return ($ret);
}

//查询设备数量
function DemoQueryDeviceCount() {
    $push = new XingeApp($appId, $secretKey, $accessId);
    $ret = $push->QueryDeviceCount();
    return ($ret);
}

//查询标签
function DemoQueryTags() {
    $push = new XingeApp($appId, $secretKey, $accessId);
    $ret = $push->QueryTags(0, 100);
    return ($ret);
}

//查询某个tag下token的数量
function DemoQueryTagTokenNum() {
    $push = new XingeApp($appId, $secretKey, $accessId);
    $ret = $push->QueryTagTokenNum("tag");
    return ($ret);
}

//查询某个token的标签
function DemoQueryTokenTags() {
    $push = new XingeApp($appId, $secretKey, $accessId);
    $ret = $push->QueryTokenTags("token");
    return ($ret);
}

//取消定时任务
function DemoCancelTimingPush() {
    $push = new XingeApp($appId, $secretKey, $accessId);
    $ret = $push->CancelTimingPush("32");
    return ($ret);
}

// 设置标签
function DemoBatchSetTag() {
    // 切记把这里的示例tag和示例token修改为你的真实tag和真实token
    $pairs = array();
    array_push($pairs, new TagTokenPair("tag1", "token00000000000000000000000000000000001"));
    array_push($pairs, new TagTokenPair("tag1", "token00000000000000000000000000000000001"));

    $push = new XingeApp($appId, $secretKey, $accessId);
    $ret = $push->BatchSetTag($pairs);
    return $ret;
}

// 删除标签
function DemoBatchDelTag() {
    // 切记把这里的示例tag和示例token修改为你的真实tag和真实token
    $pairs = array();
    array_push($pairs, new TagTokenPair("tag1", "token00000000000000000000000000000000001"));
    array_push($pairs, new TagTokenPair("tag1", "token00000000000000000000000000000000001"));

    $push = new XingeApp($appId, $secretKey, $accessId);
    $ret = $push->BatchDelTag($pairs);
    return $ret;
}

//查询某个token的信息
function DemoQueryInfoOfToken() {
    $push = new XingeApp($appId, $secretKey, $accessId);
    $ret = $push->QueryInfoOfToken("token");
    return ($ret);
}

//查询某个account绑定的token
function DemoQueryTokensOfAccount() {
    $push = new XingeApp($appId, $secretKey, $accessId);
    $ret = $push->QueryTokensOfAccount("nickName");
    return ($ret);
}

//删除某个account绑定的所有token
function DemoDeleteAllTokensOfAccount() {
    $push = new XingeApp($appId, $secretKey, $accessId);
    $ret = $push->DeleteAllTokensOfAccount("nickName");
    return ($ret);
}