<?php

namespace Tests\Unit;

use App\Console\Commands\AttendanceAuto;
use App\Models\User;
use ReflectionMethod;
use Tests\TestCase;

class AttendanceAutoTest extends TestCase
{
    public function test_it_recognizes_spatie_role_names_for_presence(): void
    {
        $command = new AttendanceAuto();
        $method = new ReflectionMethod($command, 'isAttendanceEligibleUser');
        $method->setAccessible(true);

        $agent = new User();
        $agent->setRelation('roles', collect([
            (object) ['name' => 'Agent commercial'],
        ]));

        $assistant = new User();
        $assistant->setRelation('roles', collect([
            (object) ['name' => 'Assistant commercial'],
        ]));

        $other = new User();
        $other->setRelation('roles', collect([
            (object) ['name' => 'Administrator'],
        ]));

        $allowedRoles = ['commercial', 'agent commercial', 'assistant commercial'];

        $this->assertTrue($method->invoke($command, $agent, $allowedRoles));
        $this->assertTrue($method->invoke($command, $assistant, $allowedRoles));
        $this->assertFalse($method->invoke($command, $other, $allowedRoles));
    }
}
