<?php

declare(strict_types=1);

namespace PhpPico\Utility\Tests;

use PhpPico\Utility\IpAddress;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use InvalidArgumentException;

#[CoversClass(IpAddress::class)]
final class IpAndCidrTest extends TestCase
{
    #[Test]
    #[DataProvider('identicalIpAddresses')]
    public function matches_exact_ip(string $ip): void
    {
        $this->assertTrue(
            new IpAddress()->matches($ip, $ip),
            'IpAddress::matches() must return TRUE when provided two identical IP addresses',
        );
    }

    public static function identicalIpAddresses(): array
    {
        return [
            ['127.0.0.1'],
            ['192.168.1.1'],
            ['::1'],
            ['::ffff:0a00:0001'],
        ];
    }

    #[Test]
    public function in_subnet_returns_true_for_ip_in_subnet(): void
    {
        $ip   = '10.0.10.20';
        $cidr = '10.0.0.0/8';

        $this->assertTrue(
            new IpAddress()->inSubnet($ip, $cidr),
            'IpAddress::inSubnet() must return TRUE if the provided IP address is in the provided subnet (CIDR)',
        );
    }

    #[Test]
    public function in_subnet_returns_false_for_ip_outside_subnet(): void
    {
        $ip   = '127.0.0.1';
        $cidr = '10.0.0.0/8';

        $this->assertFalse(
            new IpAddress()->inSubnet($ip, $cidr),
            'IpAddress::inSubnet() must return FALSE if the provided IP address is outside the provided subnet (CIDR)',
        );
    }

    #[Test]
    #[DataProvider('validIpv4Addresses')]
    public function matches_and_in_subnet_methods_return_the_same_results_for_same_ip_address_and_ip(string $ip): void
    {
        $cidr = '10.0.0.0/8';

        $matches  = new IpAddress()->matches($ip, $cidr);
        $inSubnet = new IpAddress()->inSubnet($ip, $cidr);

        $this->assertEquals(
            $matches,
            $inSubnet,
            'IpAddress::matches() and IpAddress::inSubnet() must return the same result for the same input when the $ipOrCidr value is a subnet',
        );
    }

    #[Test]
    #[DataProvider('invalidIpv4Addresses')]
    #[DataProvider('invalidIpv6Addresses')]
    public function matches_throws_exception_on_invalid_ip_address(string $invalidIp): void
    {
        $this->expectException(InvalidArgumentException::class);
        new IpAddress()->matches($invalidIp, $invalidIp);
    }

    #[Test]
    #[DataProvider('invalidIpv4Addresses')]
    #[DataProvider('invalidIpv6Addresses')]
    public function in_subnet_throws_exception_on_invalid_ip_address(string $invalidIp): void
    {
        $this->expectException(InvalidArgumentException::class);
        new IpAddress()->inSubnet($invalidIp, $invalidIp);
    }

    #[Test]
    #[DataProvider('validIpv4Addresses')]
    public function valid_ipv4_returns_true(string $ip): void
    {
        $this->assertTrue(
            new IpAddress()->isIpv4($ip),
            'IpAddress::isIpv4() must return TRUE when provided a valid IPv4 address.',
        );
    }

    public static function validIpv4Addresses(): array
    {
        return [
            ['127.0.0.1'],
            ['10.0.0.1'],
            ['192.168.1.1'],
        ];
    }

    #[Test]
    #[DataProvider('validIpv6Addresses')]
    public function valid_ipv6_returns_true(string $ip): void
    {
        $this->assertTrue(
            new IpAddress()->isIpv6($ip),
            'IpAddress::isIpv6() must return TRUE when provided a valid IPv6 address.',
        );
    }

    public static function validIpv6Addresses(): array
    {
        return [
            ['::1'],
            ['::ffff:7f00:0001'],
            ['0000:0000:0000:0000:0000:ffff:7f00:0001'],
            ['::ffff:0a00:0001'],
            ['0000:0000:0000:0000:0000:ffff:0a00:0001'],
            ['::ffff:c0a8:0101'],
            ['0000:0000:0000:0000:0000:ffff:c0a8:0101'],
        ];
    }

    #[Test]
    #[DataProvider('invalidIpv4Addresses')]
    public function invalid_ipv4_returns_false(string $ip): void
    {
        $this->assertFalse(
            new IpAddress()->isIpv4($ip),
            'IpAddress::isIpv4() must return FALSE when provided an invalid IPv4 address.',
        );
    }

    public static function invalidIpv4Addresses(): array
    {
        return [
            ['nonsense'],
            ['invalid.ip.v4.address'],
        ];
    }


    #[Test]
    #[DataProvider('invalidIpv6Addresses')]
    public function invalid_ipv6_returns_false(string $ip): void
    {
        $this->assertFalse(
            new IpAddress()->isIpv6($ip),
            'IpAddress::isIpv6() must return FALSE when provided an invalid IPv6 address.',
        );
    }

    public static function invalidIpv6Addresses(): array
    {
        return [
            ['nonsense'],
            ['an.invalid.ip.v6.address.example'],
        ];
    }
}
