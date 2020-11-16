<?php

namespace FoodbakeryRestApi\Endpoints;

use FoodbakeryRestApi\Schemas\Schema;

abstract class Endpoint {
    protected $schema;

    public function __construct(Schema $schema) {
        $this->schema = $schema;
        $this->register_routes();
    }

    abstract public function register_routes();
    abstract public function prepare_items_for_response($post, $request);
    abstract public function prepare_item_for_response($post, $request);
    abstract public function get_items($request);
    abstract public function get_item($request);
}
