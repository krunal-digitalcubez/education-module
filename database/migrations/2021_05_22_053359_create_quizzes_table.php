<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuizzesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Topics Table
        Schema::create('topics', function (Blueprint $table) {
            $table->increments('id');
            $table->string('topic');
            $table->string('slug')->unique();
            $table->unsignedInteger('parent_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('parent_id')->references('id')->on('topics')->onDelete('SET NULL');
        });

        //Question Types Table
        Schema::create('question_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('question_type');
            $table->timestamps();
            $table->softDeletes();
        });

        //Questions Table
        Schema::create('questions', function (Blueprint $table) {
            $table->increments('id');
            $table->text('question');
            $table->text('th_question');
            $table->unsignedInteger('question_type_id');
            $table->text('media_url')->nullable();
            $table->string('media_type')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('correct_reason')->nullable();
            $table->text('th_correct_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('question_type_id')->references('id')->on('question_types')->onDelete('cascade');
        });

        //Quiz, Questions and Topics Relations Table
        Schema::create('topicables', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('topic_id');
            $table->unsignedInteger('topicable_id');
            $table->string('topicable_type');
            $table->timestamps();
            $table->foreign('topic_id')->references('id')->on('topics')->onDelete('cascade');
        });

        //Question Options Table
        Schema::create('question_options', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('question_id');
            $table->string('option')->nullable();
            $table->string('th_option')->nullable();
            $table->string('media_url')->nullable();
            $table->string('media_type')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->boolean('show_option_media')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
        });

        //Quizzes Table
        Schema::create('quizzes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('th_title');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->text('th_description')->nullable();
            $table->text('long_description')->nullable();
            $table->text('th_long_description')->nullable();
            $table->float('total_marks')->default(0); //0 means no marks
            $table->float('pass_marks')->default(0); //0 means no pass marks
            $table->unsignedInteger('max_attempts')->default(0); //0 means unlimited attempts
            $table->tinyInteger('is_published')->default(0); //0 means not published, 1 means published
            $table->string('media_url')->nullable(); //Can be used for cover image, logo etc.
            $table->string('media_type')->nullable(); //image,video,audio etc.
            $table->text('external_media')->nullable(); //maybe we can use youtube url
            $table->unsignedInteger('duration')->default(0); //0 means no duration
            $table->timestamp('valid_from')->default(now());
            $table->timestamp('valid_upto')->nullable(); //null means no expiry
            $table->unsignedInteger('time_between_attempts')->default(0); //0 means no time between attempts, immediately
            $table->boolean('show_slides')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        //Quiz Slides Table
        Schema::create('quiz_slides', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('quiz_id');
            $table->string('title')->nullable();
            $table->string('th_title')->nullable();
            $table->text('long_desc')->nullable();
            $table->text('th_long_desc')->nullable();
            $table->boolean('show_slide')->default(true)->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('quiz_id')->references('id')->on('quiz_slides')->onDelete('cascade');
        });

        //Quiz Questions Table
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('quiz_id');
            $table->unsignedInteger('question_id');
            $table->unsignedFloat('marks')->default(0); //0 means no marks
            $table->unsignedFloat('negative_marks')->default(0); //0 means no negative marks in case of wrong answer
            $table->boolean('is_optional')->default(false); //0 means not optional, 1 means optional
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('quiz_id')->references('id')->on('quizzes')->onDelete('cascade');
            $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
            $table->unique(['quiz_id', 'question_id']);
        });

        //Quiz Attempts Table
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('quiz_id');
            $table->unsignedInteger('participant_id');
            $table->string('participant_type');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('quiz_id')->references('id')->on('quizzes')->onDelete('cascade');
        });

        //Quiz Attempt Answers Table
        Schema::create('quiz_attempt_answers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('quiz_attempt_id');
            $table->unsignedInteger('quiz_question_id');
            $table->unsignedInteger('question_option_id');
            $table->string('answer')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('quiz_attempt_id')->references('id')->on('quiz_attempts')->onDelete('cascade');
            $table->foreign('quiz_question_id')->references('id')->on('quiz_questions')->onDelete('cascade');
            $table->foreign('question_option_id')->references('id')->on('question_options')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('topics', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
        });
        Schema::table('topicables', function (Blueprint $table) {
            $table->dropForeign(['topic_id']);
        });
        Schema::table('question_options', function (Blueprint $table) {
            $table->dropForeign(['question_id']);
        });
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->dropForeign(['quiz_id']);
            $table->dropForeign(['question_id']);
        });
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropForeign(['quiz_id']);
        });
        Schema::table('quiz_attempt_answers', function (Blueprint $table) {
            $table->dropForeign(['quiz_attempt_id']);
            $table->dropForeign(['quiz_question_id']);
            $table->dropForeign(['question_option_id']);
        });
        Schema::drop('quiz_attempt_answers');
        Schema::drop('quiz_attempts');
        Schema::drop('quiz_questions');
        Schema::drop('topicables');
        Schema::drop('question_options');
        Schema::drop('questions');
        Schema::drop('topics');
        Schema::drop('question_types');
        Schema::drop('quizzes');
    }
}
