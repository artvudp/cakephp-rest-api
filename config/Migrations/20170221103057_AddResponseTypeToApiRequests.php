<?php
use Migrations\AbstractMigration;

class AddResponseTypeToApiRequests extends AbstractMigration
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
        $table = $this->table('api_requests');
        $table->addColumn('response_type', 'string', [
            'default' => 'json',
            'limit' => 50,
            'null' => true,
            'after' => 'response_code'
        ]);
        $table->update();
    }
}
