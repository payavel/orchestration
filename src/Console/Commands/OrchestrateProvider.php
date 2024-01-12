<?php

namespace Payavel\Orchestration\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Payavel\Orchestration\Service;
use Payavel\Orchestration\Traits\AsksQuestions;
use Payavel\Orchestration\Traits\GeneratesFiles;
use Payavel\Orchestration\Traits\ServesConfig;

class OrchestrateProvider extends Command
{
    use AsksQuestions,
        GeneratesFiles,
        ServesConfig;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orchestrate:provider
                            {provider? : The provider}
                            {--service= : The service}
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
     * @var \Payavel\Orchestration\Contracts\Serviceable
     */
    protected $service;

    /**
     * The service provider's id'.
     *
     * @var string
     */
    protected $id;

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
            $this->id = 'fake';

            return true;
        }

        $name = trim($this->argument('provider') ?? $this->askName('provider'));

        $this->id = $this->askId('provider', $name);

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
                $this->getStub('service-request', $this->service->getId()),
                [
                    'Provider' => $provider,
                    'Service' => $service,
                ]
            )
        );

        $this->putFile(
            app_path("Services/{$service}/{$provider}{$service}Response.php"),
            $this->makeFile(
                $this->getStub('service-response', $this->service->getId()),
                [
                    'Provider' => $provider,
                    'Service' => $service,
                ]
            )
        );

        $this->info(Str::headline($this->id) . ' ' . $this->service->getid() . ' gateway generated successfully!');
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
            $this->error('Your must first set up a service! Please call the orchestrate:service artisan command.');

            return false;
        }

        $this->service = $service;

        return true;
    }
}
