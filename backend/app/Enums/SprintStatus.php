<?php

namespace App\Enums;

enum SprintStatus: string
{
    case Planning = 'PLANNING';
    case Active = 'ACTIVE';
    case Finished = 'FINISHED';
}
