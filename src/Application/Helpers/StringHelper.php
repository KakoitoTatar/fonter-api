<?php
declare(strict_types=1);

namespace App\Application\Helpers;

class StringHelper
{
    /**
     * @param int $length
     * @return string
     * @throws \Exception
     */
    public static function generateRandomString($length = 10): string
    {
        if ($length <= 0) {
            throw new \InvalidArgumentException('Длина не может быть меньше 1');
        }
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function prepareTags(array $tags): string
    {
        $tags = array_filter($tags);

        $typeStripper = static function ($name) {
            $name = strip_tags($name);
            $name = trim($name);
            return preg_replace('/\s+/', '_', $name);
        };

        $tags = array_map(
            $typeStripper,
            $tags
        );

        $tags = array_unique($tags);
        sort($tags, SORT_STRING);

        return implode(',', $tags);
    }
}
