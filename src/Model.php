<?php

namespace PORM;

use mysqli_result;
use PDOStatement;

trait Model
{
	private readonly RecordSet $siblings;


	protected static function prepare( RecordSet $siblings ): void
	{
		foreach( $siblings->records as $record )
		{
			$record->siblings = $siblings;

			foreach( $siblings->relationships as $property => $_ )
			{
				unset( $record->{$property} );
			}
		}
	}


	public function loadRelation( string $property ): void
	{
		$relationship = $this->siblings->relationships[$property];

		$relationship->load( $this->siblings->records );
	}


	public function __get( string $property ): mixed
	{
		if( array_key_exists( $property, $this->siblings->relationships ) )
		{
			$this->loadRelation( $property );
		}

		return $this->{$property};
	}


	protected static function one( PDOStatement|mysqli_result $stmt ): ?static
	{
		$record = self::fetchObject( $stmt );

		if( !$record ) return null;

		$siblings = new RecordSet( static::class, [ $record ] );

		self::prepare( $siblings );

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

		self::prepare( $siblings );

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