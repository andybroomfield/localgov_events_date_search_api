<?php

declare(strict_types=1);

namespace Drupal\localgov_events_date_search_api\EventSubscriber;

use Drupal\search_api\Event\ProcessingResultsEvent;
use Drupal\search_api\Event\SearchApiEvents;
use Drupal\search_api\Utility\Utility;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @todo Add description for this subscriber.
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
   *
   */
  public function processResults(ProcessingResultsEvent $event) {
    $results = $event->getResults();
    $query = $results->getQuery();
    $result_items = $results->getResultItems();
    $table_name = 'date_recur__node__localgov_event_date';
    $db_query = \Drupal::database()->select($table_name, 'occurrences')
      ->fields('occurrences', ['entity_id'])
      ->orderBy('localgov_event_date_value', 'ASC');
    if ($start = \Drupal::request()->query->get('localgov_event_date')) {
      $db_query->condition('localgov_event_date_value', date('c', strtotime($start)), '>=');
    }
    if ($end = \Drupal::request()->query->get('localgov_event_date_1')) {
      $db_query->condition('localgov_event_date_end_value', date('c', strtotime($end)), '<=');
    }
    $db_result = $db_query->execute()->fetchAll();
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
    $results->setResultItems($result_items);
  }

}
