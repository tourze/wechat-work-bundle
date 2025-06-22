<?php

namespace WechatWorkBundle\Request;

use Tourze\WechatWorkContracts\AgentInterface;

trait AgentAware
{
    private ?AgentInterface $agent = null;

    public function getAgent(): ?AgentInterface
    {
        return $this->agent;
    }

    public function setAgent(?AgentInterface $agent): void
    {
        $this->agent = $agent;
    }
}
