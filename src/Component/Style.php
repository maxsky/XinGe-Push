<?php

/**
 * Created by IntelliJ IDEA.
 * User: maxsky
 * Date: 2019-04-11
 * Time: 15:01
 */

namespace Tencent\XinGe\Component;

class Style {

    /** @var int 消息对象唯一标识（仅信鸽）
     *  大于 0：会覆盖先前相同 id 的消息
     *  等于 0：展示本条通知且不影响其他消息
     *  等于 -1：将清除先前所有消息，仅展示本条消息
     */
    private $m_nId;
    /** @var int 本地通知样式标识 */
    private $m_builderId;
    /** @var int 是否响铃。1：是；0：否 */
    private $m_ring;
    /** @var string 指定 Android 工程里 raw 目录中的铃声文件名，无需后缀名 */
    private $m_ringRaw;
    /** @var int 是否震动 */
    private $m_vibrate;
    /** @var int 是否使用呼吸灯 */
    private $m_lights;
    /** @var int 通知栏是否可清除 */
    private $m_clearable;
    /** @var int 通知栏图标使用应用图标或自定义图标。0：应用本身；1：自定义 */
    private $m_iconType;
    /** @var string 应用内图标文件名或图标 url 地址。icon_type 为 1 时必须 */
    private $m_iconRes;
    /** @var int 是否覆盖通知样式编号 */
    private $m_styleId;
    /** @var string 状态栏消息图标，默认显示应用图标 */
    private $m_smallIcon;

    public function __construct($builderId = 0, $ring = 1, $vibrate = 1, $clearable = 1, $nId = 0, $lights = 1,
                                $iconType = 0, $styleId = 1) {
        $this->m_builderId = $builderId;
        $this->m_ring = $ring;
        $this->m_vibrate = $vibrate;
        $this->m_clearable = $clearable;
        $this->m_nId = $nId;
        $this->m_lights = $lights;
        $this->m_iconType = $iconType;
        $this->m_styleId = $styleId;
    }

    public function setRingRaw($ringRaw) {
        return $this->m_ringRaw = $ringRaw;
    }

    public function setIconType($iconType) {
        $this->m_iconType = $iconType;
    }

    public function setIconRes($iconRes) {
        return $this->m_iconRes = $iconRes;
    }

    public function setSmallIcon($smallIcon) {
        return $this->m_smallIcon = $smallIcon;
    }

    public function getResult() {
        $ret['n_id'] = $this->m_nId;
        $ret['builder_id'] = $this->m_builderId;
        $ret['ring'] = $this->m_ring;
        $ret['ring_raw'] = $this->m_ringRaw;
        $ret['vibrate'] = $this->m_vibrate;
        $ret['lights'] = $this->m_lights;
        $ret['clearable'] = $this->m_clearable;
        $ret['icon_type'] = $this->m_iconType;
        if ($this->m_iconType === 1) {
            $ret['icon_res'] = $this->m_iconRes;
        }
        $ret['style_id'] = $this->m_styleId;
        $ret['small_icon'] = $this->m_smallIcon;
        return $ret;
    }

    public function isValid() {
        if (!is_int($this->m_builderId) || !is_int($this->m_ring) ||
            !is_int($this->m_vibrate) || !is_int($this->m_clearable) ||
            !is_int($this->m_lights) || !is_int($this->m_iconType) ||
            !is_int($this->m_styleId)
        ) {
            return false;
        }

        if ($this->m_ring < 0 || $this->m_ring > 1) {
            return false;
        }

        if ($this->m_vibrate < 0 || $this->m_vibrate > 1) {
            return false;
        }

        if ($this->m_clearable < 0 || $this->m_clearable > 1) {
            return false;
        }

        if ($this->m_lights < 0 || $this->m_lights > 1) {
            return false;
        }

        if ($this->m_iconType < 0 || $this->m_iconType > 1) {
            return false;
        } elseif ($this->m_iconType === 1 && !$this->m_iconRes) {
            return false;
        }

        if ($this->m_styleId < 0 || $this->m_styleId > 1) {
            return false;
        }

        return true;
    }

    public function __destruct() {
    }

}
