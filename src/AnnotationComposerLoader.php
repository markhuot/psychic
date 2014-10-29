<?php namespace Exchanger;

/**
 * @link http://blog.riff.org/2014_02_16_reducing_redundancy_in_doctrine_annotations_loading
 */
class AnnotationComposerLoader {
 
  /**
   * @var array
   */
  protected $namespaces;
 
	/**
	 * @param $namespaces array(string)
	 * @return void
	 */
  public function __construct(array $namespaces) {
    $this->namespaces = $namespaces;
  }
 
  /**
   * @param string $name
   *
   * @return bool
   */
  public function __invoke($name) {
    foreach ($this->namespaces as $namespace) {
      if (strpos($name, $namespace) === 0) {
        return true;
      }
    }
    return false;
  }
}