<?php

namespace PORM;

use PDO;
use PDOStatement;

/**
 * @method static \PDOStatement prepare( string $sql, ...$params )
 * @method static \PDOStatement select( string $sql, ...$params )
 */
final class DB
{
	private static ?self $instance = null;


	public function __construct(
		public readonly PDO $pdo,
	)
	{
		self::$instance = $this;
	}


	public static function __callStatic( string $method, array $args): mixed
	{
		return self::$instance->{$method}( ...$args );
	}


	public function prepare( string $sql, ...$params ): PDOStatement
	{
		$stmt = $this->pdo->prepare( $sql );

		foreach( $params as $name => $value )
		{
			$stmt->bindValue( $name, $value );
		}

		return $stmt;
	}


	public function select( string $sql, ...$params ): PDOStatement
	{
		$stmt = $this->pdo->prepare( $sql );

		$stmt->execute( $params );

		return $stmt;
	}
}