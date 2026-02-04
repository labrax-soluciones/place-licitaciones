<?php

namespace App\Command;

use App\Service\PlaceAtomParser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:sync-place',
    description: 'Sincroniza las licitaciones desde el feed ATOM de PLACE',
)]
class SyncPlaceCommand extends Command
{
    public function __construct(
        private PlaceAtomParser $parser
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Límite de licitaciones a procesar')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Sincronización con PLACE');
        $io->info('Descargando feed ATOM...');

        try {
            $stats = $this->parser->sync();

            $io->success('Sincronización completada');
            $io->table(
                ['Métrica', 'Valor'],
                [
                    ['Total procesadas', $stats['total']],
                    ['Nuevas', $stats['nuevas']],
                    ['Actualizadas', $stats['actualizadas']],
                    ['Errores', $stats['errores']],
                ]
            );

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Error durante la sincronización: ' . $e->getMessage());
            if ($output->isVerbose()) {
                $io->text($e->getTraceAsString());
                if ($e->getPrevious()) {
                    $io->error('Caused by: ' . $e->getPrevious()->getMessage());
                    $io->text($e->getPrevious()->getTraceAsString());
                }
            }
            return Command::FAILURE;
        }
    }
}
