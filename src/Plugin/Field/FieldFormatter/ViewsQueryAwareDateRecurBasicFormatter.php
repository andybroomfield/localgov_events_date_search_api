<?php

declare(strict_types = 1);

namespace Drupal\localgov_events_date_search_api\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\DependencyTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\date_recur\DateRange;
use Drupal\date_recur\Entity\DateRecurInterpreterInterface;
use Drupal\date_recur\Plugin\Field\FieldFormatter\DateRecurBasicFormatter;
use Drupal\date_recur\Plugin\Field\FieldType\DateRecurItem;
use Drupal\datetime_range\Plugin\Field\FieldFormatter\DateRangeDefaultFormatter;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
    $start = new \DateTime(\Drupal::request()->query->get('start') ?? 'now');
    return $item->getHelper()
      ->getOccurrences($start, NULL, $maxOccurrences);
  }

}
