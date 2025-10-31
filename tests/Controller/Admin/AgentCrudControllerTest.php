<?php

declare(strict_types=1);

namespace WechatWorkBundle\Tests\Controller\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\DomCrawler\Crawler;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use WechatWorkBundle\Controller\Admin\AgentCrudController;
use WechatWorkBundle\Entity\Agent;

/**
 * @internal
 */
#[CoversClass(AgentCrudController::class)]
#[RunTestsInSeparateProcesses]
final class AgentCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): AgentCrudController
    {
        return self::getService(AgentCrudController::class);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        return [
            'id' => ['ID'],
            'corp' => ['所属企业'],
            'name' => ['应用名称'],
            'agentId' => ['应用ID'],
            'accessTokenExpireTime' => ['令牌过期时间'],
            'createTime' => ['创建时间'],
            'updateTime' => ['更新时间'],
        ];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        // 提供一些实际的字段供测试
        return [
            'name' => ['name'],
            'agentId' => ['agentId'],
            'corp' => ['corp'],
        ];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        // 提供一些实际的字段供测试
        return [
            'name' => ['name'],
            'agentId' => ['agentId'],
            'corp' => ['corp'],
        ];
    }

    public function testGetEntityFqcn(): void
    {
        $controller = $this->getControllerService();

        $this->assertSame(Agent::class, $controller::getEntityFqcn());
    }

    public function testConfigureFields(): void
    {
        $controller = $this->getControllerService();
        $fields = iterator_to_array($controller->configureFields('index'));

        $this->assertNotEmpty($fields);
        $this->assertGreaterThan(5, count($fields));
    }

    public function testConfigureFieldsForForm(): void
    {
        $controller = $this->getControllerService();
        $fields = iterator_to_array($controller->configureFields('new'));

        $this->assertNotEmpty($fields);
        $this->assertGreaterThan(3, count($fields));
    }

    public function testConfigureFieldsForDetail(): void
    {
        $controller = $this->getControllerService();
        $fields = iterator_to_array($controller->configureFields('detail'));

        $this->assertNotEmpty($fields);
        $this->assertGreaterThan(5, count($fields));
    }

    public function testValidationErrors(): void
    {
        $client = $this->createAuthenticatedClient();

        // 访问新建页面
        $crawler = $client->request('GET', $this->generateAdminUrl('new'));
        $this->assertResponseIsSuccessful();

        // 获取表单
        $form = $crawler->selectButton('Create')->form();

        // 提交空表单，触发必填字段验证
        $crawler = $client->submit($form, [
            'Agent[name]' => '',
            'Agent[agentId]' => '',
            'Agent[secret]' => '',
        ]);

        // 验证返回422状态码（表单验证失败）
        $this->assertResponseStatusCodeSame(422);

        // 验证必填字段错误消息
        $content = $crawler->html();
        $this->assertStringContainsString('should not be blank', $content);

        // 验证具体字段的错误
        $this->assertFieldValidationError($crawler, 'Agent[name]');
        $this->assertFieldValidationError($crawler, 'Agent[agentId]');
        $this->assertFieldValidationError($crawler, 'Agent[secret]');
    }

    private function assertFieldValidationError(Crawler $crawler, string $fieldName): void
    {
        $field = $crawler->filter("input[name=\"{$fieldName}\"]");
        if (0 === $field->count()) {
            return;
        }

        $formGroup = $field->closest('.form-group');
        if (null === $formGroup) {
            return;
        }

        $errorMessage = $formGroup->filter('.invalid-feedback, .form-error-message');
        if ($errorMessage->count() > 0) {
            $this->assertStringContainsString('should not be blank', $errorMessage->text());
        }
    }
}
