<?php

/*
 * Copyright © 1998 - 2018 Tencent. All Rights Reserved. 腾讯公司 版权所有
 */

namespace Tencent\XinGe;

use Tencent\XinGe\Component\ClickAction;
use Tencent\XinGe\Message\AndroidMsg;
use Tencent\XinGe\Message\iOSMsg;
use Tencent\XinGe\Utils\RequestBase;

class XingePush {

    const RESTAPI_PUSH = 'https://openapi.xg.qq.com/v3/push/app';
    const RESTAPI_TAG = 'https://openapi.xg.qq.com/v3/device/tag';
    // 账号绑定与解绑
    const RESTAPI_BIND_ACCOUNT = 'https://openapi.xg.qq.com/v3/device/account/batchoperate';
    // 账号绑定查询
    const RESTAPI_BIND_QUERY = 'https://openapi.xg.qq.com/v3/device/account/query';

    // 统计

    // 推送统计明细
    const RESTAPI_STATISTICS_RECEIVED = 'https://openapi.xg.qq.com/xg-web-service-push/xg/statistics/received';
    // 明细数据下载请求
    const RESTAPI_STATISTICS_STATUS = 'https://openapi.xg.qq.com/xg-web-service-push/xg/statistics/statusAsync';
    // 明细数据下载状态（是否可下载）
    const RESTAPI_STATISTICS_QUERY = 'https://openapi.xg.qq.com/xg-web-service-push/xg/statistics/statusAsyncTaskQuery';
    // 明细数据下载
    const RESTAPI_STATISTICS_DOWNLOAD = 'https://openapi.xg.qq.com/xg-web-service-push/xg/statistics/filedownLoad';
    // 设备状态查询（暂未实现）
    const RESTAPI_DEVICE_STATUS = 'https://openapi.xg.qq.com/xg-web-service-push/xg/statistics/devicestatus';

    /** @var XingePush $xinge */
    private static $xinge;

    private $appId;        // 应用 App ID
    private $secretKey;    // 应用 Secret Key

    public function __construct($appId, $secretKey) {
        assert(isset($appId) && isset($secretKey));

        $this->appId = $appId;
        $this->secretKey = $secretKey;
    }

    public static function Init($appId = '', $secretKey = '') {
        if (!self::$xinge) {
            self::$xinge = new self($appId, $secretKey);
        }
        if ($appId && $secretKey) {
            self::$xinge->setSecretInfo($appId, $secretKey);
        }
        return self::$xinge;
    }

    private function setSecretInfo($appId, $secretKey) {
        $this->appId = $appId;
        $this->secretKey = $secretKey;
    }

    /**
     * 高级推送。推送消息给指定设备，需自行配置消息体。
     *
     * @param string            $deviceToken 设备 Token
     * @param AndroidMsg|iOSMsg $message 推送消息体
     *
     * @return array|mixed
     */
    public function PushSingleDevice($deviceToken, $message) {
        $ret = ['ret_code' => -1, 'err_msg' => 'Message is not valid'];

        if (!($message instanceof AndroidMsg) && !($message instanceof iOSMsg)) {
            return $ret;
        }

        if (!$message->isValid()) {
            $ret['err_msg'] = "Message params invalid";
            return $ret;
        }

        $params['audience_type'] = 'token';
        $params['token_list'] = [$deviceToken];

        if ($message instanceof AndroidMsg) {
            $params['platform'] = 'android'; // android：安卓；ios：苹果；all：安卓 & 苹果。仅支持全量推送和标签推送
            $params['multi_pkg'] = $message->getMultiPkg();
            $action = $this->parseWithIntent($message->getAction(), $message->getCustomData());
            $message->setAction($action);
        }

        if ($message instanceof iOSMsg) {
            // iOS 消息体需验证是否设置正确环境
            $environment = $message->getEnvironment();
            if ($environment !== iOSMsg::IOSENV_DEV && $environment !== iOSMsg::IOSENV_PROD) {
                $ret['err_msg'] = "iOS message environment invalid";
                return $ret;
            }
            $params['platform'] = 'ios';
            $params['environment'] = $environment;
        }

        $params['message_type'] = $message->getMessageType();
        $params['message'] = $message->getResult();
        $params['expire_time'] = $message->getExpireTime();
        $params['send_time'] = $message->getSendTime();

        if ($message->getStatTag()) {
            $params['stat_tag'] = $message->getStatTag();
        }
        return $this->callRestful(self::RESTAPI_PUSH, $params);
    }

