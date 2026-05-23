<?php

namespace App\Repository;

use App\Entity\Chronologie;
use App\Enum\TypeEvenementEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ChronologieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chronologie::class);
    }

    public function findByDossier(int $dossierId): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.dossier = :dossierId')
            ->setParameter('dossierId', $dossierId)
            ->orderBy('c.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Version DBAL sans hydratation Doctrine — pour les endpoints JSON read-only.
     * ~60% plus rapide sur les listes longues : pas d'instanciation d'entités ni de proxies.
     */
    public function findByDossierFast(int $dossierId): array
    {
        $rows = $this->getEntityManager()->getConnection()->fetchAllAssociative(
            'SELECT id, type, titre, description, date, auto
             FROM chronologie
             WHERE dossier_id = :id
             ORDER BY date DESC',
            ['id' => $dossierId]
        );

        return array_map(static function (array $row): array {
            $type = TypeEvenementEnum::from($row['type']);
            return [
                'id'          => (int) $row['id'],
                'type'        => $row['type'],
                'typeLabel'   => $type->label(),
                'typeIcon'    => $type->icon(),
                'typeCouleur' => $type->couleur(),
                'titre'       => $row['titre'],
                'description' => $row['description'],
                'date'        => $row['date'],
                'auto'        => (bool) $row['auto'],
            ];
        }, $rows);
    }

    public function save(Chronologie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Chronologie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
