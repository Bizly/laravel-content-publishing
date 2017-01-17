<?php

namespace Bizly\ContentPublishing;

trait PublishableContent
{
    use ContentPublishingQueryBuilder;

    /**
     * Boot the publishable content trait for a model.
     *
     * @return void
     */
    public static function bootPublishableContent()
    {
        static::addGlobalScope(new ContentPublishingScope);
    }

    /**
     * Submit resource for approval.
     *
     * @param $id
     *
     * @return mixed
     */
    public static function submit($id)
    {
        return (new static )->newQueryWithoutScope(new ContentPublishingScope())->submit($id);
    }

    /**
     * Reject the submitted resource
     *
     * @param $id
     *
     * @return mixed
     */
    public static function reject($id)
    {
        return (new static )->newQueryWithoutScope(new ContentPublishingScope())->reject($id);
    }

    /**
     * Approve the submitted resource
     *
     * @param $id
     *
     * @return mixed
     */
    public static function approve($id)
    {
        return (new static )->newQueryWithoutScope(new ContentPublishingScope())->approve($id);
    }

    /**
     * Submit a model instance
     *
     * @return mixed
     */
    public function submitContent()
    {
        $new = (new static )->newQueryWithoutScope(new ContentPublishingScope())->submit($this->id);
        return $this->setRawAttributes($new->attributesToArray());
    }

    /**
     * Reject a submitted model instance.
     *
     * @return mixed
     */
    public function rejectContent()
    {
        $new = (new static )->newQueryWithoutScope(new ContentPublishingScope())->reject($this->id);
        return $this->setRawAttributes($new->attributesToArray());
    }

    /**
     * Approve a submitted model instance
     *
     * @return mixed
     */
    public function approveContent()
    {
        $new = (new static )->newQueryWithoutScope(new ContentPublishingScope())->approve($this->id);
        return $this->setRawAttributes($new->attributesToArray());
    }

    /**
     * Publish an approved model instance
     *
     * @return mixed
     */
    public function publishContent($publish_at)
    {
        $new = (new static )->newQueryWithoutScope(new ContentPublishingScope())->publish($this->id, $publish_at);
        return $this->setRawAttributes($new->attributesToArray());
    }

    /**
     * Archive a model instance
     *
     * @return mixed
     */
    public function archiveContent()
    {
        $new = (new static )->newQueryWithoutScope(new ContentPublishingScope())->archive($this->id);
        return $this->setRawAttributes($new->attributesToArray());
    }

    /**
     * Determine if the model instance has been drafted.
     *
     * @return bool
     */
    public function isDrafted()
    {
        return $this->{$this->getStatusColumn()} == Status::DRAFTED;
    }

    /**
     * Determine if the model instance has been submitted to publisher for approval.
     *
     * @return bool
     */
    public function isSubmitted()
    {
        return $this->{$this->getStatusColumn()} == Status::SUBMITTED;
    }

    /**
     * Determine if the model instance has been rejected by a publisher.
     *
     * @return bool
     */
    public function isRejected()
    {
        return $this->{$this->getStatusColumn()} == Status::REJECTED;
    }

    /**
     * Determine if the model instance has been approved by a publisher.
     *
     * @return bool
     */
    public function isApproved()
    {
        return $this->{$this->getStatusColumn()} == Status::APPROVED;
    }

    /**
     * Determine if the model instance has been published.
     *
     * @return bool
     */
    public function isPublished()
    {
        return $this->{$this->getStatusColumn()} == Status::PUBLISHED;
    }

    /**
     * Determine if the model instance has been published.
     *
     * @return bool
     */
    public function isArchived()
    {
        return $this->{$this->getStatusColumn()} == Status::ARCHIVED;
    }

    /**
     * Get the name of the "status" column.
     *
     * @return string
     */
    public function getStatusColumn()
    {
        return defined('static::CONTENT_PUBLISHING_STATUS') ? static::CONTENT_PUBLISHING_STATUS : config('bizly.content-publishing.status_column');
    }

    /**
     * Get the fully qualified "status" column.
     *
     * @return string
     */
    public function getQualifiedStatusColumn()
    {
        return $this->getTable() . '.' . $this->getStatusColumn();
    }

    /**
     * Get the fully qualified "published at" column.
     *
     * @return string
     */
    public function getQualifiedPublishedAtColumn()
    {
        return $this->getTable() . '.' . $this->getPublishedAtColumn();
    }

    /**
     * Get the fully qualified "published by" column.
     *
     * @return string
     */
    public function getQualifiedPublishedByColumn()
    {
        return $this->getTable() . '.' . $this->getPublishedByColumn();
    }

    /**
     * Get the name of the "published at" column.
     *
     * @return string
     */
    public function getPublishedAtColumn()
    {
        return defined('static::PUBLISHED_AT') ? static::PUBLISHED_AT : config('bizly.content-publishing.published_at_column');
    }

    /**
     * Get the name of the "published by" column.
     *
     * @return string
     */
    public function getPublishedByColumn()
    {
        return defined('static::PUBLISHED_BY') ? static::PUBLISHED_BY : config('bizly.content-publishing.published_by_column');
    }

    /**
     * Get the name of the "published at" column.
     * Append "published at" column to the attributes that should be converted to dates.
     *
     * @return array
     */
    public function getDates()
    {
        return array_merge(parent::getDates(), [$this->getPublishedAtColumn()]);
    }
}
