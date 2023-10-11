<?php

namespace Payavel\Orchestration\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Payavel\Orchestration\DataTransferObjects\Service;
use Payavel\Orchestration\Traits\AsksQuestions;
use Payavel\Orchestration\Traits\GeneratesFiles;

class Install extends Command
{
    use AsksQuestions,
        GeneratesFiles;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service:install
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
        $studlyService = Str::studly($this->service->getId());

        if (! file_exists(config_path('orchestration.php'))) {
            $this->putFile(
                config_path('orchestration.php'),
                $this->makeFile(
                    $this->getStub('config-orchestration'),
                    [
                        'id' => $this->service->getId(),
                        'config' => Str::slug($this->service->getId()),
                    ]
                )
            );
        }

        Config::set('orchestration.services.' . $this->service->getId(), [
            'config' => Str::slug($this->service->getId()),
        ]);

        $this->putFile(
            app_path("Services/{$studlyService}/Contracts/{$studlyService}Requestor.php"),
            $this->makeFile(
                $this->getStub('service-requestor'),
                [
                    'Service' => $studlyService,
                ]
            )
        );

        $this->putFile(
            app_path("Services/{$studlyService}/Contracts/{$studlyService}Responder.php"),
            $this->makeFile(
                $this->getStub('service-responder'),
                [
                    'Service' => $studlyService,
                ]
            )
        );

        $this->putFile(
            config_path(Str::slug($this->service->getId()) . '.php'),
            $this->makeFile(
                $this->getStub('config-service'),
                [
                    'Title' => $this->service->getName(),
                    'Service' => Str::studly($this->service->getId()),
                    'service' => Str::lower($this->service->getName()),
                    'SERVICE' => Str::upper(Str::slug($this->service->getId(), '_')),
                    'provider' => $this->config['defaults']['provider'],
                    'providers' => $this->config['providers'],
                    'merchant' => $this->config['defaults']['merchant'],
                    'merchants' => $this->config['merchants'],
                    'additional' => $this->getAdditionalConfig(),
                ]
            )
        );

        $this->info('The ' . Str::lower($this->service->getName()) . ' config has been successfully generated.');
    }

    protected function generateProviders()
    {
        $this->call("service:provider", ['--service' => $this->service->getId(), '--fake' => true]);

        $this->providers->each(
            fn ($provider) =>  $this->call(
                "service:provider",
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
                $this->makeFile(
                    $this->getStub('config-service-provider'),
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
                    $this->makeFile(
                        $this->getStub('config-service-merchant-providers'),
                        ['id' => $provider]
                    ) .
                    ($index < count($providers) - 1 ? "\n" : ""),
                ""
            );

            $this->merchants->push($merchant);
        } while ($this->confirm('Would you like to add another ' . Str::lower($this->service->getName()) . ' merchant?', false));

        $this->config['merchants'] = $this->merchants->reduce(
            fn ($config, $merchant) =>
                $config . $this->makeFile(
                    $this->getStub('config-service-merchant'),
                    $merchant
            ),
            ""
        );

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
