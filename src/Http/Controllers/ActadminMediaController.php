<?php

namespace ACT\Actadmin\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use League\Flysystem\Plugin\ListWith;
use ACT\Actadmin\Facades\Actadmin;

class ActadminMediaController extends Controller
{
    /** @var string */
    private $filesystem;

    /** @var string */
    private $directory = '';

    public function __construct()
    {
        $this->filesystem = config('actadmin.storage.disk');
    }

    public function index()
    {
        // Check permission
        Actadmin::canOrFail('browse_media');

        return Actadmin::view('Actadmin::media.index');
    }

    public function files(Request $request)
    {
        // Check permission
        Actadmin::canOrFail('browse_media');

        $folder = $request->folder;

        if ($folder == '/') {
            $folder = '';
        }

        $dir = $this->directory.$folder;

        return response()->json([
            'name'          => 'files',
            'type'          => 'folder',
            'path'          => $dir,
            'folder'        => $folder,
            'items'         => $this->getFiles($dir),
            'last_modified' => 'asdf',
        ]);
    }

    // New Folder with 5.3
    public function new_folder(Request $request)
    {
        // Check permission
        Actadmin::canOrFail('browse_media');

        $new_folder = $request->new_folder;
        $success = false;
        $error = '';

        if (Storage::disk($this->filesystem)->exists($new_folder)) {
            $error = __('Actadmin::media.folder_exists_already');
        } elseif (Storage::disk($this->filesystem)->makeDirectory($new_folder)) {
            $success = true;
        } else {
            $error = __('Actadmin::media.error_creating_dir');
        }

        return compact('success', 'error');
    }

    // Delete File or Folder with 5.3
    public function delete_file_folder(Request $request)
    {
        // Check permission
        Actadmin::canOrFail('browse_media');

        $folderLocation = $request->folder_location;
        $fileFolder = $request->file_folder;
        $type = $request->type;
        $success = true;
        $error = '';

        if (is_array($folderLocation)) {
            $folderLocation = rtrim(implode('/', $folderLocation), '/');
        }

        $location = "{$this->directory}/{$folderLocation}";
        $fileFolder = "{$location}/{$fileFolder}";

        if ($type == 'folder') {
            if (!Storage::disk($this->filesystem)->deleteDirectory($fileFolder)) {
                $error = __('Actadmin::media.error_deleting_folder');
                $success = false;
            }
        } elseif (!Storage::disk($this->filesystem)->delete($fileFolder)) {
            $error = __('Actadmin::media.error_deleting_file');
            $success = false;
        }

        return compact('success', 'error');
    }

    // GET ALL DIRECTORIES Working with Laravel 5.3
    public function get_all_dirs(Request $request)
    {
        // Check permission
        Actadmin::canOrFail('browse_media');

        $folderLocation = $request->folder_location;

        if (is_array($folderLocation)) {
            $folderLocation = rtrim(implode('/', $folderLocation), '/');
        }

        $location = "{$this->directory}/{$folderLocation}";

        return response()->json(
            str_replace($location, '', Storage::disk($this->filesystem)->directories($location))
        );
    }

    // NEEDS TESTING
    public function move_file(Request $request)
    {
        // Check permission
        Actadmin::canOrFail('browse_media');

        $source = $request->source;
        $destination = $request->destination;
        $folderLocation = $request->folder_location;
        $success = false;
        $error = '';

        if (is_array($folderLocation)) {
            $folderLocation = rtrim(implode('/', $folderLocation), '/');
        }

        $location = "{$this->directory}/{$folderLocation}";
        $source = "{$location}/{$source}";
        $destination = strpos($destination, '/../') !== false
            ? $this->directory.'/'.dirname($folderLocation).'/'.str_replace('/../', '', $destination)
            : "/{$destination}";

        if (!file_exists($destination)) {
            if (Storage::disk($this->filesystem)->move($source, $destination)) {
                $success = true;
            } else {
                $error = __('Actadmin::media.error_moving');
            }
        } else {
            $error = __('Actadmin::media.error_already_exists');
        }

        return compact('success', 'error');
    }

    // RENAME FILE WORKING with 5.3
    public function rename_file(Request $request)
    {
        // Check permission
        Actadmin::canOrFail('browse_media');

        $folderLocation = $request->folder_location;
        $filename = $request->filename;
        $newFilename = $request->new_filename;
        $success = false;
        $error = false;

        if (is_array($folderLocation)) {
            $folderLocation = rtrim(implode('/', $folderLocation), '/');
        }

        $location = "{$this->directory}/{$folderLocation}";

        if (!Storage::disk($this->filesystem)->exists("{$location}/{$newFilename}")) {
            if (Storage::disk($this->filesystem)->move("{$location}/{$filename}", "{$location}/{$newFilename}")) {
                $success = true;
            } else {
                $error = __('Actadmin::media.error_moving');
            }
        } else {
            $error = __('Actadmin::media.error_may_exist');
        }

        return compact('success', 'error');
    }

