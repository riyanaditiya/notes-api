<?php

namespace App\Repositories;

use App\Models\Note;

class NoteRepository
{
    public function getAllByUser(int $userId, array $filters, int $perPage)
    {
        $query = Note::with(['category', 'tags'])->where('user_id', $userId);

        if (! empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['is_favorite'])) {  
            $query->where('is_favorite', filter_var($filters['is_favorite'], FILTER_VALIDATE_BOOLEAN));
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function findByIdAndUser(int $id, int $userId)
    {
        return Note::with(['category', 'tags'])->where('id', $id)->where('user_id', $userId)->first();
    }

    public function create(array $data)
    {
        return Note::create($data);
    }

    public function update($note, array $data)
    {
        $note->update($data);
        return $note;
    }

    public function delete($note)
    {
        return $note->delete();
    }

    public function findTrashedByIdAndUser(int $id, int $userId)
    {
        return Note::onlyTrashed()
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

     public function getAllTrashedByUser(int $userId, int $perPage)
    {
        return Note::onlyTrashed()
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage);
    }

    public function restore($note)
    {
        $note->restore();
        return $note;
    }

    public function forceDelete($note)
    {
        return $note->forceDelete();
    }
}