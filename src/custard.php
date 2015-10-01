<?php

namespace Rhubarb\Custard;

// Change the working directory to the top level project folder.
use Rhubarb\Crown\Exceptions\Handlers\ExceptionHandler;
use Rhubarb\Custard\Command\DocumentModelsCommand;
use Symfony\Component\Console\Application;

const CUSTARD_VERSION = "0.1";
const CUSTARD_NAME = "Custard";

chdir(__DIR__ . "/../../../");

// Initiate our bootstrap script to boot all libraries required.
require_once "boot.php";

// Disable exception trapping as there will be no valid URL handler able to return a sensible
// interpretation of the exception details. CLI scripts are never seen publicly so it is more
// useful to have the real exception text and isn't a security risk.
ExceptionHandler::DisableExceptionTrapping();

$console = new Application(CUSTARD_NAME, CUSTARD_VERSION);

$console->addCommands([
    new DocumentModelsCommand()
]);

$console->run();