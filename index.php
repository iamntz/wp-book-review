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

define('BOOK_POST_TYPE', 'book');

define('BOOK_TAX_GENRE', 'book_genre');
define('BOOK_TAX_AUTHOR', 'book_author');
define('BOOK_TAX_PUBLISHER', 'book_publisher');

require_once 'inc/bookReview/PostTypes.php';

add_action('init', function () {
    new bookReview\PostTypes;
});

register_activation_hook(__FILE__, function () {
    new bookReview\PostTypes;
    flush_rewrite_rules();
});

require_once 'inc/bookReview/Metabox.php';

add_action('admin_init', function () {
    new bookReview\Metabox();
});

add_action('admin_enqueue_scripts', function ($hook) {
    wp_register_script('book-review-fileUpload', plugin_dir_url(__FILE__) . 'assets/javascripts/fileUpload.js', array('jquery'), '1');

    wp_localize_script('book-review-fileUpload', 'book_review_i18n', array(
        'uploaderTitle' => __('Upload a book Cover'),
        'uploaderButton' => __('Use selected Image')
    ));

    wp_enqueue_script('book-review-fileUpload');
});
