<?php

namespace App;

use Symfony\Component\HttpFoundation\Request;

enum Layout: string
{
    case DEFAULT = 'default';
    case TWO_COL = 'two-col';

    public function getHeight(int $numberOfItems): int
    {
        return match ($this) {
            self::DEFAULT => $numberOfItems * 125 - 5,
            self::TWO_COL => ceil($numberOfItems / 2) * 125 - 5,
        };
    }

    public static function createFromRequest(Request $request): self
    {
        $uriParts = array_filter(explode(
            '/',
            trim($request->getPathInfo(), '/')
        ), fn(string $uriPart) => str_contains($uriPart, 'layout:'));

        if (!$uriParts) {
            return Layout::DEFAULT;
        }

        $layout = reset($uriParts);
        return Layout::from(str_replace('layout:', '', $layout));
    }

}