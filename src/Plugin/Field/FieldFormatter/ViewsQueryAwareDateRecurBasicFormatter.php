<?php

declare(strict_types=1);

namespace Drupal\localgov_events_date_search_api\Plugin\Field\FieldFormatter;

use Drupal\date_recur\Plugin\Field\FieldFormatter\DateRecurBasicFormatter;
use Drupal\date_recur\Plugin\Field\FieldType\DateRecurItem;

/**
 * Localgov events search basic recurring date formatter.
 *
 * @FieldFormatter(
 *   id = "localgov_views_query_aware_date_recur_basic_formatter",
 *   label = @Translation("Localgov events search date recur basic formatter"),
 *   field_types = {
 *     "date_recur"
 *   }
 * )
 */
class ViewsQueryAwareDateRecurBasicFormatter extends DateRecurBasicFormatter {

  /**
   * {@inheritDoc}
   */
  protected function getOccurrences(DateRecurItem $item, $maxOccurrences): array {

    // If the start is set in the query string (match events search view)
    // then use that as the start date for the next instance.
    $start = new \DateTime(\Drupal::request()->query->get('start') ?? 'now');
    return $item->getHelper()
      ->getOccurrences($start, NULL, $maxOccurrences);
  }

}