    /**
     * 高级推送。推送消息给单个账户，需自行配置消息体。
     *
     * @param string            $account 推送账户
     * @param AndroidMsg|iOSMsg $message 推送消息体
     *
     * @return array|mixed
     */
    public function PushSingleAccount($account, $message) {
        $ret = ['ret_code' => -1];

        if (!is_string($account) || empty($account)) {
            $ret['err_msg'] = 'Account is not valid';
            return $ret;
        }

        if (!($message instanceof AndroidMsg) && !($message instanceof iOSMsg)) {
            $ret['err_msg'] = 'Message is not valid';
            return $ret;
        }

        if (!$message->isValid()) {
            $ret['err_msg'] = "Message params invalid";
            return $ret;
        }

        $params['audience_type'] = 'account';
        $params['account_list'] = [$account];

        if ($message instanceof AndroidMsg) {
            $params['platform'] = 'android'; // android：安卓；ios：苹果；all：安卓 & 苹果。仅支持全量推送和标签推送
            $params['multi_pkg'] = $message->getMultiPkg();
            $action = $this->parseWithIntent($message->getAction(), $message->getCustomData());
            $message->setAction($action);
        }

        if ($message instanceof iOSMsg) {
            $environment = $message->getEnvironment();
            if ($environment !== iOSMsg::IOSENV_DEV && $environment !== iOSMsg::IOSENV_PROD) {
                $ret['err_msg'] = 'iOS message environment invalid';
                return $ret;
            }
            $params['platform'] = 'ios';
            $params['environment'] = $environment;
        }

        $params['message_type'] = $message->getMessageType();
        $params['message'] = $message->getResult();
        $params['expire_time'] = $message->getExpireTime();
        $params['send_time'] = $message->getSendTime();

        if ($message->getStatTag()) {
            $params['stat_tag'] = $message->getStatTag();
        }
        return $this->callRestful(self::RESTAPI_PUSH, $params);
    }

    /**
     * 高级推送。推送消息给多个设备，需自行配置消息体。
     *
     * @param array             $tokenList
     * @param AndroidMsg|iOSMsg $message
     *
     * @return array|mixed
     */
    public function PushTokenList($tokenList, $message) {
        $ret = ['ret_code' => -1];

        if (!is_array($tokenList) || empty($tokenList)) {
            $ret['err_msg'] = 'Token list is not valid';
            return $ret;
        }

        if (!($message instanceof AndroidMsg) && !($message instanceof iOSMsg)) {
            $ret['err_msg'] = 'Message is not valid';
            return $ret;
        }

        if (!$message->isValid()) {
            $ret['err_msg'] = "Message params invalid";
            return $ret;
        }

        $params['audience_type'] = 'token_list';
        $params['token_list'] = $tokenList;

        if ($message instanceof AndroidMsg) {
            $params['platform'] = 'android'; // android：安卓；ios：苹果；all：安卓 & 苹果。仅支持全量推送和标签推送
            $params['multi_pkg'] = $message->getMultiPkg();
            $action = $this->parseWithIntent($message->getAction(), $message->getCustomData());
            $message->setAction($action);
        }

        if ($message instanceof iOSMsg) {
            $environment = $message->getEnvironment();
            if ($environment !== iOSMsg::IOSENV_DEV && $environment !== iOSMsg::IOSENV_PROD) {
                $ret['err_msg'] = 'iOS message environment invalid';
                return $ret;
            }
            $params['platform'] = 'ios';
            $params['environment'] = $environment;
        }

        $params['message_type'] = $message->getMessageType();
        $params['message'] = $message->getResult();
        $params['expire_time'] = $message->getExpireTime();
        $params['send_time'] = $message->getSendTime();

        if ($message->getStatTag()) {
            $params['stat_tag'] = $message->getStatTag();
        }
        return $this->callRestful(self::RESTAPI_PUSH, $params);
    }

