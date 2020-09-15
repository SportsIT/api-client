# Filtering
Index requests and requests to has-many relations filtering applied to limit the resources returned.

## Supported Operators
Often times, you will need to specify an operator to get the desired effect. The table below explains the available operators:

| Operator | Suffix(s) | Notes |
| -------- | --------- | ------------- |
| `=` | _default_, `__is` | |
| `!=` | `__not`, `__isnot` | |
| `>=` | `__gte` | |
| `<=` | `__lte` | |
| `>` | `__gt` | |
| `<` | `__lt` | |
| `= null` | `__isnull` | Value is discarded as it only filters based on if the attribute is `null` |
| `!= null` | `__notnull`| "" |
| `= null` _or_ `!= null` | `__null` | If value is truthy filters same as `__isnull`, else `__notnull` |
| `in array` | `__in`, `__includes` | Value can be either an array or comma-separated list |
| `not in array` | `__notin`, `__excludes` | "" |
| `regex match` | `__regex` |  |
| `starts like` | `__starts` |  |
| `ends like` | `__ends` |  |
| `time greater than or equal` | `__time_gte` | Special case for datetimes to filter on just the time portion |
| `time less than or equal` | `__time_lte` | "" |
| `time greater than` | `__time_gt` | "" |
| `time less than` | `__time_lt` | "" |

Constants are available for all supported operators via `\Dash\Utils\Filters`
```php
<?php

$operator = \Dash\Utils\Filters::OPERATOR_GREATER_THAN_OR_EQUAL;
```

## Filtering Relations
Filters can limit what resources will be returned based on their relation's values as well by using dot notation when defining the field name. 

For example, if we wanted to only get Events at a certain Facility, we would use the following filter when making a request to events 
```
resource.facility.id
```

> It is recommended to filter by `resource.facility.id` as opposed to `resource.facility_id` since the latter does not adhere to the spec and could be removed at any time.

## Complex Filtering
Filters can be grouped under `and`/`or` keys in order to achieve more complex filtering rules. To help with this, we have provided a helper class `Dash\Models\FilterGroup`.
```php
<?php

// Filters in this group will all have a logical OR operator between them
$filterGroup = new \Dash\Models\FilterGroup(\Dash\Models\FilterGroup::OPERATOR_OR);

// Add filters the same way you would for a request builder
$filterGroup->withFilter('field_1', \Dash\Utils\Filters::OPERATOR_LESS_THAN, 12);

// Shortcut for creating filter group on a request builder
/** @var \Dash\Builders\BaseRequestBuilder $builder */
$builder->whereGroup([
  ['field_1', \Dash\Utils\Filters::OPERATOR_LESS_THAN, 12],
  ['field_2', 'value'],
], \Dash\Models\FilterGroup::OPERATOR_OR);

// Filter Groups can be nested to achieve even more complex filtering
$group1 = (new \Dash\Models\FilterGroup())->withFilters([
  ['field_1', \Dash\Utils\Filters::OPERATOR_LESS_THAN, 12],
  ['field_2', 'value'],
]);

$group2 = (new \Dash\Models\FilterGroup())->withFilters([
  ['field_3', \Dash\Utils\Filters::OPERATOR_GREATER_THAN, 12],
  ['field_4', 'value'],
]);

// Filters in equivalent SQL: (field_1 < 12 AND field_2 = 'value') OR (field_3 > 12 AND field_4 = 'value')
$builder->whereGroup([$group1, $group2], \Dash\Models\FilterGroup::OPERATOR_OR);
```