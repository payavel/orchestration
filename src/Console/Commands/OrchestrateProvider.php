<?php

namespace Payavel\Orchestration\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Payavel\Orchestration\ServiceConfig;
use Payavel\Orchestration\Traits\AsksQuestions;
use Payavel\Orchestration\Traits\GeneratesFiles;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\select;
use function Illuminate\Filesystem\join_paths;

class OrchestrateProvider extends Command
{
    use AsksQuestions;
    use GeneratesFiles;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orchestrate:provider
                            {provider? : The provider name}
                            {--id= : The provider ID}
                            {--service= : The service ID}
                            {--fake : Generates a gateway to be used for testing purposes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scaffold a new service provider\'s gateway request and response classes.';

    /**
     * The provider's service config.
     *
     * @var \Payavel\Orchestration\ServiceConfig
     */
    protected ServiceConfig $serviceConfig;

    /**
     * The service provider's name.
     *
     * @var string
     */
    protected string $providerName;

    /**
     * The service provider's id.
     *
     * @var string
     */
    protected string $providerId;

    /**
     * Executes the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        if (! $this->setProperties()) {
            return;
        }

        $this->generateProvider();
    }

    /**
     * Formats the service provider's properties.
     *
     * @return bool
     */
    protected function setProperties(): bool
    {
        if (! $this->setService()) {
            return false;
        }

        if ($this->option('fake', false)) {
            $this->providerName = 'Fake';
            $this->providerId = 'fake';

            return true;
        }

        $this->providerName = trim($this->argument('provider') ?? $this->askName('provider'));

        $this->providerId = $this->option('id') ?? $this->askId('provider', $this->providerName);

        return true;
    }

    /**
     * Generates the provider gateway files.
     *
     * @return void
     */
    protected function generateProvider(): void
    {
        $service = Str::studly($this->serviceConfig->id);
        $provider = Str::studly($this->providerId);

        static::putFile(
            app_path($requestPath = join_paths('Services', $service, "{$provider}{$service}Request.php")),
            static::makeFile(
                static::getStub('service-request', $this->serviceConfig->id),
                [
                    'Provider' => $provider,
                    'Service' => $service,
                ]
            )
        );

        info('Gateway ['.join_paths('app', $requestPath).'] created successfully.');

        static::putFile(
            app_path($responsePath = join_paths('Services', $service, "{$provider}{$service}Response.php")),
            static::makeFile(
                static::getStub('service-response', $this->serviceConfig->id),
                [
                    'Provider' => $provider,
                    'Service' => $service,
                ]
            )
        );

        info('Gateway ['.join_paths('app', $responsePath).'] created successfully.');
    }

    /**
     * Sets the service property.
     *
     * @return bool
     */
    protected function setService(): bool
    {
        if (! is_null($this->option('service')) && is_null($serviceConfig = ServiceConfig::find($this->option('service')))) {
            error("Service with id {$this->option('service')} does not exist.");

            return false;
        } elseif (! isset($serviceConfig) && ($serviceConfigs = ServiceConfig::all())->isNotEmpty()) {
            $index = select(
                label: 'Which service will the provider be offering?',
                options: $serviceConfigs->map(fn ($serviceConfig) => $serviceConfig->id)->all()
            );

            $serviceConfig = $serviceConfigs->get($index);
        }

        if (! isset($serviceConfig)) {
            error('Your must first set up a service! Please call the orchestrate:service artisan command.');

            return false;
        }

        $this->serviceConfig = $serviceConfig;

        return true;
    }
}
