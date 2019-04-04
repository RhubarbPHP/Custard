<?php

namespace Rhubarb\Custard\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class LockedCustardCommand extends CustardCommand
{
    protected $gotLock = false;

    protected static $isDebugMode = false;

    public function run(InputInterface $input, OutputInterface $output)
    {
        $this->lock($input, $output);
        try {
            parent::run($input, $output);
        } finally {
            $this->release();
        }
    }

    /**
     * @return string Must be a single character e.g. 'a'
     */
    protected function getLockFile():string {
        if (!is_dir(TEMP_DIR)) {
            mkdir(TEMP_DIR, 0777, true);
        }

        return TEMP_DIR . md5($this->getName()) . '.lock';
    }

    protected function getLockTimeoutMins(): int {
        return 5;
    }

    protected function lock(InputInterface $input, OutputInterface $output) {
        if ($this::$isDebugMode) {
            return;
        }

        $lockFile = $this->getLockFile();
        $timeout = time() - ($this->getLockTimeoutMins() * 60);
        $fileModified = file_exists($lockFile) ? filemtime($lockFile) : 0;

        if ($fileModified > $timeout) {
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->write('Command already running. If you are debugging, add \'protected static $isDebugMode = true;\' to the command.', true);
            }

            throw new \Exception('Command already running.');
        }

        if (!file_put_contents($lockFile, $this->getName() . ' ' . time())) {
            throw new \Exception("Command failed to lock. There may be a permissions issue creating lock file '$lockFile'");
        }

        $this->gotLock = true;
    }

    protected function release() {
        if (!$this->gotLock) {
            return;
        }

        $lockFile = $this->getLockFile();
        try {
            if(file_exists($lockFile)) {
                unlink($lockFile);
            }
        } catch (\Exception $e){}
    }
}