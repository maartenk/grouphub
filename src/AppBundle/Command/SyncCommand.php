<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\LockHandler;

/**
 * Class SyncCommand
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
            ->addOption('type', null, InputOption::VALUE_REQUIRED, 'either users, groups or grouphub')
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

        switch ($input->getOption('type')) {
            case 'users':
                $service->syncUsers();
                break;

            case 'groups':
                $service->syncGroups();
                break;

            case 'grouphub':
                $service->syncGrouphubGroups();
                break;

            default:
                $service->sync();
        }

        $io->success('Done!');
    }
}
