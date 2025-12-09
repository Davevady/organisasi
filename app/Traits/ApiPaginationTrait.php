<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait ApiPaginationTrait
{
    /**
     * Apply pagination based on request body
     *
     * @param Builder|Relation $query
     * @param Request $request
     * @return array
     */
    protected function applyPagination(Builder|Relation $query, Request $request): array
    {
        $paginate = $request->input('paginate', true);

        // If paginate is false, return all data
        if ($paginate === false || $paginate === 'false') {
            return [
                'data' => $query->get(),
                'paginate' => false,
            ];
        }

        // Get pagination parameters from request body
        $perPage = $request->input('per_page', 15);
        $page = $request->input('page', 1);

        // Apply pagination
        $paginated = $query->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $paginated->items(),
            'paginate' => true,
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
                'from' => $paginated->firstItem(),
                'to' => $paginated->lastItem(),
            ],
        ];
    }

    /**
     * Return paginated JSON response
     *
     * @param Builder|Relation $query
     * @param Request $request
     * @param string $message
     * @param int $status
     * @return JsonResponse
     */
    protected function paginatedResponse(Builder|Relation $query, Request $request, string $message = 'Data retrieved successfully', int $status = 200): JsonResponse
    {
        $result = $this->applyPagination($query, $request);

        $response = [
            'message' => $message,
            'data' => $result['data'],
        ];

        if ($result['paginate']) {
            $response['pagination'] = $result['pagination'];
        }

        return response()->json($response, $status);
    }
}
