Upload Manager [![Build Status](https://travis-ci.org/ondrs/upload-manager.svg?branch=master)](https://travis-ci.org/ondrs/upload-manager)
==============

Upload manager for Nette framework

Instalation
-----

composer.json

    "ondrs/upload-manager": "dev-master"

Configuration
-----

Register the extension:

    extensions:
        uploadManager: ondrs\UploadManager\DI\Extension

Minimal configuration:

    uploadManager:
        relativePath: '/uploads'

Full configuration:

    uploadManager:
        basePath: %wwwDir%
        relativePath: '/uploads'
        fileManager:
            blackList: {php}
        imageManager:
            maxSize: 1280
            type: jpg
            quality: 80
            saveOriginal: FALSE
            dimensions:
                800:
                    - {800, NULL}
                    - shrink_only
                500:
                    - {500, NULL}
                    - shrink_only
                250:
                    - {250, NULL}
                    - shrink_only
                thumb:
                    - {100, NULL}
                    - shrink_only

In most cases you want to choose the `wwwDir` as your `basePath` (and it is chosen by default) to make your files publicly accessible.
`relativePath` is relative to the `basePath`, so complete path where your files will be uploaded looks like this

    {basePath}/{relativePath}[/{dir}]

`dir` is an optional parameter and you can set it during the runtime of your script in the `listen()` or in the `upload()` method.

**fileManager:**
- blacklist
  - array of files extensions which are blacklisted, php is by default

**imageManager**
- maxSize
  - maximum size of an image, if its bigger, it will be automatically resized to this size
  - can be number X coord of an imahe
  - or array [X, Y]

- dimensions
  - array of dimensions to which an image will be resized
  - format is
    ```
    PREFIX:
        - {X_SIZE, Y_SIZE}
        - RESIZE_OPTION
    ```

  - `PREFIX` can be whatever you want, it will be added to a resized file: `PREFIX_file.jpg`
  - `Y_SIZE` is optional as well as `RESIZE_OPTION`
  - `RESIZE_OPTION` is set to `Image::SHRINK_ONLY` by default

For example we will set the UploadManager according to the full configuration which is written above.

    $this->upload->filesToDir('dir/path')

Uploading an image file `foo.jpg` with size (1680 x 1050) will result in creation of 5 files: `foo.jpg, 800_foo.jpg, 500_.jpg, 250_foo.jpg, thumb_foo.jpg`
which will be saved in the `%wwwDir%/uploads/super/dir`
All files are resized proportionally according to their X dimension and saved with a corresponding prefix.
File foo.jpg is considered to be an original but it's resized to 1280px.


AWS S3 Support
-----

Setup your credentials and put your bucket name as a basePath. That's all: 

    uploadManager:
        basePath: 'your-bucket-name'
        relativePath: 'some/path/to/dir'
        s3:
            region: 'eu-central-1'
            version: '2006-03-01'
            credentials:
                key: 'xxxxxxx'
                secret: 'xxxxxxx'

Simple as that!

Usage
-----

Inject `ondrs\UploadManager\Upload` into your presenter or wherever you want

    /** @var \ondrs\UploadManager\Upload @inject
    public $upload;

And do an upload.

    public function renderUpload()
    {
        $this->upload->filesToDir('path/to/dir');
    }

If you want to upload just a single file (for example with a form) call the `singleFileToDir()` method

    public function processForm($form)
    {
        /** @var Nette\Http\FileUpload */
        $fileUpload = $form->values->file;

        $this->upload->singleFileToDir('path/to/dir', $fileUpload);
    }


Events
-----

The real fun comes up with an events. They are here to help you to control and monitor your upload process with an ease.

- onQueueBegin
  - called before the upload starts
  - accept one argument
    1. array of Nette\Http\FileUpload objects which *will be uploaded*

- onQueueComplete
  - called when the upload finish
  - accept two arguments
    1. array of Nette\Http\FileUpload
    2. array of \SplFileInfo objects which *were uploaded*

- onFileBegin
  - called before the upload of *each file*
  - accept two arguments
    1. Nette\Http\FileUpload
    2. dir which is constructed as `{relativePath}[/{dir}]`

- onFileComplete
  - called after the upload complete of *each file*
  - accept three arguments
    1. Nette\Http\FileUpload object of the *original file*
    2. \SplFileInfo object of the *uploaded file*
    3. dir which is constructed as `{relativePath}[/{dir}]`


Real world example
-----

    /**
     * @param int $eventId
     */
    public function uploadAttachment($eventId)
    {
        /**
         * @param FileUpload $fileUpload
         * @param \SplFileInfo $uploadedFile
         * @param $path
         */
        $this->upload->onFileComplete[] = function (\SplFileInfo $uploadedFile, FileUpload $fileUpload, $path) use ($eventId) {

            $filename = $uploadedFile->getFilename();

            $this->db->table('crm_attachments')
                ->insert([
                    'filename' => $filename,
                    'path' => $path,
                    'crm_events_id' => $eventId,
                ]);
        };

        /**
         * @param array $files
         * @param array $uploadedFiles
         */
        $this->upload->onQueueComplete[] = function(array $files, array $uploadedFiles) use($eventId) {

            $uploadedFiles = array_map(function($i) {
                return $i->getFilename();
            }, $uploadedFiles);

            $this->db->table('crm_events')
                ->wherePrimary($eventId)
                ->update([
                    'text' => implode(';', $uploadedFiles),
                ]);
        };

        $this->upload->filesToDir('attachments/' . $eventId);
    }
