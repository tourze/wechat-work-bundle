<?php

namespace WechatWorkBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIpBundle\Traits\IpTraceableAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\EnumExtra\Itemable;
use Tourze\WechatWorkContracts\AgentInterface;
use WechatWorkBundle\Exception\RuntimeException;
use WechatWorkBundle\Repository\AgentRepository;

/**
 * 除了自建应用，企业微信还有一些特殊的默认应用，如通讯录同步应用
 *
 * @see https://developer.work.weixin.qq.com/document/path/90967
 * @see https://developer.work.weixin.qq.com/document/path/96448
 */
#[ORM\Entity(repositoryClass: AgentRepository::class)]
#[ORM\Table(name: 'wechat_work_agent', options: ['comment' => '应用'])]
#[ORM\UniqueConstraint(name: 'wechat_work_agent_uniq_name', columns: ['corp_id', 'name'])]
#[ORM\UniqueConstraint(name: 'wechat_work_agent_uniq_agent_id', columns: ['corp_id', 'agent_id'])]
class Agent implements \Stringable, Itemable, AccessTokenAware, AgentInterface
{
    use TimestampableAware;
    use BlameableAware;
    use IpTraceableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private int $id = 0;

    #[ORM\ManyToOne(targetEntity: Corp::class, inversedBy: 'agents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Corp $corp = null;

    #[Groups(groups: ['admin_curd'])]
    #[TrackColumn]
    #[Assert\NotBlank]
    #[Assert\Length(max: 32)]
    #[ORM\Column(type: Types::STRING, length: 32, options: ['comment' => '名称'])]
    private ?string $name = null;

    #[Groups(groups: ['admin_curd'])]
    #[TrackColumn]
    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    #[ORM\Column(type: Types::STRING, length: 64, options: ['comment' => 'AgentId'])]
    private ?string $agentId = null;

    #[Groups(groups: ['admin_curd'])]
    #[TrackColumn]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => 'Secret'])]
    private ?string $secret = null;

    #[Groups(groups: ['admin_curd'])]
    #[TrackColumn]
    #[Assert\Length(max: 120)]
    #[ORM\Column(type: Types::STRING, length: 120, nullable: true, options: ['comment' => '服务端消息Token'])]
    private ?string $token = null;

    #[Groups(groups: ['admin_curd'])]
    #[Assert\Length(max: 255)]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '服务端消息EncodingAESKey'])]
    private ?string $encodingAESKey = null;

    #[Assert\Length(max: 300)]
    #[ORM\Column(length: 300, nullable: true, options: ['comment' => 'Access Token'])]
    private ?string $accessToken = null;

    #[Assert\Type(type: '\DateTimeImmutable')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => 'Access Token 过期时间'])]
    private ?\DateTimeImmutable $accessTokenExpireTime = null;

    #[Groups(groups: ['admin_curd'])]
    #[TrackColumn]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535)]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '私钥内容'])]
    private ?string $privateKeyContent = null;

    #[Groups(groups: ['admin_curd'])]
    #[TrackColumn]
    #[Assert\Length(max: 20)]
    #[ORM\Column(length: 20, nullable: true, options: ['comment' => '私钥版本'])]
    private ?string $privateKeyVersion = null;

    #[Groups(groups: ['admin_curd'])]
    #[TrackColumn]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535)]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '欢迎语'])]
    private ?string $welcomeText = null;

