<?php

namespace WechatWorkBundle\Request;

use WechatWorkBundle\Entity\AccessTokenAware;
use WechatWorkBundle\Entity\Agent;

trait AgentAware
{
    private Agent|AccessTokenAware $agent;

    public function getAgent(): Agent|AccessTokenAware
    {
        return $this->agent;
    }

    public function setAgent(Agent|AccessTokenAware $agent): void
    {
        $this->agent = $agent;
    }
}
