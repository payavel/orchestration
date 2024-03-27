<?php

namespace Payavel\Orchestration\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Payavel\Orchestration\Traits\GeneratesFiles;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;

class OrchestrateStubs extends Command
{
    use GeneratesFiles;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orchestrate:stubs
                            {stub? : The stub file}
                            {--service= : The service}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publishes this package\'s stub files.';

    /**
     * Stubs that can be overridden on the orchestration level.
     *
     * @var string[]
     */
    public static $baseStubs = [
      'config-service',
      'config-service-database',
      'config-service-merchant',
      'config-service-merchant-providers',
      'config-service-provider',
      'migration-service',
      'migration-service-merchants',
      'migration-service-providers',
      'service-request',
      'service-requester',
      'service-responder',
      'service-response',
    ];

    /**
     * Stubs that can be overridden on a service level.
     *
     * @var string[]
     */
    public static $serviceSpecificStubs = [
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
            ? static::$baseStubs
            : static::$serviceSpecificStubs;

        if (! is_null($this->argument('stub'))) {
            if (! in_array($this->argument('stub'), $stubs)) {
                error('The stub file you wish to publish is not available.');

                return;
            }

            $stubs = [$this->argument('stub')];
        }

        $directory = 'stubs/orchestration' . (
            is_null($this->option('service'))
                ? ''
                : ('/' . $this->option('service'))
        );

        foreach($stubs as $stub) {
            static::putFile(
                base_path($directory . '/' . $stub . '.stub'),
                file_get_contents(__DIR__ . '/../../../stubs/' . $stub . '.stub')
            );
        }

        info('Successfully published ' . Str::plural('stub', count($stubs)) . '!');
    }
}
