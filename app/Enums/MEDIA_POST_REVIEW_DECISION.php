<?php

namespace App\Enums;

enum MEDIA_POST_REVIEW_DECISION: string
{
  case APPROVED = 'APPROVED';
  case REVISION_REQUESTED = 'REVISION_REQUESTED';
}
