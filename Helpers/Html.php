<?php

/**
 * Create an attribute
 * @param  string $key
 * @param  mixed $value
 * @return string
 */
function tag_attribute($key, $value)
{
    if (is_array($value)) {
        $value = implode(' ', $value);
    }

    return "$key=\"$value\"";
}

/**
 * Create an attribute with prefix
 * @param  string $prefix
 * @param  string $key
 * @param  mixed $value
 * @return string
 */
function prefix_tag_attribute($prefix, $key, $value)
{
    $key = "$prefix-$key";
    return tag_attribute($key, $value);
}

/**
 * Create a boolean attribute
 * @param  string $key
 * @return string
 */
function boolean_tag_attribute($key)
{
    return tag_attribute($key, $key);
}

/**
 * Create a style attribute
 * @param  mixed $value
 * @return string
 */
function style_tag_attribute($value)
{
    if (is_array($value)) {
        $value = implode('', array_map_keys($value, function($value, $key){
            return "$key:$value;";
        }));
    }

    return tag_attribute('style', $value);
}

/**
 * Create an attributes
 * @param  array  $attributes
 * @return string
 */
function tag_attributes(array $attributes)
{
    if (empty($attributes)) {
        return;
    }

    $attrs = array();
    foreach ($attributes as $key => $value) {
        if ($key === 'data') {
            foreach ($value as $key => $value) {
                $attrs[] = prefix_tag_attribute('data', $key, $value);
            }
        } elseif ($key === 'style') {
            $attrs[] = style_tag_attribute($value);
        } elseif (is_bool($value)) {
            if ($value) {
                $attrs[] = boolean_tag_attribute($key, $value);
            }
        } elseif (!is_null($value)) {
            $attrs[] = tag_attribute($key, $value);
        }
    }

    return ' '.implode(' ', $attrs);
}

/**
 * Create a tag
 * @param  string $name
 * @param  array  $attributes
 * @param  mixed $content
 * @return string
 */
function tag($name, array $attributes = array(), $content = null)
{
    $attributes = tag_attributes($attributes);

    if (!$content) {
        return "<$name $attributes />";
    }

    if ($content instanceof Closure) {
        $content = ob_capture($content);
    }

    return "<$name$attributes>$content</$name>";
}

/**
 * Capture the output buffer
 * @param  Closure $callback
 * @return string
 */
function ob_capture(Closure $callback)
{
    ob_start();
    $callback();
    $html = ob_get_contents();
    ob_end_clean();
    return $html;
}

/**
 * Create an url
 * @param  string $url
 * @param  array  $params
 * @return string
 */
function url($url, array $params = array())
{
    if ($params) {
        $url .= '?'.http_build_query($params);
    }

    return $url;
}

/**
 * Create a link
 * @param  mixed $content
 * @param  string $url
 * @param  array $attributes
 * @return string
 */
function link_to($content, $url, $attributes = null)
{
    if ($url instanceof Closure) {
        list($url, $content) = array($content, $url);
    } elseif(is_array($url)) {
        list($url, $attributes, $content) = array($content, $url, $attributes);
    }

    $attributes['href'] = $url;

    return tag('a', $attributes, $content);
}

/**
 * Create an image tag
 * @param  string $src
 * @param  array  $attributes
 * @return string
 */
function img_tag($src, array $attributes = array())
{
    $attributes['src'] = $src;

    return tag('img', $attributes);
}

/**
 * Create a select tag
 * @param  string $name
 * @param  string $options
 * @param  array  $attributes
 * @return string
 */
function select_tag($name, $options, array $attributes = array())
{
    $attributes['name'] = isset($attributes['multiple']) && $attributes['multiple'] === true ? $name.'[]' : $name;
    $attributes['id'] = @$attributes['id'] ?: $name;

    if (isset($attributes['include_blank'])) {
        $include_blank = array_take($attributes, 'include_blank');

        if ($include_blank) {
            $options = tag('option', array(), '').$options;
        }
    }

    return tag('select', $attributes, $options);
}

/**
 * Create options for select tag
 * @param  array  $options
 * @param  mixed $selected
 * @param  mixed $disabled
 * @return string
 */
function options_for_select(array $options, $selected = null, $disabled = null)
{
    return implode('', array_map_keys($options, function($value, $key) use($selected, $disabled){
        $attributes = array();

        $attributes['selected'] = !is_null($selected) && in_array($key, (array) $selected);
        $attributes['disabled'] = !is_null($disabled) && in_array($key, (array) $disabled);
        $attributes['value'] =  is_integer($key) ? $value : $key;

        return tag('option', $attributes, $value);
    }));
}

/**
 * Create grouped options for select tag
 * @param  array  $options
 * @param  mixed $selected
 * @param  mixed $disabled
 * @return string
 */
function grouped_options_for_select(array $options, $selected = null, $disabled = null)
{
    return implode('', array_map_keys($options, function($options, $label) use($selected, $disabled){
        $attributes = array();

        $attributes['label'] = $label;

        return tag('optgroup', $attributes, options_for_select($options, $selected, $disabled));
    }));
}

/**
 * Create a form tag
 * @param  string $url
 * @param  array $attributes
 * @param  mixed $content
 * @return string
 */
function form_tag($url, $attributes, $content = null)
{
    if ($attributes instanceof Closure) {
        list($attributes, $content) = array(null, $attributes);
    }

    $attributes['action'] = $url;

    if (!isset($attributes['method'])) {
        $attributes['method'] = 'post';
    }

    if (array_take($attributes, 'multipart')) {
        $attributes['enctype'] = 'multipart/form-data';
    }

    return tag('form', $attributes, $content);
}

/**
 * Create a text field tag
 * @param  string $name
 * @param  mixed $value
 * @param  array  $attributes
 * @return string
 */
function text_field_tag($name, $value, array $attributes = array())
{
    array_add($attributes, array(
        'id' => $name,
        'name' => $name,
        'type' => 'text',
        'value' => $value
    ));
    return tag('input', $attributes);
}

/**
 * Create a button tag
 * @param  string $name
 * @param  array $attributes
 * @param  mixed $content
 * @return string
 */
function button_tag($name, $attributes, $content = null)
{
    if (!is_array($attributes)) {
        list($attributes, $content) = array(null, $attributes);
    }

    $default = array(
        'type' => 'button',
        'name' => $name
    );

    $attributes = array_add($default, $attributes);

    return tag('button', $attributes, $content);
}