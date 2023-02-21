<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ParseaPrevired extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct( $id )
    {
        //
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $lobjDoc = new \App\Library\MyDocumentsPrevired($this->id);
        $datosDoc = $lobjDoc->parsea();
        \Log::info("Se generó un JOB ");
    }
}
