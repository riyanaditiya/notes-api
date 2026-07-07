<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Services\NoteService;
use App\Services\TagService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class NoteController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected NoteService $noteService,
        protected TagService $tagService
        ){
    }

    #[OA\Get(
        path: "/notes",
        summary: "Get all notes (pagination, search, filter)",
        security: [["bearerAuth" => []]],
        tags: ["Notes"],
        parameters: [
            new OA\Parameter(name: "search", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "status", in: "query", schema: new OA\Schema(type: "string", enum: ["active", "archived"])),
            new OA\Parameter(name: "is_favorite", in: "query", schema: new OA\Schema(type: "boolean")),
            new OA\Parameter(name: "category_id", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer", default: 10)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "List notes",
                content: new OA\JsonContent(ref: "#/components/schemas/SuccessResponse")
            ),
        ]
    )]

    public function index(Request $request)
    {
        $filters = $request->only('search', 'status', 'is_favorite', 'category_id');
        $notes = $this->noteService->listNotes(Auth::id(), $filters, $request->get('per_page', 10));

        return $this->success($notes, 'Notes retrieved successfully');
    }

    #[OA\Post(
        path: "/notes",
        summary: "Create note baru",
        security: [["bearerAuth" => []]],
        tags: ["Notes"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["title", "content"],
                properties: [
                    new OA\Property(property: "title", type: "string", example: "Belajar Laravel"),
                    new OA\Property(property: "content", type: "string", example: "Hari ini belajar JWT authentication"),
                    new OA\Property(property: "status", type: "string", enum: ["active", "archived"]),
                    new OA\Property(property: "category_id", type: "integer", nullable: true),
                    new OA\Property(property: "tags", type: "array", items: new OA\Items(type: "integer"), example: [1, 2]),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Note berhasil dibuat",
                content: new OA\JsonContent(ref: "#/components/schemas/SuccessResponse")
            ),
            new OA\Response(
                response: 422,
                description: "Validasi gagal",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
        ]
    )]

    public function store(StoreNoteRequest $request)
    {
        $note = $this->noteService->createNote(Auth::id(), $request->validated());

        if($request->has('tags')){
            $note = $this->tagService->syncNoteTags($note, $request->tags);
        }

        return $this->success($note->load('tags'), 'Note created successfully', 201);
    }

    #[OA\Get(
        path: "/notes/{id}",
        summary: "Get detail note",
        security: [["bearerAuth" => []]],
        tags: ["Notes"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Detail note",
                content: new OA\JsonContent(ref: "#/components/schemas/SuccessResponse")
            ),
            new OA\Response(
                response: 404,
                description: "Note tidak ditemukan",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
        ]
    )]

    public function show($id)
    {
        $note = $this->noteService->getNote($id, Auth::id());

        if (! $note) {
            return $this->error('Note not found', 404);
        }

        return $this->success($note, 'Note retrieved successfully');
    }

    #[OA\Put(
        path: "/notes/{id}",
        summary: "Update note",
        security: [["bearerAuth" => []]],
        tags: ["Notes"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer")),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "title", type: "string"),
                    new OA\Property(property: "content", type: "string"),
                    new OA\Property(property: "status", type: "string", enum: ["active", "archived"]),
                    new OA\Property(property: "category_id", type: "integer", nullable: true),
                    new OA\Property(property: "tags", type: "array", items: new OA\Items(type: "integer")),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Note berhasil diupdate",
                content: new OA\JsonContent(ref: "#/components/schemas/SuccessResponse")
            ),
        ]
    )]

    public function update(UpdateNoteRequest $request, $id)
    {
        $note = $this->noteService->getNote($id, Auth::id());

        if (! $note) {
            return $this->error('Note not found', 404);
        }

        $note = $this->noteService->updateNote($note, $request->validated());

        if($request->has('tags')){
            $note = $this->tagService->syncNoteTags($note, $request->tags);
        }

        return $this->success($note->load('tags'), 'Note updated successfully');
    }

    #[OA\Delete(
        path: "/notes/{id}",
        summary: "Soft delete note",
        security: [["bearerAuth" => []]],
        tags: ["Notes"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Note berhasil dihapus",
                content: new OA\JsonContent(ref: "#/components/schemas/SuccessResponse")
            ),
        ]
    )]

    public function destroy($id)
    {
        $note = $this->noteService->getNote($id, Auth::id());

        if (! $note) {
            return $this->error('Note not found', 404);
        }

        $this->noteService->deleteNote($note);

        return $this->success([], 'Note deleted successfully');
    }

    #[OA\Get(
        path: "/notes-trashed",
        summary: "Get list note yang sudah dihapus",
        security: [["bearerAuth" => []]],
        tags: ["Notes - Soft Delete"],
        responses: [
            new OA\Response(
                response: 200,
                description: "List trashed notes",
                content: new OA\JsonContent(ref: "#/components/schemas/SuccessResponse")
            ),
        ]
    )]
    public function trashed(Request $request)
    {
        $notes = $this->noteService->listTrashedNotes(Auth::id(), $request->get('per_page', 10));

        return $this->success($notes, 'Trashed notes retrieved successfully');
    }

    #[OA\Patch(
        path: "/notes/{id}/restore",
        summary: "Restore note dari trash",
        security: [["bearerAuth" => []]],
        tags: ["Notes - Soft Delete"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Note berhasil direstore",
                content: new OA\JsonContent(ref: "#/components/schemas/SuccessResponse")
            ),
        ]
    )]

    public function restore($id)
    {
        $note = $this->noteService->getTrashedNote($id, Auth::id());

        if (! $note) {
            return $this->error('Note not found', 404);
        }

        $note = $this->noteService->restoreNote($note);

        return $this->success($note, 'Note restored successfully');
    }

    #[OA\Delete(
        path: "/notes/{id}/force-delete",
        summary: "Hapus note secara permanen",
        security: [["bearerAuth" => []]],
        tags: ["Notes - Soft Delete"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Note berhasil dihapus permanen",
                content: new OA\JsonContent(ref: "#/components/schemas/SuccessResponse")
            ),
        ]
    )]

    public function forceDelete($id)
    {
        $note = $this->noteService->getTrashedNote($id, Auth::id());

        if (! $note) {
            return $this->error('Note not found', 404);
        }

        $this->noteService->forceDeleteNote($note);

        return $this->success([], 'Note permanently deleted');
    }

    #[OA\Patch(
        path: "/notes/{id}/favorite",
        summary: "Toggle status favorite pada note",
        security: [["bearerAuth" => []]],
        tags: ["Notes - Favorite"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Status favorite berhasil diubah",
                content: new OA\JsonContent(ref: "#/components/schemas/SuccessResponse")
            ),
        ]
    )]
    
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
