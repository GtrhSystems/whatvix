<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait HasActivityLog
{
    /**
     * Only log the given attributes.
     *
     * @param  array  $attributes
     * @return array
     */
    public function logOnly(array $attributes): array
    {
        return $attributes;
    }

    /**
     * Boot the activity log trait for a model.
     * 
     * Automatically logs activity for the created, updated, and deleted events
     * by calling the logActivity method with the respective event type.
     */

    protected static function bootHasActivityLog()
    {
        static::created(function (Model $model) {
            static::logActivity($model, 'created');
        });

        static::updated(function (Model $model) {
            static::logActivity($model, 'updated');
        });

        static::deleted(function (Model $model) {
            static::logActivity($model, 'deleted');
        });
    }

    /**
     * Logs activity for the specified model and event type.
     *
     * @param Model $model The model instance related to the activity.
     * @param string $event The type of event being logged ('created', 'updated', or 'deleted').
     * 
     * @return void
     */
    protected static function logActivity(Model $model, string $event)
    {
        $logOnlyAttributes = $model->logOnly([]);
        $meta = [];

        if ($event === 'created') {
            if (!empty($logOnlyAttributes)) {
                $meta = array_intersect_key($model->getAttributes(), array_flip($logOnlyAttributes));
            } else {
                $meta = $model->getAttributes();
            }
        } elseif ($event === 'updated') {
            $changes = $model->getDirty();

            if (!empty($logOnlyAttributes)) {
                $changes = array_intersect_key($changes, array_flip($logOnlyAttributes));

                if (empty($changes)) {
                    return;
                }
            }

            $original = array_intersect_key($model->getOriginal(), $changes);

            $meta = [
                'old' => $original,
                'new' => $changes,
            ];
        } elseif ($event === 'deleted') {
            if (!empty($logOnlyAttributes)) {
                $meta = array_intersect_key($model->getAttributes(), array_flip($logOnlyAttributes));
            } else {
                $meta = $model->getAttributes();
            }
        }

        if (empty($meta)) {
            return;
        }

        $modelName = Str::title(Str::snake(class_basename($model), ' '));
        /** @var \App\Models\User */
        $user = Auth::user();

        try {
            ActivityLog::create([
                'uuid' => Str::uuid(),
                'user_id' => activeWorkspaceOwnerId() ?? Auth::id(),
                'creator_id' => Auth::id(),
                'workspace_id' => $user?->getCurrentWorkspace()?->id ?? null,
                'loggable_type' => get_class($model),
                'loggable_id' => $model->getKey(),
                'log_name' => static::getLogName() ?? class_basename($model),
                'description' => "{$modelName} was {$event}",
                'event' => $event,
                'meta' => $meta,
            ]);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
        }
    }

    public static function getLogName(): ?string
    {
        return null;
    }

    /**
     * Returns a morphMany relationship with the ActivityLog model.
     * 
     * This allows you to easily retrieve all activity logs for the current model.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'loggable');
    }
}
