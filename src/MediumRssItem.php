<?php

namespace App;

use Symfony\Component\HttpFoundation\Request;

class MediumRssItem
{
    private const RSS_FEED_URL = 'https://medium.com/feed/%s';

    private string $title;
    private string $link;
    private \DateTimeImmutable $pubDate;
    private string $creator;
    private ?string $image;
    private string $summary;

    private function __construct(
        string $userName,
        int $postIndex,
        private bool $needsRedirect
    )
    {

        $content = preg_replace(
            '/&(?!#?[a-z0-9]+;)/',
            '&amp;',
            file_get_contents(sprintf(self::RSS_FEED_URL, $userName))
        );
        $feed = new \SimpleXMLElement($content);

        if (empty($feed->channel->item[$postIndex])) {
            throw new \RuntimeException('Could not fetch post with index ' . $postIndex);
        }

        $item = $feed->channel->item[$postIndex];

        $this->title = $item->title;
        $this->link = $item->link;
        $this->pubDate = \DateTimeImmutable::createFromFormat('D, d M Y H:i:s e', $item->pubDate);
        $this->creator = $item->children('dc', true)->creator;

        $content = (string)$item->children('http://purl.org/rss/1.0/modules/content/')->encoded;
        $this->image = $this->extractImageSource($content);
        $this->summary = $this->extractSummary($content);
    }

    public function getTitle(): string
    {
        return (string)$this->title;
    }

    public function getLink(): string
    {
        return (string)$this->link;
    }

    public function getPubDate(): \DateTimeImmutable
    {
        return $this->pubDate;
    }

    public function getCreator(): string
    {
        return (string)$this->creator;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function getSummary(): string
    {
        return $this->summary;
    }

    public function needsRedirect(): bool
    {
        return $this->needsRedirect;
    }

    public static function fromRequest(Request $request): self
    {
        $uriParts = explode(
            '/',
            trim($request->getRequestUri(), '/')
        );

        if (!in_array(count($uriParts), [2, 3])) {
            throw new \RuntimeException('Provide your Medium username and the index of the post. Eg: @robiningelbrecht/0');
        }

        if (!str_starts_with($uriParts[0], '@')) {
            throw new \RuntimeException('Make sure your username stats with "@"');
        }

        return new self($uriParts[0], intval($uriParts[1]), !empty($uriParts[2]) && $uriParts[2] === 'link');
    }

    private function extractImageSource(string $content): ?string
    {
        $regex = '#src="(.*?)"#';
        preg_match($regex, $content, $matches);

        if (empty($matches[1])) {
            return null;
        }

        $type = pathinfo($matches[1], PATHINFO_EXTENSION);
        $data = file_get_contents($matches[1]);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }

    private function extractSummary($content): ?string
    {
        $regex = '#<p>(.*?)</p>#';
        preg_match($regex, $content, $matches);

        if (empty($matches[1])) {
            return null;
        }

        return substr(strip_tags($matches[1]), 0, 60) . ' ...';
    }
}