<?php

namespace App\Services;

use App\Models\BotSetting;
use App\Models\Business;
use App\Models\BusinessWorker;
use App\Models\QrCode;
use App\Models\Table;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class QrCodeService
{
    public function generateBusinessQrCode(Business $business): QrCode
    {
        if (! $business->bot_code) {
            $business->generateBotCode();
        }

        if ($business->qr_code !== $business->bot_code) {
            $business->updateQuietly(['qr_code' => $business->bot_code]);
        }

        $qrCode = QrCode::updateOrCreate([
            'code' => $business->qr_code,
            'type' => 'business',
            'business_id' => $business->id,
        ], [
            'name' => $business->name,
            'is_active' => true,
            'metadata' => ['bot_code' => $business->bot_code],
        ]);

        $this->generateQrImage($qrCode);

        return $qrCode;
    }

    public function generateWorkerQrCode(BusinessWorker $link): QrCode
    {
        if (! $link->qr_code) {
            $link->qr_code = QrCode::generateUniqueCode();
            $link->save();
        }

        $qrCode = QrCode::updateOrCreate([
            'code' => $link->qr_code,
            'type' => 'worker',
            'business_id' => $link->business_id,
            'worker_id' => $link->id,
        ], [
            'name' => $link->worker->name,
            'is_active' => true,
            'metadata' => [
                'worker_id' => $link->worker_id,
                'global_number' => $link->worker->global_number,
            ],
        ]);

        $this->generateQrImage($qrCode);

        return $qrCode;
    }

    public function generateTableQrCode(Table $table): QrCode
    {
        if (! $table->qr_code) {
            $table->qr_code = QrCode::generateUniqueCode();
            $table->save();
        }

        $qrCode = QrCode::updateOrCreate([
            'code' => $table->qr_code,
            'type' => 'table',
            'business_id' => $table->business_id,
            'table_id' => $table->id,
        ], [
            'name' => $table->display_name,
            'is_active' => true,
            'metadata' => ['display_name' => $table->display_name],
        ]);

        $this->generateQrImage($qrCode);

        return $qrCode;
    }

    private function generateQrImage(QrCode $qrCode): void
    {
        $qrUrl = $this->resolveQrTargetUrl($qrCode->code);
        $extension = 'png';
        $image = null;

        try {
            $response = Http::timeout(10)->get('https://api.qrserver.com/v1/create-qr-code/', [
                'size' => '300x300',
                'data' => $qrUrl,
            ]);

            if ($response->successful() && $response->body() !== '') {
                $image = $response->body();
            }
        } catch (\Throwable $exception) {
            report($exception);
        }

        if (! $image) {
            $extension = 'svg';
            $image = $this->fallbackSvg($qrCode->code, $qrUrl);
        }

        $path = "qr-codes/{$qrCode->type}/{$qrCode->code}.{$extension}";
        Storage::disk('public')->put($path, $image);

        $qrCode->updateQuietly(['qr_image_path' => $path]);

        match ($qrCode->type) {
            'business' => $qrCode->business?->updateQuietly(['qr_image_path' => $path]),
            'worker' => $qrCode->worker?->updateQuietly(['qr_image_path' => $path]),
            'table' => $qrCode->table?->updateQuietly(['qr_image_path' => $path]),
            default => null,
        };
    }

    private function resolveQrTargetUrl(string $code): string
    {
        $botSetting = BotSetting::current();

        if ($botSetting?->phone_number) {
            return $botSetting->whatsappUrl($code);
        }

        return route('qr.scan', ['code' => $code]);
    }

    private function fallbackSvg(string $code, string $url): string
    {
        $escapedCode = e($code);
        $escapedUrl = e($url);

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="300" height="300" viewBox="0 0 300 300">
  <rect width="300" height="300" fill="#ffffff"/>
  <rect x="14" y="14" width="272" height="272" rx="24" fill="#f6f3eb" stroke="#1f2624" stroke-width="4"/>
  <text x="150" y="112" text-anchor="middle" font-family="monospace" font-size="22" font-weight="700" fill="#1f2624">TIPTA QR</text>
  <text x="150" y="152" text-anchor="middle" font-family="monospace" font-size="20" fill="#bc6c25">{$escapedCode}</text>
  <text x="150" y="188" text-anchor="middle" font-family="monospace" font-size="10" fill="#57615c">Scan disabled locally</text>
  <text x="150" y="208" text-anchor="middle" font-family="monospace" font-size="10" fill="#57615c">Open this URL manually:</text>
  <text x="150" y="232" text-anchor="middle" font-family="monospace" font-size="8" fill="#57615c">{$escapedUrl}</text>
</svg>
SVG;
    }

    public function regenerateQrCode(QrCode $qrCode): QrCode
    {
        $newCode = QrCode::generateUniqueCode();

        if ($qrCode->type === 'business') {
            $qrCode->business?->update(['bot_code' => $newCode, 'qr_code' => $newCode]);
        } elseif ($qrCode->type === 'worker') {
            $qrCode->worker?->update(['qr_code' => $newCode]);
        } elseif ($qrCode->type === 'table') {
            $qrCode->table?->update(['qr_code' => $newCode]);
        }

        $qrCode->code = $newCode;
        $qrCode->save();

        $this->generateQrImage($qrCode);

        return $qrCode;
    }

    public function deactivateQrCode(QrCode $qrCode): void
    {
        $qrCode->is_active = false;
        $qrCode->save();
    }

    public function activateQrCode(QrCode $qrCode): void
    {
        $qrCode->is_active = true;
        $qrCode->save();
    }

    public function getQrCodeUrl(QrCode $qrCode): ?string
    {
        if (! $qrCode->qr_image_path) {
            return null;
        }

        return Storage::url($qrCode->qr_image_path);
    }
}
