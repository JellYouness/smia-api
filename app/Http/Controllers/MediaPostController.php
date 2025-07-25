<?php

namespace App\Http\Controllers;

use App\Enums\CREATOR_PROJECT_PERMISSION;
use App\Enums\MEDIA_POST_REVIEW_DECISION;
use App\Enums\MEDIA_POST_STATUS;
use App\Enums\VERSION_STATUS;
use App\Http\Controllers\CrudController;
use App\Models\MediaPost;
use App\Models\MediaPostAsset;
use App\Models\MediaPostAssignment;
use App\Models\MediaPostComment;
use App\Models\MediaPostReview;
use App\Models\MediaPostVersion;
use App\Models\Upload;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

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

  public function addAssetToMediaPost(Request $request, $postId)
  {
    try {
      $data   = $request->validate(MediaPostAsset::rules());
      $user   = $request->user();
      $post   = MediaPost::with('project.client')->find($postId);

      if (!$post) {
        return response()->json([
          'success' => false,
          'errors'  => [__('media_posts.post_not_found')],
        ], 404);
      }

      // ■ permission check
      $isOwner   = $post->project?->client?->user_id === $user->id;
      $canUpdate = $user->hasPermission('media_posts', 'update');

      // Check if user is assigned as editor to this media post
      $isAssignedEditor = false;
      if ($user->creator) {
        $isAssignedEditor = MediaPostAssignment::where('post_id', $postId)
          ->where('creator_id', $user->creator->id)
          ->where('role', CREATOR_PROJECT_PERMISSION::EDITOR)
          ->exists();
      }

      if (!$isOwner && !$canUpdate && !$isAssignedEditor) {
        return response()->json([
          'success' => false,
          'errors'  => [__('common.permission_denied')],
        ], 403);
      }

      // ■ create asset row
      $asset = DB::transaction(function () use ($post, $data, $user) {
        $data['uploaded_by'] = $user->id;
        return $post->assets()->create($data);
      });

      return response()->json([
        'success' => true,
        'data'    => ['item' => $asset],
        'message' => __('media_posts.asset_added'),
      ]);
    } catch (\Throwable $e) {
      Log::error('Error in MediaPostController.addAssetToMediaPost: ' . $e->getMessage());
      Log::error($e->getTraceAsString());
      return response()->json([
        'success' => false,
        'errors'  => [__('common.unexpected_error')],
      ], 500);
    }
  }

  public function readAllAssetsByPost(Request $request, $postId)
  {
    try {
      $user = $request->user();
      $post = MediaPost::with('project.client')->find($postId);

      if (!$post) {
        return response()->json([
          'success' => false,
          'errors'  => [__('media_posts.post_not_found')],
        ], 404);
      }

      $isClient = $post->project?->client?->user_id === $user->id;

      $query = MediaPostAsset::where('post_id', $postId);

      if ($isClient && !$user->hasPermission('media_posts', 'update')) {
        // hide draft uploads → version_id NULL **and** is_reference = FALSE
        $query->where(function ($q) {
          $q->whereNotNull('version_id')
            ->orWhere('is_reference', true);
        });
      }

      $assets = $query->with(['uploader', 'upload'])->get();

      return response()->json([
        'success' => true,
        'data'    => ['items' => $assets],
      ]);
    } catch (\Throwable $e) {
      Log::error('Error in MediaPostController.readAllAssetsByPost: ' . $e->getMessage());
      Log::error($e->getTraceAsString());
      return response()->json([
        'success' => false,
        'errors'  => [__('common.unexpected_error')],
      ], 500);
    }
  }

  public function deleteAssetFromMediaPost(Request $request, $postId)
  {
    try {
      $user   = $request->user();
      $assetId = $request->input('asset_id');

      $asset = MediaPostAsset::with('post.project.client')->find($assetId);

      if (!$asset || $asset->post_id != $postId) {
        return response()->json([
          'success' => false,
          'errors'  => [__('media_posts.asset_not_found')],
        ], 404);
      }

      $isOwner   = $asset->post->project?->client?->user_id === $user->id;
      $canUpdate = $user->hasPermission('media_posts', 'update');

      // Additional creator/editor/uploader check
      $creatorId = $user->creator?->id;
      $isEditor = false;
      if ($creatorId) {
        $isEditor = \App\Models\MediaPostAssignment::where('post_id', $postId)
          ->where('creator_id', $creatorId)
          ->where('role', \App\Enums\CREATOR_PROJECT_PERMISSION::EDITOR->value)
          ->exists();
      }
      $isUploader = $asset->uploaded_by == $user->id;

      if (!($isOwner || $canUpdate || ($isEditor && $isUploader))) {
        return response()->json([
          'success' => false,
          'errors'  => [__('common.permission_denied')],
        ], 403);
      }

      // Never delete a file that belongs to a frozen version
      if ($asset->version_id && $asset->version->status !== 'in_review') {
        return response()->json([
          'success' => false,
          'errors'  => [__('media_posts.cannot_delete_version_file')],
        ], 400);
      }

      DB::transaction(function () use ($asset) {
        Upload::where('id', $asset->upload_id)->delete();
        $asset->delete();
      });

      return response()->json([
        'success' => true,
        'message' => __('media_posts.asset_deleted'),
      ]);
    } catch (\Throwable $e) {
      Log::error('Error in MediaPostController.deleteAssetFromMediaPost: ' . $e->getMessage());
      Log::error($e->getTraceAsString());
      return response()->json([
        'success' => false,
        'errors'  => [__('common.unexpected_error')],
      ], 500);
    }
  }

  public function requestReview(Request $request, $postId)
  {
    try {
      $user = $request->user();
      $post = MediaPost::with([
        'project.client.user',
        'assignments.creator.user',
        'assets'      => fn($q) => $q->whereNull('version_id')
          ->where('is_reference', false),
        'versions',
      ])->lockForUpdate()->find($postId);

      if (!$post) {
        return response()->json([
          'success' => false,
          'errors'  => [__('media_posts.post_not_found')],
        ], 404);
      }

      $isOwner   = $post->project?->client?->user_id === $user->id;
      $canUpdate = $user->hasPermission('media_posts', 'update');

      $isEditor = false;
      if ($user->creator) {
        $isEditor = MediaPostAssignment::where('post_id', $postId)
          ->where('creator_id', $user->creator->id)
          ->where('role', CREATOR_PROJECT_PERMISSION::EDITOR)
          ->exists();
      }

      if (!($isOwner || $canUpdate || $isEditor)) {
        return response()->json([
          'success' => false,
          'errors'  => [__('common.permission_denied')],
        ], 403);
      }

      if ($post->assets->isEmpty()) {
        return response()->json([
          'success' => false,
          'errors'  => [__('media_posts.no_draft_files')],
        ], 422);
      }

      $version = DB::transaction(function () use ($post, $user) {

        $nextNumber = ($post->versions->max('number') ?? 0) + 1;

        $v = MediaPostVersion::create([
          'post_id'    => $post->id,
          'number'     => $nextNumber,
          'status'     => VERSION_STATUS::IN_REVIEW,
          'created_by' => $user->creator?->id,
        ]);

        MediaPostAsset::where('post_id', $post->id)
          ->whereNull('version_id')
          ->where('is_reference', false)
          ->update(['version_id' => $v->id]);

        $post->update(['status' => MEDIA_POST_STATUS::UNDER_REVIEW]);

        return $v->load(['files', 'creator.user']);
      });

      return response()->json([
        'success' => true,
        'data'    => ['item' => $version],
        'message' => __('media_posts.review_requested'),
      ]);
    } catch (\Throwable $e) {
      Log::error('Error in requestReview: ' . $e->getMessage());
      Log::error($e->getTraceAsString());
      return response()->json([
        'success' => false,
        'errors'  => [__('common.unexpected_error')],
      ], 500);
    }
  }

  public function reviewVersion(Request $request, $postId)
  {
    try {
      $user = $request->user();

      $data = $request->validate([
        'version_id' => 'required|exists:media_post_versions,id',
        'decision'   => ['required', 'string', Rule::in(array_column(MEDIA_POST_REVIEW_DECISION::cases(), 'value'))],
        'comment'    => 'nullable|string',
      ]);

      $post = MediaPost::find($postId);
      $version = MediaPostVersion::with('post')
        ->lockForUpdate()
        ->find($data['version_id']);

      if (!$post || !$version || $version->post_id != $postId) {
        return response()->json([
          'success' => false,
          'errors'  => [__('media_posts.version_not_in_review')],
        ], 422);
      }

      $isClient = $post->project?->client?->user_id === $user->id;
      if (!$isClient && !$user->hasPermission('media_posts', 'update')) {
        return response()->json([
          'success' => false,
          'errors'  => [__('common.permission_denied')],
        ], 403);
      }

      if ($version->status !== VERSION_STATUS::IN_REVIEW->value) {
        return response()->json([
          'success' => false,
          'errors'  => [__('media_posts.version_not_in_review')],
        ], 422);
      }

      DB::transaction(function () use ($post, $version, $data) {

        MediaPostReview::create([
          'post_id'      => $post->id,
          'version_id'   => $version->id,
          'reviewer_id'  => $post->project->client->id,
          'reviewer_type' => 'CLIENT',
          'decision'     => $data['decision'],
          'comment'      => $data['comment'] ?? null,
        ]);

        if ($data['decision'] === MEDIA_POST_REVIEW_DECISION::APPROVED->value) {
          $version->update(['status' => VERSION_STATUS::APPROVED]);
          $post->update(['status' => MEDIA_POST_STATUS::APPROVED]);
        } else {
          $version->update(['status' => VERSION_STATUS::CHANGES_REQUESTED]);
          $post->update(['status' => MEDIA_POST_STATUS::IN_PROGRESS]);
        }
      });

      return response()->json([
        'success' => true,
        'message' => __('media_posts.review_saved'),
      ]);
    } catch (\Throwable $e) {
      Log::error('reviewVersion error: ' . $e->getMessage());
      Log::error($e->getTraceAsString());
      return response()->json([
        'success' => false,
        'errors'  => [__('common.unexpected_error')],
      ], 500);
    }
  }
}
