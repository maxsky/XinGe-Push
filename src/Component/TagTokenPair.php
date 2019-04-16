<?php

/**
 * Created by IntelliJ IDEA.
 * User: maxsky
 * Date: 2019-04-11
 * Time: 14:18
 */

namespace Tencent\XinGe\Component;

class TagTokenPair {

    public $tag;
    public $token;

    public function __construct($tag, $token) {
        $this->tag = strval($tag);
        $this->token = strval($token);
    }

    public function __destruct() {
    }

}
