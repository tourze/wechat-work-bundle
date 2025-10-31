# wechat-work-bundle

[English](README.md) | [中文](README.zh-CN.md)

[![PHP Version](https://img.shields.io/packagist/php-v/tourze/wechat-work-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-work-bundle)
[![Latest Version](https://img.shields.io/packagist/v/tourze/wechat-work-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-work-bundle)  
[![License](https://img.shields.io/packagist/l/tourze/wechat-work-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-work-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/wechat-work-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/wechat-work-bundle)  
[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/wechat-work-bundle/ci.yml?style=flat-square)](https://github.com/tourze/wechat-work-bundle/actions)  
[![Coverage Status](https://img.shields.io/codecov/c/github/tourze/wechat-work-bundle?style=flat-square)](https://codecov.io/gh/tourze/wechat-work-bundle)

WeChatWork Bundle provides WeChatWork API integration capabilities for Symfony applications.

## Table of Contents

- [Dependencies](#dependencies)
- [Installation](#installation)
- [Features](#features)
- [Configuration](#configuration)
  - [Basic Configuration](#basic-configuration)
  - [Database Configuration](#database-configuration)
- [Quick Start](#quick-start)
  - [1. Configure Enterprise Information](#1-configure-enterprise-information)
  - [2. Using Services](#2-using-services)
  - [3. Automatic Access Token Refresh](#3-automatic-access-token-refresh)
- [Console Commands](#console-commands)
  - [Refresh Access Token](#refresh-access-token)
  - [Synchronize Application Information](#synchronize-application-information)
- [Advanced Usage](#advanced-usage)
  - [Custom Request Implementation](#custom-request-implementation)
  - [Service Extension](#service-extension)
- [Entity Description](#entity-description)
  - [Corp (Enterprise)](#corp-enterprise)
  - [Agent (Application)](#agent-application)
- [Security](#security)
  - [Access Token Management](#access-token-management)
  - [Sensitive Data](#sensitive-data)
- [Reference Documentation](#reference-documentation)
- [License](#license)

## Dependencies

This bundle requires:

- PHP 8.1 or higher
- Symfony 7.3 or higher
- Doctrine ORM 3.0 or higher
- `tourze/wechat-work-contracts` for interface definitions
- `tourze/doctrine-timestamp-bundle` for timestamp handling
- `tourze/doctrine-track-bundle` for tracking changes
- `tourze/doctrine-user-bundle` for user-related functionality
- `tourze/doctrine-ip-bundle` for IP tracking
- `tourze/doctrine-resolve-target-entity-bundle` for entity resolution
- `tourze/http-client-bundle` for HTTP client functionality
- `tourze/symfony-cron-job-bundle` for scheduled tasks
- `tourze/enum-extra` for enum utilities
- `nesbot/carbon` for date/time handling
- `yiisoft/json` for JSON processing

## Installation

```bash
composer require tourze/wechat-work-bundle
```

## Features

- WeChatWork application management
- Automatic access token refresh
- Application information synchronization
- Enterprise information management
- Full Doctrine ORM support
- Scheduled task support

## Configuration

### Basic Configuration

Enable the bundle in your `config/bundles.php`:

```php
<?php

return [
    // Other bundles...
    WechatWorkBundle\WechatWorkBundle::class => ['all' => true],
];
```

### Database Configuration

Run migrations to create required tables:

```bash
php bin/console doctrine:migrations:migrate
```

## Quick Start

### 1. Configure Enterprise Information

First, create a Corp entity and at least one Agent:

```php
use WechatWorkBundle\Entity\Corp;
use WechatWorkBundle\Entity\Agent;

// Create enterprise
$corp = new Corp();
$corp->setName('My Company');
$corp->setCorpId('your_corp_id');

// Create application
$agent = new Agent();
$agent->setName('My App');
$agent->setAgentId('your_agent_id');
$agent->setSecret('your_agent_secret');
$agent->setCorp($corp);

$entityManager->persist($corp);
$entityManager->persist($agent);
$entityManager->flush();
```

### 2. Using Services

```php
use WechatWorkBundle\Service\WorkService;

class MyService
{
    public function __construct(
        private WorkService $workService
    ) {}

    public function sendMessage(): void
    {
        // The service automatically handles access token refresh
        $this->workService->refreshAgentAccessToken($agent);
        
        // Use the service to make API calls
        // Implementation depends on your specific needs
    }
}
```

### 3. Automatic Access Token Refresh

The bundle automatically handles access token refresh. You can also manually 
refresh tokens:

```bash
php bin/console wechat-work:refresh-agent-access-token
```

## Console Commands

### Refresh Access Token

Refresh access tokens for all agents:

```bash
php bin/console wechat-work:refresh-agent-access-token
```

### Synchronize Application Information

Synchronize application information from WeChatWork API:

```bash
php bin/console wechat-work:sync-agent-info
```

## Advanced Usage

### Custom Request Implementation

You can extend the WorkService to implement custom API requests:

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

### Service Extension

Create custom services that utilize the WeChatWork API:

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
            // Send notification via each active agent
            $this->workService->refreshAgentAccessToken($agent);
            // Implement your notification logic here
        }
    }
}
```

## Entity Description

### Corp (Enterprise)

Represents a WeChatWork enterprise:

- `name`: Enterprise name
- `corpId`: Unique enterprise identifier
- `agents`: Collection of associated applications

### Agent (Application)

Represents a WeChatWork application within an enterprise:

- `name`: Application name
- `agentId`: Application identifier
- `secret`: Application secret for API access
- `accessToken`: Current access token (auto-managed)
- `accessTokenExpireTime`: Token expiration time
- `corp`: Associated enterprise

## Security

### Access Token Management

Access tokens are automatically managed by the bundle:

- Tokens are refreshed before expiration
- Failed refresh attempts are logged
- Tokens are stored securely in the database

### Sensitive Data

Ensure proper protection of sensitive information:

- Agent secrets should be stored securely
- Access tokens are auto-generated and managed
- Consider encryption for sensitive database fields

## Reference Documentation

- [WeChatWork API Documentation](https://developer.work.weixin.qq.com/document/)
- [Application Management](https://developer.work.weixin.qq.com/document/path/90967)
- [Message API](https://developer.work.weixin.qq.com/document/path/96448)

## License

This bundle is released under the MIT License. See the bundled LICENSE file 
for details.