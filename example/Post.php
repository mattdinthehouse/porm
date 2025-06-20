<?php

namespace PORM\Example;

use PORM\Helpers\PDO\DB;
use PORM\Model;
use PORM\Relationships\BelongsTo;
use PORM\Relationships\HasMany;

final class Post
{
	use Model;


	public int $id;

	public int $author_id;

	public string $title;

	public string $body;


	#[BelongsTo]
	public Author $author;

	/** @var Comment[] */
	#[HasMany(Comment::class)]
	public array $comments;


	private static function sql(): string
	{
		return <<<SQL
			SELECT
				p.`id`,
				p.`author_id`,
				p.`title`,
				p.`body`
			FROM `posts` p
			SQL;
	}


	public static function get( int $id ): ?static
	{
		$sql = static::sql();

		$stmt = DB::select( <<<SQL
			{$sql}
			WHERE p.`id` = :id;
			SQL,
			id: $id );

		return static::one( $stmt );
	}

	/**
	 * @param int[] $ids
	 * @return static[]
	 */
	public static function list( array $ids ): array
	{
		$sql = static::sql();

		if( empty( $ids ) ) return [];
		$ids = array_map( 'intval', $ids );
		$ids = implode( ',', $ids );

		$stmt = DB::select( <<<SQL
			{$sql}
			WHERE p.`id` IN ({$ids});
			SQL );

		return static::many( $stmt );
	}

	/**
	 * @return static[]
	 */
	public static function all( ): array
	{
		$sql = static::sql();

		$stmt = DB::select( <<<SQL
			{$sql};
			SQL );

		return static::many( $stmt );
	}
}