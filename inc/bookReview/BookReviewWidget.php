<?php

namespace bookReview;

class BookReviewWidget extends \WP_Widget
{
    public function __construct()
    {
        parent::__construct('book_review_widget', __('Book Review'),
            array('description' => __('A book widget')));
    }
    public function widget($args, $instance)
    {
    }
    public function form($instance)
    {
    }
    public function update($new_instance, $old_instance)
    {
    }
}
