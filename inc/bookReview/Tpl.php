<?php

namespace bookReview;

use Handlebars\Handlebars;

class Tpl
{
    public static function get($tplName, $data = array())
    {
        $viewsOptions = apply_filters('book-review/template/options', array('extension' => '.hbs'));
        $partialOptions = apply_filters('book-review/template/partials-options', $viewsOptions);

        $tplPath = apply_filters('book-review/template/views-path', null);
        $partialPath = apply_filters('book-review/template/partials-path', null);

        $engine = new Handlebars(array(
            'loader' => new \Handlebars\Loader\FilesystemLoader($tplPath, $viewsOptions),
            'partials_loader' => new \Handlebars\Loader\FilesystemLoader($partialPath, $partialOptions),
        ));

        return $engine->render($tplName, $data);
    }
}
