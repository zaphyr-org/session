<?php

declare(strict_types=1);

namespace Zaphyr\SessionTests;

use DateTime;
use PHPUnit\Framework\TestCase;
use Zaphyr\Session\Cookie;
use Zaphyr\Session\Exceptions\CookieException;

class CookieTest extends TestCase
{
    /* -------------------------------------------------
     * CONSTRUCTOR AND GETTERS
     * -------------------------------------------------
     */

    public function testConstructorAndGetterMethods(): void
    {
        $cookie = new Cookie(
            $name = 'foo',
            $value = 'bar',
            $expire = 120,
            $path = '/',
            $domain = 'baz',
            true,
            true,
            $sameSite = 'strict'
        );

        self::assertEquals($name, $cookie->getName());
        self::assertEquals($value, $cookie->getValue());
        self::assertEquals($expire, $cookie->getExpire());
        self::assertEquals(0, $cookie->getMaxAge());
        self::assertTrue($cookie->isCleared());
        self::assertEquals($path, $cookie->getPath());
        self::assertEquals($domain, $cookie->getDomain());
        self::assertTrue($cookie->isSecure());
        self::assertTrue($cookie->isHttpOnly());
        self::assertEquals($sameSite, $cookie->getSameSite());
    }

    /**
     * @dataProvider invalidNamesDataProvider
     *
     * @param string $name
     */
    public function testConstructorThrowsExceptionOnInvalidName(string $name): void
    {
        $this->expectException(CookieException::class);

        new Cookie($name, 'baz');
    }

    /**
     * @return array<string[]>
     */
    public static function invalidNamesDataProvider(): array
    {
        return [
            [''],
            [',MyName'],
            [';MyName'],
            [' MyName'],
            ["\tMyName"],
            ["\rMyName"],
            ["\nMyName"],
            ["\013MyName"],
            ["\014MyName"],
        ];
    }

    public function testConstructorThrowsExceptionWhenNameIsEmpty(): void
    {
        $this->expectException(CookieException::class);

        new Cookie('', 'baz');
    }

    public function testConstructorThrowsExceptionOnInvalidExpire(): void
    {
        $this->expectException(CookieException::class);

        new Cookie('foo', 'bar', 'never');
    }

    /* -------------------------------------------------
     * EXPIRE
     * -------------------------------------------------
     */

    public function testSetExpireWithInteger(): void
    {
        $cookie = new Cookie('foo', 'bar');
        $cookie->setExpire($expire = 120);

        self::assertEquals($expire, $cookie->getExpire());
    }

    public function testSetExpireWithDateTimeInstance(): void
    {
        $cookie = new Cookie('foo', 'bar');
        $cookie->setExpire($expire = new DateTime('+2 days'));

        self::assertEquals($expire->getTimestamp(), $cookie->getExpire());
    }

    public function testSetExpireWithNonNumeric(): void
    {
        $cookie = new Cookie('foo', 'bar');
        $cookie->setExpire('now');

        self::assertEquals(time(), $cookie->getExpire());
    }

    public function testSetExpireThrowsExceptionOnInvalidValue(): void
    {
        $this->expectException(CookieException::class);

        $cookie = new Cookie('foo', 'bar');
        $cookie->setExpire('never');
    }

    public function testGetExpireReturnsZeroByDefault(): void
    {
        $cookie = new Cookie('foo', 'bar');

        self::assertEquals(0, $cookie->getExpire());
    }

    public function testGetExpireReturnsZeroOnNegativeExpiration(): void
    {
        $cookie = new Cookie('foo', 'bar');
        $cookie->setExpire(-120);

        self::assertEquals(0, $cookie->getExpire());
    }

    public function testSetExpireCastsValueToInt(): void
    {
        $cookie = new Cookie('foo', 'bar');
        $cookie->setExpire(120.12);

        self::assertEquals(120, $cookie->getExpire());
    }

    /* ------------------------------------------
     * MAX AGE
     * ------------------------------------------
     */

    public function testGetMaxAge(): void
    {
        $cookie = new Cookie('foo', 'bar', new DateTime('+2 days'));

        self::assertGreaterThan(0, $cookie->getMaxAge());
    }

    /* ------------------------------------------
     * CLEARED
     * ------------------------------------------
     */

    public function testIsCleared(): void
    {
        $cookie = new Cookie('foo', 'bar', new DateTime('+2 days'));

        self::assertFalse($cookie->isCleared());
    }

    /* ------------------------------------------
     * PATH
     * ------------------------------------------
     */

    public function testSetPath(): void
    {
        $cookie = new Cookie('foo', 'bar');
        $cookie->setPath($path = 'foo');

        self::assertEquals($path, $cookie->getPath());
    }

    public function testSetPathReturnsRootPathOnEmptyString(): void
    {
        $cookie = new Cookie('foo', 'bar');
        $cookie->setPath('');

        self::assertEquals('/', $cookie->getPath());
    }

    public function testGetPathReturnsRootPathByDefault(): void
    {
        $cookie = new Cookie('foo', 'bar');
        self::assertEquals('/', $cookie->getPath());
    }

    /* ------------------------------------------
     * DOMAIN
     * ------------------------------------------
     */

