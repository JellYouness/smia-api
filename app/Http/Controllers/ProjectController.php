<?php

namespace App\Http\Controllers;

use App\Models\Project;

class ProjectController extends CrudController
{
  protected function getModel(): string
  {
    return Project::class;
  }

  protected function getTable(): string
  {
    return 'projects';
  }

  protected function getModelClass(): string
  {
    return Project::class;
  }
}
