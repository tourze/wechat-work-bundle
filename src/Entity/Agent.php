<?php

namespace WechatWorkBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineIpBundle\Attribute\UpdateIpColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\EasyAdmin\Attribute\Action\Creatable;
use Tourze\EasyAdmin\Attribute\Action\Deletable;
use Tourze\EasyAdmin\Attribute\Action\Editable;
use Tourze\EasyAdmin\Attribute\Action\Listable;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Field\FormField;
use Tourze\EasyAdmin\Attribute\Filter\Keyword;
use Tourze\EasyAdmin\Attribute\Permission\AsPermission;
use Tourze\EnumExtra\Itemable;
use Tourze\WechatWorkContracts\AgentInterface;
use WechatWorkBundle\Repository\AgentRepository;

/**
 * 除了自建应用，企业微信还有一些特殊的默认应用，如通讯录同步应用
 *
 * @see https://developer.work.weixin.qq.com/document/path/90967
 * @see https://developer.work.weixin.qq.com/document/path/96448
 */
#[AsPermission(title: '应用')]
#[Listable]
#[Deletable]
#[Editable]
#[Creatable]
#[ORM\Entity(repositoryClass: AgentRepository::class)]
#[ORM\Table(name: 'wechat_work_agent', options: ['comment' => '应用'])]
#[ORM\UniqueConstraint(name: 'wechat_work_agent_uniq_name', columns: ['corp_id', 'name'])]
#[ORM\UniqueConstraint(name: 'wechat_work_agent_uniq_agent_id', columns: ['corp_id', 'agent_id'])]
#[ORM\HasLifecycleCallbacks]
class Agent implements \Stringable, Itemable, AccessTokenAware, AgentInterface
{
    use TimestampableAware;
    use BlameableAware;
    #[ListColumn(order: -1)]
    #[ExportColumn]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    #[ORM\ManyToOne(targetEntity: Corp::class, inversedBy: 'agents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Corp $corp = null;

    #[Groups(['admin_curd'])]
    #[TrackColumn]
    #[FormField]
    #[Keyword]
    #[ListColumn]
    #[ORM\Column(type: Types::STRING, length: 32, options: ['comment' => '名称'])]
    private ?string $name = null;

    #[Groups(['admin_curd'])]
    #[TrackColumn]
    #[FormField(span: 7)]
    #[ListColumn]
    #[ORM\Column(type: Types::STRING, length: 64, options: ['comment' => 'AgentId'])]
    private ?string $agentId = null;

