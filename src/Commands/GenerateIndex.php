<?php

namespace Commands;

use Services\IndexFile;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateIndex extends Command
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('generate-index')
            ->addArgument('fileName', InputArgument::REQUIRED, 'File to index')
            ->setDescription('Generate index for file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            IndexFile::buildIndex($input->getArgument('fileName'));
        } catch (\Exception $exception) {
            $output->write("Exception caught: " . $exception->getMessage() . "\n");
        }
    }
}