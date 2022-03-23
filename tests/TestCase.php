<?php

namespace Digitalcubez\EducationModule\Tests;

use Digitalcubez\EducationModule\EducationModuleServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        // additional setup
    }

    protected function getPackageProviders($app)
    {
        return [
            EducationModuleServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // perform environment setup
        // import the CreatePostsTable class from the migration
        // include_once __DIR__ . '/../database/migrations/2021_05_22_053359_create_quizzes_table.php';
        include_once __DIR__ . '/migrations/create_authors_table.php';
        // run the up() method of that migration class
        // (new \CreateQuizzesTable)->up();
        (new \CreateAuthorsTable)->up();
    }
}
