<?php

declare(strict_types=1);

namespace WechatWorkBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use WechatWorkBundle\Entity\Agent;

/**
 * 企业微信应用管理控制器
 *
 * @extends AbstractCrudController<Agent>
 */
#[AdminCrud(routePath: '/wechat-work/agent', routeName: 'wechat_work_agent')]
final class AgentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Agent::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('企业微信应用')
            ->setEntityLabelInPlural('企业微信应用')
            ->setPageTitle('index', '企业微信应用管理')
            ->setPageTitle('new', '添加企业微信应用')
            ->setPageTitle('edit', '编辑企业微信应用')
            ->setPageTitle('detail', '企业微信应用详情')
            ->setSearchFields(['name', 'agentId', 'description'])
            ->setDefaultSort(['id' => 'DESC'])
            ->showEntityActionsInlined()
            ->setPaginatorPageSize(20)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID')
                ->hideOnForm(),

            AssociationField::new('corp', '所属企业')
                ->setRequired(true)
                ->setHelp('选择该应用所属的企业'),

            TextField::new('name', '应用名称')
                ->setRequired(true)
                ->setMaxLength(32)
                ->setHelp('企业微信应用的名称'),

            TextField::new('agentId', '应用ID')
                ->setRequired(true)
                ->setMaxLength(64)
                ->setHelp('企业微信应用的AgentId'),

            TextField::new('secret', '应用密钥')
                ->setRequired(true)
                ->setMaxLength(255)
                ->setHelp('企业微信应用的Secret')
                ->hideOnIndex(),

            TextField::new('token', '消息Token')
                ->setRequired(false)
                ->setMaxLength(120)
                ->setHelp('服务端消息验证Token')
                ->hideOnIndex(),

            TextField::new('encodingAESKey', '消息加密密钥')
                ->setRequired(false)
                ->setMaxLength(255)
                ->setHelp('服务端消息EncodingAESKey')
                ->hideOnIndex(),

            TextField::new('accessToken', '访问令牌')
                ->setRequired(false)
                ->setMaxLength(300)
                ->setHelp('当前有效的Access Token')
                ->hideOnForm()
                ->hideOnIndex(),

            DateTimeField::new('accessTokenExpireTime', '令牌过期时间')
                ->setRequired(false)
                ->setHelp('Access Token的过期时间')
                ->setFormat('yyyy-MM-dd HH:mm:ss')
                ->hideOnForm(),

            TextareaField::new('privateKeyContent', '私钥内容')
                ->setRequired(false)
                ->setHelp('应用的私钥内容')
                ->hideOnIndex(),

            TextField::new('privateKeyVersion', '私钥版本')
                ->setRequired(false)
                ->setMaxLength(20)
                ->setHelp('私钥的版本号')
                ->hideOnIndex(),

            TextareaField::new('welcomeText', '欢迎语')
                ->setRequired(false)
                ->setHelp('用户进入应用时的欢迎消息')
                ->hideOnIndex(),

            UrlField::new('squareLogoUrl', '方形头像')
                ->setRequired(false)
                ->setHelp('应用的方形logo图片URL')
                ->hideOnIndex(),

            TextField::new('description', '应用描述')
                ->setRequired(false)
                ->setMaxLength(255)
                ->setHelp('应用的详细描述')
                ->hideOnIndex(),

            TextField::new('redirectDomain', '可信域名')
                ->setRequired(false)
                ->setMaxLength(255)
                ->setHelp('应用的可信重定向域名')
                ->hideOnIndex(),

            BooleanField::new('reportLocationFlag', '地理位置上报')
                ->setRequired(false)
                ->setHelp('是否开启地理位置上报功能')
                ->hideOnIndex(),

            BooleanField::new('reportEnter', '上报用户进入')
                ->setRequired(false)
                ->setHelp('是否上报用户进入应用事件')
                ->hideOnIndex(),

            UrlField::new('homeUrl', '应用主页')
                ->setRequired(false)
                ->setHelp('应用的主页URL')
                ->hideOnIndex(),

            IntegerField::new('customizedPublishStatus', '代开发发布状态')
                ->setRequired(false)
                ->setHelp('代开发应用的发布状态')
                ->hideOnIndex(),

            DateTimeField::new('createTime', '创建时间')
                ->hideOnForm()
                ->setFormat('yyyy-MM-dd HH:mm:ss'),

            DateTimeField::new('updateTime', '更新时间')
                ->hideOnForm()
                ->setFormat('yyyy-MM-dd HH:mm:ss'),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('corp', '所属企业'))
            ->add(TextFilter::new('name', '应用名称'))
            ->add(TextFilter::new('agentId', '应用ID'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
            ->add(DateTimeFilter::new('updateTime', '更新时间'))
        ;
    }
}
