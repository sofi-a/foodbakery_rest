<?php

namespace FoodbakeryRestApi\Schemas;

abstract class Schema {
    public $property_map;

    public function __construct($property_map)
    {
        $this->property_map = $property_map;
    }

    abstract public function get_schema($request);
}