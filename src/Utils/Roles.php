<?php

namespace FoodbakeryRestApi\Utils;

final class Roles {
    public static function register_roles() {
        add_role('operator', 'Operator');
    }

    public static function remove_roles() {
        remove_role('operator');
    }
}
