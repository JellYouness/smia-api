<?php

namespace App\Models;

use App\Enums\PROJECT_UPDATE_TYPE;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\Rule;

class ProjectUpdate extends Model
{
  use HasFactory;

  protected $fillable = [
    'project_id',
    'client_id',
    'ambassador_id',
    'body',
    'type',
  ];

  protected $casts = [
    'type' => PROJECT_UPDATE_TYPE::class,
  ];

  public function project(): BelongsTo
  {
    return $this->belongsTo(Project::class);
  }

  public function client(): BelongsTo
  {
    return $this->belongsTo(Client::class);
  }

  public function ambassador(): BelongsTo
  {
    return $this->belongsTo(Ambassador::class);
  }

  public static function rules(): array
  {
    return [
      'project_id' => 'required|exists:projects,id',
      'client_id' => 'nullable|exists:clients,id',
      'ambassador_id' => 'nullable|exists:ambassadors,id',
      'body' => 'required|string',
      'type' => ['required', 'string', Rule::in(array_column(PROJECT_UPDATE_TYPE::cases(), 'value'))],
    ];
  }
}
