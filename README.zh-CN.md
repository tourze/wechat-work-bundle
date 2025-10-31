# wechat-work-bundle

[English](README.md) | [中文](README.zh-CN.md)

[![PHP 版本](https://img.shields.io/packagist/php-v/tourze/wechat-work-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-work-bundle)
[![最新版本](https://img.shields.io/packagist/v/tourze/wechat-work-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-work-bundle)  
[![许可证](https://img.shields.io/packagist/l/tourze/wechat-work-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-work-bundle)
[![总下载量](https://img.shields.io/packagist/dt/tourze/wechat-work-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-work-bundle)  
[![构建状态](https://img.shields.io/github/actions/workflow/status/tourze/wechat-work-bundle/ci.yml?style=flat-square)](https://github.com/tourze/wechat-work-bundle/actions)  
[![覆盖率](https://img.shields.io/codecov/c/github/tourze/wechat-work-bundle?style=flat-square)](https://codecov.io/gh/tourze/wechat-work-bundle)

企业微信集成能力模块，为 Symfony 应用程序提供企业微信 API 集成功能。

## 目录

- [依赖关系](#依赖关系)
- [安装](#安装)
- [特性](#特性)
- [配置](#配置)
  - [基础配置](#基础配置)
  - [数据库配置](#数据库配置)
- [快速开始](#快速开始)
  - [1. 配置企业信息](#1-配置企业信息)
  - [2. 使用服务](#2-使用服务)
  - [3. 自动刷新访问令牌](#3-自动刷新访问令牌)
- [控制台命令](#控制台命令)
  - [刷新访问令牌](#刷新访问令牌)
  - [同步应用信息](#同步应用信息)
- [高级用法](#高级用法)
  - [自定义请求实现](#自定义请求实现)
  - [服务扩展](#服务扩展)
- [实体说明](#实体说明)
  - [Corp（企业）](#corp企业)
  - [Agent（应用）](#agent应用)
- [安全性](#安全性)
  - [访问令牌管理](#访问令牌管理)
  - [敏感数据](#敏感数据)
- [参考文档](#参考文档)
- [许可证](#许可证)

## 依赖关系

此模块需要：

- PHP 8.1 或更高版本
- Symfony 7.3 或更高版本
- Doctrine ORM 3.0 或更高版本
- `tourze/wechat-work-contracts` 用于接口定义
- `tourze/doctrine-timestamp-bundle` 用于时间戳处理
- `tourze/doctrine-track-bundle` 用于跟踪变更
- `tourze/doctrine-user-bundle` 用于用户相关功能
- `tourze/doctrine-ip-bundle` 用于 IP 跟踪
- `tourze/doctrine-resolve-target-entity-bundle` 用于实体解析
- `tourze/http-client-bundle` 用于 HTTP 客户端功能
- `tourze/symfony-cron-job-bundle` 用于定时任务
- `tourze/enum-extra` 用于枚举工具
- `nesbot/carbon` 用于日期时间处理
- `yiisoft/json` 用于 JSON 处理

## 安装

```bash
composer require tourze/wechat-work-bundle
```

## 特性

- 企业微信应用管理
- 自动刷新 Access Token
- 应用信息同步
- 企业信息管理
- 完整的 Doctrine ORM 支持
- 定时任务支持

## 配置

### 基础配置

在 `config/bundles.php` 中启用模块：

```php
<?php

return [
    // 其他模块...
    WechatWorkBundle\WechatWorkBundle::class => ['all' => true],
];
```

### 数据库配置

运行迁移创建必要的数据表：

```bash
php bin/console doctrine:migrations:migrate
```

## 快速开始

### 1. 配置企业信息

首先创建一个 Corp 实体和至少一个 Agent：

```php
use WechatWorkBundle\Entity\Corp;
use WechatWorkBundle\Entity\Agent;

// 创建企业
$corp = new Corp();
$corp->setName('我的公司');
$corp->setCorpId('your_corp_id');

// 创建应用
$agent = new Agent();
$agent->setName('我的应用');
$agent->setAgentId('your_agent_id');
$agent->setSecret('your_agent_secret');
$agent->setCorp($corp);

$entityManager->persist($corp);
$entityManager->persist($agent);
$entityManager->flush();
```

### 2. 使用服务

```php
use WechatWorkBundle\Service\WorkService;

class MyService
{
    public function __construct(
        private WorkService $workService
    ) {}

    public function sendMessage(): void
    {
        // 服务会自动处理访问令牌刷新
        $this->workService->refreshAgentAccessToken($agent);
        
        // 使用服务进行 API 调用
        // 具体实现取决于您的需求
    }
}
```

### 3. 自动刷新访问令牌

模块会自动处理访问令牌刷新。您也可以手动刷新令牌：

```bash
php bin/console wechat-work:refresh-agent-access-token
```

## 控制台命令

### 刷新访问令牌

为所有应用刷新访问令牌：

```bash
php bin/console wechat-work:refresh-agent-access-token
```

### 同步应用信息

从企业微信 API 同步应用信息：

```bash
php bin/console wechat-work:sync-agent-info
```

## 高级用法

### 自定义请求实现

您可以扩展 WorkService 来实现自定义 API 请求：

```php
use WechatWorkBundle\Service\WorkService;
use WechatWorkBundle\Entity\Agent;

class CustomWorkService extends WorkService
{
    public function sendTextMessage(Agent $agent, string $content): array
    {
        $this->refreshAgentAccessToken($agent);
        
        $data = [
            'touser' => '@all',
            'msgtype' => 'text',
            'agentid' => $agent->getAgentId(),
            'text' => [
                'content' => $content
            ]
        ];
        
        return $this->request([
            'url' => $this->getBaseUrl() . '/cgi-bin/message/send',
            'method' => 'POST',
            'query' => ['access_token' => $agent->getAccessToken()],
            'json' => $data
        ]);
    }
}
```

### 服务扩展

创建利用企业微信 API 的自定义服务：

```php
use WechatWorkBundle\Service\WorkService;
use WechatWorkBundle\Repository\AgentRepository;

class NotificationService
{
    public function __construct(
        private WorkService $workService,
        private AgentRepository $agentRepository
    ) {}

    public function sendNotification(string $message): void
    {
        $agents = $this->agentRepository->findBy(['active' => true]);
        
        foreach ($agents as $agent) {
            // 通过每个活跃应用发送通知
            $this->workService->refreshAgentAccessToken($agent);
            // 在此实现您的通知逻辑
        }
    }
}
```

## 实体说明

### Corp（企业）

代表一个企业微信企业：

- `name`: 企业名称
- `corpId`: 企业唯一标识符
- `agents`: 关联应用的集合

### Agent（应用）

代表企业内的一个企业微信应用：

- `name`: 应用名称
- `agentId`: 应用标识符
- `secret`: 用于 API 访问的应用密钥
- `accessToken`: 当前访问令牌（自动管理）
- `accessTokenExpireTime`: 令牌过期时间
- `corp`: 关联的企业

## 安全性

### 访问令牌管理

访问令牌由模块自动管理：

- 令牌在过期前自动刷新
- 刷新失败的尝试会被记录
- 令牌安全地存储在数据库中

### 敏感数据

确保正确保护敏感信息：

- 应用密钥应安全存储
- 访问令牌自动生成和管理
- 考虑对敏感数据库字段进行加密

## 参考文档

- [企业微信 API 文档](https://developer.work.weixin.qq.com/document/)
- [应用管理](https://developer.work.weixin.qq.com/document/path/90967)
- [消息 API](https://developer.work.weixin.qq.com/document/path/96448)

## 许可证

此模块基于 MIT 许可证发布。详情请参阅随附的 LICENSE 文件。