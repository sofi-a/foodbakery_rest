<?php

namespace FoodbakeryRestApi\Endpoints;

use FoodbakeryRestApi\Schemas\PackageOrderSchema;
use FoodbakeryRestApi\Utils\CollectionResponse;

class PackageOrdersEndpoint extends Endpoint {
    private $sort_keys;

    public function __construct()
    {
        parent::__construct('package-orders', new PackageOrderSchema);
        $this->sort_keys = array(
            'user',
            'expiry_date',
        );
    }

    public function register_routes() {
        // GET /foodbakery/v1/package-orders
        register_rest_route('foodbakery/v1', '/package-orders', array(
            array(
                'methods'   => 'GET',
                'callback'  => array($this, 'get_items'),
            ),
            'schema'        => array($this->schema, 'get_schema'),
        ));

        // GET /foodbakery/v1/package-orders/:id
        register_rest_route('foodbakery/v1', '/package-orders/(?P<id>\d+)', array(
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
        $package_order_id = (int)$request['id'];
        $package_order = get_post($package_order_id);

        if(empty($package_order) || $this->post_type != get_post_type($package_order_id)) {
            return new WP_Error(
                'invalid_operation',
                "There is no package order with id: $package_order_id",
                array(
                    'status' => 403,
                ),
            );
        }

        $response = $this->prepare_item_for_response($package_order, $request);

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
            else if($sort === 'membership_type') {
				$args['meta_key'] = $this->schema->property_map['membership'];
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

    public function prepare_items_for_response($post, $request) {
        $schema = $this->schema->get_schema($request);

        $post_data = array();

        $post_meta = get_post_meta($post->ID);

        $membership = get_post($post_meta[$this->schema->property_map['membership']][0]);
    
        if(isset($schema['properties']['id'])) {
            $post_data['id'] = $post->ID;
        }
        
        if(isset($schema['properties']['date'])) {
            $post_data['date'] = $post->post_date;
        }
    
        if(isset($schema['properties']['modified'])) {
            $post_data['modified'] = $post->post_modified;
        }

        if(isset($schema['properties']['membership_type'])) {
            $post_data['membership_type'] = $membership->post_title;
        }

        if(isset($schema['properties']['user'])) {
            $post_data['user'] = $post_meta[$this->schema->property_map['user']][0];
        }

        if(isset($schema['properties']['amount'])) {
            $post_data['amount'] = (float)$post_meta[$this->schema->property_map['amount']][0];
        }

        return $post_data;
    }

    public function prepare_item_for_response($post, $request) {
        $schema = $this->schema->get_schema($request);

        $post_data = array();

        $post_meta = get_post_meta($post->ID);

        $membership = get_post($post_meta[$this->schema->property_map['membership']][0]);
    
        if(isset($schema['properties']['id'])) {
            $post_data['id'] = $post->ID;
        }
        
        if(isset($schema['properties']['date'])) {
            $post_data['date'] = $post->post_date;
        }
    
        if(isset($schema['properties']['modified'])) {
            $post_data['modified'] = $post->post_modified;
        }

        if(isset($schema['properties']['membership_type'])) {
            $post_data['membership_type'] = $membership->post_title;
        }

        if(isset($schema['properties']['membership_info'])) {
            $reviews = $post_meta[$this->schema->property_map['reviews']][0];
            $featured = $post_meta[$this->schema->property_map['featured']][0];
            $top_cat = $post_meta[$this->schema->property_map['top_cat']][0];
            $phone = $post_meta[$this->schema->property_map['phone']][0];
            $website = $post_meta[$this->schema->property_map['website']][0];
            $social_reach = $post_meta[$this->schema->property_map['social_reach']][0];
            $ror = $post_meta[$this->schema->property_map['ror']][0];

            $post_data['membership_info'] = array(
                'num_tags'      => $post_meta['foodbakery_transaction_restaurant_tags_num'][0],
                'reviews'       => $reviews == 'on' ? true : false,
                'featured'      => $featured == 'on' ? true : false,
                'top_cat'       => $top_cat == 'on' ? true : false,
                'phone'         => $phone == 'on' ? true : false,
                'website'       => $website == 'on' ? true : false,
                'social_reach'  => $social_reach == 'on' ? true : false,
                'ror'           => $ror == 'on' ? true : false,
            );
        }

        if(isset($schema['properties']['user'])) {
            $post_data['user'] = $post_meta[$this->schema->property_map['user']][0];
        }

        if(isset($schema['properties']['amount'])) {
            $post_data['amount'] = (float)$post_meta[$this->schema->property_map['amount']][0];
        }

        if(isset($schema['properties']['expiry'])) {
            $post_data['expiry'] = (int)$post_meta[$this->schema->property_map['expiry']][0];
        }

        if(isset($schema['properties']['expiry_date'])) {
            $post_data['expiry_date'] = (int)$post_meta[$this->schema->property_map['expiry_date']][0];
        }

        return rest_ensure_response($post_data);
    }
}
