<?php

use PORM\Helpers\PDO\DB;
use PORM\Example\Post;


chdir( '../' );

require 'vendor/autoload.php';


// Here we're booting PORM's DB helper, which in turn will set up the `PORM\Helpers\PDO\Facades\DB`
// facade. Obviously facade's are bad, but you can put the `$db` instance into your service
// container instead and never it. And you don't even need to use the DB helper at all, facade or
// instance - it's just here for the examples 😉
$db = new DB(
	new PDO( 'mysql:host=localhost;dbname=temp', 'root', 'root' ),
);


$posts = Post::all();

foreach( $posts as $post )
{
	print <<<STR
		"{$post->title}"
		Author: {$post->author->name}
		{$post->body}

		STR;

	foreach( $post->comments as $comment )
	{
		print <<<STR

			{$comment->author->name} says:
				{$comment->comment}

			STR;
	}

	print PHP_EOL;
}
