<?php

declare(strict_types=1);

namespace WechatWorkBundle\Tests\Controller\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\DomCrawler\Crawler;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use WechatWorkBundle\Controller\Admin\CorpCrudController;
use WechatWorkBundle\Entity\Corp;

/**
 * @internal
 */
#[CoversClass(CorpCrudController::class)]
#[RunTestsInSeparateProcesses]
final class CorpCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): CorpCrudController
    {
        return self::getService(CorpCrudController::class);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        return [
            'id' => ['ID'],
            'name' => ['企业名称'],
            'corpId' => ['企业ID'],
            'fromProvider' => ['来自服务商'],
            'agents' => ['关联应用'],
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
            'corpId' => ['corpId'],
            'fromProvider' => ['fromProvider'],
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
            'corpId' => ['corpId'],
            'fromProvider' => ['fromProvider'],
        ];
    }

    public function testGetEntityFqcn(): void
    {
        $controller = $this->getControllerService();

        $this->assertSame(Corp::class, $controller::getEntityFqcn());
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
            'Corp[name]' => '',
            'Corp[corpId]' => '',
            'Corp[corpSecret]' => '',
        ]);

        // 验证返回422状态码（表单验证失败）
        $this->assertResponseStatusCodeSame(422);

        // 验证必填字段错误消息
        $content = $crawler->html();
        $this->assertStringContainsString('should not be blank', $content);

        // 验证具体字段的错误
        $this->assertFieldValidationError($crawler, 'Corp[name]');
        $this->assertFieldValidationError($crawler, 'Corp[corpId]');
        $this->assertFieldValidationError($crawler, 'Corp[corpSecret]');
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
