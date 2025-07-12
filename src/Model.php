<?php

namespace PORM;

use mysqli_result;
use PDOStatement;
use PORM\Relationships\Relationship;

trait Model
{
	private static array $relationships;

	private readonly RecordSet $siblings;


	protected function prepare( RecordSet $siblings ): void
	{
		$this->siblings = $siblings;


		if( !isset( self::$relationships ) )
		{
			self::$relationships = Relationship::fetchFromClass( static::class );
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

			$relationship->load( $this->siblings->records );
		}

		return $this->{$property};
	}


	protected static function one( PDOStatement|mysqli_result $stmt ): ?static
	{
		$record = self::fetchObject( $stmt );

		if( !$record ) return null;

		$siblings = new RecordSet( static::class, [ $record ] );

		$record->prepare( $siblings );

		return $record;
	}

	protected static function many( PDOStatement|mysqli_result $stmt ): array
	{
		$records = [];

		while( $record = self::fetchObject( $stmt ) )
		{
			$records[] = $record;
		}

		$siblings = new RecordSet( static::class, $records );

		foreach( $records as $record )
		{
			$record->prepare( $siblings );
		}

		return $records;
	}

	private static function fetchObject( PDOStatement|mysqli_result $stmt ): ?static
	{
		return match( true )
		{
			( $stmt instanceof PDOStatement ) => ( $stmt->fetchObject( static::class ) ?: null ),
			( $stmt instanceof mysqli_result ) => ( $stmt->fetch_object( static::class ) ?: null ),
		};
	}
}