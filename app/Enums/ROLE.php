<?php

namespace App\Enums;

enum ROLE: string
{
    case CLIENT = 'CLIENT'; // client
    case CREATOR = 'CREATOR'; // creator
    case AMBASSADOR = 'AMBASSADOR'; // ambassador
    case SYSTEM_ADMINISTRATOR = 'ADMIN'; // admin
    case SUPER_ADMIN = 'SUPERADMIN'; // super admin
}
