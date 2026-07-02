<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\PermissionRegistrar;

class CreatePermissionTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $teams = config('permission.teams');

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }
        if ($teams && empty($columnNames['team_foreign_key'] ?? null)) {
            throw new \Exception('Error: team_foreign_key on config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }

        Schema::create($tableNames['permissions'], function (Blueprint $table) {
            $table->bigIncrements('id'); // permission id
            $table->string('name', 100);       // For MySQL 8.0 use string('name', 125);
            $table->string('guard_name', 100); // For MySQL 8.0 use string('guard_name', 125);
            $table->timestamps();

            $table->unique(['name', 'guard_name'], 'permissions_uniqueness');
        });

        Schema::create($tableNames['roles'], function (Blueprint $table) use ($teams, $columnNames) {
            $table->bigIncrements('id'); // role id
            if ($teams || config('permission.testing')) { // permission.testing is a fix for sqlite testing
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
                $table->index($columnNames['team_foreign_key'], 'roles_team_foreign_key_index');
            }
            $table->string('name', 100);       // For MySQL 8.0 use string('name', 125);
            $table->string('guard_name', 100); // For MySQL 8.0 use string('guard_name', 125);
            $table->unsignedInteger('users_count')->default(0);
            $table->timestamps();
            if ($teams || config('permission.testing')) {
                $table->unique([$columnNames['team_foreign_key'], 'name', 'guard_name'], 'roles_uniqueness');
            } else {
                $table->unique(['name', 'guard_name'], 'roles_uniqueness');
            }
        });

        Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames, $teams) {
            $table->unsignedBigInteger(PermissionRegistrar::$pivotPermission);

            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');

            $table->foreign(PermissionRegistrar::$pivotPermission)
                ->references('id') // permission id
                ->on($tableNames['permissions'])
                ->onDelete('cascade');
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_permissions_team_foreign_key_index');

                $table->primary([$columnNames['team_foreign_key'], PermissionRegistrar::$pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary');
            } else {
                $table->primary([PermissionRegistrar::$pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary');
            }

        });

        Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames, $teams) {
            $table->unsignedBigInteger(PermissionRegistrar::$pivotRole);

            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');

            $table->foreign(PermissionRegistrar::$pivotRole)
                ->references('id') // role id
                ->on($tableNames['roles'])
                ->onDelete('cascade');
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
                $table->index($columnNames['team_foreign_key'], 'model_has_roles_team_foreign_key_index');

                $table->unique([$columnNames['team_foreign_key'], PermissionRegistrar::$pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary');
            } else {
                $table->unique([PermissionRegistrar::$pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary');
            }
        });

        Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames) {
            $table->unsignedBigInteger(PermissionRegistrar::$pivotPermission);
            $table->unsignedBigInteger(PermissionRegistrar::$pivotRole);

            $table->foreign(PermissionRegistrar::$pivotPermission)
                ->references('id') // permission id
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->foreign(PermissionRegistrar::$pivotRole)
                ->references('id') // role id
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary([PermissionRegistrar::$pivotPermission, PermissionRegistrar::$pivotRole], 'role_has_permissions_permission_id_role_id_primary');
        });

        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));

            
        /**
         * Associated role assignable roles
         * If role is not a project admin
         * he can use only these roles
         */
        Schema::create('role_assignable_user', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('assignable_user_id');

            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('assignable_user_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('roles')->onDelete('set null');
            
            $table->unique(['role_id', 'assignable_user_id'], 'role_assignable_user_uniqueness');
        });
		
        /**
         * Associated role category
         * If role is not a project admin
         * he can use only these categories
         */
        Schema::create('role_category', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('category_id');

            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('roles')->onDelete('set null');
            
            $table->unique(['role_id', 'category_id']);
        });

        /**
         * Associated role label
         * If role is not a project admin
         * he can use only these labels
         */
        Schema::create('role_label', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('label_id');

            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('label_id')->references('id')->on('labels')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('roles')->onDelete('set null');
            
            $table->unique(['role_id', 'label_id']);
        });
		
        Schema::create('role_group', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('group_id');
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('roles')->onDelete('set null');
            
            $table->unique(['role_id', 'group_id']);
        });
		
        Schema::create('role_thread', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('thread_id');

            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('thread_id')->references('id')->on('threads')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('roles')->onDelete('set null');
            
            $table->unique(['role_id', 'thread_id']);
        });
		
        Schema::create('role_folder', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('folder_id');
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('folder_id')->references('id')->on('folders')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('roles')->onDelete('set null');
            
            $table->unique(['role_id', 'folder_id']);
        });
		
        Schema::create('role_order_action', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('action_id');
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('action_id')->references('id')->on('order_actions')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('roles')->onDelete('set null');
            
            $table->unique(['role_id', 'action_id']);
        });
		
        Schema::create('role_order_step', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('step_id');
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('step_id')->references('id')->on('order_steps')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('roles')->onDelete('set null');
            
            $table->unique(['role_id', 'step_id']);
        });
		
        /**
         * Calendars that every employee 
         * has the right to access
         */
        Schema::create('role_calendar', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('calendar_id');
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('calendar_id')->references('id')->on('calendars')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('roles')->onDelete('set null');
            
            $table->unique(['role_id', 'calendar_id']);
        });
		
        Schema::create('role_questionnaire', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('questionnaire_id');
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('roles')->onDelete('set null');
            $table->foreign('questionnaire_id')->references('id')->on('questionnaires')->onDelete('cascade');
            
            $table->unique(['role_id', 'questionnaire_id']);
        });
		
        Schema::create('role_document', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('document_id');
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('roles')->onDelete('set null');
            
            $table->unique(['role_id', 'document_id']);
        });
		
        Schema::create('role_menu', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('menu_id');
            $table->unsignedInteger('order')->default(0);
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();
            
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('menu_id')->references('id')->on('menus')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('roles')->onDelete('set null');
            
            $table->unique(['role_id', 'menu_id']);
        });
        
        Schema::create('role_product', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
            
            $table->unique(['role_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('role_product');
        Schema::dropIfExists('role_assignable_user');
        Schema::dropIfExists('role_category');
        Schema::dropIfExists('role_label');
        Schema::dropIfExists('role_group');
        Schema::dropIfExists('role_thread');
        Schema::dropIfExists('role_folder');
        Schema::dropIfExists('role_order_step');
        Schema::dropIfExists('role_order_action');
        Schema::dropIfExists('role_calendar');
        Schema::dropIfExists('role_questionnaire');
        Schema::dropIfExists('role_document');
        Schema::dropIfExists('role_menu');

        $tableNames = config('permission.table_names');

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not found and defaults could not be merged. Please publish the package configuration before proceeding, or drop the tables manually.');
        }

        Schema::drop($tableNames['role_has_permissions']);
        Schema::drop($tableNames['model_has_roles']);
        Schema::drop($tableNames['model_has_permissions']);
        Schema::drop($tableNames['roles']);
        Schema::drop($tableNames['permissions']);
    }
}
