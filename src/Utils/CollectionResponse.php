<?php

namespace FoodbakeryRestApi\Utils;

final class CollectionResponse {
    public static function prepare_response_for_collection($response) {
        if (!($response instanceof WP_REST_Response)) {
            return $response;
        }
    
        $data = (array) $response->get_data();
        $server = rest_get_server();
    
        if ( method_exists($server, 'get_compact_response_links')) {
            $links = call_user_func(array($server, 'get_compact_response_links'), $response);
        } else {
            $links = call_user_func(array($server, 'get_response_links'), $response);
        }
    
        if (!empty($links)) {
            $data['_links'] = $links;
        }
    
        return $data;
    }
}
