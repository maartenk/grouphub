<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\LockHandler;

/**
 * Class SyncCommand
 *
 * @todo: split into several separate commands
 */
class SyncCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('grouphub:sync')
            ->setDescription('Sync users/groups');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $lockHandler = new LockHandler('app:sync.lock');
        if (!$lockHandler->lock()) {
            $io->warning('Sync process already running');
            return;
        }

        $service = $this->getContainer()->get('app.sync');
        $service->sync();

        $io->success('Done!');
    }
}