    // Upload Working with 5.3
    public function upload(Request $request)
    {
        // Check permission
        Actadmin::canOrFail('browse_media');

        try {
            $realPath = Storage::disk($this->filesystem)->getDriver()->getAdapter()->getPathPrefix();

            $allowedImageMimeTypes = [
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/bmp',
                'image/svg+xml',
            ];
            $file = $request->file->store($request->upload_path, $this->filesystem);

            if (in_array($request->file->getMimeType(), $allowedImageMimeTypes)) {
                $image = Image::make($realPath.$file);

                if ($request->file->getClientOriginalExtension() == 'gif') {
                    copy($request->file->getRealPath(), $realPath.$file);
                } else {
                    $image->orientate()->save($realPath.$file);
                }
            }

            $success = true;
            $message = __('Actadmin::media.success_uploaded_file');
            $path = preg_replace('/^public\//', '', $file);
        } catch (Exception $e) {
            $success = false;
            $message = $e->getMessage();
            $path = '';
        }

        return response()->json(compact('success', 'message', 'path'));
    }

    private function getFiles($dir)
    {
        // Check permission
        Actadmin::canOrFail('browse_media');

        $files = [];
        $storage = Storage::disk($this->filesystem)->addPlugin(new ListWith());
        $storageItems = $storage->listWith(['mimetype'], $dir);

        foreach ($storageItems as $item) {
            if ($item['type'] == 'dir') {
                $files[] = [
                    'name'          => $item['basename'],
                    'type'          => 'folder',
                    'path'          => Storage::disk($this->filesystem)->url($item['path']),
                    'items'         => '',
                    'last_modified' => '',
                ];
            } else {
                if (empty(pathinfo($item['path'], PATHINFO_FILENAME)) && !config('actadmin.hidden_files')) {
                    continue;
                }
                $files[] = [
                    'name'          => $item['basename'],
                    'type'          => isset($item['mimetype']) ? $item['mimetype'] : 'file',
                    'path'          => Storage::disk($this->filesystem)->url($item['path']),
                    'size'          => $item['size'],
                    'last_modified' => $item['timestamp'],
                ];
            }
        }

        return $files;
    }

    // REMOVE FILE
    public function remove(Request $request)
    {
        // Check permission
        Actadmin::canOrFail('browse_media');

        try {
            // GET THE SLUG, ex. 'posts', 'pages', etc.
            $slug = $request->get('slug');

            // GET file name
            $filename = $request->get('filename');

            // GET record id
            $id = $request->get('id');

            // GET field name
            $field = $request->get('field');

            // GET multi value
            $multi = $request->get('multi');

            // GET THE DataType based on the slug
            $dataType = Actadmin::model('DataType')->where('slug', '=', $slug)->first();

            // Check permission
            Actadmin::canOrFail('delete_'.$dataType->name);

            // Load model and find record
            $model = app($dataType->model_name);
            $data = $model::find([$id])->first();

            // Check if field exists
            if (!isset($data->{$field})) {
                throw new Exception(__('Actadmin::generic.field_does_not_exist'), 400);
            }

            if (@json_decode($multi)) {
                // Check if valid json
                if (is_null(@json_decode($data->{$field}))) {
                    throw new Exception(__('Actadmin::json.invalid'), 500);
                }

                // Decode field value
                $fieldData = @json_decode($data->{$field}, true);
                $key = null;

                // Check if we're dealing with a nested array for the case of multiple files
                if (is_array($fieldData[0])) {
                    foreach ($fieldData as $index=>$file) {
                        $file = array_flip($file);
                        if (array_key_exists($filename, $file)) {
                            $key = $index;
                            break;
                        }
                    }
                } else {
                    $key = array_search($filename, $fieldData);
                }

                // Check if file was found in array
                if (is_null($key) || $key === false) {
                    throw new Exception(__('Actadmin::media.file_does_not_exist'), 400);
                }

                // Remove file from array
                unset($fieldData[$key]);

                // Generate json and update field
                $data->{$field} = empty($fieldData) ? null : json_encode(array_values($fieldData));
            } else {
                $data->{$field} = null;
            }

            $data->save();

            return response()->json([
               'data' => [
                   'status'  => 200,
                   'message' => __('Actadmin::media.file_removed'),
               ],
            ]);
        } catch (Exception $e) {
            $code = 500;
            $message = __('Actadmin::generic.internal_error');

            if ($e->getCode()) {
                $code = $e->getCode();
            }

            if ($e->getMessage()) {
                $message = $e->getMessage();
            }

            return response()->json([
                'data' => [
                    'status'  => $code,
                    'message' => $message,
                ],
            ], $code);
        }
    }

    // Crop Image
    public function crop(Request $request)
    {
        // Check permission
        Actadmin::canOrFail('browse_media');

        $createMode = $request->get('createMode') === 'true';
        $x = $request->get('x');
        $y = $request->get('y');
        $height = $request->get('height');
        $width = $request->get('width');

        $realPath = Storage::disk($this->filesystem)->getDriver()->getAdapter()->getPathPrefix();
        $originImagePath = $realPath.$request->upload_path.'/'.$request->originImageName;

        try {
            if ($createMode) {
                // create a new image with the cpopped data
                $fileNameParts = explode('.', $request->originImageName);
                array_splice($fileNameParts, count($fileNameParts) - 1, 0, 'cropped_'.time());
                $newImageName = implode('.', $fileNameParts);
                $destImagePath = $realPath.$request->upload_path.'/'.$newImageName;
            } else {
                // override the original image
                $destImagePath = $originImagePath;
            }

            Image::make($originImagePath)->crop($width, $height, $x, $y)->save($destImagePath);

            $success = true;
            $message = __('Actadmin::media.success_crop_image');
        } catch (Exception $e) {
            $success = false;
            $message = $e->getMessage();
        }

        return response()->json(compact('success', 'message'));
    }
}
