<?php

namespace bookReview;

class BookReviewWidget extends \WP_Widget
{
    protected $defaultValues = array(
        'title' => '',
        'sortby' => 'finished',
        'sort' => 'DESC',
        'widgetWasSaved' => 1,
        'displayOptions' => array(),
    );

    public function __construct()
    {
        $this->defaultValues['displayOptions'] = $this->getDefaultDisplayOptions();

        parent::__construct('book_review_widget', __('Book Review'),
            array('description' => __('A book widget')));
    }

    public function widget($args, $instance)
    {
    }

    public function form($instance)
    {
        printf('<input type="hidden" name="%s" value="1">', $this->get_field_name('widgetWasSaved'));
        echo $this->getTitleField($instance);
        echo $this->getSortField($instance);
        echo $this->getDisplayOptionsField($instance);
    }

    public function update($new_instance, $old_instance)
    {
        return $new_instance;
    }

    protected function getTitleField($instance)
    {
        $title = !empty($instance['title']) ? $instance['title'] : $this->defaultValues['title'];
        return sprintf('<p><label>%2$s</label><input type="text" name="%1$s" value="%3$s" class="widefat"></p>',
            $this->get_field_name('title'), __('Title'), esc_attr($title));
    }

    protected function getSortField($instance)
    {
        $select = '';
        $sortByOptions = apply_filters('book-review/widget/sortby-options', array(
            'finished' => __('Date you\'ve finished the book'),
            'added' => __('Date you\'ve added the book'),
        ));

        $sortby = !empty($instance['sortby']) ? $instance['sortby'] : $this->defaultValues['sortby'];
        $select[] = sprintf('<p><label>%2$s </label><select class="widefat" name="%1$s">',
            $this->get_field_name('sortby'), __('Sort By:'));

        foreach ($sortByOptions as $value => $text) {
            $select[] = sprintf('<option value="%s"%s>%s</option>',
                $value, selected($value, $sortby, false), $text);
        }

        $select[] = '</select></p>';

        $sort = !empty($instance['sort']) ? $instance['sort'] : $this->defaultValues['sort'];
        $select[] = sprintf('<p><label>%2$s </label><select class="widefat" name="%1$s">',
            $this->get_field_name('sort'), __('Sort:'));

        $select[] = sprintf('<option value="ASC"%s>%s</option>', selected('ASC', $sort, false), __('ASC'));
        $select[] = sprintf('<option value="DESC"%s>%s</option>', selected('DESC', $sort, false), __('DESC'));

        $select[] = '</select></p>';

        return implode("\n", $select);
    }

    protected function getDisplayOptionsField($instance)
    {
        $defaultDisplayOptions = $this->widgetWasSaved($instance) ? array() : array_keys($this->defaultValues['displayOptions']);
        $displayOptions = !empty($instance['displayOptions']) ? $instance['displayOptions'] : $defaultDisplayOptions;

        foreach ($this->defaultValues['displayOptions'] as $key => $text) {
            $fields[] = sprintf(' <label><input type="checkbox" name="%1$s[]" value="%2$s" %3$s> %4$s</label>',
                $this->get_field_name('displayOptions'),
                $key,
                in_array($key, $displayOptions) ? ' checked' : '',
                $text
            );
        }

        return sprintf('<p>%s</p>', implode("<br>", $fields));
    }

    protected function widgetWasSaved($instance)
    {
        return !empty($instance['widgetWasSaved']) && $instance['widgetWasSaved'] == 1;
    }

    protected function getDefaultDisplayOptions()
    {
        return array(
            'tax_' . BOOK_TAX_GENRE => __('Genre'),
            'tax_' . BOOK_TAX_AUTHOR => __('Book Author'),
            'tax_' . BOOK_TAX_PUBLISHER => __('Book Publisher'),
            'thumb' => __('Thumbnail'),
            'added_on' => __('Date you added the book'),
            'started_on' => __('Date you started to read'),
            'finished_on' => __('Date you finished the book'),
            'year' => __('The year the book was published'),
            'isbn' => __('ISBN'),
        );
    }
}
