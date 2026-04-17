<?php

namespace App\Http\Controllers;

use App\Models\BotSetting;
use App\Models\Business;
use App\Models\BusinessWorker;
use App\Models\QrCode;
use App\Models\Table;
use Illuminate\Http\RedirectResponse;

class QrRedirectController extends Controller
{
    public function __invoke(string $code): RedirectResponse
    {
        $botSetting = BotSetting::query()
            ->where('is_active', true)
            ->latest('id')
            ->first();

        abort_unless($botSetting?->phone_number, 503, 'WhatsApp bot is not active.');

        $resolvedCode = QrCode::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->value('code');

        if (! $resolvedCode) {
            $resolvedCode = Business::query()
                ->where(function ($query) use ($code): void {
                    $query->where('bot_code', $code)->orWhere('qr_code', $code);
                })
                ->value('bot_code')
                ?? BusinessWorker::query()->where('qr_code', $code)->value('qr_code')
                ?? Table::query()->where('qr_code', $code)->value('qr_code');
        }

        abort_unless($resolvedCode, 404, 'QR code not found.');

        return redirect()->away($botSetting->whatsappUrl($resolvedCode));
    }
}
