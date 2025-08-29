<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes & Schedule
|--------------------------------------------------------------------------
| Definimos tareas programadas aquí para que schedule:list
| siempre las detecte (recomendado por claridad).
*/

Schedule::command('powerbi:capacities:import')
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('powerbi:capacities:evaluate')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->onOneServer();
