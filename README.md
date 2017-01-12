# Laravel Content Publishing Workflow
An Advanced Content Publishing System for Laravel 5.* that facitates content approval workflow.
Based on the hootlex/laravel-moderation composer package.

##Please Note:
Future versions of this package will extend the polymorphic change/audit log with enhanced versioning and version restoration functionality.

##Workflow Summary:
- 5 : DRAFTED : submit for approval (changes to PENDING) | publish, schedule to publish (changes to PUBLISHED, if scheduled sets specified publish date) | archive (changes state to ARCHIVED).
- 4 : SUBMITTED : approve or reject (unused at the moment).
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
    - changes text(json, publishable_fields as defined in model) Ideally, would only be a delta between the existing and the update - but, that could get overly-complex.
> **Note:** only currently-published versions would be available in the target polymorphed table referenced.
    - would require a method for published objects to go-around if there are new minor versions with getting as an author or a publisher


##User Roles??
- reader default user role, can only consume published content after the `published_at` date has passed.
- author 
- publisher

###Suggested Methods for User to Handle Permissions in your controller:
- `isAuthor($object_type,$object_id)` requires override in App's user class
- `isPublisher($object_type[from class being published],$object_id)` 



##Required Table Migration Fields to Implement:
- status int(0,1,2,3,4,5)
- content_version_id int(FK:content_versions.id)
- created_by int(FK:users.id)
- created_at timestamp
- updated_by int(FK:users.id)
- updated_at timestamp
- published_by int(FK:users.id)
- published_at timestamp

##Workflow Summary:

1. Author Creates a `publishableContent` resource which creates a draft version. Articles::all() returns only published articles.
2. Author Updates a `publishableContent` resource which creates a minor draft version 2. 
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
    Bizly\ContentPublishing\ContentPublishingServiceProvider::class,
    ...
];
```
Lastly you publish the config file.

```
php artisan vendor:publish --provider="Bizly\ContentPublishing\ContentPublishingServiceProvider" --tag=config
```


## Prepare Model

To enable publishing for a model, use the `Bizly\ContentPublishing\Publishable` trait on the model and add the `status`, `content_version_id`, `published_by` and `published_at` columns to your model's table.
```php
use Bizly\ContentPublishing;
class Article extends Model
{
    use PublishableContent;
    ...
}
```

Create a migration to add the new columns to an existing table. [(You can use custom names for the content publishing columns)](#configuration)

Example Migration:
```php
class AddContentPublishingColumnsToArticlesTable extends Migration
{

    public function up()
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->smallInteger('status')->default(5); // 5 = Draft State upon creation.
            $table->integer('content_version_id')->unsigned();
            $table->integer('published_by')->nullable()->unsigned();
            $table->dateTime('published_at')->nullable();
            $table->timestamps; // If you don't already have them, adds: created_at, created_by, updated_at, updated_by cols.
        });
    }

    public function down()
    {
        Schema::table('articles', function(Blueprint $table)
        {
            $table->dropColumn('status');
            $table->dropColumn('content_version_id');
            $table->dropColumn('published_at');
            $table->dropColumn('published_by');
        });
    }
}
```

##Usage
> **Note:** In next examples I will use Article model to demonstrate how the query builder works. You can Moderate any Eloquent Model, even User. 

###Submit, Approve, and Publish Models
You can submit, approve, and publish a model Instance:
```php

Article::create($user_id, array $article); //Gives author or publisher the ability to create a new draft version of the content. Increments draft version. **Note** This overrides laravel's existing create method for the model.

$article->createDraft($user_id, array $newDraftArticle); //Increments the minor version. If the content is currently drafted it will update the row as well. Any other status will go-around and just update.

$article->updateDraft($draft_id, $user_id, array $updates); //Updates the current draft instead of creating a new one. This is particularly useful if your system saves changes on keyup to prevent data creep. If the content is currently drafted it will update the row as well. Any other status will go-around and just update.

$article->getLatestDraft(); //Returns the most up-to-date draft version for both authors and publishers.

$article->submitContent($authoring_user_id); //Author submits latest draft version.

$article->rejectContent($publishing_user_id); //Publisher rejects to content submission. 

$article->approveContent($publishing_user_id); //Publisher approves the content and the author can now publish at will.

$article->publishContent($user_id,$published_at[optional]); //`$published_at` defaults to now, set to a future date to schedule it. `$user_id` can be author if approved and publisher can publish at any point in time. This also creates a major version for the content.

$article->archiveContent($publisher_id); // A publisher can archive content if they want to take it offline quickly for some reason.

$article->getContentVersionHistory(); // An author or publisher can view the change/version log for an article.

$article->restoreContentVersion($content_version_id,$user_id); // An author or publisher can create a new draft based on a historical content version.

```

or by making a query.
```php
Article::where('title', 'Horse')->approveContent($publisher_id);

//Say you have an interface for publishers to view published and archived content submissions:
Article::where('title', 'Horse')->withArchived()->get();

Article::where('title', 'Horse')->withSubmitted()->get();

//For all of them:
Article::withArchived()->get(); // Published and Archived Articles
Article::archived()->get(); // Just Archived Articles

Article::withSubmitted()->get(); // Published and Submitted Articles
Article::submitted()->get(); // Returns all Articles Submitted for Approvel to the Publisher.

//Will return all published and approved Articles
Article::withApproved()->get(); //Returns the latest approved version from the content_versions table. As well as the current version of any published Articles.
Article::approved()->get(); // Just returns the latest approved version from the content_versions table for each article.

Article::latestDrafts(); //Returns the latest version from the content_versions table.

```

#####To query the Published Articles, run your queries as always.
```php
//it will return all Published Articles
Article::all();

//it will return Published Articles where title is Horse
Article::where('title', 'Horse')->get();
```

#####Query ALL models
```php
//it will return all Articles
Articles::withAnyStatus()->get();

//it will return all Articles where title is Horse
Articles::withAnyStatus()->where('title', 'Horse')->get();
```

###Model Status
To check the status of a model there are helper methods which return a boolean value.
```php
//check if a model is published
$article->isPublished();

//check if a model is approved
$article->isApproved();

//check if a model is rejected
$article->isRejected();

//check if a model is submitted
$article->isSubmitted();

```

##Configuration

###Global Configuration
To configuration Moderation package globally you have to edit `config/bizly.content-publishing.php`.
Inside `bizly.content-publishing.php` you can configure the following:

1. `status_column` represents the default column `status` in the database. 
2. `content_version_id_column` the current version of the model.
2. `approved_at_column` represents the default column `approved_at` in the database.
2. `approved_by_column` represents the default column `approved_by` in the database.

###Model Configuration
Inside your Model you can define some variables to overwrite **Global Settings**.

To overwrite `status` column define:
```php
const CONTENT_PUBLISHING_STATUS = 'content_publishing_status';
```

To overwrite `published_at` column define:
```php
const PUBLISHED_AT = 'pub_at';
```

To overwrite `published_by` column define:
```php
const PUBLISHED_BY = 'pub_by';
```
