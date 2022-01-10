# PHP library Storage

## Installation instructions

### Installation

Simple installation via Composer
```bash
composer require filerobot/storage
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
``` php
Storage::disk('filerobot')
```
### Files operations
#### List or search files
Lists all files in your Filerobot container. You can alternatively search by providing a search string. Can be recursive.
example: type:folder_name
``` php
return Storage::disk('filerobot')->listContents('file-api-demo', false);
```
You can add collect.
``` php
return collect(Storage::disk('filerobot')->listContents('file-api-demo', false))->where('name','test01')->first();
```

#### Get file or folder details
Retrieving a file's or folder details over UUID requires to authenticate against the API.
``` php
return Storage::disk('filerobot')->read('63accfbe-d1a1-502b-a1f6-47397645000e');
```

#### Rename file or folder
Renames the file or folder with the value given in the body.
``` php
return Storage::disk('filerobot')->rename($uuid, $name_change);
```

#### Move file
Will move the file or folder to a new folder. The folder will be created if it doesn't already exist.

``` php
return Storage::disk('filerobot')->copy($uuid, $name_change);
```

#### Delete file
Delete a file using its UUID as reference.
``` php
return Storage::disk('filerobot')->delete($file_uuid);
```

#### Upload files
Multiple methods are available to suit different needs

##### - Method 1 - multipart/form-data request
``` php
$config = [
    'name' => foder_name, // example '/api-demo'
    'type' => 'multipart'
];
$image = public_path('4090e6607e8bea2c9845b12630a927fd.jpg');
$name_upload = 'test01.png';
Storage::disk('filerobot')->put($name_upload, $image,$config);
```

##### - Method 2 - URL(s) of remotely hosted file(s)
``` php
$config = [
    'name' => forde_name, // example '/api-demo'
    'type' => 'remote'
];
$content = [
    [
        "name" => 'test03.png',
        "url"  => 'https://www.louisvuitton.com/images/U_Tr_Brand_campaign_Milos_DI3.jpg?wid=2048'
    ]
];

Storage::disk('filerobot')->put(null, json_encode($content),$config);
```

##### - Method 3 - base64-encoded content
``` php
$config = [
    'name' => folder_name, // example '/api-demo'
    'type' => 'base64'
];
$image  = base64_encode(file_get_contents('4090e6607e8bea2c9845b12630a927fd.jpg'));
$name_upload = 'test01.png';
Storage::disk('filerobot')->put($name_upload, $image,$config);
```

##### - Upload file stream
``` php
$config = [
    'name' => folder_name, // example '/api-demo'
];

$image  = fopen(public_path('4090e6607e8bea2c9845b12630a927fd.jpg'),"r");
$name_upload = 'test01.png';
Storage::disk('filerobot')->put($name_upload, $image,$config);
```

### Folders operations
#### List and search folders 
Lists all folders in your Filerobot container. You can search by providing a search string. Can be recursive.
example: type:folder_name
``` php
return Storage::disk('filerobot')->listContents('folder-api-demo', false);
```
#### Get folder details
Gets all information of a folder identified by its folder_uuid. This API will also allow you to check the existence of a folder.
``` php
return Storage::disk('filerobot')->read('63accfbe-d1a1-502b-a1f6-47397645000e');
```

#### Rename folder
Renames the folder identified by its folder_uuid to the value given in the body
``` php
return Storage::disk('filerobot')->rename($uuid, $name_change);
```

#### Move folder
Will move a folder, identified by its folder_uuid to a new location (folder) which can be identified by destination_folder_uuid.
``` php
return Storage::disk('filerobot')->copy($folder_uuid, $destination_folder_uuid);
```

#### Delete folder
Deletes a folder _and all sub-folders recursively_.
``` php
return Storage::disk('filerobot')->deleteDirectory($folder_uuid);
```

#### Create folder
Creates a folder from the value given in the body.
``` php
return Storage::disk('filerobot')->makeDirectory($folder_name);
```

