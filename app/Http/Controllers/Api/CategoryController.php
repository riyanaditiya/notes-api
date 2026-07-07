<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Services\CategoryService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class CategoryController extends Controller
{
    use ApiResponse;

    public function __construct(protected CategoryService $categoryService)
    {
    }

    #[OA\Get(
        path: "/categories",
        summary: "Get all categories milik user",
        security: [["bearerAuth" => []]],
        tags: ["Categories"],
        responses: [
            new OA\Response(
                response: 200,
                description: "List categories",
                content: new OA\JsonContent(ref: "#/components/schemas/SuccessResponse")
            ),
        ]
    )]

    public function index()
    {
        $categories = $this->categoryService->listCategories(Auth::id());

        return $this->success($categories, 'Categories retrieved successfully');
    }

    #[OA\Post(
        path: "/categories",
        summary: "Create category baru",
        security: [["bearerAuth" => []]],
        tags: ["Categories"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Pekerjaan"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Category berhasil dibuat",
                content: new OA\JsonContent(ref: "#/components/schemas/SuccessResponse")
            ),
        ]
    )]

    public function store(StoreCategoryRequest $request)
    {
        $category = $this->categoryService->createCategory(Auth::id(), $request->validated());

        return $this->success($category, 'Category created successfully', 201);
    }

    #[OA\Get(
        path: "/categories/{id}",
        summary: "Get detail category",
        security: [["bearerAuth" => []]],
        tags: ["Categories"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Detail category",
                content: new OA\JsonContent(ref: "#/components/schemas/SuccessResponse")
            ),
        ]
    )]

    public function show($id)
    {
        $category = $this->categoryService->getCategory($id, Auth::id());

        if (! $category) {
            return $this->error('Category not found', 404);
        }

        return $this->success($category, 'Category retrieved successfully');
    }

    #[OA\Put(
        path: "/categories/{id}",
        summary: "Update category",
        security: [["bearerAuth" => []]],
        tags: ["Categories"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer")),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Category berhasil diupdate",
                content: new OA\JsonContent(ref: "#/components/schemas/SuccessResponse")
            ),
        ]
    )]

    public function update(StoreCategoryRequest $request, $id)
    {
        $category = $this->categoryService->getCategory($id, Auth::id());

        if (! $category) {
            return $this->error('Category not found', 404);
        }

        $category = $this->categoryService->updateCategory($category, $request->validated());

        return $this->success($category, 'Category updated successfully');
    }

   #[OA\Delete(
        path: "/categories/{id}",
        summary: "Hapus category",
        security: [["bearerAuth" => []]],
        tags: ["Categories"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Category berhasil dihapus",
                content: new OA\JsonContent(ref: "#/components/schemas/SuccessResponse")
            ),
        ]
    )]
    
    public function destroy($id)
    {
        $category = $this->categoryService->getCategory($id, Auth::id());

        if (! $category) {
            return $this->error('Category not found', 404);
        }

        $this->categoryService->deleteCategory($category);

        return $this->success([], 'Category deleted successfully');
    }
}