    public function testSetDomain(): void
    {
        $cookie = new Cookie('foo', 'bar');
        $cookie->setDomain($domain = 'example.com');

        self::assertEquals($domain, $cookie->getDomain());
    }

    public function testGetDomainReturnsNullByDefault(): void
    {
        $cookie = new Cookie('foo', 'bar');

        self::assertNull($cookie->getDomain());
    }

    /**
     * ------------------------------------------
     * SECURE
     * ------------------------------------------
     */

    public function testSetSecure(): void
    {
        $cookie = new Cookie('foo', 'bar');
        $cookie->setSecure(true);

        self::assertTrue($cookie->isSecure());
    }

    public function testSetSecureReturnsFalseByDefault(): void
    {
        $cookie = new Cookie('foo', 'bar');

        self::assertFalse($cookie->isSecure());
    }

    /* ------------------------------------------
     * HTTP ONLY
     * ------------------------------------------
     */

    public function testSetHttpOnly(): void
    {
        $cookie = new Cookie('foo', 'bar');
        $cookie->setHttpOnly(false);

        self::assertFalse($cookie->isHttpOnly());
    }

    public function testGetHttpOnlyReturnsTrueByDefault(): void
    {
        $cookie = new Cookie('foo', 'bar');

        self::assertTrue($cookie->isHttpOnly());
    }

    /* ------------------------------------------
     * SAME SITE
     * ------------------------------------------
     */

    public function testSetSameSiteWithLax(): void
    {
        $cookie = new Cookie('foo', 'bar');
        $cookie->setSameSite('LAX');

        self::assertEquals(Cookie::RESTRICTION_LAX, $cookie->getSameSite());
    }

    public function testSetSameSiteWithStrict(): void
    {
        $cookie = new Cookie('foo', 'bar');
        $cookie->setSameSite('STRICT');

        self::assertEquals(Cookie::RESTRICTION_STRICT, $cookie->getSameSite());
    }

    public function testSameSiteThrowsExceptionOnInvalidArgument(): void
    {
        $this->expectException(CookieException::class);

        $cookie = new Cookie('foo', 'bar');
        $cookie->setSameSite('invalid');
    }

    public function testGetSameSiteReturnsNullByDefault(): void
    {
        $cookie = new Cookie('foo', 'bar');

        self::assertNull($cookie->getSameSite());
    }

    /* ------------------------------------------
     * TO STRING
     * ------------------------------------------
     */

    public function testToString(): void
    {
        $cookie = new Cookie('foo', 'bar');

        self::assertEquals('foo=bar; path=/; httponly', (string)$cookie);
    }

    public function testToStringDeletedCookie(): void
    {
        $cookie = new Cookie('foo', '', 1, '/foo', 'example.com');

        self::assertEquals(
            'foo=deleted; expires='
            . gmdate('D, d-M-Y H:i:s T', $expire = time() - 31536001)
            . '; Max-Age=0; path=/foo; domain=example.com; httponly',
            (string)$cookie
        );
    }

    public function testToStringWithExpireDate(): void
    {
        $cookie = new Cookie(
            'foo',
            'bar',
            strtotime('Wed, 21-Nov-2018 20:48:57 GMT'),
            '/foo',
            'example.com'
        );

        self::assertEquals(
            'foo=bar; expires=Wed, 21-Nov-2018 20:48:57 GMT; '
            . 'Max-Age=0; path=/foo; domain=example.com; httponly',
            (string)$cookie
        );
    }

    public function testToStringWithSecureTrue(): void
    {
        $cookie = new Cookie(
            'foo',
            'bar',
            strtotime('Wed, 21-Nov-2018 20:48:57 GMT'),
            '/foo',
            'example.com',
            true
        );

        self::assertEquals(
            'foo=bar; expires=Wed, 21-Nov-2018 20:48:57 GMT; '
            . 'Max-Age=0; path=/foo; domain=example.com; secure; httponly',
            (string)$cookie
        );
    }

    public function testToStringWithHttpFalse(): void
    {
        $cookie = new Cookie(
            'foo',
            'bar',
            strtotime('Wed, 21-Nov-2018 20:48:57 GMT'),
            '/foo',
            'example.com',
            false,
            false
        );

        self::assertEquals(
            'foo=bar; expires=Wed, 21-Nov-2018 20:48:57 GMT; '
            . 'Max-Age=0; path=/foo; domain=example.com',
            (string)$cookie
        );
    }

    public function testToStringWithSameSiteLax(): void
    {
        $cookie = new Cookie(
            'foo',
            'bar',
            strtotime('Wed, 21-Nov-2018 20:48:57 GMT'),
            '/foo',
            'example.com',
            true,
            true,
            'LAX'
        );

        self::assertEquals(
            'foo=bar; expires=Wed, 21-Nov-2018 20:48:57 GMT; '
            . 'Max-Age=0; path=/foo; domain=example.com; secure; httponly; samesite=lax',
            (string)$cookie
        );
    }

    public function testToStringValueWithSpace(): void
    {
        $cookie = new Cookie('foo', 'bar with spaces');

        self::assertEquals('foo=bar%20with%20spaces; path=/; httponly', (string)$cookie);
    }
}
