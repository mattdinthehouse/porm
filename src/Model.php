<?php

namespace PORM;

use PDOStatement;
use PORM\Relationships\Relationship;
use ReflectionAttribute;
use ReflectionClass;

trait Model
{
	private static array $relationships;

	private readonly array $siblings;


	private function prepare( array $siblings ): void
	{
		$this->siblings = $siblings;


		if( !isset( self::$relationships ) )
		{
			self::$relationships = [];

			$class = new ReflectionClass( $this );

			foreach( $class->getProperties() as $property )
			{
				$relationships = $property->getAttributes( Relationship::class, ReflectionAttribute::IS_INSTANCEOF );

				if( $relationships )
				{
					self::$relationships[$property->getName()] = [];

					foreach( $relationships as $relationship )
					{
						$relationship = $relationship->newInstance(); // i wish i could pass constructor args...
						$relationship->setProperty( $property ); // ... but i can't so i have to do this.

						self::$relationships[$property->getName()] = $relationship;
					}
				}
			}
		}

		foreach( self::$relationships as $property => $_ )
		{
			unset( $this->{$property} );
		}
	}


	public function __get( string $property ): mixed
	{
		if( array_key_exists( $property, self::$relationships ) )
		{
			$relationship = self::$relationships[$property];

			$relationship->load( $this->siblings );
		}

		return $this->{$property};
	}


	protected static function one( PDOStatement $stmt ): ?static
	{
		$record = $stmt->fetchObject( static::class );

		if( !$record ) return null;

		$record->prepare( [ $record ] );

		return $record;
	}

	protected static function many( PDOStatement $stmt ): array
	{
		$records = [];

		while( $record = $stmt->fetchObject( static::class ) )
		{
			$records[] = $record;
		}

		foreach( $records as $record )
		{
			$record->prepare( $records );
		}

		return $records;
	}
}