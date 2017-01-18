<?php
/**
 * class ContentPublishingQueryBuilder
 *
 * @package    namespace Bizly\ContentPublishing;
 * @version    1.0.0
 * @author     Tor Miller
 * @copyright  (c) 2015-2017, Bizly, Inc., All Rights Reserved
 */

namespace Bizly\ContentPublishing;

trait ContentPublishingQueryBuilder
{
    /**
     * Get a new query builder that returns only drafted resources.
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public static function drafted()
    {
        return (new static )->newQueryWithoutScope(new ContentPublishingScope())->drafted();
    }

    /**
     * Get a new query builder that returns resources submitted for content approval.
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public static function submitted()
    {
        return (new static )->newQueryWithoutScope(new ContentPublishingScope())->submitted();
    }

    /**
     * Get a new query builder that only includes rejected resources.
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public static function rejected()
    {
        return (new static )->newQueryWithoutScope(new ContentPublishingScope())->rejected();
    }

    /**
     * Get a new query builder that only includes approved resources.
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public static function approved()
    {
        return (new static )->newQueryWithoutScope(new ContentPublishingScope())->approved();
    }

    /**
     * Get a new query builder that only includes published resources.
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public static function published()
    {
        return (new static )->newQueryWithoutScope(new ContentPublishingScope())->published();
    }

    /**
     * Get a new query builder that returns archived resources.
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public static function archived()
    {
        return (new static )->newQueryWithoutScope(new ContentPublishingScope())->archived();
    }

    /**
     * Get a new query builder that includes drafted resources.
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public static function withDrafted()
    {
        return (new static )->newQueryWithoutScope(new ContentPublishingScope())->withDrafted();
    }

    /**
     * Get a new query builder that includes drafted resources.
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public static function withSubmitted()
    {
        return (new static )->newQueryWithoutScope(new ContentPublishingScope())->withSubmitted();
    }

    /**
     * Get a new query builder that includes approved resources.
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public static function withApproved()
    {
        return (new static )->newQueryWithoutScope(new ContentPublishingScope())->withApproved();
    }

    /**
     * Get a new query builder that includes rejected resources.
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public static function withRejected()
    {
        return (new static )->newQueryWithoutScope(new ContentPublishingScope())->withRejected();
    }

    /**
     * Get a new query builder that includes archived resources.
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public static function withArchived()
    {
        return (new static )->newQueryWithoutScope(new ContentPublishingScope())->withArchived();
    }

    /**
     * Get a new query builder that includes all resources.
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public static function withAnyStatus()
    {
        return (new static )->newQueryWithoutScope(new ContentPublishingScope());
    }
}
