<?php

namespace WechatWorkBundle\Request;

use WechatWorkBundle\Entity\Agent;

trait AgentAware
{
    private ?Agent $agent = null;

    public function getAgent(): ?Agent
    {
        return $this->agent;
    }

    public function setAgent(?Agent $agent): void
    {
        $this->agent = $agent;
    }
}
