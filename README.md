# NoSQLite – simple key => value store based on SQLite3

[![Build Status](https://secure.travis-ci.org/madcoda/nosqlite.php.png)](https://travis-ci.org/madcoda/nosqlite.php)

## Introduction

NoSQLite is simple key-value store using SQLite as raw data store. Mainly for small project where MySQL is too heavy and files are too ugly.

Library is fully covered with unit tests.

## Requirements

- PHP >=5.3.2
    - PDO (by default as of PHP 5.1.0)
    - PDO_SQLITE (by default as of PHP 5.1.0)

## Installing via Composer

[Get composer](http://getcomposer.org/download/) and add following lines to ```composer.json```:
```
{
    "require": {
        "madcoda/nosqlite": "dev-master"
    },
    "repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/madcoda/nosqlite.php"
    }
    ]
}
```

## Usage

Create stores' manager (file will be created if not exists)

        $nsql = new NoSQLite\NoSQLite('mydb.sqlite');
        $store = $nsql->getStore('movies');

Set value in store (key and value max length are [limited by SQLite TEXT datatype](http://sqlite.org/limits.html#max_length))

        $newId = uniqid();
        $store->set('doc:'.$newId, json_encode(array('title' => 'Good Will Hunting', 'director' => 'Gus Van Sant'));

Set Typed values
    
    // Integer
    $store->setInt('comment:123:count', 90);

    // Boolean
    $store->setBoolean('post:123:is_read', true);
    $store->getBoolean('post:123:is_read');

    // Float
    $store->setFloat('product:123:price', 49.99);

    // Date
    $store->setDate('product:123:modified', "2013-01-01 12:00:00");
    $store->setDate('product:123:modified', "2013-07-01");

Get value from store (will be created if not exists)

        $value = $store->get('doc:3452345');

Get all values

        $store->getAll();

Delete all values

        $store->deleteAll();

Iterate through store (Store implements Iterator interface)

        foreach($store as $key => $value)
            ...

Get number of values in store (Store implements Countable interface)

        count($store);

## Tests

Tests are written in PHPUnit which is required as a dev package in ```composer.json```. For running test use

    composer install
    make test


## License

(The MIT License)

Copyright 2013 Jason Leung http://madcoda.com

This project is free software released under the MIT/X11 license:

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
