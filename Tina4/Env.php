<?php

/**
 * Tina4 - This is not a 4ramework.
 * Copy-right 2007 - current Tina4
 * License: MIT https://opensource.org/licenses/MIT
 */

namespace Tina4;

/**
 * Reads a .env file or .env.{environment} file for settings that should not be committed up with the repository
 * @package Tina4
 */
class Env
{
    /**
     * Env constructor.
     * @param string|null $forceEnvironment
     */
    public function __construct(?string $forceEnvironment = "")
    {
        if (!empty(getenv("ENVIRONMENT"))) {
            $environment = getenv("ENVIRONMENT");
        }

        if (empty($environment)) {
            $environment = $forceEnvironment;
        }

        $this->readParams($environment);
    }


    private function parseLine($line): void
    {
        if ($line[0] === "#" || empty($line) || ($line[0] === "[" && $line[strlen($line) - 1] === "]")) {
            return;
        }
        $variables = explode("=", $line, 2);
        if (isset($variables[0], $variables[1]) && !defined(trim($variables[0]))) {
            Debug::message("Defining {$variables[0]} = $variables[1]", TINA4_LOG_DEBUG);
            $variable = trim($variables[0]);
            if ($variables[1][0] === "[" || $variables[1][0] === "\"")
            {
                eval("\${$variable} = {$variables[1]};");
            } else {
                extract([$variable => $variables[1]], EXTR_OVERWRITE);
            }

            define(trim($variables[0]), ${$variable});

        }
    }

    /**
     * The readEnvParams reads the environment variables from the .env.{ENVIRONMENT} file
     * @param string|null $environment
     * @tests tina4
     *   assert ("test") === null,"Parsing the environment"
     *   assert file_exists(".env.test") === true,"File does not exist .env.test"
     */
    final public function readParams(?string $environment): void
    {
        if (!defined("TINA4_DOCUMENT_ROOT")) {
            define("TINA4_DOCUMENT_ROOT", "./");
        }
        $fileName = TINA4_DOCUMENT_ROOT . ".env";

        if (!empty($environment)) {
            $fileName .= ".{$environment}";
        }

        if (file_exists($fileName)) {
            Debug::message("Parsing {$fileName}", TINA4_LOG_DEBUG);
            $fileContents = file_get_contents($fileName);
            if (strpos($fileContents, "\r")) {
                $fileContents = explode("\r\n", $fileContents);
            } else {
                $fileContents = explode("\n", $fileContents);
            }

            foreach ($fileContents as $id => $line) {
                $this->parseLine($line);
            }
        } else {
            Debug::message("Created an ENV file for you {$fileName}");
            file_put_contents($fileName, "[Project Settings]\nVERSION=1.0.0\nTINA4_DEBUG=true\nTINA4_DEBUG_LEVEL=[TINA4_LOG_ALL]\n[Open API]\nSWAGGER_TITLE=Tina4 Project\nSWAGGER_DESCRIPTION=Edit your .env file to change this description\nSWAGGER_VERSION=1.0.0");
        }
    }
}
