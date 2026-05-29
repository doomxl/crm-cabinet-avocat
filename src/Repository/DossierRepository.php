<?php

namespace App\Repository;

use App\Entity\Dossier;
use App\Enum\StatutDossierEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DossierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Dossier::class);
    }

    public function findByClient(int $clientId): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.client = :clientId')
            ->setParameter('clientId', $clientId)
            ->orderBy('d.dateOuverture', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByStatut(string $statut): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.statut = :statut')
            ->setParameter('statut', $statut)
            ->orderBy('d.dateOuverture', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByMatiere(string $matiere): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.matiere = :matiere')
            ->setParameter('matiere', $matiere)
            ->orderBy('d.dateOuverture', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findEnCours(): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.statut = :statut')
            ->setParameter('statut', StatutDossierEnum::En_cours->value)
            ->orderBy('d.dateOuverture', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function searchGlobal(string $q, int $limit = 15): array
    {
        $qNorm  = mb_strtolower(trim($q));
        $tokens = array_values(array_filter(explode(' ', $qNorm), fn($t) => strlen($t) >= 1));

        if (empty($tokens)) return [];

        // Extrait un éventuel numéro de dossier (ex: "17", "n°17", "n° 17", "#17")
        $qNumeric = preg_replace('/[^0-9]/', '', $qNorm);

        // Recherche DB sur titre, parties adverses, nom client et ID
        $qb = $this->createQueryBuilder('d')
            ->leftJoin('d.client', 'c')
            ->addSelect('c');

        $parts = [];
        foreach ($tokens as $i => $token) {
            $p = 'qt' . $i;
            $parts[] = "(LOWER(d.titre) LIKE :$p OR LOWER(d.partieAdverseNom) LIKE :$p"
                     . " OR LOWER(d.partieAdverseEntreprise) LIKE :$p"
                     . " OR LOWER(c.nom) LIKE :$p OR LOWER(c.prenom) LIKE :$p"
                     . " OR LOWER(d.numeroDossier) LIKE :$p OR LOWER(c.codeClient) LIKE :$p)";
            $qb->setParameter($p, '%' . $token . '%');
        }

        $whereClause = '(' . implode(' AND ', $parts) . ')';
        if ($qNumeric !== '' && is_numeric($qNumeric)) {
            $whereClause .= ' OR d.id = :qid';
            $qb->setParameter('qid', (int) $qNumeric);
        }
        $qb->andWhere($whereClause);
        $dbResults = $qb->orderBy('d.dateOuverture', 'DESC')->getQuery()->getResult();

        // Recherche floue via similar_text()
        $fuzzyAdded = [];
        if (count($dbResults) < $limit) {
            $all = $this->createQueryBuilder('d')
                ->leftJoin('d.client', 'c')
                ->addSelect('c')
                ->orderBy('d.dateOuverture', 'DESC')
                ->setMaxResults(500)
                ->getQuery()
                ->getResult();

            $dbIds = array_flip(array_map(fn($d) => $d->getId(), $dbResults));

            foreach ($all as $dossier) {
                if (isset($dbIds[$dossier->getId()])) continue;
                if ($this->fuzzyMatchDossier($dossier, $tokens)) {
                    $fuzzyAdded[] = $dossier;
                }
            }
        }

        return array_slice(array_merge($dbResults, $fuzzyAdded), 0, $limit);
    }

    private function fuzzyMatchDossier(Dossier $dossier, array $tokens): bool
    {
        $fields = array_filter([
            mb_strtolower((string)$dossier->getTitre()),
            mb_strtolower((string)$dossier->getPartieAdverseNom()),
            mb_strtolower((string)$dossier->getPartieAdverseEntreprise()),
            mb_strtolower((string)($dossier->getClient()?->getNom() ?? '')),
        ]);

        foreach ($tokens as $token) {
            if (strlen($token) < 3 && !ctype_digit($token)) continue;
            foreach ($fields as $field) {
                foreach (explode(' ', $field) as $word) {
                    if (strlen($word) < 3) continue;
                    similar_text($token, $word, $pct);
                    if ($pct >= 75.0) return true;
                }
            }
        }
        return false;
    }

    public function findFiltered(?string $statut = null, ?string $matiere = null, ?int $clientId = null, ?string $q = null): array
    {
        $qb = $this->createQueryBuilder('d')->leftJoin('d.client', 'c')->addSelect('c');
        if ($statut) {
            $qb->andWhere('d.statut = :statut')->setParameter('statut', $statut);
        }
        if ($matiere) {
            $qb->andWhere('d.matiere = :matiere')->setParameter('matiere', $matiere);
        }
        if ($clientId) {
            $qb->andWhere('d.client = :clientId')->setParameter('clientId', $clientId);
        }
        if ($q) {
            $qb->andWhere('d.titre LIKE :q OR c.nom LIKE :q')->setParameter('q', '%' . $q . '%');
        }
        return $qb->orderBy('d.dateOuverture', 'DESC')->getQuery()->getResult();
    }

    public function countByStatut(): array
    {
        return $this->createQueryBuilder('d')
            ->select('d.statut, COUNT(d.id) as total')
            ->groupBy('d.statut')
            ->getQuery()
            ->getResult();
    }

    public function countByMatiere(): array
    {
        return $this->createQueryBuilder('d')
            ->select('d.matiere, COUNT(d.id) as total')
            ->groupBy('d.matiere')
            ->getQuery()
            ->getResult();
    }

    public function updateObsoleteMatieres(array $validLabels, ?string $fallback): void
    {
        $qb = $this->createQueryBuilder('d')
            ->update()
            ->set('d.matiere', ':fallback')
            ->where('d.matiere IS NOT NULL')
            ->setParameter('fallback', $fallback);

        if (!empty($validLabels)) {
            $qb->andWhere('d.matiere NOT IN (:validLabels)')
               ->setParameter('validLabels', $validLabels);
        }

        $qb->getQuery()->execute();
    }

    public function save(Dossier $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Dossier $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
