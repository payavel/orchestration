<?php

namespace Payavel\Serviceable\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Payavel\Serviceable\Service;
use Payavel\Serviceable\Traits\GenerateFiles;
use Payavel\Serviceable\Traits\AskQuestions;
use Payavel\Serviceable\Traits\ServiceConfigs;

class MakeProvider extends Command
{
    use AskQuestions,
        GenerateFiles,
        ServiceConfigs;

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
        if(! $this->setProperties()) {
            return;
        }

        $this->generateProvider();
    }

    /**
     * Format the service provider's properties.
     *
     * @return bool
     */
    protected function setProperties()
    {
        if (! $this->setService()) {
            return false;
        }

        if ($this->option('fake', false)) {
            $this->name = 'Fake';
            $this->id = 'fake';

            return true;
        }

        $this->name = trim($this->argument('provider') ?? $this->askName('provider'));

        $this->id = $this->option('id') ?? $this->askId('provider', $this->name);

        return true;
    }

    /**
     * Generated the provider gateway files.
     *
     * @return void
     */
    protected function generateProvider()
    {
        $service = Str::studly($this->service->getId());
        $provider = Str::studly($this->id);

        $this->putFile(
            app_path("Services/{$service}/{$provider}{$service}Request.php"),
            $this->makeFile(
                $this->config($this->service->getId(), 'stubs.request', __DIR__ . '/../../../stubs/service-request.stub'),
                [
                    'Provider' => $provider,
                    'Service' => $service,
                ]
            )
        );

        $this->putFile(
            app_path("Services/{$service}/{$provider}{$service}Response.php"),
            $this->makeFile(
                $this->config($this->service->getId(), 'stubs.response', __DIR__ . '/../../../stubs/service-response.stub'),
                [
                    'Provider' => $provider,
                    'Service' => $service,
                ]
            )
        );

        $this->info("{$this->name} {$this->service->getid()} gateway generated successfully!");
    }

    /**
     * Set the service property.
     *
     * @return bool
     */
    protected function setService()
    {
        if (! is_null($this->option('service')) && is_null($service = Service::find($this->option('service')))) {
            $this->error("Service with id {$this->option('service')} does not exist.");

            return false;
        } else if (! isset($service) && ($existingServices = Service::all())->isNotEmpty()) {
            $id = $this->choice(
                'Which service will the provider be offering?',
                $existingServices->map(fn ($existingService) => $existingService->getId())->all()
            );

            $service = $existingServices->all()[$id];
        }

        if (! isset($service)) {
            $this->error('Your must first set up a service! Please call the service:install artisan command.');

            return false;
        }

        $this->service = $service;

        return true;
    }
}
