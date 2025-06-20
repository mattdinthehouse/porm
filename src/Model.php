<?php

namespace PORM;

use mysqli_result;
use PDOStatement;
use ReflectionClass;
use ReflectionAttribute;
use PORM\Relationships\Relationship;

trait Model
{
	private static array $relationships;

	private readonly array $siblings;


	protected function prepare( array $siblings ): void
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


	protected static function one( PDOStatement|mysqli_result $stmt ): ?static
	{
		$record = self::fetchObject( $stmt );

		if( !$record ) return null;

		$record->prepare( [ $record ] );

		return $record;
	}

	protected static function many( PDOStatement|mysqli_result $stmt ): array
	{
		$records = [];

		while( $record = self::fetchObject( $stmt ) )
		{
			$records[] = $record;
		}

		foreach( $records as $record )
		{
			$record->prepare( $records );
		}

		return $records;
	}

	private static function fetchObject( PDOStatement|mysqli_result $stmt ): static
	{
		return match( true )
		{
			( $stmt instanceof PDOStatement ) => $stmt->fetchObject( static::class ),
			( $stmt instanceof mysqli_result ) => $stmt->fetch_object( static::class ),
		};
	}
}