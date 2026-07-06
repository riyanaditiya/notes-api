<?php

namespace App\Repositories;

use App\Models\Tag;

class TagRepository
{
    public function getAllByUser(int $userId)
    {
        return Tag::where('user_id', $userId)->get();
    }

    public function findByIdAndUser(int $id, int $userId)
    {
        return Tag::where('id', $id)->where('user_id', $userId)->first();
    }

    public function create(array $data)
    {
        return Tag::create($data);
    }

    public function delete($tag)
    {
        return $tag->delete();
    }

    public function syncNoteTags($note, array $tagIds)
    {
        $note->tags()->sync($tagIds);
        return $note->load('tags');
    }
}