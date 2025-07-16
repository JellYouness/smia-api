<?php

namespace App\Http\Controllers;

use App\Models\Creator;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class CreatorController extends CrudController
{
  protected function getModel(): string
  {
    return Creator::class;
  }

  protected function getTable(): string
  {
    return 'creators';
  }

  protected function getModelClass(): string
  {
    return Creator::class;
  }

  protected function getRelations(): array
  {
    return ['user'];
  }

  protected function getReadAllQuery(): Builder
  {
    return $this->model()->with('user');
  }
}
