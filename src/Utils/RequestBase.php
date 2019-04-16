<?php

/**
 * Created by IntelliJ IDEA.
 * User: maxsky
 * Date: 2019-04-11
 * Time: 15:13
 */

namespace Tencent\XinGe\Utils;

use Exception;

class RequestBase {

    const METHOD_GET = 'get';
    const METHOD_POST = 'post';

    /**
     * 发起一个 get 或 post 请求
     *
     * @param string $url 请求的 url
     * @param array  $params 请求参数
     * @param string $method 请求方式
     * @param array  $extra_conf curl 配置, 高级需求可以用, 如
     *                                                  $extra_conf = [
     *                                                      CURLOPT_HEADER => true,
     *                                                      CURLOPT_RETURNTRANSFER = false
     *                                                  ]
     *
     * @return bool|mixed 成功返回数据，失败返回 false
     */
    public static function exec($url, $params = [], $method = self::METHOD_GET, $extra_conf = []) {
        // get 请求直接将参数附在 url 后面
        if ($method == self::METHOD_GET) {
            $params = is_array($params) ? http_build_query($params) : $params;
            $url .= (strpos($url, '?') === false ? '?' : '&') . $params;
        }

        // 默认配置
        $curl_conf = [
            CURLOPT_URL => $url,             // 请求 url
            CURLOPT_HEADER => false,         // 不输出头信息
            CURLOPT_RETURNTRANSFER => true,  // 不输出返回数据
            CURLOPT_CONNECTTIMEOUT => 10,    // 连接超时时间
            CURLOPT_SSL_VERIFYPEER => false, // 不验证对等证书
            CURLOPT_SSL_VERIFYHOST => 0,     // 不验证域名
        ];

        // post 请求额外需要的配置项
        if ($method == self::METHOD_POST) {
            if (is_array($params)) {
                if (function_exists('jsond_encode')) {
                    $params = jsond_encode($params);
                } else {
                    $params = json_encode($params);
                }
            }
            // 使用 post
            $curl_conf[CURLOPT_POST] = true;
            // post 参数
            $curl_conf[CURLOPT_POSTFIELDS] = $params;
        }
        // 请求数据类型和内容大小
        $curl_conf[CURLOPT_HTTPHEADER] = [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($params)
        ];

        // 添加额外的配置（如果有）
        foreach ($extra_conf as $k => $v) {
            if ($k === CURLOPT_HTTPHEADER) {
                $curl_conf[$k][] = $v;
            } else {
                $curl_conf[$k] = $v;
            }
        }
        // 初始化 curl 句柄
        $curl_handle = curl_init();
        // 设置 curl 配置项
        curl_setopt_array($curl_handle, $curl_conf);
        // 发起请求
        $data = curl_exec($curl_handle);
        if ($data === false) {
            try {
                throw new Exception('CURL ERROR: ' . curl_error($curl_handle));
            } catch (Exception $e) {
                $data = $e->getMessage();
            }
        }
        // 关闭 curl
        curl_close($curl_handle);
        return $data;
    }

}
