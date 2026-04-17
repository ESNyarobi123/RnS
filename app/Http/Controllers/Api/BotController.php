<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\BotSetting;
use App\Models\Business;
use App\Models\BusinessWorker;
use App\Models\Feedback;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Table;
use App\Models\Tip;
use App\Models\WaiterCall;
use App\Services\BusinessPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BotController extends Controller
{
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
            'bot_active' => BotSetting::query()->where('is_active', true)->exists(),
        ]);
    }

    public function getBusinessByCode(string $code): JsonResponse
    {
        if ($response = $this->authorizeBotRequest(request())) {
            return $response;
        }

        $business = Business::query()
            ->where(function ($query) use ($code): void {
                $query->where('bot_code', $code)->orWhere('qr_code', $code);
            })
            ->first();

        if (! $business) {
            return response()->json(['error' => 'Business not found.'], 404);
        }

        return response()->json([
            'id' => $business->id,
            'name' => $business->name,
            'type' => $business->type->value,
            'code' => $business->bot_code,
            'description' => $business->description,
            'address' => $business->address,
            'phone' => $business->phone,
            'status' => $business->status->value,
            'menu_image_url' => $business->menuImageUrl(),
            'worker_title' => $business->type->workerTitle(),
            'table_label' => $business->tableLabel(),
        ]);
    }

    public function getBusinessMenu(int $id): JsonResponse
    {
        if ($response = $this->authorizeBotRequest(request())) {
            return $response;
        }

        $business = Business::query()->findOrFail($id);

        $items = $business->activeProducts()
            ->with('category')
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product): array => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => (float) $product->price,
                'category' => $product->category?->name,
                'duration_minutes' => $product->duration_minutes,
                'image_url' => $product->imageUrl(),
            ])
            ->values();

        return response()->json([
            'business_name' => $business->name,
            'business_type' => $business->type->value,
            'menu_image_url' => $business->menuImageUrl(),
            'items' => $items,
        ]);
    }

    public function getBusinessTables(int $id): JsonResponse
    {
        if ($response = $this->authorizeBotRequest(request())) {
            return $response;
        }

        $business = Business::query()->findOrFail($id);

        $tables = Table::query()
            ->where('business_id', $business->id)
            ->orderBy('name')
            ->get()
            ->map(fn (Table $table): array => [
                'id' => $table->id,
                'name' => $table->name,
                'display_name' => $table->display_name,
                'capacity' => $table->capacity,
                'status' => $table->status,
                'code' => $table->qr_code,
                'qr_image_url' => $table->qrImageUrl(),
            ])
            ->values();

        return response()->json([
            'label' => $business->tableLabelPlural(),
            'tables' => $tables,
        ]);
    }

    public function getBusinessWorkers(int $id): JsonResponse
    {
        if ($response = $this->authorizeBotRequest(request())) {
            return $response;
        }

        $business = Business::query()->findOrFail($id);

        $workers = BusinessWorker::query()
            ->where('business_id', $business->id)
            ->where('is_active', true)
            ->with('worker')
            ->get()
            ->map(fn (BusinessWorker $link): array => [
                'id' => $link->id,
                'worker_id' => $link->worker_id,
                'name' => $link->worker->name,
                'phone' => $link->worker->phone,
                'global_number' => $link->worker->global_number,
                'code' => $link->qr_code,
                'qr_image_url' => $link->qr_image_path ? asset('storage/'.$link->qr_image_path) : null,
            ])
            ->values();

        return response()->json([
            'worker_title' => $business->type->workerTitlePlural(),
            'workers' => $workers,
        ]);
    }

    public function getWorkerByCode(string $code): JsonResponse
    {
        if ($response = $this->authorizeBotRequest(request())) {
            return $response;
        }

        $link = BusinessWorker::query()
            ->where('qr_code', $code)
            ->with(['business', 'worker'])
            ->first();

        if (! $link) {
            return response()->json(['error' => 'Worker not found.'], 404);
        }

        return response()->json([
            'id' => $link->id,
            'worker_id' => $link->worker_id,
            'business_id' => $link->business_id,
            'business_name' => $link->business->name,
            'business_type' => $link->business->type->value,
            'worker_title' => $link->business->type->workerTitle(),
            'table_label' => $link->business->tableLabel(),
            'name' => $link->worker->name,
            'phone' => $link->worker->phone,
            'global_number' => $link->worker->global_number,
            'code' => $link->qr_code,
        ]);
    }

    public function getWorkerTips(int $id, Request $request): JsonResponse
    {
        if ($response = $this->authorizeBotRequest($request)) {
            return $response;
        }

        $link = BusinessWorker::query()->findOrFail($id);
        $query = Tip::query()
            ->where('business_id', $link->business_id)
            ->where('worker_id', $link->worker_id);

        $query = $this->applyPeriodFilter($query, $request->string('period')->value('today'));

        $tips = $query->latest()->get();

        return response()->json([
            'tips' => $tips->map(fn (Tip $tip): array => [
                'id' => $tip->id,
                'amount' => (float) $tip->amount,
                'customer_name' => $tip->customer_name,
                'customer_phone' => $tip->customer_phone,
                'source' => $tip->source,
                'created_at' => $tip->created_at->toISOString(),
            ])->values(),
            'summary' => [
                'total_amount' => (float) $tips->sum('amount'),
                'total_count' => $tips->count(),
            ],
        ]);
    }

    public function getWorkerFeedbacks(int $id): JsonResponse
    {
        if ($response = $this->authorizeBotRequest(request())) {
            return $response;
        }

        $link = BusinessWorker::query()->findOrFail($id);
        $feedbacks = Feedback::query()
            ->where('business_id', $link->business_id)
            ->where('worker_id', $link->worker_id)
            ->latest()
            ->get();

        return response()->json([
            'feedbacks' => $feedbacks->map(fn (Feedback $feedback): array => [
                'id' => $feedback->id,
                'rating' => $feedback->rating,
                'comment' => $feedback->comment,
                'customer_name' => $feedback->customer_name,
                'created_at' => $feedback->created_at->toISOString(),
            ])->values(),
            'summary' => [
                'average_rating' => round((float) $feedbacks->avg('rating'), 2),
                'total_feedbacks' => $feedbacks->count(),
            ],
        ]);
    }

    public function getWorkerCustomersServed(int $id): JsonResponse
    {
        if ($response = $this->authorizeBotRequest(request())) {
            return $response;
        }

        $link = BusinessWorker::query()->findOrFail($id);
        $orders = Order::query()
            ->where('business_id', $link->business_id)
            ->where('worker_id', $link->worker_id)
            ->latest()
            ->get();

        $customers = $orders
            ->map(fn (Order $order): array => [
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'order_number' => $order->order_number,
                'served_at' => $order->created_at->toISOString(),
            ])
            ->unique(fn (array $customer): string => ($customer['customer_phone'] ?? '').'|'.($customer['customer_name'] ?? ''))
            ->values();

        return response()->json([
            'customers' => $customers,
            'total_served' => $customers->count(),
        ]);
    }

    public function getTableByCode(string $code): JsonResponse
    {
        if ($response = $this->authorizeBotRequest(request())) {
            return $response;
        }

        $table = Table::query()->where('qr_code', $code)->with('business')->first();

        if (! $table) {
            return response()->json(['error' => 'Table not found.'], 404);
        }

        return response()->json([
            'id' => $table->id,
            'business_id' => $table->business_id,
            'business_name' => $table->business->name,
            'business_type' => $table->business->type->value,
            'worker_title' => $table->business->type->workerTitle(),
            'table_label' => $table->business->tableLabel(),
            'name' => $table->name,
            'display_name' => $table->display_name,
            'capacity' => $table->capacity,
            'status' => $table->status,
            'code' => $table->qr_code,
        ]);
    }

    public function createOrder(Request $request): JsonResponse
    {
        if ($response = $this->authorizeBotRequest($request)) {
            return $response;
        }

        $validator = Validator::make($request->all(), [
            'business_id' => 'required|exists:businesses,id',
            'worker_id' => 'nullable|exists:business_workers,id',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $business = Business::query()->findOrFail((int) $request->integer('business_id'));
        $link = $request->filled('worker_id') ? BusinessWorker::query()->find($request->integer('worker_id')) : null;

        $order = Order::query()->create([
            'business_id' => $business->id,
            'worker_id' => $link?->worker_id,
            'customer_name' => $request->string('customer_name')->value() ?: null,
            'customer_phone' => $request->string('customer_phone')->value() ?: null,
            'status' => OrderStatus::Pending,
            'notes' => $request->string('notes')->value() ?: null,
        ]);

        foreach ($request->input('items', []) as $item) {
            $product = Product::query()->findOrFail((int) $item['product_id']);

            $order->items()->create([
                'product_id' => $product->id,
                'quantity' => (int) $item['quantity'],
                'unit_price' => $product->price,
            ]);
        }

        $order->refresh();

        return response()->json([
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status->value,
            'total' => (float) $order->total,
            'message' => 'Order created successfully.',
        ]);
    }

    public function getOrderStatus(int $id): JsonResponse
    {
        if ($response = $this->authorizeBotRequest(request())) {
            return $response;
        }

        $order = Order::query()->with(['items.product', 'payments'])->findOrFail($id);

        return response()->json([
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status->value,
            'total' => (float) $order->total,
            'paid_amount' => (float) $order->payments->sum('amount'),
            'items' => $order->items->map(fn ($item): array => [
                'name' => $item->product?->name,
                'quantity' => $item->quantity,
                'price' => (float) $item->unit_price,
                'total' => (float) $item->total_price,
            ])->values(),
            'created_at' => $order->created_at->toISOString(),
        ]);
    }

    public function initiatePayment(Request $request): JsonResponse
    {
        if ($response = $this->authorizeBotRequest($request)) {
            return $response;
        }

        $validator = Validator::make($request->all(), [
            'type' => 'nullable|in:order,tip',
            'order_id' => 'nullable|exists:orders,id',
            'business_id' => 'nullable|exists:businesses,id',
            'worker_id' => 'nullable|exists:business_workers,id',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:cash,mobile_money,card,bank_transfer',
            'reference' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $paymentType = $request->string('type')->value() ?: 'order';
        $method = PaymentMethod::from($request->string('method')->value());

        if ($paymentType === 'order' && ! $request->filled('order_id')) {
            return response()->json(['error' => ['order_id' => ['The order_id field is required for order payments.']]], 422);
        }

        if ($paymentType === 'tip' && ! $request->filled('business_id')) {
            return response()->json(['error' => ['business_id' => ['The business_id field is required for tip payments.']]], 422);
        }

        if ($method === PaymentMethod::MobileMoney && ! $request->filled('customer_phone')) {
            return response()->json(['error' => ['customer_phone' => ['The customer_phone field is required for mobile money payments.']]], 422);
        }

        $paymentService = app(BusinessPaymentService::class);

        if ($method === PaymentMethod::MobileMoney && $paymentType === 'order') {
            $order = Order::query()->with('business')->findOrFail((int) $request->integer('order_id'));

            $result = $paymentService->initiateOrderMobileMoney(
                $order,
                (float) $request->input('amount'),
                $request->string('customer_phone')->value(),
                $request->string('customer_name')->value() ?: null,
            );

            if (! $result['success'] || ! $result['payment']) {
                return response()->json(['error' => $result['error'] ?? 'Could not initiate payment.'], 422);
            }

            $payment = $result['payment'];

            return response()->json([
                'payment_id' => $payment->id,
                'status' => $payment->status->value,
                'amount' => (float) $payment->amount,
                'provider' => $payment->provider,
                'provider_order_id' => $payment->provider_order_id,
                'message' => 'Payment push sent successfully. Waiting for customer authorization.',
            ]);
        }

        if ($method === PaymentMethod::MobileMoney && $paymentType === 'tip') {
            $business = Business::query()->findOrFail((int) $request->integer('business_id'));
            $workerLink = $request->filled('worker_id')
                ? BusinessWorker::query()->with('worker')->find($request->integer('worker_id'))
                : null;

            if (! $workerLink?->worker_id) {
                return response()->json(['error' => 'Tip payments require a valid worker link.'], 422);
            }

            $result = $paymentService->initiateTipMobileMoney(
                $business,
                $workerLink,
                (float) $request->input('amount'),
                $request->string('customer_phone')->value(),
                $request->string('customer_name')->value() ?: null,
            );

            if (! $result['success'] || ! $result['payment']) {
                return response()->json(['error' => $result['error'] ?? 'Could not initiate tip payment.'], 422);
            }

            $payment = $result['payment'];

            return response()->json([
                'payment_id' => $payment->id,
                'status' => $payment->status->value,
                'amount' => (float) $payment->amount,
                'provider' => $payment->provider,
                'provider_order_id' => $payment->provider_order_id,
                'message' => 'Tip payment push sent successfully. Waiting for customer authorization.',
            ]);
        }

        $order = Order::query()->findOrFail((int) $request->integer('order_id'));

        $payment = Payment::query()->create([
            'business_id' => $order->business_id,
            'order_id' => $order->id,
            'amount' => $request->input('amount'),
            'method' => $method,
            'status' => PaymentStatus::Pending,
            'reference' => $request->string('reference')->value() ?: null,
            'customer_phone' => $request->string('customer_phone')->value() ?: null,
            'customer_name' => $request->string('customer_name')->value() ?: null,
            'metadata' => ['purpose' => $paymentType],
        ]);

        return response()->json([
            'payment_id' => $payment->id,
            'status' => $payment->status->value,
            'amount' => (float) $payment->amount,
        ]);
    }

    public function checkPaymentStatus(int $id): JsonResponse
    {
        if ($response = $this->authorizeBotRequest(request())) {
            return $response;
        }

        $payment = Payment::query()->with(['order', 'tip'])->findOrFail($id);

        if ($payment->status === PaymentStatus::Pending && $payment->provider === 'selcom') {
            $syncResult = app(BusinessPaymentService::class)->syncPaymentStatus($payment);

            if ($syncResult['success']) {
                $payment = $syncResult['payment'];
                $providerStatus = $syncResult['provider_status'];
            } else {
                $providerStatus = null;
            }
        } else {
            $providerStatus = null;
        }

        $payment->loadMissing(['order.payments', 'tip']);

        $remainingAmount = $payment->order
            ? max(0, (float) $payment->order->total - (float) $payment->order->payments->where('status', PaymentStatus::Completed)->sum('amount'))
            : null;

        return response()->json([
            'payment_id' => $payment->id,
            'status' => $payment->status->value,
            'amount' => (float) $payment->amount,
            'provider' => $payment->provider,
            'provider_order_id' => $payment->provider_order_id,
            'provider_status' => $providerStatus,
            'purpose' => data_get($payment->metadata, 'purpose'),
            'order_id' => $payment->order_id,
            'tip_id' => $payment->tip?->id,
            'remaining_amount' => $remainingAmount,
            'updated_at' => $payment->updated_at->toISOString(),
        ]);
    }

    public function submitFeedback(Request $request): JsonResponse
    {
        if ($response = $this->authorizeBotRequest($request)) {
            return $response;
        }

        $validator = Validator::make($request->all(), [
            'business_id' => 'required|exists:businesses,id',
            'worker_id' => 'nullable|integer',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'customer_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $workerId = null;

        if ($request->filled('worker_id')) {
            $link = BusinessWorker::query()->find($request->integer('worker_id'));
            $workerId = $link?->worker_id;
        }

        $feedback = Feedback::query()->create([
            'business_id' => $request->integer('business_id'),
            'worker_id' => $workerId,
            'customer_name' => $request->string('customer_name')->value() ?: null,
            'rating' => $request->integer('rating'),
            'comment' => $request->string('comment')->value() ?: null,
        ]);

        return response()->json([
            'feedback_id' => $feedback->id,
            'message' => 'Feedback submitted successfully.',
        ]);
    }

    public function submitTip(Request $request): JsonResponse
    {
        if ($response = $this->authorizeBotRequest($request)) {
            return $response;
        }

        $validator = Validator::make($request->all(), [
            'business_id' => 'required|exists:businesses,id',
            'worker_id' => 'nullable|exists:business_workers,id',
            'amount' => 'required|numeric|min:0.01',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'source' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $workerId = null;

        if ($request->filled('worker_id')) {
            $link = BusinessWorker::query()->find($request->integer('worker_id'));
            $workerId = $link?->worker_id;
        }

        $tip = Tip::query()->create([
            'business_id' => $request->integer('business_id'),
            'worker_id' => $workerId,
            'amount' => $request->input('amount'),
            'customer_name' => $request->string('customer_name')->value() ?: null,
            'customer_phone' => $request->string('customer_phone')->value() ?: null,
            'source' => $request->string('source')->value() ?: 'whatsapp',
        ]);

        return response()->json([
            'tip_id' => $tip->id,
            'amount' => (float) $tip->amount,
            'message' => 'Tip submitted successfully.',
        ]);
    }

    public function callWaiter(Request $request): JsonResponse
    {
        if ($response = $this->authorizeBotRequest($request)) {
            return $response;
        }

        $validator = Validator::make($request->all(), [
            'business_id' => 'required|exists:businesses,id',
            'table_id' => 'nullable|exists:tables,id',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'message' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $call = WaiterCall::query()->create([
            'business_id' => $request->integer('business_id'),
            'table_id' => $request->integer('table_id') ?: null,
            'customer_name' => $request->string('customer_name')->value() ?: null,
            'customer_phone' => $request->string('customer_phone')->value() ?: null,
            'notes' => $request->string('message')->value() ?: null,
            'status' => 'pending',
        ]);

        return response()->json([
            'call_id' => $call->id,
            'message' => 'Service team notified successfully.',
        ]);
    }

    public function createSupportTicket(Request $request): JsonResponse
    {
        if ($response = $this->authorizeBotRequest($request)) {
            return $response;
        }

        $validator = Validator::make($request->all(), [
            'business_id' => 'required|exists:businesses,id',
            'customer_name' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'issue' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        Log::info('WhatsApp support ticket created', [
            'business_id' => $request->integer('business_id'),
            'customer_name' => $request->string('customer_name')->value(),
            'customer_phone' => $request->string('phone_number')->value(),
            'issue' => $request->string('issue')->value(),
        ]);

        return response()->json([
            'ticket_id' => 'SUP-'.strtoupper((string) str()->random(8)),
            'message' => 'Support request received.',
        ]);
    }

    private function applyPeriodFilter($query, string $period)
    {
        return match ($period) {
            'week' => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'month' => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
            default => $query->whereDate('created_at', today()),
        };
    }

    private function authorizeBotRequest(Request $request): ?JsonResponse
    {
        $botSetting = BotSetting::query()
            ->where('is_active', true)
            ->latest('id')
            ->first();

        $secretKey = (string) $request->header('X-Bot-Secret');

        if (! $botSetting || $secretKey === '' || ! hash_equals((string) $botSetting->secret_key, $secretKey)) {
            return response()->json(['error' => 'Invalid bot credentials.'], 401);
        }

        if ($request->isMethodCacheable()) {
            return null;
        }

        $signature = (string) $request->header('X-Bot-Signature');
        $expectedSignature = hash_hmac('sha256', $request->getContent(), $secretKey);

        if ($signature !== '' && ! hash_equals($expectedSignature, $signature)) {
            return response()->json(['error' => 'Invalid request signature.'], 401);
        }

        return null;
    }
}
