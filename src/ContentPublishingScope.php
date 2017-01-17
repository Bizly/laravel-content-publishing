<?php

namespace Bizly\ContentPublishing;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ContentPublishingScope implements Scope
{
    /**
     * All of the extensions to be added to the builder.
     *
     * @var array
     */
    protected $extensions = [
        'WithDrafted',
        'WithSubmitted',
        'WithRejected',
        'WithApproved',
        'WithArchived',
        'WithAnyStatus',
        'Drafted',
        'Submitted',
        'Rejected',
        'Approved',
        'Archived',
        'Submit',
        'Reject',
        'Approve',
        'Publish',
        'Archive',
    ];

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @param  \Illuminate\Database\Eloquent\Model $model
     *
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        //Only return published resources by default:
        $builder->where($model->getQualifiedStatusColumn(), Status::PUBLISHED);

        $this->extend($builder);
    }

    /**
     * Remove the scope from the given Eloquent query builder.
     *
     * (This method exists in order to achieve compatibility with laravel 5.1.*)
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function remove(Builder $builder, Model $model)
    {
        $column = $model->getQualifiedStatusColumn();
        $query = $builder->getQuery();

        $bindingKey = 0;

        foreach ((array) $query->wheres as $key => $where) {
            if ($this->isContentPublishingConstraint($where, $column)) {
                $this->removeWhere($query, $key);

                // Here SoftDeletingScope simply removes the where
                // but since we use Basic where (not Null type)
                // we need to get rid of the binding as well
                $this->removeBinding($query, $bindingKey);
            }

            // Check if where is either NULL or NOT NULL type,
            // if that's the case, don't increment the key
            // since there is no binding for these types
            if (!in_array($where['type'], ['Null', 'NotNull'])) {
                $bindingKey++;
            }

        }

    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return void
     */
    public function extend(Builder $builder)
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }

        // $builder->onDelete(function (Builder $builder) {
        //     $column = $builder->getModel()->getPublishedAtColumn();

        //     return $builder->update([
        //         $column => $builder->getModel()->freshTimestampString(),
        //     ]);
        // });
    }

    /**
     * Add the with-drafted extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return void
     */
    protected function addWithDrafted(Builder $builder)
    {
        $builder->macro('withDrafted', function (Builder $builder) {
            $this->remove($builder, $builder->getModel());

            return $builder->whereIN($this->getStatusColumn($builder), [Status::PUBLISHED, Status::DRAFTED]);
        });
    }

    /**
     * Add the with-submitted extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return void
     */
    protected function addWithSubmitted(Builder $builder)
    {
        $builder->macro('withSubmitted', function (Builder $builder) {
            $this->remove($builder, $builder->getModel());

            return $builder->whereIN($this->getStatusColumn($builder), [Status::PUBLISHED, Status::SUBMITTED]);
        });
    }

    /**
     * Add the with-rejected extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return void
     */
    protected function addWithRejected(Builder $builder)
    {
        $builder->macro('withRejected', function (Builder $builder) {
            $this->remove($builder, $builder->getModel());

            return $builder->whereIN($this->getStatusColumn($builder),
                [Status::PUBLISHED, Status::REJECTED]);
        });
    }

    /**
     * Add the with-approved extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return void
     */
    protected function addWithApproved(Builder $builder)
    {
        $builder->macro('withApproved', function (Builder $builder) {
            $this->remove($builder, $builder->getModel());

            return $builder->whereIN($this->getStatusColumn($builder), [Status::PUBLISHED, Status::APPROVED]);
        });
    }

    /**
     * Add the with-archived extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return void
     */
    protected function addWithArchived(Builder $builder)
    {
        $builder->macro('withArchived', function (Builder $builder) {
            $this->remove($builder, $builder->getModel());

            return $builder->whereIN($this->getStatusColumn($builder), [Status::PUBLISHED, Status::ARCHIVED]);
        });
    }

    /**
     * Add the with-any-status extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return void
     */
    protected function addWithAnyStatus(Builder $builder)
    {
        $builder->macro('withAnyStatus', function (Builder $builder) {
            $this->remove($builder, $builder->getModel());
            return $builder;
        });
    }

    /**
     * Add the Drafted extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return void
     */
    protected function addDrafted(Builder $builder)
    {
        $builder->macro('drafted', function (Builder $builder) {
            $model = $builder->getModel();

            $this->remove($builder, $model);

            $builder->where($model->getQualifiedStatusColumn(), Status::DRAFTED);

            return $builder;
        });
    }

    /**
     * Add the Submitted extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return void
     */
    protected function addSubmitted(Builder $builder)
    {
        $builder->macro('submitted', function (Builder $builder) {
            $model = $builder->getModel();

            $this->remove($builder, $model);

            $builder->where($model->getQualifiedStatusColumn(), Status::SUBMITTED);

            return $builder;
        });
    }

    /**
     * Add the Rejected extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return void
     */
    protected function addRejected(Builder $builder)
    {
        $builder->macro('rejected', function (Builder $builder) {
            $model = $builder->getModel();

            $this->remove($builder, $model);

            $builder->where($model->getQualifiedStatusColumn(), Status::REJECTED);

            return $builder;
        });
    }

    /**
     * Add the Approved extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return void
     */
    protected function addApproved(Builder $builder)
    {
        $builder->macro('approved', function (Builder $builder) {
            $model = $builder->getModel();

            $this->remove($builder, $model);

            $builder->where($model->getQualifiedStatusColumn(), Status::APPROVED);

            return $builder;
        });
    }

    /**
     * Add the Archived extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return void
     */
    protected function addArchived(Builder $builder)
    {
        $builder->macro('archived', function (Builder $builder) {
            $model = $builder->getModel();

            $this->remove($builder, $model);

            $builder->where($model->getQualifiedStatusColumn(), Status::ARCHIVED);

            return $builder;
        });
    }

    /**
     * Add the Submit extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return void
     */
    protected function addSubmit(Builder $builder)
    {
        $builder->macro('submit', function (Builder $builder, $id = null) {
            $builder->withAnyStatus();
            return $this->updateContentPublishingStatus($builder, $id, Status::SUBMITTED);
        });
    }

    /**
     * Add the Reject extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return void
     */
    protected function addReject(Builder $builder)
    {
        $builder->macro('reject', function (Builder $builder, $id = null) {
            //Can only reject submitted resources.
            $builder->submitted();
            return $this->updateContentPublishingStatus($builder, $id, Status::REJECTED);

        });
    }

    /**
     * Add the Approve extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return void
     */
    protected function addApprove(Builder $builder)
    {
        $builder->macro('approve', function (Builder $builder, $id = null) {
            //Can only approve submitted resources.
            $builder->submitted();
            return $this->updateContentPublishingStatus($builder, $id, Status::APPROVED);
        });
    }

    /**
     * Add the Publish extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return void
     */
    protected function addPublish(Builder $builder)
    {
        $builder->macro('publish', function (Builder $builder, $id = null) {
            //Can only publish approved resources.
            $builder->approved();
            return $this->updateContentPublishingStatus($builder, $id, Status::PUBLISHED);
        });
    }

    /**
     * Add the Archive extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return void
     */
    protected function addArchive(Builder $builder)
    {
        $builder->macro('archive', function (Builder $builder, $id = null) {
            $builder->withAnyStatus();
            return $this->updateContentPublishingStatus($builder, $id, Status::ARCHIVED);
        });
    }

    /**
     * Get the status column for the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return string
     */
    protected function getStatusColumn(Builder $builder)
    {
        if (count($builder->getQuery()->joins) > 0) {
            return $builder->getModel()->getQualifiedStatusColumn();
        } else {
            return $builder->getModel()->getStatusColumn();
        }
    }

    /**
     * Remove scope constraint from the query.
     *
     * @param $query
     * @param  int $key
     *
     * @internal param \Illuminate\Database\Query\Builder $builder
     */
    protected function removeWhere($query, $key)
    {
        unset($query->wheres[$key]);

        $query->wheres = array_values($query->wheres);
    }

    /**
     * Remove scope constraint from the query.
     *
     * @param $query
     * @param  int $key
     *
     * @internal param \Illuminate\Database\Query\Builder $builder
     */
    protected function removeBinding($query, $key)
    {
        $bindings = $query->getRawBindings()['where'];

        unset($bindings[$key]);

        $query->setBindings($bindings);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param $id
     * @param $status
     *
     * @return bool|int
     */
    private function updateContentPublishingStatus(Builder $builder, $id, $status, $date)
    {

        //If $id parameter is passed then update the specified model
        if ($id) {
            $model = $builder->find($id);
            $model->{$model->getStatusColumn()} = $status;

            // Only set published_at and published_by if the content is being published.
            if ($status == Status::PUBLISHED) {
                $model->{$model->getPublishedAtColumn()} = $date ?? Carbon::now();
                $model->{$model->getPublishedByColumn()} = \Auth::user()->getKey();
            }

            $model->save();
            return $model;
        }

        // For multiple entities:
        $update = [
            $builder->getModel()->getStatusColumn() => $status,
        ];

        // Only set published_at and published_by if the content is being published.
        if ($status == Status::PUBLISHED) {
            $update[$builder->getModel()->getPublishedAtColumn()] = $date ?? Carbon::now();
            $update[$builder->getModel()->getPublishedByColumn()] = \Auth::user()->getKey();
        }

        return $builder->update($update);
    }

    /**
     * Determine if the given where clause is a content publishing constraint.
     *
     * @param  array $where
     * @param  string $column
     * @return bool
     */
    protected function isContentPublishingConstraint(array $where, $column)
    {
        return $where['column'] == $column;
    }
}
