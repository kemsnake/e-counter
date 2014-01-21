<?php

function antonelli_field__field_devices__object($variables){
  if (true){
    $output = '<ul>';
    foreach ($variables['items'] as $delta => $item) {
      $node = $variables['element']['#items'][$delta]['entity'];
      $output .= '<li><a href = "#tabs-' . $delta . '">' . $node->title . '</a></li>';
    }
    $output .= '</ul>';

    // Render the label, if it's not hidden.
    if (!$variables['label_hidden']) {
      $output .= '<div class="field-label"' . $variables['title_attributes'] . '>' . $variables['label'] . ':&nbsp;</div>';
    }

    // Render the items.
    $output .= '<div class="field-items"' . $variables['content_attributes'] . '>';
    foreach ($variables['items'] as $delta => $item) {
      $classes = 'field-item ' . ($delta % 2 ? 'odd' : 'even');
      $output .= '<div id="tabs-' . $delta . '" class="' . $classes . '"' . $variables['item_attributes'][$delta] . '>' . drupal_render($item) . '</div>';
    }
    $output .= '</div>';

    // Render the top-level DIV.
    $output = '<div id="tabs">' . $output . '</div>';
  }
  return $output;
}