<?php

namespace bookReview;

class BookReviewWidget extends \WP_Widget
{
    protected $defaultValues = array(
        'title' => '',
        'sortby' => 'finished',
        'limit' => 1,
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
        echo $args['before_widget'];

        if (false !== ($title = $this->getValue($instance, 'title')) && !empty($title)) {
            echo $args['before_title'] . apply_filters('widget_title', $title) . $args['after_title'];
        }

        $books = new \WP_Query();

        $books->query(array(
            "post_type" => BOOK_POST_TYPE,
        ));

        $li = array();
        if ($books->have_posts()) {
            while ($books->have_posts()) {
                $books->the_post();
                $bookId = get_the_ID();

                $elements = array();
                $elements[] = get_the_title($bookId);

                $li[] = sprintf('<li>%s</li>', implode("\n", $elements));
            }
            wp_reset_query();
        } else {

        }

        printf('<ul>%s</ul>', implode("\n", $li));
        echo $args['after_widget'];
    }

    public function form($instance)
    {
        printf('<input type="hidden" name="%s" value="1">', $this->get_field_name('widgetWasSaved'));
        echo $this->getTitleField($instance);
        echo $this->getSortField($instance);
        echo $this->getLimitField($instance);
        echo $this->getDisplayOptionsField($instance);
    }

    public function update($new_instance, $old_instance)
    {
        return $new_instance;
    }

    protected function getTitleField($instance)
    {
        return $this->getTextField($instance, 'title', __('Titile'));
    }

    protected function getLimitField($instance)
    {
        return $this->getTextField($instance, 'limit', __('Limit'), 'number', array(
            'min' => "1",
            'step' => "1",
        ));
    }

    protected function getTextField($instance, $key, $label, $inputType = 'text', $extraAttrs = array())
    {
        $value = $this->getValue($instance, $key);

        $field = Tpl::get('formFields/' . $inputType, array_merge(array(
            'name' => $this->get_field_name($key),
            'id' => $this->get_field_id($key),
            'value' => esc_attr($value),
        ), $extraAttrs));

        return Tpl::get('formFields/fieldWrapper', array(
            'labelFor' => $this->get_field_id($key),
            'label' => $label,
            'field' => $field,
        ));
    }

    protected function getSelect($instance, $key, $label, $values)
    {
        $storedValue = $this->getValue($instance, $key);

        foreach ($values as $value => $text) {
            $options[] = Tpl::get('formFields/option', array(
                'value' => $value,
                'text' => $text,
                'selected' => selected($storedValue, $value, false),
            ));
        }

        $field = Tpl::get('formFields/select', array(
            'name' => $this->get_field_name($key),
            'id' => $this->get_field_id($key),
            'options' => $options,
        ));

        return Tpl::get('formFields/fieldWrapper', array(
            'labelFor' => $this->get_field_name($key),
            'label' => $label,
            'field' => $field,
        ));
    }

    protected function getSortField($instance)
    {
        $sortByOptions = apply_filters('book-review/widget/sortby-options', array(
            'finished' => __('Date you\'ve finished the book'),
            'added' => __('Date you\'ve added the book'),
        ));

        $sortOptions = array(
            'ASC' => __('ASC'),
            'DESC' => __('DESC'),
        );

        $select[] = $this->getSelect($instance, 'sortby', __('Sort By'), $sortByOptions);
        $select[] = $this->getSelect($instance, 'sort', __('Sort'), $sortOptions);

        return implode("\n", $select);
    }

    protected function getDisplayOptionsField($instance)
    {
        $default = $this->widgetWasSaved($instance) ? array() : array_keys($this->defaultValues['displayOptions']);
        $displayOptions = $this->getValue($instance, 'displayOptions', $default);

        foreach ($this->defaultValues['displayOptions'] as $key => $text) {
            $fields[] = $field = Tpl::get('formFields/checkbox', array(
                'name' => $this->get_field_name('displayOptions'),
                'value' => $key,
                'id' => $this->get_field_id('displayOptions'),
                'label' => $text,
                'checked' => in_array($key, $displayOptions),
            ));
        }

        return Tpl::get('formFields/checkboxWrapper', array(
            'checkboxes' => $fields
        ));
    }

    protected function widgetWasSaved($instance)
    {
        return !empty($instance['widgetWasSaved']) && $instance['widgetWasSaved'] == 1;
    }

    protected function getValue($instance, $key, $default = null)
    {
        $default = !is_null($default) ? $default : $this->defaultValues[$key];
        return !empty($instance[$key]) ? $instance[$key] : $default;
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