    /**
     * 高级推送。推送消息给多个账户，需自行配置消息体。
     *
     * @param array             $accountList
     * @param AndroidMsg|iOSMsg $message
     *
     * @return array|mixed
     */
    public function PushAccountList($accountList, $message) {
        $ret = ['ret_code' => -1];

        if (!is_array($accountList) || empty($accountList)) {
            $ret['err_msg'] = 'Account list not valid';
            return $ret;
        }

        if (!($message instanceof AndroidMsg) && !($message instanceof iOSMsg)) {
            $ret['err_msg'] = 'Message is not valid';
            return $ret;
        }

        if (!$message->isValid()) {
            $ret['err_msg'] = "Message params invalid";
        }

        $params['audience_type'] = 'account_list';
        $params['account_list'] = $accountList;

        if ($message instanceof AndroidMsg) {
            $params['platform'] = 'android'; // android：安卓；ios：苹果；all：安卓 & 苹果。仅支持全量推送和标签推送
            $params['multi_pkg'] = $message->getMultiPkg();
            $action = $this->parseWithIntent($message->getAction(), $message->getCustomData());
            $message->setAction($action);
        }

        if ($message instanceof iOSMsg) {
            $environment = $message->getEnvironment();
            if ($environment !== iOSMsg::IOSENV_DEV && $environment !== iOSMsg::IOSENV_PROD) {
                $ret['err_msg'] = "ios message environment invalid";
                return $ret;
            }
            $params['platform'] = 'ios';
            $params['environment'] = $environment;
        }

        $params['message_type'] = $message->getMessageType();
        $params['message'] = $message->getResult();
        $params['expire_time'] = $message->getExpireTime();
        $params['send_time'] = $message->getSendTime();

        if ($message->getStatTag()) {
            $params['stat_tag'] = $message->getStatTag();
        }
        return $this->callRestful(self::RESTAPI_PUSH, $params);
    }

    /**
     * 高级推送。推送消息给所有设备，需自行配置消息体。
     *
     * @param string $message 推送内容
     *
     * @return array|mixed
     */
    public function PushAllDevices($message) {
        $ret = ['ret_code' => -1, 'err_msg' => 'Message is not valid'];

        if (!($message instanceof AndroidMsg) && !($message instanceof iOSMsg)) {
            return $ret;
        }

        if (!$message->isValid()) {
            $ret['err_msg'] = "Message params invalid";
        }

        $params['audience_type'] = 'all';

        if ($message instanceof AndroidMsg) {
            $params['platform'] = 'android'; // android：安卓；ios：苹果；all：安卓 & 苹果。仅支持全量推送和标签推送
            $params['multi_pkg'] = $message->getMultiPkg();
            $action = $this->parseWithIntent($message->getAction(), $message->getCustomData());
            $message->setAction($action);
        }

        if ($message instanceof iOSMsg) {
            $environment = $message->getEnvironment();
            if ($environment !== iOSMsg::IOSENV_DEV && $environment !== iOSMsg::IOSENV_PROD) {
                $ret['err_msg'] = "iOS message environment invalid";
                return $ret;
            }
            $params['platform'] = 'ios';
            $params['environment'] = $environment;
        }

        $params['message_type'] = $message->getMessageType();
        $params['message'] = $message->getResult();
        $params['expire_time'] = $message->getExpireTime();
        $params['send_time'] = $message->getSendTime();

        if ($message->getLoopTimes() && $message->getLoopInterval()) {
            $params['loop_times'] = $message->getLoopTimes();
            $params['loop_interval'] = $message->getLoopInterval();
        }
        if ($message->getStatTag()) {
            $params['stat_tag'] = $message->getStatTag();
        }
        return $this->callRestful(self::RESTAPI_PUSH, $params);
    }

    /**
     * 简易推送。使用默认设置推送消息给指定 Android 设备
     *
     * @param string $token 设备 Token
     * @param string $title 推送标题
     * @param string $content 推送内容
     * @param string $messageType 消息类型，默认通知：AndroidMsg::TYPE_NOTIFICATION；
     *                                     静默（透传）消息：AndroidMsg::TYPE_MESSAGE
     *
     * @return mixed
     */
    public function PushTokenAndroid($token, $title, $content, $messageType = AndroidMsg::TYPE_NOTIFICATION) {
        $msg = new AndroidMsg();
        $msg->setTitle($title);
        $msg->setContent($content);
        $msg->setMessageType($messageType);
        $ret = $this->PushSingleDevice($token, $msg);
        return $ret;
    }

