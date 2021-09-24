# PHP library Storage

## Installation instructions

### Installation

Simple installation via Composer
```bash
composer require thanhdai/storage
```

Your config/filesystems.php.
``` php
'filerobot' => [
    'driver' => 'filerobot',
    'key' => env('FILEROBOT_KEY_ID'),
]
```

Your .env
``` php
FILEROBOT_KEY_ID=fa5fe3303dd34e1da4810915c7c3fd6f
```
## Usage
### Files operations
#### List or search files
Lists all files in your Filerobot container. You can alternatively search by providing a search string. Can be recursive.
``` php
return $this->filerobot->list_file('/api-demo');
```
| Parameter | Default | Description |
| --- | --- | --- |
| folder | | Folder to start the search from. Case sensitive. |
| query | | (optional) Search pattern matching the file name or metadata. |
| order | filename,desc | (optional) Order results by: updated_at created_at Append ,asc or ,desc to get ascending or descending results. Example: updated_at,desc|
| limit | 50 | (optional) Specifies the maximum number of files to return. [1-4000].|
| offset | 0 | (optional) Specifies the offset of files to display.|
| mime | |  (optional) Returns only files from specified mimeType.|
| format | | (optional) Allows you to export the results as a csv. Example: format=csv |

#### Get file details
Retrieving a file's details over UUID requires to authenticate against the API.
``` php
return $this->filerobot->detail_file($file_uuid);
```

#### Rename file
Renames the file with the value given in the body.
``` php
return $this->filerobot->rename_file($file_uuid, $new_filename);
```

#### Move file
Will move the file to a new folder. The folder will be created if it doesn't already exist.
``` php
return $this->filerobot->move_file($file_uuid, $folder_uuid);
```

#### Delete file
Delete a file using its UUID as reference.
``` php
return $this->filerobot->delete_file($file_uuid);
```

#### Upload one or multiple files
Multiple methods are available to suit different needs

##### - Method 1 - multipart/form-data request
``` php
return $this->filerobot->upload_file_multipart('/api-demo', 'path/bear.jpg', 'bear.jpg');
```

##### - Method 2 - URL(s) of remotely hosted file(s)
``` php
return $this->filerobot->upload_file_remote('/api-demo', '[{"name": "new_filename.jpg",  "url":"http://sample.li/boat.jpg" }]');
```

##### - Method 3 - base64-encoded content
``` php
$image = base64_encode(file_get_contents('path/bear.jpeg'));
return $this->filerobot->upload_file_binary('new_image_from_base64.png', $image)
```

### Folders operations
#### List and search folders 
Lists all folders in your Filerobot container. You can search by providing a search string. Can be recursive.
``` php
return $this->filerobot->list_folder('/api-demo');
```
| Parameter | Default | Description |
| --- | --- | --- |
| folder | | Folder to start the search from. Case sensitive. |
| query | | (optional) Search pattern matching the folder name or metadata. |
| order | filename,desc | (optional) Order results by: updated_at created_at Append ,asc or ,desc to get ascending or descending results. Example: updated_at,desc|
| limit | 50 | (optional) Specifies the maximum number of folders to return. [1-4000].|
| offset | 0 | (optional) Specifies the offset of files to display.|

#### Get folder details
Gets all information of a folder identified by its folder_uuid. This API will also allow you to check the existence of a folder.
``` php
return $this->filerobot->detail_folder($folder_uuid);
```

#### Rename folder
Renames the folder identified by its folder_uuid to the value given in the body
``` php
return $this->filerobot->rename_folder($folder_uuid, $new_foldername);
```

#### Move folder
Will move a folder, identified by its folder_uuid to a new location (folder) which can be identified by destination_folder_uuid.
``` php
return $this->filerobot->move_folder($folder_uuid, $destination_folder_uuid);
```

#### Delete folder
Deletes a folder _and all sub-folders recursively_.
``` php
return $this->filerobot->delete_folder($folder_uuid);
```

#### Create folder
Creates a folder from the value given in the body.
``` php
return $this->filerobot->create_folder($foldername)
```

