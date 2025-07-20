<?php

namespace App\Http\Controllers;

use App\Models\ProjectUpdate;
use Illuminate\Http\Request;

class ProjectUpdateController extends CrudController
{
  protected function getModel(): string
  {
    return ProjectUpdate::class;
  }

  protected function getTable(): string
  {
    return 'project_updates';
  }

  protected function getModelClass(): string
  {
    return ProjectUpdate::class;
  }
}
