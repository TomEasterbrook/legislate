<?php

namespace App;

enum GameType: string
{
    case Local = 'local';
    case Multiplayer = 'multiplayer';
}
