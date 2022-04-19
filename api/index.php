<?php

require dirname(__DIR__) . '/vendor/autoload.php';

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

try {
    $rssItems = RssItemFactory::createMultipleFromRequest($request);
    $imageHeight = count($rssItems) * 125 - 5;
    $needsTwoColLayout = $request->query->has('two-col') && count($rssItems) > 1;
    if ($needsTwoColLayout) {
        $imageHeight = ceil(count($rssItems) / 2) * 125 - 5;
    }

    $template = $twig->load('rss-items.html.twig');
    $render = $template->render([
        'height' => $imageHeight,
        'two_col' => $needsTwoColLayout,
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

