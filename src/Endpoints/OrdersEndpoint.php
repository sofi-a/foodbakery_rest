<?php

namespace FoodbakeryRestApi\Endpoints;

use FoodbakeryRestApi\Schemas\OrderSchema;
use FoodbakeryRestApi\Utils\CollectionResponse;

class OrdersEndpoint extends Endpoint {
    public function __construct()
    {
        parent::__construct(new OrderSchema);
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

    public function get_items($request) {
        $params = $request->get_params();
        $posts_per_page = ($params['limit'] ? (int)$params['limit'] : 20);
        $page = ($params['page'] ? (int)$params['page'] : 1);
        $page = $page - 1;
        $offset = $posts_per_page * $page;
    
        $args = array(
            'posts_per_page' => $posts_per_page,
            'offset' => $offset,
            'post_type' => 'orders_inquiries',
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
        $order_id = (int)$request['id'];
        $order = get_post($order_id);

        if(empty($order)) {
            return rest_ensure_response([]);
        } else if('orders_inquiries' != get_post_type($order_id)) {
            return new WP_Error(
                'invalid_operation',
                "There is no order with id: $order_id",
                array(
                    'status' => 403,
                ),
            );
        }

        $response = $this->prepare_item_for_response($order, $request);

        return rest_ensure_response($response);
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
            $post_data['type'] = $post_meta['foodbakery_order_type'][0];
        }

        if(isset($schema['properties']['order_status'])) {
            $post_data['order_status'] = $post_meta['foodbakery_order_status'][0];
        }

        if(isset($schema['properties']['payment_status'])) {
            $post_data['payment_status'] = $post_meta['foodbakery_order_payment_status'][0];
        }

        if(isset($schema['properties']['total'])) {
            $post_data['total'] = (float)$post_meta['services_total_price'][0];
        }

        if(isset($schema['properties']['buyer'])) {
            $user = get_user_by('id', $post->post_author);
            $post_data['buyer'] = $user->data->display_name;
        }

        if(isset($schema['properties']['restaurant'])) {
            $restaurant_id = (int)$post_meta['foodbakery_restaurant_id'][0];
            $restaurant = get_post($restaurant_id);
            $post_data['restaurant'] = $restaurant->post_title;
        }

        if(isset($schema['properties']['restaurant_owner'])) {
            $publisher_id = (int)$post_meta['foodbakery_publisher_id'][0];
            $publisher = get_post($publisher_id);
            $post_data['restaurant_owner'] = $publisher->post_title;
        }

        if(isset($schema['properties']['order_date'])) {
            $post_data['order_date'] = $post_meta['foodbakery_order_date'][0];
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
    
        if(isset($schema['properties']['type'])) {
            $post_data['type'] = $post_meta['foodbakery_order_type'][0];
        }

        if(isset($schema['properties']['order_status'])) {
            $post_data['order_status'] = $post_meta['foodbakery_order_status'][0];
        }

        if(isset($schema['properties']['payment_type'])) {
            $post_data['payment_type'] = $post_meta['foodbakery_order_paytype'][0];
        }

        if(isset($schema['properties']['payment_status'])) {
            $post_data['payment_status'] = $post_meta['foodbakery_order_payment_status'][0];
        }

        if(isset($schema['properties']['currency'])) {
            $post_data['currency'] = $post_meta['foodbakery_currency_obj'][0];
        }

        if(isset($schema['properties']['subtotal'])) {
            $post_data['subtotal'] = (float)$post_meta['order_subtotal_price'][0];
        }

        if(isset($schema['properties']['total'])) {
            $post_data['total'] = (float)$post_meta['services_total_price'][0];
        }

        if(isset($schema['properties']['vat_percent'])) {
            $post_data['vat_percent'] = (int)$post_meta['order_vat_percent'][0];
        }

        if(isset($schema['properties']['vat'])) {
            $post_data['vat'] = (float)$post_meta['order_vat_cal_percent'][0];
        }

        if(isset($schema['properties']['order_fee_type'])) {
            $post_data['order_fee_type'] = $post_meta['menu_order_fee_type'][0];
        }

        if(isset($schema['properties']['order_fee'])) {
            $post_data['order_fee'] = $post_meta['menu_order_fee'][0];
        }

        if(isset($schema['properties']['commission_charged'])) {
            $post_data['commission_charged'] = (float)$post_meta['order_amount_charged'][0];
        }

        if(isset($schema['properties']['credited_amount'])) {
            $post_data['credited_amount'] = (float)$post_meta['order_amount_credited'][0];
        }

        if(isset($schema['properties']['items'])) {
            $items = array();
            foreach(get_post_meta($post->ID, 'menu_items_list')[0] as $item) $items[] = $item;
            $post_data['items'] = $items;
        }

        if(isset($schema['properties']['buyer'])) {
            $user = get_user_by('id', $post->post_author);
            $post_data['buyer'] = $user->data->display_name;
        }

        if(isset($schema['properties']['restaurant'])) {
            $restaurant_id = (int)$post_meta['foodbakery_restaurant_id'][0];
            $restaurant = get_post($restaurant_id);
            $post_data['restaurant'] = $restaurant->post_title;
        }

        if(isset($schema['properties']['restaurant_owner'])) {
            $publisher_id = (int)$post_meta['foodbakery_publisher_id'][0];
            $publisher = get_post($publisher_id);
            $post_data['restaurant_owner'] = $publisher->post_title;
        }

        if(isset($schema['properties']['order_date'])) {
            $post_data['order_date'] = $post_meta['foodbakery_order_date'][0];
        }

        if(isset($schema['properties']['delivery_date'])) {
            $post_data['delivery_date'] = $post_meta['foodbakery_delivery_date'][0];
        }

        return rest_ensure_response($post_data);
    }
}
