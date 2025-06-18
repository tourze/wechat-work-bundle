<?php

namespace WechatWorkBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineIpBundle\Attribute\UpdateIpColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\EasyAdmin\Attribute\Action\CurdAction;
use Tourze\EasyAdmin\Attribute\Action\Listable;
use Tourze\WechatWorkContracts\CorpInterface;
use WechatWorkBundle\Repository\CorpRepository;

#[Listable]
#[ORM\Entity(repositoryClass: CorpRepository::class)]
#[ORM\Table(name: 'wechat_work_corp', options: ['comment' => '企业信息'])]
class Corp implements \Stringable, CorpInterface
{
    use TimestampableAware;
    use BlameableAware;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    #[Groups(['admin_curd'])]
    #[TrackColumn]
    #[ORM\Column(type: Types::STRING, length: 32, unique: true, options: ['comment' => '名称'])]
    private ?string $name = null;

    #[Groups(['admin_curd'])]
    #[TrackColumn]
    #[ORM\Column(type: Types::STRING, length: 64, unique: true, options: ['comment' => '企业ID'])]
    private ?string $corpId = null;

    /**
     * @var Collection<Agent>
     */
    #[Groups(['admin_curd'])]
    #[CurdAction(label: '应用管理')]
    #[ORM\OneToMany(targetEntity: Agent::class, mappedBy: 'corp', cascade: ['persist'], orphanRemoval: true)]
    private Collection $agents;

    #[ORM\Column(nullable: true, options: ['comment' => '来自服务商授权'])]
    private ?bool $fromProvider = false;


    #[CreateIpColumn]
    #[ORM\Column(length: 128, nullable: true, options: ['comment' => '创建时IP'])]
    private ?string $createdFromIp = null;

    #[UpdateIpColumn]
    #[ORM\Column(length: 128, nullable: true, options: ['comment' => '更新时IP'])]
    private ?string $updatedFromIp = null;

    public function __construct()
    {
        $this->agents = new ArrayCollection();
    }

    public function __toString(): string
    {
        if (!$this->getId()) {
            return '';
        }

        return $this->getName();
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
            $this->agents[] = $agent;
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

    public function setCorpId(string $corpId): self
    {
        $this->corpId = $corpId;

        return $this;
    }

    public function isFromProvider(): ?bool
    {
        return $this->fromProvider;
    }

    public function setFromProvider(?bool $fromProvider): static
    {
        $this->fromProvider = $fromProvider;

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
