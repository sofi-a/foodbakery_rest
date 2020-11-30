<?php

namespace FoodbakeryRestApi\Schemas;

class TransactionSchema extends Schema {
    public function __construct()
    {
        parent::__construct(array(
            'order_id'              => 'foodbakery_transaction_order_id',
            'order_type'            =>'foodbakery_transaction_order_type',
            'payment_gateway'       => 'foodbakery_transaction_pay_method',
            'payment_status'        => 'foodbakery_order_payment_status',
            'currency'              => 'foodbakery_currency_obj',
            'total'                 => 'foodbakery_transaction_amount',
            'commission_charged'    => 'foodbakery_order_amount_charged',
            'first_name'            => 'foodbakery_trans_first_name',
            'last_name'             => 'foodbakery_trans_last_name',
            'email'                 => 'foodbakery_trans_email',
            'phone_number'          => 'foodbakery_trans_phone_number',
			'address'				=> 'foodbakery_trans_address',
            'restaurant'            => 'foodbakery_restaurant_id',
            'restaurant_owner'      => 'foodbakery_publisher_id',
        ));
    }

    public function get_schema($request) {
        return array(
            '$schema'                   => 'http://json-schema.org/draft-04/schema#',
            'title'                     => 'transaction',
            'type'                      => 'object',
            'properties'                => array(
                'id'                    => array(
                    'description'       => esc_html__('Unique identifier for the object.', 'foodbakery-rest'),
                    'type'              => 'integer',
                    'context'           => array('view', 'edit', 'embed'),
                    'readonly'          => true,
                ),
                'order_id'              => array(
                    'description'       => esc_html__('Unique identifier for the order id.', 'foodbakery-rest'),
                    'type'              => 'integer',
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
                'order_type'            => array(
                    'description'       => esc_html__('The type of order.', 'foodbakery-rest'),
                    'type'              => 'string',
                    'readonly'          => true,
                ),
                'payment_gateway'       => array(
                    'description'       => esc_html__('The payment gateway used to complete the order.', 'foodbakery-rest'),
                    'type'              => 'string',
                    'readonly'          => true,
                ),
                'payment_status'        => array(
                    'description'       => esc_html__('The payment status.', 'foodbakery-rest'),
                    'type'              => 'string',
                    'readonly'          => true,
                ),
                'currency'              => array(
                    'description'       => esc_html__('The currency used to perform the payment.', 'foodbakery-rest'),
                    'type'              => 'string',
                    'readonly'          => true,
                ),
                'total'                 => array(
                    'description'       => esc_html__('The total price.', 'foodbakery-rest'),
                    'type'              => 'number',
                    'readonly'          => true,
                ),
                'commission_charged'    => array(
                    'description'       => esc_html__('The amount of commission the restaurant is charged.', 'foodbakery-rest'),
                    'type'              => 'number',
                    'readonly'          => true,
                ),
                'credited_amount'       => array(
                    'description'       => esc_html__('The amount credited after commission has been deducted.', 'foodbakery-rest'),
                    'type'              => 'number',
                    'readonly'          => true,
                ),
                'buyer'                 => array(
                    'description'       => esc_html__('The name of the buyer.', 'foodbakery-rest'),
                    'type'              => 'string',
                    'readonly'          => true,
                ),
                'buyer_info'            => array(
                    'description'       => esc_html__('The buyer\'s information.', 'foodbakery-rest'),
                    'type'              => 'object',
                    'properties'        => array(
                        'first_name'    => array('type' => 'string'),
                        'last_name'     => array('type' => 'string'),
                        'email'         => array('type' => 'string'),
                        'phone_number'  => array('type' => 'string'),
                        'address'       => array('type' => 'string'),
                    ),
                    'readonly'          => true,
                ),
                'restaurant'            => array(
                    'description'       => esc_html__('The restaurant\'s name.', 'foodbakery-rest'),
                    'type'              => 'string',
                    'readonly'          => true,
                ),
                'restaurant_owner'      => array(
                    'description'       => esc_html__('The restaurant owner\'s username.', 'foodbakery-rest'),
                    'type'              => 'string',
                    'readonly'          => true,
                ),
            ),
        );
    }
}