    /**
     * 简易推送。使用默认设置推送消息给指定 iOS 设备
     *
     * @param string $token 设备 Token
     * @param string $title 推送标题
     * @param string $content 推送内容
     * @param string $environment 推送环境，开发：iOSMsg::IOSENV_DEV；正式：iOSMsg::IOSENV_PROD
     * @param string $messageType 消息类型，默认通知：iOSMsg::TYPE_APNS_NOTIFICATION；
     *                                     静默（后台）消息：iOSMsg::TYPE_REMOTE_MESSAGE
     *
     * @return mixed
     */
    public function PushTokenIos($token, $title, $content, $environment = iOSMsg::IOSENV_DEV
        , $messageType = iOSMsg::TYPE_APNS_NOTIFICATION) {
        $msg = new iOSMsg();
        $msg->setTitle($title);
        $msg->setContent($content);
        $msg->setEnvironment($environment);
        $msg->setMessageType($messageType);
        $ret = $this->PushSingleDevice($token, $msg);
        return $ret;
    }

    /**
     * 普通推送。推送消息给指定 Android 账户，可传输自定义数据、指定点击通知行为。
     *
     * @param string $account 推送目标账户
     * @param string $title 推送标题
     * @param string $content 推送内容
     * @param array  $customData 自定义数据，传入 key => value 形式的关联数组
     * @param array  $behaviour 点击通知行为
     *               默认打开 App，传入长度为 2 的数组。$array[0] 表示动作，$array[1] 表示动作相关数据，如：
     *                  打开指定 Activity：[ClickAction::TYPE_ACTIVITY, 'com.example.MyActivityClassName']
     *                  打开指定 URL：[ClickAction::TYPE_URL, 'http://example.com', 1]。第三个参数为是否提示访问 URL
     *                  打开 Intent：[ClickAction::TYPE_INTENT, 'xgscheme://com.xg.push/notify_detail']（自定义协议）
     * @param string $messageType 消息类型，默认通知：AndroidMsg::TYPE_NOTIFICATION；
     *                                     静默（透传）消息：AndroidMsg::TYPE_MESSAGE
     *
     * @return mixed
     */
    public function PushAccountAndroid($account, $title, $content, $customData = null,
                                       $behaviour = [ClickAction::TYPE_ACTIVITY, null],
                                       $messageType = AndroidMsg::TYPE_NOTIFICATION) {
        $msg = new AndroidMsg();
        $msg->setTitle($title);
        $msg->setContent($content);
        if (is_array($behaviour) && isset($behaviour[0])) {
            $action = new ClickAction();
            $action->setActionType($behaviour[0]);
            switch ($behaviour[0]) {
                case 1:
                    $action->setActivity($behaviour[1]);
                    break;
                case 2:
                    $action->setUrl($behaviour[1]);
                    $action->setConfirmOnUrl($behaviour[2] ? 1 : 0);
                    break;
                case 3:
                    $action->setIntent($behaviour[1]);
                    break;
            }
            $msg->setAction($action);
        }
        $msg->setCustomData($customData);
        $msg->setMessageType($messageType);
        $ret = $this->PushSingleAccount($account, $msg);
        return $ret;
    }

    /**
     * 普通推送。推送消息给指定 iOS 账户，支持传输自定义数据。
     *
     * @param string     $account 推送账户
     * @param string     $title 推送标题
     * @param string     $content 推送内容
     * @param array|null $customData 自定义数据，传入 key => value 形式的关联数组
     * @param string     $environment 推送环境，开发：iOSMsg::IOSENV_DEV；正式：iOSMsg::IOSENV_PROD
     * @param string     $messageType 消息类型，默认通知：iOSMsg::TYPE_APNS_NOTIFICATION；
     *                                     静默（后台）消息：iOSMsg::TYPE_REMOTE_MESSAGE
     *
     * @return mixed
     */
    public function PushAccountIos($account, $title, $content, $customData = null,
                                   $environment = iOSMsg::IOSENV_DEV, $messageType = iOSMsg::TYPE_APNS_NOTIFICATION) {
        $msg = new iOSMsg();
        $msg->setTitle($title);
        $msg->setContent($content);
        $msg->setEnvironment($environment);
        $msg->setCustomData($customData);
        $msg->setMessageType($messageType);
        $ret = $this->PushSingleAccount($account, $msg);
        return $ret;
    }

