<?php

declare(strict_types=1);

namespace App\Application\Helpers;

class TagsHelper
{
    /**
     * @param array $tags
     * @return array
     */
    public static function prepareTags(array $tags): array
    {
        sort($tags, SORT_STRING);

        $tags = array_unique($tags);

        foreach ($tags as &$tag)
        {
            $tag = trim($tag);
            $tag = str_replace(' ', '_', $tag);
        }

        return $tags;
    }
}