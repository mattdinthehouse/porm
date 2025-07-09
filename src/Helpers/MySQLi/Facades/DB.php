<?php

namespace PORM\Helpers\MySQLi\Facades;

use mysqli_result;
use PORM\Helpers\MySQLi\DB as BaseDB;

/**
 * @method static \mysqli_result select( string $sql, ...$params )
 * @method static int|string insert( string $sql, ...$params )
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