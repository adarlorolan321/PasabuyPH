<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FeedPostResource;
use App\Models\FeedPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FeedController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 20);
        $perPage = $perPage > 0 ? $perPage : 20;

        $feed = FeedPost::query()
            ->with(['user:id,name', 'trip', 'tripRequest'])
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return FeedPostResource::collection($feed)->response();
    }
}

