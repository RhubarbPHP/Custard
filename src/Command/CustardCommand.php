<?php

namespace Rhubarb\Custard\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class CustardCommand extends Command
{
    /** @var OutputInterface */
    protected $output;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
    }

    protected function writeNormal($message, $newLineAfter = false)
    {
        if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL) {
            $this->output->write($message, $newLineAfter);
        }
    }

    protected function writeVerbose($message, $newLineAfter = false)
    {
        if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $this->output->write($message, $newLineAfter);
        }
    }

    protected function writeVeryVerbose($message, $newLineAfter = false)
    {
        if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
            $this->output->write($message, $newLineAfter);
        }
    }

    protected function writeDebug($message, $newLineAfter = false)
    {
        if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG) {
            $this->output->write($message, $newLineAfter);
        }
    }
}