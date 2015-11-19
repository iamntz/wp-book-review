<?php

namespace bookReview;

class BookReviewWidget extends \WP_Widget
{
    protected $defaultValues = array(
        'title' => '',
        'sortby' => 'finished',
        'sort' => 'DESC',
    );

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
        echo $this->getTitleField($instance);
        echo $this->getSortField($instance);
    }

    public function update($new_instance, $old_instance)
    {
    }

    protected function getTitleField($instance)
    {
        $title = !empty($instance['title']) ? $instance['title'] : $this->defaultValues['title'];
        return sprintf('<p><label for="%1$s">%2$s</label><input type="text" name="%1$s" value="%3$s" class="widefat"></p>',
            $this->get_field_id('title'), __('Title'), $title);
    }

    protected function getSortField($instance)
    {
        $select = '';
        $sortByOptions = apply_filters('book-review/widget/sortby-options', array(
            'finished' => __('Date you\'ve finished the book'),
            'added' => __('Date you\'ve added the book'),
        ));

        $sortby = !empty($instance['sortby']) ? $instance['sortby'] : $this->defaultValues['sortby'];
        $select[] = sprintf('<p><label for="%1$s">%2$s </label><select class="widefat" name="%1$s">',
            $this->get_field_id('sortby'), __('Sort By:'));

        foreach ($sortByOptions as $value => $text) {
            $select[] = sprintf('<option value="%s"%s>%s</option>',
                $value, selected($value, $sortby, false), $text);
        }

        $select[] = '</select></p>';

        $sort = !empty($instance['sort']) ? $instance['sort'] : $this->defaultValues['sort'];
        $select[] = sprintf('<p><label for="%1$s">%2$s </label><select class="widefat" name="%1$s">',
            $this->get_field_id('sort'), __('Sort:'));

        $select[] = sprintf('<option value="ASC"%s>%s</option>', selected('ASC', $sort, false), __('ASC'));
        $select[] = sprintf('<option value="DESC"%s>%s</option>', selected('DESC', $sort, false), __('DESC'));

        $select[] = '</select></p>';

        return implode("\n", $select);
    }
}
