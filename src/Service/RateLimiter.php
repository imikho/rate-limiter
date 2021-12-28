<?php

namespace App\Service;

use App\Struct\RateLimiterConfig;
use App\Struct\SlidingWindowPayload;
use Predis\ClientInterface;
use Symfony\Component\HttpFoundation\Request;

final class RateLimiter
{
    private ClientInterface $storage;

    public function __construct(ClientInterface $configStorage)
    {
        $this->storage = $configStorage;
    }

    public function limit(Request $request): bool
    {
        $configKey = $this->provideConfigKey($request);

        if (null === $configKey) {
            return false;
        }

        $config = $this->getConfig($configKey);

        $payloadKey = $this->providePayloadKey($request, $configKey);

        $payload = $this->getPayload($payloadKey, $config);

        if ((microtime(true) - $payload->timestamp) > $config->intervalLength) {
            $payload = SlidingWindowPayload::createInitial($payload->currentCount);
        }

        $limitExceeded = $this->isLimitExceeded($config, $payload);

        if (!$limitExceeded) {
            $payload->currentCount++;
        }

        $this->storage->set($payloadKey, json_encode($payload));
        return $limitExceeded;
    }

    private function isLimitExceeded(RateLimiterConfig $config, SlidingWindowPayload $payload): float
    {
        $previousPart = ($config->intervalLength - (microtime(true) - $payload->timestamp)) / $config->intervalLength;

        return ($payload->currentCount + $payload->previousCount * $previousPart) > $config->requestCount;
    }

    private function getPayload(string $key, RateLimiterConfig $config): SlidingWindowPayload
    {
        $payload = $this->storage->get($key);

        if (null === $payload) {
            $payload = SlidingWindowPayload::createInitial($config->requestCount);
            $this->storage->set($key, json_encode($payload));
        } else {
            $payload = SlidingWindowPayload::createFromArray(json_decode($payload, true));
        }

        return $payload;
    }

    /**
     * Returns unique postfix per rate limitation client
     */
    private function getKeyPostfix(Request $request): string
    {
        // return user id, session id, ip address, fingerprint, etc.
        return 'test111';
    }

    /**
     * Returns key of config with rate limitation for specific request
     */
    private function provideConfigKey(Request $request): ?string
    {
        // return key of config, specified by request context: endpoint, user attribute, locale, time etc.
        return 'test111';
    }

    private function getConfig($key): RateLimiterConfig
    {
        $config = $this->storage->get($key);

        if (null === $config) {
            // this should be done in configuration layer
            $config = new RateLimiterConfig(3, 3);
            $this->storage->set($key, json_encode($config));
        } else {
            $config = RateLimiterConfig::createFromArray(json_decode($config, true));
        }

        return $config;
    }

    private function providePayloadKey(Request $request, string $configKey): string
    {
        $postfix = $this->getKeyPostfix($request);

        $key = $configKey . '_' . $postfix;
        return $key;
    }
}
