<?php

namespace FoodbakeryRestApi\Schemas;

class OrderSchema extends Schema {
    public function get_schema($request) {
        return array(
            '$schema'                   => 'http://json-schema.org/draft-04/schema#',
            'title'                     => 'order/inquiry',
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
                'type'                  => array(
                    'description'       => esc_html__('The type of order.', 'foodbakery-rest'),
                    'type'              => 'string',
                    'readonly'          => true,
                ),
                'order_status'          => array(
                    'description'       => esc_html__('The order status.', 'foodbakery-rest'),
                    'type'              => 'string',
                    'readonly'          => true,
                ),
                'payment_type'          => array(
                    'description'       => esc_html__('The type of payment used to complete the order.', 'foodbakery-rest'),
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
                'subtotal'              => array(
                    'description'       => esc_html__('The subtotal price.', 'foodbakery-rest'),
                    'type'              => 'number',
                    'readonly'          => true,
                ),
                'total'                 => array(
                    'description'       => esc_html__('The total price.', 'foodbakery-rest'),
                    'type'              => 'number',
                    'readonly'          => true,
                ),
                'vat_percent'           => array(
                    'description'       => esc_html__('The amount of VAT in percent.', 'foodbakery-rest'),
                    'type'              => 'integer',
                    'readonly'          => true,
                ),
                'vat'                   => array(
                    'description'       => esc_html__('The amount charged for VAT.', 'foodbakery-rest'),
                    'type'              => 'number',
                    'readonly'          => true,
                ),
                'order_fee_type'        => array(
                    'description'       => esc_html__('The type of order (delivery, pickup or pickup/delivery).', 'foodbakery-rest'),
                    'type'              => 'string',
                    'readonly'          => true,
                ),
                'order_fee'             => array(
                    'description'       => esc_html__('The amount charged for pickup/delivery.', 'foodbakery-rest'),
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
                'items'                 => array(
                    'description'       => esc_html__('The ordered food items.', 'foodbakery-rest'),
                    'type'              => 'array',
                    'readonly'          => true,
                    'item'              => array(
                        'category'      => array('type' => 'string'),
                        'title'         => array('type' => 'string'),
                        'price'         => array('type' => 'number'),
                        'extras'        => array(
                            'type'      => 'array',
                            'item'      => array(
                                'type'  => 'object',
                                'properties' => array(
                                    'heading'   => array('type' => 'string'),
                                    'title'     => array('type' => 'string'),
                                    'price'     => array('type' => 'string'),
                                ),
                                'default'    => [],
                            ),
                        ),
                    ),
                    'default'           => [],
                ),
                'buyer'                 => array(
                    'description'       => esc_html__('The name of the buyer.', 'foodbakery-rest'),
                    'type'              => 'string',
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
                'order_date'            => array(
                    'description'       => esc_html__('The date the order was placed on.', 'foodbakery-rest'),
                    'type'              => 'string',
                    'readonly'          => true,
                ),
                'delivery_date'         => array(
                    'description'       => esc_html__('The date the order will be delivered.', 'foodbakery-rest'),
                    'type'              => 'string',
                    'readonly'          => true,
                ),
            ),
        );
    }
}
