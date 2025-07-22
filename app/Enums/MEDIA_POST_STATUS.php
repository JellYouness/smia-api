<?php

namespace App\Enums;

enum MEDIA_POST_STATUS: string
{
    case BACKLOG = 'BACKLOG';
    case IN_PROGRESS = 'IN_PROGRESS';
    case UNDER_REVIEW = 'UNDER_REVIEW';
    case APPROVED = 'APPROVED';
    case ARCHIVED = 'ARCHIVED';
} 