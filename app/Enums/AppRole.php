<?php

namespace App\Enums;

enum AppRole: string
{
    case ADMIN = 'admin';
    case EDITOR = 'editor';
    case VIEWER = 'viewer';
}
