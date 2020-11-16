<?php

namespace FoodbakeryRestApi\Endpoints;

use FoodbakeryRestApi\Schemas\TransactionSchema;
use FoodbakeryRestApi\Utils\CollectionResponse;

class TransactionsEndpoint extends Endpoint {
    public function __construct()
    {
        parent::__construct(new TransactionSchema);
    }

    public function register_routes() {
        // GET /foodbakery/v1/trans
        register_rest_route('foodbakery/v1', '/trans', array(
            array(
                'methods'   => 'GET',
                'callback'  => array($this, 'get_items'),
            ),
            'schema'        => array($this->schema, 'get_schema'),
        ));

        // GET /foodbakery/v1/trans/:id
        register_rest_route('foodbakery/v1', '/trans/(?P<id>\d+)', array(
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
            'post_type' => 'foodbakery-trans',
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
        $trans_id = (int)$request['id'];
        $transaction = get_post($trans_id);

        if(empty($transaction)) {
            return rest_ensure_response([]);
        } else if('foodbakery-trans' != get_post_type($trans_id)) {
            return new WP_Error(
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

    public function prepare_items_for_response($post, $request) {
        $schema = $this->schema->get_schema($request);

        $post_data = array();

        $post_meta = get_post_meta($post->ID);

        $order_id = (int)$post_meta['foodbakery_transaction_order_id'][0];

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
            $post_data['order_type'] = $post_meta['foodbakery_transaction_order_type'][0];
        }

        if(isset($schema['properties']['payment_gateway'])) {
            $post_data['payment_gateway'] = $post_meta['foodbakery_transaction_pay_method'][0];
        }

        if(isset($schema['properties']['total'])) {
            $post_data['total'] = (float)$post_meta['foodbakery_transaction_amount'][0];
        }

        if(isset($schema['properties']['commission_charged'])) {
            $post_data['commission_charged'] = (float)$post_meta['foodbakery_order_amount_charged'][0];
        }

        if(isset($schema['properties']['credited_amount'])) {
            $post_data['credited_amount'] = $post_data['total'] - $post_data['commission_charged'];
        }

        if(isset($schema['properties']['buyer'])) {
            $post_data['buyer'] = $user->data->display_name;
        }

        if(isset($schema['properties']['restaurant'])) {
            $restaurant_id = (int)get_post_meta($order_id, 'foodbakery_restaurant_id')[0];
            $restaurant = get_post($restaurant_id);
            $post_data['restaurant'] = $restaurant->post_title;
        }

        if(isset($schema['properties']['restaurant_owner'])) {
            $publisher_id = (int)get_post_meta($order_id, 'foodbakery_publisher_id')[0];
            $publisher = get_post($publisher_id);
            $post_data['restaurant_owner'] = $publisher->post_title;
        }

        return rest_ensure_response($post_data);
    }

    public function prepare_item_for_response($post, $request) {
        $schema = $this->schema->get_schema($request);

        $post_data = array();

        $post_meta = get_post_meta($post->ID);

        $order_id = (int)$post_meta['foodbakery_transaction_order_id'][0];

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
            $post_data['order_type'] = $post_meta['foodbakery_transaction_order_type'][0];
        }

        if(isset($schema['properties']['payment_gateway'])) {
            $post_data['payment_gateway'] = $post_meta['foodbakery_transaction_pay_method'][0];
        }

        if(isset($schema['properties']['payment_status'])) {
            $payment_status = get_post_meta($order_id, 'foodbakery_order_payment_status')[0];
            $post_data['payment_status'] = $payment_status;
        }

        if(isset($schema['properties']['currency'])) {
            $post_data['currency'] = $post_meta['foodbakery_currency_obj'][0];
        }

        if(isset($schema['properties']['total'])) {
            $post_data['total'] = (float)$post_meta['foodbakery_transaction_amount'][0];
        }

        if(isset($schema['properties']['commission_charged'])) {
            $post_data['commission_charged'] = (float)$post_meta['foodbakery_order_amount_charged'][0];
        }

        if(isset($schema['properties']['credited_amount'])) {
            $post_data['credited_amount'] = $post_data['total'] - $post_data['commission_charged'];
        }

        if(isset($schema['properties']['buyer'])) {
            $post_data['buyer'] = $user->data->display_name;
        }

        if(isset($schema['properties']['buyer_info'])) {
            $post_data['buyer_info'] = array(
                'first_name'    => $post_meta['foodbakery_trans_first_name'][0],
                'last_name'     => $post_meta['foodbakery_trans_last_name'][0],
                'email'         => $post_meta['foodbakery_trans_email'][0],
                'phone_number'  => $post_meta['foodbakery_trans_phone_number'][0],
            );
        }

        if(isset($schema['properties']['restaurant'])) {
            $restaurant_id = (int)get_post_meta($order_id, 'foodbakery_restaurant_id')[0];
            $restaurant = get_post($restaurant_id);
            $post_data['restaurant'] = $restaurant->post_title;
        }

        if(isset($schema['properties']['restaurant_owner'])) {
            $publisher_id = (int)get_post_meta($order_id, 'foodbakery_publisher_id'[0]);
            $publisher = get_post($publisher_id);
            $post_data['restaurant_owner'] = $publisher->post_title;
        }

        return rest_ensure_response($post_data);
    }
}
