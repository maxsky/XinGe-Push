<?php

require_once '../vendor/autoload.php';

use Tencent\XinGe\Component\ClickAction;
use Tencent\XinGe\Component\Style;
use Tencent\XinGe\Component\TimeInterval;
use Tencent\XinGe\Message\AndroidMsg;
use Tencent\XinGe\Message\iOSMsg;
use Tencent\XinGe\XingePush;

const APP_ID = 'appId';
const SECRET_KEY = 'secretKey';

var_dump(DemoPushSingleDeviceNotification());
var_dump(DemoPushSingleDeviceMessage());
var_dump(DemoPushSingleDeviceIOS());
var_dump(DemoPushSingleAccount());
var_dump(DemoPushAccountList());
var_dump(DemoPushSingleAccountIOS());
var_dump(DemoPushAllDevices());
var_dump(DemoPushAllIOS());
var_dump(DemoQueryDeviceStatus());

// 单个 Android 设备下发通知消息
function DemoPushSingleDeviceNotification() {
    $push = XingePush::Init(APP_ID, SECRET_KEY);
    $mess = new AndroidMsg();
    $mess->setMessageType(AndroidMsg::TYPE_NOTIFICATION);
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
    $action->setConfirmOnUrl(1);
    $custom = ['key1' => 'value1', 'key2' => 'value2'];
    $mess->setStyle($style);
    $mess->setAction($action);
    $mess->setCustom($custom);
    $acceptTime1 = new TimeInterval(0, 0, 23, 59);
    $mess->addAcceptTime($acceptTime1);
    $ret = $push->PushSingleDevice('token', $mess);
    return $ret;
}

// 单个 Android 设备下发透传消息。注：透传消息默认不展示
function DemoPushSingleDeviceMessage() {
    $push = XingePush::Init(APP_ID, SECRET_KEY);
    $mess = new AndroidMsg();
    $mess->setTitle('title');
    $mess->setContent('content');
    $mess->setExpireTime(86400);
    $mess->setSendTime(date('Y-m-d H:i:s'));
    $mess->setMessageType(AndroidMsg::TYPE_MESSAGE);
    $ret = $push->PushSingleDevice('token', $mess);
    return $ret;
}

// 下发 iOS 设备消息
function DemoPushSingleDeviceIOS() {
    $push = $push = XingePush::Init(APP_ID, SECRET_KEY);
    $mess = new iOSMsg();
    $mess->setMessageType(iOSMsg::TYPE_APNS_NOTIFICATION);
    $mess->setTitle('title');
    $mess->setContent('content');
    $mess->setExpireTime(86400);
    $mess->setSendTime(date('Y-m-d H:i:s'));
    //$mess->setAlert(array('key1'=>'value1'));
    $mess->setBadgeType(1);
    $mess->setSound("beep.wav");
    $custom = ['key1' => 'value1', 'key2' => 'value2'];
    $mess->setCustom($custom);
    $acceptTime = new TimeInterval(0, 0, 23, 59);
    $mess->addAcceptTime($acceptTime);
    // $raw = '{"xg_max_payload":1,"accept_time":[{"start":{"hour":"20","min":"0"},"end":{"hour":"23","min":"59"}}],"aps":{"alert":"="}}';
    // $mess->setRaw($raw);
    $ret = $push->PushSingleDevice('token', $mess);
    return $ret;
}

// 下发单个 Android 账号
function DemoPushSingleAccount() {
    $push = XingePush::Init(APP_ID, SECRET_KEY);
    $mess = new AndroidMsg();
    $mess->setMessageType(AndroidMsg::TYPE_NOTIFICATION);
    $mess->setTitle("title");
    $mess->setContent("中午");
    $mess->setExpireTime(86400);
    $mess->setSendTime(date('Y-m-d H:i:s'));
    $ret = $push->PushSingleAccount('joelliu', $mess);
    return $ret;
}

// 下发多个账号，iOS 下发多个账号参考 DemoPushSingleAccountIOS 进行相应修改
function DemoPushAccountList() {
    $push = XingePush::Init(APP_ID, SECRET_KEY);
    $mess = new AndroidMsg();
    $mess->setMessageType(AndroidMsg::TYPE_NOTIFICATION);
    $mess->setTitle("title");
    $mess->setContent("中午");
    $mess->setExpireTime(86400);
    $mess->setSendTime(date('Y-m-d H:i:s'));
    $accountList = ['joelliu', 'hoepeng'];
    $ret = $push->PushAccountList($accountList, $mess);
    return $ret;
}

// 下发 iOS 账号消息
function DemoPushSingleAccountIOS() {
    $push = XingePush::Init(APP_ID, SECRET_KEY);
    $mess = new iOSMsg();
    $mess->setMessageType(iOSMsg::TYPE_APNS_NOTIFICATION);
    $mess->setTitle('title');
    $mess->setContent('content');
    $mess->setExpireTime(86400);
    $mess->setSendTime(date('Y-m-d H:i:s'));
    //$mess->setAlert(array('key1'=>'value1'));
    $mess->setBadgeType(1);
    $mess->setSound("beep.wav");
    $custom = ['key1' => 'value1', 'key2' => 'value2'];
    $mess->setCustom($custom);
    $acceptTime1 = new TimeInterval(0, 0, 23, 59);
    $mess->addAcceptTime($acceptTime1);
    $ret = $push->PushSingleAccount('joelliu', $mess);
    return $ret;
}

// 下发所有 Android 设备
function DemoPushAllDevices() {
    $push = XingePush::Init(APP_ID, SECRET_KEY);
    $mess = new AndroidMsg();
    $mess->setMessageType(AndroidMsg::TYPE_NOTIFICATION);
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
    $action->setConfirmOnUrl(1);
    $mess->setStyle($style);
    $mess->setAction($action);
    $acceptTime1 = new TimeInterval(0, 0, 23, 59);
    $mess->addAcceptTime($acceptTime1);
    $ret = $push->PushAllDevices($mess);
    return $ret;
}

function DemoPushAllIOS() {
    $push = XingePush::Init(APP_ID, SECRET_KEY);
    $mess = new iOSMsg();
    $mess->setMessageType(iOSMsg::TYPE_APNS_NOTIFICATION);
    $mess->setTitle('title');
    $mess->setContent('content');
    $mess->setExpireTime(86400);
    $mess->setSendTime(date('Y-m-d H:i:s'));
    //$mess->setAlert(array('key1'=>'value1'));
    $mess->setBadgeType(1);
    $mess->setSound("beep.wav");
    $custom = ['key1' => 'value1', 'key2' => 'value2'];
    $mess->setCustom($custom);
    $acceptTime1 = new TimeInterval(0, 0, 23, 59);
    $mess->addAcceptTime($acceptTime1);
    $ret = $push->PushAllDevices($mess);
    return $ret;
}

function DemoQueryDeviceStatus() {
    $push = XingePush::Init(APP_ID, SECRET_KEY);
    $ret = $push->QueryDeviceStatus(['aaaaaaaaa', 'bbbbbbbbb'], '123456789');
    return $ret;
}



