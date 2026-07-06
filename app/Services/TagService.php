<?php

namespace App\Services;

use App\Repositories\TagRepository;

class TagService
{
    public function __construct(protected TagRepository $tagRepository)
    {
    }

    public function listTags(int $userId)
    {
        return $this->tagRepository->getAllByUser($userId);
    }

    public function getTag(int $id, int $userId)
    {
        return $this->tagRepository->findByIdAndUser($id, $userId);
    }

    public function createTag(int $userId, array $data)
    {
        $data['user_id'] = $userId;
        return $this->tagRepository->create($data);
    }

    public function deleteTag($tag)
    {
        return $this->tagRepository->delete($tag);
    }

    public function syncNoteTags($note, array $tagIds)
    {
        return $this->tagRepository->syncNoteTags($note, $tagIds);
    }
}