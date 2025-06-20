<?php

namespace PORM\Helpers\MySQLi;

use mysqli;
use mysqli_result;

/**
 * @method static \mysqli_result select( string $sql, ...$params )
 */
final class DB
{
	private static ?self $instance = null;


	public function __construct(
		public readonly mysqli $mysql,
	)
	{
		self::$instance = $this;
	}


	public static function __callStatic( string $method, array $args): mixed
	{
		return self::$instance->{$method}( ...$args );
	}


	/**
	 * Prepare and execute a SELECT query with the given positional parameters.
	 * ```php
	 * $posts = DB::select( <<<SQL
	 *     SELECT *
	 *     FROM `posts` p
	 *     WHERE p.`author_id` = ?;
	 *     SQL,
	 *     $author_id );
	 * ```
	 * @param string $sql
	 * @param array $params
	 * @return mysqli_result
	 */
	private function select( string $sql, ...$params ): mysqli_result
	{
		$stmt = $this->mysql->prepare( $sql );

		$stmt->execute( $params );

		return $stmt->get_result();
	}
}