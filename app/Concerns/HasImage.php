<?php

namespace App\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasImage
{
    /**
     * Get the storage disk for images.
     */
    public function imageDisk(): string
    {
        return 'public';
    }

    /**
     * Get the storage directory for this model's images.
     */
    public function imageDirectory(): string
    {
        return Str::snake(Str::pluralStudly(class_basename($this)));
    }

    /**
     * Get the image column name on this model.
     */
    public function imageColumn(): string
    {
        return 'image';
    }

    /**
     * Get the full public URL for the image.
     */
    public function imageUrl(): ?string
    {
        $path = $this->{$this->imageColumn()};

        if (! $path) {
            return null;
        }

        return Storage::disk($this->imageDisk())->url($path);
    }

    /**
     * Upload and store an image, deleting the old one if it exists.
     */
    public function uploadImage(UploadedFile $file): string
    {
        $this->deleteImage();

        $path = $file->store($this->imageDirectory(), $this->imageDisk());

        $this->updateQuietly([$this->imageColumn() => $path]);

        return $path;
    }

    /**
     * Delete the current image from storage.
     */
    public function deleteImage(): void
    {
        $path = $this->{$this->imageColumn()};

        if ($path && Storage::disk($this->imageDisk())->exists($path)) {
            Storage::disk($this->imageDisk())->delete($path);
        }
    }

    /**
     * Check if this model has an image.
     */
    public function hasImage(): bool
    {
        return ! empty($this->{$this->imageColumn()});
    }

    /**
     * Boot the trait — auto-delete image when model is permanently deleted.
     * For SoftDeletes models, images are only deleted on forceDelete.
     */
    public static function bootHasImage(): void
    {
        static::deleting(function ($model) {
            if (! method_exists($model, 'isForceDeleting') || $model->isForceDeleting()) {
                $model->deleteImage();
            }
        });
    }
}
