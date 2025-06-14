<?php

namespace App\Listeners;

use App\Events\NewCustomerCreated;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\GenerateController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateVirtualAccount
{
    
    protected $ApiController;
    protected $GenerateController;

    public function __construct(ApiController $ApiController, GenerateController $generateController)
    {
        $this->ApiController = $ApiController;
        $this->GenerateController = $generateController;
    }


    /**
     * Handle the event.
     */
    public function handle(NewCustomerCreated $event): void
    {
        $user = $event->user;
        $ref = $this->GenerateController->GenerateVirtualAccountReference();

        // Make Api

    }
}
