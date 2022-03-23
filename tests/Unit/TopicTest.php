<?php

namespace Digitalcubez\EducationModule\Tests\Unit;

use Digitalcubez\EducationModule\Models\Topic;
use Digitalcubez\EducationModule\Tests\TestCase;
use Digitalcubez\EducationModule\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TopicTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function topic()
    {
        $topic = Topic::factory()->create([
            'topic' => 'Test Topic',
        ]);
        $this->assertEquals('Test Topic', $topic->topic);
    }

    /** @test */
    function topic_parent_child_relation()
    {
        $parentTopic = Topic::factory()->create([
            'topic' => 'Parent Topic',
        ]);
        $parentTopic->children()->saveMany([
            Topic::factory()->make(['topic' => 'Child Topic 1']),
            Topic::factory()->make(['topic' => 'Child Topic 2']),
        ]);
        $this->assertEquals(2, $parentTopic->children()->count());
    }

    /** @test */
    function topic_question_relation()
    {
        $topic = Topic::factory()->create([
            'topic' => 'Test Topic',
        ]);
        $question1 = Question::factory()->create([
            'question' => 'Test Question',
        ]);
        $question2 = Question::factory()->create([
            'question' => 'Test Question',
        ]);
        $question3 = Question::factory()->create([
            'question' => 'Test Question',
        ]);
        $topic->questions()->attach($question1);
        $topic->questions()->attach([$question2->id, $question3->id]);
        $this->assertEquals(3, $topic->questions()->count());
    }
}
