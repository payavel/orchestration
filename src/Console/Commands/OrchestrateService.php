<?php

namespace Payavel\Orchestration\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Payavel\Orchestration\ServiceConfig;
use Payavel\Orchestration\Traits\AsksQuestions;
use Payavel\Orchestration\Traits\GeneratesFiles;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\select;
use function Illuminate\Filesystem\join_paths;

class OrchestrateService extends Command
{
    use AsksQuestions;
    use GeneratesFiles;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orchestrate:service
                            {service? : The service name}
                            {--id= : The service ID }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install a new service into the application.';

    /**
     * The service config.
     *
     * @var \Payavel\Orchestration\ServiceConfig
     */
    protected $serviceConfig;

    /**
     * The driver to execute the new service.
     *
     * @var Payavel\Orchestration\ServiceDriver
     */
    protected $driver;

    /**
     * The collected providers.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $providers;

    /**
     * The collected accounts.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $accounts;

    /**
     * The defaults to be set.
     *
     * @var array
     */
    protected $defaults = [];

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->setProperties();

        $this->makeSureOrchestraIsReady();

        $this->generateService();

        $this->generateProviders();
    }

    /**
     * Sets the properties necessary to handle this command.
     *
     * @return void
     */
    protected function setProperties()
    {
        $this->setServiceConfig();
        $this->setDriver();
        $this->setProviders();
        $this->setAccounts();
    }

    /**
     * Generates the service skeleton.
     *
     * @return void
     */
    protected function generateService()
    {
        $this->driver::generateService($this->serviceConfig, $this->providers, $this->accounts, $this->defaults);

        $studlyService = Str::studly($this->serviceConfig->id);

        static::putFile(
            app_path($requesterPath = join_paths('Services', $studlyService, 'Contracts', "{$studlyService}Requester.php")),
            static::makeFile(
                static::getStub('service-requester', $this->serviceConfig->id),
                [
                    'Service' => $studlyService,
                ]
            )
        );

        info('Contract ['.join_paths('app', $requesterPath).'] created successfully.');

        static::putFile(
            app_path($responderPath = join_paths('Services', $studlyService, 'Contracts', "{$studlyService}Responder.php")),
            static::makeFile(
                static::getStub('service-responder', $this->serviceConfig->id),
                [
                    'Service' => $studlyService,
                ]
            )
        );

        info('Contract ['.join_paths('app', $responderPath).'] created successfully.');
    }

    /**
     * Generates the service implementation for each provider.
     *
     * @return void
     */
    protected function generateProviders()
    {
        $this->call("orchestrate:provider", ['--service' => $this->serviceConfig->id, '--fake' => true]);

        $this->providers->each(
            fn ($provider) =>  $this->call(
                "orchestrate:provider",
                [
                    'provider' => $provider['id'],
                    '--service' => $this->serviceConfig->id,
                ]
            )
        );
    }

    /**
     * Request service information and set the config.
     *
     * @return void
     */
    protected function setServiceConfig()
    {
        $name = trim($this->argument('service') ?? $this->askName('service'));
        $id = $this->option('id') ?? $this->askId('service', $name);

        Config::set("orchestration.services.{$id}", Str::slug($id));

        $this->serviceConfig = tap(ServiceConfig::find($id), fn ($serviceConfig) => $serviceConfig->set('name', $name));
    }

    /**
     * Query for driver information and set the corresponding class.
     *
     * @return void
     */
    protected function setDriver()
    {
        $this->defaults['driver'] = select(
            label: "Choose a driver for the {$this->serviceConfig->name} service.",
            options: array_keys(Config::get('orchestration.drivers')),
            default: 'config'
        );

        $this->driver = Config::get("orchestration.drivers.{$this->defaults['driver']}");
    }

    /**
     * Query for provider information, generate the service & set the config for chosen providers.
     *
     * @return void
     */
    protected function setProviders()
    {
        $this->providers = collect([]);

        $studlyService = Str::studly($this->serviceConfig->id);

        do {
            $provider = [
                'name' => $name = $this->askName('provider'),
                'id' => $id = $this->askId('provider', $name),
            ];

            $studlyProvider = Str::studly($id);

            $provider['gateway'] = "\\App\\Services\\{$studlyService}\\{$studlyProvider}{$studlyService}Request";

            $this->providers->push($provider);
        } while (confirm(label: "Would you like to add another {$this->serviceConfig->name} provider?", default: false));

        $this->defaults['provider'] = $this->providers->count() > 1
            ? select(label: "Choose a default provider for the {$this->serviceConfig->name} service.", options: $this->providers->pluck('id')->all())
            : $this->providers->first()['id'];
    }

    /**
     * Query account information & set the config for chosen accounts.
     *
     * @return void
     */
    protected function setAccounts()
    {
        $this->accounts = collect([]);

        do {
            $account = [
                'name' => $name = $this->askName('account'),
                'id' => $this->askId('account', $name),
                'providers' => $this->providers->count() > 1
                    ? multiselect(
                        label: "Choose one or more {$this->serviceConfig->name} providers for the {$name} account.",
                        options: $this->providers->pluck('id')->all(),
                        required: true
                    )
                    : [$this->providers->first()['id']],
            ];

            $this->accounts->push($account);
        } while (confirm(label: "Would you like to add another {$this->serviceConfig->name} account?", default: false));

        $this->defaults['account'] = $this->accounts->count() > 1
            ? select(label: 'Which account will be used as default?', options: $this->accounts->pluck('id')->all())
            : $this->accounts->first()['id'];
    }

    /**
     * If orchestration config file does not exist yet, generate it.
     *
     * @return void
     */
    protected function makeSureOrchestraIsReady()
    {
        if (file_exists(config_path('orchestration.php'))) {
            return;
        }

        static::putFile(
            config_path($configPath = 'orchestration.php'),
            static::makeFile(
                static::getStub('config-orchestration'),
                [
                    'id' => $this->serviceConfig->id,
                    'config' => Str::slug($this->serviceConfig->id),
                ]
            )
        );

        info('Config ['.join_paths('config', $configPath).'] created successfully.');
    }
}
