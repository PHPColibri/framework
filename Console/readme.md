Colibri Console component
=========================

Usage example:

file: `./app`:
```php
#!/usr/bin/env php
<?php
require __DIR__ . '/vendor/autoload.php';

use Application\Command\Cache;
use Colibri\Console\Application;

exit(
    (new Application('Colibri Tools', 'dev'))
    ->setLogo('_________        .__  ._____.         .__
\_   ___ \  ____ |  | |__\_ |_________|__|
/    \  \/ /  _ \|  | |  || __ \_  __ \  |
\     \___(  <_> )  |_|  || \_\ \  | \/  |
 \______  /\____/|____/__||___  /__|  |__|
        \/                    \/          ')
    ->addCommands([
        new Cache\Clear(),
    ])
    ->run()
);
```

in terminal:
```
chmod +x ./app
```

file: `Application/Command/Cache/Clear.php`:
```php
<?php
namespace Application\Command\Cache;

use Colibri\Cache\Cache;
use Colibri\Console\Command;

class Clear extends Command
{

    protected function definition(): Command
    {
        return $this
            // !!! You does not need to set the name of command. It will be 'cache:clear' automatically.
            ->setDescription('Clears all cache');
    }

    protected function go(): int
    {
        Cache::flush();

        return 0;
    }
}
```
