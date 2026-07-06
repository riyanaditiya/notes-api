<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Services\CategoryService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    use ApiResponse;

    public function __construct(protected CategoryService $categoryService)
    {
    }

    public function index()
    {
        $categories = $this->categoryService->listCategories(Auth::id());

        return $this->success($categories, 'Categories retrieved successfully');
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = $this->categoryService->createCategory(Auth::id(), $request->validated());

        return $this->success($category, 'Category created successfully', 201);
    }

    public function show($id)
    {
        $category = $this->categoryService->getCategory($id, Auth::id());

        if (! $category) {
            return $this->error('Category not found', 404);
        }

        return $this->success($category, 'Category retrieved successfully');
    }

    public function update(StoreCategoryRequest $request, $id)
    {
        $category = $this->categoryService->getCategory($id, Auth::id());

        if (! $category) {
            return $this->error('Category not found', 404);
        }

        $category = $this->categoryService->updateCategory($category, $request->validated());

        return $this->success($category, 'Category updated successfully');
    }

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
