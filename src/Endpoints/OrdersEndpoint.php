<?php

namespace FoodbakeryRestApi\Endpoints;

use FoodbakeryRestApi\Schemas\OrderSchema;
use FoodbakeryRestApi\Utils\CollectionResponse;

class OrdersEndpoint extends Endpoint {
    private $sort_keys;

    public function __construct()
    {
        parent::__construct('orders_inquiries', new OrderSchema);
        $this->sort_keys = array(
            'order_status',
            'payment_status',
            'currency',
            'restaurant_owner',
            'restaurant',
            'order_date',
            'delivery_date',
        );
    }

    public function register_routes() {
        // GET /foodbakery/v1/orders
        register_rest_route('foodbakery/v1', '/orders', array(
            array(
                'methods'   => 'GET',
                'callback'  => array($this, 'get_items'),
            ),
            'schema'        => array($this->schema, 'get_schema'),
        ));

        // GET /foodbakery/v1/orders/:id
        register_rest_route('foodbakery/v1', '/orders/(?P<id>\d+)', array(
            array(
                'methods'   => 'GET',
                'callback'  => array($this, 'get_item'),
            ),
            'schema'        => array($this->schema, 'get_schema'),
        ));
    }

    public function get_total() {
        $posts = get_posts(array(
            'posts_per_page'    => -1,
            'post_type'         => $this->post_type,
            'post_status'       => 'publish',
            'meta_query'        => array(
                array(
                    'key'       => $this->schema->property_map['type'],
                    'value'     => 'order',
                ),
            ))
        );

        return count($posts);
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
            'meta_query'        => array(
                array(
                    'key'       => $this->schema->property_map['type'],
                    'value'     => 'order',
                ),
            )
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
        $order_id = (int)$request['id'];
        $order = get_post($order_id);

        if(empty($order) || $this->post_type != get_post_type($order_id)) {
            return new \WP_Error(
                'invalid_operation',
                "There is no order with id: $order_id",
                array(
                    'status' => 403,
                )
            );
        }

        $response = $this->prepare_item_for_response($order, $request);

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

        if(isset($schema['properties']['id'])) {
            $post_data['id'] = $post->ID;
        }

        if(isset($schema['properties']['date'])) {
            $post_data['date'] = $post->post_date;
        }

        if(isset($schema['properties']['modified'])) {
            $post_data['modified'] = $post->post_modified;
        }

        if(isset($schema['properties']['type'])) {
            $post_data['type'] = $post_meta[$this->schema->property_map['type']][0];
        }

        if(isset($schema['properties']['order_status'])) {
            $post_data['order_status'] = $post_meta[$this->schema->property_map['order_status']][0];
        }

        if(isset($schema['properties']['payment_status'])) {
            $post_data['payment_status'] = $post_meta[$this->schema->property_map['payment_status']][0];
        }

        if(isset($schema['properties']['total'])) {
            $post_data['total'] = (float)$post_meta[$this->schema->property_map['total']][0];
        }

        if(isset($schema['properties']['currency'])) {
            $post_data['currency'] = $post_meta[$this->schema->property_map['currency']][0];
        }

        if(isset($schema['properties']['buyer'])) {
            $user = get_user_by('id', $post->post_author);
            $post_data['buyer'] = $user->data->display_name;
        }

        if(isset($schema['properties']['restaurant'])) {
            $restaurant_id = (int)$post_meta[$this->schema->property_map['restaurant']][0];
            $restaurant = get_post($restaurant_id);
            $post_data['restaurant'] = $restaurant->post_title;
        }

        if(isset($schema['properties']['restaurant_owner'])) {
            $publisher_id = (int)$post_meta[$this->schema->property_map['restaurant_owner']][0];
            $publisher = get_post($publisher_id);
            $post_data['restaurant_owner'] = $publisher->post_title;
        }

        if(isset($schema['properties']['order_date'])) {
			$order_date = $post_meta[$this->schema->property_map['order_date']][0];
            $post_data['order_date'] = date('d/m/Y', (int)$order_date);
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
    
        if(isset($schema['properties']['type'])) {
            $post_data['type'] = $post_meta[$this->schema->property_map['type']][0];
        }

        if(isset($schema['properties']['order_status'])) {
            $post_data['order_status'] = $post_meta[$this->schema->property_map['order_status']][0];
        }

        if(isset($schema['properties']['payment_type'])) {
            $post_data['payment_type'] = $post_meta[$this->schema->property_map['payment_type']][0];
        }

        if(isset($schema['properties']['payment_status'])) {
            $post_data['payment_status'] = $post_meta[$this->schema->property_map['payment_status']][0];
        }

        if(isset($schema['properties']['currency'])) {
            $post_data['currency'] = $post_meta[$this->schema->property_map['currency']][0];
        }

        if(isset($schema['properties']['subtotal'])) {
            $post_data['subtotal'] = (float)$post_meta[$this->schema->property_map['subtotal']][0];
        }

        if(isset($schema['properties']['total'])) {
            $post_data['total'] = (float)$post_meta[$this->schema->property_map['total']][0];
        }

        if(isset($schema['properties']['vat_percent'])) {
            $post_data['vat_percent'] = (int)$post_meta[$this->schema->property_map['vat_percent']][0];
        }

        if(isset($schema['properties']['vat'])) {
            $post_data['vat'] = (float)$post_meta[$this->schema->property_map['vat']][0];
        }

        if(isset($schema['properties']['order_fee_type'])) {
            $post_data['order_fee_type'] = $post_meta[$this->schema->property_map['order_fee_type']][0];
        }

        if(isset($schema['properties']['order_fee'])) {
            $post_data['order_fee'] = $post_meta[$this->schema->property_map['order_fee']][0];
        }

        if(isset($schema['properties']['commission_charged'])) {
            $post_data['commission_charged'] = (float)$post_meta[$this->schema->property_map['commission_charged']][0];
        }

        if(isset($schema['properties']['credited_amount'])) {
            $post_data['credited_amount'] = (float)$post_meta[$this->schema->property_map['credited_amount']][0];
        }

        if(isset($schema['properties']['items'])) {
            $items = array();
            foreach(get_post_meta($post->ID, $this->schema->property_map['items'])[0] as $item) $items[] = $item;
            $post_data['items'] = $items;
        }

        if(isset($schema['properties']['buyer'])) {
            $user = get_user_by('id', $post->post_author);
            $post_data['buyer'] = $user->data->display_name;
        }

        if(isset($schema['properties']['restaurant'])) {
            $restaurant_id = (int)$post_meta[$this->schema->property_map['restaurant']][0];
            $restaurant = get_post($restaurant_id);
            $post_data['restaurant'] = $restaurant->post_title;
        }

        if(isset($schema['properties']['restaurant_owner'])) {
            $publisher_id = (int)$post_meta[$this->schema->property_map['restaurant_owner']][0];
            $publisher = get_post($publisher_id);
            $post_data['restaurant_owner'] = $publisher->post_title;
        }

        if(isset($schema['properties']['order_date'])) {
			$order_date = $post_meta[$this->schema->property_map['order_date']][0];
            $post_data['order_date'] = date('d/m/Y', (int)$order_date);
        }

		if(isset($schema['properties']['delivery_date'])) {
			$delivery_date = $post_meta[$this->schema->property_map['delivery_date']][0];
            $post_data['delivery_date'] = date('d/m/Y', (int)$delivery_date);
		}

        return rest_ensure_response($post_data);
    }
}
