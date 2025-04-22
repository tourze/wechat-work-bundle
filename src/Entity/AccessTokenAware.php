<?php

namespace WechatWorkBundle\Entity;

interface AccessTokenAware
{
    public function getAccessToken(): ?string;
}
