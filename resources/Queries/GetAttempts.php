<?php

namespace App\GraphQL\Queries;

use Digitalcubez\EducationModule\Models\QuizAttempt;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
class GetAttempts {
    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) {

        $search  = isset($args["search"]) ? $args["search"] : "";
        $filters = isset($args["filters"]) ? $args["filters"] : json_decode('[]');
        $sort    = isset($args["sort"]) ? json_encode($args["sort"]) : json_encode('{}');
        $page    = isset($args["page"]) && $args["page"] >= 1 ? $args["page"] : 1;

        Paginator::currentPageResolver(function () use ($page) {
            return $page;
        });

        $requests = QuizAttempt::where('participant_id', Auth::guard('worker')->user()->id)->paginate(10);


        return [
            "items"     => $requests,
            "page_info" => [
                "total"       => $requests->total(),
                "currentPage" => $requests->currentPage(),
                "perPage"     => $requests->perPage(),
                "hasMore"     => $requests->hasPages(),
            ],
            "q"         => $search,
            "filters"   => $filters,
            "sort"      => $sort,
        ];
    }
}