    #[Assert\Length(max: 255)]
    #[Assert\Url]
    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '方形头像'])]
    private ?string $squareLogoUrl = null;

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '详情'])]
    private ?string $description = null;

    /**
     * @var array<string>|null
     */
    #[Assert\Type(type: 'array')]
    #[ORM\Column(nullable: true, options: ['comment' => '可见范围（人员）'])]
    private ?array $allowUsers = null;

    /**
     * @var array<string>|null
     */
    #[Assert\Type(type: 'array')]
    #[ORM\Column(nullable: true, options: ['comment' => '可见范围（部门）'])]
    private ?array $allowParties = null;

    /**
     * @var array<string>|null
     */
    #[Assert\Type(type: 'array')]
    #[ORM\Column(nullable: true, options: ['comment' => '可见范围（标签）'])]
    private ?array $allowTags = null;

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '可信域名'])]
    private ?string $redirectDomain = null;

    #[Assert\Type(type: 'bool')]
    #[ORM\Column(nullable: true, options: ['comment' => '地理位置上报'])]
    private ?bool $reportLocationFlag = null;

    #[Assert\Type(type: 'bool')]
    #[ORM\Column(nullable: true, options: ['comment' => '上报用户进入应用'])]
    private ?bool $reportEnter = null;

    #[Assert\Length(max: 255)]
    #[Assert\Url]
    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '应用主页url'])]
    private ?string $homeUrl = null;

    #[Assert\Type(type: 'int')]
    #[ORM\Column(nullable: true, options: ['comment' => '代开发发布状态'])]
    private ?int $customizedPublishStatus = null;

    public function __toString(): string
    {
        if (0 === $this->getId()) {
            return '';
        }

        return "{$this->getName()}({$this->getAgentId()})";
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getCorp(): ?Corp
    {
        return $this->corp;
    }

    public function setCorp(?Corp $corp): void
    {
        $this->corp = $corp;
    }

    public function getAgentId(): ?string
    {
        return $this->agentId;
    }

    public function setAgentId(string $agentId): void
    {
        $this->agentId = $agentId;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(string $secret): void
    {
        $this->secret = $secret;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): void
    {
        $this->token = $token;
    }

    public function getEncodingAESKey(): ?string
    {
        return $this->encodingAESKey;
    }

    public function setEncodingAESKey(?string $encodingAESKey): void
    {
        $this->encodingAESKey = $encodingAESKey;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(?string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    public function getAccessTokenExpireTime(): ?\DateTimeImmutable
    {
        return $this->accessTokenExpireTime;
    }

    public function setAccessTokenExpireTime(?\DateTimeImmutable $accessTokenExpireTime): void
    {
        $this->accessTokenExpireTime = $accessTokenExpireTime;
    }

    /**
     * @return array{label: string, text: string, value: int}
     */
    public function toSelectItem(): array
    {
        $corp = $this->getCorp();
        if (null === $corp) {
            throw new RuntimeException('Agent must have a corp');
        }
        $label = "{$corp->getName()} - {$this->getName()}";

        return [
            'label' => $label,
            'text' => $label,
            'value' => $this->getId(),
        ];
    }

    public function prePersist(): void
    {
        if (null !== $this->getAgentId()) {
            $this->setAgentId(trim($this->getAgentId()));
        }
        if (null !== $this->getSecret()) {
            $this->setSecret(trim($this->getSecret()));
        }
    }

    public function getPrivateKeyContent(): ?string
    {
        return $this->privateKeyContent;
    }

    public function setPrivateKeyContent(?string $privateKeyContent): void
    {
        $this->privateKeyContent = $privateKeyContent;
    }

    public function getPrivateKeyVersion(): ?string
    {
        return $this->privateKeyVersion;
    }

    public function setPrivateKeyVersion(?string $privateKeyVersion): void
    {
        $this->privateKeyVersion = $privateKeyVersion;
    }

    public function getWelcomeText(): ?string
    {
        return $this->welcomeText;
    }

    public function setWelcomeText(?string $welcomeText): void
    {
        $this->welcomeText = $welcomeText;
    }

    public function getSquareLogoUrl(): ?string
    {
        return $this->squareLogoUrl;
    }

    public function setSquareLogoUrl(?string $squareLogoUrl): void
    {
        $this->squareLogoUrl = $squareLogoUrl;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return array<string>|null
     */
    public function getAllowUsers(): ?array
    {
        return $this->allowUsers;
    }

    /**
     * @param array<string>|null $allowUsers
     */
    public function setAllowUsers(?array $allowUsers): void
    {
        $this->allowUsers = $allowUsers;
    }

    /**
     * @return array<string>|null
     */
    public function getAllowParties(): ?array
    {
        return $this->allowParties;
    }

    /**
     * @param array<string>|null $allowParties
     */
    public function setAllowParties(?array $allowParties): void
    {
        $this->allowParties = $allowParties;
    }

    /**
     * @return array<string>|null
     */
    public function getAllowTags(): ?array
    {
        return $this->allowTags;
    }

    /**
     * @param array<string>|null $allowTags
     */
    public function setAllowTags(?array $allowTags): void
    {
        $this->allowTags = $allowTags;
    }

    public function getRedirectDomain(): ?string
    {
        return $this->redirectDomain;
    }

    public function setRedirectDomain(?string $redirectDomain): void
    {
        $this->redirectDomain = $redirectDomain;
    }

    public function isReportLocationFlag(): ?bool
    {
        return $this->reportLocationFlag;
    }

    public function setReportLocationFlag(?bool $reportLocationFlag): void
    {
        $this->reportLocationFlag = $reportLocationFlag;
    }

    public function isReportEnter(): ?bool
    {
        return $this->reportEnter;
    }

    public function setReportEnter(?bool $reportEnter): void
    {
        $this->reportEnter = $reportEnter;
    }

    public function getHomeUrl(): ?string
    {
        return $this->homeUrl;
    }

    public function setHomeUrl(?string $homeUrl): void
    {
        $this->homeUrl = $homeUrl;
    }

    public function getCustomizedPublishStatus(): ?int
    {
        return $this->customizedPublishStatus;
    }

    public function setCustomizedPublishStatus(?int $customizedPublishStatus): void
    {
        $this->customizedPublishStatus = $customizedPublishStatus;
    }
}
