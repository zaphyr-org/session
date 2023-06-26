<?php

declare(strict_types=1);

namespace Zaphyr\Session;

use DateTimeInterface;
use Zaphyr\Session\Cookie;
use Zaphyr\Session\Exceptions\CookieException;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class Utils
{
    /**
     * @param DateTimeInterface|int|float|string $expire
     *
     * @throws CookieException
     * @return int
     */
    public static function prepareExpire(DateTimeInterface|int|float|string $expire): int
    {
        if ($expire instanceof DateTimeInterface) {
            $expire = $expire->format('U');
        }

        if (!is_numeric($expire)) {
            $expire = strtotime($expire);

            if ($expire === false) {
                throw new CookieException('Cookie expiration time is not valid.');
            }
        }

        return $expire > 0 ? (int)$expire : 0;
    }

    /**
     * @param string $sameSite
     *
     * @throws CookieException
     * @return string
     */
    public static function validateSameSiteRestrictions(string $sameSite): string
    {
        $sameSite = strtolower($sameSite);

        if (!in_array($sameSite, [Cookie::RESTRICTION_LAX, Cookie::RESTRICTION_STRICT], true)) {
            throw new CookieException(
                'Cookie sameSite parameter "' . $sameSite . '" is not valid. '
                . 'Must be "' . Cookie::RESTRICTION_LAX . '" or "' . Cookie::RESTRICTION_STRICT . '"',
            );
        }

        return $sameSite;
    }
}
