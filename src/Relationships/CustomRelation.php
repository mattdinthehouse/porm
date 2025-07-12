<?php

namespace PORM\Relationships;

use Attribute;

#[Attribute( Attribute::TARGET_PROPERTY )]
final class CustomRelation extends Relationship
{
	public function __construct(
		private string $method_name,
	)
	{ }

	public function load( array $records ): void
	{
		$local_class = $this->local_class;

		$method_name = $this->method_name;

		$local_class::$method_name( $records );
	}
}