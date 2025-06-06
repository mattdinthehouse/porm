<?php

namespace PORM\Relationships;

use ReflectionProperty;
use RuntimeException;

abstract class Relationship
{
	protected ReflectionProperty $property;

	public function setProperty( ReflectionProperty $property ): void
	{
		if( isset( $this->property ) ) throw new RuntimeException;
		else $this->property = $property;
	}


	public abstract function load( array $records ): void;


	protected function guessKey( string $class ): string
	{
		$class = $this->classBasename( $class );
		$class = strtolower( $class ); // TODO convert PascalCase to snake_case

		return "{$class}_id";
	}

	protected function classBasename( string $class ): string
	{
		$separator_at = strrpos( $class, '\\' );

		if( $separator_at !== false ) return substr( $class, $separator_at + 1 );
		else return $class;
	}
}