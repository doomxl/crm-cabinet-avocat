<?php

namespace App\Command;

use App\Repository\DossierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:regenerate-dossier-codes',
    description: 'Génère les numéros manquants de dossier selon le format D-{année}-{CABINET_CODE}-{rand}'
)]
class RegenerateDossierCodesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly DossierRepository $dossierRepo,
        #[Autowire(env: 'CABINET_CODE')] private readonly string $cabinetCode = '001',
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dossiers = $this->dossierRepo->findBy(['numeroDossier' => null]);
        $assignedCodes = [];
        $count = 0;

        foreach ($dossiers as $dossier) {
            $year = $dossier->getCreatedAt()->format('Y');

            do {
                $numero = 'D-' . $year . '-' . $this->cabinetCode . '-' . str_pad((string)rand(1, 99999), 5, '0', STR_PAD_LEFT);
            } while (
                isset($assignedCodes[$numero]) ||
                $this->dossierRepo->findOneBy(['numeroDossier' => $numero]) !== null
            );

            $assignedCodes[$numero] = true;
            $dossier->setNumeroDossier($numero);
            $count++;
        }

        $this->em->flush();
        $io->success(sprintf('%d numéros de dossier générés avec CABINET_CODE=%s.', $count, $this->cabinetCode));

        return Command::SUCCESS;
    }
}
