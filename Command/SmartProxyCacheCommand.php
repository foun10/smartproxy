<?php
declare(strict_types=1);

namespace foun10\SmartProxy\Command;

use foun10\SmartProxy\Core\SmartProxy;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SmartProxyCacheCommand extends Command
{
    const COMMAND_CLEAR_HTML = 'clear-html';
    const COMMAND_CLEAR_BY_TAG = 'clear-tag';

    const POSSIBLE_COMMANDS = [
        self::COMMAND_CLEAR_HTML,
        self::COMMAND_CLEAR_BY_TAG,
    ];

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->setName('foun10:smartproxy:cache')
            ->addArgument('commandToExecute', InputArgument::REQUIRED, 'use ' . implode(' or ', self::POSSIBLE_COMMANDS))
            ->addOption('tag', null, InputOption::VALUE_OPTIONAL, 'tag to clear')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $command = (string) $input->getArgument('commandToExecute');
        $smartProxy = oxNew(SmartProxy::class);

        switch ($command) {
            case self::COMMAND_CLEAR_HTML:
                $output->writeln('Clearing HTML cache...');
                $smartProxy->clearHtmlCache();
                break;
            case self::COMMAND_CLEAR_BY_TAG:
                $tag = $input->getOption('tag');

                if (empty($tag)) {
                    $output->writeln('Option "tag" needed for clear is not set');
                    return;
                }

                $output->writeln('Clearing cache by tag...');
                $smartProxy->clearCacheByTag([$tag]);

                break;
        }

        $output->writeln('Done.');
    }
}
