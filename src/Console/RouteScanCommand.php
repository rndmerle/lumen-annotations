<?php

namespace ProAI\Annotations\Console;

use Illuminate\Console\Command;
use ProAI\Annotations\Metadata\ClassFinder;
use ProAI\Annotations\Metadata\RouteScanner;
use ProAI\Annotations\Routing\Generator;

class RouteScanCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'route:scan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan all routes with route annotations.';

    /**
     * The class finder instance.
     *
     * @var \ProAI\Annotations\Metadata\ClassFinder
     */
    protected $finder;

    /**
     * The route scanner instance.
     *
     * @var \ProAI\Annotations\Metadata\RouteScanner
     */
    protected $scanner;

    /**
     * The routes generator instance.
     *
     * @var \ProAI\Annotations\Routing\Generator
     */
    protected $generator;

    /**
     * The config of the route annotations package.
     *
     * @var array
     */
    protected $config;

    /**
     * Create a new migration install command instance.
     *
     * @param \ProAI\Annotations\Metadata\ClassFinder $finder
     * @param \ProAI\Annotations\Metadata\RouteScanner $scanner
     * @param \ProAI\Annotations\Routing\Generator $generator
     * @param array $config
     * @return void
     */
    public function __construct(ClassFinder $finder, RouteScanner $scanner, Generator $generator, $config)
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
        if (isset($this->config['routes_classes']) and is_array($this->config['routes_classes'])) {
            $classes = array_merge($classes, $this->config['routes_classes']);
        }
        if (!empty($this->config['routes_namespace'])) {
            $classes = array_merge($classes, $this->finder->getClassesFromNamespace($this->config['routes_namespace']));
        } elseif (empty($classes)) {
            // If routes_namespace is not specified and no class as already been specified (by routes_classes), then load all the classes from \App namespace
            $classes = array_merge($classes, $this->finder->getClassesFromNamespace(null));
        }

        // build metadata
        $routes = $this->scanner->scan($classes);

        // generate routes.php file for scanned routes
        $this->generator->generate($routes);

        $this->info('Routes registered successfully!');
    }
}
