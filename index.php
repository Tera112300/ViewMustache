<?php
include __DIR__ . '/ViewMustache.php';
const VIEW_DIR = __DIR__ . '/views/';
const CACHE_DIR = __DIR__ . '/cache/';
const HELPER_DIR = __DIR__ . '/helper/';
$view = new ViewMustache(VIEW_DIR, CACHE_DIR,HELPER_DIR);

$view->helper(["toOsaka"]);
echo $view->render('index', [
  'title' => 'Articles',
  'value' => 'ViewMustacheを使ってくれてどうもありがとう。',
  'articles' => [
    'article1',
    'article2',
    'article3',
  ],
  'hoge' => 10,
  'base_url' => 'http://example.com'
]);
