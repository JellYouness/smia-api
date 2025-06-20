<?php

namespace App\Enums;

enum ROLE: string
{
    case CLIENT = 'client';
    case CREATOR = 'creator';
    case AMBASSADOR = 'ambassador';
    case SYSTEM_ADMINISTRATOR = 'system_administrator';
}
