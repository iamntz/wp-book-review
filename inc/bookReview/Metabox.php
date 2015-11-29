<?php

namespace bookReview;

use \bookReview\Tpl;

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

        return Tpl::get('metabox/previewBookFields', array(
            'fields' => $fields,
            'uploader' => $this->getImageUploader($post->ID),
            'marginAdjust' => ($this->getAttachmentSizeByName($this->getPreviewSize())['width'] + 20),
        ));
    }

    protected function getImageUploader($postID)
    {
        $value = get_post_meta($postID, '_book_cover', true);

        $attachmentPreview = '';

        $previewSize = $this->getPreviewSize();
        if (!empty($value)) {
            $attachmentPreview = wp_get_attachment_image($value, $previewSize);
        }

        return Tpl::get('metabox/previewBookCoverContainer', array(
            'width' => $this->getAttachmentSizeByName($previewSize)['width'],
            'value' => esc_attr($value),
            'preview' => $attachmentPreview,
            'hasPreview' => !empty($attachmentPreview),
            'previewSize' => $previewSize,
            'uploadAnchor' => __('Upload Book Cover'),
        ));
    }

    protected function getPreviewSize()
    {
        return apply_filters('book-review/images/cover-size', 'thumbnail');
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
            $options[] = Tpl::get('formFields/option', array(
                'value' => $value,
                'text' => $text,
                'selected' => selected($storedValue, $value, false),
            ));
        }

        $field = Tpl::get('formFields/select', array(
            'name' => $name,
            'id' => $name,
            'options' => $options,
        ));

        return Tpl::get('formFields/fieldWrapper', array(
            'labelFor' => $name,
            'label' => $label,
            'field' => $field,
        ));
    }

    protected function getTextField($postID, $name, $label, $textarea = false)
    {
        $value = get_post_meta($postID, $name, true);

        $tplData = array(
            'name' => $name,
            'id' => $name,
            'value' => $textarea ? esc_textarea($value) : esc_attr($value),
        );

        if ($textarea) {
            $field = Tpl::get('formFields/textarea', $tplData);
        } else {
            $field = Tpl::get('formFields/text', $tplData);
        }

        return Tpl::get('formFields/fieldWrapper', array(
            'labelFor' => $name,
            'label' => $label,
            'field' => $field,
        ));
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

    protected function getAttachmentSizeByName($size = '')
    {
        global $_wp_additional_image_sizes;

        $sizes = array();
        $get_intermediate_image_sizes = get_intermediate_image_sizes();

        foreach ($get_intermediate_image_sizes as $_size) {
            if (in_array($_size, array('thumbnail', 'medium', 'large'))) {
                $sizes[$_size]['width'] = get_option($_size . '_size_w');
                $sizes[$_size]['height'] = get_option($_size . '_size_h');
                $sizes[$_size]['crop'] = (bool) get_option($_size . '_crop');
            } elseif (isset($_wp_additional_image_sizes[$_size])) {
                $sizes[$_size] = array(
                    'width' => $_wp_additional_image_sizes[$_size]['width'],
                    'height' => $_wp_additional_image_sizes[$_size]['height'],
                    'crop' => $_wp_additional_image_sizes[$_size]['crop'],
                );
            }
        }

        if ($size) {
            if (isset($sizes[$size])) {
                return $sizes[$size];
            } else {
                return $this->getAttachmentSizeByName('thumbnail');
            }
        }

        return $sizes;
    }
}
