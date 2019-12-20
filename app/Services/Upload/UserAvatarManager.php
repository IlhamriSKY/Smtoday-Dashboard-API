<?php

namespace Vanguard\Services\Upload;

use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Vanguard\User;
use Illuminate\Filesystem\Filesystem;
use Intervention\Image\ImageManager;

class UserAvatarManager
{
    const AVATAR_WIDTH = 160;

    const AVATAR_HEIGHT = 160;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var ImageManager
     */
    private $imageManager;

    /**
     * @var string Storage disk used for keeping photos.
     */
    protected $disk = 'public';

    public function __construct(FilesystemManager $fs, ImageManager $imageManager)
    {
        $this->fs = $fs->disk($this->disk);
        $this->imageManager = $imageManager;
    }

    /**
     * Upload and crop user avatar to predefined width and height.
     *
     * @param UploadedFile $file
     * @param array|null $cropPoints
     * @return string Avatar file name.
     */
    public function uploadAndCropAvatar(UploadedFile $file, array $cropPoints = null)
    {
        try {
            return $this->cropAndResizeImage($file, $cropPoints);
        } catch (\Exception $e) {
            logger("Cannot upload and crop image. " . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if user has uploaded avatar photo.
     * If he is using some external url for avatar, then
     * it is assumed that avatar is not uploaded manually.
     *
     * @param User $user
     * @return bool
     */
    private function userHasUploadedAvatar(User $user)
    {
        return $user->avatar && ! Str::contains($user->avatar, ['http', 'gravatar']);
    }

    /**
     * Get destination directory where avatar should be uploaded.
     *
     * @return string
     */
    private function getDestinationDirectory()
    {
        return '/upload/users';
    }

    /**
     * @param User $user
     */
    public function deleteAvatarIfUploaded(User $user)
    {
        if (! $this->userHasUploadedAvatar($user)) {
            return;
        }

        $path = sprintf(
            "%s/%s",
            $this->getDestinationDirectory(),
            $user->avatar
        );

        $this->fs->delete($path);
    }

    /**
     * Crop image from provided selected points and
     * resize it to predefined width and height.
     *
     * @param UploadedFile $file
     * @param array|null $points
     * @return string
     */
    private function cropAndResizeImage(UploadedFile $file, array $points = null)
    {
        $image = $this->imageManager->make($file);

        if ($points) {
            // Calculate delta between two points on X axis. This
            // value will be used as width and height for cropped image.
            $size = floor($points['x2'] - $points['x1']);

            $image->crop($size, $size, (int) $points['x1'], (int) $points['y1'])
                ->resize(self::AVATAR_WIDTH, self::AVATAR_HEIGHT);
        } else {
            // If crop points are not provided, we will just crop
            // provided image to specified width and height.
            $image->crop(self::AVATAR_WIDTH, self::AVATAR_HEIGHT);
        }

        $fileName = $file->hashName($this->getDestinationDirectory());

        $this->fs->put(
            $fileName,
            $image->stream(null, 100)->__toString(),
            'public'
        );

        return basename($fileName);
    }
}
