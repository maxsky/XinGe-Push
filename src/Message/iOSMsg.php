<?php

/**
 * Created by IntelliJ IDEA.
 * User: maxsky
 * Date: 2019-04-11
 * Time: 14:19
 */

namespace Tencent\XinGe\Message;

use Tencent\XinGe\Component\TimeInterval;

class iOSMsg {

    const TYPE_APNS_NOTIFICATION = 'notify';
    const TYPE_REMOTE_MESSAGE = 'message';

    const IOSENV_PROD = 'product';
    const IOSENV_DEV = 'dev';

    const MAX_LOOP_TASK_DAYS = 14;

    /*************** 消息参数 **************/
    /** @var string 消息类型 */
    private $m_msgType;
    /** @var string 原生消息内容 */
    private $m_raw;
    /** @var array 自定义数据 */
    private $m_custom;
    /** @var string 消息标题 */
    private $m_title;
    /** @var string 消息内容 */
    private $m_content;
    /** @var array 标题与内容 */
    private $m_alert;
    /** @var string|array */
//    private $m_mediaResources;
    /** @var array 推送时间段，小于 10 个 */
    private $m_acceptTimes;

    /*************** 信鸽可选参数 **************/
    /** @var int 消息离线存储时间（秒），默认 259200（72 小时） */
    private $m_expireTime = 259200;
    /** @var string 推送时间，格式 YYYY-mm-dd HH:ii:ss */
    private $m_sendTime;
    /** @var int 角标类型
     *              -1：角标数字不变
     *              -2：角标数字自加 1
     *              >=0：「自定义」角标数字
     */
    private $m_badgeType;
    /** @var string 自定义通知音 */
    private $m_sound;
    /** @var string 自定义操作 */
    private $m_category;
    /** @var string 推送环境 */
    private $m_environment;
    /** @var int 循环重复次数，仅支持全量推送、标签推送。建议取值 1-15 */
    private $m_loopTimes;
    /** @var int 循环消息下发间隔时间（天）。建议取值 1-14 */
    private $m_loopInterval;
    /** @var string 统计标签，用于聚合统计 */
    private $m_statTag;

    public function __construct() {
        $this->m_msgType = self::TYPE_APNS_NOTIFICATION;
        $this->m_acceptTimes = [];
        $this->m_badgeType = -2;
        $this->m_environment = self::IOSENV_DEV;
    }

    public function setTitle($title) {
        $this->m_title = $title;
        $this->m_alert['title'] = $title;
    }

    public function setContent($content) {
        $this->m_content = $content;
        $this->m_alert['body'] = $content;
    }

    public function setExpireTime($expireTime) {
        $this->m_expireTime = $expireTime;
    }

    public function getExpireTime() {
        return $this->m_expireTime;
    }

    public function setSendTime($sendTime) {
        $this->m_sendTime = $sendTime;
    }

    public function getSendTime() {
        return $this->m_sendTime;
    }

    public function addAcceptTime($acceptTime) {
        $this->m_acceptTimes[] = $acceptTime;
    }

    public function acceptTimeToArray() {
        $ret = [];
        /** @var TimeInterval $acceptTime */
        foreach ($this->m_acceptTimes as $acceptTime) {
            $ret[] = $acceptTime->toArray();
        }
        return $ret;
    }

    public function setCustom($custom) {
        $this->m_custom = $custom;
    }

    public function setRaw($raw) {
        $this->m_raw = $raw;
    }

    /**
     * @param array|string $mediaResources
     */
//    public function setMediaResources($mediaResources) {
//        $this->m_mediaResources = $mediaResources;
//    }

    /**
     * 设置通知标题、内容。格式 ['title' => '标题', 'body' => '内容']
     *
     * @param array $alert
     */
    public function setAlert(array $alert) {
        $this->m_alert = $alert;
    }

    public function setBadgeType($badgeType) {
        $this->m_badgeType = $badgeType;
    }

    public function setSound($sound) {
        $this->m_sound = $sound;
    }

    public function setMessageType($type) {
        $this->m_msgType = $type;
    }

    public function getMessageType() {
        return $this->m_msgType;
    }

    public function getEnvironment() {
        return $this->m_environment;
    }

    public function setEnvironment($environment) {
        $this->m_environment = $environment;
    }

    public function setCategory($category) {
        $this->m_category = $category;
    }

    public function getLoopInterval() {
        return $this->m_loopInterval;
    }

    public function setLoopInterval($loopInterval) {
        $this->m_loopInterval = $loopInterval;
    }

    public function getLoopTimes() {
        return $this->m_loopTimes;
    }

    public function setLoopTimes($loopTimes) {
        $this->m_loopTimes = $loopTimes;
    }

    public function getStatTag() {
        return $this->m_statTag;
    }

    public function setStatTag($statTag) {
        $this->m_statTag = $statTag;
    }

    public function getResult() {
        if ($this->m_raw) {
            return $this->m_raw;
        }

        $aps = [];

        switch ($this->m_msgType) {
            case self::TYPE_APNS_NOTIFICATION:
                $aps['alert'] = $this->m_alert;
                $aps['badge_type'] = $this->m_badgeType;
                if ($this->m_sound) {
                    $aps['sound'] = $this->m_sound;
                }
                if ($this->m_category) {
                    $aps['category'] = $this->m_category;
                }
                break;
            case self::TYPE_REMOTE_MESSAGE:
                $aps['content-available'] = 1;
        }
        $ret['ios']['aps'] = $aps;
//        $ret['ios']['xg_media_resources'] = $this->m_mediaResources;
        if ($this->m_custom) {
            $ret['ios'] += $this->m_custom;
        }
        $ret['accept_time'] = $this->acceptTimeToArray();
        return $ret;
    }

    public function isValid() {
        if (is_string($this->m_raw) && !empty($this->raw)) {
            return true;
        }

        if ($this->m_expireTime) {
            if (!is_int($this->m_expireTime) || $this->m_expireTime > 259200) { // 3 * 24 * 60 * 60
                return false;
            }
        } else {
            $this->m_expireTime = 0;
        }

        if ($this->m_sendTime) {
            if (strtotime($this->m_sendTime) === false) {
                return false;
            }
        } else {
            $this->m_sendTime = '2018-07-19 00:00:00'; // 默认日期定义为该 SDK 发布日期
        }

        foreach ($this->m_acceptTimes as $value) {
            if (!($value instanceof TimeInterval) || !$value->isValid()) {
                return false;
            }
        }

        if (!empty($this->m_custom) && !is_array($this->m_custom)) {
            return false;
        }

        if ($this->m_msgType == self::TYPE_APNS_NOTIFICATION) {
            if (empty($this->m_alert) || !is_array($this->m_alert)) {
                return false;
            }
        }

        if (!is_int($this->m_badgeType)) {
            return false;
        }

        if ($this->m_sound && !is_string($this->m_sound)) {
            return false;
        }

        if ($this->m_loopInterval && $this->m_loopTimes) {
            if (($this->m_loopTimes - 1) * $this->m_loopInterval + 1 > self::MAX_LOOP_TASK_DAYS) {
                return false;
            }
        }

        if ($this->m_loopInterval) {
            if (!(is_int($this->m_loopInterval) && $this->m_loopInterval > 0)) {
                return false;
            }
        }

        if ($this->m_loopTimes) {
            if (!(is_int($this->m_loopTimes) && $this->m_loopTimes > 0)) {
                return false;
            }
        }

        return true;
    }

    public function __destruct() {
    }

}