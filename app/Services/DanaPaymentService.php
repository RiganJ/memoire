<?php

namespace App\Services;

use App\Contracts\DanaQrisGateway;
use App\Data\DanaQrisResult;
use App\Data\DanaWebhookData;
use App\Exceptions\DanaConfigurationException;
use App\Models\Payment;
use Carbon\CarbonImmutable;
use Dana\Configuration;
use Dana\Utils\SnapHeader;
use Dana\Webhook\v1\WebhookParser;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class DanaPaymentService implements DanaQrisGateway
{
    private const GENERATE_QRIS_PATH = '/v1.0/qr/qr-mpm-generate.htm';

    public function generate(Payment $payment): DanaQrisResult
    {
        $configuration = $this->configuration();
        $storeId = $this->requiredConfig('dana.store_id', 'DANA_STORE_ID');
        $channelId = $this->requiredConfig('dana.channel_id', 'DANA_CHANNEL_ID');

        $payload = [
            'merchantId' => $this->requiredConfig('dana.merchant_id', 'DANA_MERCHANT_ID'),
            'partnerReferenceNo' => $payment->partner_reference_no,
            'amount' => ['value' => number_format((float) $payment->amount, 2, '.', ''), 'currency' => $payment->currency],
            'storeId' => $storeId,
            'additionalInfo' => [
                'terminalSource' => 'MER',
                'envInfo' => ['terminalType' => 'SYSTEM', 'orderTerminalType' => 'WEB'],
            ],
        ];
        $body = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        $headers = SnapHeader::generateHeaders('POST', self::GENERATE_QRIS_PATH, $body, '', $configuration, true);
        $headers['CHANNEL-ID'] = $channelId;

        try {
            $response = Http::acceptJson()
                ->withHeaders($headers)
                ->withBody($body, 'application/json')
                ->timeout(8)
                ->post(rtrim((string) config('dana.base_url'), '/').self::GENERATE_QRIS_PATH);
        } catch (ConnectionException $exception) {
            throw new RuntimeException('DANA tidak merespons dalam batas waktu.', previous: $exception);
        }

        $data = $response->json();
        if (! $response->successful() || ! is_array($data) || ($data['responseCode'] ?? null) !== '2004700' || empty($data['qrContent'])) {
            throw new RuntimeException('DANA menolak pembuatan QRIS: '.($data['responseCode'] ?? 'unknown'));
        }

        return new DanaQrisResult(
            referenceNo: (string) ($data['referenceNo'] ?? ''),
            qrContent: (string) $data['qrContent'],
            qrUrl: isset($data['qrUrl']) ? (string) $data['qrUrl'] : null,
            qrImage: isset($data['qrImage']) ? (string) $data['qrImage'] : null,
        );
    }

    public function parseWebhook(string $method, string $path, array $headers, string $body): DanaWebhookData
    {
        $notification = WebhookParser::create()->parseWebhook($method, $path, $headers, $body);
        $amount = $notification->getAmount();

        if ($amount === null) {
            throw new RuntimeException('Notifikasi DANA tidak memiliki nominal.');
        }

        return new DanaWebhookData(
            partnerReferenceNo: (string) $notification->getOriginalPartnerReferenceNo(),
            referenceNo: $notification->getOriginalReferenceNo(),
            merchantId: (string) $notification->getMerchantId(),
            amount: (string) $amount->getValue(),
            currency: (string) $amount->getCurrency(),
            status: (string) $notification->getLatestTransactionStatus(),
            finishedAt: $notification->getFinishedTime() ? CarbonImmutable::parse($notification->getFinishedTime()) : null,
        );
    }

    private function configuration(): Configuration
    {
        $configuration = new Configuration;
        $configuration->setHost((string) config('dana.base_url'));
        $configuration->setApiKey('X_PARTNER_ID', $this->requiredConfig('dana.x_partner_id', 'DANA_CLIENT_ID'));
        $configuration->setApiKey('PRIVATE_KEY_PATH', $this->requiredConfig('dana.private_key_path', 'DANA_PRIVATE_KEY_PATH'));
        $configuration->setApiKey('ORIGIN', (string) parse_url((string) config('app.url'), PHP_URL_HOST));
        $configuration->setApiKey('DANA_ENV', (string) config('dana.env', 'sandbox'));
        $configuration->setApiKey('ENV', (string) config('dana.env', 'sandbox'));

        return $configuration;
    }

    private function requiredConfig(string $key, string $environmentName): string
    {
        $value = config($key);
        if (! is_string($value) || trim($value) === '') {
            throw new DanaConfigurationException($environmentName.' is not configured.');
        }

        return $value;
    }
}
