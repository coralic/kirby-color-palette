<?php

use Kirby\Cms\App;

if (!class_exists('SylvainJule\ColorPalette')) {
  require_once __DIR__ . '/lib/color-palette.php';
}

require_once __DIR__ . '/vendor/autoload.php';

App::plugin('sylvainjule/color-palette', [
  'options' => [
    'cache' => true,
  ],
  'fields' => require_once __DIR__ . '/lib/fields.php',
  'translations' => [
    'en' => require_once __DIR__ . '/lib/languages/en.php',
    'de' => require_once __DIR__ . '/lib/languages/de.php',
    'fr' => require_once __DIR__ . '/lib/languages/fr.php',
  ],
]);
