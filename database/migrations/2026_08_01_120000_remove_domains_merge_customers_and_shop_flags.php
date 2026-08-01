<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->upgradeUsersAndMergeCustomers();
        $this->dropDomainIdFrom('inventories');
        $this->normalizeSlugTableAndDropDomain('brands');
        $this->normalizeSlugTableAndDropDomain('categories');
        $this->dropDomainIdFromItems();
        Schema::dropIfExists('domains');
        $this->dropSubscriptionifyTables();
        $this->addShopFlags();
    }

    public function down(): void
    {
        // Irreversible structural cleanup.
    }

    protected function upgradeUsersAndMergeCustomers(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name')->nullable()->after('mobile');
            }

            if (! Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name')->nullable()->after('first_name');
            }

            if (! Schema::hasColumn('users', 'national_code')) {
                $table->string('national_code')->nullable()->after('last_name');
            }

            if (! Schema::hasColumn('users', 'birth_date')) {
                $table->date('birth_date')->nullable()->after('national_code');
            }

            if (! Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        if (! Schema::hasTable('customers')) {
            return;
        }

        $customers = DB::table('customers')->orderBy('id')->get();

        foreach ($customers as $customer) {
            $user = DB::table('users')->where('id', $customer->user_id)->first();

            if ($user === null) {
                continue;
            }

            DB::table('users')->where('id', $user->id)->update([
                'first_name' => $user->first_name ?: $customer->first_name,
                'last_name' => $user->last_name ?: $customer->last_name,
                'national_code' => $user->national_code ?: $customer->national_code,
                'birth_date' => $user->birth_date ?: $customer->birth_date,
            ]);
        }

        Schema::dropIfExists('customers');
    }

    protected function dropDomainIdFrom(string $table): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'domain_id')) {
            return;
        }

        $this->dropForeignKeyQuietly($table, $table.'_domain_id_foreign');

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->dropColumn('domain_id');
        });
    }

    protected function dropDomainIdFromItems(): void
    {
        if (! Schema::hasTable('items') || ! Schema::hasColumn('items', 'domain_id')) {
            return;
        }

        foreach ([
            'items_union_main_idx',
            'items_union_no_group_idx',
            'items_search_by_category_idx',
        ] as $index) {
            try {
                Schema::table('items', function (Blueprint $table) use ($index) {
                    $table->dropIndex($index);
                });
            } catch (\Throwable) {
                // Index may not exist.
            }
        }

        $this->dropForeignKeyQuietly('items', 'items_domain_id_foreign');

        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('domain_id');
        });

        foreach ([
            'items_union_main_idx' => ['category_id', 'is_main'],
            'items_union_no_group_idx' => ['category_id', 'group_id'],
            'items_search_by_category_idx' => ['category_id', 'title'],
        ] as $name => $columns) {
            try {
                Schema::table('items', function (Blueprint $table) use ($name, $columns) {
                    $table->index($columns, $name);
                });
            } catch (\Throwable) {
                // Index may already exist.
            }
        }
    }

    protected function normalizeSlugTableAndDropDomain(string $table): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        if (Schema::hasColumn($table, 'domain_id')) {
            $duplicates = DB::table($table)
                ->select('slug', DB::raw('MIN(id) as keep_id'), DB::raw('COUNT(*) as total'))
                ->whereNull('deleted_at')
                ->groupBy('slug')
                ->having('total', '>', 1)
                ->get();

            foreach ($duplicates as $duplicate) {
                $rows = DB::table($table)
                    ->where('slug', $duplicate->slug)
                    ->where('id', '!=', $duplicate->keep_id)
                    ->orderBy('id')
                    ->get(['id']);

                foreach ($rows as $row) {
                    DB::table($table)->where('id', $row->id)->update([
                        'slug' => $duplicate->slug.'-'.$row->id,
                    ]);
                }
            }

            $this->dropForeignKeyQuietly($table, $table.'_domain_id_foreign');

            try {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropUnique(['domain_id', 'slug']);
                });
            } catch (\Throwable) {
                // Already dropped.
            }

            if (Schema::hasColumn($table, 'domain_id')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropColumn('domain_id');
                });
            }
        }

        try {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->unique('slug');
            });
        } catch (\Throwable) {
            // Unique may already exist.
        }
    }

    protected function dropForeignKeyQuietly(string $table, string $foreignName): void
    {
        try {
            Schema::table($table, function (Blueprint $blueprint) use ($foreignName) {
                $blueprint->dropForeign($foreignName);
            });
        } catch (\Throwable) {
            // Already dropped or differently named.
        }
    }

    protected function dropSubscriptionifyTables(): void
    {
        foreach ([
            'feature_subscribables',
            'feature_usages',
            'subscriptions',
            'feature_plan',
            'features',
            'plans',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }

    protected function addShopFlags(): void
    {
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                if (! Schema::hasColumn('categories', 'show_on_home')) {
                    $table->boolean('show_on_home')->default(false)->after('sort_order');
                }

                if (! Schema::hasColumn('categories', 'views_count')) {
                    $table->unsignedBigInteger('views_count')->default(0)->after('show_on_home');
                }
            });
        }

        if (Schema::hasTable('brands') && ! Schema::hasColumn('brands', 'views_count')) {
            Schema::table('brands', function (Blueprint $table) {
                $table->unsignedBigInteger('views_count')->default(0)->after('sort_order');
            });
        }

        if (Schema::hasTable('items')) {
            Schema::table('items', function (Blueprint $table) {
                if (! Schema::hasColumn('items', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('is_main')->index();
                }

                if (! Schema::hasColumn('items', 'is_purchasable')) {
                    $table->boolean('is_purchasable')->default(true)->after('is_active')->index();
                }

                if (! Schema::hasColumn('items', 'views_count')) {
                    $table->unsignedBigInteger('views_count')->default(0)->after('is_purchasable');
                }
            });
        }
    }
};
