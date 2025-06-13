<?php

namespace PORM\Relationships;

use Attribute;

#[Attribute( Attribute::TARGET_PROPERTY )]
final class BelongsTo extends Relationship
{
	public function __construct(
		private ?string $local_key = null,
		private ?string $remote_key = null,
		private ?string $method_name = null,
	)
	{ }

	public function load( array $records ): void
	{
		$local_property = $this->property->getName();

		$remote_class = ( (string) $this->property->getType() );

		$local_key = $this->local_key ?? $this->guessKey( $remote_class );
		$remote_key = $this->remote_key ?? 'id';

		$method_name = $this->method_name ?? 'list';


		$ids = array_column( $records, $local_key );

		$parents = $remote_class::{$method_name}( $ids );
		$parents = array_column( $parents, null, $remote_key );

		foreach( $records as $record )
		{
			$record->{$local_property} = $parents[$record->{$local_key}] ?? null;
		}
	}
}