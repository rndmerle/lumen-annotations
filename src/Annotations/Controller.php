<?php

namespace ProAI\RouteAnnotations\Annotations;

/**
 * @Annotation
 * @Target("CLASS")
 */
final class Controller implements Annotation
{
    /**
     * @var string
     */
    public $prefix;

    /**
     * @var string
     */
    public $domain;
}