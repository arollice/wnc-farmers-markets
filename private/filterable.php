<?php
trait Filterable
{
  /**
   * Builds SQL conditions based on provided filter options.
   *
   * @param array $filters Associative array of filters.
   * @return array An array with two elements:
   *               - The SQL condition string (including the WHERE keyword if needed).
   *               - The array of bound parameters.
   */
  protected static function buildFilterConditions($filters)
  {
    $conditions = [];
    $params = [];

    // Example filter: if 'approved' is set, add a condition for approved vendors.
    if (!empty($filters['approved'])) {
      $conditions[] = "status = 'approved'";
    }

    $sqlCondition = '';
    if ($conditions) {
      $sqlCondition = " WHERE " . implode(" AND ", $conditions);
    }
    return [$sqlCondition, $params];
  }
}
