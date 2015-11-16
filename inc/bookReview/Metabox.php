<?php

namespace bookReview;

class Metabox
{
	public function __construct()
	{
		add_action('add_meta_boxes', array($this, 'addMetaBox'));
		add_action('save_post', array($this, 'saveMeta'));
	}

	public function addMetaBox()
	{
		add_meta_box('book_properties', __('Book Properties'), array($this, 'displayMetaBox'), BOOK_POST_TYPE, 'advanced', 'high');
	}

	public function displayMetaBox($post)
	{
		wp_nonce_field('book-review-nonce', 'book-review-nonce');
		do_action('book-review/metabox/before-fields', $post);

		do_action('book-review/metabox/after-fields', $post);
	}

	public function saveMeta($post_id)
	{
		if (!isset($_POST['book-review-nonce']) || !wp_verify_nonce($_POST['book-review-nonce'], 'book-review-nonce')) {
			return;
		}

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}

		if (isset($_POST['post_type']) && 'page' == $_POST['post_type']) {
			if (!current_user_can('edit_page', $post_id)) {
				return;
			}
		} else {
			if (!current_user_can('edit_post', $post_id)) {
				return;
			}
		}

		//  TODO: add fields

		do_action('book-review/metabox/save', $post_id);
	}
}
