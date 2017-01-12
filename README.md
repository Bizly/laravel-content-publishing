# Laravel Content Publishing Workflow
An Advanced Content Moderation System for Laravel 5.* that facitates content approval workflow.
Based on the hootlex/laravel-moderation composer package.

##Please Note:
Future versions of this package will include polymorphic change/audit log with versioning and prior version restoration functionality.

##Workflow Summary:
- 5 : DRAFT : submit for approval (changes to PENDING) | publish, schedule to publish (changes to PUBLISHED, if scheduled sets specified publish date) | archive (changes state to ARCHIVED).
- 4 : PENDING : approve or reject (unused at the moment).
- 3 : REJECTED : out-of-scope for now.
- 2 : APPROVED : publish or schedule to publish (changes to PUBLISHED, if scheduled sets specified publish date). | archive (changes state to ARCHIVED).
- 1 : PUBLISHED : mark as draft | archive
- 0 : ARCHIVED : publish | mark as draft

###Methods for Publishable Class
- Class::create(User $user) : user creates content object. (we need both object information & user information to be passed.) 
- $instance->publish(User $publisher/$author[if approved],$publish_at = now)
- $instance->approve(User $publisher)
- $instance->submitForApproval(User $author)

##Required FK Relationship Tables:
- users (must have an integer id and an email field)
- content_versions? (proposed)
    - id
    - object_type
    - object_id
    - action (create,update,submit,approve,reject,publish,archive,restore,delete,mark_as_draft)
    - date
    - user_id
    - major_version int, increments when published
    - minor_version int, increments when created or updated
    - changes text(json, moderatable_fields as defined in model) Ideally, would only be a delta between the existing and the update - but, that could get overly-complex.
- *Note : only currently-published versions would be available in the target polymorphed table referenced.
    - would require a method for published objects to go-around if there are new minor versions with getting as an author or a moderator

##User Roles??
- reader default user role, can only consume published content after the `published_at` date has passed.
- author 
- moderator 

###Methods for User
- `isAuthor($object_type,$object_id)` requires override in App's user class
- `isPublisher($object_type[from class being published],$object_id)`



##Required Table Migration Fields to Implement:
- status int(0,1,2,3,4,5)
- created_by int(FK:users.id)
- created_at timestamp
- updated_by int(FK:users.id)
- updated_at timestamp
- published_by int(FK:users.id)
- published_at timestamp

##Workflow Summary 

1. Author Creates a publishableContent resource which creates a draft version. Articles::all() returns only published articles.
2. Author Updates a publishableContent resource which creates a minor draft version 2. 
3. Author can, if need-be, restore minor draft version 1, thereby updating the resource object row and creating draft version 3, which is identical to version 1.
3. Once author is done making changes to their content they can then submit it for approval.

  0. **Archived**: Resource is only accessable by using (scope: `withArchived` )
  1. **Published**: Resource is now public and queryable.
  2. **Approved**:  Content resource can now be scheduled for publishing by either author or publisher.
  3. **Rejected**: Resource will be excluded from all queries. Rejected resources will be returned only if you scope a query to include them. (scope: `withRejected`)
  4. **Submitted**: Content is submitted to publisher for approval.
  5. **Draft**: Default State


##Installation

First, install the package through Composer.

```php
composer require bizly/laravel-content-publishing
```

Then include the service provider inside `config/app.php`.

```php
'providers' => [
    ...
    Hootlex\Moderation\ModerationServiceProvider::class,
    ...
];
```
Lastly you publish the config file.

```
php artisan vendor:publish --provider="Hootlex\Moderation\ModerationServiceProvider" --tag=config
```


## Prepare Model

To enable moderation for a model, use the `Hootlex\Moderation\Moderatable` trait on the model and add the `status`, `moderated_by` and `moderated_at` columns to your model's table.
```php
use Hootlex\Moderation\Moderatable;
class Post extends Model
{
    use Moderatable;
    ...
}
```

Create a migration to add the new columns. [(You can use custom names for the moderation columns)](#configuration)

Example Migration:
```php
class AddModerationColumnsToPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->smallInteger('status')->default(0);
            $table->dateTime('moderated_at')->nullable();
            //If you want to track who moderated the Model add 'moderated_by' too.
            //$table->integer('moderated_by')->nullable()->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('posts', function(Blueprint $table)
        {
            $table->dropColumn('status');
            $table->dropColumn('moderated_at');
            //$table->dropColumn('moderated_by');
        });
    }
}
```

**You are ready to go!**

##Usage
> **Note:** In next examples I will use Post model to demonstrate how the query builder works. You can Moderate any Eloquent Model, even User. 

###Moderate Models
You can moderate a model Instance:
```php
$post->markApproved();

$post->markRejected();

$post->markPostponed();

$post->markPending();
```

or by referencing it's id
```php
Post::approve($post->id);

Post::reject($post->id);

Post::postpone($post->id);
```

or by making a query.
```php
Post::where('title', 'Horse')->approve();

Post::where('title', 'Horse')->reject();

Post::where('title', 'Horse')->postpone();
```

###Query Models
By default only Approved models will be returned on queries. To change this behavior check the [configuration](#configuration).

#####To query the Approved Posts, run your queries as always.
```php
//it will return all Approved Posts
Post::all();

//it will return Approved Posts where title is Horse
Post::where('title', 'Horse')->get();
```
#####Query pending or rejected models.
```php
//it will return all Pending Posts
Post::pending()->get();

//it will return all Rejected Posts
Post::rejected()->get();

//it will return all Postponed Posts
Post::postponed()->get();

//it will return Approved and Pending Posts
Post::withPending()->get();

//it will return Approved and Rejected Posts
Post::withRejected()->get();

//it will return Approved and Postponed Posts
Post::withPostponed()->get();
```
#####Query ALL models
```php
//it will return all Posts
Post::withAnyStatus()->get();

//it will return all Posts where title is Horse
Post::withAnyStatus()->where('title', 'Horse')->get();
```

###Model Status
To check the status of a model there are 3 helper methods which return a boolean value.
```php
//check if a model is pending
$post->isPending();

//check if a model is approved
$post->isApproved();

//check if a model is rejected
$post->isRejected();

//check if a model is rejected
$post->isPostponed();
```

##Strict Moderation
Strict Moderation means that only Approved resource will be queried. To query Pending resources along with Approved you have to disable Strict Moderation. See how you can do this in the [configuration](#configuration).

##Configuration

###Global Configuration
To configuration Moderation package globally you have to edit `config/moderation.php`.
Inside `moderation.php` you can configure the following:

1. `status_column` represents the default column 'status' in the database. 
2. `moderated_at_column` represents the default column 'moderated_at' in the database.
2. `moderated_by_column` represents the default column 'moderated_by' in the database.
3. `strict` represents [*Strict Moderation*](#strict-moderation).

###Model Configuration
Inside your Model you can define some variables to overwrite **Global Settings**.

To overwrite `status` column define:
```php
const MODERATION_STATUS = 'moderation_status';
```

To overwrite `moderated_at` column define:
```php
const MODERATED_AT = 'mod_at';
```

To overwrite `moderated_by` column define:
```php
const MODERATED_BY = 'mod_by';
```

To enable or disable [Strict Moderation](#strict-moderation):
```php
public static $strictModeration = true;
```
