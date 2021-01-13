<?php

use Migrations\AbstractMigration;

class CreateApiRequests extends AbstractMigration
{

    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $table = $this->table('api_requests', ['id' => false, 'primary_key' => ['id']]);
        $table->addColumn('id', 'uuid', [
            'default' => null,
            'limit' => null,
            'null' => false,
        ]);
        $table->addColumn('http_method', 'string', [
            'limit' => 10,
            'null' => false,
        ]);
        $table->addColumn('endpoint', 'string', [
            'limit' => 2048,
            'null' => false,
        ]);
        $table->addColumn('token', 'string', [
            'default' => null,
            'limit' => 2048,
            'null' => true,
        ]);
        $table->addColumn('ip_address', 'string', [
            'default' => null,
            'limit' => 50,
            'null' => false,
        ]);
        $table->addColumn('request_data', 'text', [
            'default' => null,
            'null' => false,
            'null' => true,
        ]);
        $table->addColumn('response_code', 'integer', [
            'limit' => 5,
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('response_data', 'text', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('exception', 'text', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('created', 'datetime', [
            'null' => false,
        ]);
        $table->addColumn('modified', 'datetime', [
            'null' => false,
        ]);
        $table->create();
    }
}
