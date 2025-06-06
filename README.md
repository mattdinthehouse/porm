# PORM: a vanilla ORM for PHP

I think that Eloquent is too abstracted and feels bad once you go beyond single-table CRUD objects.

PHP has [this](https://www.php.net/manual/en/pdostatement.fetchobject.php) and [this](https://www.php.net/manual/en/mysqli-result.fetch-object.php), and I like to write my SQL manually, so I ended up developing a design pattern of sorts for reading data out of the database and now I'm formalising that into PORM ☺️

## How it works

I haven't written any docs yet so have a look at the `examples/` folder for a demo.

## Features

* You get clean, vanilla PHP objects that you can do whatever you want with.
* The class properties don't have to match a DB table - you can do JOINs and fancy SELECTs.
* Protection from N+1 queries by default.
* You control the type casting and property visibility.