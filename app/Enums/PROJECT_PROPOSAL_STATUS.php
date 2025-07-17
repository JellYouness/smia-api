<?php

namespace App\Enums;

enum PROJECT_PROPOSAL_STATUS: string
{
  case PENDING = 'PENDING';
  case WITHDRAWN = 'WITHDRAWN';
  case REJECTED = 'REJECTED';
  case ACCEPTED = 'ACCEPTED';
}
