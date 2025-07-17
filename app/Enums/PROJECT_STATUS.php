<?php

namespace App\Enums;

enum PROJECT_STATUS: string
{
  case DRAFT = 'draft';
  case IN_PROGRESS = 'in_progress';
  case COMPLETED = 'completed';
  case CANCELLED = 'cancelled';
}
