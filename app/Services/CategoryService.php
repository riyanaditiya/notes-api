<?php

namespace App\Services;

use App\Repositories\CategoryRepository;

class CategoryService
{
    public function __construct(protected CategoryRepository $categoryRepository)
    {
    }

    public function listCategories(int $userId)
    {
        return $this->categoryRepository->getAllByUser($userId);
    }

    public function getCategory(int $id, int $userId)
    {
        return $this->categoryRepository->findByIdAndUser($id, $userId);
    }

    public function createCategory(int $userId, array $data)
    {
        $data['user_id'] = $userId;
        return $this->categoryRepository->create($data);
    }

    public function updateCategory($category, array $data)
    {
        return $this->categoryRepository->update($category, $data);
    }

    public function deleteCategory($category)
    {
        return $this->categoryRepository->delete($category);
    }
}