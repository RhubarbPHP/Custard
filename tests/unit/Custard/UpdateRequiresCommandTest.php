<?php

namespace Rhubarb\Crown\Tests\Custard;

use Rhubarb\Custard\Command\UpdateRequiresCommand;
use Rhubarb\Crown\Tests\Fixtures\TestCases\RhubarbTestCase;

class UpdateRequiresCommandTest extends RhubarbTestCase
{
    public function testRelativePath()
    {
        $from = "/a/b/c";
        $to = "/a/b/c/d";

        $this->assertEquals("__DIR__ . \"/d\"", UpdateRequiresCommand::getRelativePath($from, $to));

        $from = "/a/b/c/d";
        $to = "/a/b/c/d";

        $this->assertEquals("__DIR__", UpdateRequiresCommand::getRelativePath($from, $to));

        $from = "/a/b/c";
        $to = "/a/b/c/e";

        $this->assertEquals("__DIR__ . \"/e\"", UpdateRequiresCommand::getRelativePath($from, $to));

        $from = "/a/b/c/";
        $to = "/a/b/c/e";

        $this->assertEquals("__DIR__ . \"/e\"", UpdateRequiresCommand::getRelativePath($from, $to));

        $from = "/a/b/c/";
        $to = "/a/b/c/e/";

        $this->assertEquals("__DIR__ . \"/e\"", UpdateRequiresCommand::getRelativePath($from, $to));

        $from = "/a/b/c/";
        $to = "/a/b";

        $this->assertEquals("__DIR__ . \"/..\"", UpdateRequiresCommand::getRelativePath($from, $to));

        $from = "/a/b/c/";
        $to = "/a/b/f";

        $this->assertEquals("__DIR__ . \"/../f\"", UpdateRequiresCommand::getRelativePath($from, $to));

        $from = "/a/b/c/";
        $to = "/g/h/i";

        $this->assertEquals("\"/g/h/i\"", UpdateRequiresCommand::getRelativePath($from, $to));


        $from = "/a/b/c/e";
        $to = "/a/b/d/e";

        $this->assertEquals("__DIR__ . \"/../../d/e\"", UpdateRequiresCommand::getRelativePath($from, $to));

        $from = "\\a\\b\\c";
        $to = "\\a\\b\\d";

        $this->assertEquals("__DIR__ . \"/../d\"", UpdateRequiresCommand::getRelativePath($from, $to));

        $from = "C:/www/Rhubarb/src/LoginProviders/Exceptions";
        $to = "C:/www/Rhubarb/src/Exceptions";

        $this->assertEquals("__DIR__ . \"/../../Exceptions\"", UpdateRequiresCommand::getRelativePath($from, $to));

    }

}

