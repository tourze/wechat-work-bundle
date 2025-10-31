<?php

declare(strict_types=1);

namespace WechatWorkBundle\Service;

use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use WechatWorkBundle\Entity\Agent;
use WechatWorkBundle\Entity\Corp;

/**
 * 企业微信管理后台菜单提供者
 */
#[Autoconfigure(public: true)]
readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(
        private LinkGeneratorInterface $linkGenerator,
    ) {
    }

    public function __invoke(ItemInterface $item): void
    {
        if (null === $item->getChild('第三方集成')) {
            $item->addChild('第三方集成');
        }

        $integrationMenu = $item->getChild('第三方集成');
        if (null === $integrationMenu) {
            return;
        }

        // 添加企业微信管理子菜单
        if (null === $integrationMenu->getChild('企业微信')) {
            $integrationMenu->addChild('企业微信')
                ->setAttribute('icon', 'fab fa-weixin')
            ;
        }

        $wechatMenu = $integrationMenu->getChild('企业微信');
        if (null === $wechatMenu) {
            return;
        }

        $wechatMenu->addChild('企业管理')
            ->setUri($this->linkGenerator->getCurdListPage(Corp::class))
            ->setAttribute('icon', 'fas fa-building')
        ;

        $wechatMenu->addChild('应用管理')
            ->setUri($this->linkGenerator->getCurdListPage(Agent::class))
            ->setAttribute('icon', 'fas fa-mobile-alt')
        ;
    }
}
