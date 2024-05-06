<?php

declare(strict_types=1);

namespace Drupal\localgov_events_date_search_api\EventSubscriber;

use Drupal\search_api\Event\IndexingItemsEvent;
use Drupal\search_api\Event\SearchApiEvents;
use Drupal\search_api\Utility\Utility;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Localgov events search api index event subscriber.
 */
final class IndexItemsSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      SearchApiEvents::INDEXING_ITEMS => ['indexingItems'],
    ];
  }

  /**
   * Reacts to the indexing items event.
   *
   * @param \Drupal\search_api\Event\IndexingItemsEvent $event
   *   The indexing items event.
   */
  public function indexingItems(IndexingItemsEvent $event) {

    // Only apply to the localgov_events search index.
    $index = $event->getIndex();
    $index_id = $index->id();
    if ($index_id != 'localgov_events') {
      return;
    }

    // Loop through each item being indexed.
    $items = $event->getItems();
    foreach ($items as $key => $item) {

      // Get the node id being indexed.
      [$datasource_type, $datasource_id] = Utility::splitCombinedId($key);
      [$nid, $lang] = Utility::splitPropertyPath($datasource_id);

      // Get the date field and date end field.
      $date_field = $item->getField('localgov_event_date');
      $date_end_field = $item->getField('localgov_event_date_end_value');

      // Get all dates for the node from the recur dates table.
      // @todo also check the revision.
      // @todo also check when these need to be reindexed.
      $table_name = 'date_recur__node__localgov_event_date';
      $result = \Drupal::database()->select($table_name, 'occurrences')
        ->fields('occurrences', ['localgov_event_date_value', 'localgov_event_date_end_value'])
        ->condition('entity_id', $nid)
        ->execute();

      // Convert dates into timestamps for the index and set them against the 
      // the relevant node.
      $timestamps = [];
      $occurances = $result->fetchAll();
      $timestamps = array_map(function ($datetime) {
        return strtotime($datetime);
      }, array_column($occurances, 'localgov_event_date_value'));
      $date_field->setValues($timestamps);
      $end_timestamps = [];
      $end_timestamps = array_map(function ($datetime) {
        return strtotime($datetime);
      }, array_column($occurances, 'localgov_event_date_end_value'));
      $date_end_field->setValues($end_timestamps);
    }
  }

}
