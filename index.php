<?php

/*
Plugin Name: Book Review
Description: Add a book review box at the end of a post and/or on a sidebar
Author: Ionuț Staicu
Version: 1.0.0
*/

if (!defined('ABSPATH')) {
  exit;
}

add_action('plugins_loaded', function () {
  load_plugin_textdomain('book-review', false, dirname(plugin_basename(__FILE__)) . '/lang');
});