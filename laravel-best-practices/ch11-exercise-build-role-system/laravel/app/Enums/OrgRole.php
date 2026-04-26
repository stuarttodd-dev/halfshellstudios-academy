<?php

namespace App\Enums;

enum OrgRole: string
{
    case Admin = 'admin';
    case Editor = 'editor';
    case Viewer = 'viewer';
}
