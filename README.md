# xinge-api-php

<a href="https://996.icu"><img src="https://img.shields.io/badge/link-996.icu-red.svg"></a>

Composer 安装：`composer require maxsky/xg-push`
## 概述
[信鸽](http://xg.qq.com) 是腾讯云提供的一款支持**百亿级**消息的移动 App 推送平台，开发者可以调用 PHP SDK 访问信鸽推送服务。

## 对应官方版本
v1.2.0 [2018-07-19]

## 引用 SDK

请到[信鸽官网](http://xg.qq.com/xg/ctr_index/download)下载最新版本的包，使用时引用 XingeApp 包即可。

## 接口说明

信鸽提供的主要推送和查询接口包括 3 种

### 创建推送任务

- pushSingleDevice 推送消息给单个设备
- pushSingleAccount 推送消息给单个账号
- pushAccountList 推送消息给多个账号
- pushAllDevice 推送消息给单个 app 的所有设备
- pushTags 推送消息给 tags 指定的设备

### 异步查询推送状态

- queryPushStatus 查询群发消息发送状态
- cancelTimingPush 取消尚未推送的定时消息

### 查询/更改账户和标签

- queryDeviceCount 查询应用覆盖的设备数
- queryTags 查询应用的 tags
- BatchSetTag 批量为 token 设置标签
- BatchDelTag 批量为 token 删除标签
- queryTokenTags 查询 token 的 tags
- queryTagTokenNum 查询 tag 下 token 的数目
- queryInfoOfToken 查询 token 的相关信息
- queryTokensOfAccount 查询 account 绑定的 token
- deleteTokenOfAccount 删除 account 绑定的 token
- deleteAllTokensOfAccount 删除 account 绑定的所有 token
