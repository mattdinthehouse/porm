<?php

namespace PORM\Relationships;

use Attribute;

#[Attribute( Attribute::TARGET_PROPERTY )]
final class HasMany extends Relationship
{
	public function __construct(
		private ?string $remote_class = null,
		private ?string $local_key = null,
		private ?string $remote_key = null,
		private ?string $method_name = null,
	)
	{ }

	public function load( array $records ): void
	{
		$local_property = $this->property->getName();

		$local_class = $records[0]::class;
		$remote_class = $this->remote_class ?? $this->guessRemoteClass();

		$remote_key = $this->remote_key ?? $this->guessKey( $local_class );
		$local_key = $this->local_key ?? 'id';

		$method_name = $this->method_name ?? 'from' . $this->classBasename( $local_class ) . 's'; // TODO pluralise properly

		
		$ids = array_column( $records, $local_key );

		$children = $remote_class::{$method_name}( $ids );
		
		foreach( $records as $record )
		{
			$record->{$local_property} = [];
			
			foreach( $children as $child )
			{
				if( $child->{$remote_key} === $record->{$local_key} ) $record->{$local_property}[] = $child;
			}
		}
	}

	private function guessRemoteClass(): ?string
	{
		$doc = $this->property->getDocComment();

		if( preg_match( "/@var (\w+)\[\]/", $doc, $matches ) ) return $matches[1];
		else return null;
	}
}