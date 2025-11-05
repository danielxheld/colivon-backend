<?php

namespace App\Console\Commands;

use App\Services\ShoppingListService;
use Illuminate\Console\Command;

class ReactivateRecurringShoppingItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shopping:reactivate-recurring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reactivate recurring shopping list items that are due';

    /**
     * Execute the console command.
     */
    public function handle(ShoppingListService $service): int
    {
        $this->info('Checking for recurring items to reactivate...');

        $count = $service->reactivateRecurringItems();

        $this->info("Reactivated {$count} recurring items.");

        return Command::SUCCESS;
    }
}
