<?php

namespace FoodbakeryRestApi\Endpoints;

use FoodbakeryRestApi\Schemas\WithdrawalSchema;
use FoodbakeryRestApi\Utils\CollectionResponse;

class WithdrawalsEndpoint extends Endpoint {
    public function __construct()
    {
        parent::__construct(new WithdrawalSchema);
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
        $params = $request->get_params();
        $posts_per_page = ($params['limit'] ? (int)$params['limit'] : 20);
        $page = ($params['page'] ? (int)$params['page'] : 1);
        $page = $page - 1;
        $offset = $posts_per_page * $page;
    
        $args = array(
            'posts_per_page' => $posts_per_page,
            'offset' => $offset,
            'post_type' => 'withdrawals',
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
        $withdrawal_id = (int)$request['id'];
        $withdrawal = get_post($withdrawal_id);

        if(empty($withdrawal)) {
            return rest_ensure_response([]);
        } else if('withdrawals' != get_post_type($withdrawal_id)) {
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
            $publisher_id = (int)$post_meta['foodbakery_withdrawal_user'][0];
            $publisher = get_post($publisher_id);
            $post_data['user'] = $publisher->post_title;
        }

        if(isset($schema['properties']['amount'])) {
            $post_data['amount'] = $post_meta['withdrawal_amount'][0];
        }

        if(isset($schema['properties']['status'])) {
            $post_data['status'] = (float)$post_meta['foodbakery_withdrawal_status'][0];
        }

        return rest_ensure_response($post_data);
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
            $publisher_id = (int)$post_meta['foodbakery_withdrawal_user'][0];
            $publisher = get_post($publisher_id);
            $post_data['user'] = $publisher->post_title;
        }

        if(isset($schema['properties']['amount'])) {
            $post_data['amount'] = $post_meta['withdrawal_amount'][0];
        }

        if(isset($schema['properties']['detail'])) {
            $post_data['detail'] = $post_meta['foodbakery_withdrawal_detail'][0];
        }

        if(isset($schema['properties']['status'])) {
            $post_data['status'] = (float)$post_meta['foodbakery_withdrawal_status'][0];
        }

        return rest_ensure_response($post_data);
    }
}
