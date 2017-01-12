<?php
return array(
    /*
    |--------------------------------------------------------------------------
    | Status column
    |--------------------------------------------------------------------------
     */
    'status_column' => 'status',

    /*
    |--------------------------------------------------------------------------
    | Published At column
    |--------------------------------------------------------------------------
     */
    'published_at_column' => 'published_at',

    /*
    |--------------------------------------------------------------------------
    | Published By column
    |--------------------------------------------------------------------------
     */
    'published_by_column' => 'published_by',

    /*
    |--------------------------------------------------------------------------
    | Strict Moderation
    |--------------------------------------------------------------------------
    | If Strict Moderation is set to true then the default query will return
    | only approved resources.
    | In other case, all resources except Rejected ones, will returned as well.
     */
    'strict' => true,
);
