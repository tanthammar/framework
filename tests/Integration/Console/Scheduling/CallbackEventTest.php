<?php

namespace Illuminate\Tests\Integration\Console\Scheduling;

use Exception;
use Illuminate\Console\Scheduling\CallbackEvent;
use Illuminate\Console\Scheduling\EventMutex;
use Mockery as m;
use Orchestra\Testbench\TestCase;

/**
 * @group integration
 */
class CallbackEventTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        m::close();
    }

    public function testDefaultResultIsSuccess()
    {
        $success = null;

        $event = (new CallbackEvent(m::mock(EventMutex::class), function () {
        }))->onSuccess(function () use (&$success) {
            $success = true;
        })->onFailure(function () use (&$success) {
            $success = false;
        });

        $event->run($this->app);

        $this->assertTrue($success);
    }

    public function testFalseResponseIsFailure()
    {
        $success = null;

        $event = (new CallbackEvent(m::mock(EventMutex::class), function () {
            return false;
        }))->onSuccess(function () use (&$success) {
            $success = true;
        })->onFailure(function () use (&$success) {
            $success = false;
        });

        $event->run($this->app);

        $this->assertFalse($success);
    }

    public function testExceptionIsFailure()
    {
        $success = null;

        $event = (new CallbackEvent(m::mock(EventMutex::class), function () {
            throw new Exception;
        }))->onSuccess(function () use (&$success) {
            $success = true;
        })->onFailure(function () use (&$success) {
            $success = false;
        });

        try {
            $event->run($this->app);
        } catch (Exception $e) {
        }

        $this->assertFalse($success);
    }

    public function testExceptionBubbles()
    {
        $event = new CallbackEvent(m::mock(EventMutex::class), function () {
            throw new Exception;
        });

        $this->expectException(Exception::class);

        $event->run($this->app);
    }
}
