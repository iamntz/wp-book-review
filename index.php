<?php

/*
Plugin Name: Book Review
Author: Ionuț Staicu
Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('plugins_loaded', function () {
    load_plugin_textdomain('book-review', false, dirname(plugin_basename(__FILE__)) . '/lang');
});

define('BOOK_VERSION', '1.0.0');
define('BOOK_POST_TYPE', 'book');

define('BOOK_TAX_GENRE', 'book_genre');
define('BOOK_TAX_AUTHOR', 'book_author');
define('BOOK_TAX_PUBLISHER', 'book_publisher');

require_once 'vendor/autoload.php';

add_filter('book-review/template/views-path', function () {
    return __DIR__ . '/views/';
});

add_filter('book-review/template/partials-path', function () {
    return __DIR__ . '/views/partials/';
});

add_action('init', function () {
    new bookReview\PostTypes;
});

register_activation_hook(__FILE__, function () {
    new bookReview\PostTypes;
    flush_rewrite_rules();
});

add_action('admin_init', function () {
    new bookReview\Metabox();
});

add_action('admin_enqueue_scripts', function ($hook) {
    wp_register_script('book-review-fileUpload', plugin_dir_url(__FILE__) . 'assets/javascripts/fileUpload.js', array('jquery'), BOOK_VERSION);

    wp_localize_script('book-review-fileUpload', 'book_review_i18n', array(
        'uploaderTitle' => __('Upload a book Cover'),
        'uploaderButton' => __('Use selected Image'),
    ));

    wp_enqueue_script('book-review-fileUpload');

    wp_register_style('book-review-fileUpload', plugin_dir_url(__FILE__) . 'assets/stylesheets/fileUpload.css', array(), BOOK_VERSION);
    wp_enqueue_style('book-review-fileUpload');
});

add_action('widgets_init', function () {
    register_widget('bookReview\BookReviewWidget');
});
