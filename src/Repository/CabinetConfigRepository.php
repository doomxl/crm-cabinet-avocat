<?php

namespace App\Repository;

use App\Entity\CabinetConfig;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CabinetConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CabinetConfig::class);
    }

    public function getConfig(): CabinetConfig
    {
        $config = $this->find(1);
        if (!$config) {
            $config = new CabinetConfig();
            $config->setId(1);
            $this->getEntityManager()->persist($config);
            $this->getEntityManager()->flush();
        }
        return $config;
    }

    public function save(CabinetConfig $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
