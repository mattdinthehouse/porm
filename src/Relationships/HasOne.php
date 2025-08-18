<?php

namespace PORM\Relationships;

use Attribute;

#[Attribute( Attribute::TARGET_PROPERTY )]
final class HasOne extends Relationship
{
	public function __construct(
		private ?string $local_key = null,
		private ?string $remote_key = null,
		private ?string $method_name = null,
		private ?string $remote_class = null,
	)
	{ }

	public function load( array $records ): void
	{
		$local_property = $this->property->getName();

		$local_class = $this->local_class;
		$remote_class = $this->remote_class ?? ltrim( (string) $this->property->getType(), '?' );

		$remote_key = $this->remote_key ?? $this->guessKey( $local_class );
		$local_key = $this->local_key ?? 'id';

		$method_name = $this->method_name ?? 'from' . $this->classBasename( $local_class ) . 's'; // TODO pluralise properly


		$ids = array_column( $records, $local_key );

		$children = $remote_class::{$method_name}( $ids );
		$children = array_column( $children, null, $remote_key );

		foreach( $records as $record )
		{
			$record->{$local_property} = $children[$record->{$local_key}] ?? null;
		}
	}
}