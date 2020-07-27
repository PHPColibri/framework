Colibri Migration component
===========================

Usage without framework
-----------------------

 - Install the package:
   ```bash
   composer require colibri-fw/migration
   ```
   
   
 - Create `migration` file in root folder of your project with the following contents:
   ```php
    #!/usr/bin/env php
    <?php
    require_once './vendor/autoload.php';
    
    use Colibri\Config\Config;
    
    Config::setBaseDir(__DIR__ . '/configs'); // <-- path to configs folder in your project
    
    require './vendor/colibri-fw/migration/bin/migration';
   ```
   and change configs folder to yours one.
   
 - Make it executable:
   ```
    chmod +x ./migration
   ```

   
 - In your configs folder create `database.php` file to configure your connection
   ```php
    <?php
    
    use Colibri\Database\Type as DbType;
    
    return [
        'connection' => [
            'default' => 'mysql',
            'mysql'   => [
                'type'       => DbType::MYSQL,
                'host'       => 'localhost',
                'database'   => 'database_name',
                'user'       => 'user',
                'password'   => 'password',
                'persistent' => false,
            ],
        ],
    ];
   ```
   
 - In your configs folder create `migration.php` file to configure migrations:
   ```php
    <?php
    
    return [
        /**
         * Name of the table where to store executed migrations.
         */
        'table'     => 'migration',
    
        /**
         * Folder where all migration classes lives.
         */
        'folder'    => __DIR__ . '/../App/Migration',    // <-- Change to yours one

        /**
         * Namespace where all migration classes lives.
         */
        'namespace' => 'App\Migration',                  // <-- Change to yours one
    ];
   ```
