<?php namespace Psychic;

use \ReflectionClass;
use \Doctrine\DBAL\Connection;
use \Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use \Doctrine\Common\Annotations\AnnotationReader;
use \Doctrine\Common\Inflector\Inflector;

class Schema {
	
	private $conn;
	
	/**
	 * @param \Doctrine\DBAL\Connection $conn
	 * @return \Psychic\Schema
	 */
	public function __construct(Connection $conn)
	{
		$this->conn = $conn;
		$this->reader = new AnnotationReader();
	}
	
	/**
	 * Sync any models present in the path to the connected database
	 * 
	 * @param string $pathToModels
	 * @return void
	 */
	public function sync($pathToModels)
	{
		$computedSchema = new \Doctrine\DBAL\Schema\Schema();
		
		// $em->getClassMetaData($className)
		
		$models = scandir($pathToModels);
		foreach ($models as $model) {
			$modelName = preg_replace('/(.*)\..*$/', '$1', $model);
			if (!$modelName || !class_exists($modelName)) continue;
			$reflectionClass = new ReflectionClass($modelName);
			
			if (!$this->reader->getClassAnnotation($reflectionClass, 'Doctrine\ORM\Mapping\Entity')) {
				continue;
			}
			
			$table = $this->createTableOnSchema($computedSchema, $reflectionClass);
			$this->addPropertiesToTable($table, $reflectionClass);
		}
		
		$existingSchema = $this->conn->getSchemaManager()->createSchema();
		$this->convert($existingSchema, $computedSchema);
	}
	
	public function createTableOnSchema($schema, $reflectionClass)
	{
		$tableName = $reflectionClass->getName();
		
		if ($tableAnnotation=$this->reader->getClassAnnotation($reflectionClass, 'Doctrine\ORM\Mapping\Table')) {
			$tableName = $tableAnnotation->name;
		}
		
		return $schema->createTable($tableName);
	}
	
	public function addPropertiesToTable($table, \ReflectionClass $reflectionClass)
	{
		$properties = $reflectionClass->getProperties();
		foreach ($properties as $property) {
			$column = (object)[
				'name' => '',
				'type' => 'string',
				'options' => [],
			];
			
			if ($columnAnnotation=$this->reader->getPropertyAnnotation($property, 'Doctrine\ORM\Mapping\Column')) {
				$column->name = $columnAnnotation->name ?: $property->getName();
				$column->type = $columnAnnotation->type;
				$column->options = $columnAnnotation->options;
				if ($columnAnnotation->length) $column->options['length'] = $columnAnnotation->length;
				if ($columnAnnotation->nullable) $column->options['notnull'] = !$columnAnnotation->nullable;
			}
			else {
				continue;
			}
			
			if ($generatedValue=$this->reader->getPropertyAnnotation($property, 'Doctrine\ORM\Mapping\GeneratedValue')) {
				$column->options['autoincrement'] = true;
			}
			
			$table->addColumn(
				$column->name,
				$column->type,
				$column->options
			);
			
			if ($columnAnnotation->unique) {
				$table->addUniqueIndex([$column->name]);
			}
			
			if ($id=$this->reader->getPropertyAnnotation($property, 'Doctrine\ORM\Mapping\Id')) {
				$table->setPrimaryKey([$column->name]);
			}
		}
	}
	
	public function convert($schemaA, $schemaB)
	{
		$sqlStatements = $schemaA->getMigrateToSql($schemaB, $this->conn->getDatabasePlatform());
		foreach ($sqlStatements as $sql) {
			$this->conn->query($sql);
		}
	}
	
}