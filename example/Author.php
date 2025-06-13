<?php

namespace PORM\Example;

use PORM\DB;
use PORM\Model;
use PORM\Relationships\HasMany;

final class Author
{
	use Model;


	public int $id;

	public string $name;


	/** @var Post[] */
	#[HasMany(Post::class)]
	public array $posts;

	/** @var Comment[] */
	#[HasMany(Comment::class)]
	public array $comments;


	private static function sql(): string
	{
		return <<<SQL
			SELECT
				a.`id`,
				a.`name`
			FROM `authors` a
			SQL;
	}


	public static function get( int $id ): ?static
	{
		$sql = static::sql();

		$stmt = DB::select( <<<SQL
			{$sql}
			WHERE a.`id` = :id;
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
			WHERE a.`id` IN ({$ids});
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