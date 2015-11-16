<?php

namespace bookReview;

class PostTypes
{
	public function __construct()
	{
		$this->registerPostType();
		$this->registerGenre();
		$this->registerAuthor();
		$this->registerPublisher();
	}

	protected function registerPostType()
	{
		$labels = array(
			'name' => _x('Books', 'post type general name'),
			'singular_name' => _x('Book', 'post type singular name'),
			'menu_name' => _x('Books', 'admin menu'),
			'name_admin_bar' => _x('Book', 'add new on admin bar'),
			'add_new' => _x('Add New', 'book'),
			'add_new_item' => __('Add New Book'),
			'new_item' => __('New Book'),
			'edit_item' => __('Edit Book'),
			'view_item' => __('View Book'),
			'all_items' => __('All Books'),
			'search_items' => __('Search Books'),
			'parent_item_colon' => __('Parent Books:'),
			'not_found' => __('No books found.'),
			'not_found_in_trash' => __('No books found in Trash.'),
		);

		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'query_var' => true,
			'rewrite' => array('slug' => 'book'),
			'capability_type' => 'post',
			'has_archive' => true,
			'hierarchical' => false,
			'supports' => array('title', 'editor', 'author', 'thumbnail'),
		);

		register_post_type(BOOK_POST_TYPE, $args);
	}

	protected function registerGenre()
	{
		$this->registerTaxonomy(array(
			'singular' => _x('Genre', 'taxonomy general name'),
			'plural' => _x('Genre', 'taxonomy general name'),
			'taxonomy' => BOOK_TAX_GENRE,
			'isHierarchical' => true,
		));
	}

	protected function registerAuthor()
	{

		$this->registerTaxonomy(array(
			'singular' => _x('Writer', 'taxonomy general name'),
			'plural' => _x('Writers', 'taxonomy general name'),
			'taxonomy' => BOOK_TAX_AUTHOR,
			'isHierarchical' => false,
		));
	}

	protected function registerPublisher()
	{
		$this->registerTaxonomy(array(
			'singular' => _x('Publisher', 'taxonomy general name'),
			'plural' => _x('Publishers', 'taxonomy general name'),
			'taxonomy' => BOOK_TAX_PUBLISHER,
			'isHierarchical' => false,
		));
	}

	protected function registerTaxonomy($options)
	{
		$labels = array(
			'name' => $options['plural'],
			'singular_name' => $options['singular'],
			'search_items' => sprintf(__('Search %s'), $options['plural']),
			'all_items' => sprintf(__('All %s'), $options['plural']),
			'parent_item' => sprintf(__('Parent %s'), $options['singular']),
			'parent_item_colon' => sprintf(__('Parent %s:'), $options['singular']),
			'edit_item' => sprintf(__('Edit %s'), $options['singular']),
			'update_item' => sprintf(__('Update %s'), $options['singular']),
			'add_new_item' => sprintf(__('Add New %s'), $options['singular']),
			'new_item_name' => sprintf(__('New %s Name'), $options['singular']),
			'menu_name' => sprintf(__('%s'), $options['singular']),

			'popular_items' => sprintf(__('Popular %s'), $options['plural']),
			'separate_items_with_commas' => sprintf(__('Separate %s with commas'), $options['plural']),
			'add_or_remove_items' => sprintf(__('Add or remove %s'), $options['plural']),
			'choose_from_most_used' => sprintf(__('Choose from the most used %s'), $options['plural']),
			'not_found' => sprintf(__('No %s found.'), $options['plural']),
		);

		$args = array(
			'hierarchical' => $options['isHierarchical'],
			'labels' => $labels,
			'show_ui' => true,
			'show_admin_column' => true,
			'query_var' => true,
			'rewrite' => array('slug' => $options['slug']),
		);

		register_taxonomy($options['taxonomy'], BOOK_POST_TYPE, $args);
	}
}
