<?php

namespace App\Http\Controllers;

use App\Models\Creator;
use Illuminate\Http\Request;

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
}
