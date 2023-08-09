<?php

namespace Payavel\Serviceable\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Payavel\Serviceable\Service;
use Payavel\Serviceable\Traits\GeneratesFiles;
use Payavel\Serviceable\Traits\Questionable;

class MakeProvider extends Command
{
    use Questionable, GeneratesFiles;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service:provider
                            {provider? : The provider name}
                            {--service= : The service name}
                            {--id= : The provider identifier}
                            {--fake : Generates a gateway to be used for testing purposes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scaffold a new service provider\'s gateway request and response classes.';

    /**
     * The provider's service.
     *
     * @var string
     */
    protected $service;

    /**
     * The service provider attributes to be saved.
     *
     * @var string $name
     * @var string $id
     */
    protected $name, $id;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->setProperties();

        $this->generateProvider();
    }

    /**
     * Format the service provider's properties.
     *
     * @return void
     */
    protected function setProperties()
    {
        $this->service = $this->option('service')
            ?? $this->choice(
                'Which service will the provider be offering?',
                Service::ids()
            );

        if ($this->option('fake', false)) {
            $this->name = 'Fake';
            $this->id = 'fake';

            return;
        }

        $this->name = trim($this->argument('provider') ?? $this->askName('provider'));

        $this->id = $this->option('id') ?? $this->askId('provider', $this->name);
    }

    /**
     * Generated the provider gateway files.
     *
     * @return void
     */
    protected function generateProvider()
    {
        $service = Str::studly($this->service);
        $provider = Str::studly($this->id);

        $this->putFile(
            app_path("Services/{$service}/{$provider}{$service}Request.php"),
            $this->makeFile(
                config($this->service . '.stubs.request', __DIR__ . '/../../../stubs/service-request.stub'),
                [
                    'Provider' => $provider,
                    'Service' => $service,
                ]
            )
        );

        $this->putFile(
            app_path("Services/{$service}/{$provider}{$service}Response.php"),
            $this->makeFile(
                config($this->service . '.stubs.response', __DIR__ . '/../../../stubs/service-response.stub'),
                [
                    'Provider' => $provider,
                    'Service' => $service,
                ]
            )
        );

        $this->info("{$this->name} {$this->service} gateway generated successfully!");
    }
}
