<?php

namespace App\Enums;

enum PROJECT_STATUS: string
{
  case PENDING = 'pending';
  case IN_PROGRESS = 'in_progress';
  case COMPLETED = 'completed';
  case CANCELLED = 'cancelled';
}
