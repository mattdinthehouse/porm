<?php

namespace PORM;

use PORM\Relationships\Relationship;

/**
 * A container for a list of Model instances that should be considered part of a related set.
 * 
 * It's used by Model::prepare() to avoid each instance of the model holding an entire copy of it's
 * list of siblings - instead they all share one instance of RecordSet which cuts the memory usage.
 */
final class RecordSet
{
	public function __construct(
		/** @var class-string  */
		public readonly string $model_class,

		/** @var Model[] */
		public readonly array $records,
	)
	{ }
}