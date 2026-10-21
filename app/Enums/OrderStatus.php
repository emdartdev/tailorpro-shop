<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case DELIVERED = 'delivered';
    case DUE_PAYMENT = 'due_payment';
}
