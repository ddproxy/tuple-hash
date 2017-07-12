<?php

namespace Commands;

use Services\IndexFile;
use SplFileObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Difference extends Command
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('difference')
            ->addArgument('master-file', InputArgument::REQUIRED, 'Master file')
            ->addArgument('difference-files', InputArgument::REQUIRED, 'Files to generate difference against')
            ->setDescription('Generate index for file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $masterFile = $input->getArgument('master-file');
            $differenceFiles = explode(',', $input->getArgument('difference-files'));

            foreach ($differenceFiles as $file) {
                if (!file_exists($file . '.filter') || !IndexFile::checkIntegrity($file)) {
                    IndexFile::buildIndex($file);
                }
            }

            $blooms = [];
            foreach ($differenceFiles as $file) {
                $bloomFile = new \SplFileObject($file . '.filter', 'r');
                $blooms[$file] = unserialize($bloomFile->fread($bloomFile->getSize()));
            }

            $file = new \SplFileObject($masterFile, 'r');
            $file->setFlags(SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY |
                SplFileObject::DROP_NEW_LINE);

            $response = [];
            foreach ($file as $line) {
                $pass = true;
                foreach ($blooms as $filter) {
                    if ($filter->check(strtolower($line))) {
                        $pass = false;
                    }
                }
                if ($pass) {
                    $response[] = $line;
                }
            }
            $output->writeln($response);
        } catch (\Exception $exception) {
            $output->write("Exception caught: " . $exception->getMessage() . "\n");
        }
    }

    public function getLines(SplFileObject $file)
    {
        $file->seek(PHP_INT_MAX);
        $lines = $file->key() + 1;
        $file->rewind();
        return $lines;
    }
}