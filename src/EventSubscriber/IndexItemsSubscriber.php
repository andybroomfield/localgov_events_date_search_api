<?php

declare(strict_types=1);

namespace Drupal\localgov_events_date_search_api\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\search_api\Event\SearchApiEvents;
use Drupal\search_api\Event\IndexingItemsEvent;
use Drupal\search_api\Utility\Utility;

/**
 * @todo Add description for this subscriber.
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
    $index = $event->getIndex();
    $index_id = $index->id();
    if ($index_id != 'localgov_events') {
      return;
    }
    $items = $event->getItems();
    foreach($items as $key => $item) {
      list($datasource_type, $datasource_id) = Utility::splitCombinedId($key);
      list($nid, $lang) = Utility::splitPropertyPath($datasource_id);
      $date_field = $item->getField('localgov_event_date');
      $date_end_field = $item->getField('localgov_event_date_end_value');
      $table_name = 'date_recur__node__localgov_event_date';
      $result = \Drupal::database()->select($table_name, 'occurrences')
        ->fields('occurrences', ['localgov_event_date_value', 'localgov_event_date_end_value'])
        ->condition('entity_id', $nid)
        ->execute();
      $timestamps = [];
      $occurances = $result->fetchAll();
      $timestamps = array_map(function($datetime) {
        return strtotime($datetime);
      }, array_column($occurances, 'localgov_event_date_value'));
      $date_field->setValues($timestamps);
      $end_timestamps = array_map(function($datetime) {
        return strtotime($datetime);
      }, array_column($occurances, 'localgov_event_date_end_value'));
      $date_end_field->setValues($end_timestamps);
    }
  }

}
