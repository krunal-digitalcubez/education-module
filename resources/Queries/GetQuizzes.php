<?php

namespace App\GraphQL\Queries;

use Digitalcubez\EducationModule\Models\Quiz;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Pagination\Paginator;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class GetQuizzes {
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

        $requests = Quiz::active()->paginate(10);


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
