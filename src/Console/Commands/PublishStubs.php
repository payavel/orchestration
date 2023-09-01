<?php

namespace Payavel\Serviceable\Console\Commands;

use Illuminate\Console\Command;
use Payavel\Serviceable\Traits\GeneratesFiles;

class PublishStubs extends Command
{
    use GeneratesFiles;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service:stubs
                            {stub? : The stub file}
                            {--service= : The service}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publishes this package\'s stub files.';

    /**
     * Stubs that can be overridden on the serviceable level.
     *
     * @var string[]
     */
    protected $stubs = [
      'config-service',
      'config-service-merchant',
      'config-service-merchant-providers',
      'config-service-provider',
      'service-request',
      'service-requestor',
      'service-responder',
      'service-response',
    ];

    /**
     * Stubs that can be overridden on a service level.
     *
     * @var string[]
     */
    protected $serviceStubs = [
        'service-request',
        'service-response',
    ];

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $stubs = is_null($this->option('service'))
            ? $this->stubs
            : $this->serviceStubs;

        if (! is_null($this->argument('stub'))) {
            if (! in_array($this->argument('stub'), $stubs)) {
                $this->error('The stub file you wish to publish is not available.');

                return;
            }

            $stubs = [$this->option('service')];
        }

        $path = base_path(
            'stubs/serviceable' .
            is_null($this->option('service'))
                ? ''
                : ('/' . $this->option('service'))
        );

        foreach($stubs as $stub) {
            $this->putFile(
                $path,
                __DIR__ . '/../../../stubs/' . $stub . '.stub'
            );
        }
    }
}
