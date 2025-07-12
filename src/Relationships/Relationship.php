<?php

namespace PORM\Relationships;

use ReflectionClass;
use ReflectionAttribute;
use ReflectionProperty;
use RuntimeException;

abstract class Relationship
{
	protected string $local_class;

	protected ReflectionProperty $property;

	/**
	 * @param class-string $local_class
	 * @param ReflectionProperty $property
	 * @throws RuntimeException
	 * @return void
	 */
	public function setProperty( string $local_class, ReflectionProperty $property ): void
	{
		if( isset( $this->property ) ) throw new RuntimeException;
		
		$this->local_class = $local_class;
		$this->property = $property;
	}


	public abstract function load( array $records ): void;


	protected function guessKey( string $class ): string
	{
		$class = $this->classBasename( $class );

		// convert from PascalCase to snake_case - credit: https://stackoverflow.com/a/19533226
		$class = preg_replace( "/(?<!^)[A-Z]/", '_$0', $class );
		$class = strtolower( $class );

		return "{$class}_id";
	}

	protected function classBasename( string $class ): string
	{
		$separator_at = strrpos( $class, '\\' );

		return ( $separator_at !== false
			? substr( $class, $separator_at + 1 )
			: $class // not in a namespace
		);
	}


	public static function fetchFromClass( string $class_name ): array
	{
		$output = [];

		$definition = new ReflectionClass( $class_name );

		foreach( $definition->getProperties() as $property )
		{
			$relationships = $property->getAttributes( Relationship::class, ReflectionAttribute::IS_INSTANCEOF );

			if( $relationships )
			{
				$output[$property->getName()] = [];

				foreach( $relationships as $relationship )
				{
					$relationship = $relationship->newInstance(); // i wish i could pass constructor args...
					$relationship->setProperty( $class_name, $property );// ... but i can't so i have to do this.

					$output[$property->getName()] = $relationship;
				}
			}
		}

		return $output;
	}
}