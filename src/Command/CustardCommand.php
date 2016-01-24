<?php

namespace Rhubarb\Custard\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

abstract class CustardCommand extends Command
{
    /** @var InputInterface */
    protected $input;
    /** @var OutputInterface */
    protected $output;

    /** @var QuestionHelper */
    protected $questionHelper;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
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

    /**
     * @param string|Question $question Question text or a Question object
     * @param null|string $default The default answer
     * @param bool|\Closure $requireAnswer True for not-empty validation, or a closure for custom validation
     * @return string User's answer
     */
    protected function askQuestion($question, $default = null, $requireAnswer = true)
    {
        if (!$this->questionHelper) {
            $this->questionHelper = $this->getHelper("question");
        }

        if (!($question instanceof Question)) {
            if (strpos($question, '<question>') === false) {
                $question = '<question>' . $question . '</question> ';
            }
            if ($default !== null) {
                $question .= "($default) ";
            }
            $question = new Question($question, $default);
        }

        if (is_callable($requireAnswer)) {
            $question->setValidator($requireAnswer);
        } elseif ($requireAnswer) {
            $question->setValidator(function ($answer) {
                if (trim($answer) == '') {
                    throw new \Exception(
                        'You must provide an answer to this question'
                    );
                }
                return $answer;
            });
        }

        return $this->questionHelper->ask($this->input, $this->output, $question);
    }

    /**
     * @param string $question Question text
     * @param array $choices An array of choices which are acceptable answers
     * @param null|int $default The array index in $choices of the default answer
     * @param bool|\Closure $requireAnswer True for not-empty validation, or a closure for custom validation
     * @return string User's answer
     */
    protected function askChoiceQuestion($question, array $choices, $default = null, $requireAnswer = true)
    {
        if (!($question instanceof Question)) {
            if (strpos($question, '<question>') === false) {
                $question = '<question>' . $question . '</question> ';
            }
            if ($default !== null) {
                $question .= "($choices[$default]) ";
            }
            $question = new ChoiceQuestion($question, $choices, $default);
        }

        if ($requireAnswer && !is_callable($requireAnswer)) {
            $requireAnswer = function ($answer) use ($choices) {
                if (trim($answer) == '') {
                    throw new \Exception(
                        'You must provide an answer to this question'
                    );
                }

                if (is_numeric($answer)) {
                    if (!isset($choices[$answer])) {
                        throw new \Exception("\"$answer\" is not a supported option index");
                    }
                    $answer = $choices[$answer];
                } elseif (!in_array($answer, $choices)) {
                    throw new \Exception("\"$answer\" is not a supported option");
                }

                return $answer;
            };
        }

        return $this->askQuestion($question, null, $requireAnswer);
    }
}
