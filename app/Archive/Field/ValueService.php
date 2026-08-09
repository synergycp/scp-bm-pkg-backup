<?php

namespace Packages\Backup\App\Archive\Field;

/**
 * Provide Business Logic for Backup Fields.
 */
class ValueService {
  /**
   * @var ValueRepository
   */
  protected $values;

  /**
   * @param ValueRepository $values
   */
  public function __construct(ValueRepository $values) {
    $this->values = $values;
  }

  /**
   * @param HasValues $hasValues
   *
   * @return ValueCollection
   */
  public function all(HasValues $hasValues) {
    $collection = $this->values
      ->query()
      ->parent($hasValues)
      ->get();

    return new ValueCollection($collection);
  }

  /**
   * @param HasValues $hasFields
   * @param string    $name
   *
   * @return Value|null
   */
  public function byName(HasValues $hasFields, $name) {
    return $this->values
      ->query()
      ->parent($hasFields)
      ->whereHas('field', function ($query) use ($name) {
        $query->where('name', $name);
      })
      ->first();
  }
}