    /**
     * 普通推送。推送消息给所有 Android 设备，可传输自定义数据、指定点击通知行为。
     *
     * @param string $title 推送标题
     * @param string $content 推送内容
     * @param array  $customData 自定义数据，传入 key => value 形式的关联数组
     * @param array  $behaviour 点击通知行为
     *               默认打开 App，传入长度为 2 的数组。$array[0] 表示动作，$array[1] 表示动作相关数据，如：
     *                  打开指定 Activity：[ClickAction::TYPE_ACTIVITY, 'com.example.MyActivityClassName']
     *                  打开指定 URL：[ClickAction::TYPE_URL, 'http://example.com', 1]。第三个参数为是否提示访问 URL
     *                  打开 Intent：[ClickAction::TYPE_INTENT, 'xgscheme://com.xg.push/notify_detail']（自定义协议）
     * @param string $messageType 消息类型，默认通知：AndroidMsg::TYPE_NOTIFICATION；
     *                                     静默（透传）消息：AndroidMsg::TYPE_MESSAGE
     *
     * @return mixed
     */
    public function PushAllAndroid($title, $content, $customData = null, $behaviour = [ClickAction::TYPE_ACTIVITY, null],
                                   $messageType = AndroidMsg::TYPE_NOTIFICATION) {
        $msg = new AndroidMsg();
        $msg->setTitle($title);
        $msg->setContent($content);
        if (is_array($behaviour) && isset($behaviour[0])) {
            $action = new ClickAction();
            switch ($behaviour[0]) {
                case 1:
                    $action->setActivity($behaviour[1]);
                    break;
                case 2:
                    $action->setUrl($behaviour[1]);
                    $action->setConfirmOnUrl($behaviour[2] ? 1 : 0);
                    break;
                case 3:
                    $action->setIntent($behaviour[1]);
                    break;
                default:
            }
            $msg->setAction($action);
        }
        $msg->setCustomData($customData);
        $msg->setMessageType($messageType);
        $ret = $this->PushAllDevices($msg);
        return $ret;
    }

    /**
     * 普通推送。推送消息给所有 iOS 设备，支持传输自定义数据
     *
     * @param string $title 推送标题
     * @param string $content 推送内容
     * @param array  $customData 自定义数据，传入 key => value 形式的关联数组
     * @param string $environment 推送环境，开发：iOSMsg::IOSENV_DEV；正式：iOSMsg::IOSENV_PROD
     * @param string $messageType 消息类型，默认通知：iOSMsg::TYPE_APNS_NOTIFICATION；
     *                                     静默（后台）消息：iOSMsg::TYPE_REMOTE_MESSAGE
     *
     * @return mixed
     */
    public function PushAllIos($title, $content, $customData = null,
                               $environment = iOSMsg::IOSENV_DEV, $messageType = iOSMsg::TYPE_APNS_NOTIFICATION) {
        $msg = new iOSMsg();
        $msg->setTitle($title);
        $msg->setContent($content);
        $msg->setEnvironment($environment);
        $msg->setCustomData($customData);
        $msg->setMessageType($messageType);
        $ret = $this->PushAllDevices($msg);
        return $ret;
    }

    /**
     * 高级推送。推送消息给指定标签设备，需自行配置消息体。若 tagList 只有一项，则 tagsOp 应为 OR
     *
     * @param array             $tagList
     * @param string            $tagsOp
     * @param AndroidMsg|iOSMsg $message 消息体
     *
     * @return array|mixed
     */
    public function PushTags($tagList, $tagsOp, $message) {
        $ret = ['ret_code' => -1, 'err_msg' => 'Message is not valid'];

        if (!is_array($tagList) || empty($tagList)) {
            $ret['err_msg'] = 'Tag list is not valid';
            return $ret;
        }

        if (!is_string($tagsOp) || ($tagsOp !== 'AND' && $tagsOp !== 'OR')) {
            $ret['err_msg'] = 'tagsOp is not valid';
            return $ret;
        }

        if (!($message instanceof AndroidMsg) && !($message instanceof iOSMsg)) {
            return $ret;
        }

        if (!$message->isValid()) {
            $ret['err_msg'] = "Message params invalid";
        }

        $params['audience_type'] = 'tag';
        $params['tag_list'] = [
            'tags' => $tagList,
            'op' => $tagsOp,
        ];

        if ($message instanceof AndroidMsg) {
            $params['platform'] = 'android'; // android：安卓；ios：苹果；all：安卓 & 苹果。仅支持全量推送和标签推送
            $params['multi_pkg'] = $message->getMultiPkg();
            $action = $this->parseWithIntent($message->getAction(), $message->getCustomData());
            $message->setAction($action);
        }
        if ($message instanceof iOSMsg) {
            $environment = $message->getEnvironment();
            if ($environment !== iOSMsg::IOSENV_DEV && $environment !== iOSMsg::IOSENV_PROD) {
                $ret['err_msg'] = "iOS message environment invalid";
                return $ret;
            }
            $params['platform'] = 'ios';
            $params['environment'] = $environment;
        }

        $params['message_type'] = $message->getMessageType();
        $params['message'] = $message->getResult();
        $params['expire_time'] = $message->getExpireTime();
        $params['send_time'] = $message->getSendTime();

        if ($message->getLoopTimes() && $message->getLoopInterval()) {
            $params['loop_times'] = $message->getLoopTimes();
            $params['loop_interval'] = $message->getLoopInterval();
        }
        if ($message->getStatTag()) {
            $params['stat_tag'] = $message->getStatTag();
        }
        return $this->callRestful(self::RESTAPI_PUSH, $params);
    }

