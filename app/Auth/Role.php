<?php

declare(strict_types=1);

namespace App\Auth;

enum Role: string
{
    case SuperAdmin = 'SuperAdmin';
    case RepoAdmin = 'RepoAdmin';
    case Staff = 'Staff';
    case User = 'User';
    case Guest = 'Guest';
}
