<?php

namespace App\Http\Controllers;

use App\Http\Controllers\CrudController;
use App\Models\MediaPost;
use App\Models\MediaPostAssignment;
use App\Models\MediaPostComment;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MediaPostController extends CrudController
{

  protected function getModel(): string
  {
    return MediaPost::class;
  }

  protected function getTable(): string
  {
    return 'media_posts';
  }

  protected function getModelClass(): string
  {
    return MediaPost::class;
  }

  public function upsertAssignee(Request $request, $postId)
  {
    try {
      $user = $request->user();

      $post = MediaPost::with(['assignments', 'assignments.creator'])->find($postId);

      if (! $post) {
        return response()->json([
          'success' => false,
          'errors'  => [__('media_posts.post_not_found')],
        ]);
      }

      if ($post->project->client->user_id !== $user->id && ! $user->hasPermission('media_posts', 'update')) {
        return response()->json([
          'success' => false,
          'errors'  => [__('common.permission_denied')],
        ]);
      }

      DB::transaction(function () use ($post, $request) {
        MediaPostAssignment::updateOrCreate(
          ['post_id' => $post->id, 'creator_id' => $request->creator_id],
          ['role' => $request->role]
        );
      });

      return response()->json([
        'success' => true,
        'message' => __('media_posts.creator_assigned'),
      ]);
    } catch (\Throwable $e) {
      Log::error('Error in MediaPostController.upsertAssignee: ' . $e->getMessage());
      Log::error($e->getTraceAsString());
      return response()->json([
        'success' => false,
        'errors'  => [__('common.unexpected_error')],
      ]);
    }
  }

  public function deleteAssignee(Request $request, $postId)
  {
    try {
      $user = $request->user();
      $post = MediaPost::with(['assignments', 'assignments.creator'])->find($postId);
      $creatorId = $request->creator_id;

      if (! $post) {
        return response()->json([
          'success' => false,
          'errors' => [__('media_posts.post_not_found')]
        ], 404);
      }

      if ($post->project->client->user_id !== $user->id && ! $user->hasPermission('media_posts', 'update')) {
        return response()->json([
          'success' => false,
          'errors' => [__('common.permission_denied')]
        ], 403);
      }

      $deleted = DB::transaction(function () use ($post, $creatorId) {
        return MediaPostAssignment::where('post_id', $post->id)
          ->where('creator_id', $creatorId)
          ->delete();
      });

      if (! $deleted) {
        return response()->json([
          'success' => false,
          'errors' => [__('media_posts.assignment_not_found')]
        ], 404);
      }

      return response()->json([
        'success' => true,
        'message' => __('media_posts.creator_unassigned'),
      ]);
    } catch (\Throwable $e) {
      Log::error('Error in MediaPostController.deleteAssignee: ' . $e->getMessage());
      Log::error($e->getTraceAsString());
      return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]], 500);
    }
  }

  public function addComment(Request $request, $postId)
  {
    try {
      $user = $request->user();

      $data = $request->validate(MediaPostComment::rules());

      $post = MediaPost::with(['project.client.user', 'assignments.creator.user'])
        ->find($postId);

      if (! $post) {
        return response()->json([
          'success' => false,
          'errors'  => [__('media_posts.post_not_found')],
        ], 404);
      }

      $isClient = $post->project?->client?->user_id === $user->id;
      $isAssignedCreator = $post->assignments
        ->pluck('creator.user.id')
        ->filter()
        ->contains($user->id);

      if (! $isClient && ! $isAssignedCreator && ! $user->hasPermission('media_posts', 'update')) {
        return response()->json([
          'success' => false,
          'errors'  => [__('common.permission_denied')],
        ], 403);
      }

      $comment = DB::transaction(function () use ($post, $data) {
        return $post->comments()->create($data);
      });

      return response()->json([
        'success' => true,
        'data'    => ['item' => $comment],
        'message' => __('media_posts.comment_added'),
      ]);
    } catch (\Throwable $e) {
      Log::error('Error in MediaPostController.addComment: ' . $e->getMessage());
      Log::error($e->getTraceAsString());
      return response()->json([
        'success' => false,
        'errors'  => [__('common.unexpected_error')],
      ], 500);
    }
  }

  public function afterReadOne($mediaPost, Request $request)
  {
    if ($mediaPost->relationLoaded('project') && $mediaPost->project) {
      $mediaPost->project->loadMissing('creators.creator.user');
    }
  }

  public function afterReadAll($mediaPosts)
  {
    foreach ($mediaPosts as $mediaPost) {
      if ($mediaPost->relationLoaded('project') && $mediaPost->project) {
        $mediaPost->project->loadMissing('creators.creator.user');
      }
    }
  }
}
