<?php

namespace App\Command;

use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:regenerate-client-codes',
    description: 'Régénère tous les codes clients selon le format C-{année}-{CABINET_CODE}-{rand}'
)]
class RegenerateClientCodesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ClientRepository $clientRepo,
        #[Autowire(env: 'CABINET_CODE')] private readonly string $cabinetCode = '001',
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $clients = $this->clientRepo->findAll();
        $assignedCodes = [];
        $count = 0;

        foreach ($clients as $client) {
            $year = $client->getCreatedAt()->format('Y');

            do {
                $code = 'C-' . $year . '-' . $this->cabinetCode . '-' . str_pad((string)rand(1, 99999), 5, '0', STR_PAD_LEFT);
            } while (
                isset($assignedCodes[$code]) ||
                $this->clientRepo->findOneBy(['codeClient' => $code]) !== null
            );

            $assignedCodes[$code] = true;
            $client->setCodeClient($code);
            $count++;
        }

        $this->em->flush();
        $io->success(sprintf('%d codes clients régénérés avec CABINET_CODE=%s.', $count, $this->cabinetCode));

        return Command::SUCCESS;
    }
}
