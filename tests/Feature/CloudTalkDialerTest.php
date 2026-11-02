<?php

namespace Tests\Feature;

use App\Models\Line;
use App\Models\Project;
use App\Models\Prospect;
use App\Services\CloudTalkDialer;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CloudTalkDialerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    public function test_it_creates_a_parallel_campaign_from_selected_prospects_and_assigns_the_agent(): void
    {
        Http::fake(function (Request $request) {
            $path = parse_url($request->url(), PHP_URL_PATH);

            return match ($request->method() . ' ' . $path) {
                'PUT /api/tags/add.json' => Http::response([
                    'responseData' => ['status' => 201, 'data' => ['id' => 91]],
                ], 201),
                'GET /api/contacts/index.json' => Http::response([
                    'responseData' => ['pageCount' => 1, 'data' => []],
                ]),
                'PUT /api/contacts/add.json' => Http::response([
                    'responseData' => ['status' => 201, 'data' => ['id' => 501]],
                ], 201),
                'PUT /api/contacts/addTags/501.json' => Http::response([
                    'responseData' => ['status' => 200],
                ]),
                'POST /v1/dialer/campaigns' => Http::response([
                    'id' => '77', 'name' => 'Batch 77', 'mode' => 'parallel', 'status' => 'initializing',
                ], 201),
                'PUT /v1/dialer/campaigns/77/associations' => Http::response([
                    'target_tag' => [['ref_id' => 91]], 'queue_group' => [], 'agent' => [88],
                ]),
                'GET /v1/dialer/campaigns/77/associations' => Http::response([
                    'target_tag' => [['ref_id' => 91]], 'queue_group' => [], 'agent' => [88],
                ]),
                'PATCH /v1/dialer/campaigns/77' => Http::response([
                    'id' => '77', 'name' => 'Batch 77', 'mode' => 'parallel', 'status' => 'active',
                ]),
                'GET /v1/dialer/campaigns/77/contacts' => Http::response([
                    'data' => [['contact_id' => '501']], 'meta' => ['nextCursor' => null],
                ]),
                default => Http::response(['message' => 'Unexpected CloudTalk request'], 500),
            };
        });

        $result = app(CloudTalkDialer::class)->createParallelCampaign(
            $this->line(),
            $this->project(),
            collect([$this->prospect(1, '+33123456789', '+33987654321')])
        );

        $this->assertTrue($result['success']);
        $this->assertSame('parallel', $result['campaign']['mode']);
        $this->assertSame('active', $result['campaign']['status']);
        $this->assertSame(1, $result['synced_count']);
        $this->assertSame(0, $result['duplicate_count']);
        $this->assertSame([], $result['errors']);

        Http::assertSent(fn (Request $request) =>
            $request->method() === 'PUT' &&
            str_ends_with(parse_url($request->url(), PHP_URL_PATH), '/contacts/add.json') &&
            data_get($request->data(), 'ContactNumber.0.public_number') === '+33123456789' &&
            str_starts_with((string) data_get($request->data(), 'ContactsTag.0.name'), 'HEROES-CRM-')
        );
        Http::assertNotSent(fn (Request $request) =>
            str_ends_with(parse_url($request->url(), PHP_URL_PATH), '/contacts/addTags/501.json')
        );
        Http::assertSent(fn (Request $request) =>
            $request->method() === 'POST' &&
            $request->url() === 'https://api.cloudtalk.io/v1/dialer/campaigns' &&
            data_get($request->data(), 'mode') === 'parallel' &&
            data_get($request->data(), 'calling_policy.parallel_calls') === 2 &&
            data_get($request->data(), 'calling_policy.number_selection') === 'automatic'
        );
        Http::assertSent(fn (Request $request) =>
            $request->method() === 'PUT' &&
            str_ends_with(parse_url($request->url(), PHP_URL_PATH), '/v1/dialer/campaigns/77/associations') &&
            data_get($request->data(), 'target_tag.0') === 91 &&
            data_get($request->data(), 'agent.0') === 88
        );
        Http::assertSent(fn (Request $request) =>
            $request->method() === 'GET' &&
            str_ends_with(parse_url($request->url(), PHP_URL_PATH), '/v1/dialer/campaigns/77/contacts') &&
            str_contains($request->url(), 'limit=100')
        );
    }

    public function test_it_deduplicates_selected_numbers_and_reports_invalid_numbers(): void
    {
        Http::fake(function (Request $request) {
            $path = parse_url($request->url(), PHP_URL_PATH);

            return match ($request->method() . ' ' . $path) {
                'PUT /api/tags/add.json' => Http::response([
                    'responseData' => ['status' => 201, 'data' => ['id' => 91]],
                ], 201),
                'GET /api/contacts/index.json' => Http::response([
                    'responseData' => ['pageCount' => 1, 'data' => []],
                ]),
                'PUT /api/contacts/add.json' => Http::response([
                    'responseData' => ['status' => 201, 'data' => ['id' => 501]],
                ], 201),
                'PUT /api/contacts/addTags/501.json' => Http::response([
                    'responseData' => ['status' => 200],
                ]),
                'POST /v1/dialer/campaigns' => Http::response([
                    'id' => '77', 'name' => 'Batch 77', 'mode' => 'parallel', 'status' => 'initializing',
                ], 201),
                'PUT /v1/dialer/campaigns/77/associations' => Http::response([
                    'target_tag' => [['ref_id' => 91]], 'queue_group' => [], 'agent' => [88],
                ]),
                'GET /v1/dialer/campaigns/77/associations' => Http::response([
                    'target_tag' => [['ref_id' => 91]], 'queue_group' => [], 'agent' => [88],
                ]),
                'PATCH /v1/dialer/campaigns/77' => Http::response([
                    'id' => '77', 'name' => 'Batch 77', 'mode' => 'parallel', 'status' => 'active',
                ]),
                'GET /v1/dialer/campaigns/77/contacts' => Http::response([
                    'data' => [['contact_id' => '501']], 'meta' => ['nextCursor' => null],
                ]),
                default => Http::response(['message' => 'Unexpected CloudTalk request'], 500),
            };
        });

        $result = app(CloudTalkDialer::class)->createParallelCampaign(
            $this->line(),
            $this->project(),
            collect([
                $this->prospect(1, '+33123456789'),
                $this->prospect(2, '+33 1 23 45 67 89'),
                $this->prospect(3, '01 23 45 67 89'),
            ])
        );

        $this->assertTrue($result['success']);
        $this->assertSame(1, $result['synced_count']);
        $this->assertSame(1, $result['duplicate_count']);
        $this->assertCount(2, $result['errors']);
        Http::assertSentCount(8);
    }

    public function test_it_reuses_a_matching_contact_and_makes_the_selected_number_primary(): void
    {
        Http::fake(function (Request $request) {
            $path = parse_url($request->url(), PHP_URL_PATH);

            return match ($request->method() . ' ' . $path) {
                'PUT /api/tags/add.json' => Http::response([
                    'responseData' => ['status' => 201, 'data' => ['id' => 91]],
                ], 201),
                'GET /api/contacts/index.json' => Http::response([
                    'responseData' => [
                        'pageCount' => 1,
                        'data' => [[
                            'Contact' => ['id' => '501'],
                            'ContactNumber' => ['public_number' => '+33123456789'],
                        ]],
                    ],
                ]),
                'GET /api/contacts/show/501.json' => Http::response([
                    'responseData' => [
                        'Contact' => ['id' => '501', 'name' => 'Existing contact'],
                        'ContactNumber' => [
                            ['public_number' => '+33987654321'],
                            ['public_number' => '+33123456789'],
                        ],
                    ],
                ]),
                'POST /api/contacts/edit/501.json' => Http::response([
                    'responseData' => ['status' => 200],
                ]),
                'PUT /api/contacts/addTags/501.json' => Http::response([
                    'responseData' => ['status' => 200],
                ]),
                'POST /v1/dialer/campaigns' => Http::response([
                    'id' => '77', 'name' => 'Batch 77', 'mode' => 'parallel', 'status' => 'initializing',
                ], 201),
                'PUT /v1/dialer/campaigns/77/associations' => Http::response([
                    'target_tag' => [['ref_id' => 91]], 'queue_group' => [], 'agent' => [88],
                ]),
                'GET /v1/dialer/campaigns/77/associations' => Http::response([
                    'target_tag' => [['ref_id' => 91]], 'queue_group' => [], 'agent' => [88],
                ]),
                'PATCH /v1/dialer/campaigns/77' => Http::response([
                    'id' => '77', 'name' => 'Batch 77', 'mode' => 'parallel', 'status' => 'active',
                ]),
                'GET /v1/dialer/campaigns/77/contacts' => Http::response([
                    'data' => [['contact_id' => '501']], 'meta' => ['nextCursor' => null],
                ]),
                default => Http::response(['message' => 'Unexpected CloudTalk request'], 500),
            };
        });

        $result = app(CloudTalkDialer::class)->createParallelCampaign(
            $this->line(),
            $this->project(),
            collect([$this->prospect(1, '+33123456789')])
        );

        $this->assertTrue($result['success']);
        $this->assertSame(1, $result['synced_count']);
        Http::assertSent(fn (Request $request) =>
            $request->method() === 'POST' &&
            str_ends_with(parse_url($request->url(), PHP_URL_PATH), '/contacts/edit/501.json') &&
            data_get($request->data(), 'ContactNumber.0.public_number') === '+33123456789' &&
            data_get($request->data(), 'ContactNumber.1.public_number') === '+33987654321'
        );
        Http::assertNotSent(fn (Request $request) =>
            str_ends_with(parse_url($request->url(), PHP_URL_PATH), '/contacts/add.json')
        );
    }

    public function test_it_keeps_syncing_valid_prospects_after_a_contact_api_error(): void
    {
        $contactAttempts = 0;
        Http::fake(function (Request $request) use (&$contactAttempts) {
            $path = parse_url($request->url(), PHP_URL_PATH);

            if ($request->method() === 'PUT' && $path === '/api/contacts/add.json') {
                $contactAttempts++;
                if ($contactAttempts === 1) {
                    return Http::response([
                        'responseData' => ['status' => 406, 'message' => 'Invalid phone number'],
                    ], 406);
                }

                return Http::response([
                    'responseData' => ['status' => 201, 'data' => ['id' => 502]],
                ], 201);
            }

            return match ($request->method() . ' ' . $path) {
                'PUT /api/tags/add.json' => Http::response([
                    'responseData' => ['status' => 201, 'data' => ['id' => 91]],
                ], 201),
                'GET /api/contacts/index.json' => Http::response([
                    'responseData' => ['pageCount' => 1, 'data' => []],
                ]),
                'POST /v1/dialer/campaigns' => Http::response([
                    'id' => '77', 'name' => 'Batch 77', 'mode' => 'parallel', 'status' => 'initializing',
                ], 201),
                'PUT /v1/dialer/campaigns/77/associations' => Http::response([
                    'target_tag' => [['ref_id' => 91]], 'queue_group' => [], 'agent' => [88],
                ]),
                'GET /v1/dialer/campaigns/77/associations' => Http::response([
                    'target_tag' => [['ref_id' => 91]], 'queue_group' => [], 'agent' => [88],
                ]),
                'PATCH /v1/dialer/campaigns/77' => Http::response([
                    'id' => '77', 'name' => 'Batch 77', 'mode' => 'parallel', 'status' => 'active',
                ]),
                'GET /v1/dialer/campaigns/77/contacts' => Http::response([
                    'data' => [['contact_id' => '502']], 'meta' => ['nextCursor' => null],
                ]),
                default => Http::response(['message' => 'Unexpected CloudTalk request'], 500),
            };
        });

        $result = app(CloudTalkDialer::class)->createParallelCampaign(
            $this->line(),
            $this->project(),
            collect([
                $this->prospect(1, '+33123456789'),
                $this->prospect(2, '+33123456780'),
            ])
        );

        $this->assertTrue($result['success']);
        $this->assertSame(1, $result['synced_count']);
        $this->assertCount(1, $result['errors']);
        $this->assertSame('Invalid phone number', $result['errors'][0]['message']);
    }

    public function test_it_exposes_campaign_validation_details_and_removes_the_unused_tag(): void
    {
        Http::fake(function (Request $request) {
            $path = parse_url($request->url(), PHP_URL_PATH);

            return match ($request->method() . ' ' . $path) {
                'PUT /api/tags/add.json' => Http::response([
                    'responseData' => ['status' => 201, 'data' => ['id' => 91]],
                ], 201),
                'GET /api/contacts/index.json' => Http::response([
                    'responseData' => [
                        'pageCount' => 1,
                        'data' => [[
                            'Contact' => ['id' => '501'],
                            'ContactNumber' => ['public_number' => '+33123456789'],
                        ]],
                    ],
                ]),
                'GET /api/contacts/show/501.json' => Http::response([
                    'responseData' => [
                        'Contact' => ['id' => '501', 'name' => 'Existing contact'],
                        'ContactNumber' => [['public_number' => '+33123456789']],
                    ],
                ]),
                'PUT /api/contacts/addTags/501.json' => Http::response([
                    'responseData' => ['status' => 200],
                ]),
                'POST /v1/dialer/campaigns' => Http::response([
                    'message' => 'Validation error',
                    'subErrors' => [[
                        'field' => 'calling_policy.parallel_calls',
                        'message' => 'must be at least 2',
                    ]],
                ], 500),
                'DELETE /api/tags/delete/91.json' => Http::response([
                    'responseData' => ['status' => 200],
                ]),
                default => Http::response(['message' => 'Unexpected CloudTalk request'], 500),
            };
        });

        $result = app(CloudTalkDialer::class)->createParallelCampaign(
            $this->line(),
            $this->project(),
            collect([$this->prospect(1, '+33123456789')])
        );

        $this->assertFalse($result['success']);
        $this->assertSame(1, $result['synced_count']);
        $this->assertStringContainsString('calling_policy.parallel_calls', $result['message']);
        $this->assertStringContainsString('must be at least 2', $result['message']);
        Http::assertSent(fn (Request $request) =>
            $request->method() === 'DELETE' &&
            str_ends_with(parse_url($request->url(), PHP_URL_PATH), '/tags/delete/91.json')
        );
    }

    public function test_it_does_not_call_cloudtalk_when_all_selected_numbers_are_invalid(): void
    {
        $result = app(CloudTalkDialer::class)->createParallelCampaign(
            $this->line(),
            $this->project(),
            collect([$this->prospect(1, '01 23 45 67 89')])
        );

        $this->assertFalse($result['success']);
        $this->assertSame(422, $result['status']);
        $this->assertSame(0, $result['synced_count']);
        $this->assertCount(1, $result['errors']);
        Http::assertNothingSent();
    }

    private function line(): Line
    {
        $line = new Line();
        $line->config = [
            'api_key_id' => 'access-key',
            'api_key_secret' => 'secret-key',
            'agent_id' => 88,
        ];

        return $line;
    }

    private function project(): Project
    {
        $project = new Project();
        $project->slug = 'dialer-test';

        return $project;
    }

    private function prospect(int $id, ?string $mobile, ?string $phone = null): Prospect
    {
        $prospect = new Prospect();
        $prospect->id = $id;
        $prospect->first_name = 'Prospect';
        $prospect->last_name = (string) $id;
        $prospect->company_name = 'Example';
        $prospect->mobile_phone_number = $mobile;
        $prospect->phone_number = $phone;

        return $prospect;
    }
}
