<?php

namespace ProAI\Annotations\Console;

use Illuminate\Console\Command;
use ProAI\Annotations\Metadata\ClassFinder;
use ProAI\Annotations\Metadata\EventScanner;
use ProAI\Annotations\Events\Generator;

class EventScanCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'event:scan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan all events with event annotations.';

    /**
     * The class finder instance.
     *
     * @var \ProAI\Annotations\Metadata\ClassFinder
     */
    protected $finder;

    /**
     * The event scanner instance.
     *
     * @var \ProAI\Annotations\Metadata\EventScanner
     */
    protected $scanner;

    /**
     * The events generator instance.
     *
     * @var \ProAI\Annotations\Events\Generator
     */
    protected $generator;

    /**
     * The config of the event annotations package.
     *
     * @var array
     */
    protected $config;

    /**
     * Create a new migration install command instance.
     *
     * @param \ProAI\Annotations\Metadata\ClassFinder $finder
     * @param \ProAI\Annotations\Metadata\EventScanner $scanner
     * @param \ProAI\Annotations\Events\Generator $generator
     * @param array $config
     * @return void
     */
    public function __construct(ClassFinder $finder, EventScanner $scanner, Generator $generator, $config)
    {
        parent::__construct();

        $this->finder = $finder;
        $this->scanner = $scanner;
        $this->generator = $generator;
        $this->config = $config;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        // get classes
        $classes = [];
        if (isset($this->config['events_classes']) and is_array($this->config['events_classes'])) {
            $classes = array_merge($classes, $this->config['events_classes']);
        }
        if (!empty($this->config['events_namespace'])) {
            $classes = array_merge($classes, $this->finder->getClassesFromNamespace($this->config['events_namespace']));
        } elseif (empty($classes)) {
            // If events_namespace is not specified and no class as already been specified (by events_classes), then load all the classes from \App namespace
            $classes = array_merge($classes, $this->finder->getClassesFromNamespace(null));
        }

        // build metadata
        $events = $this->scanner->scan($classes);

        // generate events.php file for scanned events
        $this->generator->generate($events);

        $this->info('Events registered successfully!');
    }
}
