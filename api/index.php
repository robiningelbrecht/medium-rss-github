<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use App\RssItemFactory;
use App\RssItem;
use Symfony\Component\HttpFoundation\Request;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

$request = Request::createFromGlobals();
$rssItems = RssItemFactory::createMultipleFromRequest($request);

if (count($rssItems) === 1 && str_contains($request->getRequestUri(), '/link')) {
    // Redirect to external link of RSS item.
    header('Location: ' . $rssItems[0]->getLink());
    exit();
}

$loader = new FilesystemLoader(dirname(__DIR__) . '/templates');
$twig = new Environment($loader);

header('Content-type: image/svg+xml');

$template = $twig->load('rss-items.html.twig');
echo $template->render([
    'items' => array_map(fn(RssItem $rssItem): array => [
        'title' => $rssItem->getTitle(),
        'pubDate' => $rssItem->getPubDate()->format('D M Y, H:i'),
        'link' => $rssItem->getLink(),
        'image' => $rssItem->getImage(),
        'summary' => $rssItem->getSummary(),
    ], $rssItems),
]);