Psychic
====

```php
Post::all();
Post::count();
Post::find(1);
Post::find([1,2,3]);
Post::where('title = ?', 'test');
Post::where('author = ?', $author);
Post::where('author.name = ?', $authorName);
Post::where('author.id IN ?', [1,2,3]);
Post::where('author.name = :name', ['name' => 'mark']);
Post::where('author.address.city = ?', 'Philadelphia');
```

At it's most simple level Psychich is a bridge between Laravel and Doctrine. On top of a simple bridge it adds two compelling features,

Abstract DQL Syntax
----

While DQL is great it can be a bit verbose. Psychic adds yet another layer of abstraction onto your SQL to make it that much harder to debug database issus. No, seriously, it abstracts out DQL to make it a bit more terse and readable.

Schema / Model Mapping
----

Psychic will automatically parse Laravel's models and update your database to match the PHP annotations provided. Annotations follow the [Doctrine Annotations Reference](http://doctrine-orm.readthedocs.org/en/latest/reference/annotations-reference.html) and intend to support the full featureset.
