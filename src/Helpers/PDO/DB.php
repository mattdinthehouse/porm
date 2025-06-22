<?php

namespace PORM\Helpers\PDO;

use PDO;
use PDOStatement;

/**
 * @method static \PDOStatement prepare( string $sql, ...$params )
 * @method static \PDOStatement select( string $sql, ...$params )
 * @method static string insert( string $sql, ...$params )
 * @method static int write( string $sql, ...$params )
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


	/**
	 * Prepare a statement and bind the given values to it.
	 * This is a shorthand for binding by value if you're also going to bind by reference - 
	 * ```php
	 * $stmt = DB::prepare( <<<SQL
	 *     INSERT INTO `posts`
	 *     SET `author_id` = :author_id,
	 *         `title` = :title;
	 *     SQL,
	 *     author_id: $author_id );
	 * 
	 * $stmt->bindParam( 'title', $title );
	 * 
	 * foreach( $titles as $title )
	 * {
	 *     $stmt->execute();
	 * }
	 * ```
	 * @param string $sql
	 * @param array $params
	 * @return bool|PDOStatement
	 */
	private function prepare( string $sql, ...$params ): PDOStatement
	{
		$stmt = $this->pdo->prepare( $sql );

		foreach( $params as $name => $value )
		{
			$stmt->bindValue( $name, $value );
		}

		return $stmt;
	}

	/**
	 * Prepare and execute a SELECT query with the given named parameters.
	 * ```php
	 * $posts = DB::select( <<<SQL
	 *     SELECT *
	 *     FROM `posts` p
	 *     WHERE p.`author_id` = :author_id;
	 *     SQL,
	 *     author_id: $author_id );
	 * ```
	 * @param string $sql
	 * @param array $params
	 * @return PDOStatement
	 */
	private function select( string $sql, ...$params ): PDOStatement
	{
		$stmt = $this->pdo->prepare( $sql );

		$stmt->execute( $params );

		return $stmt;
	}

	/**
	 * Insert a row into the database and return it's ID.
	 * ```php
	 * $post_id = DB::insert( <<<SQL
	 *     INSERT INTO `posts`
	 *     SET `author_id` = :author_id,
	 *         `title` = :title;
	 *     SQL,
	 *     author_id: $author_id,
	 *     title: $title );
	 * ```
	 * @param string $sql
	 * @param array $params
	 * @return string
	 */
	private function insert( string $sql, ...$params ): string
	{
		$stmt = $this->pdo->prepare( $sql );

		$stmt->execute( $params );

		return $this->pdo->lastInsertId();
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
		$stmt = $this->pdo->prepare( $sql );

		$stmt->execute( $params );

		return $stmt->rowCount();
	}
}