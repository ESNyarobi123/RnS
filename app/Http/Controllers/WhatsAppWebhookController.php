<?php

namespace App\Http\Controllers;

use App\Models\BotSetting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WhatsAppWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        // Log incoming webhook for debugging
        Log::info('WhatsApp webhook received', [
            'data' => $request->all(),
            'headers' => $request->headers->all(),
        ]);

        // Verify webhook signature if needed
        $secretKey = $request->header('X-Bot-Secret');
        if (!$secretKey) {
            return response()->json(['error' => 'Missing secret key'], 401);
        }

        $botSetting = BotSetting::where('is_active', true)->first();
        if (!$botSetting || $secretKey !== $botSetting->secret_key) {
            return response()->json(['error' => 'Invalid secret key'], 401);
        }

        try {
            $payload = $request->json()->all();

            // Handle different webhook event types
            if (isset($payload['event'])) {
                switch ($payload['event']) {
                    case 'message_received':
                        $this->handleMessageReceived($payload);
                        break;
                    case 'message_sent':
                        $this->handleMessageSent($payload);
                        break;
                    case 'session_started':
                        $this->handleSessionStarted($payload);
                        break;
                    case 'session_ended':
                        $this->handleSessionEnded($payload);
                        break;
                    case 'order_created':
                        $this->handleOrderCreated($payload);
                        break;
                    case 'payment_completed':
                        $this->handlePaymentCompleted($payload);
                        break;
                    case 'feedback_submitted':
                        $this->handleFeedbackSubmitted($payload);
                        break;
                    case 'waiter_called':
                        $this->handleWaiterCalled($payload);
                        break;
                    default:
                        Log::warning('Unknown webhook event', ['event' => $payload['event']]);
                }
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('WhatsApp webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    private function handleMessageReceived(array $payload): void
    {
        $phoneNumber = $payload['phone_number'] ?? null;
        $message = $payload['message'] ?? null;
        $sessionId = $payload['session_id'] ?? null;

        if (!$phoneNumber || !$message) {
            Log::warning('Invalid message received payload', $payload);
            return;
        }

        // Log the message for analytics
        Log::info('WhatsApp message received', [
            'phone_number' => $phoneNumber,
            'message' => $message,
            'session_id' => $sessionId,
        ]);

        // Here you could trigger additional processing if needed
        // The main processing happens in the bot itself
    }

    private function handleMessageSent(array $payload): void
    {
        $phoneNumber = $payload['phone_number'] ?? null;
        $message = $payload['message'] ?? null;

        Log::info('WhatsApp message sent', [
            'phone_number' => $phoneNumber,
            'message' => $message,
        ]);
    }

    private function handleSessionStarted(array $payload): void
    {
        $phoneNumber = $payload['phone_number'] ?? null;
        $sessionType = $payload['session_type'] ?? null; // business, worker, table
        $entityId = $payload['entity_id'] ?? null;

        Log::info('WhatsApp session started', [
            'phone_number' => $phoneNumber,
            'session_type' => $sessionType,
            'entity_id' => $entityId,
        ]);

        // Update session analytics
        // You could track session duration, popular entities, etc.
    }

    private function handleSessionEnded(array $payload): void
    {
        $phoneNumber = $payload['phone_number'] ?? null;
        $sessionId = $payload['session_id'] ?? null;
        $duration = $payload['duration'] ?? null;

        Log::info('WhatsApp session ended', [
            'phone_number' => $phoneNumber,
            'session_id' => $sessionId,
            'duration' => $duration,
        ]);

        // Update session analytics
        // Track average session duration, completion rates, etc.
    }

    private function handleOrderCreated(array $payload): void
    {
        $orderId = $payload['order_id'] ?? null;
        $businessId = $payload['business_id'] ?? null;
        $phoneNumber = $payload['customer_phone'] ?? null;

        Log::info('Order created via WhatsApp', [
            'order_id' => $orderId,
            'business_id' => $businessId,
            'customer_phone' => $phoneNumber,
        ]);

        // Notify manager/staff about new order
        // Update order analytics
    }

    private function handlePaymentCompleted(array $payload): void
    {
        $paymentId = $payload['payment_id'] ?? null;
        $orderId = $payload['order_id'] ?? null;
        $amount = $payload['amount'] ?? null;

        Log::info('Payment completed via WhatsApp', [
            'payment_id' => $paymentId,
            'order_id' => $orderId,
            'amount' => $amount,
        ]);

        // Update order status to paid
        // Send confirmation to customer
        // Update payment analytics
    }

    private function handleFeedbackSubmitted(array $payload): void
    {
        $feedbackId = $payload['feedback_id'] ?? null;
        $businessId = $payload['business_id'] ?? null;
        $workerId = $payload['worker_id'] ?? null;
        $rating = $payload['rating'] ?? null;

        Log::info('Feedback submitted via WhatsApp', [
            'feedback_id' => $feedbackId,
            'business_id' => $businessId,
            'worker_id' => $workerId,
            'rating' => $rating,
        ]);

        // Update business/worker ratings
        // Send thank you message to customer
    }

    private function handleWaiterCalled(array $payload): void
    {
        $tableId = $payload['table_id'] ?? null;
        $businessId = $payload['business_id'] ?? null;
        $phoneNumber = $payload['customer_phone'] ?? null;

        Log::info('Waiter called via WhatsApp', [
            'table_id' => $tableId,
            'business_id' => $businessId,
            'customer_phone' => $phoneNumber,
        ]);

        // Notify available waiters
        // Update table status if needed
        // Track response times
    }

    /**
     * Health check endpoint for webhooks
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
            'webhook' => 'whatsapp',
        ]);
    }
}
