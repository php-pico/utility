<?php

declare(strict_types=1);

namespace PhpPico\Utility;

use InvalidArgumentException;

final readonly class IpAddress
{
    /**
     * Check if a given IP address matches an IP or is in a given subnet (CIDR).
     *
     * @param string $ip The IP address to check.
     * @param string $ipOrCidr The IP address or subnet/CIDR to match against.
     *
     * @return bool
     * @throws InvalidArgumentException if $ip is not a valid IPv4 or IPv6 address.
     */
    public function matches(string $ip, string $ipOrCidr): bool
    {
        if (!$this->isIpv4($ip) && !$this->isIpv6($ip)) {
            throw new InvalidArgumentException('Invalid IP address. Must be a valid IPv4 or IPv6 address');
        }

        if (str_contains($ipOrCidr, '/')) {
            return $this->inSubnet($ip, $ipOrCidr);
        }

        $a = @inet_pton($ip);
        $b = @inet_pton($ipOrCidr);

        return $a !== false && $b !== false && $a === $b;
    }

    /**
     * Check if a given IP address is in a given subnet (CIDR).
     *
     * @param string $ip The IP address to check.
     * @param string $cidr The subnet/CIDR to match against.
     *
     * @return bool
     * @throws InvalidArgumentException if $ip is not a valid IPv4 or IPv6 address.
     */
    public function inSubnet(string $ip, string $cidr): bool
    {
        if (!$this->isIpv4($ip) && !$this->isIpv6($ip)) {
            throw new InvalidArgumentException('Invalid IP address. Must be a valid IPv4 or IPv6 address');
        }

        [$subnet, $bits] = explode('/', $cidr, 2) + [1 => null];

        $ipBin = @inet_pton($ip);
        $subnetBin = @inet_pton($subnet);

        if ($ipBin === false || $subnetBin === false) {
            return false;
        }

        if (strlen($ipBin) !== strlen($subnetBin)) {
            return false;
        }

        $maxBits = strlen($ipBin) * 8;
        $bits = $bits === null ? $maxBits : (int) $bits;

        if ($bits < 0 || $bits > $maxBits) {
            return false;
        }

        $bytes = intdiv($bits, 8);
        $remainder = $bits % 8;

        if ($bytes > 0 && substr($ipBin, 0, $bytes) !== substr($subnetBin, 0, $bytes)) {
            return false;
        }

        if ($remainder !== 0) {
            $mask = (~0 << (8 - $remainder)) & 0xFF;
            if ((ord($ipBin[$bytes]) & $mask) !== (ord($subnetBin[$bytes]) & $mask)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if a given IP address is IPv4.
     *
     * @param string $ip
     *
     * @return bool
     */
    public function isIpv4(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    }

    /**
     * Check if a given IP address is IPv6.
     *
     * @param string $ip
     *
     * @return bool
     */
    public function isIpv6(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }
}
