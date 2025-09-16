<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Binary Balancer Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the binary balancer service including reward amounts,
    | quotas, and other settings.
    |
    */

    'reward_amount' => env('BINARY_BALANCER_REWARD_AMOUNT', 100),

    'product_every_n_rewards' => env('BINARY_BALANCER_PRODUCT_EVERY_N', 5),

    'max_upline_depth' => env('BINARY_BALANCER_MAX_UPLINE_DEPTH', 1000),

    'default_level_index' => env('BINARY_BALANCER_DEFAULT_LEVEL_INDEX', 1),

    /*
    |--------------------------------------------------------------------------
    | Volume per Recruit
    |--------------------------------------------------------------------------
    |
    | The volume added to the binary tree when a new user is recruited.
    |
    */
    'volume_per_recruit' => env('BINARY_BALANCER_VOLUME_PER_RECRUIT', 1),
];