<?php

namespace FoodbakeryRestApi\Schemas;

class PackageOrderSchema extends Schema {
    public function get_schema($request) {
        return array(
            '$schema'                   => 'http://json-schema.org/draft-04/schema#',
            'title'                     => 'packakge-order',
            'type'                      => 'object',
            'properties'                => array(
                'id'                    => array(
                    'description'       => esc_html__('Unique identifier for the object.', 'foodbakery-rest'),
                    'type'              => 'integer',
                    'context'           => array('view', 'edit', 'embed'),
                    'readonly'          => true,
                ),
                'date'                  => array(
                    'description'       => esc_html__('The date the object was created on.', 'foodbakery-rest'),
                    'type'              => 'date',
                    'readonly'          => true,
                ),
                'modified'              => array(
                    'description'       => esc_html__('The date the object was modified on.', 'foodbakery-rest'),
                    'type'              => 'date',
                    'readonly'          => true,
                ),
                'membership_type'       => array(
                    'description'       => esc_html__('The type of membership.', 'foodbakery-rest'),
                    'type'              => 'string',
                    'readonly'          => true,
                ),
                'membership_info'       => array(
                    'description'       => esc_html__('Extra information about the membership.', 'foodbakery-rest'),
                    'type'              => 'object',
                    'properties'        => array(
                        'num_tags'      => array('type' => 'integer'),
                        'reviews'       => array('type' => 'boolean'),
                        'featured'      => array('type' => 'boolean'),
                        'top_cat'       => array('type' => 'boolean'),
                        'phone'         => array('type' => 'boolean'),
                        'website'       => array('type' => 'boolean'),
                        'social_reach'  => array('type' => 'boolean'),
                        'ror'           => array('type' => 'boolean'),
                    ),
                    'readonly'          => true,
                ),
                'user'                  => array(
                    'description'       => esc_html__('The membership buyer\'s username', 'foodbakery-rest'),
                    'type'              => 'string',
                    'readonly'          => true,
                ),
                'amount'                => array(
                    'description'       => esc_html__('The price of the membership.', 'foodbakery-rest'),
                    'type'              => 'string',
                    'readonly'          => true,
                ),
                'expiry'                => array(
                    'description'       => esc_html__('The length of the membership in days.', 'foodbakery-rest'),
                    'type'              => 'string',
                    'readonly'          => true,
                ),
                'expiry_date'           => array(
                    'description'       => esc_html__('The date the membership expires on.', 'foodbakery-rest'),
                    'type'              => 'string',
                    'readonly'          => true,
                ),
            ),
        );
    }
}