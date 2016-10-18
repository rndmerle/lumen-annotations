<?php

namespace ProAI\Annotations;

use Illuminate\Support\ServiceProvider;

class AnnotationsServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app['config']['annotations.auto_scan']) {
            $this->scanRoutes();

            $this->scanEvents();
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConfig();

        $this->app->register('ProAI\Annotations\Providers\CommandsServiceProvider');
    }

    /**
     * Register the config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->app->configure('annotations');
    }

    /**
     * Auto update routes.
     *
     * @return void
     */
    protected function scanRoutes()
    {
        $app = $this->app;

        // get classes
        // get classes
        $classes = [];
        if (isset($this->config['routes_classes']) and is_array($this->config['routes_classes'])) {
            $classes = array_merge($classes, $this->config['routes_classes']);
        }
        if (!empty($this->config['routes_namespace'])) {
            $classes = array_merge($classes, $app['annotations.classfinder']->getClassesFromNamespace($this->config['routes_namespace']));
        } elseif (empty($classes)) {
            // If routes_namespace is not specified and no class as already been specified (by routes_classes), then load all the classes from \App namespace
            $classes = array_merge($classes, $app['annotations.classfinder']->getClassesFromNamespace(null));
        }

        // build metadata
        $routes = $app['annotations.route.scanner']->scan($classes);

        // generate routes.php file for scanned routes
        $app['annotations.route.generator']->generate($routes);
    }

    /**
     * Auto update event bindings.
     *
     * @return void
     */
    protected function scanEvents()
    {
        $app = $this->app;

        // get classes
        $classes = [];
        if (isset($this->config['events_classes']) and is_array($this->config['events_classes'])) {
            $classes = array_merge($classes, $this->config['events_classes']);
        }
        if (!empty($this->config['events_namespace'])) {
            $classes = array_merge($classes, $app['annotations.classfinder']->getClassesFromNamespace($this->config['events_namespace']));
        } elseif (empty($classes)) {
            // If events_namespace is not specified and no class as already been specified (by events_classes), then load all the classes from \App namespace
            $classes = array_merge($classes, $app['annotations.classfinder']->getClassesFromNamespace(null));
        }

        // build metadata
        $events = $app['annotations.event.scanner']->scan($classes);

        // generate events.php file for scanned routes
        $app['annotations.event.generator']->generate($events);
    }
}
