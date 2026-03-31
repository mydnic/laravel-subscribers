<?php

namespace Mydnic\Kanpen\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Mydnic\Kanpen\Models\Subscriber;

class SyncSubscribersCommand extends Command
{
    protected $signature = 'kanpen:sync
                            {model : Fully qualified model class (e.g. App\\Models\\User)}
                            {--email-column=email : Column on the model that holds the email address}
                            {--filter= : Column on the model to filter by (e.g. subscribed_to_newsletter)}
                            {--filter-value=1 : Value to match for the filter column}
                            {--unsubscribe-removed : Delete subscribers whose model record no longer matches the filter}';

    protected $description = 'Sync a model (e.g. User) with the subscribers table';

    public function handle(): int
    {
        $modelClass = $this->argument('model');

        if (! class_exists($modelClass)) {
            $this->error("Class [{$modelClass}] does not exist.");

            return self::FAILURE;
        }

        $emailColumn = $this->option('email-column');
        $filterColumn = $this->option('filter');
        $filterValue = $this->option('filter-value');

        /** @var Builder $query */
        $query = $modelClass::query();

        if ($filterColumn) {
            $query->where($filterColumn, $filterValue);
        }

        $subscribed = 0;
        $skipped = 0;

        $query->chunk(200, function ($models) use ($emailColumn, &$subscribed, &$skipped) {
            foreach ($models as $model) {
                $email = $model->{$emailColumn};

                if (empty($email)) {
                    $skipped++;

                    continue;
                }

                $existing = Subscriber::withTrashed()->where('email', $email)->first();

                if ($existing && $existing->trashed()) {
                    $existing->restore();
                    $subscribed++;
                } elseif (! $existing) {
                    Subscriber::create(['email' => $email]);
                    $subscribed++;
                } else {
                    $skipped++;
                }
            }
        });

        $this->info("Sync complete. Subscribed: {$subscribed}, Skipped (already exists): {$skipped}.");

        if ($this->option('unsubscribe-removed') && $filterColumn) {
            $this->unsubscribeRemoved($modelClass, $emailColumn, $filterColumn, $filterValue);
        }

        return self::SUCCESS;
    }

    private function unsubscribeRemoved(string $modelClass, string $emailColumn, string $filterColumn, mixed $filterValue): void
    {
        // Collect emails that no longer match the filter
        $activeEmails = $modelClass::query()
            ->where($filterColumn, $filterValue)
            ->pluck($emailColumn)
            ->filter()
            ->all();

        $removed = Subscriber::whereNotIn('email', $activeEmails)->delete();

        $this->info("Removed {$removed} subscriber(s) no longer matching the filter.");
    }
}
