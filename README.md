RestApiApp
==========

==Live URL==
http://restapi.rjkeller.net


==How to run API==

The official manual is the PhpDoc comments in src/Pixonite/RestApiBundle/Controller/SampleDataController.php

For some Curl examples, see the unit test in src/Pixonite/RestApiBundle/Tests/Controller/SampleDataControllerTest.php


Symfony route dump:

 api_sample-data          GET    ANY    ANY  /api/v1/{entityName}.json         
 api_sample-data_create   POST   ANY    ANY  /api/v1/Item.json                 
 api_sample-data_update   POST   ANY    ANY  /api/v1/Item/{id}.json            
 api_item-set_create      POST   ANY    ANY  /api/v1/ItemSet.json              
 api_item-set_update      POST   ANY    ANY  /api/v1/ItemSet/{id}.json         
 api_sample-data_show     GET    ANY    ANY  /api/v1/{entityName}/{id}.json    
 api_sample-data_delete   DELETE ANY    ANY  /api/v1/{entityName}/{id}.json    

entityName can be either ItemSet or Item. Best to look at the Curl examples since I didn't have time for super polished documentation.

==Code Checkout==
git clone https://github.com/rjkeller/RestApiApp .

==Weapons of Choice==
PHP 5.5 and Symfony/Doctrine

Server:
- Gentoo (Kernel 3.16.1)
- Apache 2.4.10
- PHP 5.5.15
- MariaDB 10.0.13

==How to install==
If for some reason you wanted to try this on your local machine, checkout the code and run 'composer install' on a clean install and composer will get you all set up (except for the Apache vhost. You're stuck doing that one manually for now)

You should probably also have Doctrine set up your DB schema:
./console doctrine:schema:create --force
./console doctrine:fixtures:load

==How to get DB schema==
The real official way is to run in the /app folder:
./console doctrine:schema:create --dump-sql

To load fixtures, you run:
./console doctrine:fixtures:load

But if you want to cheat, I ran 'mysqldump' for you and put the contents in the file dump.sql in github (which includes some fixtures and test data).



==Improvements for later==
If I had more time, I'd probably improve a couple of things. Here is a brief (and likely incomplete) list:

- I was hoping to have the interface be very abstract, so that you could instantly add more models and have them supported in the API. This was somewhat accomplished, but more work would need to be done to evolve the design patterns to accommodate every need. One problem is that some entities have special query options and others have special 'create' quirks and right now those are kind of hacked in. It'd be better to move those changes to the model classes.

- Errors are not super verbose. It could use some improvement to provide more details to the developer if they hit a snag. Right now it can be challenging to debug a call without looking at the code of the Rest server and that is a problem.

- I ran out of time for super solid form validation. So the system permits more junk than it should. My plan for implementation would involve using the Symfony form validation system to make it super easy to validate each field. I have some validation that was mentioned in the spec, but it's incomplete in my opinion.

- I was hoping to have more extensive unit tests, but I ran out of time. I guess this is the story of unit tests at too many organizations :). But some is better than none.

- So using the Doctrine 'array' type was ItemSet.itemIds was good in theory, but the operators were more broken than I remember it. I ended up spending a dumb amount of time trying to debug Doctrine. So for ItemSet, the code for managing itemIds is more wonky than it should be. I basically had to hack the operators in PHP and that is not cool. I guess I should've just did it the old-fashioned way and used JOINs with a table for ItemSet->Item relations. So if I had more time, I'd likely just do joins and trash the PHP operators. With that said, the PHP operators do work.

==Why MongoDB might be better==

So relational database systems are great when you have data with a fixed, flat structure (like a bank statement). However, in the case of these item sets, you'll likely have more hierarchy than just a list of Items with a Set. For example, if I was categorizing an outfit, it might make sense to look like:

type: outfit
items: {
	'shirt' => {
		'color' => 'white',
		'style' => 'tshirt',
	}
	'pants' => {
		'style' => 'jeans'
	}
}

While a mechanical keyboard would be like:

type: keyboard
items: {
	'mechanical' => {
		'keycaps' => {
			'cherry_mx' => {
				'blue','brown',...
			}
		}
	}
}

Communicating this structure in a relational database is difficult (and even trickier to query against). MongoDB is designed for this exact cuircumstance. Through the Mongo interface, you can literally give it this information:

'items' => 'shirt' => color: white

to retrieve white shirts. If shirt isn't defined, then the item is skipped. MongoDB also allows you to add indexes over this information and provides built-in sharding for scalability.

In MySQL, you could try to create a bunch of tables and joins to put it all together, but that increases complexity of the system.

Just something to think about :).
