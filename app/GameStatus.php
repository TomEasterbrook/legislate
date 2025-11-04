<?php

namespace App;

enum GameStatus: string
{
    case Waiting = 'waiting';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
