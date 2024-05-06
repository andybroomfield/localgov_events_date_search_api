<?php

declare(strict_types=1);

namespace Drupal\localgov_events_date_search_api\EventSubscriber;

use Drupal\search_api\Event\ProcessingResultsEvent;
use Drupal\search_api\Event\SearchApiEvents;
use Drupal\search_api\Utility\Utility;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Localgov events search api results event subscriber.
 */
final class ResultsSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      SearchApiEvents::PROCESSING_RESULTS => ['processResults'],
    ];
  }

  /**
   * Reacts to the process results event.
   *
   * @param Drupal\search_api\Event\ProcessingResultsEvent $event
   *   The processing results event.
   */
  public function processResults(ProcessingResultsEvent $event) {

    // Get results.
    $results = $event->getResults();

    // Only apply to the localgov_events search index.
    $query = $results->getQuery();
    $index = $query->getIndex();
    $index_id = $index->id();
    if ($index_id != 'localgov_events') {
      return;
    }

    // Get result items.
    $result_items = $results->getResultItems();

    // Query date_recur table to dates between.
    $table_name = 'date_recur__node__localgov_event_date';
    $db_query = \Drupal::database()->select($table_name, 'occurrences')
      ->fields('occurrences', ['entity_id'])
      ->orderBy('localgov_event_date_value', 'ASC');
    if ($start = \Drupal::request()->query->get('start')) {
      $db_query->condition('localgov_event_date_value', date('c', strtotime($start)), '>=');
    }
    if ($end = \Drupal::request()->query->get('end')) {
      $db_query->condition('localgov_event_date_end_value', date('c', strtotime($end)), '<=');
    }
    $db_result = $db_query->execute()->fetchAll();

    // Sort the results based upon which recuring date instance comes first.
    $nids = array_column($db_result, 'entity_id');
    uksort($result_items, function ($left, $right) use ($nids) {
      [$datasource_type, $datasource_id] = Utility::splitCombinedId($left);
      [$left_nid, $lang] = Utility::splitPropertyPath($datasource_id);
      [$datasource_type, $datasource_id] = Utility::splitCombinedId($right);
      [$right_nid, $lang] = Utility::splitPropertyPath($datasource_id);
      $left_pos = array_search($left_nid, $nids);
      $right_pos = array_search($right_nid, $nids);
      return $left_pos - $right_pos;
    });

    // Write back results in sorted order.
    $results->setResultItems($result_items);
  }

}
