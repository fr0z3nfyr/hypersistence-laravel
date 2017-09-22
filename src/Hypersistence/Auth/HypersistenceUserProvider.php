<?php

namespace Hypersistence\Auth;

use Illuminate\Support\Str;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;

class HypersistenceUserProvider implements UserProvider
{
    /**
     * The hasher implementation.
     *
     * @var \Illuminate\Contracts\Hashing\Hasher
     */
    protected $hasher;

    /**
     * The Eloquent user model.
     *
     * @var string
     */
    protected $model;

    /**
     * Create a new database user provider.
     *
     * @param  \Illuminate\Contracts\Hashing\Hasher  $hasher
     * @param  string  $model
     * @return void
     */
    public function __construct(HasherContract $hasher, $model)
    {
        $this->hasher = $hasher;
        $this->model = $model;
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        $model = $this->createModel();

        $setId = 'set'.$model->getAuthIdentifierName();
        $model->$setId($identifier);
        $ret = $model->load();
        return $ret == false ? null : $ret ;
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed  $identifier
     * @param  string  $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        $model = $this->createModel();
        $setId = 'set'.$model->getAuthIdentifierName();
        $setToken = 'set'.$model->getRememberTokenName();
        $model->$setId($identifier);
        $model->setToken($token);
        $list = $model->search()->execute();
        if(count($list) > 0){
            return $list[0];
        }
        return null;
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $token
     * @return void
     */
    public function updateRememberToken(UserContract $user, $token)
    {
        $model = $this->createModel();
        $setToken = 'set'.$model->getRememberTokenName();

        $user->$setToken($token);
        $user->save();
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials)) {
            return null;
        }

        // First we will add each credential element to the query as a where clause.
        // Then we can execute the query and, if we found a user, return it in a
        // Eloquent User "model" that will be utilized by the Guard instances.
        $model = $this->createModel();
        foreach ($credentials as $key => $value) {
            if (! Str::contains($key, $model->getPasswordField())) {
                $set = 'set'.$key;
                $model->$set($value);
            }
        }

        $list = $model->search()->execute();

        if(count($list) > 0){
            return $list[0];
        }
        return null;
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(UserContract $user, array $credentials)
    {
        $plain = $credentials[$user->getPasswordField()];
        return $this->hasher->check($plain, $user->getAuthPassword());
    }

    /**
     * Create a new instance of the model.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createModel()
    {
         $class = '\\'.ltrim($this->model, '\\');
        $classe = new $class;
        
        if($classe instanceof \Illuminate\Contracts\Auth\Authenticatable) {
            return $classe;
        }
        $pk = $classe->getPrimaryKeyField();
        if(!class_exists('UserAux')) {
            $newClass = "
            /**
            * @table(usuario)
            * @joinColumn($pk)
            */
            class UserAux extends $class implements
                Illuminate\Contracts\Auth\Authenticatable,
                Illuminate\Contracts\Auth\Access\Authorizable,
                Illuminate\Contracts\Auth\CanResetPassword
            {
                use Hypersistence\Auth\HypersistenceAuthenticatable;
                use Illuminate\Auth\Passwords\CanResetPassword;
                use Illuminate\Foundation\Auth\Access\Authorizable;
            }";
            $newClass .= "\$aux = new UserAux();";
            eval($newClass);
        } else {
            $aux = new \UserAux();
        }

        return $aux;
    }

    /**
     * Gets the hasher implementation.
     *
     * @return \Illuminate\Contracts\Hashing\Hasher
     */
    public function getHasher()
    {
        return $this->hasher;
    }

    /**
     * Sets the hasher implementation.
     *
     * @param  \Illuminate\Contracts\Hashing\Hasher  $hasher
     * @return $this
     */
    public function setHasher(HasherContract $hasher)
    {
        $this->hasher = $hasher;

        return $this;
    }

    /**
     * Gets the name of the Eloquent user model.
     *
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Sets the name of the Eloquent user model.
     *
     * @param  string  $model
     * @return $this
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }
}