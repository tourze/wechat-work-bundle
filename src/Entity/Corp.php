<?php

namespace WechatWorkBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIpBundle\Traits\IpTraceableAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\WechatWorkContracts\CorpInterface;
use WechatWorkBundle\Repository\CorpRepository;

#[ORM\Entity(repositoryClass: CorpRepository::class)]
#[ORM\Table(name: 'wechat_work_corp', options: ['comment' => '企业信息'])]
class Corp implements \Stringable, CorpInterface
{
    use TimestampableAware;
    use BlameableAware;
    use IpTraceableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private int $id = 0;

    #[Groups(groups: ['admin_curd'])]
    #[TrackColumn]
    #[Assert\NotBlank]
    #[Assert\Length(max: 32)]
    #[ORM\Column(type: Types::STRING, length: 32, unique: true, options: ['comment' => '名称'])]
    private ?string $name = null;

    #[Groups(groups: ['admin_curd'])]
    #[TrackColumn]
    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    #[ORM\Column(type: Types::STRING, length: 64, unique: true, options: ['comment' => '企业ID'])]
    private ?string $corpId = null;

    #[Groups(groups: ['admin_curd'])]
    #[TrackColumn]
    #[Assert\NotBlank]
    #[Assert\Length(max: 128)]
    #[ORM\Column(type: Types::STRING, length: 128, nullable: false, options: ['comment' => '企业密钥'])]
    private ?string $corpSecret = null;

    /**
     * @var Collection<int, Agent>
     */
    #[Groups(groups: ['admin_curd'])]
    #[ORM\OneToMany(targetEntity: Agent::class, mappedBy: 'corp', cascade: ['persist'], orphanRemoval: true)]
    private Collection $agents;

    #[Assert\Type(type: 'bool')]
    #[ORM\Column(nullable: true, options: ['comment' => '来自服务商授权'])]
    private ?bool $fromProvider = false;

    public function __construct()
    {
        $this->agents = new ArrayCollection();
    }

    public function __toString(): string
    {
        if (0 === $this->getId()) {
            return '';
        }

        return $this->getName() ?? '';
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

    /**
     * @return Collection<int, Agent>
     */
    public function getAgents(): Collection
    {
        return $this->agents;
    }

    public function addAgent(Agent $agent): self
    {
        if (!$this->agents->contains($agent)) {
            $this->agents->add($agent);
            $agent->setCorp($this);
        }

        return $this;
    }

    public function removeAgent(Agent $agent): self
    {
        if ($this->agents->removeElement($agent)) {
            // set the owning side to null (unless already changed)
            if ($agent->getCorp() === $this) {
                $agent->setCorp(null);
            }
        }

        return $this;
    }

    public function getCorpId(): ?string
    {
        return $this->corpId;
    }

    public function setCorpId(string $corpId): void
    {
        $this->corpId = $corpId;
    }

    public function getCorpSecret(): ?string
    {
        return $this->corpSecret;
    }

    public function setCorpSecret(string $corpSecret): void
    {
        $this->corpSecret = $corpSecret;
    }

    public function isFromProvider(): ?bool
    {
        return $this->fromProvider;
    }

    public function setFromProvider(?bool $fromProvider): void
    {
        $this->fromProvider = $fromProvider;
    }
}
