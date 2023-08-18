<?php

namespace Payavel\Serviceable\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Payavel\Serviceable\DataTransferObjects\Service;
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
     * The serviceable to be saved.
     *
     * @var \Payavel\Serviceable\Contracts\Serviceable
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

        if (! file_exists(config_path('serviceable.php'))) {
            $this->putFile(
                config_path('serviceable.php'),
                $this->makeFile(
                    __DIR__ . '/../../../stubs/config-serviceable.stub',
                    [
                        'id' => $this->service->getId(),
                        'name' => $this->service->getName(),
                        'config' => Str::slug($this->service->getName()),
                    ]
                )
            );
        }

        Config::set('serviceable.services.' . $this->service->getId(), [
            'name' => $this->service->getName(),
            'config' => Str::slug($this->service->getName()),
        ]);

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
            config_path(Str::slug($this->service->getName()) . '.php'),
            $this->makeFile(
                __DIR__ . '/../../../stubs/config-service.stub',
                [
                    'Title' => Str::title($this->service->getName()),
                    'Service' => Str::studly($this->service->getId()),
                    'service' => Str::lower($this->service->getName()),
                    'SERVICE' => Str::upper(Str::slug($this->service->getName(), '_')),
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

        $this->providers->each(function ($provider) {
            $this->call(
                "service:provider",
                [
                    'provider' => $provider['name'],
                    '--service' => $this->service->getId(),
                    '--id' => $provider['id']
                ]
            );
        });
    }

    protected function setService()
    {
        $this->service = new Service([
            'name' => $name = trim($this->argument('service') ?? $this->askName('service')),
            'id' => ($this->option('id') ?? $this->askId('service', $name)),
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
            $this->providers->push([
                'name' => $name = $this->askName('provider'),
                'id' => $id = $this->askId('provider', $name),
                'Provider' => Str::studly($id),
                'Service' => Str::studly($this->service->getId()),
            ]);
        } while ($this->confirm('Would you like to add another '. Str::lower($this->service->getName()) .' provider?', false));

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
        } while ($this->confirm('Would you like to add another ' . Str::lower($this->service->getName()) . ' merchant?', false));

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
