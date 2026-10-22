<?php

namespace Tests\Unit;

use App\Utils\Tracker\RemembersIds;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TrackerRemembersIdsTest extends TestCase
{
    protected function resolver(): object
    {
        return new class {
            use RemembersIds;

            public int $calls = 0;

            public function id(string $key, int $id)
            {
                return $this->rememberId($key, function () use ($id) {
                    $this->calls++;

                    return $id;
                });
            }
        };
    }

    public function test_the_id_is_resolved_once_then_served_from_the_cache(): void
    {
        $resolver = $this->resolver();

        $this->assertSame(7, $resolver->id('tracker.test.' . uniqid(), 7));

        $key = 'tracker.test.' . uniqid();
        $resolver->id($key, 9);
        $this->assertSame(9, $resolver->id($key, 123));
        $this->assertSame(2, $resolver->calls);
    }

    public function test_an_unavailable_cache_falls_back_to_the_database_lookup(): void
    {
        Cache::shouldReceive('get')->andThrow(new \RuntimeException('redis down'));
        Cache::shouldReceive('put')->andThrow(new \RuntimeException('redis down'));

        $resolver = $this->resolver();

        $this->assertSame(5, $resolver->id('tracker.test.down', 5));
        $this->assertSame(1, $resolver->calls);
    }
}
