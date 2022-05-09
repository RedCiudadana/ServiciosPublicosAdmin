<?php

namespace App\Config;

class Roles
{
    const SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    const ADMIN = 'ROLE_ADMIN';

    const roles = [
        self::ADMIN,
        self::SUPER_ADMIN
    ];
}