<?php

namespace PORM\Example;

use PORM\DB;
use PORM\Model;
use PORM\Relationships\BelongsTo;

final class Comment
{
	use Model;


	public int $id;

	public int $post_id;

	public int $author_id;

	public string $comment;


	#[BelongsTo]
	public Post $post;

	#[BelongsTo]
	public Author $author;


	private static function sql(): string
	{
		return <<<SQL
			SELECT
				c.`id`,
				c.`post_id`,
				c.`author_id`,
				c.`comment`
			FROM `comments` c
			SQL;
	}


	public static function get( int $id ): static
	{
		$sql = static::sql();

		$stmt = DB::select( <<<SQL
			{$sql}
			WHERE c.`id` = :id;
			SQL,
			id: $id );

		return static::one( $stmt );
	}

	/**
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
			WHERE c.`id` IN ({$ids});
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

	/**
	 * @param int[] $post_ids
	 * @return static[]
	 */
	public static function fromPosts( array $post_ids ): array
	{
		$sql = static::sql();

		if( empty( $post_ids ) ) return [];
		$post_ids = array_map( 'intval', $post_ids );
		$post_ids = implode( ',', $post_ids );

		$stmt = DB::select( <<<SQL
			{$sql}
			WHERE c.`post_id` IN ({$post_ids});
			SQL );

		return static::many( $stmt );
	}
}