<?php

namespace PORM\Helpers\PDO\Facades;

use PDOStatement;
use PORM\Helpers\PDO\DB as BaseDB;

/**
 * @method static \PDOStatement prepare( string $sql, ...$params )
 * @method static \PDOStatement select( string $sql, ...$params )
 * @method static string insert( string $sql, ...$params )
 * @method static int write( string $sql, ...$params )
 */
final class DB
{
	private static ?BaseDB $db = null;


	public function __construct(
		BaseDB $db,
		bool $replace_if_set = true,
	)
	{
		if( !self::$db || $replace_if_set ) self::$db = $db;
	}


	public static function __callStatic( string $method, array $args): mixed
	{
		return self::$db->{$method}( ...$args );
	}
}