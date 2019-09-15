# Hejiang Storage

Thanks for your attention. This package is only used for my company projects, please do NOT use it in your product environment.

## Usage

1. Firstly, add this lines of code to your Yii application config:

    ```php
    'components' => [
        'class' => 'Hejiang\Storage\Components\StorageComponent',
        'basePath' => 'temp/',
        'driver' => [
            'class' => 'Hejiang\Storage\Drivers\Local',
            'accessKey' => '',
            'secretKey' => '',
            'bucket' => '',
        ]
    ]
    ```

2. Then after app bootstarpping, you would get the storage component instance like that:

    ```php
    $storage = \Yii::$app->storage;
    ```

    Alternatively, you can also create a driver while app running:

    ```php
    $storage->setDriver('Hejiang\Storage\Drivers\Local', []);
    ```

3. Fetch uploaded file by field name:

    ```php
    $file = $storage->getUploadedFile('FILE-FIELD-NAME');
    ```

4. Save it.

    ```php
    $url = $file->saveAs('NEW-FILE-NAME.EXT');
    // or
    $url = $file->saveWithOriginalExtension('NEW-FILE-BASE-NAME');
    // or
    $url = $file->saveAsUniqueHash();
    ```

    `$url` will be a URL string which can access this file on success, or `false` on failure.

    If there's any error occurred, these methods will throw a `Hejiang\Storage\Exceptions\StorageException`. Don't forget to `try... catch ...`.

## About

Working at: [Zhejiang Hejiang Technology Co., Ltd.](http://www.zjhejiang.com/)