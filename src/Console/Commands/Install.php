<?php

namespace Payavel\Serviceable\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Payavel\Serviceable\Traits\GeneratesFiles;
use Payavel\Serviceable\Traits\Questionable;

class Install extends Command
{
    use Questionable, GeneratesFiles;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service:install
                            {service? : The service name}
                            {--id= : The service identifier}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install a new service within the application.';

    /**
     * The service to be installed.
     *
     * @var string
     */
    protected $service;

    /**
     * The service attributes to be saved.
     *
     * @var string $name
     * @var string $id
     */
    protected $name, $id;

    /**
     * The collected providers.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $providers;

    /**
     * The collected merchants.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $merchants;

    /**
     * The config to be set.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->setProperties();

        $this->generateService();

        $this->generateProviders();
    }

    protected function setProperties()
    {
        $this->setService();
        $this->setProviders();
        $this->setMerchants();
    }

    protected  function generateService()
    {
        $studlyService = Str::studly($this->service);

        $this->putFile(
            app_path("Services/{$studlyService}/Contracts/{$studlyService}Requestor"),
            $this->makeFile(
                __DIR__ . '/../../../stubs/service-requestor.stub',
                [
                    'Service' => $studlyService,
                ]
            )
        );

        $this->putFile(
            app_path("Services/{$studlyService}/Contracts/{$studlyService}Responder"),
            $this->makeFile(
                __DIR__ . '/../../../stubs/service-responder.stub',
                [
                    'Service' => $studlyService,
                ]
            )
        );

        $this->putFile(
            config_path(Str::slug($this->name) . '.php'),
            $this->makeFile(
                __DIR__ . '/../../../stubs/config-service.stub',
                [
                    'Service' => Str::title($this->name),
                    'service' => Str::lower($this->name),
                    'SERVICE' => Str::upper(Str::slug($this->name)),
                    'provider' => $this->config['defaults']['provider'],
                    'providers' => $this->config['providers'],
                    'merchant' => $this->config['defaults']['merchant'],
                    'merchants' => $this->config['merchants'],
                    'additional' => $this->getAdditionalConfig(),
                ]
            )
        );

        $this->info("The {$this->name} config has been successfully generated.");
    }

    protected function generateProviders()
    {
        $this->call("service:provider", ['--service' => $this->id, '--fake' => true]);

        $this->providers->each(function ($provider) {
            $this->call(
                "service:provider",
                [
                    'provider' => $provider['name'],
                    '--service' => $this->id,
                    '--id' => $provider['id']
                ]
            );
        });
    }

    protected function setService()
    {
        $this->name = trim($this->argument('service') ?? $this->askName('service'));
        $this->id = ($this->option('id') ?? $this->askId('service', $this->name));
        $this->service = Str::lower($this->name);
    }

    /**
     * Query for provider information, generate the service & set the config for chosen providers.
     *
     * @return void
     */
    protected function setProviders()
    {
        $this->providers = collect([]);

        do {
            $this->providers->push([
                'name' => $name = $this->askName('provider'),
                'id' => $id = $this->askId('provider', $name),
                'Provider' => Str::studly($id),
                'Service' => Str::studly($this->id),
            ]);
        } while ($this->confirm("Would you like to add another {$this->service} provider?", false));

        $this->config['providers'] = $this->providers->reduce(function ($config, $provider) {
            return $config . $this->makeFile(__DIR__ . '/../../../stubs/config-provider.stub', $provider);
        }, "");

        $this->config['defaults']['provider'] = $this->providers->count() > 1
            ? $this->choice('Which provider will be used as default?', $this->providers->pluck('id')->all())
            : $this->providers->first()['id'];
    }

    /**
     * Query merchant information & set the config for chosen merchants.
     *
     * @return void
     */
    protected function setMerchants()
    {
        $this->merchants = collect([]);

        do {
            $merchant = [
                'name' => $name = $this->askName('merchant'),
                'id' => $this->askId('merchant', $name),
            ];

            $providers = $this->providers->count() > 1
                ? $this->choice(
                    "Which providers will the {$name} merchant be integrating? (default first)",
                    $this->providers->pluck('id')->all(),
                    null,
                    null,
                    true
                )
                : [$this->providers->first()['id']];

            $merchant['providers'] = collect($providers)->reduce(function ($config, $provider, $index) use ($providers) {
                return $config . $this->makeFile(__DIR__ . '/../../../stubs/config-merchant-providers.stub', ['id' => $provider]) . ($index < count($providers) - 1 ? "\n" : "");
            }, "");

            $this->merchants->push($merchant);
        } while ($this->confirm("Would you like to add another {$this->service} merchant?", false));

        $this->config['merchants'] = $this->merchants->reduce(function ($config, $merchant) {
            return $config . $this->makeFile(__DIR__ . '/../../../stubs/config-merchant.stub', $merchant);
        }, "");

        $this->config['defaults']['merchant'] = $this->merchants->count() > 1
            ? $this->choice('Which merchant will be used as default?', $this->merchants->pluck('id')->all())
            : $this->merchants->first()['id'];
    }

    /**
     * Get additional config for the service.
     *
     * @return string
     */
    protected function getAdditionalConfig()
    {
        return '';
    }
}
