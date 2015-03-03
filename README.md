agINFRA-ESPOL
=============

Visualization components, integrated

* For sqlite database management (ag_couch_proxy/ipb.ds.sqlite), it's recommended to use SQLite Manager firefox extension.
* Please edit config.php to your servers
* You have to install elasticsearch 0.90.x
* You have to install CouchDB River Plugin for Elasticsearch:
  bin/plugin -install elasticsearch/elasticsearch-river-couchdb/1.3.0

```
curl -XDELETE 'http://localhost:9200/aginfra_ds/'
curl -XPUT 'http://localhost:9200/aginfra_ds/'
curl -XPUT 'http://localhost:9200/_river/aginfra_ds/_meta' -d '{
    "type" : "couchdb",
    "couchdb" : {
        "host" : "localhost",
        "port" : 5984,
        "db" : "aginfra_datasets",
        "filter" : null
    },
    "index" : {
        "index" : "aginfra_ds",
        "type" : "aginfra_ds",
        "bulk_size" : "100",
        "bulk_timeout" : "10ms"
    }
}'
```
* PHP requirements:
php curl extension
php sqlite extension
maximum execution time increased (or unlimited)
