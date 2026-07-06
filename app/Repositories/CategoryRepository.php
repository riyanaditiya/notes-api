<?php 

namespace App\Repositories;

use App\Models\Category;


class CategoryRepository
{
    public function getAllByUser(int $userId)
    {
        return Category::where('user_id', $userId)->get();
    }

    public function findByIdAndUser(int $id, int $userId)
    {
        return Category::where('id', $id)->where('user_id', $userId)->first();
    }

    public function create(array $data)
    {
        return Category::create($data);
    }

    public function update($category, array $data)
    {
        $category->update($data);
        return $category;
    }

    public function delete($category)
    {
        return $category->delete();
    }
}