    #[Groups(['admin_curd'])]
    #[TrackColumn]
    #[FormField(span: 17)]
    #[ListColumn]
    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => 'Secret'])]
    private ?string $secret = null;

    #[Groups(['admin_curd'])]
    #[TrackColumn]
    #[FormField]
    #[ORM\Column(type: Types::STRING, length: 120, nullable: true, options: ['comment' => '服务端消息Token'])]
    private ?string $token = null;

    #[Groups(['admin_curd'])]
    #[FormField]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '服务端消息EncodingAESKey'])]
    private ?string $encodingAESKey = null;

    #[ORM\Column(length: 300, nullable: true)]
    private ?string $accessToken = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $accessTokenExpireTime = null;

    #[Groups(['admin_curd'])]
    #[TrackColumn]
    #[FormField]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '私钥内容'])]
    private ?string $privateKeyContent = null;

    #[Groups(['admin_curd'])]
    #[TrackColumn]
    #[FormField]
    #[ORM\Column(length: 20, nullable: true, options: ['comment' => '私钥版本'])]
    private ?string $privateKeyVersion = null;

    #[Groups(['admin_curd'])]
    #[TrackColumn]
    #[FormField]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '欢迎语'])]
    private ?string $welcomeText = null;

    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '方形头像'])]
    private ?string $squareLogoUrl = null;

    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '详情'])]
    private ?string $description = null;

    #[ORM\Column(nullable: true, options: ['comment' => '可见范围（人员）'])]
    private ?array $allowUsers = null;

    #[ORM\Column(nullable: true, options: ['comment' => '可见范围（部门）'])]
    private ?array $allowParties = null;

    #[ORM\Column(nullable: true, options: ['comment' => '可见范围（标签）'])]
    private ?array $allowTags = null;

    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '可信域名'])]
    private ?string $redirectDomain = null;

    #[ORM\Column(nullable: true, options: ['comment' => '地理位置上报'])]
    private ?bool $reportLocationFlag = null;

    #[ORM\Column(nullable: true, options: ['comment' => '上报用户进入应用'])]
    private ?bool $reportEnter = null;

    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '应用主页url'])]
    private ?string $homeUrl = null;

    #[ORM\Column(nullable: true, options: ['comment' => '代开发发布状态'])]
    private ?int $customizedPublishStatus = null;


    #[CreateIpColumn]
    #[ORM\Column(length: 128, nullable: true, options: ['comment' => '创建时IP'])]
    private ?string $createdFromIp = null;

    #[UpdateIpColumn]
    #[ORM\Column(length: 128, nullable: true, options: ['comment' => '更新时IP'])]
    private ?string $updatedFromIp = null;

    public function __toString(): string
    {
        if (!$this->getId()) {
            return '';
        }

        return "{$this->getName()}({$this->getAgentId()})";
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCorp(): ?Corp
    {
        return $this->corp;
    }

    public function setCorp(?Corp $corp): self
    {
        $this->corp = $corp;

        return $this;
    }

    public function getAgentId(): ?string
    {
        return $this->agentId;
    }

    public function setAgentId(string $agentId): self
    {
        $this->agentId = $agentId;

        return $this;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(string $secret): self
    {
        $this->secret = $secret;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getEncodingAESKey(): ?string
    {
        return $this->encodingAESKey;
    }

    public function setEncodingAESKey(?string $encodingAESKey): self
    {
        $this->encodingAESKey = $encodingAESKey;

        return $this;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(?string $accessToken): static
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getAccessTokenExpireTime(): ?\DateTimeInterface
    {
        return $this->accessTokenExpireTime;
    }

    public function setAccessTokenExpireTime(?\DateTimeInterface $accessTokenExpireTime): static
    {
        $this->accessTokenExpireTime = $accessTokenExpireTime;

        return $this;
    }

    public function toSelectItem(): array
    {
        $label = "{$this->getCorp()->getName()} - {$this->getName()}";

        return [
            'label' => $label,
            'text' => $label,
            'value' => $this->getId(),
        ];
    }

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        if ($this->getAgentId() !== null) {
            $this->setAgentId(trim($this->getAgentId()));
        }
        if ($this->getSecret() !== null) {
            $this->setSecret(trim($this->getSecret()));
        }
    }

    public function getPrivateKeyContent(): ?string
    {
        return $this->privateKeyContent;
    }

    public function setPrivateKeyContent(?string $privateKeyContent): self
    {
        $this->privateKeyContent = $privateKeyContent;

        return $this;
    }

    public function getPrivateKeyVersion(): ?string
    {
        return $this->privateKeyVersion;
    }

    public function setPrivateKeyVersion(?string $privateKeyVersion): self
    {
        $this->privateKeyVersion = $privateKeyVersion;

        return $this;
    }

    public function getWelcomeText(): ?string
    {
        return $this->welcomeText;
    }

    public function setWelcomeText(?string $welcomeText): self
    {
        $this->welcomeText = $welcomeText;

        return $this;
    }

    public function getSquareLogoUrl(): ?string
    {
        return $this->squareLogoUrl;
    }

    public function setSquareLogoUrl(?string $squareLogoUrl): static
    {
        $this->squareLogoUrl = $squareLogoUrl;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getAllowUsers(): ?array
    {
        return $this->allowUsers;
    }

    public function setAllowUsers(?array $allowUsers): static
    {
        $this->allowUsers = $allowUsers;

        return $this;
    }

    public function getAllowParties(): ?array
    {
        return $this->allowParties;
    }

    public function setAllowParties(?array $allowParties): static
    {
        $this->allowParties = $allowParties;

        return $this;
    }

    public function getAllowTags(): ?array
    {
        return $this->allowTags;
    }

    public function setAllowTags(?array $allowTags): static
    {
        $this->allowTags = $allowTags;

        return $this;
    }

    public function getRedirectDomain(): ?string
    {
        return $this->redirectDomain;
    }

    public function setRedirectDomain(?string $redirectDomain): static
    {
        $this->redirectDomain = $redirectDomain;

        return $this;
    }

    public function isReportLocationFlag(): ?bool
    {
        return $this->reportLocationFlag;
    }

    public function setReportLocationFlag(?bool $reportLocationFlag): static
    {
        $this->reportLocationFlag = $reportLocationFlag;

        return $this;
    }

    public function isReportEnter(): ?bool
    {
        return $this->reportEnter;
    }

    public function setReportEnter(?bool $reportEnter): static
    {
        $this->reportEnter = $reportEnter;

        return $this;
    }

    public function getHomeUrl(): ?string
    {
        return $this->homeUrl;
    }

    public function setHomeUrl(?string $homeUrl): static
    {
        $this->homeUrl = $homeUrl;

        return $this;
    }

    public function getCustomizedPublishStatus(): ?int
    {
        return $this->customizedPublishStatus;
    }

    public function setCustomizedPublishStatus(?int $customizedPublishStatus): static
    {
        $this->customizedPublishStatus = $customizedPublishStatus;

        return $this;
    }


    public function setCreatedFromIp(?string $createdFromIp): self
    {
        $this->createdFromIp = $createdFromIp;

        return $this;
    }

    public function getCreatedFromIp(): ?string
    {
        return $this->createdFromIp;
    }

    public function setUpdatedFromIp(?string $updatedFromIp): self
    {
        $this->updatedFromIp = $updatedFromIp;

        return $this;
    }

    public function getUpdatedFromIp(): ?string
    {
        return $this->updatedFromIp;
    }

}
