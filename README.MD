
Cattegory attribute is_spulen determines wheter category should display sink configurator
```sql
SELECT catalog_eav_attribute.attribute_id, eav_attribute.attribute_code FROM `catalog_eav_attribute` JOIN eav_attribute ON catalog_eav_attribute.attribute_id = eav_attribute.attribute_id WHERE eav_attribute.attribute_code = 'is_spulen'
```

Sink configurator attributes:

```
width_overlay                           Auflage - Breite
height_overlay                          Auflage - Tiefe
width_undercounter                      Breite Unterbau
height_undercounter                     Unterbau - Tiefe
width_flush                             Breite Bündig
height_flush                            Bündig - Tiefe
width_outerdemision                     Außenmaß - Breite
height_outerdemision                    Außenmaß - Tiefe
```


###Important!
There are two different approaches based on whether elasticsearch is turned on or off.
- Replacing Magento\Elasticsearch7\SearchAdapter\Adapter is the way when elasticsearch is on
- Replacing Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection (only for total count) and creating catalog_block_product_list_collection observer to manipulate product collection is the way to go when there is no elasticsearch
- There might be one more condition when there is no elasticsearch and temporary tables are disabled
