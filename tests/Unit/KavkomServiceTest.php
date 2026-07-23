<?php

namespace Tests\Unit;

use App\Services\KavkomService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class KavkomServiceTest extends TestCase
{
    public function test_it_returns_success_when_kavkom_accepts_the_credentials(): void
    {
        Http::fake([
            'https://api.kavkom.com/api/pbx/v1/extension/list*' => Http::response([
                'success' => true,
                'data' => [['id' => '1001']],
            ], 200),
        ]);

        $service = new KavkomService();

        $result = $service->testConnection('test-token', 'domain-uuid');

        $this->assertTrue($result['success']);
        $this->assertSame('Connexion Kavkom réussie. Le jeton et le domain_uuid sont valides.', $result['message']);
        $this->assertSame(1, $result['details']['extensionsCount']);
    }

    public function test_it_originates_a_click_to_call(): void
    {
        Http::fake([
            'https://api.kavkom.com/api/pbx/v1/active_call/call*' => Http::response([
                'success' => true,
                'call_uuid' => 'a1b2c3d4',
            ], 200),
        ]);

        $service = new KavkomService();

        $result = $service->originateCall('test-token', 'domain-uuid', '0611223344', '0699887766');

        $this->assertTrue($result['success']);
        $this->assertSame('a1b2c3d4', $result['call_uuid']);
    }

    public function test_it_fails_to_originate_a_call_without_source_number(): void
    {
        $service = new KavkomService();

        $result = $service->originateCall('test-token', 'domain-uuid', '', '0699887766');

        $this->assertFalse($result['success']);
    }

    public function test_it_resolves_the_first_enabled_extension_of_the_domain(): void
    {
        Http::fake([
            'https://api.kavkom.com/api/pbx/v1/extension/list*' => Http::response([
                'success' => true,
                'data' => [
                    ['extension' => '900', 'enabled' => 'false'],
                    ['extension' => '901', 'enabled' => 'true', 'password' => 'sip-secret', 'user_context' => 'client.kavkom.com'],
                ],
            ], 200),
        ]);

        $service = new KavkomService();

        $result = $service->resolveExtension('test-token', 'domain-uuid');

        $this->assertTrue($result['success']);
        $this->assertSame('901', $result['extension']);
        $this->assertSame('sip-secret', $result['password']);
        $this->assertSame('client.kavkom.com', $result['user_context']);
    }

    public function test_it_fails_to_resolve_extension_when_domain_has_none(): void
    {
        Http::fake([
            'https://api.kavkom.com/api/pbx/v1/extension/list*' => Http::response([
                'success' => true,
                'data' => [],
            ], 200),
        ]);

        $service = new KavkomService();

        $result = $service->resolveExtension('test-token', 'domain-uuid');

        $this->assertFalse($result['success']);
    }
}
