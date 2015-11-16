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

		echo $this->addFields($post);

		do_action('book-review/metabox/after-fields', $post);
	}

	protected function addFields($post)
	{
		$fields = '';

		$fields .= $this->getTextField($post->ID, '_isbn', __('ISBN'));
		$fields .= $this->getTextField($post->ID, '_publish_year', __('Publish Year'));
		$fields .= $this->getTextField($post->ID, '_buy_book', __('Buying Links'), true);

    return $fields;
	}

	protected function getTextField($postID, $name, $label, $textarea = false)
	{
		$value = get_post_meta($postID, $name, true);

		if ($textarea) {
			$field = sprintf('<textarea name="%2$s" id="%2$s" class="widefat">%1$s</textarea>', esc_textarea($value), $name);
		} else {
			$field = sprintf('<input type="text" name="%2$s" id="%2$s" value="%1$s" class="widefat">', esc_attr($value), $name);
		}

		return sprintf('<p><label for="%s">%s: %s</label></p>', $name, $label, $field);
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

		$this->saveFields($post_id);

		do_action('book-review/metabox/save', $post_id);
	}

  protected function saveFields($postID)
  {
    update_post_meta($postID, '_isbn', sanitize_text_field($_POST['_isbn']));
    update_post_meta($postID, '_publish_year', sanitize_text_field($_POST['_publish_year']));

    update_post_meta($postID, '_buy_book', wp_kses($_POST['_buy_book']));
  }
}
