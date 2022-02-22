<?php

use Kirby\Cms\FilePicker;
use Kirby\Data\Yaml;
use coralic\ColorPalette;

return [
  'color-palette' => [
    'mixins' => ['filepicker'],
    'extends' => 'radio',
    'props' => [
      'options' => function ($options = []) {
        return $options;
      },
      'display' => function ($display = 'single') {
        return $display;
      },
      'size' => function ($size = 'medium') {
        return $size;
      },
      'unselect' => function ($unselect = false) {
        return $unselect;
      },
      'limit' => function ($limit = 10) {
        return $limit;
      },
      'queryLimit' => function ($limit = 20) {
        return $limit;
      },
      'default' => function ($default = false) {
        return $default;
      },
      'extract' => function ($extract = false) {
        return $extract;
      },
      'value' => function ($value = null) {
        $yaml = Yaml::decode($value);
        return count($yaml) ? $yaml : $value;
      },
    ],
    'computed' => [
      'parentModel' => function () {
        if (is_string($this->parent) === true && $model = $this->model()->query($this->parent, 'Kirby\Cms\Model')) {
          return $model;
        }
        return $this->model();
      },
      'parent' => function () {
        return $this->parentModel->apiUrl(true);
      },
      'options' => function () {
        $options = $this->options;

        if ($options == 'query') {
          return $this->getOptions();
        }

        if (is_string($this->extract)) {
          $image = $this->parentModel->query($this->extract);

          // validate the query result
          if (
            is_a($image, 'Kirby\Cms\File') === true ||
            is_a($image, 'Kirby\Filesystem\Asset') === true
          ) {
            $options = ColorPalette::extractColor($image, $this->limit);
          }
        }

        return $options;
      },
      'default' => function () {
        return $this->default;
      },
      'value' => function () {
        return $this->value;
      },
    ],
    'api' => function () {
      return [
        [
          'pattern' => "/",
          'action' => function () {
            $field = $this->field();
            $params = [
              'model' => $field->parentModel,
              'image'  => $field->image(),
              'info'   => $field->info(),
              'limit'  => $field->queryLimit(),
              'page'   => $this->requestQuery('page'),
              'query'  => $field->query(),
              'search' => $this->requestQuery('search'),
              'text'   => $field->text()
            ];

            return (new FilePicker($params))->toArray();
          }
        ],
        [
          'pattern' => '/extract',
          'method'  => 'GET',
          'action'  => function () {
            $field = $this->field();
            $file = get('file');
            $limit = $field->limit();
            $file = $field->parentModel->files()->find($file);

            try {
              $colors = ColorPalette::extractColor($file, $limit);
              $response = [
                'status' => 'success',
                'colors' => $colors,
              ];
              return $response;
            } catch (Exception $e) {
              $response = [
                'status' => 'error',
                'message' => get('file')
              ];
              return $response;
            }
          }
        ]
      ];
    }
  ],
];
