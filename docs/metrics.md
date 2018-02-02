### EventLogging

* https://meta.wikimedia.org/wiki/Schema:TwoColConflictConflict

### Graphite

#### Info

##### General

* **{PREFIX}** is a metric prefix defined by MediaWiki ([docs](https://www.mediawiki.org/wiki/Manual:$wgStatsdMetricPrefix)).
* **{AGGREGATION}** is a suffix added by statsd / graphite per aggregation type. ([docs](https://wikitech.wikimedia.org/wiki/Graphite#Extended_properties))
* You can find more docs @ https://wikitech.wikimedia.org/wiki/Graphite

##### TwoColConflict specific

* **{PREFIX}** is "MediaWiki.TwoColConflict"
* **{NSID}** is an id between NS_MAIN(0) and NS_CATEGORY_TALK(15) - [Manual:Namespace#Built-in_namespaces](https://www.mediawiki.org/wiki/Manual:Namespace#Built-in_namespaces)

#### Metrics

* **{PREFIX}.conflict.{AGGREGATION}** - Total number of edit conflict page loads (All namespaces)
* **{PREFIX}.conflict.byNamespaceId.{NSID}.{AGGREGATION}** - Number of edit conflict page loads in the given namespace
  * A user refreshing a conflict page would result in 2 counts here

* **{PREFIX}.conflict.resolved.{AGGREGATION}** - Total number of conflicts resolved (All namespaces)
* **{PREFIX}.conflict.resolved.byNamespaceId.{NSID}.{AGGREGATION}** - Number of conflicts resolved in the given namespace

* **{PREFIX}.event.baseSelection.your.{AGGREGATION}** - Number of times a user selects their own text in the base selection dialog
* **{PREFIX}.event.baseSelection.current.{AGGREGATION}** - Number of times a user selects the current saved text in the base selection dialog
