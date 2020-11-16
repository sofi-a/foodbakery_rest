<?php

namespace FoodbakeryRestApi\Schemas;

abstract class Schema {
    abstract public function get_schema($request);
}