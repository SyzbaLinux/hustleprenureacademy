<?php

namespace Tests\Feature;

use App\Jobs\CheckPaymentStatus;
use App\Models\Event;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CheckPaymentStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_payment_does_not_throw_exception(): void
    {
        Queue::fake();

        $event = Event::factory()->create();
        $payment = Payment::factory()->create([
            'event_id' => $event->id,
            'status' => 'pending',
        ]);

        // Mock the PesePayService to return pending status
        $this->mock('App\Services\Payment\PesePayService', function ($mock) {
            $mock->shouldReceive('checkPaymentStatus')
                ->andReturn([
                    'paid' => false,
                    'status' => 'pending',
                ]);
        });

        // Should not throw an exception
        $job = new CheckPaymentStatus($payment);
        $job->handle(
            app('App\Services\Payment\PesePayService'),
            app('App\Services\Chatbot\EnrollmentService')
        );

        // Payment status should be updated to processing
        $payment->refresh();
        $this->assertEquals('processing', $payment->status);

        // Another job should be dispatched for retry
        Queue::assertPushed(CheckPaymentStatus::class);
    }

    public function test_successful_payment_completes_enrollment(): void
    {
        Queue::fake();

        $event = Event::factory()->create();
        $payment = Payment::factory()->create([
            'event_id' => $event->id,
            'status' => 'pending',
        ]);

        // Mock the PesePayService to return paid status
        $this->mock('App\Services\Payment\PesePayService', function ($mock) {
            $mock->shouldReceive('checkPaymentStatus')
                ->andReturn([
                    'paid' => true,
                    'status' => 'paid',
                ]);
        });

        // Mock the EnrollmentService
        $this->mock('App\Services\Chatbot\EnrollmentService', function ($mock) {
            $mock->shouldReceive('completeEnrollment');
        });

        $job = new CheckPaymentStatus($payment);
        $job->handle(
            app('App\Services\Payment\PesePayService'),
            app('App\Services\Chatbot\EnrollmentService')
        );

        // Payment status should be 'paid'
        $payment->refresh();
        $this->assertEquals('paid', $payment->status);
    }

    public function test_failed_payment_notifies_user(): void
    {
        Queue::fake();

        $event = Event::factory()->create();
        $payment = Payment::factory()->create([
            'event_id' => $event->id,
            'status' => 'pending',
        ]);

        // Mock the PesePayService to return failed status
        $this->mock('App\Services\Payment\PesePayService', function ($mock) {
            $mock->shouldReceive('checkPaymentStatus')
                ->andReturn([
                    'paid' => false,
                    'status' => 'failed',
                    'transaction_status' => 'User declined payment',
                ]);
        });

        // Mock the EnrollmentService
        $this->mock('App\Services\Chatbot\EnrollmentService', function ($mock) {
            $mock->shouldReceive('handleFailedPayment');
        });

        $job = new CheckPaymentStatus($payment);
        $job->handle(
            app('App\Services\Payment\PesePayService'),
            app('App\Services\Chatbot\EnrollmentService')
        );

        // Payment status should be 'failed'
        $payment->refresh();
        $this->assertEquals('failed', $payment->status);
    }
}
