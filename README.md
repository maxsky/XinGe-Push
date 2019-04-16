# xinge-api-php

<a href="https://996.icu"><img src="https://img.shields.io/badge/link-996.icu-red.svg"></a>

Composer 安装：`composer require maxsky/xg-push`



## 概述

[信鸽](http://xg.qq.com) 是腾讯云提供的一款支持**百亿级**消息的移动 App 推送平台，开发者可以调用 PHP SDK 访问信鸽推送服务。



## 官方客户端、服务端 SDK

[信鸽官网](http://xg.qq.com/xg/ctr_index/download)



## 使用指南

大幅修改了官方提供 PHP SDK，使其更友好。



### 初始化

```php
use Tencent\XinGe\XingePush;

$xinge = XingePush::Init($AppID, $AppSecret);
```



### 简易推送

我在每个方法的注释中贴上了该方法属于简易推送、基本推送、高级推送的标签。

例如：

```php
use Tencent\XinGe\Message\AndroidMsg;
use Tencent\XinGe\Message\iOSMsg;

// Android 简易推送(通知)
$xinge->PushTokenAndroid($deviceToken, '推送标题', '推送内容');
// iOS 简易推送(通知)，正式环境将 iOSMsg::IOSENV_DEV 修改为 iOSMsg::IOSENV_PROD
// 注意 iOS 和 Android 的 AppID、AppSecret 并不相同，这种情况下需要使用 Init() 方法设置
XingePush::Init($iOSAppID, $iOSAppSecret)->PushTokenIos($deviceToken, '推送标题', '推送内容', iOSMsg::IOSENV_DEV);

/** 透传（后台）消息只需将 $messageType 设置为 'message' 即可 */
XingePush::Init($AndroidAppID, $AndroidAppSecret)->PushTokenAndroid($deviceToken, '推送标题', '推送内容', AndroidMsg::TYPE_MESSAGE);
XingePush::Init($iOSAppID, $iOSAppSecret)->PushTokenIos($deviceToken, '推送标题', '推送内容', iOSMsg::IOSENV_DEV, iOSMsg::TYPE_REMOTE_MESSAGE);
```



### 普通推送

普通推送是指在简易推送的基础上多了 **点击通知行为**（Android）和 **自定义数据**（Android & iOS）

```php
use Tencent\XinGe\Component\ClickAction;
use Tencent\XinGe\Message\AndroidMsg;

// ClickAction 数组中，第一个元素为操作类型，第二个为相关数据
// 仅打开 URL 时存在第三个元素，表示打开 URL 是否提示
XingePush::Init($AndroidAppID, $AndroidAppSecret)
  	->PushAccountAndroid('推送帐号', '推送标题', '推送内容', ['自定义键' => '自定义值'],
                        [ClickAction::TYPE_URL, 'https://m.baidu.com', 1], 
                        AndroidMsg::TYPE_NOTIFICATION);
```



### 高级推送

高级推送指完全自定义，存在的方法有如下 6 个：

* PushSingleDevice - 推送给单个设备
* PushSingleAccount - 推送给单个帐号
* PushTokenList - 推送给指定设备列表
* PushAccountList - 推送给帐号列表
* PushAllDevices - 推送给所有 Android/iOS 设备
* PushTags - 推送给指定标签设备

其中所有方法均含有 `$message` 参数，该参数类型为 `AndroidMsg|iOSMsg`，即 Android 或 iOS 的消息体

使用方法如下：（以下为 **Android** 示例，**iOS** 同理但更简单，无需设置 `Style` 及`ClickAction`）

```php
use Tencent\XinGe\Component\Style;
use Tencent\XinGe\Message\AndroidMsg;
use Tencent\XinGe\XingePush;

// 实例化 Android 消息类
$message = new AndroidMsg();
// 设置标题、内容
$message->setTitle('我是标题');
$message->setContent('我是一个内容');
// 设置消息类型
$message->setMessageType(AndroidMsg::TYPE_NOTIFICATION);
// 实例化 Style 类
$style = new Style();
// 指定应用通知中 Icon 类型。默认 0 为应用本身图标，1 为指定图标资源
// IconType 为 1 时必需设置 IconRes
$style->setIconType(1);
$style->setIconRes('https://www.maxsky.cc/images/avatar.jpg');
// 实例化 ClickAction 类
$action = new ClickAction();
// 设置操作类型，打开 URL
$action->setActionType(ClickAction::TYPE_URL);
// 设置类型相关数据，此处为 URL 地址
$action->setUrl('https://m.baidu.com');
// 设置打开 URL 是否提示确认
$action->setConfirmOnUrl(0);
// 合并进消息类
$message->setAction($action);
$message->setStyle($style);
// 设置自定义数据
$message->setCustom(['key' => 'value']);
// 设置富媒体资源地址（目前仅 Android，且只能设置 1 条数据）
// 设置后下拉戳开通知栏可以看到该资源
$message->setMediaResources('https://www.maxsky.cc/images/avatar.jpg');
// 设置完成后放进去就可以推送测试了
XingePush::Init($AppID, $AppSecret)->PushSingleAccount('maxsky', $message);
```

