<?php

namespace App\Enums;

enum PROJECT_INVITE_FILTER: string
{
    case ALL = 'ALL';
    case UNINVITED = 'UNINVITED';
    case PENDING = 'PENDING';
    case ACCEPTED = 'ACCEPTED';
    case DECLINED = 'DECLINED';
}
