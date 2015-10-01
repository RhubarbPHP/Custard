<?php

namespace Rhubarb\Custard\Command;

use Rhubarb\Stem\Exceptions\SchemaNotFoundException;
use Rhubarb\Stem\Exceptions\SchemaRegistrationException;
use Rhubarb\Stem\Schema\SolutionSchema;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DocumentModelsCommand extends Command
{
    protected function configure()
    {
        $this->setName('custard:document-models')
            ->setDescription('Generate phpDoc comments for Rhubarb Stem models, describing their fields and relationships')
            ->addArgument('schema', InputArgument::REQUIRED, 'The name of the schema to scan models in');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $schemaName = $input->getArgument('schema');

        try {
            SolutionSchema::getSchema($schemaName);
        } catch (SchemaNotFoundException $ex) {
            $output->writeln("Couldn't find schema named '$schemaName'");
            return;
        } catch (SchemaRegistrationException $ex) {
            $output->writeln("Schema registered as '$schemaName' is not a SolutionSchema");
            return;
        }

        $output->writeln("Yeo ho ho");
    }
}