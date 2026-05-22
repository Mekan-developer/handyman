<?php

namespace App\Jobs;

use App\Models\OrderTask;
use App\Support\PhotoConverter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class ConvertTaskPhotoJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 30;

    /** @param 'before'|'after' $photoType */
    public function __construct(
        public readonly int $taskId,
        public readonly string $photoType,
    ) {}

    public function handle(): void
    {
        $task = OrderTask::find($this->taskId);

        if ($task === null) {
            return;
        }

        $pathField = $this->photoType.'_photo_path';
        $statusField = $this->photoType.'_status';

        $currentPath = $task->{$pathField};
        $currentStatus = $task->{$statusField};

        if ($currentPath === null || $currentStatus === OrderTask::STATUS_DONE) {
            return;
        }

        $task->update([$statusField => OrderTask::STATUS_CONVERTING]);

        try {
            $absoluteOriginal = Storage::disk('public')->path($currentPath);
            $absoluteWebp = PhotoConverter::convert($absoluteOriginal);

            Storage::disk('public')->delete($currentPath);

            $webpRelative = ltrim(str_replace(
                Storage::disk('public')->path(''),
                '',
                $absoluteWebp
            ), DIRECTORY_SEPARATOR);

            $task->update([
                $pathField => $webpRelative,
                $statusField => OrderTask::STATUS_DONE,
            ]);
        } catch (\Throwable $e) {
            $task->update([$statusField => OrderTask::STATUS_FAILED]);
            throw $e;
        }
    }
}
