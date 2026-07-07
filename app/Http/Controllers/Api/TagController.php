<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTagRequest;
use App\Services\TagService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;


class TagController extends Controller
{
    use ApiResponse;

    public function __construct(protected TagService $tagService)
    {
    }

    #[OA\Get(
        path: "/tags",
        summary: "Get all tags milik user",
        security: [["bearerAuth" => []]],
        tags: ["Tags"],
        responses: [
            new OA\Response(
                response: 200,
                description: "List tags",
                content: new OA\JsonContent(ref: "#/components/schemas/SuccessResponse")
            ),
        ]
    )]
    public function index()
    {
        $tags = $this->tagService->listTags(Auth::id());

        return $this->success($tags, 'Tags retrieved successfully');
    }

    #[OA\Post(
        path: "/tags",
        summary: "Create tag baru",
        security: [["bearerAuth" => []]],
        tags: ["Tags"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "urgent"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Tag berhasil dibuat",
                content: new OA\JsonContent(ref: "#/components/schemas/SuccessResponse")
            ),
        ]
    )]

    public function store(StoreTagRequest $request)
    {
        $tag = $this->tagService->createTag(Auth::id(), $request->validated());

        return $this->success($tag, 'Tag created successfully', 201);
    }

    #[OA\Delete(
        path: "/tags/{id}",
        summary: "Hapus tag",
        security: [["bearerAuth" => []]],
        tags: ["Tags"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Tag berhasil dihapus",
                content: new OA\JsonContent(ref: "#/components/schemas/SuccessResponse")
            ),
        ]
    )]
    
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
