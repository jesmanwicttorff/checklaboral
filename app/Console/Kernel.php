<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\BackupAccesos;
use App\Console\Commands\EmailInforme;
use App\Console\Commands\PersonasMutual;
use App\Console\Commands\f30Tesoreria;
use App\Console\Commands\LevantaSolicitudesPeriodicas;
use App\Console\Commands\MaestroMovil;
use App\Console\Commands\ReporteAccesoFaena;
use App\Console\Commands\ReporteAccesoFaenaEmail;


class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        BackupAccesos::class,
        EmailInforme::class,
        f30Tesoreria::class,
        LevantaSolicitudesPeriodicas::class,
        MaestroMovil::class,
        ReporteAccesoFaena::class,
        ReporteAccesoFaenaEmail::class
        /*PersonasMutual::class*/
       /* MoveraHistoricos::class*/

    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('RespaldoAccesos')->dailyAt('02:00');
        $schedule->command('EmailInforme')->dailyAt('10:30');
        $schedule->command('EmailInforme')->dailyAt('12:30');
        $schedule->command('levante')->monthlyOn(1, '1:00');
        $schedule->command('MaestroMovil')->monthlyOn(1, '0:10');
        $schedule->command('ReporteAccesoFaena')->everyFiveMinutes();
        $schedule->command('ReporteAccesoFaenaEmail')->dailyAt('9:00');
    }
}
