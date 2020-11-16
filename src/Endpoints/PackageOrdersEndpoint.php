<?php

namespace FoodbakeryRestApi\Endpoints;

use FoodbakeryRestApi\Schemas\PackageOrderSchema;
use FoodbakeryRestApi\Utils\CollectionResponse;

class PackageOrdersEndpoint extends Endpoint {
    public function __construct()
    {
        parent::__construct(new PackageOrderSchema);
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
        $params = $request->get_params();
        $posts_per_page = ($params['limit'] ? (int)$params['limit'] : 20);
        $page = ($params['page'] ? (int)$params['page'] : 1);
        $page = $page - 1;
        $offset = $posts_per_page * $page;
    
        $args = array(
            'posts_per_page' => $posts_per_page,
            'offset' => $offset,
            'post_type' => 'package-orders',
        );
    
        $posts = get_posts($args);

        $data = array();
 
        if(empty($posts)) {
            return rest_ensure_response($data);
        }
 
        foreach($posts as $post) {
            $response = $this->prepare_items_for_response($post, $request)->data;
            $data[] = CollectionResponse::prepare_response_for_collection($response);
        }

        return rest_ensure_response($data);
    }

    public function get_item($request) {
        $package_order_id = (int)$request['id'];
        $package_order = get_post($package_order_id);

        if(empty($package_order)) {
            return rest_ensure_response([]);
        } else if('package-orders' != get_post_type($package_order_id)) {
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

    public function prepare_items_for_response($post, $request) {
        $schema = $this->schema->get_schema($request);

        $post_data = array();

        $post_meta = get_post_meta($post->ID);

        $membership = get_post($post_meta['foodbakery_transaction_package'][0]);
    
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
            $post_data['user'] = $post_meta['foodbakery_restaurant_username'][0];
        }

        if(isset($schema['properties']['amount'])) {
            $post_data['amount'] = (float)$post_meta['foodbakery_transaction_amount'][0];
        }

        return rest_ensure_response($post_data);
    }

    public function prepare_item_for_response($post, $request) {
        $schema = $this->schema->get_schema($request);

        $post_data = array();

        $post_meta = get_post_meta($post->ID);

        $membership = get_post($post_meta['foodbakery_transaction_package'][0]);
    
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
            $reviews = $post_meta['foodbakery_transaction_restaurant_reviews'][0];
            $featured = $post_meta['foodbakery_transaction_restaurant_feature_list'][0];
            $top_cat = $post_meta['foodbakery_transaction_restaurant_top_cat_list'][0];
            $phone = $post_meta['foodbakery_transaction_restaurant_phone'][0];
            $website = $post_meta['foodbakery_transaction_restaurant_website'][0];
            $social_reach = $post_meta['foodbakery_transaction_restaurant_social'][0];
            $ror = $post_meta['foodbakery_transaction_restaurant_ror'][0];

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
            $post_data['user'] = $post_meta['foodbakery_restaurant_username'][0];
        }

        if(isset($schema['properties']['amount'])) {
            $post_data['amount'] = (float)$post_meta['foodbakery_transaction_amount'][0];
        }

        if(isset($schema['properties']['expiry'])) {
            $post_data['expiry'] = (int)$post_meta['foodbakery_transaction_restaurant_expiry'][0];
        }

        if(isset($schema['properties']['expiry_date'])) {
            $post_data['expiry_date'] = (int)$post_meta['foodbakery_transaction_expiry_date'][0];
        }

        return rest_ensure_response($post_data);
    }
}
