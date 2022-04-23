<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use \App\Layout;
use App\RssItemFactory;
use App\RssItem;
use Symfony\Component\HttpFoundation\Request;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;


$loader = new FilesystemLoader(dirname(__DIR__) . '/templates');
$twig = new Environment($loader);
$request = Request::createFromGlobals();

$rssItems = [];
$render = null;

var_dump('here');

try {
    $rssItems = RssItemFactory::createMultipleFromRequest($request);
    $layout = Layout::createFromRequest($request);

    $template = $twig->load('rss-items.html.twig');
    $render = $template->render([
        'height' => $layout->getHeight(count($rssItems)),
        'layout' => $layout->value,
        'items' => array_map(fn(RssItem $rssItem): array => [
            'title' => $rssItem->getTitle(),
            'pubDate' => $rssItem->getPubDate()->format('D M d Y, H:i'),
            'link' => $rssItem->getLink(),
            'image' => $rssItem->getImage(),
            'summary' => $rssItem->getSummary(),
        ], $rssItems),
    ]);
} catch (Throwable $e) {
    $template = $twig->load('error.html.twig');
    $render = $template->render([
        'message' => $e->getMessage(),
    ]);
}

if (count($rssItems) === 1 && str_contains($request->getRequestUri(), '/link')) {
    // Redirect to external link of RSS item.
    header('Location: ' . $rssItems[0]->getLink());
    exit();
}

header('Content-type: image/svg+xml');
echo $render;

