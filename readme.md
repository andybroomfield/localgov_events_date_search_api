# Localgov events dates in search api index

Proof of concept support for reccuring dates in the [Localgov events][https://github.com/localgovdrupal/localgov_events] search.

__Caution : Experimental, not reccomended for use on production sites!__

This works using two search api event subscribers:
- indexingItems: Add all reccuring dates to the events search index
- processResults: Sort events from the index by the next recurring date

Also included is a field formatter to take the query string from the events view and use that for the start date of date instances, so that the search results use that as the basis for displaying the next date.

On install, this should replace the existing events search index and view and event teaser display with the above. The event index needs to then be reindexed.