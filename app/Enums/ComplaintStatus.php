<?php

namespace App\Enums;

enum ComplaintStatus: string
{
    case NEW = 'New';
    case IN_PROGRESS = 'In Progress';
    case RESOLVED = 'Resolved';
    case REJECTED = 'Rejected';
    case REQUESTED_INFO = 'Requested Info';

   
}