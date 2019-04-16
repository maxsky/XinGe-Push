<?php

/**
 * Created by IntelliJ IDEA.
 * User: maxsky
 * Date: 2019-04-11
 * Time: 14:19
 */

namespace Tencent\XinGe\Component;

class ClickAction {

    const TYPE_ACTIVITY = 1;
    const TYPE_URL = 2;
    const TYPE_INTENT = 3;

    /**
     * 点击动作类型
     *
     * @var int
     *         1 - 打开 Activity 或 App 本身
     *         2 - 打开 url
     *         3 - 打开 Intent
     */
    private $m_actionType;
    /** @var string 链接 */
    private $m_url;
    /** @var int 打开链接是否需要用户确认。默认 1（需要） */
    private $m_confirmOnUrl;
    /** @var string Activity 属性 */
    private $m_activity;
    /** @var string 客户端 Schema 属性，SDK >= 3.2.3 */
    private $m_intent;
    /** @var int Intent Flag 属性 */
    private $m_atyAttrIntentFlag;
    /** @var int Pending Intent Flag 属性 */
    private $m_atyAttrPendingIntentFlag;

    public function __construct() {
        $this->m_actionType = self::TYPE_ACTIVITY;
        $this->m_atyAttrIntentFlag = 0;
        $this->m_atyAttrPendingIntentFlag = 0;
        $this->m_confirmOnUrl = 1;
    }

    public function setActionType($actionType) {
        $this->m_actionType = $actionType;
    }

    public function setUrl($url) {
        $this->m_url = $url;
    }

    public function setConfirmOnUrl($confirmOnUrl) {
        $this->m_confirmOnUrl = $confirmOnUrl;
    }

    public function setActivity($activity) {
        $this->m_activity = $activity;
    }

    public function setIntent($intent) {
        $this->m_intent = $intent;
    }

    public function setAtyAttrIntentFlag($atyAttrIntentFlag) {
        $this->m_atyAttrIntentFlag = $atyAttrIntentFlag;
    }

    public function setAtyAttrPendingIntentFlag($atyAttrPendingIntentFlag) {
        $this->m_atyAttrPendingIntentFlag = $atyAttrPendingIntentFlag;
    }

    public function getResult() {
        $ret = [];
        $ret['action_type'] = $this->m_actionType;
        switch ($this->m_actionType) {
            case self::TYPE_ACTIVITY:
                $ret['activity'] = $this->m_activity;

                $aty_attr['if'] = $this->m_atyAttrIntentFlag;
                $aty_attr['pf'] = $this->m_atyAttrPendingIntentFlag;

                $ret['aty_attr'] = $aty_attr;
                break;

            case self::TYPE_URL:
                $ret['browser'] = [
                    'url' => $this->m_url,
                    'confirm' => $this->m_confirmOnUrl
                ];
                break;
            case self::TYPE_INTENT:
                $ret['intent'] = $this->m_intent;
        }
        return $ret;
    }

    public function isValid() {
        if (!is_int($this->m_actionType)) {
            return false;
        } elseif (!in_array($this->m_actionType, [self::TYPE_ACTIVITY, self::TYPE_URL, self::TYPE_INTENT])) {
            $this->m_actionType = self::TYPE_ACTIVITY;
        }
        switch ($this->m_actionType) {
            case self::TYPE_ACTIVITY:
                if (!$this->m_activity) {
                    $this->m_activity = '';
                    return true;
                }

                if ($this->m_atyAttrIntentFlag && !is_int($this->m_atyAttrIntentFlag)) {
                    return false;
                }

                if ($this->m_atyAttrPendingIntentFlag && !is_int($this->m_atyAttrPendingIntentFlag)) {
                    return false;
                }
                break;
            case self::TYPE_URL:
                if (is_string($this->m_url) && !empty($this->m_url) && is_int($this->m_confirmOnUrl) &&
                    ($this->m_confirmOnUrl === 1 || $this->m_confirmOnUrl === 0)
                ) {
                    return true;
                }
                return false;
            case self::TYPE_INTENT:
                if (is_string($this->m_intent) && !empty($this->m_intent)) {
                    return true;
                }
                return false;
        }
        return true;

    }

}
