<?php

namespace App\Repository;

use App\Entity\ModeleActe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ModeleActeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ModeleActe::class);
    }

    public function findByCategorie(string $categorie): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.categorie = :categorie')
            ->setParameter('categorie', $categorie)
            ->orderBy('m.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllGroupedByCategorie(): array
    {
        $modeles = $this->findBy([], ['categorie' => 'ASC', 'nom' => 'ASC']);
        $grouped = [];
        foreach ($modeles as $modele) {
            $cat = $modele->getCategorie() ?? 'Sans catégorie';
            $grouped[$cat][] = $modele;
        }
        return $grouped;
    }

    public function save(ModeleActe $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ModeleActe $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
