<?php

namespace App\Services;

use App\Repositories\NoteRepository;


class NoteService
{
    public function __construct(protected NoteRepository $noteRepository)
    {
    }

    public function listNotes(int $userId, array $filters, int $perPage = 10)
    {
        return $this->noteRepository->getAllByUser($userId, $filters, $perPage);
    }

    public function getNote(int $id, int $userId)
    {
        return $this->noteRepository->findByIdAndUser($id, $userId);
    }

    public function createNote(int $userId, array $data)
    {
        $data['user_id'] = $userId;
        $data['status'] = $data['status'] ?? 'active';

        return $this->noteRepository->create($data);
    }

    public function updateNote($note, array $data)
    {
        return $this->noteRepository->update($note, $data);
    }

    public function deleteNote($note)
    {
        return $this->noteRepository->delete($note);
    }
}