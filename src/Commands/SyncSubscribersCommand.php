<?php

namespace Mydnic\Kanpen\Commands;

use Illuminate\Console\Command;
use Mydnic\Kanpen\Traits\HasNewsletterSubscription;

class SyncSubscribersCommand extends Command
{
    protected $signature = 'kanpen:sync
                            {model : Fully qualified model class (e.g. App\\Models\\User)}';

    protected $description = 'Sync a model with the subscribers table using its shouldBeSubscribed() method';

    public function handle(): int
    {
        $modelClass = $this->argument('model');

        if (! class_exists($modelClass)) {
            $this->error("Class [{$modelClass}] does not exist.");

            return self::FAILURE;
        }

        if (! in_array(HasNewsletterSubscription::class, class_uses_recursive($modelClass))) {
            $this->error("Class [{$modelClass}] does not use the HasNewsletterSubscription trait.");

            return self::FAILURE;
        }

        $synced = 0;

        $modelClass::query()->chunk(200, function ($models) use (&$synced) {
            foreach ($models as $model) {
                $model->syncSubscriberRecord();
                $synced++;
            }
        });

        $this->info("Sync complete. Processed {$synced} record(s).");

        return self::SUCCESS;
    }
}
