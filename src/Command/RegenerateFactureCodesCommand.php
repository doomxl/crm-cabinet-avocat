<?php

namespace App\Command;

use App\Repository\FactureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:regenerate-facture-codes',
    description: 'Régénère tous les numéros de facture selon le format F-{année}-{CABINET_CODE}-{rand}'
)]
class RegenerateFactureCodesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly FactureRepository $factureRepo,
        #[Autowire(env: 'CABINET_CODE')] private readonly string $cabinetCode = '001',
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $factures = $this->factureRepo->findAll();
        $assignedCodes = [];
        $count = 0;

        foreach ($factures as $facture) {
            $year = $facture->getDateEmission()->format('Y');

            do {
                $numero = 'F-' . $year . '-' . $this->cabinetCode . '-' . str_pad((string)rand(1, 99999), 5, '0', STR_PAD_LEFT);
            } while (
                isset($assignedCodes[$numero]) ||
                $this->factureRepo->findOneBy(['numero' => $numero]) !== null
            );

            $assignedCodes[$numero] = true;
            $facture->setNumero($numero);
            $count++;
        }

        $this->em->flush();
        $io->success(sprintf('%d numéros de facture régénérés avec CABINET_CODE=%s.', $count, $this->cabinetCode));

        return Command::SUCCESS;
    }
}
