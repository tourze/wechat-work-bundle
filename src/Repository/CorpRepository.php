<?php

namespace WechatWorkBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use WechatWorkBundle\Entity\Corp;

/**
 * @method Corp|null find($id, $lockMode = null, $lockVersion = null)
 * @method Corp|null findOneBy(array $criteria, array $orderBy = null)
 * @method Corp[]    findAll()
 * @method Corp[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CorpRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Corp::class);
    }
}
