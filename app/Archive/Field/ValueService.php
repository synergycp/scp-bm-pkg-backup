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
   * @return Field
   */
  public function byName(HasValues $hasFields, $name) {
    // TODO: query single record from the database.

    return $this->all($hasFields)->byName($name);
  }
}
