<?php

namespace App\Actions;

use App\Jobs\ConvertTaskPhotoJob;
use App\Models\Master;
use App\Models\OrderTask;
use Illuminate\Http\UploadedFile;

class UploadTaskPhotoAction
{
    /** @param 'before'|'after' $type */
    public function handle(Master $master, OrderTask $task, string $type, UploadedFile $photo): OrderTask
    {
        if ($task->order->master_id !== $master->id) {
            throw new \DomainException('This task does not belong to you.');
        }

        $pathField = "{$type}_photo_path";
        $statusField = "{$type}_status";

        $path = $photo->store("orders/{$task->order_id}/tasks/{$task->id}/{$type}", 'public');

        $task->update([
            $pathField => $path,
            $statusField => OrderTask::STATUS_PENDING,
        ]);

        ConvertTaskPhotoJob::dispatch($task->id, $type);

        return $task->fresh();
    }
}
