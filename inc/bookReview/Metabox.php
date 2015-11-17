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
        $fields[] = $this->getTextField($post->ID, '_isbn', __('ISBN'));
        $fields[] = $this->getTextField($post->ID, '_publish_year', __('Publish Year'));
        $fields[] = $this->getTextField($post->ID, '_buy_book', __('Buying Links'), true);
        $fields[] = $this->getProgress($post->ID);
        $fields[] = $this->getRating($post->ID);
        $fields[] = $this->getImageUploader($post->ID);

        return implode("\n", $fields);
    }

    protected function getImageUploader($postID)
    {
        $value = get_post_meta($postID, '_book_cover', true);
        $field[] = sprintf('<input type="text" name="_book_cover" value="%s" class="js-bookCover">', esc_attr($value));
        $field[] = sprintf('<button class="js-uploadBookCover">%s</button>', __('Upload Book Cover'));

        return sprintf('<p>%s</p>', implode("\n", $field));
    }

    protected function getProgress($postID)
    {
        $values = apply_filters('book-review/metabox/progress-options', array(
            "list" => __('On My List'),
            "reading" => __('Currently Reading'),
            "read" => __('Read'),
        ));

        return $this->getSelectField($postID, '_book_progress', __('Book Progress'), $values);
    }

    protected function getRating($postID)
    {
        $values = apply_filters('book-review/metabox/progress-options', array(
            -1 => __('- Pick One -'),
            1 => __('Bad'),
            2 => __('Meh'),
            3 => __('Mediocre'),
            4 => __('Pretty good'),
            5 => __('Awesome!'),
        ));

        return $this->getSelectField($postID, '_book_rating', __('Book Rating'), $values);
    }

    protected function getSelectField($postID, $name, $label, array $values)
    {
        $storedValue = get_post_meta($postID, $name, true);

        $options = array();
        foreach ($values as $value => $text) {
            $options[] = sprintf('<option value="%1$s"%2$s>%3$s</option>', $value, selected($storedValue, $value, false), $text);
        }

        $field = sprintf('<select name="%1$s" id="%1$s" class="widefat">%2$s</select>', $name, implode("\n", $options));

        return sprintf('<p><label for="%s">%s: %s</label></p>', $name, $label, $field);
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
        update_post_meta($postID, '_book_progress', sanitize_text_field($_POST['_book_progress']));
        update_post_meta($postID, '_book_rating', sanitize_text_field($_POST['_book_rating']));
        update_post_meta($postID, '_book_cover', sanitize_text_field($_POST['_book_cover']));

        update_post_meta($postID, '_buy_book', wp_kses($_POST['_buy_book']));
    }
}
