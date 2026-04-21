<?php
declare(strict_types=1);

final class UnknownCarrierException extends \RuntimeException
{
    public function __construct(string $carrier)
    {
        parent::__construct("Unknown carrier: {$carrier}");
    }
}
