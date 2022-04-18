<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use App\MediumRssItem;
use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();
$mediumRssItem = MediumRssItem::fromRequest($request);

if ($mediumRssItem->needsRedirect()) {
    header('Location: ' . $mediumRssItem->getLink());
    exit();
}

header('Content-type: image/svg+xml');

echo '
<svg xmlns="http://www.w3.org/2000/svg" fill="none" width="800" height="120">
    <foreignObject width="100%" height="100%">
        <div xmlns="http://www.w3.org/1999/xhtml">
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                    font-family: sans-serif
                }

                .flex {
                    display: flex;
                    align-items: center;
                }

                .container {
                    height: 120px;
                    border: 1px solid rgba(0, 0, 0, .2);
                    padding: 10px 20px;
                    border-radius: 10px;
                    background: rgb(255, 255, 255);
                    overflow: hidden;
                    text-overflow: ellipsis;
                }

                img {
                    margin-right: 10px;
                    width: 150px;
                    height: 100%;
                    object-fit: cover;
                }

                .right {
                    flex: 1;
                }

                a {
                    text-decoration: none;
                    color: inherit
                }

                p {
                    line-height: 1.5;
                    color: #555
                }

                h3 {
                    color: #333
                }

                small {
                    color: #888;
                    display: block;
                    margin-top: 5px;
                    margin-bottom: 8px
                }
            </style>
            <a class="container flex" href="' . $mediumRssItem->getLink() . '" target="__blank">
                <img src="' . $mediumRssItem->getImage() . '" />
                <div class="right">
                    <h3>' . $mediumRssItem->getTitle() . '</h3>
                    <small>' . $mediumRssItem->getPubDate()->format('D M Y, H:i') . '</small>
                    <p>' . $mediumRssItem->getSummary() . '</p>
                </div>
            </a>
        </div>
    </foreignObject>
</svg>';
