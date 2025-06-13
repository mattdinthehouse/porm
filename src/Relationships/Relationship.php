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
}