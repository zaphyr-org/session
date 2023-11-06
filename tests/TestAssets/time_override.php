<?php

declare(strict_types=1);

namespace Zaphyr\Session\Handler;

use Zaphyr\SessionTests\TestAssets\Time;

function time(): int
{
    return Time::$now ?? \time();
}
