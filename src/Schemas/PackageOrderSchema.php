<?php

namespace FoodbakeryRestApi\Schemas;

class PackageOrderSchema extends Schema {
    public function __construct()
    {
        parent::__construct(array(
            'membership'    => 'foodbakery_transaction_package',
            'num_tags'      => 'foodbakery_transaction_restaurant_tags_num',
            'reviews'       => 'foodbakery_transaction_restaurant_reviews',
            'featured'      => 'foodbakery_transaction_restaurant_feature_list',
            'top_cat'       => 'foodbakery_transaction_restaurant_top_cat_list',
            'phone'         => 'foodbakery_transaction_restaurant_phone',
            'website'       =>  'foodbakery_transaction_restaurant_website',
            'social_reach'  => 'foodbakery_transaction_restaurant_social',
            'ror'           => 'foodbakery_transaction_restaurant_ror',
            'user'          => 'foodbakery_restaurant_username',
            'amount'        => 'foodbakery_transaction_amount',
            'expiry'        => 'foodbakery_transaction_restaurant_expiry',
            'expiry_date'   => 'foodbakery_transaction_expiry_date',
        ));
    }

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
                    'type'              => 'number',
                    'readonly'          => true,
                ),
                'expiry'                => array(
                    'description'       => esc_html__('The length of the membership in days.', 'foodbakery-rest'),
                    'type'              => 'integer',
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