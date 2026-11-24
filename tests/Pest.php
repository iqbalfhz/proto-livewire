<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Feature tests run on the Laravel TestCase with a fresh database.

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');
