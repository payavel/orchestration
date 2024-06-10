<?php

namespace Payavel\Orchestration\Tests\Unit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Payavel\Orchestration\Contracts\Accountable;
use Payavel\Orchestration\Contracts\Orchestrable;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\Service;
use Payavel\Orchestration\Tests\Contracts\CreatesServiceables;
use Payavel\Orchestration\Tests\TestCase;
use Payavel\Orchestration\Tests\Traits\CreatesServices;
use Payavel\Orchestration\Traits\OrchestratesService;
use PHPUnit\Framework\Attributes\Test;

abstract class TestOrchestratesServiceTrait extends TestCase implements CreatesServiceables
{
    use CreatesServices;

    /**
     * The model instance to test the trait functionalities.
     *
     * @var FakeModel
     */
    protected $fakeModel;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('fake_models', function ($table) {
            $table->id();
            $table->string('service_id');
            $table->string('provider_id');
            $table->string('account_id');
            $table->timestamps();
        });

        $service = $this->createService();
        $provider = $this->createProvider($service);
        $account = $this->createAccount($service);
        $this->linkAccountToProvider($account, $provider);
        $this->setDefaultsForService($service, $account, $provider);

        $this->fakeModel = FakeModel::create([
            'service_id' => $service->getId(),
            'provider_id' => $provider->getId(),
            'account_id' => $account->getId(),
        ]);
    }

    #[Test]
    public function get_service_returns_service()
    {
        $this->assertInstanceOf(Service::class, $this->fakeModel->getService());
    }

    #[Test]
    public function get_provider_returns_providable()
    {
        $this->assertInstanceOf(Providable::class, $this->fakeModel->getProvider());
    }

    #[Test]
    public function get_provider_returns_accountable()
    {
        $this->assertInstanceOf(Accountable::class, $this->fakeModel->getAccount());
    }

    #[Test]
    public function calling_service_attribute_on_orchestrable_model_returns_service()
    {
        $this->assertInstanceOf(Service::class, $this->fakeModel->service);
    }
}

class FakeModel extends Model implements Orchestrable
{
    use OrchestratesService;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = ['id'];

    /**
     * The orchestra's service id.
     *
     * @var string
     */
    public $serviceId;
}
