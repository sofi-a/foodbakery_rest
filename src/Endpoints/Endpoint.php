<?php

namespace FoodbakeryRestApi\Endpoints;

use FoodbakeryRestApi\Schemas\Schema;

abstract class Endpoint {
    protected $schema;
    protected $post_type;
    protected $params;

    public function __construct($post_type, Schema $schema) {
        $this->post_type = $post_type;
        $this->schema = $schema;
		$this->params = array(
			'perPage'   => 20,
			'page'      => 1,
		);
        $this->register_routes();
    }

    protected function get_total() {
        $posts = get_posts(array(
            'posts_per_page'    => -1,
            'post_type'         => $this->post_type,
            'post_status'       => 'publish',
        ));

        return count($posts);
    }

    protected function extract_params($request) {
        $params = $request->get_params();

        if(array_key_exists('perPage', $params)) {
			if(is_numeric($params['perPage']))
				$this->params['perPage'] = (int)$params['perPage'];
		}
		if(array_key_exists('page', $params)) {
			if(is_numeric($params['page']))
				$this->params['page'] = (int)$params['page'];
		}
        if(array_key_exists('sort', $params)) $this->params['sort'] = $params['sort'];
        if(array_key_exists('order', $params)) $this->params['order'] = $params['order'];
    }

    abstract public function register_routes();
    abstract public function prepare_items_for_response($post, $request);
    abstract public function prepare_item_for_response($post, $request);
    abstract public function get_items($request);
    abstract public function get_item($request);
    abstract public function sort(&$args);
}
