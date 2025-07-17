<?php

namespace App\Enums;

enum PROJECT_INVITE_STATUS: string
{
  case PENDING = 'PENDING';
  case ACCEPTED = 'ACCEPTED';
  case DECLINED = 'DECLINED';
  case EXPIRED = 'EXPIRED';
}
