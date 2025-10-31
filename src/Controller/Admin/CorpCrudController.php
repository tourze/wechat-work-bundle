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
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use WechatWorkBundle\Entity\Corp;

/**
 * 企业微信企业管理控制器
 *
 * @extends AbstractCrudController<Corp>
 */
#[AdminCrud(routePath: '/wechat-work/corp', routeName: 'wechat_work_corp')]
final class CorpCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Corp::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('企业微信企业')
            ->setEntityLabelInPlural('企业微信企业')
            ->setPageTitle('index', '企业微信企业管理')
            ->setPageTitle('new', '添加企业微信企业')
            ->setPageTitle('edit', '编辑企业微信企业')
            ->setPageTitle('detail', '企业微信企业详情')
            ->setSearchFields(['name', 'corpId'])
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

            TextField::new('name', '企业名称')
                ->setRequired(true)
                ->setMaxLength(32)
                ->setHelp('企业的显示名称'),

            TextField::new('corpId', '企业ID')
                ->setRequired(true)
                ->setMaxLength(64)
                ->setHelp('企业微信的企业ID'),

            TextField::new('corpSecret', '企业密钥')
                ->setRequired(true)
                ->setMaxLength(128)
                ->setHelp('企业微信的企业密钥')
                ->hideOnIndex(),

            BooleanField::new('fromProvider', '来自服务商')
                ->setRequired(false)
                ->setHelp('是否来自服务商授权'),

            AssociationField::new('agents', '关联应用')
                ->setRequired(false)
                ->setHelp('该企业下的所有应用列表')
                ->hideOnForm(),

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
            ->add(TextFilter::new('name', '企业名称'))
            ->add(TextFilter::new('corpId', '企业ID'))
            ->add(BooleanFilter::new('fromProvider', '来自服务商'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
            ->add(DateTimeFilter::new('updateTime', '更新时间'))
        ;
    }
}
