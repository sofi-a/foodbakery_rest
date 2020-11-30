<?php

namespace FoodbakeryRestApi\Schemas;

class WithdrawalSchema extends Schema {
    public function __construct()
    {
        parent::__construct(array(
            'user'      => 'foodbakery_withdrawal_user',
            'amount'    => 'withdrawal_amount',
            'detail'    => 'foodbakery_withdrawal_detail',
            'status'    => 'foodbakery_withdrawal_status',
        ));
    }
    
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
                'user'                  => array(
                    'description'       => esc_html__('The name of the user requesting the withdrawal.', 'foodbakery-rest'),
                    'type'              => 'string',
                    'readonly'          => true,
                ),
                'amount'                => array(
                    'description'       => esc_html__('The amount to be withdrawn.', 'foodbakery-rest'),
                    'type'              => 'number',
                    'readonly'          => true,
                ),
                'detail'                => array(
                    'description'       => esc_html__('Extra information about the withdrawal.', 'foodbakery-rest'),
                    'type'              => 'string',
                    'readonly'          => true,
                ),
                'status'                => array(
                    'description'       => esc_html__('The status of the withdrawal.', 'foodbakery-rest'),
                    'type'              => 'string',
                    'readonly'          => true,
                ),
            ),
        );
    }
}