<?php

namespace App\Enums;

enum VERSION_STATUS: string
{
  case IN_REVIEW = 'IN_REVIEW';
  case CHANGES_REQUESTED = 'CHANGES_REQUESTED';
  case APPROVED = 'APPROVED';
}
