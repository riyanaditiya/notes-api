<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Services\NoteService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoteController extends Controller
{
    use ApiResponse;

    public function __construct(protected NoteService $noteService)
    {
    }

    public function index(Request $request)
    {
        $filters = $request->only('search', 'status', 'is_favorite');
        $notes = $this->noteService->listNotes(Auth::id(), $filters, $request->get('per_page', 10));

        return $this->success($notes, 'Notes retrieved successfully');
    }

    public function store(StoreNoteRequest $request)
    {
        $note = $this->noteService->createNote(Auth::id(), $request->validated());

        return $this->success($note, 'Note created successfully', 201);
    }

    public function show($id)
    {
        $note = $this->noteService->getNote($id, Auth::id());

        if (! $note) {
            return $this->error('Note not found', 404);
        }

        return $this->success($note, 'Note retrieved successfully');
    }

    public function update(UpdateNoteRequest $request, $id)
    {
        $note = $this->noteService->getNote($id, Auth::id());

        if (! $note) {
            return $this->error('Note not found', 404);
        }

        $note = $this->noteService->updateNote($note, $request->validated());

        return $this->success($note, 'Note updated successfully');
    }

    public function destroy($id)
    {
        $note = $this->noteService->getNote($id, Auth::id());

        if (! $note) {
            return $this->error('Note not found', 404);
        }

        $this->noteService->deleteNote($note);

        return $this->success([], 'Note deleted successfully');
    }

    public function trashed(Request $request)
    {
        $notes = $this->noteService->listTrashedNotes(Auth::id(), $request->get('per_page', 10));

        return $this->success($notes, 'Trashed notes retrieved successfully');
    }

    public function restore($id)
    {
        $note = $this->noteService->getTrashedNote($id, Auth::id());

        if (! $note) {
            return $this->error('Note not found', 404);
        }

        $note = $this->noteService->restoreNote($note);

        return $this->success($note, 'Note restored successfully');
    }

    public function forceDelete($id)
    {
        $note = $this->noteService->getTrashedNote($id, Auth::id());

        if (! $note) {
            return $this->error('Note not found', 404);
        }

        $this->noteService->forceDeleteNote($note);

        return $this->success([], 'Note permanently deleted');
    }

    public function toggleFavorite($id)
    {
        $note = $this->noteService->getNote($id, Auth::id());

        if (! $note) {
            return $this->error('Note not found', 404);
        }

        $note = $this->noteService->toggleFavorite($note);

        return $this->success($note, 'Favorite status updated successfully');
    }
}
