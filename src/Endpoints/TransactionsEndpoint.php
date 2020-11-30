<?php

namespace FoodbakeryRestApi\Endpoints;

use FoodbakeryRestApi\Schemas\TransactionSchema;
use FoodbakeryRestApi\Utils\CollectionResponse;

class TransactionsEndpoint extends Endpoint {
    private $sort_keys;

    public function __construct()
    {
        parent::__construct('foodbakery-trans', new TransactionSchema);
        $this->sort_keys = array(
            'order_id',
            'order_type',
            'payment_gateway',
            'payment_status',
            'currency',
            'restaurant',
            'restaurant_owner',
        );
    }

    public function register_routes() {
        // GET /foodbakery/v1/trans
        register_rest_route('foodbakery/v1', '/transactions', array(
            array(
                'methods'   => 'GET',
                'callback'  => array($this, 'get_items'),
            ),
            'schema'        => array($this->schema, 'get_schema'),
        ));

        // GET /foodbakery/v1/trans/:id
        register_rest_route('foodbakery/v1', '/transactions/(?P<id>\d+)', array(
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
        $trans_id = (int)$request['id'];
        $transaction = get_post($trans_id);

        if(empty($transaction) || $this->post_type != get_post_type($trans_id)) {
            return new \WP_Error(
                'invalid_operation',
                "There is no transaction with id: $trans_id",
                array(
                    'status' => 403,
                ),
            );
        }

        $response = $this->prepare_item_for_response($transaction, $request);

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
            else if($sort === 'buyer') {
                $args['orderby'] = 'author';
            }
			else if($sort === 'total') {
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

    public function prepare_items_for_response($post, $request) {
        $schema = $this->schema->get_schema($request);

        $post_data = array();

        $post_meta = get_post_meta($post->ID);

        $order_id = (int)$post_meta[$this->schema->property_map['order_id']][0];

        $user = get_user_by('id', $post->post_author);
    
        if(isset($schema['properties']['id'])) {
            $post_data['id'] = $post->ID;
        }
        
        if(isset($schema['properties']['date'])) {
            $post_data['date'] = $post->post_date;
        }
    
        if(isset($schema['properties']['modified'])) {
            $post_data['modified'] = $post->post_modified;
        }

        if(isset($schema['properties']['order_id'])) {
            $post_data['order_id'] = $order_id;
        }

        if(isset($schema['properties']['order_type'])) {
            $post_data['order_type'] = $post_meta[$this->schema->property_map['order_type']][0];
        }

        if(isset($schema['properties']['payment_gateway'])) {
            $post_data['payment_gateway'] = $post_meta[$this->schema->property_map['payment_gateway']][0];
        }

        if(isset($schema['properties']['total'])) {
            $post_data['total'] = (float)$post_meta[$this->schema->property_map['total']][0];
        }

        if(isset($schema['properties']['currency'])) {
            $post_data['currency'] = $post_meta[$this->schema->property_map['currency']][0];
        }

        if(isset($schema['properties']['commission_charged'])) {
            $post_data['commission_charged'] = (float)$post_meta[$this->schema->property_map['commission_charged']][0];
        }

        if(isset($schema['properties']['credited_amount'])) {
            $post_data['credited_amount'] = $post_data['total'] - $post_data['commission_charged'];
        }

        if(isset($schema['properties']['buyer'])) {
            $post_data['buyer'] = $user->data->display_name;
        }

        if(isset($schema['properties']['restaurant'])) {
            $restaurant_id = (int)get_post_meta($order_id, $this->schema->property_map['restaurant'])[0];
            $restaurant = get_post($restaurant_id);
            $post_data['restaurant'] = $restaurant->post_title;
        }

        if(isset($schema['properties']['restaurant_owner'])) {
            $publisher_id = (int)get_post_meta($order_id, $this->schema->property_map['restaurant_owner'])[0];
            $publisher = get_post($publisher_id);
            $post_data['restaurant_owner'] = $publisher->post_title;
        }

        return $post_data;
    }

    public function prepare_item_for_response($post, $request) {
        $schema = $this->schema->get_schema($request);

        $post_data = array();

        $post_meta = get_post_meta($post->ID);

        $order_id = (int)$post_meta[$this->schema->property_map['order_id']][0];

        $user = get_user_by('id', $post->post_author);
    
        if(isset($schema['properties']['id'])) {
            $post_data['id'] = $post->ID;
        }
        
        if(isset($schema['properties']['date'])) {
            $post_data['date'] = $post->post_date;
        }
    
        if(isset($schema['properties']['modified'])) {
            $post_data['modified'] = $post->post_modified;
        }

        if(isset($schema['properties']['order_id'])) {
            $post_data['order_id'] = $order_id;
        }

        if(isset($schema['properties']['order_type'])) {
            $post_data['order_type'] = $post_meta[$this->schema->property_map['order_type']][0];
        }

        if(isset($schema['properties']['payment_gateway'])) {
            $post_data['payment_gateway'] = $post_meta[$this->schema->property_map['payment_gateway']][0];
        }

        if(isset($schema['properties']['payment_status'])) {
            $payment_status = get_post_meta($order_id, $this->schema->property_map['payment_status'])[0];
            $post_data['payment_status'] = $payment_status;
        }

        if(isset($schema['properties']['currency'])) {
            $post_data['currency'] = $post_meta[$this->schema->property_map['currency']][0];
        }

        if(isset($schema['properties']['total'])) {
            $post_data['total'] = (float)$post_meta[$this->schema->property_map['total']][0];
        }

        if(isset($schema['properties']['commission_charged'])) {
            $post_data['commission_charged'] = (float)$post_meta[$this->schema->property_map['commission_charged']][0];
        }

        if(isset($schema['properties']['credited_amount'])) {
            $post_data['credited_amount'] = $post_data['total'] - $post_data['commission_charged'];
        }

        if(isset($schema['properties']['buyer'])) {
            $post_data['buyer'] = $user->data->display_name;
        }

        if(isset($schema['properties']['buyer_info'])) {
            $post_data['buyer_info'] = array(
                'first_name'    => $post_meta[$this->schema->property_map['first_name']][0],
                'last_name'     => $post_meta[$this->schema->property_map['last_name']][0],
                'email'         => $post_meta[$this->schema->property_map['email']][0],
                'phone_number'  => $post_meta[$this->schema->property_map['phone_number']][0],
                'address'		=> $post_meta[$this->schema->property_map['address']][0],
            );
        }

        if(isset($schema['properties']['restaurant'])) {
            $restaurant_id = (int)get_post_meta($order_id, $this->schema->property_map['restaurant'])[0];
            $restaurant = get_post($restaurant_id);
            $post_data['restaurant'] = $restaurant->post_title;
        }

        if(isset($schema['properties']['restaurant_owner'])) {
            $publisher_id = (int)get_post_meta($order_id, $this->schema->property_map['restaurant_owner'])[0];
            $publisher = get_post($publisher_id);
            $post_data['restaurant_owner'] = $publisher->post_title;
        }

        return rest_ensure_response($post_data);
    }
}
