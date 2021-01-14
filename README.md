# RestApi plugin for CakePHP 4.2.2




## Our packagist repo: https://packagist.org/packages/vudp/cakephp-rest-api






This plugin provides basic support for building REST API services in your CakePHP 4 application. Read a detailed guide on how to implement below.

## Requirements
This plugin has the following requirements:

* CakePHP 4.2.2 or greater.
* PHP 7.2 or greater.

## Installation
You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

After that, you can setup our package by standing at orginal folder of your project and running
```
composer require vudp/cakephp-rest-api
```
After installation, [Load the plugin](http://book.cakephp.org/3.0/en/plugins.html#loading-a-plugin)
```php
$this->addPlugin('RestApi');
```
Or, you can load the plugin using the shell command
```sh
$ bin/cake plugin load RestApi
```

The complete code of "bootstrap" function at src/Application.php where you just added your RestApi plugin.
```
 public function bootstrap(): void
    {
        $this->addPlugin('RestApi');

        // Call parent to load bootstrap from files.
        parent::bootstrap();

        if (PHP_SAPI === 'cli') {
            $this->bootstrapCli();
        } else {
            FactoryLocator::add(
                'Table',
                (new TableLocator())->allowFallbackClass(false)
            );
        }

        /*
         * Only try to load DebugKit in development mode
         * Debug Kit should not be installed on a production system
         */
        if (Configure::read('debug')) {
            $this->addPlugin('DebugKit');
        }

        // Load more plugins here
    }
```
## Usage
You just need to create your API related controller and extend it to `ApiController` instead of default `AppController`.

## Configurations
The default configurations are as below and defined in `RestApi/config/api.php`.

```php
<?php

return [
    'ApiRequest' => [
        'debug' => false,
        'responseType' => 'json',
        'xmlResponseRootNode' => 'response',
    	'responseFormat' => [
            'statusKey' => 'status',
            'statusOkText' => 'OK',
            'statusNokText' => 'NOK',
            'resultKey' => 'result',
            'messageKey' => 'message',
            'defaultMessageText' => 'Empty response!',
            'errorKey' => 'error',
            'defaultErrorText' => 'Unknown request!'
        ],
        'log' => false,
	'logOnlyErrors' => true,
        'logOnlyErrorCodes' => [404, 500],
        'jwtAuth' => [
            'enabled' => true,
            'cypherKey' => 'R1a#2%dY2fX@3g8r5&s4Kf6*sd(5dHs!5gD4s',
            'tokenAlgorithm' => 'HS256'
        ],
        'cors' => [
            'enabled' => true,
            'origin' => '*',
            'allowedMethods' => ['GET', 'POST', 'OPTIONS'],
            'allowedHeaders' => ['Content-Type, Authorization, Accept, Origin'],
            'maxAge' => 2628000
        ]
    ]
];
```

### Debug
Set `debug` to true in your development environment to get original exception messages in response.

### Response format
It supports `json` and `xml` formats. The default response format is `json`. Set `responseType` to change your response format. In case of `xml` format, you can set the root element name by `xmlResponseRootNode` parameter.

If you want to pass token in header, use below format.
```php
Authorization: Bearer [token]
```
In case of GET or POST parameter, pass the token in `token` parameter.

### cors
By default, cors requests are enabled and allowed from all domains. You can overwrite these settings by creating config file at `APP/config/api.php`. The content of file will look like,
```php
<?php
return [
    'ApiRequest' => [
        'cors' => [
            'enabled' => true,
            'origin' => '*',
            'allowedMethods' => ['GET', 'POST', 'OPTIONS'],
            'allowedHeaders' => ['Content-Type, Authorization, Accept, Origin'],
            'maxAge' => 2628000
        ]
    ]
];
```
To disable cors request, set `enabled` flag to `false`. To allow requests from specific domains, set them in `origin` option like,
```php
<?php
return [
    'ApiRequest' => [
        'cors' => [
            'enabled' => true,
            'origin' => ['localhost', 'www.example.com', '*.example.com'],
            'allowedMethods' => ['GET', 'POST', 'OPTIONS'],
            'allowedHeaders' => ['Content-Type, Authorization, Accept, Origin'],
            'maxAge' => 2628000
        ]
    ]
];
```
### Log request & response
By default, request log is disabled. You can overwrite this by creating/updating config file at `APP/config/api.php` . The content of file will look like,
```php
<?php
return [
    'ApiRequest' => [
        'log' => true,
        // other config options
    ]
];
```
After enabling the log, you need to create a table in your database. Below is the table structure.
```sql
CREATE TABLE IF NOT EXISTS `api_requests` (
  `id` char(36) NOT NULL,
  `http_method` varchar(10) NOT NULL,
  `endpoint` varchar(2048) NOT NULL,
  `token` varchar(2048) DEFAULT NULL,
  `ip_address` varchar(50) NOT NULL,
  `request_data` longtext,
  `response_code` int(5) NOT NULL,
  `response_type` varchar(50) DEFAULT 'json',
  `response_data` longtext,
  `exception` longtext,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```
Or you can use the `bake` command to automatically generate the above table.
```sh
$ bin/cake migrations migrate --plugin RestApi
```

## Response format
The default response format of API is `json` and its structure is defined as below.
```json
{
  "status": "OK",
  "result": {
    //your result data
  }
}
```
If you have set httpResponseCode to any value other that 200, the `status` value will be `NOK` otherwise `OK`. In case of exceptions, it will be handled automatically and set the appropriate status code.
> You can modify the default response configuration like the text for OK response, key for main response data, etc. by overwriting them  in your `APP/config/api.php` file.

In case of `xml` format, the response structure will look like,
```xml
<?xml version="1.0" encoding="UTF-8"?>
<response>
    <status>1</status>
    <result>
        // your data
    </result>
</response>
```

## Examples
Below are few examples to understand how this plugin works.

Register API

Route: /api/register

Params
- email: join.nguyen@gmail.com
- password: 123456
- name: Jose Nguyen

Login API

Route: /api/login

Params
- email: join.nguyen@gmail.com
- password: 123456

### Controller > AuthController to support for certificate of log-in and registration

```
<?php
declare(strict_types=1);
namespace App\Controller;

use RestApi\Controller\ApiController;
use RestApi\Utility\JwtToken;

/**
 * AuthController Controller
 *
 */
class AuthController extends ApiController
{
    /**
     * Login method
     *
     * @return void
     */
    public function login()
    {
        $this->request->allowMethod('post');
        $this->loadModel('Users');
        $entity = $this->Users->newEntity($_REQUEST, ['validate' => 'LoginApi']);

        if ($entity->getErrors()) {
                $this->httpStatusCode = 400;
                $this->apiResponse['message'] = 'Validation failed.';
                foreach ($entity->errors() as $field => $validationMessage) {
                    $this->apiResponse['error'][$field] = $validationMessage[key($validationMessage)];
                }
            } else {
                $user = $this->Users->find()
                    ->where([
                        'email' => $entity->email,
                        'password' => md5($entity->password),
                        'status' => 1
                    ])
                    ->first();
        if (empty($user)) {
                    $this->httpStatusCode = 403;
                    $this->apiResponse['error'] = 'Invalid email or password.';
        return;
                }
        $payload = ['email' => $user->email, 'name' => $user->name];
        $this->apiResponse['token'] = JwtToken::generateToken($payload);
                $this->apiResponse['message'] = 'Logged in successfully.';
        unset($user);
                unset($payload);
            }
    }

        /**
     * Register method
     *
     * Returns a token on successful registration
     *
     * @return void
     */
    public function register()
    {
        $this->request->allowMethod('post');

        $this->loadModel('Users');

        $user = $this->Users->newEntity($_REQUEST);

        try {
            if ($this->Users->save($user)) {

                $this->apiResponse['message'] = 'Registered successfully.';
                $payload = ['email' => $user->email, 'name' => $user->name];
                $this->apiResponse['token'] = JwtToken::generateToken($payload);
            } else {
                $this->httpStatusCode = 400;
                $this->apiResponse['message'] = 'Unable to register user.';
                if ($user->errors()) {
                    $this->apiResponse['message'] = 'Validation failed.';
                    foreach ($user->errors() as $field => $validationMessage) {
                        $this->apiResponse['error'][$field] = $validationMessage[key($validationMessage)];
                    }
                }
            }
        } catch (Exception $e) {
            $this->httpStatusCode = 400;
            $this->apiResponse['message'] = 'Unable to register user.';
        }

        unset($user);
        unset($payload);
    }
}
```

### Model > Table > UsersTable.php

```
<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Event\Event;
use Cake\Datasource\EntityInterface;
use ArrayObject;

/**
 * Users Model
 *
 * @method \App\Model\Entity\User newEmptyEntity()
 * @method \App\Model\Entity\User newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\User[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\User get($primaryKey, $options = [])
 * @method \App\Model\Entity\User findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\User patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\User[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\User|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\User saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UsersTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('users');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {

        $validator
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('name')
            ->maxLength('name', 255)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmptyString('email');

        $validator
            ->scalar('password')
            ->maxLength('password', 50)
            ->requirePresence('password', 'create')
            ->notEmptyString('password');

        $validator
            ->boolean('status')
            ->notEmptyString('status');

        return $validator;
    }

    public function validationLoginApi(Validator $validator): Validator
    {

        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmptyString('email');

        $validator
            ->scalar('password')
            ->maxLength('password', 50)
            ->requirePresence('password', 'create')
            ->notEmptyString('password');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['email']), ['errorField' => 'email']);
        return $rules;
    }

    /**
     * Modifies password before saving into database
     *
     * @param Event $event Event
     * @param EntityInterface $entity Entity
     * @param ArrayObject $options Array of options
     * @return bool
     */
    public function beforeSave(Event $event, EntityInterface $entity, ArrayObject $options)
    {
        if (isset($entity->password)) {
            $entity->password = md5($entity->password);
        }

        return true;
    }
}
```

### Model > Entity > User.php

```
<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * User Entity
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property bool $status
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 */
class User extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'name' => true,
        'email' => true,
        'password' => true,
        'status' => true,
        'created' => true,
        'modified' => true,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array
     */
    protected $_hidden = [
        'password',
    ];
}
```

### Routing

```
$builder->connect('/api/login', 'Auth::login');
$builder->connect('/api/register', 'Auth::register');
```
