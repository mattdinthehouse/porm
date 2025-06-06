<?php

use PORM\DB;
use PORM\Example\Post;


chdir( '../' );

require 'vendor/autoload.php';


new DB(
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
