<?php

namespace App;

use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Request;

class RssItemFactory
{
    private const RSS_FEED_URL = 'https://medium.com/feed/%s';

    public static function createMultipleFromRequest(Request $request): array
    {
        var_dump('1');
        $uriParts = explode(
            '/',
            trim($request->getPathInfo(), '/')
        );
        var_dump('2');
        if (count($uriParts) < 2) {
            throw new \RuntimeException('Provide your Medium username and the index of the post. Eg: @robiningelbrecht/0');
        }
        var_dump('3');
        [$userName, $itemIndexesString] = $uriParts;
        var_dump('4');
        if (!str_starts_with($userName, '@')) {
            throw new \RuntimeException('Make sure your username stats with "@"');
        }
        var_dump('5');
        var_dump('getttt');
        $response = (new Client())->request('GET', sprintf(self::RSS_FEED_URL, $userName));

        if ($response->getStatusCode() != 200) {
            throw new \RuntimeException('Could not fetch your RSS feed. Did you provide a valid username?');
        }

        $content = preg_replace(
            '/&(?!#?[a-z0-9]+;)/',
            '&amp;',
            $response->getBody()->getContents()
        );
        $feed = new \SimpleXMLElement($content);

        $itemIndexes = explode(',', $itemIndexesString);
        foreach ($itemIndexes as $itemIndex) {
            if (empty($feed->channel->item[(int)$itemIndex])) {
                throw new \RuntimeException('Could not fetch post with index ' . $itemIndex);
            }
        }

        return array_map(fn(int $itemIndex): RssItem => new RssItem(
            $feed->channel->item[(int)$itemIndex]
        ), $itemIndexes);
    }
}