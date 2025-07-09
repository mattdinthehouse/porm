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
		$local_class = $this->property->getDeclaringClass()->getName(); // annoyingly this is self::class not static::class

		$method_name = $this->method_name;

		$local_class::$method_name( $records );
	}
}