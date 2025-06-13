# PORM: a vanilla ORM for PHP

I think that Eloquent is too abstracted and feels bad once you go beyond single-table CRUD objects, Doctrine is too verbose, and neither of them are feasible to put into legacy projects.

PHP has [`PDOStatement::fetchObject()`](https://www.php.net/manual/en/pdostatement.fetchobject.php) and I like to write my SQL manually, so I ended up developing a design pattern of sorts for reading data out of the database and now I'm formalising that into PORM ☺️

PORM works more like a "framework" by providing the mechanisms for object mapping and loading associated records, but let's you write your own SQL and whatever logic for loading those associations and casting data types.

## Installation

`composer require mattdwyercool/porm`

## Features

* You get clean, vanilla PHP objects that you can do whatever you want with.
* The class properties don't have to match a DB table - you can do JOINs and fancy SELECTs.
* Protection from N+1 queries by default.
* You control the type casting and property visibility.

## How it works

I haven't written proper docs yet so have a look at the `examples/` folder for a demo or read below for a crash course.

But in a nutshell: define a class with properties like you normally would, then make it `use \PORM\Model` and define some static getter methods. Those getter methods should build a `PDOStatement` object whose columns match up with your class properties, and then call `static::one()` for a single-record getter or `static::many()` for an array.

`one()` and `many()` will call `PDOStatement::fetchObject( static::class )` on the statement you give it, then scan the class for any relationships you've defined (see below). PORM `unset()`'s those relationship properties so that you keep IDE autocomplete, and relies on `__get()` to lazy-load them when called upon. When that happens, PORM will also load the related records for the "siblings" in your original `static::many()` call to avoid N+1 queries.

## Object Mapping

Create a plain PHP class with your properties like:

```php
use PORM\Model;

final class Person
{
	use Model;

	public int $id;
	public string $name;
	public string $email;
}
```

That's it, but keep the data types as simple scalars (see below for non-scalar Type Casting).

### Getter functions

It's up to you how to manage your `PDO` instance, but PORM includes a very simple singleton wrapper class that we'll use in these examples which you can initialise with `new \PORM\DB( new PDO( ... ) )` during your app's bootstrapping.

You can define whatever model getters you need by adding static methods to your class that create a `PDOStatement` and pass it to `static::one()` for single-record getters or `static::many()` for an array.

`static::one()` returns an instance of your class or `null` if the SQL query returned empty:

```php
use PORM\DB;

final class Person
{
	public static function get( int $id ): ?static
	{
		$stmt = DB::select( <<<SQL
			SELECT
				p.`id`,
				p.`name`,
				p.`email`
			FROM `people` p
			WHERE p.`id` = :id;
			SQL,
			id: $id );

		return static::one( $stmt );
	}
}
```

`static::many()` returns an array of instances:

```php
use PORM\DB;

final class Person
{
	/**
	 * @return static[]
	 */
	public static function all(): array
	{
		$stmt = DB::select( <<<SQL
			SELECT
				p.`id`,
				p.`name`,
				p.`email`
			FROM `people` p;
			SQL );

		return static::many( $stmt );
	}
}
```

Obviously writing the same SQL query every time is dumb so for best practice just move it to a helper method:

```php
final class Person
{
	private static function sql(): string
	{
		return <<<SQL
			SELECT
				p.`id`,
				p.`name`,
				p.`email`
			FROM `people` p;
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
	 * @return static[]
	 */
	public static function all(): array
	{
		$sql = static::sql();

		$stmt = DB::select( $sql );

		return static::many( $stmt );
	}
}
```

### Type Casting

Under the hood `static::one()` and `static::many()` calls `$stmt->fetchObject( static::class )` which works like this:

> When an object is fetched, its properties are assigned from respective column values, and afterwards its constructor is invoked. 

therefore if you have something like a JSON object or a CSV string of tags you can handle them in the constructor like this:

```php
final class Post
{
	public int $id;

	private string $_tags;
	private string $_data;

	/** @var string[] */
	public array $tags;

	public object $data;


	public function __construct()
	{
		$this->tags = explode( ',', $this->_tags );

		$this->data = json_decode( $this->_data );
	}

	public static function get( int $id ): ?static
	{
		$stmt = DB::select( <<<SQL
			SELECT
				p.`id`,
				p.`tags` AS `_tags`,
				p.`data` AS `_data`
			FROM `posts` p
			WHERE p.`id` = :id;
			SQL,
			id: $id );

		return static::one( $stmt );
	}
}
```

### Joining multiple tables

Because you control the SQL you can do whatever you want:

```php
final class Post
{
	public int $id;

	public string $title;

	public string $author_name;

	public static function get( int $id ): ?static
	{
		$stmt = DB::select( <<<SQL
			SELECT
				p.`id`,
				p.`title`,
				a.`name` AS `author_name`
			FROM `posts` p
			INNER JOIN `authors` a ON (a.`id` = p.`author_id`)
			WHERE p.`id` = :id;
			SQL,
			id: $id );

		return static::one( $stmt );
	}
}
```

You can use CTEs and the `LATERAL` keyword, and `UNION`'s can be done a couple of ways:
1. wrap them in a CTE and have one `static::sql()` method like:
```php
return <<<SQL
	WITH x AS (
		SELECT
			a.`id`
		FROM `table_a` a

		UNION ALL

		SELECT
			b.`id`
		FROM `table_b` b
	)
	SELECT
		x.*
	FROM x
	SQL;
```
2. have multiple SQL helpers and assemble them in your getter method like:
```php
$sqlAbc = static::sqlAbc();
$sqlXyz = static::sqlXyz();

$stmt = DB::select( <<<SQL
	{$sqlAbc}
	WHERE ...

	UNION ALL

	{$sqlXyz}
	WHERE ...
	SQL );
```
3. merge two model classes together in PHP like:
```php
$records = [
	...Abc::all(),
	...Xyz::all(),
];
```

### Polymorphism and Inheritance

PORM uses `static` everywhere so you can extend your class and just override the necessary methods.

I don't have a generic example for this yet, but I've done these before:
* override your `static::sql()` helper in the sub-class to have an `INNER JOIN`
* override certain getter methods to have extra stuff in the `WHERE` clause
* override `static::sql()` in the sub-class to use a completely different table but the same column names

Because PORM models are just PHP classes and you write your own SQL the world is your oyster ❤️

## Relationships

Relationships in PORM are protected from N+1 queries by default, and you define them using attributes:

### Belongs To

On the child record you define the "local key" and the "remote class":

```php
use PORM\Relationships\BelongsTo;

public int $parent_id;

#[BelongsTo]
public Parent $parent;
```

PORM will call `Parent::list( $ids )` with the `$parent_id`'s of the loaded child records.

You can manually specify the local key (`$parent_id`) and remote key (`$id`) on the `BelongsTo` attribute if PORM guesses them wrong based on the property type, and a custom method if you want something other than `Parent::list()`.

### Has One

This is the inverse of `BelongsTo` - on the parent record you just define the remote class:

```php
use PORM\Relationships\HasOne;

#[HasOne]
public Child $child;
```

PORM will call `Child::fromParents( $parent_ids )` with the `$id`'s of the loaded parent records.

You can manually specify the local key (`$id`), remote key (`$parent_id`), and method name here too.

### Has Many

On the parent record you define an array and type-hint the remote class:

```php
use PORM\Relationships\HasMany;

/** @var Child[] */
#[HasMany( Child::class )]
public array $children;
```

PORM will call `Child::fromParents( $parent_ids )` with the `$id`'s of the loaded parent records same as `HasOne` does, but plucks `Child` records into the `$children` array where `$child->parent_id === $parent->id`.

`HasMany` makes the same guesses as `HasOne` and you can manually specify the same properties, but because PHP doesn't have typing for arrays yet you have to specify the remote class manually and a PHPDoc to get IDE autocomplete working.

# Writing to the DB

Right now the scope of PORM is just for reading from the DB but it's your code so you can do whatever you want.

I plan to add insert and update helpers to the `DB` class as well as some other utilities so check back later!

# Contributing

Yeah go for it mate, no worries 😊