<?php

namespace PORM\Helpers\MySQLi;

use mysqli;
use mysqli_result;

/**
 * @method static \mysqli_result select( string $sql, ...$params )
 * @method static int|string insert( string $sql, ...$params )
 * @method static int write( string $sql, ...$params )
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

	/**
	 * Insert a row into the database and return it's ID.
	 * ```php
	 * $post_id = DB::insert( <<<SQL
	 *     INSERT INTO `posts`
	 *     SET `author_id` = ?,
	 *         `title` = ?;
	 *     SQL,
	 *     $author_id,
	 *     $title );
	 * ```
	 * @param string $sql
	 * @param array $params
	 * @return int|string
	 */
	private function insert( string $sql, ...$params ): int|string
	{
		$stmt = $this->mysql->prepare( $sql );

		$stmt->execute( $params );

		return $this->mysql->insert_id;
	}

	/**
	 * Prepare and execute an UPDATE or DELETE query and return the number of affected rows.
	 * ```php
	 * $num_affected = DB::modify( <<<SQL
	 *     UPDATE `posts`
	 *     SET `title` = :title
	 *     WHERE `id` = :id;
	 *     SQL,
	 *     id: $post_id,
	 *     title: $new_title );
	 * ```
	 * @param string $sql
	 * @param array $params
	 * @return int
	 */
	private function modify( string $sql, ...$params ): int
	{
		$stmt = $this->mysql->prepare( $sql );

		$stmt->execute( $params );

		return $stmt->affected_rows;
	}
}