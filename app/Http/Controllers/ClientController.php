<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class ClientController extends CrudController
{
    protected function getModel(): string
    {
        return Client::class;
    }

    protected function getTable(): string
    {
        return 'clients';
    }

    protected function getModelClass(): string
    {
        return Client::class;
    }

    protected function getRelations(): array
    {
        return ['user'];
    }

    protected function getReadAllQuery(): Builder
    {
        return $this->model()->with('user');
    }

    public function readOne($id, Request $request)
    {
        try {
            if (in_array('read_one', $this->restricted)) {
                $user = $request->user();
                if (! $user->hasPermission($this->getTable(), 'read', $id)) {
                    return response()->json(
                        [
                            'success' => false,
                            'errors' => [__('common.permission_denied')],
                        ]
                    );
                }
            }

            $item = $this->model()->with(['user.profile'])->find($id);

            if (! $item) {
                return response()->json(
                    [
                        'success' => false,
                        'errors' => [__($this->getTable() . '.not_found')],
                    ]
                );
            }

            if (method_exists($this, 'afterReadOne')) {
                $this->afterReadOne($item, $request);
            }

            return response()->json(
                [
                    'success' => true,
                    'data' => ['item' => $item],
                ]
            );
        } catch (\Exception $e) {
            Log::error('Error caught in function ClientController.readOne: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }
}
