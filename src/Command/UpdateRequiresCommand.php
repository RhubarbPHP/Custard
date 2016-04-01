<?php

namespace Rhubarb\Custard\Command;

// Rhubarb auto-generated includes
require_once __DIR__ . "/CustardCommand.php";
// End of Rhubarb auto-generated includes

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateRequiresCommand extends CustardCommand
{
    const WHITESPACE_OFFSET = 2;

    protected function configure()
    {
        $this->setName('rhubarb:update-requires')
            ->setDescription('Updates the requires for each file within the project')
            ->addArgument('dir', InputArgument::REQUIRED, 'The directory to perform the update within');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $dir = $input->getArgument("dir");
        self::scanDirectory($dir);
    }

    public static function scanDirectory($dir)
    {
        try {
            $items = scandir($dir);
            if (count($items) > 2) {
                // If there are more than two items in the directory (more than 'current directory - .' and 'previous directory - ..' )
                foreach ($items as $item) {
                    if (preg_match("/.php$/", $item)) {
                        self::updateIncludes($dir . "/" . $item);
                    } else {
                        if (!is_numeric(strpos($item, "."))) {
                            self::scanDirectory($dir . "/" . $item);
                        }
                    }
                }
            }
        } catch (\Exception $ex) {
            echo $dir . " wasn't a directory";
        }
    }

    public static function updateIncludes($file)
    {
        $fileContents = file_get_contents($file);
        $tokens = token_get_all($fileContents);

        $i = 0;

        $namespace = self::getNamespace($fileContents);

        foreach ($tokens as $token) {
            if (is_array($token)) {
                if (token_name($token[0]) == "T_CLASS") {
                    // The class name will be two tokens after the class token that we found
                    $className = $tokens[$i + self::WHITESPACE_OFFSET][1];

                    try {
                        if ($namespace == "") {
                            $reflectionClass = new \ReflectionClass($className);
                        } else {
                            $reflectionClass = new \ReflectionClass($namespace . "\\" . $className);
                        }

                        $filePath = $reflectionClass->getFileName();


                        $allIncludePaths = [];
                        $x = 0;

                        // Find the parent
                        $parent = $reflectionClass->getParentClass();
                        if ($parent != null) {
                            $parentPath = $parent->getFileName();
                            if ($parentPath) {
                                $allIncludePaths[$x] = $parent->getFileName();
                            }
                        }

                        // Find the interfaces
                        $interfaceIncludes = [];
                        $interfaces = $reflectionClass->getInterfaces();
                        if ($interfaces != null) {
                            $interfaces = array_values($interfaces);
                            $interfaceIncludes = self::createIncludesGroup($interfaces);
                        }

                        // Find the traits
                        $traitIncludes = [];
                        $traits = $reflectionClass->getTraits();
                        if ($traits != null) {
                            $traits = array_values($traits);
                            $traitIncludes = self::createIncludesGroup($traits);
                        }

                        foreach ($interfaceIncludes as $ii) {
                            $allIncludePaths[$x] = $ii;
                            $x++;
                        }

                        foreach ($traitIncludes as $ti) {
                            $allIncludePaths[$x] = $ti;
                            $x++;
                        }

                        if (count($allIncludePaths) == 0) {
                            // This class had nothing to include, no reason to check remaining tokens.
                            break;
                        }

                        $allIncludes = [];
                        $y = 0;

                        foreach ($allIncludePaths as $includePath) {
                            $include = self::createFullRelativeFilePath($filePath, $includePath);
                            if ($include != "") {
                                $allIncludes[$i] = $include;
                                $y++;
                            }
                        }

                        self::addIncludes($reflectionClass, $allIncludes);

                        // This class has been done, no reason to check remaining tokens.
                        break;

                    } catch (\ReflectionException $ex) {
                        echo "Could not find class " . $className . "\n";
                    }
                }
            }
            $i++;
        }
    }

    /**
     * Gets the file and adds any required includes to it. Then writes the file if changes were made.
     *
     * @param \ReflectionClass $reflectionClass The reflection class
     * @param $includes string[] File paths to be required in the file
     */
    public static function addIncludes(\ReflectionClass $reflectionClass, $includes)
    {
        $fileContents = file_get_contents($reflectionClass->getFileName());

        // Remove the last auto-generated block
        $fileContents = preg_replace("%// Rhubarb auto-generated includes.+// End of Rhubarb auto-generated includes(\r\n|\r|\n)+%ms",
            "", $fileContents);

        // Build up our new block
        $requireStatements = "// Rhubarb auto-generated includes\r\n";

        array_walk($includes, function ($path) use (&$requireStatements, $fileContents) {
            if (!strpos($fileContents, "require_once " . $path . ";")) {
                //If the include has been added manually
                $requireStatements .= "require_once " . $path . ";\r\n";
            }
        });

        $requireStatements .= "// End of Rhubarb auto-generated includes";

        // If the block has no elements (Due to them being added manually)
        if ($requireStatements == "// Rhubarb auto-generated includes\r\n// End of Rhubarb auto-generated includes") {
            //Leave (we have nothing to add)
            return;
        }

        if (preg_match("/^namespace/m", $fileContents)) {
            // Find the namespace and add our statements below it
            $fileContents = preg_replace("/^namespace(.+);/m", "\\0\r\n\r\n$requireStatements\r\n", $fileContents);
        } else {
            // If there was no namespace, add it below the php block
            $fileContents = preg_replace("/<?php.+/", "\\0\r\n\r\n$requireStatements\r\n", $fileContents);
        }

        file_put_contents($reflectionClass->getFileName(), $fileContents);
    }

    /**
     * Scan the files tokens for the namespace token to return the namespace.
     *
     * @param $fileContents string The contents of the file to get the namespace from
     * @return string The namespace
     */
    public static function getNamespace($fileContents)
    {
        $tokens = token_get_all($fileContents);
        $namespace = "";
        $i = 0;
        foreach ($tokens as $token) {
            if (is_array($token)) {
                if (token_name($token[0]) == "T_NAMESPACE") {
                    for ($j = $i + self::WHITESPACE_OFFSET; $j < count($tokens); $j++) {
                        if ($tokens[$j] == ";") {
                            break;
                        }
                        $namespace = $namespace . $tokens[$j][1];
                    }
                    break;
                }
            }
            $i++;
        }
        return $namespace;
    }

    /**
     * @param $fromDir String The from directory
     * @param $toDir String The to directory
     * @return string The relative directory
     */
    public static function GetRelativePath($fromDir, $toDir)
    {
        $fromDir = rtrim($fromDir, "/");
        $toDir = rtrim($toDir, "/");

        $fromDir = str_replace("\\", "/", $fromDir);
        $toDir = str_replace("\\", "/", $toDir);

        if ($fromDir == $toDir) {
            // Where they match exactly
            return "__DIR__";
        } else {
            if (strpos($toDir, $fromDir) === 0) {
                // Where $to is a child or grandchild folder
                return str_replace($fromDir, "__DIR__ . \"", $toDir) . "\"";
            } else {
                // Where $to is in a parent folder
                $parents = explode("/", ltrim($fromDir, "/"));
                $parentCount = 0;

                for ($x = count($parents) - 1; $x > 0; $x--) {
                    $parentCount++;
                    $testPath = implode("/", array_slice($parents, 0, $x));
                    if (strpos($toDir, $testPath) === 1 || strpos($toDir, $testPath) === 0) {
                        return "__DIR__ . \"" . str_repeat("/..", $parentCount) . str_replace($testPath, "",
                            ltrim($toDir, "/")) . "\"";
                    }
                }
            }
        }

        return "\"" . $toDir . "\"";
    }

    /**
     * @param $currentFilePath string whole file path of the current file
     * @param $includeFilePath string whole file path of the file needing to be included
     * @return string The generated full file path
     */
    public static function createFullRelativeFilePath($currentFilePath, $includeFilePath)
    {
        $relativePath = self::getRelativePath(dirname($currentFilePath), dirname($includeFilePath));
        $fileName = basename($includeFilePath);
        if ($fileName != "") {
            if ($relativePath == "__DIR__") {
                $relativePath .= " . \"/" . $fileName . "\"";
            } else {
                $relativePath = substr($relativePath, 0, -1) . "/" . $fileName . "\"";
            }
        }
        return $relativePath;
    }

    /**
     * @param $reflectionClassArray \ReflectionClass[] Array of ReflectionClass objects
     * @return array String array of FileNames
     */
    public static function createIncludesGroup($reflectionClassArray)
    {
        $i = 0;
        $includes = [];
        foreach ($reflectionClassArray as $reflectionClass) {
            $location = $reflectionClass->getFileName();
            if ($location) {
                $includes[$i] = $location;
            }
            $i++;
        }
        return $includes;
    }
}