<?php

/**
 * Created by IntelliJ IDEA.
 * User: maxsky
 * Date: 2019-04-11
 * Time: 14:18
 */

namespace Tencent\XinGe\Message;

use Tencent\XinGe\Component\ClickAction;
use Tencent\XinGe\Component\Style;
use Tencent\XinGe\Component\TimeInterval;

class AndroidMsg {

    const TYPE_NOTIFICATION = 'notify';
    const TYPE_MESSAGE = 'message';
    const MAX_LOOP_TASK_DAYS = 14;

    /*************** 消息参数 **************/
    /** @var string 消息体类型，默认为 notify */
    private $m_msgType;
    /** @var Style Android 消息风格。震动、铃声、呼吸灯等 */
    private $m_style;
    /** @var string 原生消息内容 */
    private $m_raw;
    /** @var string 消息标题 */
    private $m_title;
    /** @var string 消息内容 */
    private $m_content;
    /** @var array 推送时间段，小于 10 个 */
    private $m_acceptTimes;
    /** @var string 富媒体地址，目前仅 1 个，Android SDK >= 4.2.0 */
    private $m_mediaResources;
    private $m_action;
    /** @var array 自定义数据 */
    private $m_customContent;

    /*************** 信鸽可选参数 **************/
    /** @var int 消息离线存储时间（秒），默认 259200（72 小时） */
    private $m_expireTime = 259200;
    /** @var string 推送时间，格式 YYYY-mm-dd HH:ii:ss */
    private $m_sendTime;
    /** @var bool 多包名推送 */
    private $m_multiPkg;
    /** @var int 循环重复次数，仅支持全量推送、标签推送。建议取值 1-15 */
    private $m_loopTimes;
    /** @var int 循环消息下发间隔时间（天）。建议取值 1-14 */
    private $m_loopInterval;
    /** @var string 统计标签，用于聚合统计 */
    private $m_statTag;

    public function __construct() {
        $this->m_msgType = self::TYPE_NOTIFICATION;
        $this->m_acceptTimes = [];
        $this->m_multiPkg = false;
        $this->m_style = new Style();
        $this->m_action = new ClickAction();
    }

    public function setTitle($title) {
        $this->m_title = $title;
    }

    public function setContent($content) {
        $this->m_content = $content;
    }

    public function setStyle($style) {
        $this->m_style = $style;
    }

    public function setAction($action) {
        $this->m_action = $action;
    }

    public function setCustom($customContent) {
        $this->m_customContent = $customContent;
    }

    public function setRaw($raw) {
        $this->m_raw = $raw;
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

    public function getMediaResources() {
        return $this->m_mediaResources;
    }

    public function setMediaResources($mediaResources) {
        $this->m_mediaResources = $mediaResources;
    }

    public function setMessageType($type) {
        $this->m_msgType = $type;
    }

    public function getMessageType() {
        return $this->m_msgType;
    }

    public function setMultiPkg($multiPkg) {
        $this->m_multiPkg = $multiPkg;
    }

    public function getMultiPkg() {
        return $this->m_multiPkg;
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
        switch ($this->m_msgType) {
            case self::TYPE_NOTIFICATION:
                $ret['title'] = $this->m_title;
                $ret['content'] = $this->m_content;
                $ret['xg_media_resources'] = $this->m_mediaResources;
                $android = $this->m_style->getResult();
                $android['action'] = $this->m_action->getResult();
                $ret['android'] = $android;
                break;
            case self::TYPE_MESSAGE:
                $ret['title'] = $this->m_title;
                $ret['content'] = $this->m_content;
        }
        if ($this->m_customContent) {
            $ret['android']['custom_content'] = $this->m_customContent;
        }
        $ret['accept_time'] = $this->acceptTimeToArray();
        return $ret;
    }

    public function isValid() {
        if (is_string($this->m_raw) && !empty($this->raw)) {
            return true;
        }

        if (is_null($this->m_title)) {
            $this->m_title = '';
        } elseif (!is_string($this->m_title)) {
            return false;
        }

        if (!is_string($this->m_content) || empty($this->m_content)) {
            return false;
        }

        if (!is_bool($this->m_multiPkg)) {
            if (!is_int($this->m_multiPkg) || $this->m_multiPkg < 0 || $this->m_multiPkg > 1) {
                return false;
            }
        }

        if ($this->m_msgType === self::TYPE_NOTIFICATION) {
            if (!($this->m_style instanceof Style) || !($this->m_action instanceof ClickAction)) {
                return false;
            }

            if (!$this->m_style->isValid() || !$this->m_action->isValid()) {
                return false;
            }

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

        if (!empty($this->m_customContent) && !is_array($this->m_customContent)) {
            return false;
        }

        if ($this->m_loopInterval && $this->m_loopTimes) {
            if (($this->m_loopTimes - 1) * $this->m_loopInterval + 1 > self::MAX_LOOP_TASK_DAYS) {
                return false;
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
        }
        return true;
    }

    public function __destruct() {
    }

}
