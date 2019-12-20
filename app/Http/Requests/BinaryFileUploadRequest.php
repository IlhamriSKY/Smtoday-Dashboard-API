<?php

namespace Vanguard\Http\Requests;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Str;

abstract class BinaryFileUploadRequest extends Request
{
    /**
     * @var
     */
    private $fileName;

    /**
     * @var
     */
    protected $fs;

    /**
     * {@inheritdoc}
     */
    protected function prepareForValidation()
    {
        $this->fs = $this->container->make(Filesystem::class);

        $this->files->set(
            $this->fileFieldName(),
            $this->getUploadedFile()
        );
    }

    /**
     * Get the file field name which will be used for storing the UploadedFile
     * into request's FilesBag.
     * @return string
     */
    protected function fileFieldName()
    {
        return 'file';
    }

    /**
     * Get UploadedFile from request body.
     * @return UploadedFile
     */
    protected function getUploadedFile()
    {
        $this->fileName = Str::random(20);

        $this->fs->put($this->fileName, $this->getContent());

        return new UploadedFile(
            storage_path("app/{$this->fileName}"),
            $this->fileName,
            $this->fs->mimeType($this->fileName),
            null,
            null,
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function failedValidation(Validator $validator)
    {
        if ($this->fs->has($this->fileName)) {
            $this->fs->delete($this->fileName);
        }

        parent::failedValidation($validator);
    }
}
