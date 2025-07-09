<?php

namespace PORM\Example;

use PDO;
use PORM\Model;
use PORM\Helpers\PDO\Facades\DB;
use PORM\Relationships\HasMany;
use PORM\Relationships\CustomRelation;

final class Author
{
	use Model;


	public int $id;

	public string $name;


	/** @var Post[] */
	#[HasMany(Post::class)]
	public array $posts;

	/** @var Comment[] */
	#[CustomRelation('withComments' )]
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

	/**
	 * @param static[] $authors
	 * @return Comment[]
	 */
	public static function withComments( array $authors ): array
	{
		// NOTE: this is a contrived example for how to implement a CustomRelation because it's
		// literally just a HasMany, but it's here to show off two things:
		//   1. you can implement custom relationship mapping logic if your relationship has
		//      additional parameters, comes from a different data source (eg REST API), or is
		//      otherwise non-trivial.
		//   2. that you can return the related objects which is handy for eager-loading or if
		//      you're calling code also needs to perform work on the related entities without
		//      fetching them itself.

		if( empty( $authors ) ) return [];

		// Get the applicable comment IDs
		$author_ids = array_column( $authors, 'id' );
		$author_ids = implode( ',', $author_ids );

		$comment_ids = DB::select( <<<SQL
			SELECT
				c.`id`
			FROM `comments` c
			WHERE c.`author_id` IN ({$author_ids});
			SQL );
		$comment_ids = $comment_ids->fetchAll( PDO::FETCH_COLUMN );

		// Load the comments and attach them to each author
		$comments = Comment::list( $comment_ids );

		foreach( $authors as $author )
		{
			$author->comments = [];

			foreach( $comments as $comment )
			{
				if( $comment->author_id === $author->id ) $author->comments[] = $comment;
			}
		}

		return $comments;
	}
}