<?php
/**
 * This file is part of the php-tricorder project.
 * 
 * (c) Yannick Voyer (http://github.com/yvoyer)
 */

namespace Tricorder\Scanner;

use SimpleXMLElement;
use Symfony\Component\Console\Output\OutputInterface;
use Tricorder\Formatter\MethodFormatter;
use Tricorder\Processor\ArgumentProcessor;
use Tricorder\Processor\ReturnTypeProcessor;
use Tricorder\Tag\Extractor\MethodTagExtractor;

/**
 * Class MethodScanner
 *
 * @author  Yannick Voyer (http://github.com/yvoyer)
 *
 * @package Tricorder\Scanner
 */
class MethodScanner
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var MethodTagExtractor
     */
    private $extractor;

    /**
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output    = $output;
        $this->extractor = new MethodTagExtractor();
    }

    /**
     * Scan the $method for tags to process.
     *
     * @param SimpleXMLElement $method
     */
    public function scan(SimpleXMLElement $method)
    {
        $methodTags = $this->extractor->extractTags($method);

        $tricorderTags = array_filter($methodTags, function($tag) {
                if (isset($tag['@attributes']['name']) && $tag['@attributes']['name'] == 'tricorder') {
                    return true;
                }
            });

        // Check to see if we have any parameters that we need to test
        $paramTags = array_filter($methodTags, function($tag) {
                if (isset($tag['@attributes']['name']) && $tag['@attributes']['name'] == 'param') {
                    return true;
                }
            });

        // Grab our method return information
        $returnTag = array_filter($methodTags, function($tag) {
                if (isset($tag['@attributes']['name']) && $tag['@attributes']['name'] == 'return') {
                    return true;
                }
            });

        $argumentProcessor = new ArgumentProcessor($this->output);
        $argumentProcessor->process((string)$method->name, $paramTags, $tricorderTags);

        // Process ReturnType
        $processor = new ReturnTypeProcessor($this->output);
        $processor->process((string)$method->name, $returnTag, $tricorderTags);

        $methodFormatter = new MethodFormatter($method);
        $methodFormatter->outputMessage($this->output);
    }
}
