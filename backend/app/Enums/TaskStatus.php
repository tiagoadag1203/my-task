<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Backlog = 'BACKLOG';
    case Todo = 'TODO';
    case InProgress = 'IN_PROGRESS';
    case Done = 'DONE';
}