    /**
     * 简易推送。使用默认设置推送消息给指定标签的 Android 设备
     *
     * @param string $tags
     * @param string $title 消息标题
     * @param string $content 消息内容
     * @param string $messageType 消息类型，默认通知：AndroidMsg::TYPE_NOTIFICATION；
     *                                     静默（透传）消息：AndroidMsg::TYPE_MESSAGE
     *
     * @return mixed
     */
    public function PushTagsAndroid($tags, $title, $content, $messageType = AndroidMsg::TYPE_NOTIFICATION) {
        $msg = new AndroidMsg();
        $msg->setTitle($title);
        $msg->setContent($content);
        $msg->setMessageType($messageType);
        $ret = $this->PushTags([$tags], 'OR', $msg);
        return $ret;
    }

    /**
     * 简易推送。使用默认设置推送消息给指定标签的 iOS 设备
     *
     * @param string $tags
     * @param string $title 消息标题
     * @param string $content 消息内容
     * @param string $environment 推送环境，开发：iOSMsg::IOSENV_DEV；正式：iOSMsg::IOSENV_PROD
     * @param string $messageType 消息类型，默认通知：iOSMsg::TYPE_APNS_NOTIFICATION；
     *                                     静默（后台）消息：iOSMsg::TYPE_REMOTE_MESSAGE
     *
     * @return mixed
     */
    public function PushTagsIos($tags, $title, $content, $environment = iOSMsg::IOSENV_DEV,
                                $messageType = iOSMsg::TYPE_APNS_NOTIFICATION) {
        $msg = new iOSMsg();
        $msg->setTitle($title);
        $msg->setContent($content);
        $msg->setEnvironment($environment);
        $msg->setMessageType($messageType);
        $ret = $this->PushTags([$tags], 'OR', $msg);
        return $ret;
    }

    /**
     * 查询设备消息推送状态
     *
     * @param array  $tokenList 设备 Token 列表
     * @param string $pushId 推送 ID
     *
     * @return array|mixed
     */
    public function QueryDeviceStatus($tokenList, $pushId) {
        $ret = ['ret_code' => -1];
        if (!is_array($tokenList) || empty($tokenList)) {
            $ret['err_msg'] = 'TokenList is not valid';
            return $ret;
        }
        if (!is_string($pushId)) {
            $ret['err_msg'] = 'PushId is not valid';
            return $ret;
        }
        $params['push_id'] = $pushId;
        $params['token_list'] = $tokenList;

        return $this->callRestful(self::RESTAPI_DEVICE_STATUS, $params);
    }

    /**
     * @param ClickAction $action
     * @param array       $customContent
     *
     * @return ClickAction
     */
    private function parseWithIntent($action, array $customContent = []) {
        if ($action->getActionType() === ClickAction::TYPE_INTENT) {
            $intent = $action->getIntent();
            $intent .= (strpos($intent, '?') === false ? '?' : '&') . http_build_query($customContent);
            $action->setIntent($intent);
        }
        return $action;
    }

    /**
     * Json 转数组
     *
     * @param string $json
     *
     * @return mixed
     */
    private function json2Array($json) {
        return function_exists('jsond_decode') ?
            jsond_decode(stripslashes($json), true) :
            json_decode(stripslashes($json), true);
    }

    private function callRestful($url, $params) {
        $extra_curl_conf = [
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD => "{$this->appId}:{$this->secretKey}"
        ];
        $ret = RequestBase::exec(
            $url,
            $params,
            RequestBase::METHOD_POST,
            $extra_curl_conf
        );
        return $this->json2Array($ret) ?: $ret;
    }
}
