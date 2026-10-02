<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveWinRequest;
use App\Http\Resources\WinResource;
use App\Models\Win;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class WinController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:255'],
        ]);

        $query = Win::with('category');

        if (isset($filters['search'])) {
            $search = '%'.$filters['search'].'%';

            $query->where(function (Builder $query) use ($search): void {
                $query->whereLike('title', $search)->orWhereLike('description', $search);
            });
        }

        if (isset($filters['category'])) {
            $query->whereHas('category', function (Builder $query) use ($filters): void {
                $query->where('slug', $filters['category']);
            });
        }

        return WinResource::collection($query->orderByDesc('win_date')->orderByDesc('created_at')->get());
    }

    public function store(SaveWinRequest $request): JsonResponse
    {
        $win = Win::create($request->validated());

        return (new WinResource($win->load('category')))->response()->setStatusCode(201);
    }

    public function show(Win $win): WinResource
    {
        return new WinResource($win->load('category'));
    }

    public function update(SaveWinRequest $request, Win $win): WinResource
    {
        $win->update($request->validated());

        return new WinResource($win->load('category'));
    }

    public function destroy(Win $win): Response
    {
        $win->delete();

        return response()->noContent();
    }
}
