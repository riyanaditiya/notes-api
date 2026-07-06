<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTagRequest;
use App\Services\TagService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TagController extends Controller
{
    use ApiResponse;

    public function __construct(protected TagService $tagService)
    {
    }

    public function index()
    {
        $tags = $this->tagService->listTags(Auth::id());

        return $this->success($tags, 'Tags retrieved successfully');
    }

    public function store(StoreTagRequest $request)
    {
        $tag = $this->tagService->createTag(Auth::id(), $request->validated());

        return $this->success($tag, 'Tag created successfully', 201);
    }

    public function destroy($id)
    {
        $tag = $this->tagService->getTag($id, Auth::id());

        if (! $tag) {
            return $this->error('Tag not found', 404);
        }

        $this->tagService->deleteTag($tag);

        return $this->success((object) [], 'Tag deleted successfully');
    }
}
