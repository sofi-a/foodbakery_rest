<?php

namespace FoodbakeryRestApi\Endpoints;

use FoodbakeryRestApi\Schemas\WithdrawalSchema;
use FoodbakeryRestApi\Utils\CollectionResponse;

class WithdrawalsEndpoint extends Endpoint {
    private $sort_keys;

    public function __construct()
    {
        parent::__construct('withdrawals', new WithdrawalSchema);
        $sort_keys = array(
            'user',
            'status',
        );
    }

    public function register_routes()
    {
        // GET /foodbakery/v1/withdrawals
        register_rest_route('foodbakery/v1', '/withdrawals', array(
            array(
                'methods'   => 'GET',
                'callback'  => array($this, 'get_items'),
            ),
            'schema'        => array($this->schema, 'get_schema'),
        ));

        // GET /foodbakery/v1/withdrawals/:id
        register_rest_route('foodbakery/v1', '/withdrawals/(?P<id>\d+)', array(
            array(
                'methods'   => 'GET',
                'callback'  => array($this, 'get_item'),
            ),
            'schema'        => array($this->schema, 'get_schema'),
        ));
    }

    public function get_items($request) {
        $this->extract_params($request);

        if($this->params['perPage'] < 1 || $this->params['page'] < 1) {
            return new \WP_Error(
                'invalid_operation',
                'perPage and page must be greater than or equal to 1',
                array(
                    'status' => 403,
                )
            );
        }

        $posts_per_page = $this->params['perPage'];
        $page = $this->params['page'];
        $offset = $posts_per_page * ($page - 1);

        $args = array(
            'posts_per_page'    => $posts_per_page,
            'offset'            => $offset,
            'post_type'         => $this->post_type,
            'post_status'       => 'publish',
        );

        $this->sort($args);

        $posts = get_posts($args);
    
        $data = array();
 
        if(empty($posts)) {
            return rest_ensure_response($data);
        }
 
        foreach($posts as $post) {
            $response = $this->prepare_items_for_response($post, $request);
            $data[] = CollectionResponse::prepare_response_for_collection($response);
        }

        return new \WP_REST_Response($data, 200, array(
            'X-Total-Count' => $this->get_total(),
        ));
    }

    public function get_item($request) {
        $withdrawal_id = (int)$request['id'];
        $withdrawal = get_post($withdrawal_id);

        if(empty($withdrawal) || $this->post_type != get_post_type($withdrawal_id)) {
            return new WP_Error(
                'invalid_operation',
                "There is no withdrawal with id: $withdrawal_id",
                array(
                    'status' => 403,
                ),
            );
        }

        $response = $this->prepare_item_for_response($withdrawal, $request);

        return rest_ensure_response($response);
    }

    public function sort(&$args) {
        if(array_key_exists('sort', $this->params)) {
            $sort = $this->params['sort'];

            if(array_key_exists('order', $this->params))
                $order = strtolower($this->params['order']);
            else
                die(json_encode(
					(new \WP_Error(
						'invalid_operation',
						'_order must be specified with _sort',
						array(
							'status' => 403,
						)
					))->errors
                ));

            if($order === 'asc') {
                $args['order'] = $order;
            }
            else if($order === 'desc') {
                $args['order'] = $order;
            }
            else {
                die(json_encode(
					(new \WP_Error(
						'invalid_operation',
						"$order cannot be used to order results",
						array(
							'status' => 403,
						)
					))->errors
                ));
            }

            if($sort === 'id') {
                $args['orderby'] = strtoupper($sort);
            }
            else if($sort === 'date' || $sort === 'modified') {
                $args['orderby'] = $sort;
            }
			else if($sort === 'amount') {
				$args['meta_key'] = $this->schema->property_map[strtolower($sort)];
				$args['orderby'] = 'meta_value_num';
			}
            else if(in_array(strtolower($sort), $this->sort_keys)) {
                $args['meta_key'] = $this->schema->property_map[strtolower($sort)];
                $args['orderby'] = 'meta_value';
            } else {
                die(json_encode(
					(new \WP_Error(
						'invalid_operation',
						"$sort cannot be used as a sorting key",
						array(
							'status' => 403,
						)
					))->errors
                ));
            }
        }
    }

    public function prepare_items_for_response($post, $request)
    {
        $schema = $this->schema->get_schema($request);

        $post_data = array();

        $post_meta = get_post_meta($post->ID);

        if(isset($schema['properties']['id'])) {
            $post_data['id'] = $post->ID;
        }

        if(isset($schema['properties']['date'])) {
            $post_data['date'] = $post->post_date;
        }

        if(isset($schema['properties']['modified'])) {
            $post_data['modified'] = $post->post_modified;
        }

        if(isset($schema['properties']['user'])) {
            $publisher_id = (int)$post_meta[$this->schema->property_map['user']][0];
            $publisher = get_post($publisher_id);
            $post_data['user'] = $publisher->post_title;
        }

        if(isset($schema['properties']['amount'])) {
            $post_data['amount'] = $post_meta[$this->schema->property_map['amount']][0];
        }

        if(isset($schema['properties']['status'])) {
            $post_data['status'] = (float)$post_meta[$this->schema->property_map['status']][0];
        }

        return $post_data;
    }

    public function prepare_item_for_response($post, $request) {
        $schema = $this->schema->get_schema($request);

        $post_data = array();

        $post_meta = get_post_meta($post->ID);

        if(isset($schema['properties']['id'])) {
            $post_data['id'] = $post->ID;
        }

        if(isset($schema['properties']['date'])) {
            $post_data['date'] = $post->post_date;
        }

        if(isset($schema['properties']['modified'])) {
            $post_data['modified'] = $post->post_modified;
        }

        if(isset($schema['properties']['user'])) {
            $publisher_id = (int)$post_meta[$this->schema->property_map['user']][0];
            $publisher = get_post($publisher_id);
            $post_data['user'] = $publisher->post_title;
        }

        if(isset($schema['properties']['amount'])) {
            $post_data['amount'] = $post_meta[$this->schema->property_map['amount']][0];
        }

        if(isset($schema['properties']['detail'])) {
            $post_data['detail'] = $post_meta[$this->schema->property_map['detail']][0];
        }

        if(isset($schema['properties']['status'])) {
            $post_data['status'] = (float)$post_meta[$this->schema->property_map['status']][0];
        }

        return rest_ensure_response($post_data);
    }
}
