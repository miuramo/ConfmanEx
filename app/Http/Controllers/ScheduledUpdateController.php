<?php

namespace App\Http\Controllers;

use App\Models\ScheduledUpdate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ScheduledUpdateController extends Controller
{
    private const STATUS_OPTIONS = ['pending', 'completed', 'failed', 'canceled'];

    public function index()
    {
        $this->authorizeManage();
        $scheduledUpdates = ScheduledUpdate::with('target')
            ->orderByRaw("status = 'pending' desc")
            ->orderBy('execute_at')
            ->orderByDesc('id')
            ->paginate(50);

        return view('scheduled_update.index')->with(compact('scheduledUpdates'));
    }

    public function create(Request $request)
    {
        $this->authorizeManage();
        $models = $this->modelOptions();
        $selectedTargetType = $request->input('target_type', array_key_first($models));
        $selectedTargetId = $request->input('target_id');
        $selectedFieldName = $request->input('field_name');
        $target = $this->findTarget($selectedTargetType, $selectedTargetId);
        $columns = $this->columnsFor($selectedTargetType);
        $scheduledUpdate = new ScheduledUpdate([
            'target_type' => $selectedTargetType,
            'target_id' => $selectedTargetId,
            'field_name' => in_array($selectedFieldName, $columns, true) ? $selectedFieldName : null,
            'status' => 'pending',
            'execute_at' => now()->addHour(),
        ]);

        return view('scheduled_update.edit')->with(compact('scheduledUpdate', 'models', 'target', 'columns'));
    }

    public function store(Request $request)
    {
        $this->authorizeManage();
        $data = $this->validatedData($request);
        $scheduledUpdate = ScheduledUpdate::create($data);

        return redirect()->route('scheduled_update.edit', ['scheduled_update' => $scheduledUpdate])
            ->with('feedback.success', '予約更新を作成しました。');
    }

    public function edit(ScheduledUpdate $scheduledUpdate)
    {
        $this->authorizeManage();
        $models = $this->modelOptions();
        $target = $scheduledUpdate->target;
        $columns = $this->columnsFor($scheduledUpdate->target_type);

        return view('scheduled_update.edit')->with(compact('scheduledUpdate', 'models', 'target', 'columns'));
    }

    public function update(Request $request, ScheduledUpdate $scheduledUpdate)
    {
        $this->authorizeManage();
        $data = $this->validatedData($request);
        if ($request->input('action') === 'reuse') {
            $data['status'] = 'pending';
        }
        if ($scheduledUpdate->status !== 'pending' && $data['status'] === 'pending') {
            $data['executed_at'] = null;
            $data['error_message'] = null;
        }
        $scheduledUpdate->update($data);

        return redirect()->route('scheduled_update.edit', ['scheduled_update' => $scheduledUpdate])
            ->with('feedback.success', '予約更新を保存しました。');
    }

    public function destroy(ScheduledUpdate $scheduledUpdate)
    {
        $this->authorizeManage();
        $scheduledUpdate->delete();

        return redirect()->route('scheduled_update.index')->with('feedback.success', '予約更新を削除しました。');
    }

    public function bulkRescheduleNextYear(Request $request)
    {
        $this->authorizeManage();
        $data = $request->validate([
            'scheduled_update_ids' => ['required', 'array', 'min:1'],
            'scheduled_update_ids.*' => ['integer', 'min:1'],
        ]);

        $scheduledUpdates = ScheduledUpdate::whereIn('id', $data['scheduled_update_ids'])->get();
        foreach ($scheduledUpdates as $scheduledUpdate) {
            $wasCompleted = $scheduledUpdate->status === 'completed';
            $scheduledUpdate->execute_at = $scheduledUpdate->execute_at->copy()->addYear();
            $scheduledUpdate->status = 'pending';
            $scheduledUpdate->error_message = null;
            if ($wasCompleted) {
                $scheduledUpdate->executed_at = null;
            }
            $scheduledUpdate->save();
        }

        return redirect()->route('scheduled_update.index')
            ->with('feedback.success', $scheduledUpdates->count() . '件の予約更新を1年延長して再スケジュールしました。');
    }

    private function authorizeManage(): void
    {
        if (!auth()->user()->can('role_any', 'admin')) {
            abort(403);
        }
    }

    private function validatedData(Request $request): array
    {
        $request->validate([
            'target_type' => ['required', 'string'],
            'target_id' => ['required', 'integer', 'min:1'],
            'field_name' => ['required', 'string'],
            'new_value_text' => ['nullable', 'string'],
            'execute_at' => ['required', 'date'],
            'status' => ['required', 'in:' . implode(',', self::STATUS_OPTIONS)],
        ]);

        $targetType = $request->input('target_type');
        $targetId = (int) $request->input('target_id');
        $fieldName = $request->input('field_name');
        $modelClass = $this->normalizeModelClass($targetType);
        if ($modelClass === null) {
            return back()->withInput()->with('feedback.error', '対象モデルが正しくありません。')->throwResponse();
        }
        $target = $modelClass::find($targetId);
        if ($target === null) {
            return back()->withInput()->with('feedback.error', '対象レコードが見つかりません。')->throwResponse();
        }
        if (!in_array($fieldName, $this->columnsFor($modelClass), true)) {
            return back()->withInput()->with('feedback.error', '対象カラムが正しくありません。')->throwResponse();
        }

        return [
            'target_type' => $modelClass,
            'target_id' => $targetId,
            'field_name' => $fieldName,
            'new_value' => [$fieldName => $this->parseValue($request->input('new_value_text'))],
            'execute_at' => $request->input('execute_at'),
            'status' => $request->input('status'),
        ];
    }

    private function parseValue(?string $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }
        return $value;
    }

    private function modelOptions(): array
    {
        $models = [];
        foreach (glob(app_path('Models/*.php')) as $path) {
            $class = 'App\\Models\\' . basename($path, '.php');
            if ($class === ScheduledUpdate::class || !is_subclass_of($class, Model::class)) {
                continue;
            }
            try {
                $instance = new $class();
                if (Schema::hasTable($instance->getTable())) {
                    $models[$class] = class_basename($class) . ' (' . $instance->getTable() . ')';
                }
            } catch (\Throwable) {
                continue;
            }
        }
        ksort($models);
        return $models;
    }

    private function normalizeModelClass(?string $targetType): ?string
    {
        if ($targetType === null || $targetType === '') {
            return null;
        }
        $class = Str::startsWith($targetType, 'App\\Models\\') ? $targetType : 'App\\Models\\' . $targetType;
        if (!class_exists($class) || !is_subclass_of($class, Model::class)) {
            return null;
        }
        try {
            $instance = new $class();
            return Schema::hasTable($instance->getTable()) ? $class : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function columnsFor(?string $targetType): array
    {
        $modelClass = $this->normalizeModelClass($targetType);
        if ($modelClass === null) {
            return [];
        }
        $instance = new $modelClass();
        return array_values(array_filter(Schema::getColumnListing($instance->getTable()), function ($column) {
            return !in_array($column, ['id', 'created_at', 'updated_at', 'deleted_at'], true);
        }));
    }

    private function findTarget(?string $targetType, mixed $targetId): ?Model
    {
        $modelClass = $this->normalizeModelClass($targetType);
        if ($modelClass === null || !is_numeric($targetId)) {
            return null;
        }
        return $modelClass::find((int) $targetId);
    }
}
