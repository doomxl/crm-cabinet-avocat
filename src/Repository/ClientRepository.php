<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    public function search(string $q): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.nom LIKE :q OR c.prenom LIKE :q OR c.email LIKE :q OR c.codeClient LIKE :q OR c.intitule LIKE :q')
            ->setParameter('q', '%' . $q . '%')
            ->orderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function searchGlobal(string $q, int $limit = 15): array
    {
        $qNorm  = mb_strtolower(trim($q));
        $tokens = array_values(array_filter(explode(' ', $qNorm), fn($t) => strlen($t) >= 1));

        if (empty($tokens)) return [];

        // Recherche DB avec LOWER() LIKE (insensible à la casse)
        $qb = $this->createQueryBuilder('c');
        $parts = [];
        foreach ($tokens as $i => $token) {
            $p = 'qt' . $i;
            $parts[] = "(LOWER(c.nom) LIKE :$p OR LOWER(c.prenom) LIKE :$p OR LOWER(c.email) LIKE :$p"
                     . " OR LOWER(c.telephone) LIKE :$p OR LOWER(c.intitule) LIKE :$p"
                     . " OR c.siret LIKE :$p OR c.codeClient LIKE :$p)";
            $qb->setParameter($p, '%' . $token . '%');
        }
        $qb->andWhere(implode(' AND ', $parts));
        $dbResults = $qb->orderBy('c.nom', 'ASC')->getQuery()->getResult();

        // Recherche floue (tolérance aux fautes) via similar_text()
        $fuzzyAdded = [];
        if (count($dbResults) < $limit) {
            $all   = $this->createQueryBuilder('c')->orderBy('c.nom', 'ASC')->setMaxResults(500)->getQuery()->getResult();
            $dbIds = array_flip(array_map(fn($c) => $c->getId(), $dbResults));

            foreach ($all as $client) {
                if (isset($dbIds[$client->getId()])) continue;
                if ($this->fuzzyMatchClient($client, $tokens)) {
                    $fuzzyAdded[] = $client;
                }
            }
        }

        return array_slice(array_merge($dbResults, $fuzzyAdded), 0, $limit);
    }

    private function fuzzyMatchClient(Client $client, array $tokens): bool
    {
        $fields = array_filter([
            mb_strtolower((string)$client->getNom()),
            mb_strtolower((string)$client->getPrenom()),
            mb_strtolower((string)$client->getIntitule()),
        ]);

        foreach ($tokens as $token) {
            if (strlen($token) < 3) continue;
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

    public function findWithDossiers(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.dossiers', 'd')
            ->addSelect('d')
            ->orderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findPaginated(int $page, int $limit, ?string $q = null): array
    {
        $qb = $this->createQueryBuilder('c')->orderBy('c.nom', 'ASC');
        if ($q) {
            $qb->where('c.nom LIKE :q OR c.prenom LIKE :q OR c.email LIKE :q OR c.codeClient LIKE :q OR c.intitule LIKE :q')
               ->setParameter('q', '%' . $q . '%');
        }
        $total = (clone $qb)->select('COUNT(c.id)')->resetDQLPart('orderBy')->getQuery()->getSingleScalarResult();
        $items = $qb->setFirstResult(($page - 1) * $limit)->setMaxResults($limit)->getQuery()->getResult();
        return ['items' => $items, 'total' => (int)$total, 'page' => $page, 'limit' => $limit, 'pages' => (int)ceil($total / $limit)];
    }

    public function save(Client $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Client $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
