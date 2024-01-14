<?php

namespace Payavel\Orchestration\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Payavel\Orchestration\DataTransferObjects\Service;
use Payavel\Orchestration\Traits\AsksQuestions;
use Payavel\Orchestration\Traits\GeneratesFiles;

class OrchestrateService extends Command
{
    use AsksQuestions,
        GeneratesFiles;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orchestrate:service
                            {service? : The service}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install a new service within the application.';

    /**
     * The serviceable to be saved.
     *
     * @var \Payavel\Orchestration\Contracts\Serviceable
     */
    protected $service;

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

        $this->makeSureOrchestraIsReady();

        $this->generateService();

        $this->generateProviders();
    }

    protected function setProperties()
    {
        $this->setService();
        $this->setDriver();
        $this->setProviders();
        $this->setMerchants();
    }

    protected  function generateService()
    {
        $studlyService = Str::studly($this->service->getId());

        Config::set('orchestration.services.' . $this->service->getId(), Str::slug($this->service->getId()));

        static::putFile(
            app_path("Services/{$studlyService}/Contracts/{$studlyService}Requester.php"),
            static::makeFile(
                static::getStub('service-requester'),
                [
                    'Service' => $studlyService,
                ]
            )
        );

        static::putFile(
            app_path("Services/{$studlyService}/Contracts/{$studlyService}Responder.php"),
            static::makeFile(
                static::getStub('service-responder'),
                [
                    'Service' => $studlyService,
                ]
            )
        );

        $this->driver::generateService($this->service, $this->config);

        $this->info('The ' . Str::lower($this->service->getName()) . ' config has been successfully generated.');
    }

    protected function generateProviders()
    {
        $this->call("orchestrate:provider", ['--service' => $this->service->getId(), '--fake' => true]);

        $this->providers->each(
            fn ($provider) =>  $this->call(
                "orchestrate:provider",
                [
                    'provider' => $provider['id'],
                    '--service' => $this->service->getId(),
                ]
            )
        );
    }

    protected function setService()
    {
        $name = trim($this->argument('service') ?? $this->askName('service'));

        $this->service = new Service([
            'id' => $this->askId('service', $name),
        ]);
    }

    protected function setDriver()
    {
        $driver = trim(
            $this->choice(
                'Which driver will handle the ' . $this->service->getName() . ' service?',
                array_keys(Config::get('orchestration.drivers')),
                'config'
            )
        );

        $this->driver = Config::get('orchestration.drivers.' . $driver);
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
            $name = $this->askName('provider');

            $this->providers->push([
                'id' => $id = $this->askId('provider', $name),
                'Provider' => Str::studly($id),
                'Service' => Str::studly($this->service->getId()),
            ]);
        } while ($this->confirm('Would you like to add another '. Str::lower($this->service->getName()) .' provider?', false));

        $this->config['providers'] = $this->providers->reduce(
            fn ($config, $provider) =>
                $config .
                static::makeFile(
                    static::getStub('config-service-provider'),
                    $provider
                ),
            ""
        );

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
            $name = $this->askName('merchant');

            $merchant = [
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

            $merchant['providers'] = collect($providers)->reduce(
                fn ($config, $provider, $index) =>
                    $config .
                    static::makeFile(
                        static::getStub('config-service-merchant-providers'),
                        ['id' => $provider]
                    ) .
                    ($index < count($providers) - 1 ? "\n" : ""),
                ""
            );

            $this->merchants->push($merchant);
        } while ($this->confirm('Would you like to add another ' . Str::lower($this->service->getName()) . ' merchant?', false));

        $this->config['merchants'] = $this->merchants->reduce(
            fn ($config, $merchant) =>
                $config . static::makeFile(
                    static::getStub('config-service-merchant'),
                    $merchant
            ),
            ""
        );

        $this->config['defaults']['merchant'] = $this->merchants->count() > 1
            ? $this->choice('Which merchant will be used as default?', $this->merchants->pluck('id')->all())
            : $this->merchants->first()['id'];
    }

    protected function makeSureOrchestraIsReady()
    {
        if (file_exists(config_path('orchestration.php'))) {
            return;
        }

        static::putFile(
            config_path('orchestration.php'),
            static::makeFile(
                static::getStub('config-orchestration'),
                [
                    'driver' => $this->choice('Choose a default driver for your orchestra.', array_keys(Config::get('orchestration.drivers'))),
                    'id' => $this->service->getId(),
                    'config' => Str::slug($this->service->getId()),
                ]
            )
        );
    }
}
